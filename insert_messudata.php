
<?php

session_start();
require('phputils/essential.php');
$url = SaveGetParams();
#login:
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
        require('login.php');
    }
}

if (isset($_SESSION['user_id'])){
    $con = new DbCon();

?>

<html lang="fi">
<head>
  <meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
  <link rel="stylesheet" href="styles/updated.css?id=jjasdasd">
  <link rel='stylesheet' href='font-awesome-4.6.3/css/font-awesome.min.css'>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

<title>Messujen syöttö järjestelmään</title>
</head>

<body>
<?php
CreateNavi(false, $url, False);

if(isset($_POST["newservices"]))
    InsertServices($con);
if(isset($_POST["remover"]))
    RemoveServices($con);

?>
<article id="maincontainer2">
    <h2 id='syotto_ots'>Uusien messujen / kausien syöttö</h2>
    <p><em>Huom! Voit muuttaa messujen aiheita messukohtaisessa näkymässä. Tässä näkymässä voit poistaa tai lisätä messuja ja kausia.</em></p>


    <form id='messusyotto' action="insert_messudata.php" method="post" >

        <div id='slistcont' class='withsomemargin'>
            <?php ListSeasons(); ?>
        </div>

        <div id='existingservices'>
            <?php $alreadyexist = LoadExistingServices($con);?>
        </div>
        <div id='syottodiv'>
            <div id="addedservices"></div>
            <div id="adderdiv"><a href="javascript:void(0);"  onClick="AddNewService();" id="adderlink" class="simplelink <?php echo $ishidden;?>">Lisää uusi messu tähän kauteen</a></div>
            <div id="removelinkdiv"> <a class="simplelink" href="javascript:void(0);" OnClick="RemoveExistingServices();">Poista valitut messut</a></div>
            <div><input value='Tallenna messut'  type='submit' class="<?php echo $ishidden;?> sbutton" name='newservices' id='s1'> </div>
        </div>
        <input class="hidden" id="remover" type="submit" value="Poista valitut" name="remover">

    </form>


<script src="scripts/essential.js?id=09224"></script>
<script>
document.getElementById('homeli').addEventListener('click',function(){window.location='index.php';});
document.getElementById('seasonlist').addEventListener('change',NewSeason,false);
 //$( function() { $(".dateinput").datepicker(); } );
</script>


<?php require('menu.php');?>

</article>

</body>


</html>

<?php

} #Login
?>
