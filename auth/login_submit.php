<?php

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

    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
    $password = sha1( $password ); //encrypt
    $mysql_hostname = 'localhost';
    $mysql_username = 'testuser';
    $mysql_password = 'testpw';
    $mysql_dbname = 'majakka_auth';

    try {
        $dbh = new PDO("mysql:host=$mysql_hostname;dbname=$mysql_dbname", $mysql_username, $mysql_password);
        // set the error mode to exceptions
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // prepare and bind
        $stmt = $dbh->prepare("SELECT user_id, username, password FROM majakka_users 
                    WHERE username = :username AND password = :password");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR, 40);

        $stmt->execute();
        $user_id = $stmt->fetchColumn();

        if($user_id){
            $_SESSION['user_id'] = $user_id;
            $msg = 'Kirjautuminen onnistui';
        }
    }
    catch(Exception $e) {
        $msg = 'Virhe kirjautumisessa:' . $e;
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
