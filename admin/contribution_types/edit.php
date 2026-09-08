<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$pdo = $database->connect();


$stmt = $pdo->prepare("
    SELECT
        contribution_type_id,
        type_name,
        description,
        status
    FROM contribution_types
    WHERE contribution_type_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$type = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$type) {
    header('Location: index.php');
    exit;
}


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

    <div class="card clan-page-card">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-edit mr-2 text-warning"></i>

                <?= __('edit_contribution_type') ?>

            </h5>

        </div>


        <form
            action="update.php"
            method="POST"
        >

            <input
                type="hidden"
                name="contribution_type_id"
                value="<?= (int)$type['contribution_type_id'] ?>"
            >


            <div class="card-body">

                <div class="form-group">

                    <label>

                        Type Name

                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="text"
                        name="type_name"
                        class="form-control"
                        maxlength="100"
                        required
                        value="<?= htmlspecialchars($type['type_name']) ?>"
                    >

                </div>


                <div class="form-group">

                    <label>

                        Description

                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                    ><?= htmlspecialchars($type['description'] ?? '') ?></textarea>

                </div>


                <div class="form-group">

                    <label>

                        Status

                    </label>

                    <select
                        name="status"
                        class="form-control"
                    >

                        <option
                            value="active"
                            <?= $type['status'] === 'active' ? 'selected' : '' ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $type['status'] === 'inactive' ? 'selected' : '' ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>

            </div>


            <div class="card-footer">

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="fas fa-save mr-1"></i>

                    Update

                </button>


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

</section>

</div>


<?php

require_once '../../includes/dashboard/footer.php';

?>