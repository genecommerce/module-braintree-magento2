define(
    [
        'uiComponent',
        'knockout',
        'mage/translate'
    ],
    function (
        Component,
        ko,
        $t
    ) {
        return {
            init: function (element, context) {
                if (!element || !context) {
                    return;
                }

                var button = document.createElement('button');
                button.innerHTML = 'Foo';
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    alert('foo');
                });
                element.appendChild(button);
            }
        }
    }
);