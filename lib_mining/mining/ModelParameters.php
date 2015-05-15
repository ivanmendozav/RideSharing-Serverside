<?php

/**
 * Description of ModelParameters
 *
 * @author Ivan
 * @date 30/04/2015 
 */
class ModelParameters {
    //STAY POINTS
    public static $distance_threshold = 0.1; //km. To look for points around a stay point
    public static $time_threshold = 1800000; //miliseconds. To dwell around a stay point
    public static $cluster_size = 10; //integer. visits to a stay point before converting to a POI
    
    //GENERAL SETTINGS
    public static $csv_delimiter = ","; //to parse a CSV file's line
    public static $csv_longitude_column = 1;
    public static $csv_latitude_column = 0;
    public static $csv_altitude_column = 2;
    public static $csv_timestamp_column = 3;
    
    public static $debug_mode = true;
}
