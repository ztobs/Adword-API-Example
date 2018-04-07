<?php

include '../functions.php';
use Lazer\Classes\Database;

updateCampaigns();

$campaignName = str_replace("_", " ", $argv[1]);


//Fetching campaign id by campaign name, will create if not exist
$campaign_id =  getCampaignIdByName($campaignName);
//$campaign_id = "1345262307";


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

//$hello = explode(",", " ,  ");
//var_dump(array_filter($hello, "trim"));

//$id = createAdGroup($campaign_id, "Great Group", BID, "Active");

 // $ads[] = new Ztobs\Classes\Ad("ID", "Ad to delete", "h2 my friend", "description", array("http://tobilukan.com"), "Active", null, null);
 // $id = createAds("54332147432", $ads);

//$id = createCampaign("New Campaign", CAMPAIGN_BUDGET);

//$id = createKeywords("54332147432", array("Speed Darlington", "Some"), "PHRASE", "http://tobilukan.com", BID);

// $id = getAdGroups($campaign_id);

// $id = getAds("53643621586");

//$id = getKeywords("53643621586");

//$id = pauseAd("53643621586", "262768499504");

//$id = pauseAdGroup("53643621586");

//$id = removeAd("54332147432", "262782467722", "hello");

//$id = removeAdGroup("54332147432");

// $id = removeKeyword("53643621586", "38474680");

// $id = resumeAd("53643621586", "262768499504");

// $id = resumeAdGroup("53643621586");

// $id = searchAdGroupByName($campaign_id, "Tobi Group");

// $id = searchAdGroupFromServer($campaign_id, "52713026543");

$id = updateKeyword($adGroupId, $keywordId, $finalUrl)
var_dump($id);


