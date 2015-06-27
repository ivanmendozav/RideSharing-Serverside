<?php

/** 
 * @date 30/04/2015
 * @author Ivan
 * 
 * A Stay point S is defined as a virtual space region defined by |S| points(pm,..po) where a user has spend a long amount of time.
 * Given a GPS log G of n Gps points<lon,lat,timestamp> = {p1,p2,pm, pm+1,..,po,..,pn} is a total order over time p3>p2>p1
 * a thresold epsilon that represents the minimim time a user has to stay in a region(between points pm and po) 
 * to be consider a Stay point (E.g. 1 hour)
 * a parameter delta for the maximum distance to search around a point pm.
 * A stay point requires distance(pm, pi) <= delta.  For time(pi) > time(pm) and time(pi)<=po
 * And also that time(po)- time(pm) >= epsilon 
 * 
 * Attributes are: average latitude, average longitude, arrival and depature timestamps. Optional: label
 * Example: 4.689, 50.81233, 481234654, 48124657, home. 
 */
Class StayPoint{
    protected $avg_latitude; // sum(pi.lat)/|S|  For pm<=pi<=po
    protected $avg_longitude; // sum(pi.lon)/|S|  For pm<=pi<=po
    protected $arrival; // time(pm)
    protected $departure; // time(po)
    protected $cardinality; // |S|
    protected $label; //optional description (if filled in by the user)
    protected $stay_time;
    public $id;
    public $clusterId;
    public $visited;
    public $noise;
    
    /**
     * Stay point constructor
     * @param type $avg_latitude
     * @param type $avg_longitude
     * @param type $arrival
     * @param type $departure
     */
    public function StayPoint($avg_latitude,$avg_longitude,$arrival,$departure,$cardinality, $label){
        $this->avg_latitude = $avg_latitude;
        $this->avg_longitude = $avg_longitude;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->cardinality = $cardinality;
        $this->label = $label;
        $this->stay_time = $this->departure - $this->arrival;
        $this->visited = false;
        $this->noise = false;
        $this->clusterId = 0;
    }

    public function hasCluster(){
        if($this->clusterId == 0) return false;
        return true;
    }
    
    /**
     * Getters
     * @return type
     */
    public function getAvg_latitude() {
        return $this->avg_latitude;
    }

    public function getAvg_longitude() {
        return $this->avg_longitude;
    }

    public function getArrival() {
        return $this->arrival;
    }

    public function getDeparture() {
        return $this->departure;
    }

    public function getCardinality() {
        return $this->cardinality;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getStay_time() {
        return $this->stay_time;
    }

    public function IsMergeable(StayPoint $s){
        $p1 = new GpsPoint($this->getAvg_longitude(), $this->getAvg_latitude(), 0, 0);
        $p2 = new GpsPoint($s->getAvg_longitude(), $s->getAvg_latitude(), 0, 0);
        if(ModelFormulas::GCDistance($p1, $p2)< ModelParameters::$search_radius){
            return true;
        }
        return false;
    } 

}