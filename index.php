<html lang="fi">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="styles/default.css">
<title>Majakkaportaali 0.1</title>
</head>
<?php
require('phputils/essential.php');
//session_start();
if (!isset($_GET["messuid"]) OR !isset($_GET))
    $messulist =  CreateMessulist();
elseif(isset($_GET["messuid"])){
    $h2 = new DomEl("h2","Majakkamessu " . $_GET["pvm"]);
    $h3 = new DomEl("h3", $_GET["teema"]);
    $messulist =  MessuDetails($_GET["messuid"]);
    }
?>

<body>
<?php

if(isset($h2)){
    echo $h2->Show();
    echo $h3->Show();
}

echo $messulist; 

?>
</body>

<script src="scripts/essential.js"></script>
<script>
//Add listeners
var messurows = document.getElementsByClassName('messurow');
for(var row_idx = 0; row_idx < messurows.length;row_idx++){
    var messurow = messurows[row_idx];
    messurow.addEventListener('click',SelectMessu,false);
}

</script>
</html>
