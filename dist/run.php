<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:30 AM
 */







include '../functions.php';
include 'variation.php';

updateCampaigns();

// Checking if arguments were supplied
if(!isset($argv[1]) || !isset($argv[2]))
{
    die("2 arguments are required.\nFeed_file and campaign_name\nEg.\nphp run.php feed.csv campaign_1");
}

// taking care of log to avoid new log file when script restart on error
if(isset($argv[4]))
{
    $logfile = $argv[4];
}

// Option feed start position
if(isset($argv[3]))
{
    if(intval($argv[3]) == 0) $feedStart = 2;
    else $feedStart = $argv[3];
}
else $feedStart = 2;


$feedArr = feedToArr($argv[1], $feedStart);
$campaignName = str_replace("_", " ", $argv[2]);


// Fetching campaign id by campaign name, will create if not exist
// Set the budget in the constant.php file
$campaign_id =  getCampaignIdByName($campaignName);







///////////////////////////////////////////////////////////////////////////////

// create AdGroups, Ads and keywords
creator($feedArr, $variation, $feedStart);

// Gone: Pausing Ads and AdGroups for gone
pauseGones(getGone());

// Prepare Database for Next Run
prepare4NextRun();


//////////////////////////////////////////////////////////////////////////////
// Setting feed position to ZERO when completed
saveInTable(
    DB_EXEC,
    [
        'position'      =>  "0",
        'campaign_id'   =>  $campaign_id
    ],
    ['campaign_id'   =>  $campaign_id]
);
exit(0);




