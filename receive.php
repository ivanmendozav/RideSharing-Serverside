<?php
include_once "lib_mining/config.inc.php";

$sensor_id = filter_input(INPUT_POST,"sensor_id",FILTER_VALIDATE_INT);
$username = filter_input(INPUT_POST,"username",FILTER_DEFAULT); 
//error_reporting(E_ALL);

   if ($_FILES["uploadedfile"]["error"] == UPLOAD_ERR_OK)       
   {      
     $tmp_name = $_FILES["uploadedfile"]["tmp_name"];     
     $filename = $_FILES["uploadedfile"]["name"];         
     
     if(move_uploaded_file($tmp_name, UPLOAD_DIR."/$filename")){ 
         chmod(UPLOAD_DIR."$filename",0777);
        //Parse each file
        if ($sensor_id == BATTERY){            
            include_once "lib_mining/batteryParser.php";
            $parser = new batteryParser($filename, $username);
            $parser->parse();
        }
        if ($sensor_id == ACCELEROMETER){
            include_once "lib_mining/accelerometerParser.php";
            $parser = new accelerometerParser($filename, $username);
            $parser->parse();
        }
        if ($sensor_id == WIFI){
            include_once "lib_mining/wifiParser.php";
            $parser = new wifiParser($filename, $username);
            $parser->parse();
        }
        if ($sensor_id == GPS){
            include_once "lib_mining/gpsParser.php";
            $parser = new gpsParser($filename, $username);
            $parser->parse();
        }
        if ($sensor_id == STAY_POINTS){
            echo "Cloud Server: backup stay points";
            include_once "lib_mining/stayPointParser.php";
            $parser = new stayPointParser($filename, $username);
            $parser->parse();
        }
     }else{echo "Error moving file ".UPLOAD_DIR."/$filename";}
   }else{echo "No files received";}