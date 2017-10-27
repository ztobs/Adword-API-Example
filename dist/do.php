<?php
/**
 * Created by PhpStorm.
 * User: donztobs
 * Date: 10/21/17
 * Time: 11:41 AM
 */

include "../functions.php";

$noFeed = count(feedToArr($argv[1], 2));

updateCampaigns();
$campaignName = str_replace("_", " ", $argv[2]);
$campaign_id =  getCampaignIdByName($campaignName);

// Time monitor
$start_time = date("Y-m-d H:i:s");

$i = 1;

$logFile = logFileName();

while(true)
{
    $row = \Lazer\Classes\Database::table(DB_EXEC)->where("campaign_id", "=", "$campaign_id")->find();
    if(isset($row->position)) $feedCont = $row->position;
    else $feedCont = 2;

    system("php run.php ".$argv[1]." ".$argv[2]." ".$feedCont." ".$logFile." ".$campaign_id, $exitCode);
    if($exitCode == 0 || $feedCont >= $noFeed) break;
}

// Time monitor
$end_time = date("Y-m-d H:i:s");
$execTime = strtotime($end_time) - strtotime($start_time);
echo "\n=================================================\nExecution Time: $execTime seconds\n\n";