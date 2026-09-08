<?php

declare(strict_types=1);

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/includes/public/header.php';

?>

<div class="login-page">


<div class="login-container">

    <div class="login-logo">

        <h1><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></h1>

    </div>

    <div class="login-header">

        <h2>Welcome Back</h2>

        <p>Sign in to continue.</p>

    </div>

    <?php if (!empty($_SESSION['error'])): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars(
                $_SESSION['error'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>

    <?php if (isset($_GET['timeout'])): ?>

        <div class="alert alert-warning">

            Your session has expired. Please log in again.

        </div>

    <?php endif; ?>

    <form
        action="login_process.php"
        method="POST"
        autocomplete="off"
    >

        <?= csrf_field(); ?>

        <div class="form-group">

            <label>
                Username
            </label>

            <input
                type="text"
                name="login"
                placeholder="Enter your username"
                autocomplete="username"
                required
            >

        </div>

        <div class="form-group">

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >

        </div>

        <button
            type="submit"
            class="btn btn-primary login-btn"
        >

            Login

        </button>

    </form>

    <div class="login-footer">

        <a href="#">

            Forgot Password?

            <br>

            <h6 style="color: red;">
                Contact your chairperson to create a new password.
            </h6>

        </a>

    </div>

</div>


</div>

<?php

require_once __DIR__ . '/includes/public/footer.php';

?>
