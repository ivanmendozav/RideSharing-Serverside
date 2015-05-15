<?php

/* 
 * Global constants
 */

/*Sensors****************************/
//(also defined on client side)
define(ACCELEROMETER,1);
define(BATTERY,90);
define(WIFI,91);
define(GPS,92);
define(STAY_POINTS, 100);

//$_MAGNETIC_FIELD =2;
//$_GYROSCOPE =4;
//$_LIGHT =5;
//$_PRESSURE =6;
//$_PROXIMITY =8;
//$_GRAVITY =9;
//$_LINEAR_ACCELERATION =10;
//$_ROTATION_VECTOR =11;
//$_RELATIVE_HUMIDITY =12;
//$_AMBIENT_TEMPERATURE =13;
//$_TYPE_STEP_DETECTOR =18;


/*Database********************************/

/*local
define("DB_NAME" , "ridesharingmining");
define("DB_USER","root");
define("DB_PASSWORD","");
define("DB_HOST","localhost");
define("DB_PORT",3306);
define("DB_PERSISTANT", false);*/

//server (godaddy)
define("DB_NAME" , "test_cases");
define("DB_USER","test_cases");
define("DB_PASSWORD","malmsteen");
define("DB_HOST","localhost");
define("DB_PORT",3306);
define("DB_PERSISTANT", false);
define("ANONYMOUS_ID", 1);
define("TABLE_NAME", "stay_points");

/*PATHS*/
define("UPLOAD_DIR","uploads/");