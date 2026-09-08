<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once 'functions.php';

requireLogin();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: ../login.php');
    exit;
}

/* =========================================================
   LOGGED-IN USER ROLE
========================================================= */

$pdo = familyTreePDO();
$clanId = getCurrentClanId();

$stmt = $pdo->prepare("
    SELECT 
        u.user_id,
        u.role_id,
        r.role_name
    FROM users u
    LEFT JOIN roles r 
        ON r.role_id = u.role_id
    WHERE u.user_id = ?
      AND u.is_deleted = 0
      AND u.clan_id = ?
    LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$loggedInUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loggedInUser) {
    header('Location: ../login.php');
    exit;
}

$userRole = $loggedInUser['role_name'] ?? 'Member';

$roleFolder = strtolower(trim($userRole));

// Map administrator to admin folder
if ($roleFolder === 'administrator') {
    $roleFolder = 'admin';
}


/* =========================================================
   COMPLETE CLAN TREE
========================================================= */

$trees = getCompleteClanTree();



/* =========================================================
   SEARCH
========================================================= */

$searchQuery = trim($_GET['search'] ?? '');

if ($searchQuery !== '') {
    $searchQuery = strtolower($searchQuery);
}


/* =========================================================
   MEMBER CARD
========================================================= */

function renderFamilyMemberCard($member, $relationship = '')
{
    if (!$member) {
        return;
    }

    $name = htmlspecialchars(
        familyMemberName($member),
        ENT_QUOTES,
        'UTF-8'
    );

    $code = htmlspecialchars(
        $member['member_code'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );

    $photo = htmlspecialchars(
        familyMemberPhoto($member),
        ENT_QUOTES,
        'UTF-8'
    );

    $deceased = !empty($member['is_deceased']);

    /*
    |--------------------------------------------------------------------------
    | Relationship text
    |--------------------------------------------------------------------------
    */

    $relationshipText = $relationship
        ? htmlspecialchars(
            $relationship,
            ENT_QUOTES,
            'UTF-8'
        )
        : '';

    ?>

<a
    href="member.php?id=<?= (int)$member['user_id'] ?>"
    class="family-tree-card-link family-tree-member"
    data-member-name="<?= htmlspecialchars(
        strtolower(familyMemberName($member)),
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    data-member-code="<?= htmlspecialchars(
        strtolower($member['member_code'] ?? ''),
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

    <?php
            $genderClass = strtolower($member['gender'] ?? '');
            ?>

            <div class="family-tree-card <?= 
                $genderClass === 'male'
                    ? 'gender-male'
                    : ($genderClass === 'female' ? 'gender-female' : '')
            ?>">

            <div class="family-tree-photo">

                <img
                    src="<?= $photo ?>"
                    alt="<?= $name ?>"
                    onerror="this.onerror=null;this.src='../assets/images/default-avatar.png';"
                >

                <?php if ($deceased): ?>

                    <span
                        class="family-tree-deceased"
                        title="<?= __('deceased') ?>"
                    >
                        ★
                    </span>

                <?php endif; ?>

            </div>


            <div class="family-tree-info">

                <?php if ($relationshipText): ?>

                    <div class="family-tree-relation">
                        <?= $relationshipText ?>
                    </div>

                <?php endif; ?>


                <div class="family-tree-name">
                    <?= $name ?>
                </div>


                <div class="family-tree-code">
                    <?= $code ?>
                </div>


                <?php if (!empty($member['gender'])): ?>

                    <div class="family-tree-gender">
                        <?= htmlspecialchars(
                            $member['gender'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </div>

                <?php endif; ?>

            </div>

        </div>

    </a>

    <?php
}


/* =========================================================
   RECURSIVE BRANCH
========================================================= */

function renderFamilyBranch($node, $level = 0)
{
    if (
        !$node ||
        empty($node['member'])
    ) {
        return;
    }

    $member   = $node['member'];
    $spouse   = $node['spouse'] ?? null;
    $children = $node['children'] ?? [];

    ?>

    <div class="family-tree-generation">

        <div class="family-tree-couple">

            <?php

            renderFamilyMemberCard(
                $member,
                $level === 0
                    ? __('ancestor')
                    : __('member_relation')
            );

            ?>


            <?php if ($spouse): ?>

                <div class="family-tree-spouse-line">
                    <span>♥</span>
                </div>

                <?php

                renderFamilyMemberCard(
                    $spouse,
                    __('spouse_relation')
                );

                ?>

            <?php endif; ?>

        </div>


        <?php if (!empty($children)): ?>

            <div class="family-tree-parent-connector"></div>

            <div class="family-tree-children">

                <?php foreach ($children as $child): ?>

                    <div class="family-tree-child">

                        <?php

                        renderFamilyBranch(
                            $child,
                            $level + 1
                        );

                        ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <?php
}


/* =========================================================
   HEADER / SIDEBAR / NAVBAR
========================================================= */

require_once '../includes/dashboard/header.php';


/*
|--------------------------------------------------------------------------
| Use actual logged-in user's role
|--------------------------------------------------------------------------
*/

$sidebarFile =
    '../includes/dashboard/sidebars/'
    . $roleFolder
    . '.php';


if (file_exists($sidebarFile)) {

    require_once $sidebarFile;

} else {

    /*
    |--------------------------------------------------------------------------
    | Safety fallback
    |--------------------------------------------------------------------------
    */

    require_once '../includes/dashboard/sidebars/member.php';
}


require_once '../includes/dashboard/navbar.php';

?>


<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">


            <!-- PAGE HEADER -->

            <div class="card clan-page-card family-tree-toolbar">

    <div class="card-body">

        <div class="family-tree-toolbar-top">

            <div>
                <h3 class="mb-1">
                    <i class="fas fa-sitemap mr-2 text-primary"></i>
                    <?= __('family_tree') ?>
                </h3>

                <p class="text-muted mb-0">
                    <?= __('complete_clan_ancestry') ?>
                </p>
            </div>

            <div class="family-tree-actions">

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    id="treeZoomOut"
                    title="<?= __('zoom_out') ?>"
                >
                    <i class="fas fa-minus"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    id="treeZoomReset"
                    title="<?= __('reset_zoom') ?>"
                >
                    <i class="fas fa-expand"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    id="treeZoomIn"
                    title="<?= __('zoom_in') ?>"
                >
                    <i class="fas fa-plus"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    onclick="window.location.reload();"
                >
                    <i class="fas fa-sync-alt mr-1"></i>
                    <?= __('refresh') ?>
                </button>

            </div>

        </div>

        <form
            method="GET"
            class="family-tree-search mt-3"
        >

        <div class="input-group">

                    <input
                        type="search"
                        name="search"
                        value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"
                        class="form-control"
                        placeholder="<?= __('search_member_placeholder') ?>"
                        autocomplete="off"
                    >

                    <div class="input-group-append">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-search mr-1"></i>
                            <?= __('search') ?>
                        </button>

                        <?php if ($searchQuery !== ''): ?>

                            <a
                                href="index.php"
                                class="btn btn-outline-secondary"
                            >
                                <?= __('clear') ?>
                            </a>

                        <?php endif; ?>

                        </div>

            </div>

        </form>

    </div>

</div>


            <!-- COMPLETE CLAN TREE -->
            <div class="card clan-page-card">

                <div class="card-body">

                <?php if (!empty($trees)): ?>

                <!--FAMILY TREE CONTROLS -->
                <div class="family-tree-controls mb-3">

                    

                </div>


                <div class="family-tree-wrapper"
                             id="familyTreeViewport"
                             >

                    <div
                        class="family-tree-canvas"
                        id="familyTreeCanvas"
                    >

                        <div class="family-tree-forest">

                            <?php foreach ($trees as $tree): ?>

                                <div class="family-tree-root">

                                    <?php
                                    renderFamilyBranch($tree);
                                    ?>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                    </div>
                    
                    
                    <?php else:  ?>

                        <div class="text-center py-5">

                            <div class="mb-3">

                                <i
                                    class="fas fa-sitemap fa-3x text-muted"
                                ></i>

                            </div>

                            <h5>
                                <?= __('family_tree_not_available') ?>
                            </h5>

                            <p class="text-muted mb-0">

                                <?= __('no_family_relationships') ?>

                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </section>

</div>


<?php

require_once '../includes/dashboard/footer.php';


?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const viewport = document.getElementById('familyTreeViewport');
    const canvas = document.getElementById('familyTreeCanvas');

    if (!viewport || !canvas) return;

    let scale = 1;
    let translateX = 0;
    let translateY = 0;

    let dragging = false;

    let startX = 0;
    let startY = 0;

    let startTranslateX = 0;
    let startTranslateY = 0;

    const activePointers = new Map();
    let lastPinchDistance = 0;


    function updateTransform() {

        canvas.style.transform =
            `translate(${translateX}px, ${translateY}px) scale(${scale})`;
    }


    function fitToScreenIfNeeded() {

        const isSmallScreen =
            window.matchMedia('(max-width: 767.98px)').matches;

        if (!isSmallScreen || scale !== 1) return;

        const viewportWidth = viewport.clientWidth;

        const contentWidth = canvas.scrollWidth;

        if (contentWidth > viewportWidth) {

            scale = Math.max(
                Math.min(viewportWidth / contentWidth, 1),
                0.4
            );

            translateX = 0;
            translateY = 0;

            updateTransform();

        }

    }


    document.getElementById('treeZoomIn')
        ?.addEventListener('click', function () {

            scale = Math.min(scale + 0.1, 2.5);

            updateTransform();

        });


    document.getElementById('treeZoomOut')
        ?.addEventListener('click', function () {

            scale = Math.max(scale - 0.1, 0.4);

            updateTransform();

        });


    document.getElementById('treeZoomReset')
        ?.addEventListener('click', function () {

            scale = 1;

            translateX = 0;
            translateY = 0;

            updateTransform();

        });


    viewport.addEventListener('pointerdown', function (e) {

        if (e.target.closest('a')) {
            return;
        }

        activePointers.set(e.pointerId, e);

        if (activePointers.size === 2) {

            const pts = Array.from(activePointers.values());

            lastPinchDistance = Math.hypot(
                pts[0].clientX - pts[1].clientX,
                pts[0].clientY - pts[1].clientY
            );

            return;

        }

        if (activePointers.size > 2) return;

        dragging = true;

        viewport.classList.add('is-dragging');

        startX = e.clientX;
        startY = e.clientY;

        startTranslateX = translateX;
        startTranslateY = translateY;

        viewport.setPointerCapture(e.pointerId);

    });


    viewport.addEventListener('pointermove', function (e) {

        if (activePointers.size >= 2) {

            activePointers.set(e.pointerId, e);

            const pts = Array.from(activePointers.values());

            const distance = Math.hypot(
                pts[0].clientX - pts[1].clientX,
                pts[0].clientY - pts[1].clientY
            );

            if (lastPinchDistance > 0 && distance > 0) {

                const factor = distance / lastPinchDistance;

                scale = Math.min(
                    Math.max(scale * factor, 0.4),
                    2.5
                );

                updateTransform();

            }

            lastPinchDistance = distance;

            return;

        }

        if (!dragging) return;

        translateX =
            startTranslateX + (e.clientX - startX);

        translateY =
            startTranslateY + (e.clientY - startY);

        updateTransform();

    });


    function stopDragging(e) {

        activePointers.delete(e.pointerId);

        if (activePointers.size < 2) {
            lastPinchDistance = 0;
        }

        if (activePointers.size === 0) {

            dragging = false;

            viewport.classList.remove('is-dragging');

        }

    }


    viewport.addEventListener('pointerup', stopDragging);
    viewport.addEventListener('pointercancel', stopDragging);
    viewport.addEventListener('pointerleave', stopDragging);


    /* =====================================================
       MOUSE WHEEL ZOOM
    ===================================================== */

    viewport.addEventListener(
        'wheel',
        function (e) {

            e.preventDefault();

            if (e.deltaY < 0) {

                scale = Math.min(
                    scale + 0.08,
                    2.5
                );

            } else {

                scale = Math.max(
                    scale - 0.08,
                    0.4
                );

            }

            updateTransform();

        },
        { passive: false }
    );


    updateTransform();

    fitToScreenIfNeeded();

});






document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.querySelector(
        'input[name="search"]'
    );

    const members = document.querySelectorAll(
        '.family-tree-member'
    );

    if (!searchInput || !members.length) {
        return;
    }

    searchInput.addEventListener('input', function () {

        const query = this.value
            .trim()
            .toLowerCase();

        members.forEach(function (member) {

            const name = (
                member.dataset.memberName || ''
            ).toLowerCase();

            const code = (
                member.dataset.memberCode || ''
            ).toLowerCase();

            const matched =
                query === '' ||
                name.includes(query) ||
                code.includes(query);

            if (matched) {

                member.style.display = 'block';

                member.classList.add(
                    'family-tree-search-match'
                );

            } else {

                member.style.display = 'none';

                member.classList.remove(
                    'family-tree-search-match'
                );

            }

        });

    });

});


</script>