<?php

// Usage:
// php laundry.php campaign_name
// eg. php laundry.php adwords_dynamic_display_ads_ATv51


include '../functions.php';

use Lazer\Classes\Database;

updateCampaigns();

$campaignName = str_replace("_", " ", $argv[1]);


// Fetching campaign id by campaign name, will create if not exist
$campaign_id =  getCampaignIdByName($campaignName);

echo "Please wait, this may take several minutes... \n";
//$adgroupFromServer = searchAdGroupFromServer($campaign_id, 46042159170);

$counter = 0;
$adgroup2delete = [];

echo "Compiling .";
$adgroupsDB = Database::table(DB_ADGROUPS)->where("campaign_id", "=", $campaign_id)->findAll();
foreach ($adgroupsDB as $adgroupDB) { // loop through adgroup temp database
	$adgroup_id = $adgroupDB->adgroup_id;
	$adgroup_name = $adgroupDB->adgroup_name;
	
	// search for the adgroup from adwords server and compare
	$adgroupFromServer = searchAdGroupFromServer($campaign_id, $adgroup_id); 
	if(!isset($adgroupFromServer[0]['name']))
	{
		array_push($adgroup2delete, $adgroup_id);
		$counter++;
		echo ".";
	} 
	
	
}

echo "\n Deleting .";
// We should delete the adgroup database record while still looping, so we are going to use the compile adgroup_id of thos to be deleted
foreach ($adgroup2delete as $adgroup_id) {
	Database::table(DB_ADGROUPS)->where('adgroup_id', '=', $adgroup_id)->delete(); // Deleting adgroups
	Database::table(DB_ADS)->where('adgroup_id', '=', $adgroup_id)->delete(); // Deleting ads
	Database::table(DB_KEYWORDS)->where('adgroup_id', '=', $adgroup_id)->delete(); // Deleting keywords
	echo ".";
}

//Database::table(DB_EXEC)->delete();

echo "\n $counter adgroups removed from local db";

?>
