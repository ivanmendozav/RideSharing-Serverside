<?php

include_once "ModelParameters.php";
include_once "ModelFormulas.php";
include_once "GpsPoint.php";
include_once "StayPoint.php";
include_once "StaysLog.php";
/**
 * Set of n Gps points<lon,lat,timestamp> = {p1,p2,..,pn-1,pn} measured within a time interval
 * Timestamp interval between points can be fixed or dynamic (depending on signal strength)
 *
 * @author Ivan
 * @date 30/04/2015 
 */
class GpsLog {
    protected $gps_points;
    protected $empty;
    
    /**
     * Gps log constructor
     */
    public function GpsLog(){
        $this->empty = true;
    }
    
    /**
     * Adds a point to the Log
     * @param type $gps_point
     */
    public function AddPoint($gps_point){
        $this->gps_points[] = $gps_point;
        $this->empty = false;
    }
    
    /**
     * Retrieve loaded GPS points (loadFromCSV must be called first)
     */
    public function GetPoints(){
        return $this->gps_points;
    }
    
    /**
     * Identity points pm to po of extended dwell for the GPS points in the interval [k,n]
     * Default values are k = 0 and n = length of log
     * A stay point requires distance(pm, pi) <= delta.  For time(pm)< time(pi)< time(pi)<=po
     * And also that time(po)- time(pm) >= epsilon 
     */
    public function GetStayPoints($k = 0, $n = null, $debug = null){
        if(!$debug) {$debug = ModelParameters::$debug_mode; }
        $start = microtime(true);
        $stay_points = null; //New set of stay points
        $delta = ModelParameters::$distance_threshold;
        $epsilon = ModelParameters::$time_threshold;
        $subset_log = null; //set of points {pm,.., po} that form a stay point
        $m = $k;
        if(!$n) {$n = count($this->gps_points);}
        
        while($m < $n-1){
            $found = false;
            //for two succesive points            
            $pm = $this->getPoint($m);            
            $i = $m+1;
            $pi = $this->getPoint($i);
            //count next points within a distance lower than delta            
            while(ModelFormulas::GCDistance($pm, $pi) <= $delta && $i<$n){
                //if($debug){echo $m."->".$i."<br>";}
                $o = $i; $po = $this->getPoint($o); //last point po under requirements
                $subset_log[] = $pi;
                $i++; if($i<$n) {$pi = $this->getPoint($i);}
            }
            if (count($subset_log)>0){
                $subset_log[] = $pm;
                //check whether dwelling time is greater than epsilon    
                $stay_time = abs($po->getTimestamp() - $pm->getTimestamp());
                //if($debug){echo $m."->".$o." : ".$stay_time."ms<br>";}
                if($stay_time >= $epsilon){
                    $found = true;
                    //create stay point from subset
                    $avg_latitude = $this->averageLatitudes($subset_log);
                    $avg_longitude = $this->averageLongitudes($subset_log);
                    $arrival = $pm->getTimestamp();
                    $departure = $po->getTimestamp();
                    $cardinality = count($subset_log);
                    $stay = new StayPoint($avg_latitude,$avg_longitude,$arrival,$departure,$cardinality, null);
                    $stay_points[] = $stay;
                   // if($debug){print_r($stay);echo "<br>";}
                    $m = $i; // proceed searching from last stay point
                }
            }
           //  $m = $i;
            if(!$found)
                $m++; // proceed searching from next point
            $subset_log = null;
        }
        //if($debug) {echo count($stay_points)." stay point(s) were identified.<br>"; echo "Finished in ". (microtime(true)-$start)/1000 . "ms";}
        return $stay_points;
    }

    /**
     * Returns point in $index position (zero-based)
     * @param type $index
     * @return type
     */
    public function getPoint($index){
        return $this->gps_points[$index];
    }
    
    /**
     * Returns average latitude of input GPS points
     * Default whole GPS log
     * @param type $points
     * @return float
     */
    protected function averageLatitudes($points = null){
        if(!$points) {$points = $this->gps_points; }
        $sum = 0; $length = count($points);
        foreach($points as $p){
            $sum+= (float)$p->getLatitude();
        }
        return ($length > 0 ? $sum/$length : 0);
    }
    
    /**
     * Returns average longitude of input GPS points
     * Default whole GPS log
     * @param type $points
     * @return float
     */
    protected function averageLongitudes($points = null){
        if(!$points) {$points = $this->gps_points; }
        $sum = 0; $length = count($points);
        foreach($points as $p){
            $sum+= (float)$p->getLongitude();
        }
        return ($length > 0 ? $sum/$length : 0);
    }
    
    public static function size(){
        return count($this->GetPoints());
    }
    
    /**
     * Instantiates a GPS log object from a CSV file (longitude, latitude, timestamp).
     * Fields must conform ModelParameters static attributes
     * @param type $filename_path
     * @param type $delimiter
     */
    public function loadPointsFromCSV($filename_path, $delimiter = null){
        if(!$delimiter) {$delimiter = ModelParameters::$csv_delimiter;}
        $longitude_index = ModelParameters::$csv_longitude_column;
        $latitude_index = ModelParameters::$csv_latitude_column;
        $altitude_index = ModelParameters::$csv_altitude_column;
        $timestamp_index = ModelParameters::$csv_timestamp_column;
        
        if (($handle = fopen($filename_path, "r")) !== FALSE) {
            $this->gps_points = null;
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {                  
                $longitude = $row[$longitude_index];
                $latitude = $row[$latitude_index];
                $altitude = 0;//$row[$altitude_index];
                $timestamp = $row[$timestamp_index];    
                $this->AddPoint(new GpsPoint($longitude, $latitude, $altitude, $timestamp));
            }                    
        }
        fclose($handle);
    }
}