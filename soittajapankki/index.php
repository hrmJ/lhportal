<?php
$relpath = "../";
session_start();
if (isset($_GET["logout"])){
    session_unset();
    session_destroy();
}
require('../phputils/essential.php');
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

    if(isset($_POST["playeradded"])){
        #Jos äsken syötetty uusia messuja:
        InsertPlayers($con);
        echo "<script>window.alert('Uusi soittaja syötetty onnistuneesti!');</script>";
    }

    AddHeader($relpath);
    #Hae url-parametrit talteen
    $url = SaveGetParams();
    #Aseta vastuulista tyhjäksi ja muuta tätä, jos messulistanäkymässä
    $vastuulist = False;


?>

<body>


<?php
CreateNavi($vastuulist, $url, False);
?>

<article id='maincontainer'>

<section id="addnew">
    <h2>Majakan soittajapankki</h2>

    <p>

    Soittajapankin tarkoituksena on pitää yllä listaa 
    Majakkamessussa käytettävissä olevista  muusikoista. Yhteystiedot ovat
    luottamuksellisia.

    </p>

    <form name="addplayer" id="addplayer" method="POST" action="<?php echo $url;?>">

    <h3 onClick="ShowPlayerAdder();" id="addplayerheader">Lisää uusi soittaja >></h3>
    <section id="addplayersec">

        <p>
            <label for="playername">Nimi: </label><input type="text" name="playername">
        </p>

        <p>
            <label for="phone">Puhelin: </label><input type="text" name="phone">
        </p>

        <p>
            <label for="email">Sähköposti: </label><input type="text" name="email">
        </p>

        <h4>Soittimet</h4>

        <div id="addedinstruments">
            <div id="emptyinstrumentspan">Tälle soittajalle ei ole vielä lisättynä yhtään soitinta. Lisää ainakin yksi soitin (tai rooli) painamalla alla olevaa linkkiä. </div>
        </div>


        <div>
            <span>
                <a href="javascript:void(0);" onClick="AddInstrument();">Lisää soitin</a>
            </span>
        </div>


        <div id="instrumentadder">
            <p id="instrumentaddparagraph" class="hidden">
                <span><input type="text" id="instrumentname"></span>
                <span><input type="button" value="Lisää" onClick="ConfirmInstrAdd();"></span>
            </p>
        </div>


        <div>
            <input type="submit" name= "playeradded" value="Tallenna uusi soittaja">
        </div>

        <input type="text" name="instruments" id="repertoire" class="hidden">

        </form>
    </section>
</section>

<section id="datalist">

<?php echo FetchPlayers($con); ?>

</section>

</article>

<?php require('../menu.php');?>

<script>
    //Add listeners
    document.getElementById('homeli').addEventListener('click',function(){window.location='index.php';});
    var menu_li_items = document.getElementsByClassName('menuli');
    for(var row_idx = 0; row_idx < menu_li_items.length;row_idx++){
        var row = menu_li_items[row_idx];
        row.addEventListener('click',MenuClick,false);
    }


</script>

</body>

</html>
<?php

} #Login
?>
