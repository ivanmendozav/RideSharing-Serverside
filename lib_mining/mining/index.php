<?php
/**
 * Routines to test mining module
 */
include "../config.inc.php";
include "GpsLog.php";
include "DataMapper.php";
$tic = microtime(true);

$log = new GpsLog();
$log->loadPointsFromCSV("test.csv");
foreach($log->GetStayPoints() as $s)
    {DataMapper::SaveStayPoint($s, 98);}

$toc = microtime(true);
if(ModelParameters::$debug_mode) echo "Finished in ".  \abs($toc - $tic)." seconds.";