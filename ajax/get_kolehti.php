<?php
//Päivittää tietokantaan kolehtikohteen tai kerätyn kolehdin määrän

require('../phputils/dbutils.php');
$con = new DbCon();
if(isset($_GET["just_amount"])){
    $output = $con->select('messut',Array('kolehtia_keratty'),Array(Array("id","=",$_GET["messu_id"])))->fetchColumn();
}
else if(isset($_GET["goal"])){
    $output = $con->select('kolehtitavoitteet',Array('tavoitemaara'),Array(Array("tavoite","=",$_GET["goal"]),Array("kohde","=",$_GET["kohde"])))->fetchColumn();
    if($output === false){
        $output = 0;
    }
}
else{
    //Katso, mikä kohde ja tavoite tässä messussa
    $current_params = $con->select('messut',Array('kolehtikohde','kolehtitavoite'),Array(Array("id","=",$_GET["messu_id"])))->fetch();
    //Hae kaikki tavoitteet, jotta niitä voidaan vaihdella
    $kohde = ($_GET["kohde"] === "from_db" ? $current_params["kolehtikohde"] : $_GET["kohde"]);
    $tavoitteet = $con->select('kolehtitavoitteet',Array('kohde','tavoite'),Array(Array("kohde","=",$kohde)),"distinct")->fetchAll();
    $output = Array();
    foreach($tavoitteet as $key => $tavoite){
        $tab = Array("kohde"=>$current_params["kolehtikohde"],"tavoite"=>$tavoite["tavoite"],"selected" => false);
        $amounts = $con->select('messut',Array('kolehtia_keratty'),Array(Array("kolehtitavoite","=",$tavoite["tavoite"]),Array("kolehtikohde","=",$_GET["kohde"])),"distinct")->fetchAll();
        $total = 0;
        foreach($amounts as $amount){
            $total += $amount["kolehtia_keratty"];
        }
        $tab["amount"] = $total;
        if($tavoite["tavoite"] === $current_params["kolehtitavoite"] and $tavoite["kohde"] == $current_params["kolehtikohde"]){
            $tab["selected"] = true;
        }
        $output[] = $tab;
    }
}
echo json_encode($output);

?>
