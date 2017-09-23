<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:30 AM
 */

// Setting Timezone
date_default_timezone_set('CET');

//Time monitor
$start_time = date("Y-m-d H:i:s");

// removing execution limits
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

include '../functions.php';
include '../classes/Ad.php';

// Checking if arguments were supplied
if(!isset($argv[1]) || !isset($argv[2]))
{
    die("2 arguments are required, third arguement is optional but recommended.\nFeed_file, campaign_name, and sync\nEg.\nphp run.php feed.csv campaign_1 sync");
}

// Option feed start position
if(isset($argv[4])) $feedStart = $argv[4];
else $feedStart = 2;

// Optional cleanup
if(isset($argv[5]) )
{
    if($argv[5] == "no-cleanup") $noCleanup = true;
}

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
if(!$noCleanup)  // Dont run if not cleanup set, this happens when an error made script to re-run
{
    // First remove ads that re-occurred in the feed
    cleanUp($feedArr);

    // Collect ads to be paused
    $residueAdGroups = residue();

    // Pause ads not in the feed
    pauseResidues($residueAdGroups);
}


// create ads and keywords
createAdsNKeywords($feedArr, $variation, $feedStart);



// cleanup file fragments
echo "Defragmenting Local Files Database ....\n";
defragment(TEMP_PATH.ADGROUPS_LOCAL_FILE);


echo "Last Script restart ran with following details:\n".count($feedArr)." Products\n";
echo count($feedArr)*count($variation)." Ads updated\n";
if($er) echo "Some error occured, please check the log file\n";

//Time monitor
$end_time = date("Y-m-d H:i:s");
$execTime = strtotime($end_time) - strtotime($start_time);
echo "Execution Time: $execTime sec";
