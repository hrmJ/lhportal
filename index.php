<?php
session_start();
require('phputils/essential.php');
AddHeader();
UpdateMessudata();


$url = SaveGetParams();
if (!isset($_GET["messuid"]) OR !isset($_GET)){
    if(isset($_GET["vastuu"])){
        $messulist =  CreateMessulist($_GET["vastuu"]);
    }
    else{
        $messulist =  CreateMessulist();
    }
    $vastuulist =  CreateVastuuList();
}
elseif(isset($_GET["messuid"])){
    $h2 = new DomEl("h2","Majakkamessu " . $_GET["pvm"]);
    $h3 = new DomEl("h3", $_GET["teema"]);
    $messulist =  MessuDetails($_GET["messuid"]);
    }

?>

<body>
<form name='updater' id='updater' method="post" action="<?php echo $url;?>">
<?php
if (sizeof($_GET)>0){
    //Jos muu kuin alkunäkymä
?>
<a href="index.php">Alkuun</a>
<?php
}
if (!isset($_GET["messuid"]) OR !isset($_GET)){
?>
<div>

<span>Tarkastele vastuun perusteella:</span>
<?php echo $vastuulist; ?>

</div>
<?php
}
?>

<?php

if(isset($h2)){
    echo $h2->Show();
    echo $h3->Show();
}

echo $messulist; 

if (isset($_GET))
    echo '<input type="submit" name="updated" value="Tallenna">';
?>
</form>

<section id="comments">
<form name='commentform' id='commentform' action=''>
    <a href='#' onClick='AddComment();'>Lisää infoasia/kommentti/kysymys/yms.</a>
</form>

<?php
LoadComments();
?>
</section>

</body>

<script src="scripts/pohjat.js"></script>
<script src="scripts/essential.js"></script>
<script>
//Add listeners
var messurows = document.getElementsByClassName('messurow');
for(var row_idx = 0; row_idx < messurows.length;row_idx++){
    var messurow = messurows[row_idx];
    messurow.addEventListener('click',SelectMessu,false);
}

var editables = document.getElementsByClassName('editable');
for(var row_idx = 0; row_idx < editables.length;row_idx++){
    var editable = editables[row_idx];
    editable.addEventListener('click',edit,false);
}

var vastuulist = document.getElementById('vastuulist');
if (vastuulist){
    vastuulist.addEventListener('change',SelectVastuu,false);
}

</script>
</html>
