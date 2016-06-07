<html lang="fi">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="styles/default.css">
<title>Majakkaportaali 0.1</title>
</head>
<?php
require('phputils/essential.php');
//session_start();
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
<?php
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

?>
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
vastuulist.addEventListener('change',SelectVastuu,false);

</script>
</html>
