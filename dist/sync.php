<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/18/2017
 * Time: 5:52 PM
 */

include '../functions.php';
include '../classes/Ad.php';

$campaign_id = $argv[1];
// update the local file(database) with adgroups on server
$adGroups = updateAdGroups($campaigns, $campaign_id);



