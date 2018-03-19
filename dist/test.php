<?php

include '../functions.php';
use Lazer\Classes\Database;

updateCampaigns();

$campaignName = str_replace("_", " ", $argv[1]);


//Fetching campaign id by campaign name, will create if not exist
$campaign_id =  getCampaignIdByName($campaignName);



// //$t = getAds("53030040155");
// // $t = searchAdGroupFromServer($campaign_id, "53030040155");
// $t = searchAdGroupByName($campaign_id, "After Bite Stift Kids (AT-P2946112-20G)");
//$t = getAdsByProductId("AT-P3911794-20ST");

//$t = getAdgroupByProductId("AT-P3911794-20ST");
//$t = populateAdgroupDB();
// $t = populateAdDB();
//echo count($t);
//var_dump($t);

//populateKeywordDB();

//prepare4NextRun();

//removeNullProductDb();

//$tt = getAdGroups($campaign_id);
//foreach ($tt as $t) {
//	removeAdGroup($t['id']);
//}

$tt = getKeywords("55746133800");
var_dump($tt);