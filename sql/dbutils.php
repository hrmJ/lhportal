<?php

class DbCon{

    public function Connect(){
        $password = 'testpw';
        $hostname = 'localhost';
        $username = 'testuser';
        $dbname = 'majakka_auth';
        $this->connection = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        // set the error mode to exceptions
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

?>
