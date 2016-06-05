<?php
require('htmlutils.php');
require('dbutils.php');

function CreateMessulist(){
    $date = date('Y-m-d');
    $con = new DbCon();
    $result = $con->select("messut",Array("pvm","teema","id"),Array(Array("pvm",">=",$date),Array("pvm","<=","2017-01-01")))->fetchAll();

    $ul = new DomEl("ul");
    foreach($result as $row){
        $litext = $row["pvm"];
        $li = new DomEl('li',$litext,$ul);
        $li->AddAttribute('id',"messu_" . $row["id"]);
        $li->AddAttribute('teema',$row["teema"]);
        $li->AddAttribute('pvm',$row["pvm"]);
        $li->AddAttribute('class',"messurow");
        }
    return $ul->Show();
}

function MessuDetails($id){
    $con = new DbCon();
    $result = $con->select("vastuut",Array("vastuu","vastuullinen","id"),Array(Array("messu_id","=",$id)))->fetchAll();
    $table = new DomEl("table");
    $head = new DomEl('thead','',$table);
    $tbody = new DomEl('tbody','',$table);
    foreach($result as $row){
        $tr = new DomEl('tr','',$tbody);
        $td = new DomEl('td',$row["vastuu"],$tr);
        $td = new DomEl('td',$row["vastuullinen"],$tr);
        }
    return $table->Show();
}

?>
