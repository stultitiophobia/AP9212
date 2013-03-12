<?php

/*
------------------------------------------------------------------------------
APC_Masterswitch-SNMP-Tool - AP9212 - v.1.0.6 (C) 2009 Martin Fuchs
                          -> https://github.com/trendchiller/AP9212

Übergabe per CMD: APC_MS.php X Y [X=Outlet; Y=Status: AN, AUS, NEUSTART]
Übergabe per HTTP: APC_MS.php?outlet=#&status=# [Status: AN, AUS, NEUSTART]
------------------------------------------------------------------------------
*/

$host = "10.100.100.49"; // IP des APC Masterswitch
$community = "private";
$string1 =".1.3.6.1.4.1.318.1.1.4.4.2.1.3.";
$string2 =".1.3.6.1.4.1.318.1.1.4.5.2.1.3.";

/*
$sysname = snmpget($host, $community,"system.sysName.0");
$syslocation = snmpget($host, $community,"system.sysLocation.0");
$sysdescr = snmpget($host, $community,"system.sysDescr.0");
$sysup = snmpget($host, $community,"system.sysUpTime.0");

echo "\n";
echo $sysname." - ".$syslocation." - ".$host."\n";
echo "\n";
*/

if (!empty($_SERVER['argv'][1])) { // Übergebene Kommandozeilen-Parameter verwenden
  if ($_SERVER['argv'][1] >= 1 AND $_SERVER['argv'][1] <= 8)
    $outlet = $_SERVER['argv'][1];    
  else
    die("Es ist ein Fehler aufgetreten: Nur Outlet 1 - 8 sind zulässig !");
  }

elseif (!empty($_GET['outlet'])) { // Übergebene HTTP-Parameter verwenden
  if ($_GET['outlet'] >= 1 AND $_GET['outlet'] <= 8)
    $outlet = $_GET['outlet'];    
  else
    die("Es ist ein Fehler aufgetreten: Nur Outlet 1 - 8 sind zulässig !\n");
  }
  
if (!isset($outlet)) 
  die("Es ist ein Fehler aufgetreten: Es ist kein Outlet definiert !\n");

$statusdescr_in = array(
  "AN" => "1",
  "AUS" => "2",
  "NEUSTART" => "3"
  );

if (!empty($_SERVER['argv'][2])) { // Übergebene Kommandozeilen-Parameter verwenden
    if ($_SERVER['argv'][2] == 'AN' || $_SERVER['argv'][2] == 'AUS' || $_SERVER['argv'][2] == 'NEUSTART') {    
      $status = $statusdescr_in[$_SERVER['argv'][2]];
      $set = snmpset($host, $community, $string1.$outlet,"i",$status);
      }
    else
      die("Es ist ein Fehler aufgetreten: Nur Status AN, AUS oder NEUSTART zulässig !\n");    
   }

elseif (!empty($_GET['status'])) { // Übergebene HTTP-Parameter verwenden
    if ($_GET['status'] == 'AN' || $_GET['status'] == 'AUS' || $_GET['status'] == 'NEUSTART') {
      $status = $statusdescr_in[$_GET['status']];
      $set = snmpset($host, $community, $string1.$outlet,"i",$status);
      }
    else
      die("Es ist ein Fehler aufgetreten: Nur Status AN, AUS oder NEUSTART zulässig !\n");    
   }

$getoutletname = trim(strtr(snmpget($host, $community, $string2.$outlet),"\""," "));
$getstatus = snmpget($host, $community, $string1.$outlet);

if (empty($_SERVER['argv'][2]) && empty($_GET['status'])) { // AN => AUS, AUS => AN
  if ($getstatus == "INTEGER: 1"){
    $status = "2";
    $set = snmpset($host, $community, $string1.$outlet,"i",$status);
    }
  elseif ($getstatus == "INTEGER: 2"){
    $status = "1";
    $set = snmpset($host, $community, $string1.$outlet,"i",$status);
    }
  }   

$set //SNMP-Kommando absetzen
  or exit("Es ist ein Fehler aufgetreten:\nDose ".$outlet." [".$getoutletname."] wurde nicht geschaltet !\n[evtl. im Web-Interface angemeldet ?]\n");

if ($set == TRUE) {
  $statusdescr_out = array(
    "1" => "AN",
    "2" => "AUS",
    "3" => "NEUSTART"
    );    
  echo "Dose ".$outlet.": [".$getoutletname."] wurde auf Status ".$statusdescr_out["$status"]." geschaltet\n";
  }

/*
echo "\n\n";
echo $sysdescr."\n";

echo "Uptime: ".$sysup."\n";
*/

?>