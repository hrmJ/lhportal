<?php
session_start();
require('sql/dbutils.php');
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

//Syötä tiedot itse messusta:
$con = new DbCon();
$con->Connect();
foreach($data as $row){
    $con->insert("messut", Array("pvm"=>$row["pvm"],"teema"=>$row["teema"]));
}
?>

<html lang="fi">
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
<head>
<title>Messujen syöttö tietokantaan</title>
</head>
<body>
<p></p>
</body>
</html>
