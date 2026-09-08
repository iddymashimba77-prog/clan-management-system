<?php

/**
 * FAMILY TREE - CLAN RELATIONSHIP ENGINE
 */

require_once '../config/database.php';
require_once '../config/tenant.php';
require_once '../config/language.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   PDO
========================================================= */

function familyTreePDO()
{
    static $pdo = null;

    if ($pdo === null) {
        $database = new Database();
        $pdo = $database->connect();
    }

    return $pdo;
}


/* =========================================================
   MEMBER
========================================================= */

function getFamilyMember($user_id)
{
    $pdo = familyTreePDO();
    $clanId = getCurrentClanId();

    $stmt = $pdo->prepare("
        SELECT
            user_id,
            member_code,
            first_name,
            middle_name,
            last_name,
            gender,
            date_of_birth,
            profile_picture,
            family_member_type,
            marital_status,
            is_deceased,
            death_date,
            father_user_id,
            mother_user_id,
            spouse_user_id
        FROM users
        WHERE user_id = ?
          AND is_deleted = 0
          AND account_status = 'active'
          AND clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id, $clanId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}


/* =========================================================
   ALL CLAN MEMBERS
========================================================= */

function getAllFamilyMembers()
{
    $pdo = familyTreePDO();
    $clanId = getCurrentClanId();

    $stmt = $pdo->prepare("
        SELECT
            user_id,
            member_code,
            first_name,
            middle_name,
            last_name,
            gender,
            date_of_birth,
            profile_picture,
            family_member_type,
            marital_status,
            is_deceased,
            death_date,
            father_user_id,
            mother_user_id,
            spouse_user_id
        FROM users
        WHERE is_deleted = 0
          AND account_status = 'active'
          AND clan_id = ?
        ORDER BY
            date_of_birth ASC,
            first_name ASC,
            last_name ASC
    ");

    $stmt->execute([$clanId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================================================
   NAME
========================================================= */

function familyMemberName($member)
{
    if (!$member) {
        return __('unknown');
    }

    $name = trim(
        $member['first_name'] . ' ' .
        ($member['middle_name'] ?? '') . ' ' .
        $member['last_name']
    );

    return preg_replace('/\s+/', ' ', $name);
}


/* =========================================================
   SPOUSE
========================================================= */

function getSpouse($user_id)
{
    $pdo = familyTreePDO();
    $clanId = getCurrentClanId();

    $stmt = $pdo->prepare("
        SELECT
            s.user_id,
            s.member_code,
            s.first_name,
            s.middle_name,
            s.last_name,
            s.gender,
            s.date_of_birth,
            s.profile_picture,
            s.family_member_type,
            s.marital_status,
            s.is_deceased,
            s.death_date,
            s.father_user_id,
            s.mother_user_id,
            s.spouse_user_id
        FROM users u
        INNER JOIN users s
            ON s.user_id = u.spouse_user_id
        WHERE u.user_id = ?
          AND u.is_deleted = 0
          AND u.account_status = 'active'
          AND u.spouse_user_id IS NOT NULL
          AND u.clan_id = ?
          AND s.is_deleted = 0
          AND s.account_status = 'active'
          AND s.clan_id = ?
          AND s.user_id <> u.user_id
        LIMIT 1
    ");

    $stmt->execute([(int)$user_id, $clanId, $clanId]);

    $spouse = $stmt->fetch(PDO::FETCH_ASSOC);

    return $spouse ?: null;
}


/* =========================================================
   PARENTS
========================================================= */

function getFather($user_id)
{
    $member = getFamilyMember($user_id);

    if (!$member || empty($member['father_user_id'])) {
        return null;
    }

    return getFamilyMember($member['father_user_id']);
}


function getMother($user_id)
{
    $member = getFamilyMember($user_id);

    if (!$member || empty($member['mother_user_id'])) {
        return null;
    }

    return getFamilyMember($member['mother_user_id']);
}


/* =========================================================
   CHILDREN
========================================================= */

function getChildren($user_id)
{
    $pdo = familyTreePDO();
    $clanId = getCurrentClanId();

    $stmt = $pdo->prepare("
        SELECT
            user_id,
            member_code,
            first_name,
            middle_name,
            last_name,
            gender,
            date_of_birth,
            profile_picture,
            family_member_type,
            marital_status,
            is_deceased,
            death_date,
            father_user_id,
            mother_user_id,
            spouse_user_id
        FROM users
        WHERE is_deleted = 0
          AND account_status = 'active'
          AND clan_id = ?
          AND (
                father_user_id = ?
                OR mother_user_id = ?
          )
        ORDER BY
            date_of_birth ASC,
            first_name ASC
    ");

    $stmt->execute([$clanId, $user_id, $user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================================================
   ROOT ANCESTORS
========================================================= */

function getFamilyRoots()
{
    $pdo = familyTreePDO();
    $clanId = getCurrentClanId();

    $stmt = $pdo->prepare("
        SELECT
            user_id,
            member_code,
            first_name,
            middle_name,
            last_name,
            gender,
            date_of_birth,
            profile_picture,
            family_member_type,
            marital_status,
            is_deceased,
            death_date,
            father_user_id,
            mother_user_id,
            spouse_user_id
        FROM users
        WHERE is_deleted = 0
          AND account_status = 'active'
          AND father_user_id IS NULL
          AND mother_user_id IS NULL
          AND clan_id = ?
        ORDER BY
            date_of_birth ASC,
            first_name ASC
    ");

    $stmt->execute([$clanId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================================================
   FIND ANCESTOR
========================================================= */

function findFamilyRoot($user_id)
{
    $visited = [];

    $current = getFamilyMember($user_id);

    while ($current) {

        $id = (int)$current['user_id'];

        if (isset($visited[$id])) {
            break;
        }

        $visited[$id] = true;

        $father = getFather($id);
        $mother = getMother($id);

        if (!$father && !$mother) {
            break;
        }

        /*
         * Continue upward.
         * Father is preferred, otherwise mother.
         */
        $current = $father ?: $mother;
    }

    return $current;
}


/* =========================================================
   BUILD COMPLETE FAMILY BRANCH
========================================================= */

function buildFamilyTree($user_id, &$visited = [])
{
    if (isset($visited[$user_id])) {
        return null;
    }

    $member = getFamilyMember($user_id);

    if (!$member) {
        return null;
    }

    $visited[$user_id] = true;

    $tree = [
        'member'   => $member,
        'spouse'   => null,
        'children' => []
    ];


    /* =========================================================
   EXACT SPOUSE FOR THIS MEMBER
       ========================================================= */

$spouse = getSpouse($member['user_id']);

if ($spouse) {

    $tree['spouse'] = $spouse;

    /*
     * Mark spouse as visited only to prevent
     * duplicate branch generation.
     */
    $visited[(int)$spouse['user_id']] = true;
}


    /*
    |--------------------------------------------------------------------------
    | CHILDREN OF THIS MEMBER
    |--------------------------------------------------------------------------
    */

    $children = getChildren($member['user_id']);

    foreach ($children as $child) {

        /*
        | If this child was already used as a spouse/member,
        | don't create duplicate branches.
        */

        if (isset($visited[$child['user_id']])) {
            continue;
        }

        $childTree = buildFamilyTree(
            $child['user_id'],
            $visited
        );

        if ($childTree) {
            $tree['children'][] = $childTree;
        }
    }

    return $tree;
}


/* =========================================================
   GET COMPLETE CLAN TREE
   EVERY USER CAN SEE THE WHOLE CLAN
========================================================= */

function getCompleteClanTree()
{
    $pdo = familyTreePDO();
    $clanId = getCurrentClanId();

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE is_deleted = 0
          AND account_status = 'active'
          AND father_user_id IS NULL
          AND mother_user_id IS NULL
          AND clan_id = ?
        ORDER BY date_of_birth ASC, user_id ASC
    ");

    $stmt->execute([$clanId]);

    $roots = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $trees = [];

    foreach ($roots as $rootId) {

        $visited = [];

        $tree = buildFamilyTree(
            (int)$rootId,
            $visited
        );

        if ($tree) {
            $trees[] = $tree;
        }
    }

    return $trees;
}


/* =========================================================
   OLD COMPATIBILITY FUNCTION
========================================================= */

function getTreeForMember($user_id)
{
    /*
     * IMPORTANT:
     * User identity no longer limits the tree.
     * Every logged-in member receives the complete clan tree.
     */

    return getCompleteClanTree();
}


/* =========================================================
   DECEASED
========================================================= */

function isFamilyMemberDeceased($member)
{
    return !empty($member['is_deceased']);
}


/* =========================================================
   PHOTO
========================================================= */

function familyMemberPhoto($member)
{
    $picture = (string)($member['profile_picture'] ?? '');

    if ($picture !== '') {

        $candidates = [];

        if (strpos($picture, '../') === 0) {

            // Already a relative path usable from the family_tree folder.
            $candidates[] = $picture;

        } elseif (preg_match('#^(uploads|assets|images)/#', $picture)) {

            // Project-root relative path.
            $candidates[] = '../' . ltrim($picture, '/');

        } else {

            // Bare filename -> try both upload locations.
            $candidates[] = '../uploads/profile_pictures/' . ltrim($picture, '/');
            $candidates[] = '../uploads/profiles/' . ltrim($picture, '/');
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return '../assets/images/default-avatar.png';
    }

    return '../assets/images/default-avatar.png';
}


/* =========================================================
   RELATIONSHIP LABEL
========================================================= */

function familyRelationshipLabel($member)
{
    if (!$member) {
        return '';
    }

    if (
        isset($member['family_member_type']) &&
        $member['family_member_type'] === 'marriage'
    ) {
        return __('marriage');
    }

    return __('family');
}