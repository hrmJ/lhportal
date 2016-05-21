<?php

session_start();

function validate_length($inputs){
    //check that pw and usrname are of valid lengths
    //inputs = array
    foreach($inputs as $input){
         $valid = (strlen($input)>20 || strlen($input)<4 || !ctype_alnum($input) ? false : true);
         if (!$valid)
             return false;
    }
    return true;
}

function validate_login($uname,$pw){

    echo $_POST["username"];
    if (!isset($uname,$pw))
        return "Et antanut käyttäjätunnusta tai salasanaa!";
    if (!validate_length(func_get_args()))
        return "Käyttäjätunnus tai salasana eivät kelpaa";
    elseif (ctype_alnum($_POST['username']) != true)

    return "OK";

}

$msg = validate_login($_POST["username"],$_POST["password"]);

if(isset( $_SESSION['user_id'] ))
    $msg = 'Kirjautuminen voimassa.';

}

/*** check the username has only alpha numeric characters ***/
elseif (ctype_alnum($_POST['username']) != true)
{
    /*** if there is no match ***/
    $message = "Username must be alpha numeric";
}
/*** check the password has only alpha numeric characters ***/
elseif (ctype_alnum($_POST['password']) != true)
{
        /*** if there is no match ***/
        $message = "Password must be alpha numeric";
}

$message = "moro " . $_POST["username"] . "!";
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
