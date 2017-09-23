<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:56 AM
 */

// Handle Fatal Error
register_shutdown_function( "fatal_handler" );

// No displaying of error
error_reporting(0);

// Allows mac detect line_endings in fgets methods
ini_set("auto_detect_line_endings", true);

// Setting currency format
setlocale(LC_MONETARY,"en_US");





require "../vendor/autoload.php";
include "constants.php";
include "../dist/variation.php";
include "../classes/AddAdGroup.php";
include "../classes/GetCampaigns.php";
include "../classes/AddAds.php";
include "../classes/GetAdGroupsByCampaign.php";
include "../classes/GetAds.php";
include "../classes/PauseAd.php";
include "../classes/RemoveAd.php";
include "../classes/AddCampaign.php";
include "../classes/RemoveAdGroup.php";
include "../classes/PauseAdGroup.php";
include "../classes/AddKeywords.php";




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


$adwordServices = new AdWordsServices();


// Initialize campaigns from dashboard
$campaign_id = "";
$campaigns = [];
$er = false;
$feedPos = 0;
updateCampaigns();


//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////

/*
 * Function fetches all campaigns into array
 * param:
 * return: array
 */
function getCampaigns()
{
    global $session;
    return GetCampaigns::run(new AdWordsServices(), $session);
}


/*
 * Function creates campaign
 * params: $name, $budget, $cap(optional)
 * return: array
 */
function createCampaign($name, $budget, $cap=null)
{
    global $session;
    $ret = AddCampaign::run(new AdWordsServices(), $session, $name, $budget, $cap);
    $id = $ret['id'];
    $name = $ret['name'];
    writeToFile(CAMPAIGNS_LOCAL_FILE, "$id||$name\n");
    log_("Create Campaign: $name");
    return $ret;
}


function emptyFile($fileName)
{
    unlink($fileName);
    $sh = fopen($fileName, 'a+');
    fclose($sh);
}


function deleteLineInFile($file, $lineNumber)
{
    $file_out = file($file); // Read the whole file into an array
    //unset($file_out[$lineNumber-1]);
    $file_out[$lineNumber-1] = "\n";
    file_put_contents($file, implode("", $file_out));

}


/*
 * Function removed empty lines from file database
 * param: $file
 */
function defragment($fileName)
{
    $file = file_get_contents($fileName);
    $data = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $file);
    file_put_contents($fileName, $data);
}


function log_($data)
{
    $datetime = date("Y-m-d H:i:s");
    $data = "[$datetime] $data";
    writeToFile(LOG_FILE, $data."\n");
}


/*
 * Function to convert csv to array
 * param: $filename
 * return: assoc 2D array of feeds
 */
function feedToArr($fileName, $feedStart)
{
    if(!filter_var($fileName, FILTER_VALIDATE_URL)) $fileName = FEED_PATH.$fileName; // appending file path if nto a url
    try
    {
        $file = fopen($fileName, 'r');
        $cc = 1;
        $result = [];
        while (($line = fgetcsv($file, 1000000, ";", '"')) !== FALSE) {
            //$line is an array of the csv elements
            if($cc > 1 && $cc >= $feedStart)$result[] = $line;
            $cc++;
        }
        fclose($file);
    }
    catch(Exception $e)
    {
        die("Invalid File");
    }
    return $result;
}



/*
 * Function to remove ads that will be re-created
 * param: $feedArr
 *
 */
function cleanUp($feedArr)
{
    echo "Cleaning Up repeat products ....\n";
    foreach ($feedArr as $feed)
    {
        //$existAdGroup = adGroupIdFromProductId($feed[0]);  // check if product/adgroup exist
        $adGroupName = $feed[6]." (".$feed[0].")";
        $adGroupData = findAdGroupData($adGroupName);
        if($adGroupData)
        {
            removeAdGroup($adGroupData['adgroup_id']);  // remove the adgroup
            deleteLineInFile(TEMP_PATH.ADGROUPS_LOCAL_FILE, $adGroupData['line_number']); //for adgroups.txt
            log_("Remove** AdGroup: ".$adGroupData['adgroup_name']." With Ads and Keywords");
        }
    }
}

/*
 * Function removed adgroup
 * param: adgroupId
 */
function removeAdGroup($adGroupId)
{
    global $session;
    RemoveAdGroup::run(new AdWordsServices(), $session, $adGroupId);
}





/*
 * Function finds the remaining details about the adgroup like product_name and line_number in adgroups.txt
 */
function findAdGroupData($adGroupToFind)
{
    global $campaign_id;
    $handle = fopen(TEMP_PATH.ADGROUPS_LOCAL_FILE, "a+");
    $cc = 1;
    $occur = null;
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if(trim($line) != "")
            {
                $line_arr = explode("||", $line);
                $adGroudId = $line_arr[0];
                $adGroupName = trim($line_arr[1]);
                $camp_id = trim($line_arr[2]);

                if($adGroupName == $adGroupToFind && $camp_id == $campaign_id)
                {
                    $occur = array("adgroup_id"=>$adGroudId, "adgroup_name"=>$adGroupName, "line_number"=>$cc);
                }
            }
            $cc++;
        }
        fclose($handle);
    } else {
        // error opening the file.
    }
    return $occur;
}



/*
 * Function to compile ads that were not cleaned, to be paused
 * param:
 * return: assoc array of ads (ad_id and adgroup_id)
 */
function residue()
{
    $ids = [];
    $handle = fopen(TEMP_PATH.ADGROUPS_LOCAL_FILE, "a+");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if(trim($line) != "")
            {
                $line_arr = explode("||", $line);
                $ids[] = array("adgroup_id"=>trim($line_arr[0]), "product_name"=>trim($line_arr[1]));
            }
        }
        fclose($handle);
    } else {
        // error opening the file.
    }
    return $ids;
}


/*
 * Function to pause all the ads from residue function
 * params: $residue
 * return:
 */
function pauseResidues($residueAdGroups)
{
    echo "Pausing Non-repeat Products/Adgroups ....\n";
    if(count($residueAdGroups) == 0) return;
    foreach ($residueAdGroups as $adGroups)
    {
        pauseAdGroup($adGroups['adgroup_id']);
        log_("Pause AdGroup: ".$adGroups['product_name']." With its Ads and Keywords");
    }
}


/*
 * Function pauses adgroup and its ads and keywords
 */
function pauseAdGroup($adGroupId)
{
    global $session;
    PauseAdGroup::run(new AdWordsServices(), $session, $adGroupId);
}


/*
 *  Function creates ad groups into campaign supplied
 *  Params: Integer $campaign_id, String $adGroup_name, Integer $bid
 *  Returns: Integer
 */
function createAdGroup($campaign_id, $adGroup_name, $bid, $status)
{
    global $session;
    global $campaign_id;
    $id = AddAdGroup::run(new AdWordsServices(), $session, $campaign_id, $adGroup_name, $bid, $status);
    return $id;
}


/*
 * Function to make total ads to create from product feeds,
 * params: $feedArr, $variation_arr
 * return: array of ads object
 */
function createAdsNKeywords($feedArr, $variation_arr, $feedStart)
{
    global $campaign_id;
    global $feedPos;
    echo "Creating Adgroups, Ads and Keywords ....\n";
    $count = 0;
    foreach ($feedArr as $feed)
    {
        $feedPos = $feedStart+$count;
        echo "$feedPos,";
        if(eligibleProduct($feed))
        {
            $keywords = explode(";", preg_replace('/[^A-Za-z0-9\-]/', '',$feed[12]));  //remove special characters and convert to array

            // Create AdGroup
            $adGroupName = $feed[6]." (".$feed[0].")";
            $adGroupId = floatval(createAdGroup($campaign_id, $adGroupName, BID, $feed[16]));

            // Compile ads per product
            $ads = [];
            $product_url = $feed[14];
            $is_https = strpos($product_url, "https://");
            $product_url = str_replace("http://", "", $product_url);
            $product_url = str_replace("https://", "", $product_url);
            $url_parts = explode("/", $product_url, 3);
            $urlPart1 = str_replace(".", "", $url_parts[1]);
            if(isset($url_parts[2])) $urlPart2 = $url_parts[2];
            else $urlPart2 = null;
            $final_url = $is_https?"https://".$url_parts[0]:"http://".$url_parts[0];
            $keywordFinalUrl = $is_https?"https://".$product_url:"http://".$product_url;

            foreach ($variation_arr as $var)
            {
                $productNameLimit = 30 - (strlen($var['headline1']) - 15);
                $productName = substr($feed[6], 0, $productNameLimit);
                $headline1 = str_replace("{{productName}}", $productName, $var['headline1']);
                $headline1 = str_replace("{{productPrice}}", number_format(str_replace(" EUR", "", $feed[2])), $headline1);
                $headline1 = str_replace("{{productDiscountInPercent}}", $feed[10], $headline1);
                $headline2 = str_replace("{{productName}}", $productName, $var['headline2']);
                $headline2 = str_replace("{{productPrice}}", number_format(str_replace(" EUR", "", $feed[2])), $headline2);
                $headline2 = str_replace("{{productDiscountInPercent}}", $feed[10], $headline2);

                $ads[] = new Ad($feed[0], $headline1, $headline2, $feed[5], array($keywordFinalUrl), $feed[16], null, null);
            }

            // Create Ads
            createAds($adGroupId, $ads);


            // Create Keywords
            createKeywords($adGroupId, $keywords, $keywordFinalUrl, KEYWORDS_BID);


            // Writing new additions to local files
            writeToFile(ADGROUPS_LOCAL_FILE, $adGroupId."||".$adGroupName."||".$campaign_id."\n");


            // Logging
            log_("Create AdGroup: ".$productName." With ".count($variation_arr)." Ads Variations and Keywords (".implode(", ", $keywords).")");

        }
        $count++;


    }
    echo "\n";
}


function eligibleProduct($feed)
{
    global $er;
    $error = "";
    if(isEmpty($feed[0])) $error .= "Product Id, ";
    if(isEmpty($feed[2])) $error .= "Price, ";
    if(isEmpty($feed[5])) $error .= "Description, ";
    if(isEmpty($feed[6])) $error .= "Short Name, ";
    if(isEmpty($feed[10])) $error .= "Discount Percentage, ";
    if(isEmpty($feed[12])) $error .= "Keywords, ";
    if(isEmpty($feed[14])) $error .= "Product URL, ";
    if(isEmpty($feed[16])) $error .= "Status, ";

    if($error != "")
    {
        log_("**Error: The following cannot be empty in the feed; ".$error);
        $er = true;
    }
    else return true;
}


function isEmpty($string)
{
    if($string == null || $string == "") return true;
    else return false;
}





function createKeywords($adgroupId, $keywordsArr, $finalUrl, $bid)
{
    global $session;
    AddKeywords::run(new AdWordsServices(), $session, $adgroupId, $keywordsArr, $finalUrl, $bid);
}


/*
 *  Function creates ad in bulk
 *  Params:  Integer $adGroupId, Array $ads
 *  Returns: array
 */
function createAds($adGroupId, $ads)
{
    global $session;
    $ad_data = AddAds::run(new AdWordsServices(), $session, $adGroupId, $ads);

}



/*
 * Function used to update the currently available campaigns
 */
function updateCampaigns()
{
    global $campaigns;
    $campaigns = getCampaigns();
    if(file_exists(TEMP_PATH.CAMPAIGNS_LOCAL_FILE)) emptyFile(TEMP_PATH.CAMPAIGNS_LOCAL_FILE);
    foreach ($campaigns as $campaign)
    {
        $name = $campaign['name'];
        $id = $campaign['id'];
        writeToFile(CAMPAIGNS_LOCAL_FILE, "$id||$name\n");
    }
    return TRUE;
}


function updateAdGroups($campaigns, $campaign_id)
{
    if(file_exists(TEMP_PATH.ADGROUPS_LOCAL_FILE)) emptyFile(TEMP_PATH.ADGROUPS_LOCAL_FILE);
    $results = [];
    foreach ($campaigns as $campaign)
    {
        $name = $campaign['name'];
        $id = $campaign['id'];

        $adGroups = getAdGroups($id);
        if(count($adGroups) > 0)
        {
            foreach ($adGroups as $adGroup)
            {
                $adGroupName = $adGroup['name'];
                $adGroupId = $adGroup['id'];
                $results[] = array('id'=>$adGroupId, 'name'=>$adGroupName);
                writeToFile(ADGROUPS_LOCAL_FILE, "$adGroupId||$adGroupName||$campaign_id\n");
            }
        }
    }
    return $results;
}



function writeToFile($fileName, $data)
{
    $file = fopen(TEMP_PATH.$fileName, "a+");
    $resp = fwrite($file, $data);
    fclose($file);
    return $resp;
}


function getAdGroups($campaign_id)
{
    global $session;
    return GetAdGroupsByCampaign::run(new AdWordsServices(), $session, $campaign_id);
}


function getCampaignIdByName($name)
{
    global $campaigns;
    $id = null;
    foreach($campaigns as $campaign)
    {
        if($campaign['name'] == $name) $id = $campaign['id'];
    }

    if($id == null)
    {
        echo "The campaign name '$name' wasnt found, we are going to create it\n Type: y to continue \n";
        $stdin = fopen('php://stdin', 'r');
        $response = fgetc($stdin);

        if($response == "y")
        {
            $campaignData = createCampaign($name, CAMPAIGN_BUDGET);
            $id = $campaignData['id'];
        }
        else
        {
            exit();
        }
    }
    return $id;

}


function fatal_handler() {
    global $feedPos;
    global $argv;
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if( $error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        // If fatal error, restart script
        if($errno === E_ERROR)
        {
            $feedCont = $feedPos+1;
            echo "\nFatal Error at FeedLine $feedPos, check log for details\nRestarting script from Feedline $feedCont \n";
            log_("Fatal Error at FeedLine $feedPos: $errstr");

            // re-run script with special options like, no-sync, startPos and no-cleanup
            system("php run.php ".$argv[1]." ".$argv[2]." no-sync ".$feedCont." no-cleanup");
            //echo $output;
        }



    }
}







/*
 *  Function find all occurences of the product id
 *  Params:  Integer $findProdtuctId
 *  Returns: Array of ad_id, line_number(In file), and adgroup_id
 */
//function existAd($findProdtuctId)
//{
//    $adGroup = adGroupIdFromProductId($findProdtuctId);
//    $occur = [];
//    foreach($adGroups as $adGroup)
//    {
//        $productLineNumber = $adGroup['line_number'];
//        $handle = fopen(TEMP_PATH.ADS_LOCAL_FILE, "a+");
//        $cc = 1;
//        if ($handle) {
//            while (($line = fgets($handle)) !== false) {
//                if(trim($line) != "")
//                {
//                    $line_arr = explode("||", $line);
//                    $id = $line_arr[0];
//                    $adName = trim($line_arr[1]);
//                    $adGroupId = trim($line_arr[2]);
//
//                    if($ad['ad_id'] == $id)
//                    {
//                        $occur[] = array("ad_id"=>$id, "ad_name"=>$adName, "line_number"=>$cc, "line_number_product"=>$productLineNumber, "adgroup_id"=>$adGroupId);
//                    }
//                }
//
//                $cc++;
//            }
//
//            fclose($handle);
//        } else {
//            // error opening the file.
//        }
//    }
//    return $occur;
//}







//////////////////////////////////////////////////////
//////////////////////////////////////////////////////

//function removeProductIds($feedArr)
//{
//    foreach ($feedArr as $feed)
//    {
//        $ads = existAd($feed[0]);
//        foreach ($ads as $ad)
//        {
//            deleteLineInFile(TEMP_PATH.PRODUCTS_LOCAL_FILE, $ad['line_number']);
//        }
//    }
//    defragment(TEMP_PATH.PRODUCTS_LOCAL_FILE);
//}

















//
//
//
//
///*
// * Function take only on ad object then searches if its adgroup exist, is adgroup exists or is full, it creates new one and inserts
// * param: ad (object)
// *
// */
//function createAdDyn($campaign_id, $ad)
//{
//    // FIsrt create the ad
//    //////////////////////////////////////////////////////////////////////////////
//
//    // First check if the category/adgroup exist
//    $adGroupDet = existAdGroup($ad->category);
//    if($adGroupDet)
//    {
//        $lastAdGroupId = $adGroupDet['last_id'];
//        $lastNumber = $adGroupDet['last_number'];
//
//        // loop throu to fill up adgroups starting from l
//        $i = $lastNumber;
//
//        while ($i > 0)
//        {
//            $adGroupId = getAdGroupId($ad->category."#".$i);
//
//            // Check if adgroup isnt full
//            if(countAdsInAdGroup($adGroupId) < 50)
//            {
//                createAd($adGroupId, $ad);
//                break;
//            }
//            $i--;
//        }
//        // If all adGroups are full, then create the adgroup and insert ad
//        if($i == 0)
//        {
//            $adGroupId = createAdGroup($campaign_id, $ad->category, BID);
//            createAd($adGroupId, $ad);
//        }
//    }
//    else
//    {
//        // Create the adGroup and insert ad
//        $adGroupId = createAdGroup($campaign_id, $ad->category, BID);
//        createAd($adGroupId, $ad);
//    }
//
//}
//
//
//
//
//
//
//function countAdsInAdGroup($adGroupId)
//{
//    global $session;
//    $ads = GetAds::run(new AdWordsServices(), $session, $adGroupId);
//    return count($ads);
//}
//
//
//
//
///*
// *  Function checks if a adgroup exists and return the id or false
// *  Params:  Integer $adgroup
// *  Returns: Array of last_id and last_number
// */
//function existAdGroup($findName)
//{
//    $findName = trim($findName);
//    $adGroupId = null;
//    $adGroupLastNumber = 0;
//    $handle = fopen(TEMP_PATH.ADGROUPS_LOCAL_FILE, "a+");
//    if ($handle) {
//        while (($line = fgets($handle)) !== false) {
//            if(trim($line) != "")
//            {
//                $line_arr = explode("||", $line);
//                $id = trim($line_arr[0]);
//                $nameFull = trim($line_arr[1]);
//
//                $adGroupNameSplit = explode("#", $nameFull);
//                $adGroupName = trim($adGroupNameSplit[0]);
//                $adGroupNumber = trim($adGroupNameSplit[1]);
//
//                if($findName == $adGroupName)
//                {
//                    if($adGroupNumber > $adGroupLastNumber) $adGroupLastNumber = $adGroupNumber;
//                }
//            }
//
//        }
//
//        fclose($handle);
//    } else {
//        // error opening the file.
//    }
//
//    $adGroupId = getAdGroupId($findName."#".$adGroupLastNumber);
//
//    if($adGroupId) return array('last_id'=>$adGroupId, 'last_number'=>$adGroupLastNumber);
//    else return FALSE;
//}

//
//function getAdGroupId($name)
//{
//    $name = trim($name);
//    $adGroupId = null;
//    $handle = fopen(TEMP_PATH.ADGROUPS_LOCAL_FILE, "a+");
//    if ($handle) {
//        while (($line = fgets($handle)) !== false) {
//            $line_arr = explode("||", $line);
//            $id = trim($line_arr[0]);
//            $nameFull = trim($line_arr[1]);
//
//
//            if($nameFull == $name)
//            {
//                $adGroupId = $id;
//                break;
//            }
//        }
//
//        fclose($handle);
//    } else {
//        // error opening the file.
//    }
//    return $adGroupId;
//
//}
//
//
//
//
//
//function getAds($adGroupId)
//{
//    global $session;
//    return GetAds::run(new AdWordsServices(), $session, $adGroupId);
//}
//








function removeAd($adGroupId, $adId, $adName)
{
    global $session;
    RemoveAd::run(new AdWordsServices(), $session, $adGroupId, $adId);
    log_("Remove Ad: $adName **to be recreated**");
}







