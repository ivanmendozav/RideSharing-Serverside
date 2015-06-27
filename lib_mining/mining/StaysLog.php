<?php
/**
 * Set of n Gps points<lon,lat,timestamp> = {p1,p2,..,pn-1,pn} measured within a time interval
 * Timestamp interval between points can be fixed or dynamic (depending on signal strength)
 *
 * @author Ivan
 * @date 30/04/2015 
 */
class StaysLog {
    protected $stay_points;
    protected $empty;
    
    /**
     * Gps log constructor
     */
    public function StaysLog(){
        $this->empty = true;
    }
    
    /**
     * Adds a point to the Log
     * @param type $stay_point
     */
    public function AddPoint($stay_point){
        $stay_point->id = count($this->stay_points)+1;
        $this->stay_points[] = $stay_point;
        $this->empty = false;
    }
    
    /**
     * Retrieve loaded stay points (loadFromCSV must be called first)
     */
    public function GetPoints(){
        return $this->stay_points;
    }
    
    /**
     * Whether all points have been visited by dbscan
     */    
    public function AllPointsVisited(){
        $all = true;
        foreach($this->stay_points as $point){
            if($point->visited==false)
            {return false;}
        }
        return true;
    }
    
    /**
     * Identity points pm to po of extended dwell for the GPS points in the interval [k,n]
     * Default values are k = 0 and n = length of log
     * A stay point requires distance(pm, pi) <= delta.  For time(pm)< time(pi)< time(pi)<=po
     * And also that time(po)- time(pm) >= epsilon 
     */
    public function GetPOIs($debug = null){
        if(!$debug) {$debug = ModelParameters::$debug_mode; }
        $maxDistance = ModelParameters::$search_radius;
        $minNeighborPts = ModelParameters::$cluster_size;
        $clustering = null; //set of points {pm,.., po} that form a stay point
        
        $nextClusterID = 1;
        while(!$this->AllPointsVisited()){
            $point = $this->GetNextUnvisitedPoint();
            $point->visited=true;
            $neighbors = $this->GetNeighborhoodByDistance($point, $maxDistance);
            if ($neighbors->size() < $minNeighborPts){
                $point->noise=true;
            }else{
                $cluster = new Cluster($nextClusterID);
                $nextClusterID++;
                $cluster->AddPoint($point);
                $cluster = $this->ExpandClusterByDistance($cluster, $neighbors, $maxDistance, $minNeighborPts);
                $clustering[] = $cluster;
            }                
        }
        return $clustering;
    }
    
    public function GetNextUnvisitedPoint(){
        foreach($this->stay_points as $point){
            if($point->visited == false)
                return $point;
        }
        return null;
    }
    
    /**
     * Merge density-connected points
     * @param Cluster $cluster
     * @param StaysLog $neighbors
     * @param double $maxDistance
     * @param int $minNeighborPts
     * @return type
     */
    protected function ExpandClusterByDistance(Cluster $cluster, StaysLog $neighbors, $maxDistance, $minNeighborPts){
        foreach($neighbors->GetPoints() as $neighbor){
            $neighbor->visited= true;
            $moreNeighbors = $this->GetNeighborhoodByDistance($neighbor, $maxDistance);
            if(!$neighbor->hasCluster()){
                $cluster->AddPoint($neighbor);
            }
            if ($moreNeighbors->size() >= $minNeighborPts){
                $cluster = $this->ExpandClusterByDistance($cluster, $moreNeighbors, $maxDistance, $minNeighborPts);
            }             
        }
        return $cluster;
    }

    /**
     * Retrive neraby points
     * @param type $point
     * @param type $maxDistance
     * @return type
     */
    protected function GetNeighborhoodByDistance($point, $maxDistance){
        $neighborhood = new StaysLog();
        if (count($this->stay_points) > 0){     
            foreach($this->stay_points as $p){
                if($p->id != $point->id && !$p->visited && !$p->noise){
                    $p1 = new GpsPoint($point->getAvg_longitude(), $point->getAvg_latitude(), 0, 0);
                    $p2 = new GpsPoint($p->getAvg_longitude(), $p->getAvg_latitude(), 0, 0);
                    if (ModelFormulas::GCDistance($p1,$p2) <= $maxDistance){
                        $neighborhood->AddPoint($p);
                    }
                }
            }
        }
        return $neighborhood;
    }
    
    /**
     * Returns point in $index position (zero-based)
     * @param type $index
     * @return type
     */
    public function getPoint($index){
        return $this->stay_points[$index];
    }
    
    /**
     * Returns average latitude of input GPS points
     * Default whole GPS log
     * @param type $points
     * @return float
     */
    protected function averageLatitudes($points = null){
        if(!$points) {$points = $this->stay_points; }
        $sum = 0; $length = count($points);
        foreach($points as $p){
            $sum+= (float)$p->getAvg_latitude();
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
        if(!$points) {$points = $this->stay_points; }
        $sum = 0; $length = count($points);
        foreach($points as $p){
            $sum+= (float)$p->getAvg_longitude();
        }
        return ($length > 0 ? $sum/$length : 0);
    }
    
    public function size(){
        return count($this->GetPoints());
    }
    
    /**
     * Instantiates a Stays log object from a CSV file (longitude, latitude, arrival, departure, cardinality).
     * Fields must conform ModelParameters static attributes
     * @param type $full_csv_path
     */
    public static function writeFromCSVFolder($full_csv_path){
        $folder_handle = opendir($full_csv_path);
        $latitude_index = ModelParameters::$csv_latitude_column;
        $longitude_index = ModelParameters::$csv_longitude_column;
        $arrival_index = ModelParameters::$csv_arrival_column;
        $departure_index = ModelParameters::$csv_departure_column;
        $cardinality_index = ModelParameters::$csv_cardinality_column;
        $prev_id = 0;
        $log = new StaysLog();
        //parse folder
        while (false !== ($filename_path = readdir($folder_handle))) {
            
            if(($filename_path != ".") && ($filename_path != "..") && (!is_dir($full_csv_path."/".$filename_path))){
            $file_array = explode("_", $filename_path);
            $prefix = $file_array[0]; $user_id = $file_array[1];                 
                if ($prefix == "stays"){
               //if user changes
                   if($prev_id!=$user_id) {
                       if($log->size() > 0){
                           //store csv for single user/date
                           $pois_full_filename = "pois_".$prev_id.".csv";
                           StaysLog::writeCSV($full_csv_path,$pois_full_filename, $log, $count);
                       }
                       $log = new StaysLog(); $count=0;             
                   }
                   if (($handle = fopen($full_csv_path."/".$filename_path, "r")) !== FALSE) {    
                       while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                           $avg_latitude = $row[$latitude_index];
                           $avg_longitude = $row[$longitude_index];
                           $arrival = $row[$arrival_index];
                           $departure = $row[$departure_index];
                           $cardinality = $row[$cardinality_index];                        
                           //instance stay point prototype
                           $log->AddPoint(new StayPoint($avg_latitude,$avg_longitude,$arrival,$departure,$cardinality, null));
                       } 
                   }
                   $prev_id = $user_id;
               }
            }
        }
        if($log->size() > 0){
            //store csv for single user/date
            $pois_full_filename = "pois_".$prev_id.".csv";
            StaysLog::writeCSV($full_csv_path,$pois_full_filename, $log, $count);
        }
    }
    
    /**
     * Write a CSV with POIs
     * @param type $full_csv_path
     * @param type $pois_full_filename
     * @param type $log
     * @param type $count
     */
    public static function writeCSV($full_csv_path,$pois_full_filename, $log, $count=0){
        $_text = "";
        $POIs = $log->GetPOIs(); $count=0;
        if(isset($POIs)){
            foreach($POIs as $cluster){
                $_text .= $cluster->getLabel().", ".$cluster->getAvg_latitude().", ".$cluster->getAvg_longitude().",".$cluster->getCardinality()."\n";
                $count++;
            }   
            if($_text != ""){
                $csv_file = fopen($full_csv_path."/".$pois_full_filename, "w");
                fwrite($csv_file, $_text);
                fclose($csv_file);
                echo "File ".$pois_full_filename." created ".$count." points-of-interest identified<br>";
                StaysLog::CsvToKml($full_csv_path,$pois_full_filename,"k".$pois_full_filename.".kml");
            }
        }
    }
    
    /**
     * Parse CSV and convert to KML for Google Earth
     * @param type $full_csv_path
     * @param type $pois_full_filename
     * @param type $kml_filename
     */
    public static function CsvToKml($full_csv_path,$pois_full_filename,$kml_filename){
        $text = '<?xml version="1.0" encoding="UTF-8"?>
                <kml xmlns="http://earth.google.com/kml/2.0">
                <Document>';        
        $latitude_index = ModelParameters::$csv_poi_latitude_column;
        $longitude_index = ModelParameters::$csv_poi_longitude_column;  
        if (($handle = fopen($full_csv_path."/".$pois_full_filename, "r")) !== FALSE) {                
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) { 
                $latitude = $row[$latitude_index];
                $longitude = $row[$longitude_index];
                $text.= '<Placemark>
                        <Point><coordinates> '.$longitude.','.$latitude.',0</coordinates></Point>
                        </Placemark>';
            }
            $text.='</Document></kml>';
        }
        if($text != ""){
            $kml_file = fopen($full_csv_path."/".$kml_filename, "w");
            fwrite($kml_file, $text);
            fclose($kml_file);
            echo "File ".$kml_filename." created<br>";
        }
    }  
}
/**
 * To store near stay points
 */
class Cluster{
    protected $label;
    protected $id;
    protected $points;
    
    public function Cluster($id){
        $this->id = $id;
        $this->label = "P".$id;
    }
    
    public function AddPoint($stay_point){
        $this->points[] = $stay_point;
    }
    
    public function GetPoints(){
        return $this->points;
    }
    
    public function getLabel(){
        return $this->label;
    }
    
    public function getAvg_latitude(){
        $points = $this->points;$sum = 0; $length = count($points);
        foreach($points as $p){ $sum+= (float)$p->getAvg_latitude();}
        return ($length > 0 ? $sum/$length : 0);
    }
    
    public function getAvg_longitude(){
        $points = $this->points;$sum = 0; $length = count($points);
        foreach($points as $p){ $sum+= (float)$p->getAvg_longitude();}
        return ($length > 0 ? $sum/$length : 0);
    }
    
    public function getCardinality(){
        return count($this->points);
    }

}