<?php

// Usage:
// php laundry.php campaign_name
// eg. php laundry.php adwords_dynamic_display_ads_ATv51


include '../functions.php';


updateCampaigns();

$campaignName = str_replace("_", " ", $argv[1]);


// Fetching campaign id by campaign name, will create if not exist
$campaign_id =  getCampaignIdByName($campaignName);

echo "Please wait, this may take several minutes... \n";



$a = populateAdgroupDB(); echo "\n";
populateAdDB(); echo "\n";
populateKeywordDB(); echo "\n";

echo "Adgroups, Ads and Keywords regenerated\nTotal of $a Adgroups/Products";
