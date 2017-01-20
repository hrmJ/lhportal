<?php
require('phputils/essential.php');
$con = new DbCon();
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <link href="https://fonts.googleapis.com/css?family=Nothing+You+Could+Do|Quicksand" rel="stylesheet">
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="pres/tyylit2.css">
    <title>Majakkamessu</title>
</head>

<body>

<?php


$date = date('Y-m-d');
#Valitse messu, joka on tänään tai seuraava lähin päivä
$messuid = $con->select("messut",Array("id"),Array(Array("pvm",">=",$date)))->fetchColumn(0);
$items = $con->select("messukulku",Array('entry','entrytype','iscurrent'), Array(Array("messu_id","=",$messuid)),"","")->fetchAll();

$updated = $con->select("logs",Array("time"),Array(Array("event","=","presentation update")))->fetchColumn(0);
$updated = date("H:i:s",strtotime($updated));
?>

<section id="trackersection">

<p>Messun tilanne päivitetty viimeksi:  <?php echo $updated;?></p>
<p>Tarkistit viimeksi:  <?php echo date("H:i:s");?></p>

<?php

$mainul = new DomEl('ul','',Null,'hlnavsection');

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
foreach($items as $item){
    if($item["entrytype"]=="item"){
       $mainli = new DomEl('li',$item["entry"],$mainul,'unhlsection');
       $ul = new DomEl('ul','',$mainli,'unhlsection');
    }
    else{
        $li = new DomEl('li',$item["entry"],$ul,'unhlsection');
        if($item["iscurrent"]>0){
            $li->el->setAttribute('class','subsectionnavhl');
            $ul->el->setAttribute('class','sectionnavhl');
            $mainli->el->setAttribute('class','sectionnavhl');
        }
    }
   # $li = new DomEl('li','',$ul);
   # $li->AddAttribute('id','settings');
}

echo $mainul->Show();

?>
</section>

<!--
<li class="unhlsection" sectionidx="0" secitemidx="0">Johdanto<ul><li class="unhlsection" sectionidx="0" secitemidx="0">Krediitit1</li><li class="unhlsection" sectionidx="0" secitemidx="1">Alkulaulu: Aamusta iltaan</li><li class="unhlsection" sectionidx="0" secitemidx="2">Alkusanat</li><li class="unhlsection" sectionidx="0" secitemidx="3">Seurakuntalaisen sana</li><li class="unhlsection" sectionidx="0" secitemidx="4">Pyhisinfo</li></ul></li><li class="unhlsection" sectionidx="1" secitemidx="0">Sana<ul><li class="unhlsection" sectionidx="1" secitemidx="0">Päivän laulu: Anna lamppuuni öljyä herra</li><li class="unhlsection" sectionidx="1" secitemidx="1">Evankeliumi</li><li class="unhlsection" sectionidx="1" secitemidx="2">Saarna</li><li class="unhlsection" sectionidx="1" secitemidx="3">Synnintunnustus</li><li class="unhlsection" sectionidx="1" secitemidx="4">Uskontunnustus: </li></ul></li><li class="unhlsection" sectionidx="2" secitemidx="0">Ylistys ja rukous<ul><li class="unhlsection" sectionidx="2" secitemidx="0">Laulu: Anna anteeksi</li><li class="unhlsection" sectionidx="2" secitemidx="1">rukousinfo</li><li class="unhlsection" sectionidx="2" secitemidx="2">Laulu: Anna, oi herra</li><li class="unhlsection" sectionidx="2" secitemidx="3">rukousinfo</li><li class="unhlsection" sectionidx="2" secitemidx="4">Laulu: Isä meidän</li><li class="unhlsection" sectionidx="2" secitemidx="5">Esirukous</li></ul></li><li class="unhlsection" sectionidx="3" secitemidx="0">Ehtoollisen asetus<ul><li class="unhlsection" sectionidx="3" secitemidx="0">Pyhä: </li><li class="unhlsection" sectionidx="3" secitemidx="1">Ehtoollisrukous</li><li class="unhlsection" sectionidx="3" secitemidx="2">Isä meidän: </li><li class="unhlsection" sectionidx="3" secitemidx="3">Ehtoollisinfo</li><li class="unhlsection" sectionidx="3" secitemidx="4">Jumalan karitsa: </li></ul></li><li class="unhlsection" sectionidx="4" secitemidx="0">Ehtoollisen vietto<ul><li class="unhlsection" sectionidx="4" secitemidx="0">Laulu: Astun pyhyyteesi, herrani (siionin kannel nro 114)</li><li class="unhlsection" sectionidx="4" secitemidx="1">Laulu: Liekki ja leimu</li><li class="unhlsection" sectionidx="4" secitemidx="2">Laulu: Mitä rakkaus on?</li><li class="unhlsection" sectionidx="4" secitemidx="3">Laulu: Olethan lähellä</li></ul></li><li class="unhlsection" sectionidx="5" secitemidx="0">Siunaus ja lähettäminen<ul><li class="unhlsection" sectionidx="5" secitemidx="0">Herran siunaus</li><li class="unhlsection" sectionidx="5" secitemidx="1">Loppusanat</li><li class="unhlsection" sectionidx="5" secitemidx="2">Loppulaulu: Anna lamppuuni öljyä herra</li></ul></li></ul>
-->


</body>


</html>
