<?php


/**
 * Description of DataMapper
 * @date 04/05/2015
 * @author Ivan
 */
class DataMapper {
    /**
     * Store in database table stay_points
     * id: autoincrement
     * created: default timestamp autofilled
     * enabled: autofilled with 1 (true)
     * @param StayPoint $s
     * @param int $user_id
     */
    public static function SaveStayPoint(StayPoint $s, $user_id){
        try{
            $db = new MySQL(DB_NAME, DB_USER, DB_PASSWORD);
            if($db->lastError){
                throw new Exception ("Couldn't connect to ".DB_NAME.",".DB_USER.",".DB_PASSWORD);}
            //values for insert
            $avg_latitude = $s->getAvg_latitude(); // sum(pi.lat)/|S|  For pm<=pi<=po
            $avg_longitude = $s->getAvg_longitude(); // sum(pi.lon)/|S|  For pm<=pi<=po
            $arrival = $s->getArrival(); // time(pm)
            $departure = $s->getDeparture(); // time(po)
            $cardinality = $s->getCardinality(); // |S|
            $label = $s->getLabel(); //optional description (if filled in by the user)
            $stay_time = $s->getStay_time();
            //prepare statement
            $db->insert("stay_points", array(
                "avg_latitude" => $avg_latitude, 
                "avg_longitude" => $avg_longitude, 
                "arrival" => $arrival, 
                "departure" => $departure, 
                "cardinality" => $cardinality,
                "label" => $label,
                "stay_time" => $stay_time,
                "user_id" => $user_id),'',array("float","float","float","float","integer","string","float","integer"));
            $db->closeConnection();
        }catch(Exception $e){
            $debug_mode = ModelParameters::$debug_mode;
            //log error
            if($debug_mode){echo $e->getMessage();}
        }
    }
    
    /**
     * Validates a username and returns the users table id
     * @param String $username
     * @return int
     */
    public static function checkUser($username){
        $db = new MySQL(DB_NAME, DB_USER, DB_PASSWORD);
        $user = $db->select("users", array("username" => "$username"));
        //print_r($user);
        $db->closeConnection();
        if($user["id"]){
            echo $username." connected.";
            return $user["id"];        
        }
        else{
            echo "user ".$username." not found. changed to Anonymous"; 
            return ANONYMOUS_ID;}
    }
    
     /**
     * Save a point from GPS to the database 
     * @param int $user_id
     * @param GpsPoint $point
     */
    public static function SaveGpsPoint(GpsPoint $point, $user_id){
        
        $latitude = $point->getLatitude();
        $longitude = $point->getLongitude();
        $altitude = $point->getAltitude();
        $timestamp = $point->getTimestamp();
        
        $db = new MySQL(DB_NAME, DB_USER, DB_PASSWORD);  
        $db->insert("gps_sensor_data", array("latitude" => $latitude, "longitude" => $longitude, "altitude" => $altitude, "timestamp" => $timestamp, "user_id" => $user_id),'',array("float","float","float","float","integer"));
        $db->closeConnection();
    }
}
