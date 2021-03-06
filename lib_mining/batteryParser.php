<?php
include "sensorParser.php";

/**
 * Parser for CSV files generated by a battery pseudo sensor
 */
class batteryParser extends sensorParser{
    //column index : FORMAT:  charging, usb, ac, level, timestamp
    private $_CHARGING = 0; private $_USB = 1; private $_AC = 2; private $_LEVEL = 3; private $_TIMESTAMP = 4;    

    /**
     * @overrides
     * @param type $row
     */
    protected function parseLine($db,$row, $user_id){
        $charging = $row[$this->_CHARGING];
        $usb = $row[$this->_USB];
        $ac = $row[$this->_AC];
        $level = $row[$this->_LEVEL];
        $timestamp = $row[$this->_TIMESTAMP];

        $db->insert("battery_sensor_data", array("charging" => $charging, "usb" => $usb, "ac" => $ac, "level" => $level, "timestamp" => $timestamp, "user_id" => $user_id),'',array("integer","integer","integer","float","float","integer"));
    }
}