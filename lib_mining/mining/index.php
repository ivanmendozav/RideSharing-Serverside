<?php
/**
 * Routines to test mining module
 */
include "../config.inc.php";
include_once "GpsLog.php";
include "DataMapper.php";
//------------------------------------------------------------------------------
//CONVERSION #1
//parse ghent files
$tic = microtime(true);
ModelFormulas::loadDataFromTxtFiles("bike","csv"); //generate everything from text files
$toc = microtime(true);
if(ModelParameters::$debug_mode) echo "Finished in ".  \abs($toc - $tic)." seconds.";
die;
$tic = microtime(true);
date_default_timezone_set("Europe/Brussels");

//------------------------------------------------------------------------------
//TEST#1 Tour plan
//$log = new GpsLog();
//$log->loadPointsFromCSV("20150528phone.csv");
//foreach($log->GetStayPoints() as $s)
//{
//    //DataMapper::SaveStayPoint($s, 97);
//    echo $s->getAvg_latitude().",".$s->getAvg_longitude()."<br>";
//}
//-------------------------------------------------------------------------------
//TEST#2
//$log = new GpsLog();
//$log->loadPointsFromCSV("20150528phone.csv");
//foreach($log->GetStayPoints() as $s)
//    {echo $s->getAvg_latitude().", ".$s->getAvg_longitude().", ".date("d-M-Y H:i:s",floor(($s->getArrival())/1000)).",".date("d-M-Y H:i:s",floor(($s->getDeparture())/1000))."<br>";}
//die;
//--------------------------------------------------------------------------------
////TEST#3 atypical day
//$log = new GpsLog();
//$log->loadPointsFromCSV("20150609.csv"); //20150609 in Brussels
//$stays = $log->GetStayPoints();
//foreach($stays as  $s)
//    {echo $s->getAvg_latitude().", ".$s->getAvg_longitude().", ".date("d-M-Y H:i:s",floor(($s->getArrival())/1000)).",".date("d-M-Y H:i:s",floor(($s->getDeparture())/1000))."<br>";}
//die;
//--------------------------------------------------------------------------------
////TEST#4
//$log->loadPointsFromCSV("20150604.csv");
//foreach($log->GetStayPoints() as $s)
//{echo $s->getAvg_latitude().",".$s->getAvg_longitude()."<br>";}

//---------------------------------------------------------------------------------------------
//TEST #5 // compare tour plan (calibrate params)
//1.	Home	9:15 – 9:30
//2.	Work	9:40 – 12:20
//3.	Alma 3	12:25 – 13:25
//4.	VUB Auditorium(Brussels)    14:59 – 17:30
//5.	VUB Complex(Brussels)	17:37 – 18:45
echo "-------PLAN  vs. ALGORITHM1--------<br>";
$plan = new StaysLog();
$plan->AddPoint(new StayPoint(50.878394, 4.688631,strtotime("2015-05-28 10:58:00"),strtotime("2015-05-28 15:45:00"),0, "Home"));
$plan->AddPoint(new StayPoint(50.861481, 4.681193,strtotime("2015-05-28 9:29:00"),strtotime("2015-05-28 9:50:00"),0, "Work"));
//$plan->AddPoint(new StayPoint(50.867564, 4.685212,strtotime("2015-06-09 12:25:00"),strtotime("2015-06-09 13:25:00"),0, "Alma3"));
$plan->AddPoint(new StayPoint(50.880494, 4.705966,strtotime("2015-05-28 15:59:00"),strtotime("2015-05-28 16:15:00"),0, "Bart Smit"));
$plan->AddPoint(new StayPoint(50.879133, 4.701082,strtotime("2015-05-28 16:18:00"),strtotime("2015-05-28 16:40:00"),0, "Groot Markt"));
$plan->AddPoint(new StayPoint(50.879500, 4.704386,strtotime("2015-06-09 9:40:00"),strtotime("2015-06-09 12:20:00"),0, "Caffe Leffe"));
//$plan->AddPoint(new StayPoint(50.877027, 4.689486,strtotime("2015-06-09 17:41:00"),strtotime("2015-06-09 17:57:00"),0, "Day Care Center"));
//$plan->AddPoint(new StayPoint(50.821201, 4.394503,strtotime("2015-06-09 14:59:00"),strtotime("2015-06-09 17:30:00"),0, "Auditorium VUB"));
//$plan->AddPoint(new StayPoint(50.824351, 4.395724,strtotime("2015-06-09 17:37:00"),strtotime("2015-06-09 18:45:00"),0, "Complex bar VUB"));

$bottom_maxDist = 0.02; //5 meters 
$upper_maxDist = 0.1; //500 meters
$bottom_minTime = 300000; //5min mseconds 
$upper_minTime = 600000; // 2hour mseconds
$penalty = 0.100;
//$penalty = 1800; //seconds

$maxDist = $bottom_maxDist; $minTime = $bottom_minTime;

while($maxDist <= $upper_maxDist){
    $minTime = $bottom_minTime;
    while($minTime <= $upper_minTime){
        ModelParameters::$distance_threshold = $maxDist;        ModelParameters::$time_threshold = $minTime;
        $log = new GpsLog();
        $log->loadPointsFromCSV("20150528phone.csv"); //20150609 in Brussels
        $stays = $log->GetStayPoints();
        $ghost_stays = (count($stays)-count($plan->GetPoints()));
        //compare and calculate errors
        $error = 0; ; $i=0; //if($ghost_stays>0) {$error += $ghost_stays*$penalty;}
        $count = 0;
        foreach($plan->GetPoints() as $s){
            $identical = false; $i=0;
            while($i < count($stays) && $identical==false){
                $shat = $stays[$i];
                if($shat->IsMergeable($s)){
                    $count++;
                    $identical = true;
                    //echo date("d-M-Y H:i:s",$s->getArrival())." - ".date("d-M-Y H:i:s",floor($shat->getArrival()/1000))." = ".abs($s->getArrival() - floor($shat->getArrival()/1000))." seconds<br>";
                    //echo date("d-M-Y H:i:s",$s->getDeparture())." - ".date("d-M-Y H:i:s",floor($shat->getDeparture()/1000))." = ".abs($s->getDeparture() - floor($shat->getDeparture()/1000))." seconds<br>";
                    //$error += abs($s->getArrival() - floor($shat->getArrival()/1000)) + abs($s->getDeparture() - floor($shat->getDeparture()/1000));
                    $error += ModelFormulas::GCDistance(new GpsPoint($s->getAvg_longitude(),$s->getAvg_latitude(),0,0), new GpsPoint($shat->getAvg_longitude(),$shat->getAvg_latitude(),0,0));
                }
                $i++;
            } if($identical==false){$error +=$penalty;}
        }echo $maxDist.",".$minTime.",".$error."<br>";
        $minTime+=300000;
    }    
    $maxDist+=0.02; 
}
$toc = microtime(true);
if(ModelParameters::$debug_mode) echo "Finished in ".  \abs($toc - $tic)." seconds.<br>";