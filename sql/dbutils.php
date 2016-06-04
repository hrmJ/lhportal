<?php

class DbCon{

    public function Connect(){
        $password = 'testpw';
        $hostname = 'localhost';
        $username = 'testuser';
        $dbname = 'majakka_auth';
        $this->connection = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8", $username, $password);
        // set the error mode to exceptions
        //$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //mysql_set_charset('utf8', $this->connection);  
    }

    public function SelectUser($username,$password){
        $this->query = $this->connection->prepare("SELECT user_id FROM majakka_users WHERE username = :username and password = :password");
        $password = sha1( $password ); //encrypt
        $this->query->bindParam(':username', $username, PDO::PARAM_STR);
        $this->query->bindParam(':password', $password, PDO::PARAM_STR, 40);
        $this->Run();
        return $this->query->fetchColumn();
    }

    public function CheckLogin($userid){
        $this->query = $this->connection->prepare("SELECT username FROM majakka_users WHERE user_id = :user_id");
        $this->query->bindParam(':user_id', $userid, PDO::PARAM_STR);
        $this->Run();
        return $this->query->fetchColumn();
    }

    public function insert($tablename, $valuedict){
        $columnlist = "";
        $valuelist = "";
        //Build the query strings
        foreach($valuedict as $key => $value){
            if (!empty($columnlist))
                $columnlist .= ", ";
            if (!empty($valuelist))
                $valuelist .= ", ";
            $columnlist .= $key;
            $valuelist .= ":$key";
        }
        $qstring = "INSERT INTO $tablename ($columnlist) values ($valuelist)";
        $this->query = $this->connection->prepare($qstring);

        foreach($valuedict as $key=>$value){
            //TODO: check the PDO stuff
            $this->query->bindParam(":$key", $valuedict[$key], PDO::PARAM_STR);
        }
        $this->Run();
    }

    public function select($tablename, $columns, $wheredict=Array()){
        //$columns: array, $wheredict: array of arrays, with [0] as column name, [1] as =, not, LIke etc, [2] as the value
        //TODO: LIKE conditions, e.g. with a leading # in the string + negative conditions

        $columnlist = implode($columns,", ");
        $whereclause = "";
        //Build the WHERE clause, if present
        if (isset($wheredict)){
            foreach($wheredict as $condition){
                if (!empty($whereclause))
                    $whereclause .= " AND ";
                else
                    $whereclause = "WHERE ";
                $whereclause .= "$condition[0] $condition[1] :$condition[0]";
            }
        }
        $qstring = "SELECT $columnlist FROM $tablename $whereclause";
        $this->query = $this->connection->prepare($qstring);

        foreach($wheredict as $condition){
            //TODO: check the PDO stuff
            $this->query->bindParam(":$condition[0]", $condition[2], PDO::PARAM_STR);
        }
        $this->Run();
    }

    public function maxval($tablename, $colname){
        $qstring = "SELECT max($colname) FROM $tablename";
        $this->query = $this->connection->prepare($qstring);
        $this->Run();
        return $this->query->fetchColumn();
    }

    public function Run(){
        try{
            $this->query->execute();
        }
        catch(Exception $e) {
            echo 'Virhe kyselyssä: \n' . $e;
        }
    }

}

function FixEncode($string){
    return utf8_encode(utf8_decode($string)); 
}

/*
iconv_set_encoding("internal_encoding", "UTF-8");
$con->Connect();
$con->insert("messut", Array("pvm"=>"2015-01-01","teema"=>"ööööööööö"));
$con = new DbCon();
$con->Connect();
$con->select("messut", Array("teema","pvm"), Array(Array("teema","LIKE","%öö%"),Array("pvm",">","2014-01-01")));
//$con->select("messut", Array("teema","pvm"));
$res = $con->query->fetchAll();
var_dump($res);
 */

?>
