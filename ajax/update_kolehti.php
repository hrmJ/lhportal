<?php
//Päivittää tietokantaan kolehtikohteen tai kerätyn kolehdin määrän

require('../phputils/dbutils.php');
$con = new DbCon();
$update_fields = Array("kolehtikohde" =>$_POST["kohde"], "kolehtia_keratty" =>$_POST["keratty"]);
$con->update("messut",$update_fields, Array(Array("id","=",$_POST["id"])));

?>
