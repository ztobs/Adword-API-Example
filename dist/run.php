<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:30 AM
 */

// Setting Timezone
date_default_timezone_set('CET');


// removing execution limits
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');



include '../functions.php';
include 'variation.php';

// Checking if arguments were supplied
if(!isset($argv[1]) || !isset($argv[2]))
{
    die("2 arguments are required, third arguement is optional but recommended.\nFeed_file, campaign_name, and sync\nEg.\nphp run.php feed.csv campaign_1 sync");
}

// Option feed start position
if(isset($argv[4])) $feedStart = $argv[4];
else $feedStart = 2;


$feedArr = feedToArr($argv[1], $feedStart);
$campaignName = str_replace("_", " ", $argv[2]);



// Fetching campaign id by campaign name, will create if not exist
// Set the budget in the constant.php file
$campaign_id =  getCampaignIdByName($campaignName);


// Optional syncing
if(isset($argv[3]))
{
    if($argv[3] == "sync")
    {
        echo "Syncing Local with Server ...\n";
        exec("php sync.php ".$campaign_id);
    }
}







///////////////////////////////////////////////////////////////////////////////

// create AdGroups, Ads and keywords
creator($feedArr, $variation, $feedStart);

// Gone: Pausing Ads and AdGroups for gone
pauseGones(getGone());

// Prepare Database for Next Run
prepare4NextRun();

if($er) echo "Some error occurred, please check the log file\n";



//initTables();

