<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:57 AM
 */


define("BID", 1000000);
define("CAMPAIGN_BUDGET", 10000);  // minimum acceptable is 10000
define("KEYWORDS_BID", 1000000);

define("TEMP_PATH", "../temp/");
define('LAZER_DATA_PATH', realpath(dirname(__FILE__)).'/temp/'); //Path to folder with tables
define('DB_PRODUCTS', 'Products');
define('DB_CAMPAIGNS', 'Campaigns');
define('DB_ADGROUPS', 'AdGroups');
define('DB_ADS', 'Ads');
define('DB_KEYWORDS', 'Keywords');


define("ADGROUPS_LOCAL_FILE", "adgroups.txt");
define("ADS_LOCAL_FILE", "ads.txt");
define("CAMPAIGNS_LOCAL_FILE", "campaigns.txt");
define("PRODUCTS_LOCAL_FILE", "products.txt");
define("LOG_FILE", "log.log");
define("FEED_PATH", "../dist/");
