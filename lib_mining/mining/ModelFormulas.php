<?php

/**
 * List of general formulas to be used along the app
 *
 * @author Ivan
 */
class ModelFormulas {
    /**
     * Great-circle distance between two GPS points p1, p2 (Arc length between two locations on Earth)
     * Earth radius r ~ 6371 km.
     * Distance = r*delta. With delta the central angle = arccos(sin(lat1)*sin(lat2) + cos(lat1)*cos(lat2)*cos(lon1-lon2))
     * @param type $p1
     * @param type $p2
     */
    public static function GCDistance(GpsPoint $p1,GpsPoint $p2, $r=6371){    
        $lat1 = deg2rad($p1->getLatitude()); 
        $lon1 = deg2rad($p1->getLongitude()); 
        $lat2 = deg2rad($p2->getLatitude()); 
        $lon2 = deg2rad($p2->getLongitude());     
        
        $delta = acos(sin($lat1)*sin($lat2) + cos($lat1)*cos($lat2)*cos(abs($lon1-$lon2)));
        $distance = round($r*$delta,4);
        //echo $distance."</br>";
        return $distance; //round to cm.
    }
    /**
     * AVOID TO USE: Only for testing (not real distance, use great-circle distance instead)
     * Euclidean distance between two GPS points
     * For 2 dimensions : pithagorean distance
     * @param type $p1
     * @param type $p2
     */
    public static function EDistance($p1, $p2){
        $lat1 = $p1->getLatitude(); 
        $lon1 = $p1->getLongitude(); 
        $lat2 = $p2->getLatitude(); 
        $lon2 = $p2->getLongitude(); 
        
        return round(sqrt(pow($lat1-$lat2,2) + pow($lon1-$lon2,2)),3);
    }
    
    /**
     * Retrieves a CSV file (longitude, latitude, timestamp) from multiple TXT files(x,x,x,time,lat_min,x,lon_min,x,x,x,x,x,x,x,x).
     * Fields must conform ModelParameters static attributes
     * @param type $folder_path
     * @param type $delimiter
     */
    public static function loadDataFromTxtFiles($folder_path, $csv_path, $delimiter = null){
        if(!$delimiter) {$delimiter = ModelParameters::$csv_delimiter;}        
        $latitude_minutes_index = ModelParameters::$txt_latitude_column;
        $longitude_minutes_index = ModelParameters::$txt_longitude_column;
        $time_index = ModelParameters::$txt_timestamp_column;
        
        $folder_handle = opendir($folder_path);
        $prev_id=0;$text = "";$count=0;
        //parse folder
        while (false !== ($filename_path = readdir($folder_handle))) {
            if(($filename_path != ".") && ($filename_path != ".." && !is_dir($folder_path."/".$filename_path))){
                //echo "loading ".$filename_path."<br>";
                $file_array = explode("_", $filename_path);
                $user_id = substr($file_array[0],-3); 
                //if user changes
                if($prev_id!=$user_id) {
                    if($text != ""){
                        //store csv for single user/date
                        $csv_full_filename = $prev_id."_".$date.".csv";
                        ModelFormulas::writeCSV($folder_path,$csv_path,$csv_full_filename, $text, $count);
                    }
                    $text = ""; $count=0;             
                }
                $date = $file_array[1]; 
                $day = substr($date, 0,2);$month = substr($date, 2,2);$year = (int) substr($date, 4,2) + 2000;
                if($date){
                    if (($handle = fopen($folder_path."/".$filename_path, "r")) !== FALSE) {                
                        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {     
                            //parse txt
                            $latitude_minutes = $row[$latitude_minutes_index];
                            $longitude_minutes = $row[$longitude_minutes_index];
                            $timestamp = $row[$time_index];    $time_array = explode(".",$timestamp); 
                            $hours = substr($time_array[0],0,2); $minutes = substr($time_array[0],2,2); $seconds = substr($time_array[0],4,2);

                            //if data is present
                            if($latitude_minutes && $longitude_minutes && $timestamp){
                                //conversion
                                $latitude = floor($latitude_minutes/100)+( ($latitude_minutes/60) - floor($latitude_minutes/60) );
                                $longitude = floor($longitude_minutes/100)+( ($longitude_minutes/60) - floor($longitude_minutes/60) );
                                $datetime = new DateTime("$year-$month-$day $hours:$minutes:$seconds");
                                $unix_timestamp = $datetime->getTimestamp()*1000;//(($hours*3600)+($minutes*60)+$seconds)*1000;
                                //merge in csv format
                                $text .= $latitude.",".$longitude.",".$unix_timestamp."\n";
                                $count++;
                            }
                        }        
                        fclose($handle);
                    }
                }
                $prev_id = $user_id;
            }
        }
        if($text != ""){
            //store csv for single user/date
            $csv_full_filename = $prev_id."_".$date.".csv";
            ModelFormulas::writeCSV($folder_path,$csv_path,$csv_full_filename, $text, $count);
        }
        StaysLog::writeFromCSVFolder($folder_path."/".$csv_path);
    }
    
    /**
     * Write the CSV for points and stay points
     * @param type $csv_full_filename
     * @param type $text
     * @param type $count
     */
    public static function writeCSV($folder_path,$csv_path,$csv_full_filename, $text, $count){
        //store csv for single user/date points
        $csv_file = fopen($folder_path."/".$csv_path."/".$csv_full_filename, "w");
        fwrite($csv_file, $text);
        fclose($csv_file);
        echo "File ".$csv_full_filename." created ".$count." points collected<br>";
        ModelFormulas::CsvToKml($folder_path,$csv_path,$csv_full_filename, $csv_full_filename.".kml");

        //identify stay points
        $log = new GpsLog();
        date_default_timezone_set("Europe/Brussels");
        $_text = ""; $count = 0;
        $log->loadPointsFromCSV($folder_path."/".$csv_path."/".$csv_full_filename);
        $stays = $log->GetStayPoints();
        if(isset($stays)){
            foreach($stays as $s)
            {
                $_text .= $s->getAvg_latitude().", ".$s->getAvg_longitude().", ".$s->getArrival().",".$s->getDeparture().",".$s->getCardinality()."\n";
                $count++;
            }   
            if($_text != ""){
                $csv_file = fopen($folder_path."/".$csv_path."/"."stays_".$csv_full_filename, "w");
                fwrite($csv_file, $_text);
                fclose($csv_file);
                echo "File stays".$csv_full_filename." created ".$count." stays points identified<br>";
                ModelFormulas::CsvToKml($folder_path,$csv_path,"stays_".$csv_full_filename, "kstays_".$csv_full_filename.".kml");
                
            }
        }
    }
    
    /**
     * Parse CSV and convert to KML for Google Earth
     * @param type $folder_path
     * @param type $csv_path
     * @param type $csv_full_filename
     * @param type $kml_filename
     */
    public static function CsvToKml($folder_path,$csv_path,$csv_full_filename, $kml_filename){
        $text = '<?xml version="1.0" encoding="UTF-8"?>
                <kml xmlns="http://earth.google.com/kml/2.0">
                <Document>';        
        $latitude_index = ModelParameters::$csv_latitude_column;
        $longitude_index = ModelParameters::$csv_longitude_column;     
        if (($handle = fopen($folder_path."/".$csv_path."/".$csv_full_filename, "r")) !== FALSE) {                
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
            $kml_file = fopen($folder_path."/".$csv_path."/".$kml_filename, "w");
            fwrite($kml_file, $text);
            fclose($kml_file);
            echo "File ".$kml_filename." created<br>";
        }
    }    
}