<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:56 AM
 */

// No displaying of error
error_reporting(0);

// removing execution limits
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

// Handle Fatal Error
register_shutdown_function( "fatal_handler" );

// Setting Timezone
date_default_timezone_set('CET');

// Allows mac detect line_endings in fgets methods
ini_set("auto_detect_line_endings", true);

// Setting currency format
setlocale(LC_MONETARY,"en_US");




include "vendor/autoload.php";
include 'dist/variation.php';


use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\AdWordsServices;
use Lazer\Classes\Database;
use Lazer\Classes\LazerException;
use Lazer\Classes\Relation;
use Ztobs\Classes\Ad;
use Ztobs\Classes\AddAdGroup;
use Ztobs\Classes\GetCampaigns;
use Ztobs\Classes\AddAds;
use Ztobs\Classes\GetAdGroupsByCampaign;
use Ztobs\Classes\GetAds;
use Ztobs\Classes\PauseAd;
use Ztobs\Classes\RemoveAd;
use Ztobs\Classes\AddCampaign;
use Ztobs\Classes\RemoveAdGroup;
use Ztobs\Classes\PauseAdGroup;
use Ztobs\Classes\AddKeywords;
use Ztobs\Classes\ResumeAd;
use Ztobs\Classes\ResumeAdGroup;
use Ztobs\Classes\RemoveKeyword;
use Ztobs\Classes\UpdateKeyword;
use Ztobs\Classes\SearchAdGroupInCampaign;
use Ztobs\Classes\SearchAdGroupByName;
use Ztobs\Classes\GetKeywords;




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
$logfile = "";
$campaign_id = "";
$campaigns = [];
$er = false;
$feedPos = 0;
$currentFeed = [];
//updateCampaigns();




////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////      Adword Functions    ////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////

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
 * Function Resumes a paused adgroup
 * @param float $adGroupId
 */
function resumeAdGroup($adGroupId)
{
    global $session;
    ResumeAdGroup::run(new AdWordsServices(), $session, $adGroupId);
}


/*
 * Function Resumes a paused ad
 * @param float $adGroupId
 * @param float $adId
 */
function resumeAd($adGroupId, $adId)
{
    global $session;
    ResumeAd::run(new AdWordsServices(), $session, $adGroupId, $adId);
}


/*
 * Function Pause a paused ad
 * @param float $adGroupId
 * @param float $adId
 */
function pauseAd($adGroupId, $adId)
{
    global $session;
    PauseAd::run(new AdWordsServices(), $session, $adGroupId, $adId);
}


/*
 * Function Removes keyword
 * @param float $adGroupId
 * @param float $keywordId
 */
function removeKeyword($adGroupId, $keywordId)
{
    global $session;
    RemoveKeyword::run(new AdWordsServices(), $session, $adGroupId, $keywordId);
}


/*
 * Function updates keyword in adwords dashboard
 * @params: float $adGroupId, float $keywordId, string $finalUrl
 */
function updateKeyword($adGroupId, $keywordId, $finalUrl)
{
    global $session;
    UpdateKeyword::run(new AdWordsServices(), $session, $adGroupId, $keywordId, $finalUrl);
}




/*
 * Function creates keyword
 * @params: float $adGroupId, array $keywordsArr, string $finalUrl, integer $bid
 * @return: array $keywordIds
 */
function createKeywords($adgroupId, $keywordsArr, $type, $finalUrl, $bid)
{
    $cleanKeywordsArr = array_filter($keywordsArr, "trim");
    if(count($cleanKeywordsArr) < 1) return [];
    global $session;
    $ret = AddKeywords::run(new AdWordsServices(), $session, $adgroupId, $cleanKeywordsArr, $type, $finalUrl, $bid);
    return $ret;
}


/*
 *  Function gets list of ads in adgroup
 *  Params:  Integer $adGroupId, [String $adGroupStatus {ALL, ENABLED, PAUSED}]
 *  Returns: array
 */
function getAds($adGroupId, $adGroupStatus="ALL")
{
    global $session;
    $ad_data = GetAds::run(new AdWordsServices(), $session, $adGroupId, $adGroupStatus);
    return $ad_data;
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
    return $ad_data;
}


/*
 * Function removes ad
 * Params: float $adGroupId, float $adId, string $adName
 */
function removeAd($adGroupId, $adId, $adName)
{
    global $session;
    RemoveAd::run(new AdWordsServices(), $session, $adGroupId, $adId);
    log_("Remove Ad: $adName **to be recreated**");
}



/*
 * Function pauses adgroup and its ads and keywords
 * @param float $adGroupId
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
 * Function retrieves adgroups in a campaign
 * @param float $campaign_id
 * @return array
 */
function getAdGroups($campaign_id)
{
    global $session;
    return GetAdGroupsByCampaign::run(new AdWordsServices(), $session, $campaign_id);
}


/*
 * Function retrieves keywords in a adgroup
 * @param Integer $adgroup_id
 * @return array
 */
function getKeywords($adgroup_id)
{
    global $session;
    return GetKeywords::run(new AdWordsServices(), $session, $adgroup_id);
}


/*
 * Function searches for adgroup by campaign_id and adgroup_id
 * @param float $campaign_id, bigint $adgroup_id
 * @return array
 */
function searchAdGroupFromServer($campaign_id, $adgroup_id)
{
    global $session;
    return SearchAdGroupInCampaign::run(new AdWordsServices(), $session, $campaign_id, $adgroup_id);
}


/*
 * Function searches for adgroup by campaign_id and adgroup_name
 * @param float $campaign_id
 * @return array
 */
function searchAdGroupByName($campaign_id, $adgroup_name)
{
    global $session;
    return SearchAdGroupByName::run(new AdWordsServices(), $session, $campaign_id, $adgroup_name);
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



////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////     Lazer Functions     /////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////


/*
 * Function creates table if not exist
 * @param string $tableName
 * @throws LazerException
 */
function initTables()
{
    // Create Tables
    Database::create(DB_PRODUCTS,
        [
            'id'            =>  'integer',
            'product_id'    =>  'string',
            'product_name'  =>  'string',
            'description'   =>  'string',
            'price'         =>  'string',
            'discount'      =>  'string',
            'status'        =>  'string',
            'url'           =>  'string',
            'keywords'      =>  'string',
            'processed'     =>  'string',
            'campaign_id'   =>  'string'
        ]
    );

    Database::create(DB_ADS,
        [
            'id'            =>  'integer',
            'product_id'    =>  'string',
            'ad_id'         =>  'string',
            'adgroup_id'    =>  'string',
            'campaign_id'   =>  'string',
            'headline1'     =>  'string',
            'headline2'     =>  'string',
            'description'   =>  'string',
            'final_url'     =>  'string',
            'status'        =>  'string',
            'last'          =>  'string'
        ]
    );

    Database::create(DB_ADGROUPS,
        [
            'id'            =>  'integer',
            'adgroup_id'    =>  'string',
            'adgroup_name'  =>  'string',
            'product_id'    =>  'string',
            'campaign_id'   =>  'string',
            'status'        =>  'string',
            'last'          =>  'string'
        ]
    );

    Database::create(DB_KEYWORDS,
        [
            'id'            =>  'integer',
            'keyword_id'    =>  'string',
            'keyword'       =>  'string',
            'type'          =>  'string',
            'adgroup_id'    =>  'string',
            'product_id'    =>  'string',
            'campaign_id'   =>  'string',
            'status'        =>  'string'
        ]
    );

    Database::create(DB_CAMPAIGNS,
        [
            'id'            =>  'integer',
            'campaign_id'   =>  'string',
            'campaign_name' =>  'string'
        ]
    );

    Database::create(DB_EXEC,
        [
            'id'            =>  'integer',
            'position'      =>  'string',
            'campaign_id'   =>  'string'
        ]
     );



    // Relate Tables
    Relation::table('AdGroups')->belongsTo('Campaigns')->localKey('campaign_id')->foreignKey('campaign_id')->setRelation();
    Relation::table('Ads')->belongsTo('Campaigns')->localKey('campaign_id')->foreignKey('campaign_id')->setRelation();
    Relation::table('Ads')->belongsTo('Products')->localKey('product_id')->foreignKey('product_id')->setRelation();
    Relation::table('AdGroups')->belongsTo('Products')->localKey('product_id')->foreignKey('product_id')->setRelation();
    Relation::table('Ads')->belongsTo('AdGroups')->localKey('adgroup_id')->foreignKey('adgroup_id')->setRelation();
    Relation::table('Keywords')->belongsTo('AdGroups')->localKey('adgroup_id')->foreignKey('adgroup_id')->setRelation();


}


/*
 * Function Inserts and updates record into database
 * @param string @table Table name
 * @param array @data Assoc array of values where key is field name
 *
 */
function saveInTable($table, $data, $unique=null)
{
    if($unique)  $row0 = Database::table($table)->where(key($unique), '=', reset($unique))->find();

    if(isset($row0->id)) $row = Database::table($table)->find($row0->id); // Handle update
    else $row = Database::table($table); // Handle insert

    foreach ($data as $key=>$value)
    {
        $row->$key = "$value";
    }
    $row->save();
}



/*
 * Function saves in product database
 * @param array $feed
 */
function saveProduct($feed)
{
    global $campaign_id;

    saveInTable(
        DB_PRODUCTS,
        [
            'product_id'    =>  $feed[0],
            'product_name'  =>  $feed[1],
            'description'   =>  $feed[5],
            'price'         =>  $feed[2],
            'discount'      =>  $feed[10],
            'status'        =>  $feed[18],
            'url'           =>  $feed[16],
            'keywords'      =>  keywords_merge($feed[12],$feed[13],$feed[14]),
            'processed'     =>  'true',
            'campaign_id'   =>  $campaign_id
        ],
        ['product_id'   =>  $feed[0]]
    );
}







////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////  Other Functions    /////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////





/*
 * Function creates and updates log file
 * @param string $data
 */
function log_($data, $logFile_=null)
{
    global $logfile;
    $datetime = date("Y-m-d H:i:s");
    $data = "[$datetime] $data";

    $logfile = ($logFile_==null)? logFileName() : $logFile_;
    writeToFile2("../log/".$logfile, $data."\n");
}

/*
 * Function generates new logfile namif not exist
 * @params: 
 * @return string $logfilename
 */
function logFileName()
{
    global $logfile;
    $datetime = date("Y-m-d H:i:s");
    $stamp = str_replace(":", "_", str_replace(" ", "_", $datetime));
    return ($logfile!="")?$logfile:"log.$stamp.log";
    // sample  log.2017-10-02-11:52:11.log
}

/*
 * Function simply joins all keyords (exact, phrase and broad) into a single string with <!> seperator, to help maintain consistency
 * @params: string $exact, string $phrase, string $broad
 * @return string
 */
function keywords_merge($exact, $phrase, $broad)
{
    return $exact."<!>".$phrase."<!>".$broad;
}

/*
 * Function converts the string from keyword_merge() function into an array
 * @params: string $merged_string
 * @return array
 */
function keywords_split($string)
{
    return explode("<!>", $string);
}

/*
 * Function help organize keywords into array that can easily be imported to adwords
 * @params: integer/string $adGroupId, string $keyword, string $finalurl, $double $bid
 * @return assoc_array
 */
function createKeywordsBulk($adGroupId, $kw, $finalUrl, $bid)
{
    $kw_arr = keywords_split($kw);
    $arr_exact = createKeywords($adGroupId, explode(",", $kw_arr[0]), "EXACT", $finalUrl, $bid);
    $arr_phrase = createKeywords($adGroupId, explode(",", $kw_arr[1]), "PHRASE", $finalUrl, $bid);
    $arr_broad = createKeywords($adGroupId, explode(",", $kw_arr[2]), "BROAD", $finalUrl, $bid);

    $all_arr = array_merge($arr_exact, $arr_phrase, $arr_broad);
    $all_string = [];
    foreach ($all_arr as $vv) 
    {
        $all_string[] = $vv['text'];
    }

    return array('array'=>$all_arr, 'string'=>$all_string);
}


/**
 * Function uses the keywords on adwords server and compares with feed to trigger if there is change, comparing with local db was messing up big time
 * @param $feed
 * @param $adGroupId
 * @return bool
 */
function keywordChange($feed, $adGroupId)
{
    // lets first get keywords from adgroup and list then in array while appending the type to it. Reason for appending type is so that keywords from eg broad dont mixup with same keyword in exact
    $kws_server = getKeywords($adGroupId);
    $kw_s = [];
    foreach ($kws_server as $kw_server)
    {
        $kw_s[] = $kw_server['keyword']."!!".$kw_server['type'];
    }

    // Now lets get keywords from feed treating the feed columns seperately so that we can append the feed type to it
    $kws_feed = [];
    $kws_feed_e = explode(",", $feed[12]);
    foreach ($kws_feed_e as $kw_feed_e)
    {
        if($kw_feed_e != "") $kws_feed[] = $kw_feed_e."!!EXACT";
    }

    $kws_feed_p = explode(",", $feed[13]);
    foreach ($kws_feed_p as $kw_feed_p)
    {
        if($kw_feed_p != "") $kws_feed[] = $kw_feed_p."!!PHRASE";
    }

    $kws_feed_b = explode(",", $feed[14]);
    foreach ($kws_feed_b as $kw_feed_b)
    {
        if($kw_feed_b != "") $kws_feed[] = $kw_feed_b."!!BROAD";
    }

    // We can now check for each keyword in feed with keyword from adgroup server
    $notFound = FALSE;
    foreach ($kws_feed as $kw_feed)
    {
        if(!in_array($kw_feed, $kw_s))
        {
            $notFound = TRUE;
            break;
        }
    }

    // In case the no new keyword was added to feed but was subtracted, the logic above wouldnt detect it, we just compare the sizes of both array. keyword change is taken care of 100%
    if(count($kws_feed) != count($kw_s)) $notFound = TRUE;

    // return
    return $notFound;
}


/*
 * Function checks if product_id, price, description, product name, discount, url and status is empty and logs to file which feedline is empty
 * @params array $feed, integer $feedPos
 * @return boolean
 */
function eligibleProduct($feed, $feedPos)
{
    global $er;
    $error = "";
    if(isEmpty($feed[0])) $error .= "Product Id, ";
    if(isEmpty($feed[2])) $error .= "Price, ";
    if(isEmpty($feed[5])) $error .= "Description, ";
    if(isEmpty($feed[1])) $error .= "Product Name, ";
    if(isEmpty($feed[10])) $error .= "Discount Percentage, ";
    if(isEmpty($feed[16])) $error .= "Product URL, ";
    if(isEmpty($feed[18])) $error .= "Status, ";

    if($error != "")
    {
        log_("**Error: Ad was not created because the following cannot be empty in the feed ($error) at FeedLine $feedPos");
        $er = true;
    }
    else return true;
}


/*
 * Function checks if keyword is empty and logs to file which feedline is empty
 * @params: array $feed, integer $feedPos
 * @return boolean
 */
function eligibleKeywords($feed, $feedPos)
{
    global $er;
    if(isEmpty($feed[12]))
    {
        log_("**Notice: No keyword found at FeedLine $feedPos");
        $er = true;
    }
    else return true;
}


/*
 * Function check if a string is null or empty
 * @param string $string
 * @return boolean
 */
function isEmpty($string)
{
    if($string == null || $string == "") return true;
    else return false;
}





/*
 * Function used to update the currently available campaigns to array and database
 */
function updateCampaigns()
{
    global $campaigns;
    $campaigns = getCampaigns(); 
    foreach ($campaigns as $campaign)
    {
        $name = $campaign['name']; 
        $id = $campaign['id']; 
        saveInTable(DB_CAMPAIGNS, ["campaign_id"=>$id, "campaign_name"=>$name], ["campaign_id"=>$id]);
    }
    return TRUE;

}


/*
 * Function writes data to file
 * @params: string $fileName, string $data
 * @return $resp
 */
function writeToFile($fileName, $data)
{
    $file = fopen(TEMP_PATH.$fileName, "a+");
    $resp = fwrite($file, $data);
    fclose($file);
    return $resp;
}


/*
 * Function writes data to file
 * @params: string $fileName, string $data
 * @return $resp
 */
function writeToFile2($fileName, $data)
{
    $file = fopen($fileName, "a+");
    $resp = fwrite($file, $data);
    fclose($file);
    return $resp;
}


/*
 * Function gets campaign_id using name
 * @param string $name
 * @return float $id
 */
function getCampaignIdByName($name)
{
    global $campaigns;
    $id = null;
    foreach($campaigns as $campaign)
    {
        //echo "Manually updating adgroups and ads or using different script for existing campaign can lead to irrational behaviour and fatal error.\nMake sure same script runs a campaign always so that the local database in temp folder matches with adwords dashboard\nPlease cancel if violated\n\n";
        if($campaign['name'] == $name) $id = $campaign['id'];
    }

    if($id == null)
    {
        echo "The campaign name '$name' wasnt found, we are going to create it\n Type: y to continue \n";
        $stdin = fopen('php://stdin', 'r');
        $response = fgetc($stdin);

        if(strtolower($response) == "y")
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




/*
 * Function pauses last adgroup in database and adwords dashboard
 */
function pauseLastAdGroup($er_str, $pos, $adGroupId, $adGroupName)
{
    if($adGroupId != 0)
    {
        try 
        {
            $adRow = getAds($adGroupId);

            if(strpos($er_str, "CriterionError.KEYWORD")!== FALSE)  // Checks if the error is from keyword
            {
                log_("Adgroup: '".$adGroupName."' Paused, due to error in keyword");
            }
            elseif(count($adRow) < 1) // if ad is not created but adgroup is created
            {
                log_("Adgroup: '".$adGroupName."' Paused or removed, due to error creating ads");
            }
            
            else
            {
                log_("!!! A previously active adgroup '".$adGroupName."' was paused due to previous error at Feedline $pos in keyword");
            }


            pauseAdGroup($adGroupId);
            saveInTable(DB_ADGROUPS, ["status" => "Not Active"], ["adgroup_id" => $adGroupId]);

            // removeAdGroup($adGroupId);
            // Database::table(DB_ADGROUPS)->where("adgroup_id", "=", $adGroupId)->find()->delete(); 
        } 
        catch (Exception $e) 
        {
            echo "Exception '$e' occured \n====> $er_str <=====";
        }
    }

}


/*
 * Function to convert csv to array
 * @param: $filename
 * return: assoc 2D array of feeds
 */
function feedToArr($fileName, $feedStart)
{
    if(!filter_var($fileName, FILTER_VALIDATE_URL)) $fileName = FEED_PATH.$fileName; // appending file path if not a url
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
 * Function to make total ads to create from product feeds,
 * params: $feedArr, $variation_arr
 * return: array of ads object
 */
function creator($feedArr, $variation_arr, $feedStart)
{
    global $campaign_id;
    global $feedPos;
    global $currentFeed;
    echo "Creating Adgroups, Ads and Keywords ....\n";
    $count = 0;
    foreach ($feedArr as $feed)
    {
        $feedPos = $feedStart+$count;
        $currentFeed = $feed;
        echo "$feedPos,";


        $kw = iconv(mb_detect_encoding(keywords_merge($feed[12], $feed[13], $feed[14]), mb_detect_order(), true), "UTF-8", keywords_merge($feed[12], $feed[13], $feed[14]));
        //$keywords_arr = explode(",", preg_replace('/[^A-Za-z0-9\-\(\) ]/', '', $kw));  //remove special characters and convert to array
        

        $product_url = $feed[16];
        $is_https = strpos($product_url, "https://");
        $product_url = str_replace("http://", "", $product_url);
        $product_url = str_replace("https://", "", $product_url);
        $finalUrl = $is_https?"https://".$product_url:"http://".$product_url;

        $adGroupData = searchAdGroupByName($campaign_id, $feed[1]." (".$feed[0].")");
        $ret = checkType($feed, $adGroupData[0]['id']);

//         var_dump($ret['type']);
//         echo "== END ==\n";

        if(in_array("skip", $ret['type']))
        {
            // do nothing
        }
        else
        {
            // New: Creating new records
            if(in_array("new", $ret['type']))
            {
                createAll($feed, $variation_arr, $feedPos, $kw, $finalUrl);
            }



            // Activate: Activating paused
            if(in_array("activate", $ret['type']))
            {
                $data = $ret['data'];

                if (count($adGroupData) > 0) 
                {
                    resumeAdGroup($adGroupData[0]['id']);
                    saveInTable(DB_ADGROUPS, ["status" => "Active"], ["adgroup_id" => $adGroupData[0]['id']]);

                    $adData = getAdsByProductId($feed[0], $adGroupData[0]['id'], "last");
                    if ($adData) {
                        foreach ($adData as $dd) {
                            resumeAd($adGroupData[0]['id'], $dd->ad_id);
                            saveInTable(DB_ADS, ["status" => "Active"], ["id" => $dd->id]);
                        }
                        log_("Product: '" . $feed[1] . "' Resumed");
                    }
                }
            }


            // Pause: Pausing Adgroup and Ads
            if(in_array("pause", $ret['type']))
            {

                if (count($adGroupData) > 0) 
                {
                    pauseAdGroup($adGroupData[0]['id']);
                    saveInTable(DB_ADGROUPS, ["status" => "Not Active"], ["adgroup_id" => $adGroupData[0]['id']]);

                    $adData = getAds($adGroupData[0]['id']);
                    if (count($adData) > 0 ) 
                    {
                        foreach ($adData as $dd) {
                            pauseAd($adGroupData[0]['id'], $dd['id']);
                            saveInTable(DB_ADS, ["status" => "Not Active"], ["ad_id" => $dd['id']]);
                        }
                        log_("Product: '" . $feed[1] . "' Paused");
                    }
                }
            }


            // Name_Change: Pausing Old and Creating new Record for Name Change
            if(in_array("name_change", $ret['type']))
            {
                // Pausing Adgroups
                if(count($adGroupData) > 0)
                {
                    pauseAdGroup($adGroupData[0]['id']);
                    saveInTable(DB_ADGROUPS, ["status"=>"Not Active", "last"=>"false"], ["adgroup_id"=>$adGroupData[0]['id']]);

                    $adData = getAds($adGroupData[0]['id']);
                    if(count($adData) > 0)
                    {
                        foreach ($adData as $dd)
                        {
                            pauseAd($adGroupData[0]['id'], $dd['id']);
                            saveInTable(DB_ADS, ["status"=>"Not Active", "last"=>"false"], ["ad_id"=>$dd['id']]);
                        }
                        log_("Product: '".$feed[1]."' Paused");
                    }
                }
                // Create New
                createAll($feed, $variation_arr, $feedPos, $kw, $finalUrl);

            }


            // Keyword_Change: Replacing the keywords
            if(in_array("keyword_change", $ret['type']))
            {
                if(count($adGroupData) > 0 )
                {
                    if($feed[18] == "Active" && $adGroupData[0]['status'] == "PAUSED")
                    {
                        resumeAdGroup($adGroupData[0]['id']);
                        saveInTable(DB_ADGROUPS, ["status" => "Active"], ["adgroup_id" => $adGroupData[0]['id']]);

                        $adData = getAdsByProductId($feed[0], $adGroupData[0]['id'], "last");
                        if ($adData)
                        {
                            foreach ($adData as $dd) {
                                resumeAd($adGroupData[0]['id'], $dd->ad_id);
                                saveInTable(DB_ADS, ["status" => "Active"], ["id" => $dd->id]);
                            }
                            log_("Product: '" . $feed[1] . "' Resumed");
                        }
                    }
                    // Removing keywords
                    $keywords = getKeywordsByProductId($feed[0]);
                    foreach ($keywords as $keyword)
                    {
                        removeKeyword($adGroupData[0]['id'], $keyword->keyword_id);
                        Database::table(DB_KEYWORDS)->find($keyword->id)->delete();
                    }

                    // Adding Keywords
                    $kw_string = [];
                    $retn = createKeywordsBulk($adGroupData[0]['id'], $kw, $finalUrl, KEYWORDS_BID);
                    foreach ($retn['array'] as $kws)
                    {
                        saveInTable(
                            DB_KEYWORDS,
                            [
                                'keyword_id'    =>  $kws['id'],
                                'keyword'       =>  $kws['text'],
                                'type'          =>  $kws['type'],
                                'adgroup_id'    =>  $adGroupData[0]['id'],
                                'campaign_id'   =>  $campaign_id,
                                'product_id'    =>  $feed[0],
                                'status'        =>  $feed[18]
                            ]
                        );
                    }
                    $kw_string = $retn['string'];

                    // logging
                    log_("Keywords in Product: '".$feed[1]."' updated to: '".implode(", ", $kw_string)."'");
                }

            }



            // Other_Change: Pausing Ad and Creating new
            if(in_array("other_change", $ret['type']))
            {
                if(count($adGroupData) > 0)
                {
                    if($feed[18] == "Active" && $adGroupData[0]['status'] == "PAUSED")
                    {
                        resumeAdGroup($adGroupData[0]['id']);
                        saveInTable(DB_ADGROUPS, ["status" => "Active"], ["adgroup_id" => $adGroupData[0]['id']]);
                    }

                    // Pausing Ads
                    $adsData = getAds($adGroupData[0]['id']);
                    foreach ($adsData as $adData)
                    {
                        pauseAd($adGroupData[0]['id'], $adData['id']);
                        saveInTable(DB_ADS, ["status"=>"Not Active", "last"=>"false"], ["ad_id"=>$adData['id']]);
                        log_("Ad: '".$adData['headlinePart1']." - ".$adData['description']."' Paused");
                    }

                    // Creating Ads
                    $headlines = makeAds($feed, $variation_arr, $adGroupData[0]['id'], $finalUrl);

                    // Logging
                    log_("Ads: '".implode(", ", $headlines)."' Created");
                }


            }

        }

        saveProduct($feed);

        $count++;
    }
    echo "\n";


}



/*
 * Function retrieves adgroup data by product_id from adgroups database
 * @param integer $product_id
 * @return stdObj adgroup
 */
function getAdgroupByProductId($product_id, $activeOnly=false)
{
    global $campaign_id;
    if($activeOnly) $row = Database::table(DB_ADGROUPS)->where('product_id', "=", $product_id)->andWhere('campaign_id', '=', $campaign_id)->andWhere('status', '=', 'Active')->andWhere('last', '=', 'true')->find();
    else $row = Database::table(DB_ADGROUPS)->where('product_id', "=", $product_id)->andWhere('campaign_id', '=', $campaign_id)->find();
    if(isset($row->id)) return $row;
}


/*
 * Function retrieves ad data by product_id from ads database
 * @param integer $product_id
 * @return array of stdObj ad row
 */
function getAdsByProductId($product_id, $adGroupId, $lastOnly=false)
{
    global $campaign_id;
    if($lastOnly) $table = Database::table(DB_ADS)->where('product_id', "=", $product_id)->andwhere('last', '=', 'true')->andWhere('adgroup_id', '=', $adGroupId)->andWhere('campaign_id', '=', $campaign_id)->findAll();
    else $table = Database::table(DB_ADS)->where('product_id', "=", $product_id)->andWhere('adgroup_id', '=', $adGroupId)->andWhere('campaign_id', '=', $campaign_id)->findAll();
    if(count($table) > 0) return $table;
}



/*
 * Function retrieves keywords by product_id from ads database
 * @param integer $product_id
 * @return array of stdObj keyword row
 */
function getKeywordsByProductId($product_id)
{
    global $campaign_id;
    $table = Database::table(DB_KEYWORDS)->where('product_id', "=", $product_id)->andWhere('campaign_id', '=', $campaign_id)->findAll();
    if(count($table) > 0) return $table;
}




/*
 * Function creates new records, adgroups, ad, keywords
 */
function createAll($feed, $variation_arr, $feedPos, $kw, $finalUrl)
{
    if(eligibleProduct($feed, $feedPos))
    {
        global $campaign_id;

        // Create AdGroup
        $adGroupName = $feed[1]." (".$feed[0].")";
        $adGroupId = createAdGroup($campaign_id, $adGroupName, BID, $feed[18]);
        saveInTable(
            DB_ADGROUPS,
            [
                'adgroup_id'    =>  $adGroupId,
                'adgroup_name'  =>  $adGroupName,
                'product_id'    =>  $feed[0],
                'campaign_id'   =>  $campaign_id,
                'status'        =>  $feed[18],
                'last'          =>  'true'
            ]
        );


        // Compile ads per product
        makeAds($feed, $variation_arr, $adGroupId, $finalUrl);


        // Create Keywords
        $kw_string = [];
        if(eligibleKeywords($feed, $feedPos))
        {
            $ret = createKeywordsBulk($adGroupId, $kw, $finalUrl, KEYWORDS_BID);
            foreach ($ret['array'] as $kws)
            {
                saveInTable(
                    DB_KEYWORDS,
                    [
                        'keyword_id'    =>  $kws['id'],
                        'keyword'       =>  $kws['text'],
                        'type'          =>  $kws['type'],
                        'adgroup_id'    =>  $adGroupId,
                        'campaign_id'   =>  $campaign_id,
                        'product_id'    =>  $feed[0],
                        'status'        =>  $feed[18]
                    ]
                );
            };
            $kw_string = $ret['string'];
        }

        // Logging
        log_("Create Product: '".$feed[1]."' With ".count($variation_arr)." Ads Variations and Keywords (".implode(", ", $kw_string).")");
    }

}


/*
 * Function Creates products with ad variations
 * @params: array $feed, array $variation_arr, double $adGroupId
 */
function makeAds($feed, $variation_arr, $adGroupId, $finalUrl)
{
    global  $campaign_id;
    $ads = [];
    $description = str_replace("!", ".", $feed[5]);

    foreach ($variation_arr as $var)
    {
        $productNameLimit = 30 - (strlen($var['headline1']) - 15);
        $productName = substr($feed[1], 0, $productNameLimit);

        $productShortNameLimit = 30 - (strlen($var['headline1']) - 20);
        $productShortName = substr($feed[6], 0, $productShortNameLimit);
        $headline1 = str_replace("{{productName}}", $productName, $var['headline1']);
        $headline1 = str_replace("{{productShortName}}", $productShortName, $headline1);
        $headline1 = str_replace("{{productPrice}}", str_replace(" EUR", "", $feed[2]), $headline1);
        $headline1 = str_replace("{{productDiscountInPercent}}", $feed[10], $headline1);

        $headline2 = str_replace("{{productName}}", $productName, $var['headline2']);
        $headline2 = str_replace("{{productShortName}}", $productShortName, $headline2);
        $headline2 = str_replace("{{productPrice}}", str_replace(" EUR", "", $feed[2]), $headline2);
        $headline2 = str_replace("{{productDiscountInPercent}}", $feed[10], $headline2);

        $ads[] = new Ad($feed[0], $headline1, $headline2, $description, array($finalUrl), $feed[18], null, null);
    }

    // Create Ads
    $ads_ret = [];
    $ad_ids = createAds($adGroupId, $ads);
    for($j=0; $j<count($variation_arr); $j++)
    {
        saveInTable(
            DB_ADS,
            [
                'product_id'    =>  $feed[0],
                'ad_id'         =>  $ad_ids[$j],
                'adgroup_id'    =>  $adGroupId,
                'campaign_id'   =>  $campaign_id,
                'headline1'     =>  $ads[$j]->headline1,
                'headline2'     =>  $ads[$j]->headline2,
                'description'   =>  $description,
                'final_url'     =>  $finalUrl,
                'status'        =>  $feed[18],
                'last'          =>  'true'
            ]
        );

        $ads_ret[] = $ads[$j]->headline1;
    }

    return $ads_ret;

}


/*
 * Function prepares database for next run
 */
function prepare4NextRun()
{
    global $campaign_id;
    echo "\nPreparing database for next run\n";

    removeNullProductDb();
    $prodd = Database::table(DB_PRODUCTS)->where("campaign_id", "=", $campaign_id)->findAll();
    foreach($prodd as $pd)
    {
        $row1 = Database::table(DB_PRODUCTS)->where("product_name", "=", $pd->product_name)->find(); //Edit row with ID 1
        $row1->processed = "false"; // setting all product to not processed
        $row1->save();
        echo ".";
    }
    Database::table(DB_EXEC)->delete(); // Setting process pointer to the beginning
    echo "\n";
}


/*
 * Function to check if its a new product, name changed, to be paused, keyword change, others changed, or already proceeds product
 * @params array $feed
 */
function checkType($feed, $adGroupId)
{
    $row = Database::table(DB_PRODUCTS)->where('product_id', '=', $feed[0])->find();


    $type = [];
    if(isset($row->id))
    {


        if($row->processed == 'true')
        {
            // For Processed, will be skipped
            $type[] = 'skip';
        }
        else
        {
            // For Name Change
            if($feed[1] != $row->product_name) $type[] = 'name_change';

            // For Others Change
            if($feed[2] != $row->price || $feed[5] != $row->description || $feed[10] != $row->discount || $feed[16] != $row->url) $type[] = 'other_change';

            // For Keywords Change
        if(keywords_merge($feed[12],$feed[13],$feed[14]) != $row->keywords) $type[] = 'keyword_change';
//            if(keywordChange($feed, $adGroupId)) $type[] = 'keyword_change';

            // For activate
            if($feed[18] == 'Active' && $row->status != 'Active') $type[] = 'activate';

            // For Pause
            if($feed[18] != 'Active' && $row->status == 'Active') $type[] = 'pause';
        }

        if(count($type) < 1) $type[] = 'skip';


    }
    else
    {
        // For new
        $type[] = 'new';
    }

    return array('type'=>$type, 'data'=>$row);
}



/*
 * Function retrieves data from products database that hasnt been proceeds, i.e products that did not occur in feed, also called GONE
 * @return: array of stdObj
 */
function getGone()
{
    global $campaign_id;
    $p = Database::table(DB_PRODUCTS)->where('processed', '=', 'true')->andWhere('campaign_id', '=', $campaign_id)->findAll();
    $processed = [];
    foreach ($p as $proc) 
    {
        $processed[] = $proc->product_name." (".$proc->product_id.")";
    }

    $a = getAdGroups($campaign_id);
    $all = [];
    foreach ($a as $al) {
        $all[] = $al['name'];
    }

    return array_diff( $all, $processed );
}


/*
 * Function remove null values from product database
 * @return: 
 */
function removeNullProductDb()
{
    global $campaign_id;
    try 
    {
        $tbs = Database::table(DB_PRODUCTS)->where("campaign_id", "=", $campaign_id)->findAll();
        Database::table(DB_PRODUCTS)->delete();

        foreach ($tbs as $tb) 
        {
            $row = Database::table(DB_PRODUCTS);

            $row->product_id = $tb->product_id;
            $row->product_name = $tb->product_name;
            $row->description = $tb->description;
            $row->price = $tb->price;
            $row->discount = $tb->discount;
            $row->status = $tb->status;
            $row->url = $tb->url;
            $row->keywords = $tb->keywords;
            $row->processed = $tb->processed;
            $row->campaign_id = $tb->campaign_id;
            $row->save();
        }
    } catch (LazerException  $e) {}
}



/*
 * Function Pauses gone
 * @param array of stdObj $gones
 */
function pauseGones($gones, $logFile)
{
    global $campaign_id;

    foreach ($gones as $gone)
    {
        // Retrieving Adgroup data
        $adGroupData = searchAdGroupByName($campaign_id, $gone);
        if(count($adGroupData) > 0)
        {
            pauseAdGroup($adGroupData[0]['id']);
            saveInTable(DB_ADGROUPS, ["status" => "Not Active"], ["adgroup_id" => $adGroupData[0]['id']]);

            $adData = getAds($adGroupData[0]['id']);
            if ($adData) {
                foreach ($adData as $dd) {
                    pauseAd($adGroupData[0]['id'], $dd['id']);
                    saveInTable(DB_ADS, ["status" => "Not Active"], ["ad_id" => $dd['id']]);
                }
                saveInTable(DB_PRODUCTS, ["status" => "Not Active"], ["product_id" => $dd['id']]);
                log_("Product: '" . $dd['headlinePart1']." - ".$dd['description']. "' Paused; No longer Exist in Feed", $logFile);
            }
        }
    }
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
    saveInTable(DB_CAMPAIGNS, ["campaign_id"=>$id, "campaign_name"=>$name], ["campaign_id"=>$id]);
    log_("Create Campaign: $name");
    return $ret;
}


/*
 * Function populates local adgroup table from server
 * params: null
 * return: Integer (number of updates)
 */
function populateAdgroupDB()
{
    global $session;
    global $campaign_id;

    $adGroupsFromServer = getAdGroups($campaign_id);
    $count = 0;
    Database::table(DB_ADGROUPS)->delete(); // lets 1st empty the db

    echo "Populating local AdGroup Database .\n";
    foreach ($adGroupsFromServer as $adGroupData) {
   

        $adGroupId = $adGroupData['id'];
        $adGroupName = $adGroupData['name'];
        $productId = explode(")", explode("(", $adGroupData['name'])[1])[0];
        $adGroupStatus = ($adGroupData['status']=="PAUSED") ? "Not Active" : "Active";
        
        saveInTable(
                DB_ADGROUPS,
                [
                    'adgroup_id'    =>  $adGroupId,
                    'adgroup_name'  =>  $adGroupName,
                    'product_id'    =>  $productId,
                    'campaign_id'   =>  $campaign_id,
                    'status'        =>  $adGroupStatus,
                    'last'          =>  'true'
                ]
            );
        echo ".";
        $count++;
    }

    return $count;
}


/*
 * Function populates local ads table from server
 * params: null
 * return: Integer (number of updates)
 */
function populateAdDB()
{
    global $session;
    global $campaign_id;
    global $variation;

    $adGroupsFromServer = getAdGroups($campaign_id);
    $variation_count = count($variation);
    $count = 0;
    Database::table(DB_ADS)->delete(); // lets 1st empty the db

    echo "Populating local Ad Database .\n";
    foreach ($adGroupsFromServer as $adGroupData) 
    {
        $adGroupId = $adGroupData['id'];
        $adGroupName = $adGroupData['name'];
        $productId = explode(")", explode("(", $adGroupData['name'])[1])[0];
        $adGroupStatus = ($adGroupData['status']=="PAUSED") ? "Not Active" : "Active";

        $adData = getAds($adGroupId);
        $ads_count = count($adData);
        
        for ($i=0; $i<$ads_count; $i++) 
        { 
            $adId = $adData[$i]['id'];
            $h1 = $adData[$i]['headlinePart1'];
            $h2 = $adData[$i]['headlinePart2'];
            $description = $adData[$i]['description'];
            $finalUrl = $adData[$i]['finalUrl'];
            $adStatus = ($adData[$i]['status']=="PAUSED") ? "Not Active" : "Active";
            $adLast = ( $i >= ($ads_count - $variation_count) )? "true" : "false";

            saveInTable(
                    DB_ADS,
                    [
                        'product_id'    =>  $productId,
                        'ad_id'         =>  $adId,
                        'adgroup_id'    =>  $adGroupId,
                        'campaign_id'   =>  $campaign_id,
                        'headline1'     =>  $h1,
                        'headline2'     =>  $h2,
                        'final_url'     =>  $finalUrl,
                        'status'        =>  $adStatus,
                        'last'          =>  $adLast
                    ]
                );
            $count++;
        }

        echo ".";
    }

    return $count;
}



/*
 * Function populates local keywords table from server
 * params: null
 * return: Integer (number of updates)
 */
function populateKeywordDB()
{
    global $session;
    global $campaign_id;

    $adGroupsFromServer = getAdGroups($campaign_id);
    $count = 0;
    Database::table(DB_KEYWORDS)->delete(); // lets 1st empty the db

    echo "Populating local keyword Database .\n";
    foreach ($adGroupsFromServer as $adGroupData) {
   

        $adGroupId = $adGroupData['id'];
        $adGroupName = $adGroupData['name'];
        $productId = explode(")", explode("(", $adGroupData['name'])[1])[0];
        $adGroupStatus = ($adGroupData['status']=="PAUSED") ? "Not Active" : "Active";

        $keywords = getKeywords($adGroupId);

        foreach ($keywords as $keyword) {

            saveInTable(
                   DB_KEYWORDS,
                    [
                        'keyword_id'    =>  $keyword['id'],
                        'keyword'       =>  $keyword['keyword'],
                        'type'          =>  $keyword['type'],
                        'adgroup_id'    =>  $adGroupId,
                        'product_id'    =>  $productId,
                        'campaign_id'   =>  $campaign_id,
                        'status'        =>  'Active'
                    ]
                );

            $count++;
        }
        echo ".";
    }
    return $count;
}

/*
 * Function helps restart script and continue from where stopped when a fatal error occurs
 */
function fatal_handler() {
    global $feedPos;
    global $argv;
    global $logfile;
    global $campaign_id;
    global $currentFeed;

    $currentAdGroupId = 0;

    if(!$currentFeed == [])
    {
        $currentAdGroupName = ltrim($currentFeed[1])." (".$currentFeed[0].")";
        $currentAdGroupData = searchAdGroupByName($campaign_id, $currentAdGroupName);
        if(count($currentAdGroupData) > 0) $currentAdGroupId = $currentAdGroupData[0]['id'];
    }

        

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
        if($errno)// === E_ERROR)
        {
            $feedCont = $feedPos+1;
            
            
            $pos = strpos($errstr, "violatingText");
            $er = "$errstr";
            if($pos !== FALSE)
            {
                $er = explode("}", substr($errstr, $pos))[0];
                log_("Fatal Error at FeedLine $feedPos: $er");
            }

            if(strpos($er, "UNSUPPORTED_VERSION") !== FALSE)
            {
                $er = "API Version is no longer supported";
                log_("Fatal Error at FeedLine $feedPos: $er");
                exit(99);
            }

            if(strpos($er, "CriterionError.KEYWORD") !== FALSE)
            {
                $er = "InvalidKeyword";
                log_("Fatal Error at FeedLine $feedPos: $er");
            }

            if(strpos($er, "RateExceededError") !== FALSE)
            {
                $er = "API Usage Exceeded, Try again tomorrow to continue from where it stopped";
                log_("Fatal Error at FeedLine $feedPos: $er");
                exit(99);
            }




            if(strpos($er, "AD_NOT_UNDER_ADGROUP") !== FALSE)
            {
                exit(0);
            }


            if(strpos($er, "AdGroupServiceError.DUPLICATE_ADGROUP_NAME") !== FALSE)
            {
                $er = "Cannot add adgroup '$currentAdGroupName', because it already exist, we would remove it and attempt to recreate it.";
                $feedCont--;
                log_("Fatal Error at FeedLine $feedPos: $er");
                try
                {
                    removeAdGroup($currentAdGroupId);
                    Database::table(DB_ADGROUPS)->where("adgroup_id", "=", $currentAdGroupId)->find()->delete(); 
                }
                catch(Exception $e)
                {
                    log_("Fatal Error at FeedLine $feedPos: We were denied permission to remove adgroup");
                    $feedCont++;
                }
                
            }
            elseif (isset($currentAdGroupName))
            {
                // Removing adGroup that failed
                pauseLastAdGroup($errstr, $feedPos, $currentAdGroupId, $currentAdGroupName);
            }

            

            // Saving stop point for continuation later
            saveInTable(
                DB_EXEC,
                [
                    'position'      =>  $feedCont,
                    'campaign_id'   =>  $campaign_id
                ],
                ['campaign_id'   =>  $campaign_id]
            );

            echo "\nFatal Error at FeedLine $feedPos, check log for details\nRestarting script from Feedline $feedCont \n";

        }

    }
}

?>