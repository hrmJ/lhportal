<?php
require('sql/dbutils.php');
session_start();

$message = "moro";
if(!isset($_SESSION['user_id'])){
    $message = 'You must be logged in to access this page';
}
else
{
    $con = new DbCon();
    $con->Connect();
    $username = $con->CheckLogin($_SESSION['user_id']);
        if($username == false)
            $message = 'Access Error';
        else
            $message = 'Welcome '.$phpro_username;
}

?>

<html lang="fi">
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
<title>Majakkaportaali</title>
</head>
<body>
<h2><?php echo $message; ?></h2>
<a href='auth/logout.php'>Kirjaudu ulos</a>
</body>
</html>
