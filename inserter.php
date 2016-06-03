<html lang="fi">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Messujen syöttö tietokantaan</title>
</head>
<?php
session_start();
require('sql/dbutils.php');
$data = Array();
var_dump($_POST);
die();
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
$vastuufields = Array("Saarnateksti","Liturgi","Saarna","Juonto","Bändi","Sanailija","Pyhis","Klubi");
foreach($data as $row){
    //Syötä tiedot itse messusta:
    $con->insert("messut", Array("pvm"=>$row["pvm"],"teema"=>$row["teema"]));
    //Syötä mahdolliset jo tiedossa olevat vastuut + saarnateksti
    $max = $con->maxval("messut","id");
    $vastuudata=Array();
    foreach($vastuufields as $vastuufield){
        $con->insert("vastuut", Array("messu_id"=>$max,"vastuu" => $vastuufield, "vastuullinen" =>$row[$vastuufield]));
    }
}


?>
<body>
<p></p>
</body>
</html>
