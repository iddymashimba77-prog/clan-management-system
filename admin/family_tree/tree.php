<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../family_tree/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: ../../login.php');
    exit;
}

$pdo = familyTreePDO();

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
                    onerror="this.onerror=null;this.src='../../assets/images/default-avatar.png';"
                >

                <?php if ($deceased): ?>

                    <span
                        class="family-tree-deceased"
                        title="Deceased"
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
                    ? 'ANCESTOR'
                    : 'MEMBER'
            );

            ?>


            <?php if ($spouse): ?>

                <div class="family-tree-spouse-line">
                    <span>♥</span>
                </div>

                <?php

                renderFamilyMemberCard(
                    $spouse,
                    'SPOUSE'
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

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

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
                    Complete clan ancestry, families and descendants.
                </p>
            </div>

            <div class="family-tree-actions">

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    id="treeZoomOut"
                    title="Zoom out"
                >
                    <i class="fas fa-minus"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    id="treeZoomReset"
                    title="Reset zoom"
                >
                    <i class="fas fa-expand"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    id="treeZoomIn"
                    title="Zoom in"
                >
                    <i class="fas fa-plus"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    onclick="window.location.reload();"
                >
                    <i class="fas fa-sync-alt mr-1"></i>
                    Refresh
                </button>

                <a
                    href="index.php"
                    class="btn btn-outline-secondary btn-sm"
                >
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to Management
                </a>

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
                        placeholder="Search member name or member code..."
                        autocomplete="off"
                    >

                    <div class="input-group-append">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-search mr-1"></i>
                            Search
                        </button>

                        <?php if ($searchQuery !== ''): ?>

                            <a
                                href="tree.php"
                                class="btn btn-outline-secondary"
                            >
                                Clear
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
                                Family Tree Not Available
                            </h5>

                            <p class="text-muted mb-0">

                                No family relationships have been connected yet.

                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </section>

</div>


<?php

require_once '../../includes/dashboard/footer.php';


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


    function updateTransform() {

        canvas.style.transform =
            `translate(${translateX}px, ${translateY}px) scale(${scale})`;
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

        if (e.button !== 0) return;

        dragging = true;

        startX = e.clientX;

        startY = e.clientY;

        startTranslateX = translateX;

        startTranslateY = translateY;

        viewport.style.cursor = 'grabbing';

    });


    document.addEventListener('pointermove', function (e) {

        if (!dragging) return;

        const dx = e.clientX - startX;

        const dy = e.clientY - startY;

        translateX = startTranslateX + dx;

        translateY = startTranslateY + dy;

        updateTransform();

    });


    document.addEventListener('pointerup', function () {

        dragging = false;

        viewport.style.cursor = 'grab';

    });


    viewport.addEventListener('wheel', function (e) {

        if (e.ctrlKey) {

            e.preventDefault();

            const delta = e.deltaY > 0 ? -0.1 : 0.1;

            scale = Math.max(0.4, Math.min(2.5, scale + delta));

            updateTransform();

        }

    }, { passive: false });


    viewport.style.cursor = 'grab';

});
</script>

