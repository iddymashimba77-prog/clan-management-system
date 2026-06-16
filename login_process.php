<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once 'config/database.php';
require_once 'config/constants.php';

/*
|--------------------------------------------------------------------------
| request ni POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: login.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Pokea data kutoka kwenye form
|--------------------------------------------------------------------------
*/

$login = trim($_POST['login'] ?? '');

$password = trim($_POST['password'] ?? '');

/*
|--------------------------------------------------------------------------
| Validation ya mwanzo
|--------------------------------------------------------------------------
*/

if (empty($login) || empty($password)) {

    $_SESSION['error'] = "Please enter your login details and password.";

    header("Location: login.php");

    exit;

}

/*
|--------------------------------------------------------------------------
| Search user
|--------------------------------------------------------------------------
*/

/* $sql = "
    SELECT *
    FROM users
    WHERE
        member_code = :login
        OR username = :login
        OR email = :login
        OR phone = :login
    LIMIT 1
"; */


$sql = "
    SELECT *
    FROM users
    WHERE
        member_code = :member_code
        OR username = :username
        OR email = :email
        OR phone = :phone
    LIMIT 1
";

/* $stmt = $pdo->prepare($sql);

$stmt->execute([
    ':login' => $login
]);


*/

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':member_code' => $login,
    ':username'    => $login,
    ':email'       => $login,
    ':phone'       => $login
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| User Exists?
|--------------------------------------------------------------------------
*/

if (!$user) {

    $_SESSION['error'] = "Invalid login credentials.";

    header("Location: login.php");

    exit;

}

/*
|--------------------------------------------------------------------------
| Account Status
|--------------------------------------------------------------------------
*/

if ($user['account_status'] !== 'active') {

    $_SESSION['error'] = "Your account is inactive.";

    header("Location: login.php");

    exit;

}

/*
|--------------------------------------------------------------------------
| Verify Password
|--------------------------------------------------------------------------
*/

if (!password_verify($password, $user['password_hash'])) {

    $_SESSION['error'] = "Invalid login credentials.";

    header("Location: login.php");

    exit;

} 


/*
|--------------------------------------------------------------------------
| Secure Session
|--------------------------------------------------------------------------
*/

/* session_regenerate_id(true);

$_SESSION['user_id'] = $user['user_id'];

$_SESSION['member_code'] = $user['member_code'];

$_SESSION['role_id'] = $user['role_id'];

$_SESSION['full_name'] =
    trim(
        $user['first_name'] . ' ' .
        ($user['middle_name'] ?? '') . ' ' .
        $user['last_name']
    ); 

    echo "<h2>Login Successful ✅</h2>";

    echo "<pre>";

    print_r($_SESSION);

    echo "</pre>";
    */
    
    switch ($user['role_id']) {

        case ROLE_CHAIRPERSON:
            header("Location: admin/dashboard.php");
            break;
    
        case ROLE_SECRETARY:
            header("Location: secretary/dashboard.php");
            break;
    
        case ROLE_TREASURER:
            header("Location: treasurer/dashboard.php");
            break;
    
        default:
            header("Location: member/dashboard.php");
            break;
    }
    
    exit;