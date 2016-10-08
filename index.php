<?php
session_start();
if (isset($_GET["logout"])){
    session_unset();
    session_destroy();
}
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
$con = new DbCon();

    if(isset($_POST["newservices"])){
        #Jos äsken syötetty uusia messuja:
        InsertServices($con);
        echo "<script>window.alert('Uudet messut syötetty onnistuneesti');</script>";
    }

    AddHeader();
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
        #Yksittäinen messunäkymä
        $h2 = new DomEl("h2","Majakkamessu " . FormatPvm($_GET["pvm"]));

        #Teeman muuttaisen mahdollisuus:
        if (array_key_exists("messutheme",$_POST)){
            $_GET["teema"] = $_POST["messutheme"];
        }
        $themeform = new DomEl("form","");
        $themeform->AddAttribute("id","themeupdater");
        $themeform->AddAttribute("name","themeform");
        $themeform->AddAttribute("method","post");
        $themeform->AddAttribute("action",$url);

        #Is this safe from injections?
        $idfield = AddHidden($themeform,"theme_messu_id",$_GET["messuid"]);

        $h3 = new DomEl("h3", $_GET["teema"],$themeform);
        $h3->AddAttribute('id',"themeheader");
        $h3->AddAttribute('class',"editable");
        $h3->AddAttribute('onClick',"AddSaveButton();");

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
                <li><i class='fa fa-bars' id='settings' onClick='ShowSettings();' karia-hidden='true'></i></li>
                <li id='homeli' title='Takaisin alkunäkymään'>Majakkaportaali</li>
                <li><a href='$index'>&#x25c1; Palaa alkuun</a></li>
            </ul>
            </span>";
            echo "</section>";
        }
        ?>

<article id='maincontainer'>

        <?php

        if(isset($h2)){
            echo $h2->Show();
            echo $themeform->Show();
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

<div id='menu'>

    <?php
    $seasonname = $_SESSION["kausi"]["nimi"];
    $nextseason = $url . "&kausi=next&";
    $prevseason = $url . "&kausi=previous&";
    ?>
    <ul>
        <li class='menuli'><a href='#'>Lue ohjeet</a></li>
        <li class='' title='Vaihda edelliseen tai seuraavaan kauteen'> 
            <a href="javascript:void(0);" onClick='SwitchSeason("edellinen");'>
                <i class="fa fa-backward" title="Vaihda edelliseen kauteen" aria-hidden="true"></i>
            </a> 

            <em><?php echo $seasonname; ?></em> 

            <a href="javascript:void(0);" title="Vaihda seuraavaan kauteen" onClick='SwitchSeason("seuraava");'>
                <i class="fa fa-forward" aria-hidden="true"></i>
            </a> 
        </li>
        <li class='menuli'><a href='index.php?logout=Yes'>Kirjaudu ulos</a></li>
        <li class='menuli'><a href='insert_messudata.php'>Syötä uusia messuja</a></li>
        <li class='menuli'><a href='uusivastuu.php'>Syötä uusia vastuutyyppejä</a></li>
    </ul>
</div>

<script>
    //Add listeners
    document.getElementById('homeli').addEventListener('click',function(){window.location='index.php';});
    document.getElementById('settings').addEventListener('click',ShowSettings,false);
    var messurows = document.getElementsByClassName('messurow');
    for(var row_idx = 0; row_idx < messurows.length;row_idx++){
        var messurow = messurows[row_idx];
        messurow.addEventListener('click',SelectMessu,false);
        messurow.addEventListener('mouseover',FixOver,false);
        messurow.addEventListener('mouseout',FixOut,false);
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

    var menu_li_items = document.getElementsByClassName('menuli');
    for(var row_idx = 0; row_idx < menu_li_items.length;row_idx++){
        var row = menu_li_items[row_idx];
        row.addEventListener('click',MenuClick,false);
    }

    var commenticons = document.getElementsByClassName('fa-comments');
    for(var idx = 0; idx < commenticons.length; idx++){
        var icon = commenticons[idx];
        icon.addEventListener('click',CommentClick,false);
    }

</script>

</body>

</html>
<?php

} #Login
?>
