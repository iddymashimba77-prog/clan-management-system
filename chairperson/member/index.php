<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name,
        gender,
        phone,
        email,
        role_id
    FROM users
    WHERE is_deleted = 0
    ORDER BY user_id DESC
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">

            <?php if (
                isset($_GET['success']) &&
                $_GET['success'] === 'member_added'
            ): ?>

                <div class="alert alert-success alert-dismissible fade show clan-success-alert"
                     role="alert">

                    <i class="fas fa-check-circle mr-2"></i>

                    <strong><?= __('success_punct') ?></strong>
                    <?= __('family_member_added_successfully') ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>

            <?php endif; ?>


            <!-- PAGE HEADER -->

            <div class="card clan-page-card">

                <div class="card-header">

                    <div class="d-flex align-items-center justify-content-between">

                        <div>

                            <h3 class="card-title mb-1">

                                <i class="fas fa-users mr-2 text-primary"></i>

                                <?= __('family_members') ?>

                            </h3>


                        </div>


                        <div class="card-tools">

                            <a
                                href="create.php"
                                class="btn btn-success"
                            >

                                <i class="fas fa-user-plus mr-1"></i>

                                <?= __('add_member') ?>

                            </a>

                        </div>

                    </div>

                </div>


                <!-- MEMBERS TABLE -->

                <div class="card-body">

                    <div class="table-responsive">

                        <table
                            id="membersTable"
                            class="table table-bordered table-striped table-hover"
                        >

                            <thead>

                                <tr>

                                    <th>ID</th>

                                    <th><?= __('member_code') ?></th>

                                    <th><?= __('full_name') ?></th>

                                    <th><?= __('gender') ?></th>

                                    <th><?= __('phone') ?></th>

                                    <th><?= __('email') ?></th>

                                    <th width="180"><?= __('actions') ?></th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($members as $member): ?>

                                    <tr>

                                        <td>
                                            <?= (int)$member['user_id']; ?>
                                        </td>


                                        <td>

                                            <span class="badge badge-light">

                                                <?= htmlspecialchars(
                                                    $member['member_code']
                                                ); ?>

                                            </span>

                                        </td>


                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    trim(
                                                        $member['first_name']
                                                        . ' '
                                                        . ($member['middle_name'] ?? '')
                                                        . ' '
                                                        . $member['last_name']
                                                    )
                                                ); ?>

                                            </strong>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $member['gender']
                                            ); ?>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $member['phone'] ?? ''
                                            ); ?>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $member['email'] ?? ''
                                            ); ?>

                                        </td>


                                        <td>

                                            <?php $isAdmin =
                                                (int)($member['role_id'] ?? 0)
                                                === ROLE_ADMINISTRATOR; ?>

                                            <a
                                                href="view.php?id=<?= (int)$member['user_id']; ?>"
                                                class="btn btn-info btn-sm"
                                                title="<?= __('view') ?>"
                                            >

                                                <i class="fas fa-eye"></i>

                                            </a>


                                            <?php if ($isAdmin): ?>

                                                <a
                                                    href="#"
                                                    class="btn btn-primary btn-sm"
                                                    title="<?= __('edit') ?>"
                                                    onclick="return alert('<?= __('cannot_edit_administrator') ?>')"
                                                >

                                                    <i class="fas fa-edit"></i>

                                                </a>

                                                <a
                                                    href="#"
                                                    class="btn btn-danger btn-sm"
                                                    title="<?= __('delete') ?>"
                                                    onclick="return alert('<?= __('cannot_delete_administrator') ?>')"
                                                >

                                                    <i class="fas fa-trash"></i>

                                                </a>

                                            <?php else: ?>

                                                <a
                                                    href="edit.php?id=<?= (int)$member['user_id']; ?>"
                                                    class="btn btn-primary btn-sm"
                                                    title="<?= __('edit') ?>"
                                                >

                                                    <i class="fas fa-edit"></i>

                                                </a>


                                                <a
                                                    href="delete.php?id=<?= (int)$member['user_id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    title="<?= __('delete') ?>"
                                                    onclick="return confirm('<?= __('delete_this_member') ?>')"
                                                >

                                                    <i class="fas fa-trash"></i>

                                                </a>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>


<?php

require_once '../../includes/dashboard/footer.php';
?>