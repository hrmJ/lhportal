<?php
session_start();
require('../phputils/dbutils.php');

if(!isset($_POST['username'], $_POST['password'], $_POST['form_token']))
{
    $message = 'Please enter a valid username and password';
}
elseif( $_POST['form_token'] != $_SESSION['form_token'])
{
    $message = 'Invalid form submission';
}
elseif (strlen( $_POST['username']) > 20 || strlen($_POST['username']) < 4)
{
    $message = 'Incorrect Length for Username';
}
elseif (strlen( $_POST['password']) > 20 || strlen($_POST['password']) < 4)
{
    $message = 'Incorrect Length for Password';
}
elseif (ctype_alnum($_POST['username']) != true)
{
    $message = "Username must be alpha numeric";
}

elseif (ctype_alnum($_POST['password']) != true)
{
        $message = "Password must be alpha numeric";
}
else
{
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

    #encrypt the password
    $password = sha1( $password );
    
    $con = new DbCon();

    try
    {
        $con->insert("majakka_users", Array("username"=>$username,"password"=>$password));
        unset($_SESSION['form_token']);
        $message = 'New user added';
    }
    catch(Exception $e)
    {
        if( $e->getCode() == 23000)
        {
            $message = 'Username already exists';
        }
        else
        {
            $message = "$e";
        }
    }
}
?>

<html>
<head>
<title>Processing the new user...</title>
</head>
<body>
<p><?php echo $message . "</p>"; ?>
</body>
</html>
