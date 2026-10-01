(function () {
    var STORAGE_KEY = 'artisan/adminScheme';
    var SCHEMES = { warm: true, soft: true };

    function applyScheme(scheme) {
        if (scheme && SCHEMES[scheme]) {
            document.documentElement.setAttribute('data-artisan-scheme', scheme);
        } else {
            document.documentElement.removeAttribute('data-artisan-scheme');
        }
    }

    /* Apply immediately (documentElement always exists, even from <head>) */
    applyScheme(localStorage.getItem(STORAGE_KEY));

    document.addEventListener('DOMContentLoaded', function () {
        applyScheme(localStorage.getItem(STORAGE_KEY));
        addDropdownItems();
    });

    function addDropdownItems() {
        var dropdowns = document.querySelectorAll('.dropdown-settings');
        if (!dropdowns.length) return;

        var saved = localStorage.getItem(STORAGE_KEY);

        dropdowns.forEach(function (dropdown) {
            var list = dropdown.querySelector('ul.dropdown-menu');
            if (!list || list.dataset.customAdded) return;
            list.dataset.customAdded = '1';

            [
                { key: 'warm', label: 'Chaleureux', icon: '☀️' },
                { key: 'soft', label: 'Gris doux',  icon: '🌫️' },
            ].forEach(function (item) {
                var li = document.createElement('li');
                li.innerHTML =
                    '<a class="dropdown-item dropdown-appearance-item' + (saved === item.key ? ' active' : '') + '" ' +
                    'data-artisan-scheme="' + item.key + '" href="#" ' +
                    'style="display:flex;align-items:center;gap:6px;">' +
                    '<span style="font-size:13px">' + item.icon + '</span>' +
                    '<span>' + item.label + '</span></a>';
                list.appendChild(li);

                li.querySelector('a').addEventListener('click', function (e) {
                    e.preventDefault();
                    localStorage.setItem(STORAGE_KEY, item.key);
                    applyScheme(item.key);

                    /* Update active state on all appearance items */
                    document.querySelectorAll('a.dropdown-appearance-item').forEach(function (a) {
                        a.classList.remove('active');
                    });
                    document.querySelectorAll('a[data-artisan-scheme="' + item.key + '"]').forEach(function (a) {
                        a.classList.add('active');
                    });

                    /* Remove EA's own active marker when a custom theme is chosen */
                    document.querySelectorAll('a[data-ea-color-scheme]').forEach(function (a) {
                        a.classList.remove('active');
                    });
                });
            });

            /* When a native EA scheme is clicked, clear our custom scheme */
            dropdown.querySelectorAll('a[data-ea-color-scheme]').forEach(function (a) {
                a.addEventListener('click', function () {
                    localStorage.removeItem(STORAGE_KEY);
                    document.documentElement.removeAttribute('data-artisan-scheme');
                    document.querySelectorAll('a[data-artisan-scheme]').forEach(function (b) {
                        b.classList.remove('active');
                    });
                });
            });
        });
    }
})();
