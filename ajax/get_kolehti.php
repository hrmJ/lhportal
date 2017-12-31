<?php
//Päivittää tietokantaan kolehtikohteen tai kerätyn kolehdin määrän

require('../phputils/dbutils.php');
$con = new DbCon();
if(isset($_GET["goal"])){
    $output = $con->select('kolehtitavoitteet',Array('tavoitemaara'),Array(Array("tavoite","=",$_GET["goal"]),Array("kohde","=",$_GET["kohde"])))->fetchColumn();
    if($output === false){
        $output = 0;
    }
}
else{
    $tavoitteet = $con->select('kolehtitavoitteet',Array('tavoite'),Array(Array("kohde","=",$_GET["kohde"])),"distinct")->fetchAll();
    $output = Array();
    foreach($tavoitteet as $this_arr){
        $tab = Array("tavoite"=>$this_arr["tavoite"]);
        $amounts = $con->select('messut',Array('kolehtia_keratty'),Array(Array("kolehtitavoite","=",$this_arr["tavoite"]),Array("kolehtikohde","=",$_GET["kohde"])),"distinct")->fetchAll();
        $total = 0;
        foreach($amounts as $amount){
            $total += $amount["kolehtia_keratty"];
        }
        $tab["amount"] = $total;
        $output[] = $tab;
    }
}
echo json_encode($output);

?>
