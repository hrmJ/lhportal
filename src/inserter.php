<html lang="fi">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Messujen syöttö tietokantaan</title>
</head>
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

$data = Array();
foreach($_POST as $fieldname => $value){
    $pos = strpos($fieldname,'_');
    $number = substr($fieldname,$pos+1);
    $dbfield = substr($fieldname,0,$pos);
    if (!isset($data[$number]) AND $pos){
       $data[$number]  = Array($dbfield=>$value);
    }
    elseif($pos){ 
        end($data);
        $data[$number][$dbfield] = $value;
    }
}

$con = new DbCon();
$con->Connect();
$vastuufields = ListJobs();
foreach($data as $row){
    //Syötä tiedot itse messusta:
    var_dump($row["pvm"]);
    #$con->insert("messut", Array("pvm"=>$row["pvm"],"teema"=>$row["teema"]));
    //Syötä mahdolliset jo tiedossa olevat vastuut + saarnateksti
    $max = $con->maxval("messut","id");
    $vastuudata=Array();
    foreach($vastuufields as $vastuufield){
        $con->insert("vastuut", Array("messu_id"=>$max,"vastuu" => $vastuufield, "vastuullinen" =>$row[$vastuufield]));
        #$con->insert("vastuut", Array("messu_id"=>$max,"vastuu" => $vastuufield, "vastuullinen" =>$row[$vastuufield]));
    }
}


?>
<body>
<p></p>
</body>
</html>
<?php

} #Login
?>
