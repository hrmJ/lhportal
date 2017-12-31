<?php
//Päivittää tietokantaan kolehtikohteen tai kerätyn kolehdin määrän

require('../phputils/dbutils.php');
$con = new DbCon();

$update_fields = Array("kolehtitavoite" => $_POST["tavoite"], "kolehtikohde" =>$_POST["kohde"], "kolehtia_keratty" => $_POST["keratty"]);
//Päivitetään kolehtikohde, -tavoite ja kerätty määrä
$con->update("messut",$update_fields, Array(Array("id","=",$_POST["id"])));
$tavoite_id = $con->select("kolehtitavoitteet", Array("id"),Array(Array("tavoite","=",$_POST["tavoite"]),Array("kohde","=",$_POST["kohde"])))->fetchColumn();

if(!$tavoite_id){
    //Jos syötetään uusi tavoitemäärä
    $con->insert("kolehtitavoitteet", Array("tavoite" => $_POST["tavoite"], "kohde" => $_POST["kohde"], "tavoitemaara" => $_POST["total_goal"]));
}
else{
    //Jos muutetaan jo asetettua tavoitemäärää
    $con->update("kolehtitavoitteet", Array("tavoitemaara" => $_POST["total_goal"]) , Array(Array("id","=",$tavoite_id)));
}
?>
