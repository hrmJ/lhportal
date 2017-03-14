<?php
session_start();
require('phputils/essential.php');
$url = SaveGetParams();

if (!isset( $_SESSION['user_id'] )){
    if (isset($_POST["username"],$_POST["password"])){
        $valid = validate_login($_POST["username"],$_POST["password"]);
        $loginfail = True;
        if ($valid){
            //if the login info passed validation and no active session, try to login
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

            $con = new DbCon();
            $usr_id = $con->SelectUser($username, $password);

            if($usr_id){
                $_SESSION['user_id'] = $usr_id;
                $loginfail = False;
            }

        }
    }
    if($loginfail or !$valid or !isset($_POST["username"],$_POST["password"])){
        #Kun saavutaan sivulle 1. kertaa tai kirjautuminen ei onnistunut
        require('login.php');
    }
}
if (isset($_SESSION['user_id'])){

AddHeader();
$con = new DbCon();

?>
<body>

<?php
CreateNavi(false, $url, False);
?>


<article id="maincontainer2">

<?php
$messut = $con->select('messut',Array('id'),Array(),"distinct")->fetchAll();
if(!isset($messut) or sizeof($messut)==0){
    echo "<br><br><br><br><br><br>";
    echo "<p>Yhtään messua ei ole vielä tietokannassa. Aloita lisäämällä messuja 
        <a class='simplelink' href='insert_messudata.php'>Tästä linkistä</a>. </p>";
}
else{
    if(isset($_POST["uusivastuu"])){
        if(isset($messut)){
            foreach($messut as $messu){
                $con->insert("vastuut", Array("messu_id"=>$messu["id"],"vastuu"=>$_POST["uusivastuu"], "vastuullinen"=>""));
            }
        }
    }
?>


    <?php

    if(isset($_POST["remover"])){
        foreach($_POST as $key => $item){
            $pos = strpos($key,'REM_');
            if($pos!==false){
                $vastuu = substr($key,$pos+strlen('REM_'));
                $con->query = $con->connection->prepare("DELETE FROM vastuut WHERE vastuu = :tyyp ");
                $con->query->bindParam(':tyyp', $vastuu, PDO::PARAM_STR);
                $con->Run();
            }

            $pos = strpos($key,'edited_');
            if($pos!==false){
                $editedname = substr($key,$pos+strlen('edited_'));
                $editedname = str_replace('_', ' ', $editedname);
                $con->update("vastuut", Array("vastuu"=>$item),Array(Array("vastuu","=",$editedname)));
            }

        }
    }

        $ul = new DomEl('ul','');
        $vastuut = $con->select('vastuut',Array('vastuu'),Array(),"distinct")->fetchAll();
        foreach($vastuut as $vastuu){
            $li = new DomEl('li',"",$ul);
            $span1 = new DomEl('span',"",$li);
            $checkbox = new DomEl('input', "", $span1);
            $checkbox->AddAttribute("type", "checkbox");
            $checkbox->AddAttribute("name", "REM_" . $vastuu["vastuu"]);
            $span2 = new DomEl('span',$vastuu["vastuu"],$li);
            $span2->AddAttribute("class","editable");
            $span2->AddAttribute("name", "edited_" . str_replace(' ', '_', $vastuu["vastuu"]));
            $span3 = new DomEl('span',"",$li);
        }
        if(sizeof($vastuut)>0) {
            ?>

            <h3 style="margin-top:3em;">Muokkaa nykyisiä vastuita</h3>

            <form id='vastuuhallinta' action="uusivastuu.php" method="post" >
                <p>Tällä hetkellä käytössä alla olevat vastuut. Voit nimetä vastuun uudelleen klikkaamalla sitä. Muista painaa tämän jälkeen "Tallenna muutetut nimet" -linkkiä.</p>
                <?php echo $ul->Show();?>
            <div>
                <ul>
                    <li> <a class="simplelink" href="javascript:void(0);" OnClick="EditVastuuNames();">Tallenna muutetut nimet</a>
                    <li> <a class="simplelink" href="javascript:void(0);" OnClick="EditVastuuNames(true);">Poista valitut vastuut kokonaan</a>
                </ul>
            </div>

            <input class="hidden" id="remover" type="submit" value="Poista valitut" name="remover">
        </form>
        <?php }//sizeof(vastuut)?>

    <h3 style="margin-top:3em;">Lisää uusi vastuu</h3>

    <form id='messusyotto' action="uusivastuu.php" method="post" >
        <label for="uusivastuu">Anna uuden vastuutyypin nimi:</label>
        <input type="text" name="uusivastuu">
        <input type="submit" value="Tallenna">
    </form>

</article>

<script src="scripts/essential.js"></script>
<script>

var editables = document.getElementsByClassName('editable');
for(var row_idx = 0; row_idx < editables.length;row_idx++){
    var editable = editables[row_idx];
    var e_row = editable.parentElement.parentElement;
    e_row.addEventListener('click',edit,false);
}

</script>

<?php

} // isset(messut)

 require('menu.php');?>

</body>


</html>

<?php

} #Login
?>
