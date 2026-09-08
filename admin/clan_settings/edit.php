<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("SELECT * FROM clan_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$columnsStmt = $pdo->query("SHOW COLUMNS FROM clan_settings");
$columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-edit mr-2 text-primary"></i>

            <?= __('edit_clan_settings') ?>

        </h3>

    </div>


    <form action="update.php" method="POST">

        <div class="card-body">

            <?php foreach ($columns as $column): ?>

                <?php

                $field = $column['Field'];

                if (
                    $field === 'id' ||
                    $field === 'setting_id' ||
                    $field === 'created_at' ||
                    $field === 'updated_at'
                ) {
                    continue;
                }

                $value = $settings[$field] ?? '';

                $label = ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $field
                    )
                );

                $type = strtolower($column['Type']);

                ?>

                <div class="form-group">

                    <label>
                        <?= htmlspecialchars($label); ?>
                    </label>


                    <?php if (stripos($type, 'text') !== false): ?>

                        <textarea
                            name="<?= htmlspecialchars($field); ?>"
                            class="form-control"
                            rows="4"
                        ><?= htmlspecialchars((string)$value); ?></textarea>


                    <?php elseif (stripos($type, 'enum') !== false): ?>

                        <?php

                        preg_match(
                            "/enum\((.*)\)/",
                            $type,
                            $matches
                        );

                        $options = [];

                        if (!empty($matches[1])) {

                            $options = str_getcsv(
                                $matches[1],
                                ',',
                                "'"
                            );
                        }

                        ?>

                        <select
                            name="<?= htmlspecialchars($field); ?>"
                            class="form-control"
                        >

                            <option value="">
                                Select
                            </option>

                            <?php foreach ($options as $option): ?>

                                <option
                                    value="<?= htmlspecialchars($option); ?>"
                                    <?= (string)$value === (string)$option
                                        ? 'selected'
                                        : ''; ?>
                                >

                                    <?= htmlspecialchars($option); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                    <?php elseif (
                        stripos($type, 'int') !== false &&
                        stripos($type, 'tinyint') !== false
                    ): ?>

                        <select
                            name="<?= htmlspecialchars($field); ?>"
                            class="form-control"
                        >

                            <option value="1"
                                <?= (string)$value === '1'
                                    ? 'selected'
                                    : ''; ?>>
                                Yes
                            </option>

                            <option value="0"
                                <?= (string)$value === '0'
                                    ? 'selected'
                                    : ''; ?>>
                                No
                            </option>

                        </select>


                    <?php elseif (
                        stripos($type, 'date') !== false
                    ): ?>

                        <input
                            type="date"
                            name="<?= htmlspecialchars($field); ?>"
                            value="<?= htmlspecialchars((string)$value); ?>"
                            class="form-control"
                        >


                    <?php elseif (
                        stripos($type, 'email') !== false ||
                        stripos($field, 'email') !== false
                    ): ?>

                        <input
                            type="email"
                            name="<?= htmlspecialchars($field); ?>"
                            value="<?= htmlspecialchars((string)$value); ?>"
                            class="form-control"
                        >


                    <?php else: ?>

                        <input
                            type="text"
                            name="<?= htmlspecialchars($field); ?>"
                            value="<?= htmlspecialchars((string)$value); ?>"
                            class="form-control"
                        >

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>


        <div class="card-footer">

            <button
                type="submit"
                class="btn btn-primary"
            >

                <i class="fas fa-save mr-1"></i>

                Save Changes

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