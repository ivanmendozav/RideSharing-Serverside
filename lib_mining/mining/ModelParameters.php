<?php

/**
 * Description of ModelParameters
 *
 * @author Ivan
 * @date 30/04/2015 
 */
class ModelParameters {
    //STAY POINTS
    public static $distance_threshold = 0.04; //km. To look for points around a stay point
    public static $time_threshold = 300000; //miliseconds. To dwell around a stay point
    //POI
    public static $search_radius = 0.1; //km
    public static $cluster_size = 1; //integer. additonal visits to a previously visited location to become a POI
    
    
    //GENERAL SETTINGS
    public static $csv_delimiter = ","; //to parse a CSV file's line
    public static $csv_longitude_column = 1;
    public static $csv_latitude_column = 0;
    public static $csv_altitude_column = 3;
    public static $csv_timestamp_column = 2;
    
    //for txt files (Bike gps Ghent)    
    public static $txt_timestamp_column = 3;
    public static $txt_latitude_column = 4;
    public static $txt_longitude_column = 6;
    //for stay point files
    public static $csv_arrival_column = 2;
    public static $csv_departure_column = 3;
    public static $csv_cardinality_column = 4;
    //for poi files
    public static $csv_poi_longitude_column = 2;
    public static $csv_poi_latitude_column = 1;
    
    public static $debug_mode = true;
}
