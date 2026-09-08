/**
 * Clan Management System
 * Global JavaScript
 */


/*
|--------------------------------------------------------------------------
| Language Switch
|--------------------------------------------------------------------------
*/

function changeLanguage(language) {

    const url = new URL(
        window.location.href
    );

    url.searchParams.set(
        'lang',
        language
    );

    window.location.href =
        url.toString();

}


/*
|--------------------------------------------------------------------------
| Confirm Delete
|--------------------------------------------------------------------------
*/

function confirmDelete(message) {

    return confirm(
        message ||
        'Are you sure you want to delete this item?'
    );

}


/*
|--------------------------------------------------------------------------
| Copy To Clipboard
|--------------------------------------------------------------------------
*/

function clanCopy(text) {

    navigator.clipboard
        .writeText(text)
        .then(function () {

            alert(
                'Copied successfully.'
            );

        })
        .catch(function () {

            console.error(
                'Copy failed.'
            );

        });

}


/*
|--------------------------------------------------------------------------
| Sidebar Active State
|--------------------------------------------------------------------------
| Highlights the nav item matching the current page and keeps the
| relevant parent treeview open, so the "touched" menu item stays
| highlighted until another item is clicked/visited.
*/

function clanHighlightSidebar() {

    var currentRel = window.location.pathname;

    if (!currentRel) {
        return;
    }

    document
        .querySelectorAll(
            '.clan-system .nav-sidebar .nav-link'
        )
        .forEach(function (link) {

            var href = link.getAttribute('href') || '';

            if (
                !href ||
                href.indexOf('#') === 0 ||
                href.indexOf('javascript:') === 0
            ) {
                return;
            }

            var linkRel = (new URL(link.href))
                .pathname;

            link.classList.remove('active');

            if (linkRel === currentRel) {

                link.classList.add('active');

            } else {

                var linkFile = linkRel
                    .split('/')
                    .pop()
                    .split('?')[0];

                if (linkFile === 'index.php') {

                    var linkDir = linkRel.slice(
                        0,
                        linkRel.lastIndexOf('/')
                    );

                    if (
                        linkDir &&
                        currentRel.indexOf(linkDir + '/') === 0
                    ) {

                        link.classList.add('active');

                    }

                }

            }

        });

    /* Keep the enclosing treeview open + highlight its parent
       when a child is the active one */
    document
        .querySelectorAll(
            '.clan-system .nav-sidebar .nav-link.active'
        )
        .forEach(function (link) {

            var nested = link.closest('li.nav-item');
            var tree = nested
                ? nested.closest('.nav-treeview')
                : null;

            if (tree) {

                var parentLi = tree.closest('li.nav-item');

                if (parentLi) {

                    parentLi
                        .classList
                        .add('menu-open');

                    var parentLink = parentLi.querySelector(
                        ':scope > a.nav-link'
                    );

                    if (parentLink) {

                        parentLink
                            .classList
                            .add('active');

                    }

                }

            }

        });

}


document.addEventListener(
    'DOMContentLoaded',
    function () {
        clanHighlightSidebar();
    }
);


/*
|--------------------------------------------------------------------------
| Auto Hide Alerts
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        setTimeout(function () {

            document
                .querySelectorAll(
                    '.alert.auto-hide'
                )
                .forEach(function (alert) {

                    alert.style.transition =
                        'opacity .5s';

                    alert.style.opacity =
                        '0';

                    setTimeout(
                        function () {

                            alert.remove();

                        },
                        500
                    );

                });

        }, 5000);

    }
);