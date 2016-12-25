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
        return $this->query->fetchColumn(); }

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

    public function update($tablename, $valuedict, $wheredict){
        $valuelist = "";

        foreach($valuedict as $key => $value){
            if (!empty($valuelist))
                $valuelist .= ", ";
            $valuelist .= "$key = :$key";
        }

        $this->BuildwhereClause($wheredict);
        $qstring = "UPDATE $tablename SET $valuelist $this->whereclause";
        $this->query = $this->connection->prepare($qstring);
        $this->BindWhereClause();
        foreach($valuedict as $key=>$value){
            //TODO: check the PDO stuff
            $this->query->bindParam(":$key", $valuedict[$key], PDO::PARAM_STR);
        }
        $this->Run();

    }

    public function select($tablename, $columns, $wheredict=Array(), $distinct = '', $orderby = ''){
        //$columns: array, $wheredict: array of arrays, with [0] as column name, [1] as =, not, LIke etc, [2] as the value
        $columnlist = implode($columns,", ");
        //Build the WHERE clause, if present
        $this->BuildwhereClause($wheredict);
        $qstring = "SELECT $distinct $columnlist FROM $tablename $this->whereclause $orderby";
        $this->query = $this->connection->prepare($qstring);
        $this->BindWhereClause();
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

    public function BuildwhereClause($wheredict) {

            $this->wheredict = $wheredict;
            $this->whereclause = "";
            $this->usedkeys = Array();
            if (isset($this->wheredict)){
                foreach($this->wheredict as $condition){
                    if (!empty($this->whereclause))
                        $this->whereclause .= " AND ";
                    else
                        $this->whereclause = "WHERE ";
                    //If this column name already used in a condition
                    if (array_key_exists($condition[0],$this->usedkeys)){
                        $suffix = sizeof($this->usedkeys[$condition[0]]);
                        $this->whereclause .= "$condition[0] $condition[1] :$condition[0]$suffix";
                    }
                    else{
                        $this->usedkeys[$condition[0]] = Array();
                        $suffix = "";
                        $this->whereclause .= "$condition[0] $condition[1] :$condition[0]";
                    }
                    //For cases where multiple conditions for the same column
                    $this->usedkeys[$condition[0]][] = Array($condition[0] . $suffix, $condition[2]);
                }
            }

    }

    public function BindWhereClause(){
        $appliedkeys = Array();
        foreach($this->wheredict as $condition){
            //TODO: check the PDO stuff
            if(!in_array($condition[0],$appliedkeys)){
                foreach($this->usedkeys[$condition[0]] as $thispair){
                    $this->query->bindParam(":$thispair[0]", $thispair[1], PDO::PARAM_STR);
                }
              $appliedkeys[] = $condition[0];
           }
        }
    }

}


?>
