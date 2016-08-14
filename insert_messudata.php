
<?php

session_start();
require('phputils/essential.php');

#login:
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


?>

<html lang="fi">
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
  <link rel="stylesheet" href="styles/default.css">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>
  $(function() {
    $( "#first" ).datepicker();
    $( "#last" ).datepicker();
  });
  </script>

<title>Messujen syöttö järjestelmään</title>
</head>

<body>

    <section id='leftbanner'>
        <ul>
            <li><i id='settings' class="fa fa-cog" aria-hidden="true"></i> </li>
            <li id='homeli' title='Takaisin alkunäkymään'>Majakkaportaali</li>
            <li style='margin-right:0px;'><a id='help' title="Lue ohjeet!">?</a></li>
        </ul>
    </section>

    <h2 id='syotto_ots'>Uusien messujen / kausien syöttö</h2>


    <form id='messusyotto' action="index.php" method="post" >

        <div id='slistcont' class='withsomemargin'>
            <?php ListSeasons(); ?>
        </div>

        <div id='syottodiv'>
            <label for="first">Ensimmäinen su</label>
            <input type="text" id="first" name="first" value="" maxlength="20" />

            <label for="last">Viimeinen su</label>
            <input type="text" id="last" name="last" value="" maxlength="20" />

            <input type = "button" onClick='CreateInputs();' value = "Luo pohjat" />

                <input value='Tallenna' class='hidden' type='submit' name='newservices' id='s1'>
        </div>

    </form>


</body>

<script src="scripts/pohjat.js"></script>
<script>
document.getElementById('homeli').addEventListener('click',function(){window.location='index.php';});
document.getElementById('seasonlist').addEventListener('change',NewSeason,false);

</script>

</html>

<?php

} #Login
?>
