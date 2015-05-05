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
}