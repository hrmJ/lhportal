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
        InsertPlayers($con,"puhuja");
    }
    elseif(isset($_POST["playeredited"])){
        UpdatePlayers($con,"puhuja");
    }

    #Lataa myös jquery:
    AddHeader($relpath,true);
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

<div class='hidden' id='instrument_placeholder'>

<?php

$instruments = $con->select("puheenaiheet",Array("puheenaihe"),Array(),"DISTINCT","")->fetchAll();
foreach($instruments as $instrument){
    echo "<span class='listedinstrument'>" . $instrument["puheenaihe"] ."</span>";
}
?>

</div>

<article id='maincontainer'>

<section id="addnew">
    <h2>Majakan alfakurssin puhujapankki</h2>

    <p>

    Puhujapankin tarkoituksena on pitää yllä listaa 
    alfakurssilla käytettävissä olevista  puhujista. Yhteystiedot ovat
    luottamuksellisia. Voit muokata puhujia klikkaamalla puhujan riviä.

    </p>

    <form name="addplayer" id="addplayer" method="POST" action="<?php echo $url;?>">

    <h3 onClick="ShowPlayerAdder();" id="addplayerheader">Lisää uusi puhuja >></h3>


    <section id="addplayersec">

        <?php PlayerEditInfo(); ?>

        <h4>Aiheet</h4>


        <div id="addedinstruments">
            <div id="emptyinstrumentspan">Tälle puhujalle ei ole vielä lisättynä yhtään aihetta. Lisää ainakin yksi aihe (tai muu erikoiskyky) painamalla alla olevaa linkkiä. </div>
        </div>


        <div>
            <span>
                <a href="javascript:void(0);" onClick="AddInstrument();">Lisää aihe</a>
            </span>
        </div>


        <div id="instrumentadder">
            <p id="instrumentaddparagraph" class="hidden" onkeypress="return event.keyCode != 13;">
                <span><input type="text" id="instrumentname" placeholder="Mistä tämä henkilö voisi puhua"></span>
                <span><input type="button" value="Lisää" onClick="ConfirmInstrAdd();"></span>
            </p>
        </div>


        <div>
            <input type="submit" name= "playeradded" value="Tallenna uusi puhuja">
        </div>

        <input type="text" name="instruments" id="repertoire" class="hidden">

        </form>
    </section>

    <section id="filters">
        <p>
            <label for="instrumentfilter">Suodata aiheen perusteella:</label>
            <input type="text" name="instrumentfilter" id="instrumentfilter" placeholder="hae tästä aihetta" OnKeyUp="CheckPlayerFilter(this,'instruments');">
        </p>
        <p>
            <label for="playerfilter">Suodata puhujan nimen perusteella:</label>
            <input type="text" name="playerfilter" id="playerfilter" placeholder="hae tästä puhujaa" OnKeyUp="CheckPlayerFilter(this, 'playername');">
        </p>
    </section>

</section>

<section id="datalist">

<?php echo FetchPlayers($con,"puhuja"); ?>

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
    <h4>Muokkaa puhujan tietoja</h4>

    <p class="small">(Peru muokkaus sulkemalla ikkuna ruksista)</p>

    <p><a href="javascript:void(0);" onClick="RemovePlayer()"; class='deletelink'>Poista tämä puhuja</a></p>

    <?php echo PlayerEditInfo("edit"); ?>

    <h4>Aiheet</h4>

    <p>Poista aihe klikkaamalla sitä
    <input type="text" name="playerdeleted" value="false" class="hidden" id="playerdeleted">
    </p>

    <div id="addedinstruments_edit">
        <div></div>
    </div>


    <div>
        <span>
            <a href="javascript:void(0);" onClick="EditInstrument();">Lisää aihe</a>
        </span>
    </div>

    <div id="instrumentadder_edit">
        <p id="instrumentaddparagraph_edit" class="hidden" onkeypress="return event.keyCode != 13;">
            <span><input type="text" id="instrumentname_edit" placeholder="Mistä aiheesta voi puhua?" OnKeyUp="Auto"></span>
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

<script>
var instrspans = document.getElementsByClassName("listedinstrument");
var instrlist = [];
for(var i=0;i<instrspans.length;i++){
    instrlist.push(instrspans[i].textContent);
}

$("#instrumentname_edit").autocomplete({ source: instrlist });
$("#instrumentname").autocomplete({ source: instrlist });

</script>

</body>

</html>
<?php

} #Login
?>
