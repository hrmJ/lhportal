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
        $fromsongs = True;
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Nothing+You+Could+Do|Quicksand" rel="stylesheet"> 
  <link rel="stylesheet" href="styles/updated.css">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<title>Laulujen syöttö</title>
</head>
<body>

<?php
$kausi = SetSeason($con);
$vastuulist = CreateNavi(False, $url, True);
require('menu.php');
?>

<section id='songlistsection'>
    <div id='songlistcontainer'>
        <input type='text' value='' id='pickedlistsong' class='hidden'>
        <div id='searchtools' class='searchdiv'>
            <p>Tässä näkymässä voit selailla olemassaolevien laulujen listaa tarkemmin. 
            Voit hakea laulun nimen perusteella kirjoittamalla alla olevaan tekstikenttään.
            Klikkaa laulun nimeä listalta, niin voit asettaa sen alkulauluksi,
            päivän lauluksi jne. Voit myös tutkia ja muokata laulusta tallennettuja sanoja.
            </p>
            <input type="text" id="songfilterinput" OnKeyUp="CheckFilter();">
        </div>
        <div id='songlistdivcont' class='searchdiv'>
            <div id='songcontrols'>
                <div id='listeditwords'></div>
                <div id='songpanel'></div>
            </div>
            <div id='songlistdiv'></div>
        </div>
    </div>
</section>

<form id="sform" method="post" action="<?php echo $url; ?>"> <?php
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

<article class='widetables brightlink' id='maincontainer'>
        <h2 id="mainheader">Majakkamessun laulut</h2>

            <?php if(!isset($_POST["sbut"])){ ?>
            <div class="instr">Moi! Niin kuin huomaat, messulaulujen ilmoittaminen on vähän muuttunut, mutta älä pelästy. Luulen ihan
               oikeasti, että helpompaan suuntaan. <a id='infolink' href='javascript:void(0);' onClick='MoreSongInfo();' >Lue pikaohjeet</a>

                <div class='instr' id='help'>
                    <ul>
                        <li> Vasemmalla ei aluksi näy listaa lauluista, mutta
                            <em>kun alat kirjoittaa jotakin laulun nimeä esimerkiksi kohdan "Alkulaulu"
                            viereiseen kenttään, ruudulle ilmestyy lista kaikista olemassaolevista
                            lauluista, joissa näpyttelemäsi kirjaimet / sana esiintyy.</em> (Jos haluat tutkia olemassaolevien laulujen listaa perinteisemmin, klikkaa ylhäältä "Selaa lauluja")
                        <li> Jos etsimäsi laulu on listassa, klikkaa sitä.
                        <li> Voit katsella valitsemasi laulun sanoja klikkaamalla laulun viereistä "Katso sanoja" -linkkejä
                        <li> Jos kyseisestä laulusta ei ole sanoja, linkissä lukee "lisää sanat". Teksti päivittyy, kun klikkaat jotain toista tekstikenttää.
                        <li> Klikkaa siinä tapauksessa Lisää sanat -linkkiä, jolloin näyttöön ilmestyy uusi ikkuna
                        <li> Kirjoita puuttuvat sanat suoraan ilmestyvään tekstikenttään ja paina "Tallenna"
                        <li> Tiedot eivät enää lähde sähköpostitse eteenpäin, vaan tallentuvat Majakkaportaalin tietokantaan.
                        <li> Kysy heti jos askarruttaa (vaikka juho.harme*at*gmail.com tai katso puhelinnumero Majakan vastuunkantajan abc:sta)!
                    </ul>
                    <p><a  href='javascript:void(0);' onClick='MoreSongInfo();' >Piilota ohjeet</a></p>
                </div>
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
                   ystävällisesti alla olevaan tekstilaatikkoon laulun sanat:
                <p><textarea id="editarea">- Ei laulun nimeä, aloita suoraan 1. säkeistöstä
- Erota säkeistöt, kertosäe ym. toisistaan yhdellä tyhjällä rivivälillä.
- Paina lopuksi "Tallenna tiedot" -painiketta
- Poistu tallentamatta painamalla "Peruuta"
                </textarea></p>
                <p><span><input type="button" name="editsub" id="editsub" onClick="submitedit();" value="Tallenna tiedot"></span><span><input type="button" name="canceledit" id="canceledit" onClick="RemoveWordView();" value="Peruuta"></span></p>
            </div>

            <p id='selectcontainer'>

            <span>
                <?php 
                    $pickedid = GetDateList($con); 
                    $urlparams = GetMessuParams($con, $pickedid);
                ?>
            </span>
            <p class='topper'>
                <a href='index.php?<?php echo $urlparams;?>'>Näytä tämä messu perusnäkymässä</a>
            </p>

            <input type='text' class='hidden' name='pickedid' value='<?php echo $pickedid; ?>'>
            </p>

            <h3 id='firstheader'>Yksittäiset laulut</h3>


            <?php
            $songlist =  SongListForInsertion($pickedid, $con, Array("Alkulaulu","Päivän laulu","Loppulaulu")); 
            echo $songlist;
            ?>
            <h3>Ylistyslaulut</h3>
            <?php
                $ylistys_songlist =  WsSongList($con, $pickedid, "Ylistyslaulu"); 
                echo $ylistys_songlist;
            ?>
            <p ><input type="button" title='Lisää ylistyslaulu'  class='plusminus' onClick='AddWsSong("Ylistyslaulu");' value="+"><input type="button" title='Poista viimeinen ylistyslaulu'  onClick='RemoveWsSong("Ylistyslaulu");' value="-"> </p>
            <h3>Ehtoollisen aikana laulettavat</h3>
            <?php
                $eht_songlist =  WsSongList($con, $pickedid, "Ehtoollislaulu"); 
                echo $eht_songlist;
            ?>
            <p ><input type="button" class='plusminus' title='Lisää ehtoollislaulu' onClick='AddWsSong("Ehtoollislaulu");' value="+"><input type="button" title='Poista viimeinen ehtoollislaulu'   onClick='RemoveWsSong("Ehtoollislaulu");' value="-"></p>

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
            <?php 
            $infostring = "Lisää tähän miksaajalle tiedoksi, mitä soitimia teillä on ja keitä soittajia.  Esimerkiksi: kitara (Ville V.), cajon (Hessu H.). Samoin, jos on jotain toiveita äänitekniikan suhteen, niin voit ilmoittaa niistä tässä. Tai mitä vain muuta viestiä :)";
            $techinfo=FetchTechInfo($pickedid, $con, $infostring);
            if($techinfo==$infostring){
                echo "<textarea class='area' name='techinfo' placeholder='$techinfo' id='techinfobox'></textarea>";
            }
            else{
                echo "<textarea class='area' name='techinfo' value='$techinfo' id='techinfobox'>$techinfo</textarea>";
            }
            ?>
    
            </p>

            <p> <input type="button" name="confirmsub" id="confirmsub" onClick="confirmsubmit();" value="Tallenna tiedot">
                <input type="submit" class="hidden" name="sbut" id="sbut" value="Tallenna tiedot"></p>


            <div id="slidecreator">
                <ul>
                    <li>Valitse tausta</li>
                    <li>Valitse otsikoiden taustaväri</li>
                </ul>
                <p><a href='javascript:void(0);' onClick='CreateSlides(<?php echo $pickedid; ?>);'>Luo diat</a></p>
            </div>

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
    document.getElementById('homeli').addEventListener('click',function(){window.location='index.php';});
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



  $(document).ready( function() {
      //Lisää autocomplete-plugi
        $( ".songeditinput" ).autocomplete({
          source: songnames
        });
      // Dialuontidiv
        //$("<a>Avaa diojen luontivalikko</a>").wrap()$("#slidecreator").
  } );

</script>


<div class='hidden'>
    <form name="esongedit" method="POST" action="<?php echo $url;?>">
        <input name="editedsongid" id="editedsongid">
        <textarea name="edited_existing_text" id="edited_existing_text"></textarea>
        <input type="submit" name="edited_existing" id="edited_existing_button">
    </form>

    <form name="remover" method="POST" action="<?php echo $url;?>">
        <input name="messu_id" id="removed_messuid" value="<?php echo $pickedid ?>">
        <input name="removed_type" id="removed_type">
        <input type="submit" name="removed_ws" id="removed_ws_sub">
    </form>

<!--Tallenna muistiin   -->

<div id="ylistys_memo" class="hidden">
<?php
    $ylistys_songlist = str_replace('Ylistyslaulutable','Ylistyslaulutable_memo',$ylistys_songlist);
    echo $ylistys_songlist;
?>
</div>

<div id="eht_memo" class="hidden">
<?php
    $eht_songlist = str_replace('Ehtoollislaulutable','Ehtoollislaulutable_memo',$eht_songlist);
    echo $eht_songlist;
?>
</div>

</div>

</body>

</html>
<?php

} #Login
?>
