<?php
include "sensorParser.php";

/**
 * Parser for CSV files generated by a battery pseudo sensor
 */
class stayPointParser extends sensorParser{
    //column index : FORMAT:  charging, usb, ac, level, timestamp
    private $_avg_longitude = 0; private $_avg_latitude= 1; private $_arrival = 2; 
    private $_departure = 3; private $_cardinality = 4; private $_label = 5; 
    private $_start_longitude = 6; private $_start_latitude = 7;  private $_client_id = 8;

    /**
     * @overrides
     * @param type $row
     */
    protected function parseLine($db,$row, $user_id){
        try{
            $_avg_longitude = $row[$this->_avg_longitude];  $_avg_latitude= $row[$this->_avg_latitude];  $_arrival = $row[$this->_arrival]; 
            $_departure = $row[$this->_departure];  $_cardinality = $row[$this->_cardinality];  $_label = $row[$this->_label]; 
            $_start_longitude = $row[$this->_start_longitude];  $_start_latitude = $row[$this->_start_latitude]; $client_id = $row[$this->_client_id];

            if(!$this->checkPoint($db, $user_id, $client_id)){
       //         echo "Cloud Server: stay added.\n";
               $db->insert("stay_points", array("avg_latitude" => $_avg_latitude, "avg_longitude" => $_avg_longitude, 
                   "arrival" => $_arrival, "departure" => $_departure, "cardinality" => $_cardinality, "label" => $_label,
                   "start_longitude" => $_start_longitude,"start_latitude" => $_start_latitude, "user_id" => $user_id, "client_id" => $client_id),'',
                       array("float","float","float","float","integer","string","float","float"));
            }else{
       //         echo "Cloud Server: stay updated.\n";
                $db->update("stay_points", array("departure" => $_departure, "cardinality" => $_cardinality), array("client_id" => $client_id, "user_id" => $user_id), '', array("float", "integer"), array("integer", "integer"));
            }
            echo $db->lastError;
            $db->commit();}
        catch(Exception $e){
            echo $e->getMessage();
            $db->rollbakc();
        }
    }
    
    /**
     * Verifies if a stay point exists
     */
    protected function checkPoint($db,$user_id, $client_id){
        $point = $db->select("stay_points", array("user_id" => "$user_id", "client_id" => $client_id));
        $db->closeConnection();
        if($point["id"]){
            return true;        
        }
        else{
            return false;            
        }
    }
}