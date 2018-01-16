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

function AddHeader($relpath="",$jquery=true){

    echo "<!DOCTYPE html>
     <html lang='fi'>
     <head>
      <link href='https://fonts.googleapis.com/css?family=Nothing+You+Could+Do|Quicksand' rel='stylesheet'> 
     <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
     <meta name='viewport' content='width=device-width, initial-scale=1'>
     <link rel='stylesheet' href='$relpath" . "styles/updated.css?v=ljd'" . time() .">
     <link rel='stylesheet' href='$relpath" . "font-awesome-4.6.3/css/font-awesome.min.css'>";
     if($jquery==true){
         echo '<script src="scripts/jquery-3.2.1.min.js"></script>
               <!-- <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> -->
               <script src="scripts/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
               <!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"> -->
               <link rel="stylesheet" href="scripts/jquery-ui-1.12.1.custom/jquery-ui.min.css">
               ';
     }
    echo "<script src='$relpath" . "scripts/essential.js?v=axxxooo'" . time() ."></script>";
    echo "<title>Majakkaportaali 0.1</title></head>";

     #<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
     #<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
}

function CreateNavi($vastuulist, $url, $songmenu=False){

    $section = new DomEl('section','');
    $section->AddAttribute('id','leftbanner');

    $form = new DomEl('form','',$section);
    $form->AddAttribute('action','URL_RPL');
    $form->AddAttribute('method','GET');

    $span = new DomEl('span','',$form);
    $span->AddAttribute('class','menuleft');

    $ul = new DomEl('ul','',$span);
    $li = new DomEl('li','',$ul);
    $li->AddAttribute('id','settings');
    $li->AddAttribute('onClick','ShowSettings();');
    $li->AddAttribute('onClick','ShowSettings();');

    $icon = new DomEl('i','',$li);
    $icon->AddAttribute('class','fa fa-bars');
    $icon->AddAttribute('karia-hidden','true');
    $li = new DomEl('li','Majakkaportaali',$ul);
    $li->AddAttribute('id','homeli');
    $li->AddAttribute('title','Takaisin alkunäkymään');
    $li->AddAttribute('OnClick','window.location="index.php";');
    if($songmenu==True){
        $li = new DomEl('li','Selaa lauluja >',$ul);
        $li->AddAttribute('id','laululista_launcher');
        $li->AddAttribute('title','Selaa lauluja');
        $li->AddAttribute('onClick','ViewSongList();');
    }


    $span = new DomEl('span','',$form);
    $span->AddAttribute('class','menuright');
    $ul = new DomEl('ul','',$span);
    $li = new DomEl('li','',$ul);
    if($vastuulist == True){
        CreateVastuuList($li);
    }

    $li = new DomEl('li','',$ul);
    $li->AddAttribute("id","instrli");
    $li->AddAttribute("title","Lue ohjeet");
    $a = new DomEl('a','',$li);
    $a->AddAttribute("href","ohjeet.html");
    $a->AddAttribute("target","_blank");
    $a->AddAttribute("id","ohjelinka");
    $icon = new DomEl('i','',$a);
    $icon->AddAttribute('class','fa fa-question-circle');
    $icon->AddAttribute('karia-hidden','true');
    $icon->AddAttribute("id","ohjelink");

        #Tallenna vielä tieto kausista
        $input1 = new DomEl('input','',$form);
        $input1->AddAttribute('name','kausi');
        $input1->AddAttribute('class','hidden');
        $input1->AddAttribute('id','kausi_input');

        #Ja tee niiden vaihto mahdolliseksi
        $input1 = new DomEl('input','',$form);
        $input1->AddAttribute('name','seasonsubmit');
        $input1->AddAttribute('class','hidden');
        $input1->AddAttribute('id','seasonsubmit');
        $input1->AddAttribute('type','submit');

    echo $section->Show();
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

function AddSection($submit=False, $sectionclass='',$url="index.php", $tableid=""){
    $section = new DomEl("section","");
    $section->AddAttribute("id","contentlist");
    $section->AddAttribute("class",$sectionclass);

    $form = new DomEl("form","",$section);
    $form->AddAttribute("id","updater");
    $form->AddAttribute("name","contentlist");
    $form->AddAttribute("method","post");
    $form->AddAttribute("action","URL_RPL");

    $table = new HtmlTable($form);
    $table->element->AddAttribute("id",$tableid);

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

function CreateMessulist($con, $vastuu='',$url=''){
    $kausi = SetSeason($con);

    #MESSULISTA
    $commentlists = new DomEl("div", " ");
    $commentlists->AddAttribute("class","hidden");

    $result = $con->select("messut",Array("pvm","teema","id"),Array(Array("pvm",">=",$kausi["alkupvm"]),Array("pvm","<=",$kausi["loppupvm"])),"","ORDER BY pvm")->fetchAll();

    $submit = False;
    if(!empty($vastuu)){
        $submit=True;
    }
    $table = AddSection($submit,"rightcontent",$url,"messulisttable");

    $months = Array();
    $years = Array();
    #Jos ei messuja:
    if(sizeof($result)==0){
        $msg = "<p>Yhtään messua ei ole vielä tietokannassa. Aloita lisäämällä messuja 
        <a class='simplelink' href='insert_messudata.php'>Tästä linkistä</a>. </p>";
        return $msg;
    }
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
            if($vastuures){
                $tr = $table->AddRow(Array(implode($pvm_list, "."),$vastuures[0]["vastuullinen"]));
                $tr->cells[0]->AddAttribute("class","pvm left");
                $tr->cells[1]->AddAttribute("class","editable right");
                $tr->cells[1]->AddAttribute("name",$row["pvm"]);
                AddHidden($tr->cells[0],"id_" . $row["pvm"], $row["id"]);
                if (empty($vastuures[0]["vastuullinen"]))
                    $input = AttachEditable($tr->cells[1], $row["pvm"]);
            }
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
            $content_span->AddAttribute("class","themespan");
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


function CreateVastuuList($parent=null){
    $date = date('Y-m-d');
    $con = new DbCon();
    $result = $con->select("vastuut",Array("vastuu"),Array(),"DISTINCT")->fetchAll();

    if(!isset($parent)){
        $select = new DomEl("select",'');
        $select->AddAttribute('id',"commentthemes");
        $select->AddAttribute('name',"commenttheme");
        $option = new DomEl('option','Kommentin aihe',$select);
        $option = new DomEl('option','----',$select);
        $option = new DomEl('option','Yleinen',$select);
        $option = new DomEl('option','Infoasia',$select);
    }
    else{
        $select = new DomEl("select",'',$parent);
        $select->AddAttribute('id',"vastuulist");
        $option = new DomEl('option','Yleisnäkymä',$select);
        $option = new DomEl('option','----',$select);
    }

    foreach($result as $row){
        $litext = $row["vastuu"];
        $option = new DomEl('option',$litext,$select);
        }
    return $select;
}

function MessuDetails($id, $url=''){
    $con = new DbCon();
    $result = $con->select("vastuut",Array("vastuu","vastuullinen","id"),Array(Array("messu_id","=",$id)))->fetchAll();
    $table = AddSection(True,"centercontent",$url,"vastuulisttable");

    if(sizeof($result)==0){
        $vastuut = $con->select('vastuut',Array('vastuu'),Array(),"distinct")->fetchAll();
        foreach($vastuut as $vastuu){
            $con->insert("vastuut", Array("messu_id"=>$id,"vastuu"=>$vastuu["vastuu"],"vastuullinen"=>""));
        }
        $result = $con->select("vastuut",Array("vastuu","vastuullinen","id"),Array(Array("messu_id","=",$id)))->fetchAll();
        if(sizeof($vastuut)==0){
            $msg = "<p>Et ole vielä määritellyt yhtään vastuutehtävää. Voit lisätä 
                vastuutehtäviä <a class='simplelink' href='uusivastuu.php'>Tästä linkistä</a>. </p>";
            return $msg;
        }
    }

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


function SetSeason($con){

    $date = date('Y-m-d');

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

    return $kausi;
}

function FetchServiceList(){

    #2. MESSULISTA
    
    $commentlists = new DomEl("div", " ");
    $commentlists->AddAttribute("class","hidden");

    $result = $con->select("messut",Array("pvm","teema","id"),Array(Array("pvm",">=",$kausi["alkupvm"]),Array("pvm","<=",$kausi["loppupvm"])),"","ORDER BY pvm")->fetchAll();

}

function GetDateList($con){
    #Tehdään lista kaista tämän kauden messuista
    $kausi = SetSeason($con);
    $messut = $con->select("messut",Array("pvm","teema","id"),Array(Array("pvm",">=",$kausi["alkupvm"]),Array("pvm","<=",$kausi["loppupvm"])),"","ORDER BY pvm")->fetchAll();
    if(isset($_GET["messupvm"])){
        $dstring = date_format(date_create_from_format('d.m.Y', $_GET["messupvm"]), 'Y-m-d');
        $idrows = $con->select("messut",Array("id"),Array(Array("pvm","=",$dstring)),"","")->fetchAll();
        $datechange = True;
    }
    else{
        $dateset = False;
        $datechange = False;
    }
    $select = new DomEl("select");
    $select->AddAttribute('id',"pvmlist");
    $select->AddAttribute('name',"pvmlist");
    $date = date('Y-m-d');
    $pickedid = False;
    foreach($messut as $messu){
        $litext = FormatPvm($messu["pvm"]);
        $option = new DomEl('option',$litext,$select);
        if(strtotime($messu["pvm"])>=strtotime($date) and $dateset==False and $datechange==False){
            #Jos ei muuta valittu, valitse oletuksena  ensimmäinen
            #tämän päivän jälkeinen sunnuntai
            $dateset = True;
            $option->AddAttribute("selected","selected");
            $pickedid = $messu["id"];
        }
        elseif($datechange==True){
            if($messu["id"] == $idrows[0]["id"]){
                $dateset = True;
                $option->AddAttribute("selected","selected");
                $pickedid = $messu["id"];
            }
        }
        }
    echo $select->Show();
    return $pickedid;
}

function SongListForInsertion($pickedid, $con, $songtypes){
    #1.Haetaan kaikki tähän messuun aikaisemmin tallennetut laulut
    $result = $con->select("laulut",Array("tyyppi","nimi"),Array(Array("messu_id","=",$pickedid)),'','ORDER by id')->fetchAll();

    #2. Luodaan taulukko 
    $table = new HtmlTable();
    $table->element->AddAttribute("id","songtable");

    #3. Täytetään
    foreach($songtypes as $songtype){
        #Säilytä järjestys ja täytä kaikki tietokannasta löytyvät
        $tr = $table->AddRow(Array($songtype,"",""));
        $songtitle = "";
        $thisrow = Null;
        foreach($result as $row){
            #Käy jokaisen laulutyypin osalta läpi koko tietokantatulos ja täytä, jos löytyy
            if($row["tyyppi"]==$songtype){
                $songtitle = $row["nimi"];
                $thisrow = $row;
                break;
            }
        }
        $input = AttachEditable($tr->cells[1], $songtype, "");
        $input->AddAttribute("value", $songtitle);
        $input->AddAttribute("class", "linestyle songeditinput");
        $tr->cells[0]->AddAttribute("class","left");
        $tr->cells[1]->AddAttribute("class","right");
        CreateLyricsLink($tr, $thisrow["nimi"]);

    }

    return $table->element->Show();
}

function CreateLyricsLink($tr, $name){
    $tr->cells[2]->AddAttribute("class","lyricslinkcell");
    $link = new DomEl("a", "Katso sanoja", $tr->cells[2]);
    $link->AddAttribute("class","lyricslink");
    $link->AddAttribute("id", "link_" . str_replace(' ', '_', $name));
}

function WsSongList($con, $id, $tyyppi){
    #1. Haetaan kaikki tämän tyypin laulut
    $result = $con->select("laulut",Array("tyyppi","nimi"),Array(Array("messu_id","=",$id),Array("tyyppi","=", $tyyppi)),'','ORDER by id')->fetchAll();

    #2. Luodaan taulukko 
    $table = new HtmlTable();
    $table->element->AddAttribute("id", $tyyppi . "table");

    #3. Täytetään
    $tr=Null;
    $idx = 1;
    foreach($result as $row){
        $tr = $table->AddRow(Array($tyyppi . " $idx","",""));
        $input = AttachEditable($tr->cells[1], $tyyppi . "_$idx");
        $input->AddAttribute("value", $row["nimi"]);
        $input->AddAttribute("class", "linestyle songeditinput editable" . $tyyppi);
        $tr->cells[0]->AddAttribute("class","left");
        $tr->cells[1]->AddAttribute("class","right");
        $tr->cells[2]->AddAttribute("class","lyricslinkcell");
        CreateLyricsLink($tr, $row["nimi"]);
        $idx++;
    }

    if(!isset($tr)){
        $tr = $table->AddRow(Array($tyyppi . " 1","",""));
        $input = AttachEditable($tr->cells[1], $tyyppi . "_1");
        $input->AddAttribute("value", "");
        $input->AddAttribute("class", "linestyle songeditinput editable" . $tyyppi);
        $tr->cells[0]->AddAttribute("class","left");
        $tr->cells[1]->AddAttribute("class","right");
        $tr->cells[2]->AddAttribute("class","lyricslinkcell");
        CreateLyricsLink($tr, "");
    }

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
    $urlparams = "?";

    if (isset($_GET)){
        //Tallenna parametrit, jotta sama sivu latautuisi myös tallennettaessa tietoja
        foreach($_GET as $paramname => $param){
            if($paramname=="teema" and isset($_POST["messutheme"])){
                $param = $_POST["messutheme"];
            }
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
                        #muuta välit alaviivoiksi (?)
                        $vastuukey = str_replace(" ","_",$vastuu);
                        if(array_key_exists($vastuukey,$_POST)){
                            if (!empty($_POST[$vastuukey])){
                                $con->update("vastuut",
                                    Array("vastuullinen" =>$_POST[$vastuukey]),
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

                #Varmista, ettei valitsemattomia teemoja
                if(in_array($_POST["commenttheme"], Array("Kommentin aihe","----")))
                    $_POST["commenttheme"] = "Yleinen";

                $date = date('Y-m-d H:i:s');
                $con->insert("comments", Array("messu_id"=>$_POST["messu_id_comments"],"comment_time"=>$date,"content"=>$_POST["newcomment_text"],"commentator"=>$_POST["commentator"],"theme"=>$_POST["commenttheme"]));
            }
            elseif (array_key_exists("edited_comment_id",$_POST)){
                #4. Jos käyttäjä on muokannut kommentteja
                if(!empty($_POST["deleted_comment_id"])){
                    #4.a poistetaan
                    $con->query = $con->connection->prepare("DELETE FROM comments WHERE id = :sid");
                    $con->query->bindParam(':sid', intval($_POST["deleted_comment_id"]), PDO::PARAM_STR);
                    $con->Run();
                }
                else{
                    #4.b päivitetään
                    $con->update("comments", Array("theme"=>$_POST["commenttheme"], "content"=>$_POST["editedcomment"]),Array(Array("id","=",intval($_POST["edited_comment_id"]))));
                }
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
        $comments = $con->select("comments",Array("content","commentator","id","comment_time","theme"),Array(Array("messu_id","=",intval($messuid))),'','ORDER BY comment_time DESC')->fetchAll();
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
    $select->AddAttribute('name',"seasonlist");
    $option = new DomEl('option','Valitse kausi, johon syötetään',$select);
    $option = new DomEl('option','----',$select);
    foreach($result as $row){
        $litext = $row["nimi"];
        $option = new DomEl('option',$litext,$select);
        if(isset($_GET["seasonname"])){
            if($litext==$_GET["seasonname"]){
                $option->AddAttribute("selected","selected");
            }
        }
        if(isset($_POST["seasonlist"])){
            if($litext==$_POST["seasonlist"]){
                $option->AddAttribute("selected","selected");
            }
        }
        if(isset($_POST["newsname"])){
            if($litext==$_POST["newsname"]){
                $option->AddAttribute("selected","selected");
            }
        }
        }
    $option = new DomEl('option','Lisää uusi kausi',$select);
    echo $select->Show();
}

function Liturgiset($con, $type, $pickedid){
    $result = $con->select("laulut",Array("tyyppi","nimi"),Array(Array("messu_id","=",$pickedid),Array("tyyppi","=",$type)),'','ORDER by id')->fetchAll();

    $select = new DomEl("select");
    $select->AddAttribute("OnChange","UpdateLit('$type');");
    $select->AddAttribute('id', str_replace(' ','_', $type) . "_select");
    $option = new DomEl('option','Valitse versio',$select);
    $option = new DomEl('option','----',$select);

    $versions = $con->select("liturgiset", Array("name","songname"), Array(Array("songtype","=",$type)),'','ORDER by id')->fetchAll();
    $selected = "";
    foreach($versions as $version){
        $option = new DomEl('option',$version["name"],$select);
        $option->AddAttribute("id","link_" . $version["songname"]);
        if(isset($result[0])){
            #Valtse valmiiksi jumalan karisa/pyhä, jos jo asetettu
            if($version["songname"]==$result[0]["nimi"]){
                $option->AddAttribute("selected","selected");
                $selected = $version["songname"];
            }
        }
        }
    $option = new DomEl('option','Jokin muu',$select);
    echo $select->Show();
    return $selected;
}

function FormatPvm($pvm){
    $pvm_arr = ParseMonth($pvm);
    return ($pvm_arr["p"] . "." . $pvm_arr["kk"] . "." . $pvm_arr["v"]);
}

function RemoveServices($con){
    foreach($_POST as $key => $item){
        $pos = strpos($key,'REM_');
        if($pos!==false){
            $id = substr($key,$pos+strlen('REM_'));
            $con->query = $con->connection->prepare("DELETE FROM messut WHERE id = :tyyp ");
            $con->query->bindParam(':tyyp', intval($id), PDO::PARAM_STR);
            $con->Run();
        }
    }
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
    

    foreach($data as $key=> $row){
        $date = DateTime::createFromFormat('m/d/Y', $row["pvm"]);
        $row["pvm"] = $date->format('Y-m-d');
        $data[$key]["pvm"] = $date->format('Y-m-d');
        $con->insert("messut", Array("pvm"=>$row["pvm"],"teema"=>$row["teema"]));
    }

    //Syötä tiedot uudesta kaudesta, jos sellainen asetettu:
    if(isset($_POST["newsname"])){
        $con->insert("kaudet", Array("alkupvm"=>$data[1]["pvm"],"loppupvm"=>$row["pvm"],"nimi"=>$_POST["newsname"]));
    }
    elseif(isset($_POST["seasonlist"])){
        $res = $con->select("kaudet",Array("nimi","alkupvm","loppupvm"),Array(Array("nimi","=",$_POST["seasonlist"])),"","")->fetch();
        $seasonmin = new DateTime($res["alkupvm"]);
        $seasonmax = new DateTime($res["loppupvm"]);
        $insertedmin = new DateTime($data[1]["pvm"]);
        $insertedmax = new DateTime($row["pvm"]);
        #Päivitä kaudelle uudet minimit ja maksimit, jos tarpeen:
        if($insertedmin < $seasonmin)
            $con->update("kaudet", Array("alkupvm"=>$data[1]["pvm"]),Array(Array("nimi","=",$_POST["seasonlist"])));
        if($insertedmax > $seasonmax)
            $con->update("kaudet", Array("loppupvm"=>$row["pvm"]),Array(Array("nimi","=",$_POST["seasonlist"])));
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

function FetchSongNames($con){
    $result = $con->select("songs",Array("filename","id","title"),Array(),"","ORDER BY filename")->fetchAll();
    foreach($result as $row){
        if (!empty($row["filename"])){
            //Hae laulujen sanat
            $verses = $con->select("verses",Array("content"),Array(Array("song_id","=",intval($row["id"]))),"","ORDER BY id")->fetchAll();


            $span = new DomEl('span',$row["filename"]);
            $span->AddAttribute('class',"hidden songtitleentry");
            echo $span->Show();

            $div = new DomEl('div',"");
            $rep_name = "song_" . str_replace(' ', '_', $row["filename"]);
            $div->AddAttribute("id", $rep_name);



            $title = new DomEl('h3',$row["title"],$div);
            $editp = new DomEl('p',"",$div);
            $editlink = new DomEl('a',"Muokkaa sanoja",$editp);
            $editlink->AddAttribute("href","javascript:void(0);");
            $editlink->AddAttribute("OnClick","EditWords('" . $rep_name ."');");

            foreach($verses as $verse){
                $p = new DomEl('p',$verse["content"],$div);
            }

            $span = new DomEl('span',$row["id"], $div);

            #$p = new DomEl('p',"Sulje sanojen katselu klikkaamalla mihin tahansa laatikkoa",$div);

            echo $div->Show();
        }
    }
}

function FetchTechInfo($pickedid, $con, $infostring){
    $result = $con->select("messut",Array("info"),Array(Array("id","=",intval($pickedid))),"","")->fetchAll();
    if(!empty($result[0]["info"])){
        return $result[0]["info"];
    }
    else{
        return $infostring;
    }
}

function UpdateSongData($con, $simpleupdate=false,$songcon=false){
    if($songcon==false)
        $songcon = $con;

    if(isset($_POST["pickedid"])){
        #Luo olio (poistaa kaikki vanhat laulut tällä id:llä)
        $inserter = new SongInserter(intval($_POST["pickedid"]), $con);
        #
        if(!$simpleupdate){
            $inserter->InsertSong("Alkulaulu",$_POST["Alkulaulu"]);
            $inserter->InsertSong("Päivän laulu",$_POST["Päivän_laulu"]);
            $inserter->InsertSong("Loppulaulu",$_POST["Loppulaulu"]);
            if (isset($_POST["new_Pyhä-hymni"])){
                $con->insert("liturgiset", Array("songtype"=>"Pyhä-hymni","songname"=>$_POST["new_Pyhä-hymni"], "name"=>$_POST["new_Pyhä-hymni"]));
                $_POST["pyhä-hymni"] = $_POST["new_Pyhä-hymni"];
            }
            if (isset($_POST["new_Jumalan_karitsa"])){
                $con->insert("liturgiset", Array("songtype"=>"Jumalan karitsa","songname"=>$_POST["new_Jumalan_karitsa"], "name"=>$_POST["new_Jumalan_karitsa"]));
                $_POST["jumalan_karitsa"] = $_POST["new_Jumalan_karitsa"];
            }
            $inserter->InsertSong("Jumalan karitsa",$_POST["jumalan_karitsa"]);
            $inserter->InsertSong("Pyhä-hymni",$_POST["pyhä-hymni"]);
            #Tiedot tekniikalle
            #TODO: jos info-kenttään jotain mutakin...
            $con->update("messut", Array("info"=>$_POST["techinfo"]),Array(Array("id","=",intval($_POST["pickedid"]))));
        }

        foreach($_POST as $entry=>$val){
            if(strpos($entry,"Ylistyslaulu") !== false){
                $inserter->InsertSong("Ylistyslaulu",$val);
            }
            if(strpos($entry,"Ehtoollislaulu") !== false){
                $inserter->InsertSong("Ehtoollislaulu",$val);
            }
            if(strpos($entry,"Laulu") !== false){
                $inserter->InsertSong("Laulu",$val);
            }
        }

        if($_POST["edited_song_name"]!=="none" and !isset($_SESSION['indicator'])){
            //erota otsikko
            $title = str_replace('song_', '', $_POST["edited_song_name"]);
            $title = str_replace('_', ' ', $title);
            //Sävel ja sanat (TODO)
            $sav = "";
            $san = "";
            //pvm
            $date = date('Y-m-d');
            //Pilko säkeistöksi, jos 3 tai enemmän rivinloppua
            $verses = preg_split("/(\\r|\\n){3,}/", $_POST['editedsong_hidden']);

            //Syötä upouusi laulu suoraan tietokantaan
            //JOS tänne päädytty sivun päivittämisen takia, älä syötä uutta!
            $insert = True;
            if(isset($_SESSION['insertedsongs'])){
                if(in_array($title, $_SESSION['insertedsongs'])){
                    $insert = False;
                }
            }

            if($insert==True){
                //Ensin metatiedot
                $songcon->insert("songs", Array("title"=>$title,"filename"=>$title, "san"=>$san, "sav"=>$sav,"added"=>$date));
                //Hae uuden biisin id, jos useampia tällä nimellä, ota viimeisin
                $idrows = $songcon->select("songs",Array("id"),Array(Array("title","=",$title)),"","ORDER BY ID DESC")->fetchAll();
                //Syötä säkeistöt
                foreach($verses as $verse){
                    $songcon->insert("verses", Array("content"=>$verse,"song_id"=>intval($idrows[0]["id"])));
                }
                if(!isset($_SESSION['insertedsongs'])){
                    $_SESSION['insertedsongs'][] = $title;
                }
            }
        }

        }
    if (isset($_POST["edited_existing"])){

        #Poista vanhat säkeistöt kokonaan
        $songcon->query = $songcon->connection->prepare("DELETE FROM verses WHERE song_id = :sid");
        $songcon->query->bindParam(':sid', intval($_POST["editedsongid"]), PDO::PARAM_STR);
        $songcon->Run();

        #syötä päivitetyt säkeistöt
        $verses = preg_split("/(\\r|\\n){3,}/", $_POST['edited_existing_text']);
        foreach($verses as $verse){
                $songcon->insert("verses", Array("content"=>$verse,"song_id"=>intval($_POST["editedsongid"])));
        }
    }
    if (isset($_POST["removed_type"])){
        $con->query = $con->connection->prepare("DELETE FROM laulut WHERE tyyppi = :tyyp AND messu_id =  :mid ORDER BY id DESC LIMIT 1");
        $con->query->bindParam(':tyyp', $_POST["removed_type"], PDO::PARAM_STR);
        $con->query->bindParam(':mid', $_POST["messu_id"], PDO::PARAM_STR);
        $con->Run();
    }
}

class SongInserter{

    public function __construct ($messuid, $con) {
        $this->con = $con;
        $this->messuid = $messuid;
        #Poista kaikki aikaisemmat tälle päivälle merkityt laulut
        $this->con->query = $this->con->connection->prepare("DELETE FROM laulut WHERE messu_id = :mid");
        $this->con->query->bindParam(':mid', $this->messuid, PDO::PARAM_STR);
        $this->con->Run();
    }

    public function InsertSong($type, $name){
        $this->con->insert("laulut", Array("messu_id"=>$this->messuid,"tyyppi"=>$type, "nimi"=>$name));
    }
}

class VerseInserter{

    public function __construct ($messuid, $con) {
        $this->con = $con;
        $this->messuid = $messuid;
        #Poista kaikki aikaisemmat tälle päivälle merkityt laulut
        $this->con->query = $this->con->connection->prepare("DELETE FROM laulut WHERE messu_id = :mid");
        $this->con->query->bindParam(':mid', $this->messuid, PDO::PARAM_STR);
        $this->con->Run();
    }

    public function InsertSong($type, $name){
        $this->con->insert("laulut", Array("messu_id"=>$this->messuid,"tyyppi"=>$type, "nimi"=>$name));
    }
}

class MessuPresentation{

    public function __construct ($messuid, $con, $messuheader, $type) {
            $this->id = $messuid;
            $this->con = $con;
            $this->messuheader = $messuheader;
            $this->messutype = $type;
            $this->vastuut = $con->select("vastuut",Array('vastuu','vastuullinen'), Array(Array("messu_id","=",$messuid)),"","")->fetchAll();
            $this->messutitle = $con->select("messut",Array('teema'), Array(Array("id","=",$messuid)),'','')->fetchColumn(0);

            if($type=="parkki")
                $this->songs  = $this->GetMultiSongs($con,"Laulu");
            else{
                $this->singlesongs = Array();
                $yksittaiset = Array("Alkulaulu","Päivän laulu","Loppulaulu");
                foreach($yksittaiset as $tyyppi){
                    $this->singlesongs[$tyyppi] = new SongDom($tyyppi, $con->select("laulut",Array("nimi"),Array(Array("messu_id","=",$messuid),Array("tyyppi","=",$tyyppi)),'','')->fetchColumn(0));
                }
                $this->wssongs  = $this->GetMultiSongs($con,"Ylistyslaulu");
                $this->comsongs  = $this->GetMultiSongs($con,"Ehtoollislaulu");
                $this->pyha = new SongDom("Pyhä-hymni", $con->select("laulut",Array("nimi"),Array(Array("messu_id","=",$messuid),Array("tyyppi","=","Pyhä-hymni")),'','')->fetchColumn(0));
                $this->jk = new SongDom("Jumalan karitsa", $con->select("laulut",Array("nimi"),Array(Array("messu_id","=",$messuid),Array("tyyppi","=","Jumalan karitsa")),'','')->fetchColumn(0));

            }
    }


    public function GetMultiSongs($con, $tyyppi){
        $res = $con->select("laulut",Array("nimi"),Array(Array("messu_id","=",$this->id),Array("tyyppi","=",$tyyppi)),'','ORDER by id')->fetchAll();
        #Nimeämiseroja js:n ja php:n välillä, TODO poista tämä. -->
        if($tyyppi=='Ylistyslaulu')
            $tyyppi='Ylistys- ja rukouslauluja';
        if($tyyppi=='Ehtoollislaulu')
            $tyyppi='Ehtoollislauluja';
        #<--

        $ar = Array();
        foreach($res as $song){
            $ar[] = new SongDom($tyyppi, $song["nimi"]);
        }
        return $ar;
    }

    public function PrintSongInfo($name, $role){
        $el = new DomEl('span',$name);
        $el->AddAttribute('role',$role);
        return $el;
    }

    public function CreateHtml($onbackground=false){
        $struct_div = new DomEl('div','');
        $struct_div->AddAttribute('id','structure');
        if($this->messutype=="parkki"){
            foreach($this->songs as $song){
                $struct_div->AddChild($song);
            }
        }
        else{
            $struct_div->AddChild($this->singlesongs["Alkulaulu"]);
            $struct_div->AddChild($this->singlesongs["Päivän laulu"]);
            foreach($this->wssongs as $song){
                $struct_div->AddChild($song);
            }
            $struct_div->AddChild($this->pyha);
            $struct_div->AddChild($this->jk);
            foreach($this->comsongs as $song){
                $struct_div->AddChild($song);
            }
            $struct_div->AddChild($this->singlesongs["Loppulaulu"]);


            $struct_div->AddChild($this->singlesongs["Loppulaulu"]);
        }

        echo $struct_div->Show();

        #Normivastuut
        $resp_div = new DomEl('div','');
        $resp_div->AddAttribute('id','vastuut');

        $address="";
        foreach($this->vastuut as $vastuu){
            if($vastuu["vastuu"]=='Saarnateksti')
                $address = $vastuu["vastuullinen"];

            $el = new DomEl('span', $vastuu["vastuullinen"]);
            $el-> AddAttribute("id",$vastuu["vastuu"]);
            $el-> AddAttribute("class","vastuudata");
            $resp_div->AddChild($el);
        }
        echo $resp_div->Show();


        #Kolehti
        $kolehti_params = $this->con->select('messut',Array('kolehtikohde','kolehtitavoite'),Array(Array("id","=",$this->id)))->fetch();
        $keratty = $this->con->select('messut',Array('SUM(kolehtia_keratty)'),
            Array(Array("kolehtikohde","=",$kolehti_params["kolehtikohde"]),
            Array("kolehtitavoite","=",$kolehti_params["kolehtitavoite"])
        ))->fetchColumn();
        $total = $this->con->select('messut',Array('kolehtikohde','kolehtitavoite'),Array(Array("id","=",$this->id)))->fetch();
        $kolehtitavoite = $this->con->select('kolehtitavoitteet',Array('tavoitemaara','kuvaus','kuva'),
            Array(Array("tavoite","=",$kolehti_params["kolehtitavoite"]),
            Array("kohde","=",$kolehti_params["kolehtikohde"])))->fetch();
        $kolehti_div = new DomEl('div','');
        $kolehti_div->AddAttribute('id','kolehti');

        foreach($kolehti_params as $key=>$param){
            if(!in_array($key,Array("0","1"))){
                $el = new DomEl('span', $param);
                $el-> AddAttribute("class",$key);
                $kolehti_div->AddChild($el);
            }
        }
        $el = new DomEl('span', $kolehtitavoite["tavoitemaara"]);
        $el-> AddAttribute("class","tavoitemaara");
        $kolehti_div->AddChild($el);

        $el = new DomEl('span', $kolehtitavoite["kuvaus"]);
        $el-> AddAttribute("class","tavoitekuvaus");
        $kolehti_div->AddChild($el);

        $el = new DomEl('span', $kolehtitavoite["kuva"]);
        $el-> AddAttribute("class","tavoitekuva");
        $kolehti_div->AddChild($el);


        $el = new DomEl('span', $keratty);
        $el-> AddAttribute("class","kerattymaara");
        $kolehti_div->AddChild($el);

        echo $kolehti_div->Show();


        if($this->messutype!="parkki"){
            #evankeliumi evl:n sivuilta:
            $address = ParseBibleAddress($address);
            $booknames = Array('Matt', 'Mark', 'Luuk', 'Joh', 'Apt', 'Room', '1Kor', '2Kor', 'Gal', 'Ef', 'Fil', 'Kol', '1Tess', '2Tess', '1Tim', '2Tim', 'Tit', 'Filem', 'Hepr', 'Jaak', '1Piet', '2Piet', '1Joh', '2Joh', '3Joh', 'Juud', 'Ilm','1Moos', '2Moos', '3Moos', '4Moos', '5Moos', 'Joos', 'Tuom', 'Ruut', '1Sam', '2Sam', '1Kun', '2Kun', '1Aik', '2Aik', 'Esra', 'Neh', 'Est', 'Job', 'Ps', 'Sananl', 'Saarn', 'Laull', 'Jes', 'Jer', 'Valit', 'Hes', 'Dan', 'Hoos', 'Joel', 'Aam', 'Ob', 'Joona', 'Miika', 'Nah', 'Hab', 'Sef', 'Hagg', 'Sak', 'Mal');
            //if(!in_array($address["book"],$booknames)){
            //    $msg = "<p style='width:40em;'>Raamattutekstin hakeminen ei onnistu, koska ohjelma ei tunnista kirjaa <strong>". $address["book"] . "</strong>. Hyväksytyt kirjojen lyhenteet ovat: <strong>" . implode($booknames,", ") . "</strong>. Käy korjaamassa Raamattuviittaus portaalin messukohtaisessa näkymässä ja päivitä tämä sivu.</p>";
            //    die($msg);
            //};
            
            $gospelverses = FetchBibleContent($address["book"] . "." . $address["chapter"], $address["verses"], $onbackground);

            if (sizeof($gospelverses)>1)
                $gospeltext =  implode($gospelverses, "¤");
            else
                $gospeltext =  ($gospelverses);

            $gospel = new DomEl('p',$gospeltext);
            $gospel->AddAttribute('id','evankeliumi');
            $gospel->AddAttribute('address', $address["book"] . "." . $address["chapter"] . ": " . $address["verses"]);
            $gospel->AddAttribute('role','evankeliumi');
            echo $gospel->Show();

            $gospel = new DomEl('p',$gospeltext);
        }

        $title = new DomEl('p',$this->messutitle);
        $title->AddAttribute('id','messutitle');
        $header = new DomEl('p',$this->messuheader);
        $header->AddAttribute('id','messuheader');
        $type = new DomEl('p',$this->messutype);
        $type->AddAttribute('id','messutype');

        echo $title->Show();
        echo $type->Show();
        echo $header->Show();
    }


    public function UploadTrackingInfo($con){
        #Tallenna tiedot messun etenemisen seuraamista varten

        #1. Poista mahdolliset vanhat tiedot tästä messusta
        $con->query = $con->connection->prepare("DELETE FROM messukulku WHERE messu_id = :sid");
        $con->query->bindParam(':sid', $this->id, PDO::PARAM_STR);
        $con->Run();

        $con->insert("messukulku", Array("typeidentifier"=>"none", "messu_id"=>$this->id,"entrytype"=>"item","entry"=>"Johdanto"));
        $con->insert("messukulku", Array("typeidentifier"=>"Alkulaulu", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Alkulaulu: " . $this->singlesongs["Alkulaulu"]->songtitle, "iscurrent"=>true));
        $con->insert("messukulku", Array("typeidentifier"=>"Alkusanat", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Alkusanat"));
        $con->insert("messukulku", Array("typeidentifier"=>"Seurakuntalaisen sana", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Seurakuntalaisen sana"));
        $con->insert("messukulku", Array("typeidentifier"=>"none", "messu_id"=>$this->id,"entrytype"=>"item","entry"=>"Sana"));
        $con->insert("messukulku", Array("typeidentifier"=>"Päivän laulu", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Päivän laulu: " . $this->singlesongs["Päivän laulu"]->songtitle));
        $con->insert("messukulku", Array("typeidentifier"=>"Evankeliumi", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Evankeliumiteksti"));
        $con->insert("messukulku", Array("typeidentifier"=>"Saarna", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Saarna"));
        $con->insert("messukulku", Array("typeidentifier"=>"Synnintunnustus", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Synnintunnustus"));
        $con->insert("messukulku", Array("typeidentifier"=>"Uskontunnustus", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Uskontunnustus"));
        $con->insert("messukulku", Array("typeidentifier"=>"none", "messu_id"=>$this->id,"entrytype"=>"item","entry"=>"Ylistys ja rukous"));
        $idx=1;
        foreach($this->wssongs as $song){
            $con->insert("messukulku", Array("typeidentifier"=>"ylistyslaulu" . $idx, "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Ylistys- ja rukouslauluja: " . $song->songtitle));
            $idx++;
        }
        $con->insert("messukulku", Array("typeidentifier"=>"Esirukous", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Esirukous"));
        $con->insert("messukulku", Array("typeidentifier"=>"none", "messu_id"=>$this->id,"entrytype"=>"item","entry"=>"Ehtoollisen asetus"));
        $con->insert("messukulku", Array("typeidentifier"=>"Pyhä", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Pyhä-hymni"));
        $con->insert("messukulku", Array("typeidentifier"=>"Ehtoollisrukous", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Ehtoollisrukous"));
        $con->insert("messukulku", Array("typeidentifier"=>"Isä meidän", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Isä meidän"));
        $con->insert("messukulku", Array("typeidentifier"=>"Jumalan karitsa", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Jumalan karitsa"));
        $con->insert("messukulku", Array("typeidentifier"=>"none", "messu_id"=>$this->id,"entrytype"=>"item","entry"=>"Ehtoollisen vietto"));
        $idx=1;
        foreach($this->comsongs as $song){
            $con->insert("messukulku", Array("typeidentifier"=>"ehtoollislaulu" . $idx, "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Ylistys- ja rukouslauluja: " . $song->songtitle));
            $idx++;
        }
        $con->insert("messukulku", Array("typeidentifier"=>"none", "messu_id"=>$this->id,"entrytype"=>"item","entry"=>"Siunaus ja lähettäminen"));
        $con->insert("messukulku", Array("typeidentifier"=>"Herran siunaus", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Herran siunaus"));
        $con->insert("messukulku", Array("typeidentifier"=>"Loppusanat", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Loppusanat"));
        $con->insert("messukulku", Array("typeidentifier"=>"Loppulaulu", "messu_id"=>$this->id,"entrytype"=>"subitem","entry"=>"Loppulaulu: " . $this->singlesongs["Loppulaulu"]->songtitle));

        #$con->insert("messukulku", Array("messu_id"=>$this->id,""=>$date,"content"=>$_POST["newcomment_text"],"commentator"=>$_POST["commentator"],"theme"=>$_POST["commenttheme"]));
        #$struct_div->AddChild($this->singlesongs["Alkulaulu"]);
        #$struct_div->AddChild($this->singlesongs["Päivän laulu"]);
        #foreach($this->wssongs as $song){
        #    $struct_div->AddChild($song);
        #}
        #$struct_div->AddChild($this->pyha);
        #$struct_div->AddChild($this->jk);
        #foreach($this->comsongs as $song){
        #    $struct_div->AddChild($song);
        #}
    }

}


function ParseBibleAddress($addr){
        #1. erottele luku
        preg_match("/(\\w+)[^\\d]*(\\d+) *: *(.+)/", $addr, $groups);

        #jos luku ja jakeet annettu
        if(!empty($groups))
            return Array("book"=> $groups[1], "chapter"=>$groups[2], "verses"=>$groups[3]);

        #jos pelkkä luku
        preg_match("/(\\w+)[^\\d]*(\\d+)[^\\d]*$/", $addr, $groups);
        if(!empty($groups))
            return Array("book"=> $groups[1], "chapter"=>$groups[2], "verses"=>"");

        return Array("book"=> "", "chapter"=>"","verses"=>"");
}

class SongDom extends DomEl{

    public function __construct ($role, $name) {
        parent::__construct('song',$name);
        $this->AddAttribute('role', $role);
        $this->songtitle = $name;
    }

}

function FetchSongsForSlides($con){
    $result = $con->select("songs",Array("filename","id","title"),Array())->fetchAll();
    foreach($result as $row){
        if (!empty($row["filename"])){
            //Hae laulujen sanat
            $verses = $con->select("verses",Array("content"),Array(Array("song_id","=",intval($row["id"]))),"","ORDER BY id")->fetchAll();


            $div = new DomEl('div',"");
            $div->AddAttribute("id", $row["filename"]);
            $div->AddAttribute("class","songdata");

            $span = new DomEl('span',$row["title"],$div);
            $span->AddAttribute('class',"songtitle");

            $songtext = "";
            foreach($verses as $verse){
                $songtext .= $verse["content"] . "\n\n";
            }
            $p = new DomEl('p',trim($songtext),$div);
            $p->AddAttribute("class","songdatacontent");

            #$p = new DomEl('p',"Sulje sanojen katselu klikkaamalla mihin tahansa laatikkoa",$div);

            echo $div->Show();
        }
    }
}

function GetMessuParams($con, $id){
    $res = $con->select("messut",Array("pvm","teema"),Array(Array("id","=",$id)),"","")->FetchAll();
    return "messuid=$id&pvm=" . $res[0]["pvm"] . "&teema=" . $res[0]["teema"];
}


function UpdatePlayers($con, $type="soittaja"){
    $id = $_POST["player_id"];
    if($type=="soittaja"){
        $onetable = "soittajat";
        $manytable = "soittimet";
        $person = "soittaja";
    }
    elseif($type=="puhuja"){
        $onetable = "puhujat";
        $manytable = "puheenaiheet";
        $person = "puhuja";
    }

    #Poistaminen:
    if($_POST["playerdeleted"]=="true"){
        $con->query = $con->connection->prepare("DELETE FROM $manytable WHERE $person" . "_id = :soittajaid");
        $con->query->bindParam(':soittajaid', intval($id), PDO::PARAM_STR);
        $con->Run();
        $con->query = $con->connection->prepare("DELETE FROM $onetable WHERE id = :soittajaid");
        $con->query->bindParam(':soittajaid', intval($id), PDO::PARAM_STR);
        $con->Run();
        return 0;
    }

    #Normaali päivitys
    $con->update($onetable, Array("nimi"=>$_POST["playername"], "puhelin"=>$_POST["phone"],"email"=>$_POST["email"]),Array(Array("id","=",$id)));

    #poista vanhat soittimet...
    $con->query = $con->connection->prepare("DELETE FROM $manytable WHERE $person" . "_id = :soittajaid");
    $con->query->bindParam(':soittajaid', intval($id), PDO::PARAM_STR);
    $con->Run();

    # ...ja lisää uudet
    $instruments = explode(";",$_POST["instruments"]);
    foreach($instruments as $instrument){
        if(!empty($instrument)){
            if($type=="soittaja")
                $con->insert("soittimet", Array("soitin"=>$instrument,"soittaja_id"=>$id));
            elseif($type=="puhuja")
                $con->insert("puheenaiheet", Array("puheenaihe"=>$instrument,"puhuja_id"=>$id));
        }
    }
}


function InsertPlayers($con, $tyyppi="soittaja"){
    if($tyyppi=="soittaja"){
        $con->insert("soittajat", Array("nimi"=>$_POST["playername"],"email" => $_POST["email"], "puhelin" =>$_POST["phone"]));
        $id = $con->select("soittajat",Array("id"),Array(),"","ORDER BY id DESC")->fetchColumn(0);
    }
    elseif($tyyppi=="puhuja"){
        $con->insert("puhujat", Array("nimi"=>$_POST["playername"],"email" => $_POST["email"], "puhelin" =>$_POST["phone"]));
        $id = $con->select("puhujat",Array("id"),Array(),"","ORDER BY id DESC")->fetchColumn(0);
    }
    $instruments = explode(";",$_POST["instruments"]);
    foreach($instruments as $instrument){
        if(!empty($instrument)){
            if($tyyppi=="soittaja")
                $con->insert("soittimet", Array("soitin"=>$instrument,"soittaja_id"=>$id));
            elseif($tyyppi=="puhuja")
                $con->insert("puheenaiheet", Array("puheenaihe"=>$instrument,"puhuja_id"=>$id));
        }
    }
}

Function FetchPlayers($con, $type="soittajat"){

    if($type=="soittajat")
        $result = $con->select("soittajat",Array("nimi","puhelin","email","id"),Array(),"","ORDER BY id DESC")->fetchAll();
    elseif($type=="puhuja")
        $result = $con->select("puhujat",Array("nimi","puhelin","email","id"),Array(),"","ORDER BY id DESC")->fetchAll();
    $table = new HtmlTable();
    $table->element->AddAttribute("id","playertable");
    foreach($result as $row){
        $instrlist = "";
        if($type=="soittajat")
            $instruments = $con->select("soittimet",Array("soitin"),Array(Array("soittaja_id","=",$row["id"])))->fetchAll();
        elseif($type=="puhuja")
            $instruments = $con->select("puheenaiheet",Array("puheenaihe"),Array(Array("puhuja_id","=",$row["id"])))->fetchAll();

        foreach($instruments as $instrument){
            if(!empty($instrlist)){
                $instrlist .= ", ";
            }
            if($type=="soittajat")
                $instrlist .= $instrument["soitin"];
            elseif($type=="puhuja")
                $instrlist .= $instrument["puheenaihe"];
        }
        $tr = $table->AddRow(Array($row["nimi"],$instrlist, $row["puhelin"],$row["email"]));
        $tr->element->AddAttribute("id","id_" . $row["id"]);
        $tr->element->AddAttribute("onClick","EditRow(this)");
        $tr->cells[0]->AddAttribute("class","playername");
        $tr->cells[1]->AddAttribute("class","instruments");
        $tr->cells[2]->AddAttribute("class","phone");
        $tr->cells[3]->AddAttribute("class","email");
    }
    return $table->element->Show();
}


function LoadExistingServices($con){
    if(isset($_GET["seasonname"]) or isset($_POST["seasonlist"]) or isset($_POST["newsname"])){
        if(isset($_GET["seasonname"]))
            $name = $_GET["seasonname"];
        elseif(isset($_POST["newsname"]))
            $name = $_POST["newsname"];
        elseif(isset($_POST["seasonlist"]))
            $name = $_POST["seasonlist"];

        $kausi = $con->select("kaudet",Array("alkupvm","loppupvm"),Array(Array("nimi","=",$name)),"","")->fetch();
        $messut = $con->select("messut",Array("pvm","teema","id"),Array(Array("pvm",">=",$kausi["alkupvm"]),Array("pvm","<=",$kausi["loppupvm"])),"","ORDER BY pvm")->fetchAll();
        if(sizeof($messut)>0){
            $ul = new DomEl('ul','');
            $ul->AddAttribute("id","existingserviceslist");
            foreach($messut as $messu){
                $li = new DomEl('li',"",$ul);
                $span0 = new DomEl('span',"",$li);
                $span1 = new DomEl('span',FormatPvm($messu["pvm"]),$li);
                $checkbox = new DomEl('input', "", $span0);
                $checkbox->AddAttribute("type", "checkbox");
                $checkbox->AddAttribute("name", "REM_" . $messu["id"]);
                $span2 = new DomEl('span',$messu["teema"],$li);
                $span2->AddAttribute("class","editable");
                $span2->AddAttribute("name", "edited_" . str_replace(' ', '_', $vastuu["vastuu"]));
            }
            echo $ul->Show();
            return "";
        }

    }
    return "hidden";

}

?>
