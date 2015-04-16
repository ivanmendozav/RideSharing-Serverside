<?php
//Constants (also defined on client side)
$_BATTERY = 90;
$_WIFI = 91;
$_GPS = 92;
$_ACCELEROMETER =1;
$_MAGNETIC_FIELD =2;
$_GYROSCOPE =4;
$_LIGHT =5;
$_PRESSURE =6;
$_PROXIMITY =8;
$_GRAVITY =9;
$_LINEAR_ACCELERATION =10;
$_ROTATION_VECTOR =11;
$_RELATIVE_HUMIDITY =12;
$_AMBIENT_TEMPERATURE =13;
$_TYPE_STEP_DETECTOR =18;

$uploads_dir = 'uploads/'; //Directory to save the file that comes from client application.     
//local
//define("DB_NAME" , "ridesharingmining");
//define("DB_USER","root");
//define("DB_PASSWORD","");
//define("DB_HOST","localhost");
//define("DB_PORT",3306);
//define("DB_PERSISTANT", false);
//server (godaddy)
define("DB_NAME" , "test_cases");
define("DB_USER","test_cases");
define("DB_PASSWORD","malmsteen");
define("DB_HOST","localhost");
define("DB_PORT",3306);
define("DB_PERSISTANT", false);
define("ANONYMOUS_ID", 1);

$sensor_id = filter_input(INPUT_POST,"sensor_id",FILTER_VALIDATE_INT);
$user_id = filter_input(INPUT_POST,"user_id",FILTER_VALIDATE_INT);

   if ($_FILES["uploadedfile"]["error"] == UPLOAD_ERR_OK)       
   {      
     $tmp_name = $_FILES["uploadedfile"]["tmp_name"];     
     $filename = $_FILES["uploadedfile"]["name"];    
     
     
     if(move_uploaded_file($tmp_name, "$uploads_dir/$filename")){ 
         chmod("$uploads_dir/$filename",0777);
        //Parse each file
        if ($sensor_id == $_BATTERY){            
            include_once "lib_mining/batteryParser.php";
            $parser = new batteryParser($filename, $user_id);
            $parser->setDir($uploads_dir);
            $parser->parse();
        }
        if ($sensor_id == $_ACCELEROMETER){
            include_once "lib_mining/accelerometerParser.php";
            $parser = new accelerometerParser($filename, $user_id);
            $parser->setDir($uploads_dir);
            $parser->parse();
        }
        if ($sensor_id == $_WIFI){
            include_once "lib_mining/wifiParser.php";
            $parser = new wifiParser($filename, $user_id);
            $parser->setDir($uploads_dir);
            $parser->parse();
        }
        if ($sensor_id == $_GPS){
            include_once "lib_mining/gpsParser.php";
            $parser = new gpsParser($filename, $user_id);
            $parser->setDir($uploads_dir);
            $parser->parse();
        }
     }else{echo "No files received";}
   }