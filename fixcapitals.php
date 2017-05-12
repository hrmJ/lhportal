<?php
require("phputils/dbutils.php");
$con = new DbCon();
$allverses = $con->select("verses",Array("content","id"),Array())->fetchAll();
foreach($allverses as $verse){
    if(strpos($verse["content"],"jeesus") > -1 or strpos($verse["content"],"jumala") > -1){
       $verse["content"] = str_replace("jeesus","Jeesus",$verse["content"]);
       $verse["content"] = str_replace("jumala","Jumala",$verse["content"]);
       $con->update("verses",Array("content"=>$verse["content"]),Array(Array("id","=",$verse["id"])));;
    }
}
print("<p>All done.</p>");

?>

