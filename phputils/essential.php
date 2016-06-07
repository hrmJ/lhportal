<?php
require('htmlutils.php');
require('dbutils.php');

function CreateMessulist($vastuu=''){
    $date = date('Y-m-d');
    $con = new DbCon();
    $result = $con->select("messut",Array("pvm","teema","id"),Array(Array("pvm",">=",$date),Array("pvm","<=","2017-01-01")))->fetchAll();

    $ul = new DomEl("ul");
    foreach($result as $row){
        $litext = $row["pvm"];
        if (!empty($vastuu))
            $litext = "";
        $li = new DomEl('li',$litext,$ul);
        $li->AddAttribute('id',"messu_" . $row["id"]);
        $li->AddAttribute('teema',$row["teema"]);
        $li->AddAttribute('pvm',$row["pvm"]);
        if (!empty($vastuu)){
            $vastuures = $con->select("vastuut",Array("vastuullinen"),Array(Array("messu_id","=",$row["id"]),Array("vastuu","=",$vastuu)))->fetchAll();
            $vastuullinen = $vastuures[0]["vastuullinen"];
            $span1 = new DomEl('span',$row["pvm"],$li);
            $span1->AddAttribute('class',"messurow");
            $span2 = new DomEl('span',$vastuullinen,$li);
            if (empty($vastuullinen)){
                $input = new DomEl('input','',$span2);
                $input->AddAttribute('type',"text");
                $input->AddAttribute('class',"linestyle");
                $input->AddAttribute('id',"test");
            }
            $span2->AddAttribute('class',"editable");
        }
        else
            $li->AddAttribute('class',"messurow");
        }
    return $ul->Show();
}

function CreateVastuuList(){
    $date = date('Y-m-d');
    $con = new DbCon();
    $result = $con->select("vastuut",Array("vastuu"),Array(),"DISTINCT")->fetchAll();

    $select = new DomEl("select");
    $select->AddAttribute('id',"vastuulist");
    $option = new DomEl('option','----',$select);
    foreach($result as $row){
        $litext = $row["vastuu"];
        $option = new DomEl('option',$litext,$select);
        }
    return $select->Show();
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
