<?php
session_start();
require('phputils/essential.php');

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

AddHeader();
$con = new DbCon();

?>


<section id='leftbanner'>
    <ul>
        <li id='homeli' title='Takaisin alkunäkymään'>Majakkaportaali</li>
        <li style='margin-right:0px;'><a id='help' title="Lue ohjeet!">?</a></li>
    </ul>
</section>


<?php
if(isset($_POST["uusivastuu"])){
    $messut = $con->select('vastuut',Array('messu_id'),Array(),"distinct")->fetchAll();
    foreach($messut as $messu){
        $con->insert("vastuut", Array("messu_id"=>$messu["messu_id"],"vastuu"=>$_POST["uusivastuu"], "vastuullinen"=>""));
        $con->insert("messut", Array("pvm"=>$row["pvm"],"teema"=>$row["teema"]));
    }
    echo "<p>Vastuu lisätty!</p>";
}
?>
<h3 style="margin-top:3em;">Lisää uusi vastuu </h3>

<p>Tällä hetkellä käytössä seuraavat:</p>

<ul>
<?php
    $vastuut = $con->select('vastuut',Array('vastuu'),Array(),"distinct")->fetchAll();
    foreach($vastuut as $vastuu){
        echo "<li>" . $vastuu["vastuu"] . "</li>";
    }

?>
</ul>

<form id='messusyotto' action="uusivastuu.php" method="post" >
    <label for="uusivastuu">Anna uuden vastuutyypin nimi:</label>
    <input type="text" name="uusivastuu">
    <input type="submit" value="Tallenna">
</form>

</body>

<script src="scripts/essential.js"></script>
<script>
document.getElementById('homeli').addEventListener('click',function(){window.location='index.php';});
document.getElementById('seasonlist').addEventListener('change',NewSeason,false);

</script>

</html>

<?php

} #Login
?>
