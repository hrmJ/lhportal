<?php
//Hakee tietokannasta kolehtikohteen ja muut kolehtiin liittyvät tiedot

require('../phputils/dbutils.php');
$con = new DbCon();
if(isset($_GET["just_amount"])){
    $output = $con->select('messut',Array('kolehtia_keratty'),Array(Array("id","=",$_GET["messu_id"])))->fetchColumn();
}
else if(isset($_GET["goal"])){
    $output = $con->select('kolehtitavoitteet',Array('tavoitemaara','kuvaus'),Array(Array("tavoite","=",$_GET["goal"]),Array("kohde","=",$_GET["kohde"])))->fetch();
    if($output === false){
        $output = 0;
    }
}
else{
    //Katso, mikä kohde ja tavoite tässä messussa
    $current_params = $con->select('messut',Array('kolehtikohde','kolehtitavoite'),Array(Array("id","=",$_GET["messu_id"])))->fetch();
    //Hae kaikki tavoitteet, jotta niitä voidaan vaihdella
    $kohde = ($_GET["kohde"] === "from_db" ? $current_params["kolehtikohde"] : $_GET["kohde"]);
    if(!$kohde){
        $kohde = $_GET["fallback"];
    }
    $tavoitteet = $con->select('kolehtitavoitteet',Array('kohde','tavoite','kuvaus'),Array(Array("kohde","=",$kohde)),"distinct")->fetchAll();
    $output = Array();
    foreach($tavoitteet as $key => $tavoite){
        $tab = Array("kohde"=>$kohde,"tavoite"=>$tavoite["tavoite"],"selected" => false, "kuvaus" => $tavoite["kuvaus"]);
        $tab["amount"]  = $con->select('messut',Array('SUM(kolehtia_keratty)'),
            Array(Array("kolehtikohde","=",$tavoite["kohde"]),
            Array("kolehtitavoite","=",$tavoite["tavoite"])
        ))->fetchColumn();
        if(!$tab["amount"]){
            $tab["amount"] = 0;
        }
        if($tavoite["tavoite"] === $current_params["kolehtitavoite"] and $tavoite["kohde"] == $current_params["kolehtikohde"]){
            $tab["selected"] = true;
        }
        $output[] = $tab;
    }
}
echo json_encode($output);

?>
