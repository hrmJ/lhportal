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
    }
    elseif(isset($_POST["playeredited"])){
        UpdatePlayers($con);
    }

    AddHeader($relpath);
    #Hae url-parametrit talteen
    $url = SaveGetParams();
    #Aseta vastuulista tyhjäksi ja muuta tätä, jos messulistanäkymässä
    $vastuulist = False;

    function PlayerEditInfo($idprefix="insert"){
        
    echo '
    <p>
    <label for="playername">Nimi: </label><input type="text" id="' . $idprefix . '_playername" name="playername">
    </p>

    <p>
    <label for="phone">Puhelin: </label><input type="text" id="' . $idprefix . '_phone" name="phone">
    </p>

    <p>
    <label for="email">Sähköposti: </label><input type="text" name="email" id="' . $idprefix . '_email">
    </p>';

    }

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
    luottamuksellisia. Voit muokata soittajia klikkaamalla soittajan riviä.

    </p>

    <form name="addplayer" id="addplayer" method="POST" action="<?php echo $url;?>">

    <h3 onClick="ShowPlayerAdder();" id="addplayerheader">Lisää uusi soittaja >></h3>
    <section id="addplayersec">

        <?php PlayerEditInfo(); ?>

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
                <span><input type="text" id="instrumentname" placeholder="Kirjoita soittimen nimi"></span>
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

<section id="editrowsection">
<span onClick='CloseRowEdit();' class='fa-stack fa-lg close-button'> <i class='fa fa-circle fa-stack-2x'></i> <i class='fa fa-times fa-stack-1x fa-inverse'></i></span>

<form name="editplayer" id="editplayer" method="POST" action="<?php echo $url;?>">
    <h4>Muokkaa soittajan tietoja</h4>

    <p class="small">(Peru muokkaus sulkemalla ikkuna ruksista)</p>

    <p><a href="javascript:void(0);" onClick="RemovePlayer()"; class='deletelink'>Poista tämä soittaja</a></p>

    <?php echo PlayerEditInfo("edit"); ?>

    <h4>Soittimet</h4>

    <p>Poista soitin klikkaamalla sitä
    <input type="text" name="playerdeleted" value="false" class="hidden" id="playerdeleted">
    </p>

    <div id="addedinstruments_edit">
        <div></div>
    </div>


    <div>
        <span>
            <a href="javascript:void(0);" onClick="EditInstrument();">Lisää soitin</a>
        </span>
    </div>

    <div id="instrumentadder_edit">
        <p id="instrumentaddparagraph_edit" class="hidden">
            <span><input type="text" id="instrumentname_edit" placeholder="Kirjoita soittimen nimi" ></span>
            <span><input type="button" value="Lisää" onClick="ConfirmInstrEdit();"></span>
        </p>
    </div>

    <div>
        <input type="submit" name="playeredited" value="Tallenna muutokset" id="savechanges">
    </div>

    <input type="text" name="instruments" id="edit_repertoire" class="hidden">
    <input type="text" name="player_id" id="player_id" class="hidden">
</form>
</section>

</body>

</html>
<?php

} #Login
?>
