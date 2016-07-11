<?php
require('htmlutils.php');
require('dbutils.php');

function validate_login(){
    //check that pw and usrname are set, are of valid lengths, contain only alphanumeric chars
    $valid = true;
    foreach(func_get_args() as $input){
        if (!isset($input) || strlen($input)>20 || strlen($input)<4 || !ctype_alnum($input) )
            $valid = false;
    }
    return $valid;
}

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

function AddSection($submit=False, $sectionclass=''){
    $section = new DomEl("section","");
    $section->AddAttribute("id","contentlist");
    $section->AddAttribute("class",$sectionclass);

    $form = new DomEl("form","",$section);
    $form->AddAttribute("id","updater");
    $form->AddAttribute("name","contentlist");
    $form->AddAttribute("method","post");
    $form->AddAttribute("action",$url);

    $table = new HtmlTable($form);

    if($submit==True){
        $submit = new DomEl("input","",$form);
        $submit->AddAttribute("type","submit");
        $submit->AddAttribute("name","updated");
        $submit->AddAttribute("value","Tallenna");
    }

    return $table;
}


function SeasonWarning($sname,$change){
    if ($change=='previous'){
        $text = " (ei aikaisempia kausia)";
    }
    else if ($change=='next'){
        $text = " (ei myöhempiä kausia)";
    }

    if(strpos($sname,"kausia)") !== FALSE)
        return $sname;
    else
        return $sname . $text;
}

function CreateMessulist($vastuu=''){
    $date = date('Y-m-d');
    $con = new DbCon();

    #1. KAUDEN valinta
    if(!isset($_SESSION["kausi"])){
        $kausi =  GetSeason($con, $date);
        $_SESSION["kausi"] = $kausi;
    }
    else{
        $kausi = $_SESSION["kausi"];
        if(isset($_GET['kausi'])){
            #Jos halutaan siirtyä tarkastelemaan seuraavaa tai edellistä messukautta
            $change = $_GET['kausi'];
            if($change == 'previous')
                $date = $_SESSION["kausi"]["alkupvm"];
            else if($change == 'next')
                $date = $_SESSION["kausi"]["loppupvm"];
            $newseason =  GetSeason($con, $date, $change);
            #Tarkista, onko edellistä / seuraavaa kautta
            if(isset($newseason))
                $kausi = $newseason;
            else{
                $_SESSION["kausi"]["nimi"] = SeasonWarning($_SESSION["kausi"]["nimi"],$change);
                $kausi = $_SESSION["kausi"];
            }
            $_SESSION["kausi"] = $kausi;
        }
    }

    #2. MESSULISTA
    
    $result = $con->select("messut",Array("pvm","teema","id"),Array(Array("pvm",">=",$kausi["alkupvm"]),Array("pvm","<=",$kausi["loppupvm"])),"","ORDER BY pvm")->fetchAll();

    $submit = False;
    if(!empty($vastuu)){
        $submit=True;
    }
    $table = AddSection($submit,"rightcontent");

    $months = Array();
    $years = Array();
    foreach($result as $row){
        $pvm_list = ParseMonth($row["pvm"]);
        #Erottele kuukaudet taulukossa
        if (!in_array($pvm_list["kk"],$months)) {
            $months[] = $pvm_list["kk"];
            $tr = $table->AddRow(Array(MonthName($pvm_list["kk"])));
            $tr->cells[0]->AddAttribute("class","month");
            $tr->cells[0]->AddAttribute("colspan","2");
        }

        if (!empty($vastuu)) {
            //Jos halutaan filtteröidä vastuun ukaan
            $vastuures = $con->select("vastuut",Array("vastuullinen"),Array(Array("messu_id","=",$row["id"]),Array("vastuu","=",$vastuu)))->fetchAll();
            $tr = $table->AddRow(Array(implode($pvm_list, "."),$vastuures[0]["vastuullinen"]));
            $tr->cells[0]->AddAttribute("class","pvm left");
            $tr->cells[1]->AddAttribute("class","editable right");
            $tr->cells[1]->AddAttribute("name",$row["pvm"]);
            AddHidden($tr->cells[0],"id_" . $row["pvm"], $row["id"]);
            if (empty($vastuures[0]["vastuullinen"]))
                $input = AttachEditable($tr->cells[1], $row["pvm"]);
        }
        else{
            //Jos katsellaan vain listaa ilman filtteriä
            if (!empty($row["teema"]))
                $tr = $table->AddRow(Array(implode($pvm_list, ".") . ": " . $row["teema"]));
            else
                $tr = $table->AddRow(Array(implode($pvm_list, "."). ": (ei teemaa lisättynä)"));
            $tr->cells[0]->AddAttribute("class","messurow");
        }
        $tr->cells[0]->AddAttribute('id',"messu_" . $row["id"]);
        $tr->cells[0]->AddAttribute('teema',$row["teema"]);
        $tr->cells[0]->AddAttribute('pvm',$row["pvm"]);
        //$tr->cells[0]->AddAttribute("class","messurow");
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
    $option = new DomEl('option','Yleisnäkymä',$select);
    $option = new DomEl('option','----',$select);
    foreach($result as $row){
        $litext = $row["vastuu"];
        $option = new DomEl('option',$litext,$select);
        }
    return $select->Show();
}

function MessuDetails($id, $url=''){
    $con = new DbCon();
    $result = $con->select("vastuut",Array("vastuu","vastuullinen","id"),Array(Array("messu_id","=",$id)))->fetchAll();
    $table = AddSection(True,"centercontent");

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

function ParseMonth($pvm){
    $kk = substr($pvm, 5,2);
    $v = substr($pvm, 0,4);
    $p = substr($pvm, 8,2);
    return(Array("p"=>RemoveZero($p), "kk"=>RemoveZero($kk), "v"=>$v));
}

function RemoveZero($input){
    if(substr($input,0,1)=="0")
        $input  = substr($input,1,1);
    return $input;
}

function MonthName($month_number){
    switch($month_number){
    case "1":
        return "Tammikuu";
        break;
    case "2":
        return "Helmikuu";
        break;
    case "3":
        return "Maaliskuu";
        break;
    case "4":
        return "Huhtikuu";
        break;
    case "5":
        return "Toukokuu";
        break;
    case "6":
        return "Kesäkuu";
        break;
    case "7":
        return "Heinäkuu";
        break;
    case "8":
        return "Elokuu";
        break;
    case "9":
        return "Syyskuu";
        break;
    case "10":
        return "Lokakuu";
        break;
    case "11":
        return "Marraskuu";
        break;
    case "12":
        return "Joulukuu";
        break;
    }
    return "no match";

}

function LoadComments($con){ //TODO: Use only one open connection, pass it on as argument $con = new DbCon();
    if (array_key_exists("messuid",$_GET)){
        $messuid = $_GET["messuid"];
        $comments = $con->select("comments",Array("content","commentator","id","comment_time"),Array(Array("messu_id","=",intval($messuid))),'','ORDER BY comment_time DESC')->fetchAll();
        foreach ($comments as $comment){
            $thiscomment = new Comment($comment);
            echo $thiscomment->container->Show();
        }
    }
}

function GetSeason($con, $date, $change='None'){
    if($change == 'None'){
        #1. Osuuko tämä päivä jonkin alun ja lopun väliin?
        $result = $con->select("kaudet", Array("id", "nimi","alkupvm","loppupvm"), Array(Array("alkupvm","<=",$date),Array("loppupvm",">=",$date)),"","ORDER BY alkupvm")->fetchAll();
    }
    if (empty($result) && $change != "previous"){
        #2. Hae tulevaisuudesta lähin kausi
        $result = $con->select("kaudet", Array("id", "nimi","alkupvm","loppupvm"), Array(Array("alkupvm",">=",$date)),"","ORDER BY alkupvm")->fetchAll();
    }
    if (empty($result) && $change != "next"){
        #3. Hae menneisyydestä lähin päivä
        $result = $con->select("kaudet", Array("id", "nimi","alkupvm","loppupvm"), Array(Array("loppupvm","<=",$date)),"","ORDER BY loppupvm DESC")->fetchAll();
    }


    #Palauta lähimmin osunut kausi (=0)
    return $result[0];
    #$recordDate = date("y-m-d", $datetime);

}

?>
