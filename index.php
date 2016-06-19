<?php
session_start();
require('phputils/essential.php');
AddHeader();
$url = SaveGetParams();
$con = new DbCon();
$con->Connect();

if(isset($_POST)){
    #JOS käyttäjä on päivittänyt jotakin kenttää
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
}

if (!isset($_GET["messuid"]) OR !isset($_GET)){
    if(isset($_GET["vastuu"])){
        $messulist =  CreateMessulist($_GET["vastuu"]);
    }
    else{
        $messulist =  CreateMessulist();
    }
    $vastuulist =  CreateVastuuList();
}
elseif(isset($_GET["messuid"])){
    $h2 = new DomEl("h2","Majakkamessu " . $_GET["pvm"]);
    $h3 = new DomEl("h3", $_GET["teema"]);
    $messulist =  MessuDetails($_GET["messuid"]);
    }

?>

<body>
<form name='updater' id='updater' method="post" action="<?php echo $url;?>">
<?php
if (!isset($_GET["messuid"]) OR !isset($_GET)){
?>
<div>

<span>Tarkastele vastuun perusteella:</span>
<?php echo $vastuulist; ?>

</div>
<?php
}
?>

<?php

if(isset($h2)){
    echo $h2->Show();
    echo $h3->Show();
}

echo $messulist; 

if (isset($_GET))
    echo '<input type="submit" name="updated" value="Tallenna">';
?>
</form>
</body>

<script src="scripts/pohjat.js"></script>
<script src="scripts/essential.js"></script>
<script>
//Add listeners
var messurows = document.getElementsByClassName('messurow');
for(var row_idx = 0; row_idx < messurows.length;row_idx++){
    var messurow = messurows[row_idx];
    messurow.addEventListener('click',SelectMessu,false);
}

var editables = document.getElementsByClassName('editable');
for(var row_idx = 0; row_idx < editables.length;row_idx++){
    var editable = editables[row_idx];
    editable.addEventListener('click',edit,false);
}

var vastuulist = document.getElementById('vastuulist');
if (vastuulist){
    vastuulist.addEventListener('change',SelectVastuu,false);
}

</script>
</html>
