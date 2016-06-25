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
        if (!empty($vastuu)) {
            //Jos halutaan filtteröidä vastuun ukaan
            $vastuures = $con->select("vastuut",Array("vastuullinen"),Array(Array("messu_id","=",$row["id"]),Array("vastuu","=",$vastuu)))->fetchAll();
            $tr = $table->AddRow(Array($row["pvm"],$vastuures[0]["vastuullinen"],""));
            $tr->cells[0]->AddAttribute("class","left");
            $tr->cells[1]->AddAttribute("class","editable right");
            $tr->cells[1]->AddAttribute("name",$row["pvm"]);
            AddHidden($tr->cells[2],"id_" . $row["pvm"], $row["id"]);
            if (empty($vastuures[0]["vastuullinen"]))
                $input = AttachEditable($tr->cells[1], $row["pvm"]);
        }
        else{
            //Jos katsellaan vain listaa ilman filtteriä
            $tr = $table->AddRow(Array($row["pvm"],""));
        }
        $tr->cells[0]->AddAttribute('id',"messu_" . $row["id"]);
        $tr->cells[0]->AddAttribute('teema',$row["teema"]);
        $tr->cells[0]->AddAttribute('pvm',$row["pvm"]);
        $tr->cells[0]->AddAttribute("class","messurow");
    }
    AddHidden($table->element,"vastuu",$vastuu);
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
    $section = new DomEl("section","");
    $section->AddAttribute("id","contentlist");
    $table = new HtmlTable($section);
    foreach($result as $row){
        $tr = $table->AddRow(Array($row["vastuu"],$row["vastuullinen"]));
        if (empty($row["vastuullinen"])){
            $input = AttachEditable($tr->cells[1],$row["vastuu"]);
            $tr->cells[1]->AddAttribute("class","right");
        }
        else{
            $tr->cells[1]->AddAttribute("class","editable right");
            $tr->cells[1]->AddAttribute("name",$row["vastuu"]);
        }
        $tr->cells[0]->AddAttribute("class","left");
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

function UpdateMessudata($con){
    //Jos käyttäjä on päivittänyt jotain tietoja messusta tai messuista, prosessoi dataa:
    if(isset($_POST)){
            if (array_key_exists("messu_id",$_POST)){
                #1. Päivitykset messukohtaisesti, kaikki roolit mahdollisia
                    $updatables = Array("Saarna","Pyhis","Klubi","Saarnateksti","Liturgi","Juonto","Bändi","Sanailija");
                    foreach ($updatables as $vastuu){
                        if(array_key_exists($vastuu,$_POST)){
                            if (!empty($_POST[$vastuu])){
                                $con->update("vastuut",
                                    Array("vastuullinen" =>$_POST[$vastuu]),
                                    Array(Array("messu_id","=",intval($_POST["messu_id"])), Array("vastuu","=",$vastuu)));
                            }
                        }
                    }
            }
            elseif (array_key_exists("updated",$_POST)){
                #2. Päivitykset roolikohtaisesti, kaikki messut mahdollisia
                $updatables = Array();
                foreach($_POST as $key => $value){
                    if(strpos($key, "id_") !== FALSE){
                        #Tallenna ID taulukkoon pvm:n kanssa
                        $pvm = substr($key,3,strlen($key));
                        if (!empty($_POST[$pvm])) {
                            $con->update("vastuut",
                                Array("vastuullinen" =>$_POST[$pvm]),
                                Array(Array("messu_id","=",intval($value)), Array("vastuu","=",$_POST["vastuu"])));
                        }
                    }
                }
            }
            elseif (array_key_exists("newcomment_text",$_POST)){
                #3. Jos käyttäjä on lisännyt kommentin, lataa se tietokantaan
                date_default_timezone_set('Europe/Helsinki');
                $date = date('Y-m-d H:i:s');
                $con->insert("comments", Array("messu_id"=>$_POST["messu_id_comments"],"comment_time"=>$date,"content"=>$_POST["newcomment_text"],"commentator"=>""));
            }
    }
}


function LoadComments(){
    //TODO: Use only one open connection, pass it on as argument
    $con = new DbCon();
    $con->Connect();
    if (array_key_exists("messuid",$_GET)){
        $messuid = $_GET["messuid"];
        $comments = $con->select("comments",Array("content","commentator","id","comment_time"),Array(Array("messu_id","=",intval($messuid))),'','ORDER BY comment_time DESC')->fetchAll();
        foreach ($comments as $comment){
            $thiscomment = new Comment($comment);
            echo $thiscomment->container->Show();
        }
    }
}

?>
