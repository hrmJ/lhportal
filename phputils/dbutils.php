<?php

class DbCon{

    public function __construct () {
        $this->Connect();
    }

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

    public function select($tablename, $columns, $wheredict=Array(), $distinct = ''){
        //$columns: array, $wheredict: array of arrays, with [0] as column name, [1] as =, not, LIke etc, [2] as the value
        $columnlist = implode($columns,", ");
        $whereclause = "";
        //Build the WHERE clause, if present
        $usedkeys = Array();
        if (isset($wheredict)){
            foreach($wheredict as $condition){
                if (!empty($whereclause))
                    $whereclause .= " AND ";
                else
                    $whereclause = "WHERE ";
                //If this column name already used in a condition
                if (array_key_exists($condition[0],$usedkeys)){
                    $suffix = sizeof($usedkeys[$condition[0]]);
                    $whereclause .= "$condition[0] $condition[1] :$condition[0]$suffix";
                }
                else{
                    $usedkeys[$condition[0]] = Array();
                    $suffix = "";
                    $whereclause .= "$condition[0] $condition[1] :$condition[0]";
                }
                //For cases where multiple conditions for the same column
                $usedkeys[$condition[0]][] = Array($condition[0] . $suffix, $condition[2]);
            }
        }
        $qstring = "SELECT $distinct $columnlist FROM $tablename $whereclause";
        $this->query = $this->connection->prepare($qstring);

        $appliedkeys = Array();
        foreach($wheredict as $condition){
            //TODO: check the PDO stuff
            if(!in_array($condition[0],$appliedkeys)){
                foreach($usedkeys[$condition[0]] as $thispair){
                    $this->query->bindParam(":$thispair[0]", $thispair[1], PDO::PARAM_STR);
                }
              $appliedkeys[] = $condition[0];
           }
        }
        $this->Run();
        return $this->query;
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



/*
$con = new DbCon();
$res = $con->select("messut",Array("pvm","teema"),Array(Array("pvm",">=","2016-11-09"),Array("pvm","<=","2016-11-19")));
$all = $res->fetchAll();
var_dump($all);
 */

?>
