<?php

require_once 'config/auth.php';

if (isLoggedIn()) {

    echo "User Logged In";

} else {

    echo "User Not Logged In";

}