<?php
declare(strict_types=1);

namespace Magento\Braintree\Console;

use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class VaultMigrate
 */
class VaultMigrate extends Command
{
    const HOST = 'host';
    const DBNAME = 'dbname';
    const USERNAME = 'username';
    const PASSWORD = 'password';

    const EAV_ATTRIBUTE_TABLE = 'eav_attribute';
    const ATTRIBUTE_ID = 'attribute_id';
    const ATTRIBUTE_CODE = 'attribute_code';

    const CUSTOMER_ENTITY_TABLE = 'customer_entity_varchar';
    const VALUE = 'value';

    const CC_MAPPER = [
        'american-express' => 'AE',
        'discover' => 'DI',
        'jcb' => 'JCB',
        'mastercard' => 'MC',
        'master-card' => 'MC',
        'visa' => 'VI',
        'maestro' => 'MI',
        'diners-club' => 'DN',
        'unionpay' => 'CUP'
    ];

    /**
     * Array to store M1 customer details
     *
     * @var array $customers
     */
    private $customers = [];
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;
    /**
     * @var BraintreeAdapter
     */
    private $braintreeAdapter;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var PaymentTokenFactory
     */
    private $paymentToken;
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * VaultMigrate constructor.
     *
     * @param ConnectionFactory $connectionFactory
     * @param BraintreeAdapter $braintreeAdapter
     * @param CustomerRepositoryInterface $customerRepository
     * @param PaymentTokenFactory $paymentToken
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param EncryptorInterface $encryptor
     * @param Json $json
     * @param string|null $name
     */
    public function __construct(
        ConnectionFactory $connectionFactory,
        BraintreeAdapter $braintreeAdapter,
        CustomerRepositoryInterface $customerRepository,
        PaymentTokenFactory $paymentToken,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        EncryptorInterface $encryptor,
        Json $json,
        string $name = null
    ) {
        parent::__construct($name);
        $this->connectionFactory = $connectionFactory;
        $this->braintreeAdapter = $braintreeAdapter;
        $this->customerRepository = $customerRepository;
        $this->paymentToken = $paymentToken;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->json = $json;
        $this->encryptor = $encryptor;
    }

    protected function configure()
    {
        $options = [
            new InputOption(self::HOST, null, InputOption::VALUE_REQUIRED, 'Hostname/IP. Port is optional'),
            new InputOption(self::DBNAME, null, InputOption::VALUE_REQUIRED, 'Database name'),
            new InputOption(
                self::USERNAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Database username. Must have read access'
            ),
            new InputOption(self::PASSWORD, null, InputOption::VALUE_REQUIRED, 'Password')
        ];

        $this->setName('braintree:migrate');
        $this->setDescription('Migrate stored cards from a Magento 1 database');
        $this->setDefinition($options);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getOption(self::HOST);
        $databaseName = $input->getOption(self::DBNAME);
        $username = $input->getOption(self::USERNAME);
        $password = $input->getOption(self::PASSWORD);

        // Set DB connection details
        $db = $this->createDbConnection($host, $databaseName, $username, $password);

        // Get the `braintree_customer_id` attribute ID
        $eavAttributeId = $this->getEavAttributeId($db);

        // Find all instances of `braintree_customer_id` in the customer entity table
        $results = $this->getBraintreeCustomers($db, $eavAttributeId);

        $output->writeln('<info>'. count($results) .' stored cards found</info>');

        // For each record, look up the Braintree ID
        foreach ($results as $result) {

            $this->

            $output->write('<info>Search Braintree for Customer ID ' . $result['braintree_id'] . '...</info>');
            $customer = $this->braintreeAdapter->getCustomerById($result['braintree_id']);

            // If we find customer records, grab the important data
            if ($customer) {
                $output->writeln('<info>Customer found!</info>');

                $customerData = [
                    'braintree_id' => $result['braintree_id'],
                    'email' => $customer->email
                ];

                if ($customer->creditCards) {
                    // grab each stored credit card
                    foreach ($customer->creditCards as $creditCard) {
                        $customerData['storedCards'][] = [
                            'token' => $creditCard->token,
                            'expirationMonth' => $creditCard->expirationMonth,
                            'expirationYear' => $creditCard->expirationYear,
                            'last4' => $creditCard->last4,
                            'cardType' => self::CC_MAPPER[str_replace(' ', '-', strtolower($creditCard->cardType))]
                        ];
                    }
                }

                // Add customer data to the main customer array
                $this->customers[] = $customerData;
            } else {
                $output->writeln('<error>No records found</error>');
            }
        }

        // For each customer, locate them in the M2 database
        foreach ($this->customers as $customer) {
            try {
                // Customer entity can only have one unique email assigned, so this method is acceptable to use.
                $m2Customer = $this->customerRepository->get($customer['email']);

                $output->write('<info>Customer ' . $customer['braintree_id'] . ' found in Magento 2 store...</info>');

                foreach ($customer['storedCards'] as $storedCard) {
                    // Create new vault payment token.
                    $vaultPaymentToken = $this->paymentToken->create(PaymentTokenFactory::TOKEN_TYPE_CREDIT_CARD);
                    $vaultPaymentToken->setCustomerId($m2Customer->getId());
                    $vaultPaymentToken->setPaymentMethodCode('braintree');
                    $vaultPaymentToken->setExpiresAt(
                        sprintf(
                            '%s-%s-01 00:00:00',
                            $storedCard['expirationYear'],
                            $storedCard['expirationMonth']
                        )
                    );
                    $vaultPaymentToken->setGatewayToken($storedCard['token']);
                    $vaultPaymentToken->setTokenDetails($this->json->serialize([
                        'type' => $storedCard['cardType'],
                        'maskedCC' => $storedCard['last4'],
                        'expirationDate' => $storedCard['expirationMonth'] .'/' . $storedCard['expirationYear']
                    ]));
                    $vaultPaymentToken->setPublicHash(
                        $this->encryptor->getHash(
                            $m2Customer->getId()
                            . $vaultPaymentToken->getPaymentMethodCode()
                            . $vaultPaymentToken->getType()
                            . $vaultPaymentToken->getTokenDetails()
                        )
                    );

                    if ($this->paymentTokenRepository->save($vaultPaymentToken)) {
                        $output->writeln('<info>Card stored successfully!</info>');
                    }
                }
            } catch (NoSuchEntityException $e) {
                $output->writeln(
                    '<error>Customer ' . $customer['braintree_id'] . ' not found in Magento 2 store</error>'
                );
            } catch (LocalizedException $e) {
                $output->writeln('<error>Failed to store card details.</error>');
            }
        }

        $output->writeln('<info>Migration complete</info>');
    }

    /**
     * @param $host
     * @param $databaseName
     * @param $username
     * @param null $password
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function createDbConnection($host, $databaseName, $username, $password = null)
    {
        return $this->connectionFactory->create([
            'host' => $host,
            'dbname' => $databaseName,
            'username' => $username,
            'password' => $password ?? null
        ]);
    }

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $db
     * @return string
     */
    private function getEavAttributeId(\Magento\Framework\DB\Adapter\AdapterInterface $db)
    {
        $select = $db->select()
            ->where('attribute_code = ?', 'braintree_customer_id')
            ->from(self::EAV_ATTRIBUTE_TABLE, self::ATTRIBUTE_ID);
        return $db->fetchOne($select);
    }

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $db
     * @param string $eavAttributeId
     * @return array
     */
    private function getBraintreeCustomers(\Magento\Framework\DB\Adapter\AdapterInterface $db, string $eavAttributeId)
    {
        $select = $db->select()
            ->join('customer_entity', 'customer_entity.entity_id = customer_entity_varchar.entity_id')
            ->where(self::ATTRIBUTE_ID . ' = ?', $eavAttributeId)
            ->from(self::CUSTOMER_ENTITY_TABLE, self::VALUE . ' as braintree_id');
        return $db->fetchAll($select);
    }
}
