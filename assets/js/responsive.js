/**
 * Clan Management System
 * Global Responsive JavaScript
 *
 * Wraps bare <table> elements (and DataTables) in a
 * horizontal-scroll container so they never overflow the
 * viewport on phones/tablets, without editing every page.
 *
 * Loaded globally via includes/dashboard/footer.php
 */

(function () {
    'use strict';

    function wrapTables(root) {
        var scope = root || document;

        var tables = scope.querySelectorAll('table');

        for (var i = 0; i < tables.length; i++) {
            var table = tables[i];

            // Skip if already inside a .table-responsive container
            var parent = table.parentElement;
            if (parent && parent.classList.contains('table-responsive')) {
                continue;
            }

            // Skip if an ancestor is already a scrollable context
            var ancestor = table.parentElement;
            var alreadyWrapped = false;
            while (ancestor) {
                if (
                    ancestor.classList &&
                    ancestor.classList.contains('table-responsive')
                ) {
                    alreadyWrapped = true;
                    break;
                }
                ancestor = ancestor.parentElement;
            }
            if (alreadyWrapped) {
                continue;
            }

            var wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            wrapper.style.overflowX = 'auto';
            wrapper.style.webkitOverflowScrolling = 'touch';

            parent.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            wrapTables();
        });
    } else {
        wrapTables();
    }
})();
