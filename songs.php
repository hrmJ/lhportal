<?php
session_start();
if (isset($_GET["logout"])){
    session_unset();
    session_destroy();
}
require('phputils/essential.php');
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
        require('login.php');
    }
}
if (isset($_SESSION['user_id'])){
#JOS kirjauduttu onnistuneesti
$con = new DbCon();

?>

<html lang="fi">
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
  <link rel="stylesheet" href="styles/default.css">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<title>Messujen syöttö järjestelmään</title>
</head>
<body>

<form id="sform" method="post" action="<?php echo $url; ?>">
<?php
UpdateSongData($con);
?>


<div id="songnames">
<?php

FetchSongNames($con);
$url = SaveGetParams();
?>
</div>

<div id="wordview">

</div>

<article id='maincontainer'>
        <h2>Majakkamessun laulut</h2>

            <?php if(!isset($_POST["sbut"])){ ?>
            <div class="instr">Moi! Niin kuin huomaat, messulaulujen ilmoittaminen on vähän muuttunut, mutta älä pelästy. Luulen ihan
               oikeasti, että helpompaan suuntaan. Pari oleellista pointtia tähän liittyen:

                <ol>
                    <li> Vasemmalla ei aluksi näy listaa lauluista, mutta
                        <em>kun alat kirjoittaa jotakin laulun nimeä esimerkiksi kohdan "Alkulaulu"
                        viereiseen kenttään, ruudulle ilmestyy lista kaikista olemassaolevista
                        lauluista, joissa näpyttelemäsi kirjaimet / sana esiintyy.</em>
                    <li> Jos etsimäsi laulu on listassa, klikkaa sitä.
                    <li> Voit katsella valitsemasi laulun sanoja klikkaamalla laulun viereistä "Katso sanoja" -linkkejä
                    <li> Jos kyseisestä laulusta ei ole sanoja, linkissä lukee "lisää sanat"
                    <li> Klikkaa siinä tapauksessa Lisää sanat -linkkiä, jolloin näyttöön ilmestyy uusi ikkuna
                    <li> Kirjoita puuttuvat sanat suoraan ilmestyvään tekstikenttään ja paina "Tallenna"
                    <li> Tiedot eivät enää lähde sähköpostitse eteenpäin, vaan tallentuvat Majakkaportaalin tietokantaan.
                    <li> Kysy heti kun askarruttaa (vaikka juho.harme*at*gmail.com)!
                </ol>

             </div>

            <?php } 
            else{
            ?>

            <div class="instr">Hienoa! Tallensit uusia tietoja. Tällä hetkellä
                    tämän messun lauluista on tallennettuna seuraavanlaiset faktat. Voit edelleen muokata
                    lauluja, jos haluat. Muista tallentaa tiedot alhaalla olevalla painikkeella.

             </div>
            <?php }?>

            <div id="editor">
                <h3 id="editedtitle"></h3>
                <p>Tätä laulua ei ole vielä tietokannassa (ainakaan tämännimisenä). Lisäisitkö 
                   ystävällisesti alla olevaan tekstilaatikkoon laulun sanat, niin että: </p>
                <ol>
                    <li> Älä kirjoita enää tekstikenttään laulun nimeä, vaan aloita suoraan 1. säkeistöstä
                    <li> Erota säkeistöt, kertosäe ym. toisistaan yhdellä tyhjällä rivivälillä.
                    <li> Paina lopuksi "Tallenna tiedot" -painiketta
                    <li> Poistu tallentamatta painamalla "Peruuta"
                </ol>
                <p><textarea id="editarea">asldkjasd</textarea></p>
                <p><span><input type="button" name="editsub" id="editsub" onClick="submitedit();" value="Tallenna tiedot"></span><span><input type="button" name="canceledit" id="canceledit" onClick="RemoveWordView();" value="Peruuta"></span></p>
            </div>

            <p>
            <?php 
                $pickedid = GetDateList($con); 
                #Tallennetaan valittu id
                echo "<input type='text' class='hidden' name='pickedid' value='$pickedid'>";
            ?>
            </p>

            <h3>Yksittäiset laulut</h3>


            <?php
            $songlist =  SongListForInsertion($pickedid, $con, Array("Alkulaulu","Päivän laulu","Loppulaulu")); 
            echo $songlist;
            ?>
            <h3>Ylistyslaulut</h3>
            <?php
                $wssonglist =  WsSongList($con, $pickedid, "Ylistyslaulu"); 
                echo $wssonglist;
            ?>
            <p><input type="button" onClick='AddWsSong("Ylistyslaulu");' value="+"></p>
            <h3>Ehtoollisen aikana laulettavat</h3>
            <?php
                $wssonglist =  WsSongList($con, $pickedid, "Ehtoollislaulu"); 
                echo $wssonglist;
            ?>
            <p><input type="button" onClick='AddWsSong("Ehtoollislaulu");' value="+"></p>

            <h3>Liturgiset</h3>

            <table id="songtable">
            <thead></thead>
            <tbody>
                <tr><td class="left">Jumalan karitsa</td><td class="right"> <?php $jkval = Liturgiset($con, "Jumalan karitsa", $pickedid); ?></td><td class="lyricslinkcell"><a id="jklink" class="lyricslink">Katso sanoja</a></td></tr>
                <tr><td class="left">Pyhä-hymni</td><td class="right"> <?php $pyhval = Liturgiset($con, "Pyhä-hymni", $pickedid); ?></td><td class="lyricslinkcell"><a id="pyhalink" class="lyricslink">Katso sanoja</a></td></tr>
            </tbody>
            </table>


            <h3>Tiedot tekniikalle</h3>
            <p>
            <?php $techinfo=FetchTechInfo($pickedid, $con);?>
            <textarea class='area' name="techinfo" value="<?php echo $techinfo;?>" id="techinfobox"><?php echo $techinfo;?></textarea>
            </p>

            <p><input type="submit" name="sbut" id="sbut" value="Tallenna tiedot"></p>


</article>


<input class='hidden' name="jumalan_karitsa" value="<?php echo $jkval;?>" id="jumalan_karitsa">
<input class='hidden' name="pyhä-hymni" value="<?php echo $pyhval;?>" id="pyhä-hymni">
<input class='hidden' name="edited_song_name" value="none" id="edited_song_name">
<textarea class='hidden' name="editedsong_hidden" value="none" id="editedsong_hidden"></textarea>

</form>

<script src="scripts/essential.js"></script>
<script>
    //A GLOBAL(!) variable for the songnames
    var songnames = [];
    var snamespans = document.getElementsByClassName('songtitleentry');
    for(spidx in snamespans){
        var thisspan = snamespans[spidx];
        if (thisspan.innerText !== undefined){
            songnames.push(thisspan.innerText);
        }
    }

    //Add listeners
    var pvmlist = document.getElementById('pvmlist');
    pvmlist.addEventListener('change',ChangeSongPvm,false);

    var lyricslinks = document.getElementsByClassName('lyricslink');
    for(var idx = 0; idx < lyricslinks.length;idx++){
        var link = lyricslinks[idx];
        link.addEventListener('click',ShowWords,false);
        link.parentNode.addEventListener('click',ShowWords,false);
    }

    //TODO lisää myös dyn.luotuihin
    var lyricslinks = document.getElementsByClassName('right');
    for(var idx = 0; idx < lyricslinks.length;idx++){
        var link = lyricslinks[idx];
        link.addEventListener('focusout',UpdateLyrics,false);
    }



  $( function() {
    $( ".songeditinput" ).autocomplete({
      source: songnames
    });
  } );

</script>


</body>

</html>
<?php

} #Login
?>
