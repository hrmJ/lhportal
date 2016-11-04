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

    if(isset($_POST["newservices"])){
        #Jos äsken syötetty uusia messuja:
        InsertServices($con);
        echo "<script>window.alert('Uudet messut syötetty onnistuneesti');</script>";
    }


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

<?php
UpdateSongData($con);
?>


<div id="songnames">
<?php

FetchSongNames($con);
$url = SaveGetParams();
?>
</div>

<article id='maincontainer'>
        <h2>Majakkamessun laulut</h2>
        <form id="sform" method="post" action="<?php echo $url; ?>">

            <div id="editor">
                <p><textarea id="editarea" name="editedsong"></textarea></p>
                <p><input type="submit" name="editsub" id="editsub" onClick="submitedit();" value="Tallenna tiedot"></p>
                <input class='hidden' name="edited_song_name" value="none" id="edited_song_name">
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
                <tr><td class="left">Jumalan karitsa</td><td class="right"> <?php Liturgiset($con, "Jumalan karitsa"); ?></td></tr>
                <tr><td class="left">Pyhä-hymni</td><td class="right"> <?php Liturgiset($con, "Pyhä-hymni"); ?></td></tr>
            </tbody>
            </table>

            <p><input type="submit" name="sbut" id="sbut" value="Tallenna tiedot"></p>


        </form>
</article>

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
