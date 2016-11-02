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


?>

<html lang="fi">
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
  <link rel="stylesheet" href="styles/default.css">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<title>Messujen syöttö järjestelmään</title>
</head>
<body>

<div id="songnames">
<?php
FetchSongNames($con);
?>
</div>

<article id='maincontainer'>

        <?php

        $songlist =  SongList($con, $_GET["messuid"], "vsongdiv"); 
        echo $songlist;
        echo "<p><a onClick='AddWsSong(\"Ylistyslaulu\");'>Lisää ylistyslaulu</a></p>";
        echo "<p><a onClick='AddWsSong(\"Ehtoollislaulu\");'>Lisää ehtoollislaulu</a></p>";
        echo "<p><a onClick='AddWsSong(\"Ylistyslaulu\");'>Jumalan karitsan versio</a></p>";
        echo "<p><a onClick='AddWsSong(\"Ylistyslaulu\");'>Pyhä-hymnin versio</a></p>";
        ?>

</article>

<script src="scripts/essential.js"></script>
<script>
    //Add listeners
    var editables = document.getElementsByClassName('editable');
    for(var row_idx = 0; row_idx < editables.length;row_idx++){
        var editable = editables[row_idx];
        var e_row = editable.parentElement.parentElement;
        e_row.addEventListener('click',edit,false);
    }

  $( function() {
      var songnames = [];
      var snamespans = document.getElementsByClassName('songtitleentry');
      for(spidx in snamespans){
          var thisspan = snamespans[spidx];
          if (thisspan.innerText !== undefined){
              songnames.push(thisspan.innerText);
          }
      }
    $( ".songeditinput" ).autocomplete({
      source: songnames
    });
  } );
</script>


</body>

</html>
<?php

} #Login
?>
