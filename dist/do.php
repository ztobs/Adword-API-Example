<?php
/**
 * Created by PhpStorm.
 * User: donztobs
 * Date: 10/21/17
 * Time: 11:41 AM
 */

// Run:
// php do.php feed.csv campaign_name restart

include "../functions.php";

$noFeed = count(feedToArr($argv[1], 2));

updateCampaigns();
$campaignName = str_replace("_", " ", $argv[2]);
$campaign_id =  getCampaignIdByName($campaignName);

$restart = false;
if(isset($argv[3]))
{
    if($argv[3] == "restart") $restart = true;
}

// Time monitor
$start_time = date("Y-m-d H:i:s");

$i = 1;

$logFile = logFileName();

while(true)
{
    $row = \Lazer\Classes\Database::table(DB_EXEC)->where("campaign_id", "=", "$campaign_id")->find();


    if($restart) $feedCont = 2;
    elseif(isset($row->position)) $feedCont = $row->position;
    else $feedCont = 2;

    if($feedCont >= $noFeed) break;


    system("php run.php ".$argv[1]." ".$argv[2]." ".$feedCont." ".$logFile." ".$campaign_id, $exitCode);
    $restart = false;

    
    if($exitCode == 0 || $exitCode == 99) break;
}

// Gone: Pausing Ads and AdGroups for gone
pauseGones(getGone(), $logFile);

prepare4NextRun();

// Time monitor
$end_time = date("Y-m-d H:i:s");
$execTime = strtotime($end_time) - strtotime($start_time);
echo "\n=================================================\nExecution Time: $execTime seconds\n\n";