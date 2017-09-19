<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:30 AM
 */

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

// Optional syncing
if(isset($argv[3]))
{
    if($argv[3] == "sync")
    {
        echo "Syncing Local with Server ...\n";
        exec("php sync.php");
    }
}


$feedArr = feedToArr($argv[1]);
$campaignName = str_replace("_", " ", $argv[2]);


// Fetching campaign id by campaign name, will create if not exist
// Set the budget in the constant.php file
$campaign_id =  getCampaignIdByName($campaignName);


///////////////////////////////////////////////////////////////////////////////
//



// First remove ads that re-occurred in the feed
cleanUp($feedArr);

// Collect ads to be paused
$residueAd = residue();

// Pause ads not in the feed
pauseResidues($residueAd);

// create the ads to insert
$adsToInsert = adsToInsert($feedArr, $variation);

// create ads one by one
echo "Creating Ads ....\n";
foreach($adsToInsert as $ad)
{
    createAdDyn($campaign_id, $ad);
}

// cleanup file fragments
echo "Defragmenting Local Files Database ....\n";
defragment(TEMP_PATH.ADS_LOCAL_FILE);
defragment(TEMP_PATH.PRODUCTS_LOCAL_FILE);

