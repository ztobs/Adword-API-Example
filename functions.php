<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:56 AM
 */

require "../vendor/autoload.php";
include "../classes/AddAdGroup.php";
include "../classes/GetCampaigns.php";
include "../classes/AddAd.php";



use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\AdWordsServices;



// Creating the Session from google oAuth
$oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->build();

// Construct an API session configured from a properties file and the OAuth2
// credentials above.
$session = (new AdWordsSessionBuilder())
    ->fromFile()
    ->withOAuth2Credential($oAuth2Credential)
    ->build();


// Initialize campaigns from dashboard
$campaigns = [];
updateCampaigns();




/*
 * Function used to update the currently available campaigns
 */
function updateCampaigns()
{
    global $campaigns;
    global $session;
    $campaigns = GetCampaigns::run(new AdWordsServices(), $session);
    return TRUE;
}

/*
 *  Function to get campaign is by name
 *  Params: String Name,
 *  Returns: Integer
 */
function getCampaignIdByName($name)
{
    global $campaigns;
    return array_search(trim($name), $campaigns);
}

/*
 *  Function creates ad groups into campaign supplied
 *  Params: Integer $campaign_id, String $adGroup_name, Integer $bid
 *  Returns: Integer
 */
function createAdGroup($campaign_id, $adGroup_name, $bid)
{
    global $session;
    return AddAdGroup::run(new AdWordsServices(), $session, $campaign_id, $adGroup_name, $bid);
}

/*
 *  Function creates ad in bulk
 *  Params:  Integer $adGroupId, Array $ads
 *  Returns: Integer
 */
function createAd($adGroupId, $ads)
{
    global $session;
    AddAd::run(new AdWordsServices(), $session, $adGroupId, $ads);
}
