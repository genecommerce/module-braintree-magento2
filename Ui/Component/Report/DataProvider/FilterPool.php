<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Braintree\Ui\Component\Report\DataProvider;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool as MagentoFilterPool;

/**
 * Filter poll apply filters from search criteria
 * Created to fix error 'Call to undefined method getSelect() on TransactionsCollection'.
 * Magento updated the method applyFilters of FilterPool class to use property 'Select' of collection, but
 * method applyFilters continue to expect object of Magento\Framework\Data\Collection which haven't property 'Select'.
 *
 * @api
 */
class FilterPool extends MagentoFilterPool
{
    /**
     * @param FilterApplierInterface[] $appliers
     */
    public function __construct(array $appliers = [])
    {
        parent::__construct($appliers);
    }

    /**
     * Apply filters from search criteria
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     */
    public function applyFilters(Collection $collection, SearchCriteriaInterface $criteria): void
    {
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                /** @var $filterApplier FilterApplierInterface*/
                if (isset($this->appliers[$filter->getConditionType()])) {
                    $filterApplier = $this->appliers[$filter->getConditionType()];
                } else {
                    $filterApplier = $this->appliers['regular'];
                }
                $filterApplier->apply($collection, $filter);
            }
        }
    }
}
