<?php
session_start();
require('phputils/essential.php');
#session_unset();
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
#JOS kirjauduttu onnistuneesti
AddHeader();
$con = new DbCon();
#Jos käyttäjä on päivittänyt jotain tietoja messusta tai messuista, prosessoi dataa:
UpdateMessudata($con);
#Hae url-parametrit talteen
$url = SaveGetParams();

if (!isset($_GET["messuid"]) OR !isset($_GET)){
    $vastuu = '';
    if(isset($_GET["vastuu"])){
        $vastuu = $_GET["vastuu"];
        if(in_array($_GET["vastuu"],Array("Yleisnäkymä","----"))){
            $vastuu = "";
        }
    }
    $messulist =  CreateMessulist($vastuu, $url);
    $vastuulist =  CreateVastuuList();
}
elseif(isset($_GET["messuid"])){
    $h2 = new DomEl("h2","Majakkamessu " . FormatPvm($_GET["pvm"]));
    $h3 = new DomEl("h3", $_GET["teema"]);
    $messulist =  MessuDetails($_GET["messuid"]);
    }

?>

<body>


        <?php
        if (sizeof($_GET)>0){
            # Muu kuin alkunäkymä
            require('nav.php');
        }
        if (!isset($_GET["messuid"]) OR !isset($_GET)){
            # Alkunäkymä
            require('alkunav.php');
        }
        if(isset($h2)){
            $index = $_SERVER['PHP_SELF'];
            echo "<section id='leftbanner'>";
            echo"<span class='menuleft'>
            <ul>
                <li id='homeli' title='Takaisin alkunäkymään'>Majakkaportaali</li>
                <li><a id='help' title='Lue ohjeet!'>?</a></li>
                <li><a href='$index' title='Lue ohjeet!'>&#x25c1; Palaa alkuun</a></li>
            </ul>
            </span>";
            echo "</section>";
        }
        ?>

<article id='maincontainer'>

        <?php

        if(isset($h2)){
            echo $h2->Show();
            echo $h3->Show();
        }

        echo $messulist; 

        if (isset($_GET["pvm"])){
        ?>
        <section id="comments">
            <form name='commentform' id='commentform' method="post" action="<?php echo $url;?>">
            <input class='hidden' value="<?php echo $_GET['messuid'];?>" name="messu_id_comments">
                <a href='javascript:void(0);' onClick='AddComment();'>&#x25b7; Lisää infoasia/kommentti/kysymys/yms.</a>
            </form>
        <?php
            LoadComments($con);
            echo "</section>";
        }
        ?>

</article>

</body>

<script src="scripts/pohjat.js"></script>
<script src="scripts/essential.js"></script>
<script>
    //Add listeners
    document.getElementById('homeli').addEventListener('click',function(){window.location='index.php';});
    var messurows = document.getElementsByClassName('messurow');
    for(var row_idx = 0; row_idx < messurows.length;row_idx++){
        var messurow = messurows[row_idx];
        messurow.addEventListener('click',SelectMessu,false);
    }

    var editables = document.getElementsByClassName('editable');
    for(var row_idx = 0; row_idx < editables.length;row_idx++){
        var editable = editables[row_idx];
        var e_row = editable.parentElement.parentElement;
        e_row.addEventListener('click',edit,false);
    }

    var vastuulist = document.getElementById('vastuulist');
    if (vastuulist){
        vastuulist.addEventListener('change',SelectVastuu,false);
        var vastuu = getURLParameter('vastuu');
        if(vastuu != null){
            vastuulist.value=vastuu;
        }
        else{
            vastuulist.value='Yleisnäkymä';
        }
    }

</script>
</html>
<?php

} #Login
?>
