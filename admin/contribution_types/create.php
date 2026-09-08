<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

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

                <i class="fas fa-plus-circle mr-2 text-primary"></i>

                Create <?= __('contribution_type') ?>

            </h5>

        </div>


        <form
            action="store.php"
            method="POST"
        >

            <div class="card-body">

                <div class="form-group">

                    <label for="type_name">

                        <?= __('type_name') ?>

                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="text"
                        name="type_name"
                        id="type_name"
                        class="form-control"
                        maxlength="100"
                        required
                        placeholder="Example: Education"
                    >

                </div>


                <div class="form-group">

                    <label for="description">

                        <?= __('description') ?>

                    </label>

                    <textarea
                        name="description"
                        id="description"
                        class="form-control"
                        rows="4"
                        placeholder="Describe this contribution type..."
                    ></textarea>

                </div>


                <div class="form-group">

                    <label for="status">

                        Status

                    </label>

                    <select
                        name="status"
                        id="status"
                        class="form-control"
                    >

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
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

                    Save

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