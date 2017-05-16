<?php
session_start();
if (isset($_GET["logout"])){
    session_unset();
    session_destroy();
}
require('../phputils/essential.php');
$embed=True;
require('biblecrawl.php');
#session_unset();
if (!isset( $_SESSION['user_id'] )){
    if (isset($_POST["username"],$_POST["password"])){
        $valid = validate_login($_POST["username"],$_POST["password"]);
        $loginfail = True;
        if ($valid){
            //if the login info passed validation and no active session, try to login
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

            $con = new DbCon();
            $usr_id = $con->SelectUser($username, $password);

            if($usr_id){
                $_SESSION['user_id'] = $usr_id;
                $loginfail = False;
            }

        }
    }
    if($loginfail or !$valid or !isset($_POST["username"],$_POST["password"])){
        #Kun saavutaan sivulle 1. kertaa tai kirjautuminen ei onnistunut
        require('../login.php');
    }
}
if (isset($_SESSION['user_id'])){
#JOS kirjauduttu onnistuneesti
$con = new DbCon();

?>

<html lang="fi">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
<title>updater script</title>
</head>

<body>

<?php

$messu = new MessuPresentation($_GET["id"], $con);
$messu->UploadTrackingInfo($con);
$messu->CreateHtml($onbackground=true);

} #Login
?>

<div id='songs'>
<?php FetchSongsForSlides($con); ?>
</div>

<div id='updatecompleted'>
</div>
</body>
</html>
