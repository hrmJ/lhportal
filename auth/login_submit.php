<?php

require('../sql/dbutils.php');
session_start();

function validate_login(){
    //check that pw and usrname are set, are of valid lengths, contain only alphanumeric chars
    $valid = true;
    foreach(func_get_args() as $input){
        if (!isset($input) || strlen($input)>20 || strlen($input)<4 || !ctype_alnum($input) )
            $valid = false;
    }
    return $valid;
}


$msg = 'Kirjautuminen epäonnistui';
$valid = validate_login($_POST["username"],$_POST["password"]);

if (isset( $_SESSION['user_id'] )){
    $msg = 'Kirjautuminen voimassa.';
}
elseif ($valid){
    //if the login info passed validation and no active session, try to login
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

    $con = new DbCon();
    $con->Connect();
    $usr_id = $con->SelectUser($username, $password);

    if($usr_id){
        $_SESSION['user_id'] = $usr_id;
        $msg = 'Kirjautuminen onnistui';
    }

}


?>

<html lang="fi">
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
<head>
<title>Login</title>
</head>
<body>
<p><?php echo $msg; ?></p>
</body>
</html>
