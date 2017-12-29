<?php
//Päivittää tietokantaan kolehtikohteen tai kerätyn kolehdin määrän

require('../phputils/dbutils.php');
$con = new DbCon();

if($_GET["action"]=="typechange"){
 $update_fields = Array("kolehtikohde" =>$_GET["kohde"]);
}
else{
 $update_fields = Array("kolehtia_keratty" =>$_GET["keratty"]);
}

$con->update("messut",$update_fields, Array(Array("id","=",$_GET["id"])));

?>
