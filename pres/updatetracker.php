<?php
require('../phputils/essential.php');
$con = new DbCon();
$date = date('Y-m-d');
#Valitse messu, joka on tänään tai seuraava lähin päivä
$messuid = $con->select("messut",Array("id"),Array(Array("pvm",">=",$date)))->fetchColumn(0);
#Ensin aseta kaikki epäaktiivisiksi
$con->update("messukulku", Array("iscurrent" => 0), Array(Array("messu_id","=",$messuid)));
#Aktivoi tunnisteen perusteella:
$con->update("messukulku", Array("iscurrent" => 1), Array(Array("messu_id","=",$messuid),Array("typeidentifier","=",$_GET["identifier"])));

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

#Remove previous presentation updates
$con->query = $con->connection->prepare("DELETE FROM logs WHERE event = :sid");
$con->query->bindValue(':sid', "presentation update", PDO::PARAM_STR);
$con->Run();

# update time:
$con->insert("logs", Array("event" => "presentation update","time"=>date("Y-m-d H:i:s")));
?>
