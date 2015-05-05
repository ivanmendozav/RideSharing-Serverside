<?php

/**
 * A coordinated measured with a navigation device
 * Attributes: latitude, longitude, timestamp, Optional:altitude
 * Timestamp represents the time the point as measured
 * Timestamp interval between points can be fixed or dynamic (depending on signal strength)
 *
 * @author Ivan
 * @date 30/04/2015
 */
class GpsPoint {
    protected $longitude;
    protected $latitude;
    protected $altitude;
    protected $timestamp; //UNIX timestamp
    
    /**
     * GETTERS
     * @return type
     */
    function getLongitude() {
        return $this->longitude;
    }
    function getLatitude() {
       return $this->latitude;
    }
    function getTimestamp() {
        return $this->timestamp;
    }
    public function getAltitude() {
        return $this->altitude;
    }

            
    /**
     * Gps Point constructor
     * @param float $longitude
     * @param float $latitude
     * @param float $altitude;
     * @param float $timestamp
     */
    public function GpsPoint($longitude,$latitude,$altitude,$timestamp){        
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->altitude = $altitude;
        $this->timestamp = $timestamp;
    }
}