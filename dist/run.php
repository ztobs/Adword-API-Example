<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:30 AM
 */

include '../functions.php';
include '../classes/Ads.php';

// Fetching campaign id by campaign name
$campaign_id =  getCampaignIdByName("Campaign #1");


// Creating AdGroup
$adGroupId = createAdGroup($campaign_id, "Tb", 1000000);


// Creating array of ads object
// $headline1, $headline2, $description, $finalUrls(array), $path1=NULL, $path2=NULL
$ads[] = new Ads("Cruise to Ve", "Best Space Cruise Line", "Buy your tickets now!", ["http://tobilukan.com/cruise/"] );
$ads[] = new Ads("LG TV", "4K 3D Television.", "uo nsdfjsdfsd fgsdfgisd fgsdfgsdfg", ["http://tobilukan.com/jp/"] );


// Creating Adverts in bulk
createAd($adGroupId, $ads);