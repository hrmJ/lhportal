<?php
session_start();
if (isset($_GET["logout"])){
    session_unset();
    session_destroy();
}
$loginfail = True;
require('phputils/essential.php');
#session_unset();
if (!isset( $_SESSION['user_id'] )){
    if (isset($_POST["username"],$_POST["password"])){
        $valid = validate_login($_POST["username"],$_POST["password"]);
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

    AddHeader();
    #Jos käyttäjä on päivittänyt jotain tietoja messusta tai messuista, prosessoi dataa:
    UpdateMessudata($con);
    #Hae url-parametrit talteen
    $url = SaveGetParams();
    #Aseta vastuulista tyhjäksi ja muuta tätä, jos messulistanäkymässä
    $vastuulist = False;

    if (!isset($_GET["messuid"]) OR !isset($_GET)){
        $vastuu = '';
        if(isset($_GET["vastuu"])){
            $vastuu = $_GET["vastuu"];
            if(in_array($_GET["vastuu"],Array("Yleisnäkymä","----"))){
                $vastuu = "";
            }
        }
        $messulist =  CreateMessulist($con, $vastuu,$url);
        $vastuulist =  True;
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
        $themeform->AddAttribute("action","URL_RPL");

        #Is this safe from injections?
        $idfield = AddHidden($themeform,"theme_messu_id",$_GET["messuid"]);

        $h3 = new DomEl("h3", $_GET["teema"],$themeform);
        $h3->AddAttribute('id',"themeheader");
        $h3->AddAttribute('class',"editable");
        $h3->AddAttribute('onClick',"AddSaveButton();");

        $messulist =  MessuDetails($_GET["messuid"],$url);
        }

?>

<body>


<?php
CreateNavi($vastuulist, $url, False);
?>

<article id='maincontainer'>


        <div id="instructiontext">  </div>

        <?php

        if(isset($h2)){
            echo $h2->Show();
            echo $themeform->Show();
            ?>
            <div class="some_mtop" ><a class="unhider" href="javascript:void(0)">Näytä kolehtitiedot</a></div>
            <div class="below_hidden">
                <div class='labeldiv'>
                    <div>Kolehtikohde</div>
                    <div>
                        <select name="kolehtikohde">
                            <option>Myanmar</option>
                            <option>Kimbilio</option>
                        </select>
                    </div>
                </div>
                <div class='labeldiv'>
                    <div>Tarkempi tavoite</div>
                    <div id='tarkempitavoite'>
                        <select name="kolehti_tavoite">
                            <option>Uusi tavoite</option>
                        </select>
                    </div>
                    <div>Kokonaistavoite</div><div><input name="total_goal"></div>
                </div>
                <div class="labeldiv">
                     <div>Tavoitteen kuvaus:</div> 
                    <div><textarea name="kolehti_description" placeholder="Kirjoita tähän halutessasi kuvaus tästä nykyisestä tavoitteesta: mitä tämän asian kerääminen merkitsisi / mitä sillä voitaisiin saada aikaan kolehtikohteessa."></textarea></div>
                </div>
                <div class="labeldiv">
                     <div>Kerättiin (€):</div> 
                    <div><input type="text" pattern="[0-9]+([\.,][0-9]+)?" name="kolehti_amount" value="0" step="0.10"></div>
                </div>
                <div class="some_mtop">
                    <button id="save_kolehti">Tallenna</button>
                </div>
            </div>
            <?php
        }

        echo $messulist; 

        if (isset($_GET["pvm"])){
            $songlist =  SongList($con, $_GET["messuid"]); 
            if ($songlist !== False){ 
                echo "<section id='songs'>";
                    echo "<p class='centerp'><a href='javascript:void(0);' id='songswitch' title='Näytä messussa soitettavat laulut' onClick='ShowSongList();'>Näytä messun laulut</a></p>\n";
                    echo $songlist;
                echo "</section>";
            }
            ?>
            <section id="comments">
                <h4>Huomioita messusta</h4>
                <form name='commentform' id='commentform' method="post" action="<?php echo $url;?>">
                    <input class='hidden' value="<?php echo $_GET['messuid'];?>" name="messu_id_comments">
                    <textarea name="newcomment_text" id="cm1" class="commenttext" placeholder="Lisää huomio tai kommentti..." onClick='ExpandComment(this);'></textarea>

            <div class='commentadder hidden' id='themechooser'>
            <?php
                //Lisää aihevalitsin
                $vlist = CreateVastuuList();
                echo $vlist->Show();
            ?>
            </div>
            </form>
            <form name='comment_edit_form' id='comment_edit_form' method="post" action="<?php echo $url;?>">
            <?php
                LoadComments($con);
            }
            ?>
            <input type='text' class='hidden' id='edited_comment_id' name='edited_comment_id'>
            <input type='text' class='hidden' id='deleted_comment_id' name='deleted_comment_id'>
            <input type='submit' class='hidden' id='submit_comment_edits' name='cmdeletesub' value='toteuta'>
            </form>
            </section>

</article>

<?php require('menu.php');?>

<script>
    //HACK: setting form urls without ampersands

        $(document).ready(function(){
        
            $("[action='URL_RPL']").each(function(){
                $(this).attr("action","<?php echo $url; ?>")
            });
        
        });

    //Add listeners
    document.getElementById('homeli').addEventListener('click',function(){window.location='index.php';});
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
        if (editable.tagName=="TD"){
            var editable = editable.parentElement.parentElement;
        }
        editable.addEventListener('click',edit,false);
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

    var instrdiv = document.getElementById("instructiontext");
    var table = document.getElementById("vastuulisttable");
    if(table !== undefined && table !== null){
        if(table.getElementsByTagName("TR").length<4){
            instrdiv.appendChild(TagParent("p",[TagWithText("em","Muistathan, että voit lisätä uusia vastuita ja messuja vasemman yläkulman valikosta kohdasta Ylläpito.","")]),"");
        }
    }

    <?php
        //NEWSFEED:
        if(!isset($h2)){
    ?>

        $(document).ready(function(){
            $('<div id="news"><a class="simplelink" href="files/kysely.pptx">Majakkamessun kyselyn tulokset</a></div>').prependTo("#contentlist").addClass("comment_container");
        });


    <?php
    }
        else{

    ?>

        
        
        
    <?php
        
    }

    ?>



</script>

</body>

</html>
<?php

} #Login
?>
