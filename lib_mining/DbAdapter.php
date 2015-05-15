<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DbAdapter
 *
 * @author Ivan
 * @date 14-May-2015
 */
class DbAdapter {
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $conn;
    public $debug = false;
    
    /**
     * Open a new connection
     * @param type $servername
     * @param type $username
     * @param type $password
     * @param type $dbname
     */
    public function DbAdapter($servername, $username, $password, $dbname){
        try{
            // Create connection
            $this->conn = new mysqli($servername, $username, $password, $dbname);
            $this->conn->autocommit(true);
            // Check connection
            if ($this->conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
    
    /**
     * Insert a new record in  table $table
     * @param String $table
     * @param array $contentValues
     * @return boolean
     * @throws Exception
     */
    public function Insert($table, $contentValues){
        try{
            $values =""; $columns = "";
            if(is_array($contentValues)){
                if(count($contentValues)>0){
                    foreach($contentValues as $column => $value){
                        if($values != "") $values .= ",";
                        $values.="'".$this->conn->real_escape_string($value)."'";
                        if($columns != "") $columns .= ",";
                        $columns.=$column;
                    }
                }
                $query = "INSERT INTO $table (".$columns.") VALUES (".$values.")";
                if($this->debug) echo $query."<br>";
                if ($this->conn->query($query) === TRUE) {
                    return true;
                } else {
                    throw new Exception("Couldn't insert in $table: ".$query);
                }
            }
        }  catch (Exception $e){
            $e->getMessage();
        }
    }
    
    /**
     * Retrieves an array of table rows
     * @param String $table
     * @param array $where
     * @return type
     */
    public function Get($table, $where=null){
        $results = array();
        $query = "SELECT * FROM $table ";
        if(is_array($where)){
            $where_clause = "";
            foreach($where as $column => $filter){
                if($where_clause != ""){
                    $where_clause .= " AND ";
                }
                $where_clause.="$column = '".$this->conn->real_escape_string ($filter)."'";  
            }
            $query .= "WHERE ".$where_clause;
        }
        if($this->debug) echo $query."<br>";
        if ($result = $this->conn->query($query)) {
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $results[] = $row;
                }
            }
        }
        return $results;
    }
    
    /**
     * Retrieves an array of table rows
     * @param String $table
     * @param array $contentValues
     * @param array $where
     * @return type
     */
    public function Update($table, $contentValues, $where){
        try{
            $results = array();
            $query = "UPDATE $table";

            //update values
            if(is_array($contentValues)){
                $values = "";
                if(count($contentValues)>0){
                    foreach($contentValues as $column => $value){
                        if($values != "") $values .= ",";
                        $values.=$column."='".$this->conn->real_escape_string ($value)."'";
                    }
                }
                $query .= " SET ".$values;
            }
            //filters
            if(is_array($where)){
                $where_clause = "";
                foreach($where as $column => $filter){
                    if($where_clause != ""){
                        $where_clause .= " AND ";
                    }
                    $where_clause.="$column = '".$this->conn->real_escape_string($filter)."'";  
                }
                $query .= " WHERE ".$where_clause;
            }
            //execute
            if($this->debug) echo $query."<br>";
            if ($this->conn->query($query) === TRUE) {
                return true;
            } else {
                throw new Exception("Couldn't update $table: ".$query);
            }
        }  catch (Exception $e){
            $e->getMessage();
        }
    }
    
    /**
     * Close connection
     */
    public function Close(){
        $this->conn->close();
    }
}

//include_once "config.inc.php";
////test
//$db = new DbAdapter(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//$db->Insert(TABLE_NAME, array("avg_longitude" => 4.54, "avg_latitude" => 50.8, "arrival" => 321654987, "departure" => 654987321, "cardinality" => 100, "label" => "test", "start_longitude" => 4.65, "start_latitude" => 50.89, "user_id" => 2, "client_id" => 100));
//print_r($db->Get(TABLE_NAME, array("user_id" => 2)));
//$db->Update(TABLE_NAME, array("departure" => 654654654), array("client_id" => 100));
//print_r($db->Get(TABLE_NAME, array("client_id" => 100)));
//$db->Close();