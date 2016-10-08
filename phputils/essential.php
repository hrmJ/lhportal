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
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <link rel="stylesheet" href="styles/default.css">
     <link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">
     <script src="scripts/essential.js"></script>
     <title>Majakkaportaali 0.1</title>
     </head>';

     #<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
     #<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
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
    
    $commentlists = new DomEl("div", " ");
    $commentlists->AddAttribute("class","hidden");

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
            $tr = $table->AddRow(Array(""));

            //showing the comments
            $comments = $con->select("comments",Array("content","commentator","id","comment_time"),Array(Array("messu_id","=",intval($row["id"]))),'','ORDER BY comment_time DESC')->fetchAll();
            $clist = new CommentList($commentlists, $comments, $row["id"]);
            $tr->cells[0] = AddCommentIcon($comments, $row, $tr->cells[0]);

            if (!empty($row["teema"]))
                $theme = implode($pvm_list, ".") . ": " . $row["teema"];
            else
                $theme = implode($pvm_list, ".") . ": (ei teemaa lisättynä)";

            $content_span = new DomEl("span", $theme, $tr->cells[0]);
            $tr->cells[0]->AddAttribute("class","messurow");
        }
        $tr->cells[0]->AddAttribute('id',"messu_" . $row["id"]);
        $tr->cells[0]->AddAttribute('teema',$row["teema"]);
        $tr->cells[0]->AddAttribute('pvm',$row["pvm"]);
        //$tr->cells[0]->AddAttribute("class","messurow");
    }

    AddHidden($table->element,"vastuu",$vastuu);
    echo $commentlists->Show();
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

function SongList($con, $id){
    $result = $con->select("laulut",Array("tyyppi","nimi"),Array(Array("messu_id","=",$id)),'','ORDER by id')->fetchAll();
    if (sizeof($result)==0){
        return False;
    }
    else{
        $div = new DomEl("div");
        $div->AddAttribute("id","songdiv");
        $table = new HtmlTable($div);
        $table->element->AddAttribute("class","songtable");
        foreach($result as $row){
            $tr = $table->AddRow(Array($row["tyyppi"],$row["nimi"]));
            $tr->element->AddAttribute("class","songtr");
        }
        return $table->element->Show();
    }
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

function ListJobs($con){
    $vastuut = $con->select('vastuut',Array('vastuu'),Array(),"distinct")->fetchAll();
    #kerää lista kkäytetyistä vastuista
    $updatables = Array();
    foreach($vastuut as $vastuu){
        $updatables[] = $vastuu["vastuu"];
    }
    return $updatables;
}

function UpdateMessudata($con){
    //Jos käyttäjä on päivittänyt jotain tietoja messusta tai messuista, prosessoi dataa:
    if(isset($_POST)){
            if (array_key_exists("messu_id",$_POST)){
                #1. Päivitykset messukohtaisesti, kaikki roolit mahdollisia
                    $updatables = ListJobs($con);
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
            elseif (array_key_exists("themesubmit",$_POST)){
                $con->update("messut",
                    Array("teema" =>$_POST["messutheme"]),
                    Array(Array("id","=",intval($_POST["theme_messu_id"]))));
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

function ListSeasons(){
    $con = new DbCon();
    $result = $con->select("kaudet", Array("id", "nimi","alkupvm","loppupvm"), Array(),"","ORDER BY loppupvm DESC")->fetchAll();
    $select = new DomEl("select");
    $select->AddAttribute('id',"seasonlist");
    $option = new DomEl('option','Valitse kausi, johon syötetään',$select);
    $option = new DomEl('option','----',$select);
    foreach($result as $row){
        $litext = $row["nimi"];
        $option = new DomEl('option',$litext,$select);
        }
    $option = new DomEl('option','Lisää uusi kausi',$select);
    echo $select->Show();
}

function FormatPvm($pvm){
    $pvm_arr = ParseMonth($pvm);
    return ($pvm_arr["p"] . "." . $pvm_arr["kk"] . "." . $pvm_arr["v"]);


}

function InsertServices($con){
    $data = Array();
    foreach($_POST as $fieldname => $value){
        $pos = strpos($fieldname,'_');
        $number = substr($fieldname,$pos+1);
        $dbfield = substr($fieldname,0,$pos);
        if (!isset($data[$number]) AND $pos){
           $data[$number]  = Array($dbfield=>$value);
        }
        elseif($pos){ end($data);
            $data[$number][$dbfield] = $value;
        }
    }

    $vastuufields = ListJobs($con);
    

    foreach($data as $row){
        //Syötä tiedot itse messusta:
        $con->insert("messut", Array("pvm"=>$row["pvm"],"teema"=>$row["teema"]));
        //Syötä mahdolliset jo tiedossa olevat vastuut + saarnateksti
        $max = $con->maxval("messut","id");
        $vastuudata=Array();
        foreach($vastuufields as $vastuufield){
            $con->insert("vastuut", Array("messu_id"=>$max,"vastuu" => $vastuufield, "vastuullinen" =>$row[$vastuufield]));
        }
        
    }

    //Syötä tiedot uudesta kaudesta, jos sellainen asetettu:
    if(isset($_POST["newsname"])){
        $con->insert("kaudet", Array("alkupvm"=>$data[0]["pvm"],"loppupvm"=>$row["pvm"],"nimi"=>$_POST["newsname"]));
    }

}

function AddCommentIcon($comments, $row, $cell){
        //showing the comments
        $icon_span = new DomEl("span"," ",$cell);
        $ta = "ta";
        if (sizeof($comments)==1)
            $ta = "";
        $comtitle = sizeof($comments) . " huomio$ta tästä messusta";
        $icon =  new DomEl("i","",$icon_span);
        $icon->AddAttribute("title",$comtitle);
        $icon->AddAttribute("commentcount",sizeof($comments));
        if (sizeof($comments)==0){
            $icon->AddAttribute("class","fa fa-comments inv");
        }
        else{
            $icon->AddAttribute("class","fa fa-comments vis");
        }

        $icon->AddAttribute("messuid", $row["id"]);

        return $cell;
}

?>
