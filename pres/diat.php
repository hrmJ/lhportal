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
 <link rel="stylesheet" href="../font-awesome-4.6.3/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/css?family=Nothing+You+Could+Do|Quicksand" rel="stylesheet">
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
<link rel="stylesheet" href="tyylit2.css">
<title>Majakkamessu</title>
</head>

<body>


<section id='leftbanner'>

        <span class='menuright'>
            <ul>
                <li><a href="javascript:void(0);" OnClick="OpenFunctionMenu();" id="openfunctlink">Toiminnot</a></li>
                <li id="openlink" title="Käynnistä esitys"><a href="javascript:void(0);" OnClick="OpenPres();">Avaa esitys</a> </li>
                <li id="closelink" title="Sulje esitys"><a href="javascript:void(0);" OnClick="ClosePres();">Sulje esitys</a> </li>
                <li id="updatelink" title="Päivitä esitys"><a href="javascript:void(0);" OnClick="UpdateStructure();">Päivitä esitys</a> </li>
                <li id="" title="Peitä esitysnäyttö"><button id="blanklink" OnClick="BlankScreen();">BLANK SCREEN</button></li>
                <li id="" title="Suurenna esitysikkunan fonttia">Fonttikoko:  <button id="fontplus" OnClick="FontIncrease(this);">+</button> 
                                                                              <button id="fontminus" OnClick="FontIncrease(this);">-</button> 
                </li>
            </ul>
        </span>

</section>


<section id="functionalmenu">



</section>


<div id='menu'>
    <ul>
        <li class='menuli'><a href='../songs.php'>Laulujen syöttö</a></li>
        <li class='menuli' OnClick='ViewMaintenance(this);'>Ylläpito</li>
        <li class='menuli'><a href='../index.php?logout=Yes'>Kirjaudu ulos</a></li>
    </ul>
        <ul id='maintenancelist'>
            <li class='menuli'><a href='insert_messudata.php'>Syötä uusia messuja</a></li>
            <li class='menuli'><a href='uusivastuu.php'>Syötä uusia vastuutyyppejä</a></li>
        </ul>
</div>



<?php

$messu = new MessuPresentation($_GET["id"], $con, "Majakkamessu", "majakka");
$messu->UploadTrackingInfo($con);
$messu->CreateHtml();

} #Login
?>
<div id='songs'>
<?php FetchSongsForSlides($con); ?>
</div>

<script src='presenter.js'></script>

<iframe id="updaterframe"></iframe>

</body>
</html>
