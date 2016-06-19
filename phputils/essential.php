<?php
require('htmlutils.php');
require('dbutils.php');

function AddHeader(){

echo '<html lang="fi">
     <head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
     <link rel="stylesheet" href="styles/default.css">
     <title>Majakkaportaali 0.1</title>
     </head>';

}

function AttachEditable($parent, $name){
    $input = new DomEl('input','',$parent);
    $input->AddAttribute('type',"text");
    $input->AddAttribute('class',"linestyle");
    $input->AddAttribute('id',$name);
    $input->AddAttribute('name',$name);
    return $input;
}

function AddHidden($parent, $name, $value){
    $input = new DomEl('input',$value,$parent);
    $input->AddAttribute('class',"hidden");
    $input->AddAttribute('id',$name);
    $input->AddAttribute('name',$name);
    $input->AddAttribute('value',$value);
    return $input;
}

function CreateMessulist($vastuu=''){
    $date = date('Y-m-d');
    $con = new DbCon();
    $result = $con->select("messut",Array("pvm","teema","id"),Array(Array("pvm",">=",$date),Array("pvm","<=","2017-01-01")))->fetchAll();
    $table = new HtmlTable();
    foreach($result as $row){
        $litext = $row["pvm"];
        if (!empty($vastuu)) {
            $vastuures = $con->select("vastuut",Array("vastuullinen"),Array(Array("messu_id","=",$row["id"]),Array("vastuu","=",$vastuu)))->fetchAll();
            $tr = $table->AddRow(Array($row["pvm"],$vastuures[0]["vastuullinen"]));
            $tr->cells[1]->AddAttribute("class","editable");
            if (empty($vastuures[0]["vastuullinen"]))
                $input = AttachEditable($tr->cells[1], $row["pvm"]);
        }
        else{
            $tr = $table->AddRow(Array($row["pvm"],""));
        }
        $tr->cells[0]->AddAttribute('id',"messu_" . $row["id"]);
        $tr->cells[0]->AddAttribute("class","messurow");
    }
    return $table->element->Show();
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
    $table = new HtmlTable();
    foreach($result as $row){
        $tr = $table->AddRow(Array($row["vastuu"],$row["vastuullinen"]));
        if (empty($row["vastuullinen"])){
            $input = AttachEditable($tr->cells[1],$row["vastuu"]);
        }
        else{
            $tr->cells[1]->AddAttribute("class","editable");
            $tr->cells[1]->AddAttribute("name",$row["vastuu"]);
        }
    }
    #Tallennetaan myös messuid  (piilotetusti)
    $idfield = AddHidden($table->element,"messu_id",$id);
    return $table->element->Show();
}

function SaveGetParams(){
    $urlparams .= "?";

    if (isset($_GET)){
        //Tallenna parametrit, jotta sama sivu latautuisi myös tallennettaessa tietoja
        foreach($_GET as $paramname => $param){
            if ($urlparams !== "?")
                $urlparams .= "&";
            $urlparams .= "$paramname=$param";
        }
    $url = $_SERVER['PHP_SELF'] . $urlparams ;
    }
    else{
        $url = $_SERVER['PHP_SELF'];
    }
    return $url;
}

?>
