<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:30 AM
 */

include '../functions.php';
include '../classes/Ad.php';

$feedArr = feedToArr($argv[1]);


// Fetching campaign id by campaign name
$campaign_id =  getCampaignIdByName("Campaign #1");


//var_dump(existAdGroup("KMS"));
//
// Creating AdGroup
//$adGroupId = createAdGroup($campaign_id, "PMS", 1000000);
//
//
// Creating array of ads object
// $headline1, $headline2, $description, $finalUrls(array), $path1=NULL, $path2=NULL

//$ads[] = new Ad("1234", "Shoes", "Ad".$i, "Best Line".rand(0, 999999999), "Buy your tickets now!", ["http://tobilukan.com/cruise/"] );
//
//
//// Creating Adverts in bulk
//var_dump(createAd("46873797099", $ads));


/////////////////////////////////////////////////////////////////
//var_dump(countAdsInAdGroup("46873797099"));
//var_dump(existAd("1234"));


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
foreach($adsToInsert as $ad)
{
    //var_dump($ad); die();
    createAdDyn($campaign_id, $ad);
}


