<?php
include "sensorParser.php";
/**
 * Parser for CSV files generated by a accelerometer sensor
 */
class accelerometerParser extends sensorParser{
    //column index : FORMAT:  X,Y,Z, timestamp
    private $_X = 0; private $_Y = 1; private $_Z = 2;  private $_TIMESTAMP = 3;    

    /**
     * Store to db
     * @param type $row
     * @param type $user_id
     */
    protected function parseLine($db,$row, $user_id){
        $x = $row[$this->_X];
        $y = $row[$this->_Y];
        $z = $row[$this->_Z];
        $timestamp = $row[$this->_TIMESTAMP];
        $db->insert("accelerometer_sensor_data", array("x" => $x, "y" => $y, "z" => $z,"timestamp" => $timestamp, "user_id" => $user_id),'',array("float","float","float","string","integer"));
    }
}