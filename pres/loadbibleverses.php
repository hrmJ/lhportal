<?php
/**
 * Ajax-skripti raamatuntekstien hakemiseksi tietokannasta.
 */
require("database.php");
require("loader.php");

$loader = new BibleLoader($_GET["testament"],"config.ini");
if(isset($_GET["startbook"])){
    //Itse sisältö
    $start = Array( $_GET["startbook"], $_GET["startchapter"], $_GET["startverse"]);
    $end = Array( $_GET["endbook"], $_GET["endchapter"], $_GET["endverse"]);
    $loader->LoadBibleVerses($start,$end);
}
elseif(isset($_GET["verse"])){
    //jakeen sisältö
    $startend = Array( $_GET["book"], $_GET["chapter"], $_GET["verse"]);
    $loader->LoadBibleVerses($startend,$startend);
}
elseif(isset($_GET["chapter"])){
    //jakeiden määrä
    $loader->LoadVerses($_GET["book"], $_GET["chapter"]);
}
elseif(isset($_GET["book"])){
    //lukujen määrä
    $loader->LoadChapters($_GET["book"]);
}
else{
    //Kirjojen nimet
    $loader->LoadBookNames();
}

$loader->OutputData();

?>
