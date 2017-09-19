<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/18/2017
 * Time: 5:52 PM
 */

include '../functions.php';
include '../classes/Ad.php';



// update the local file(database) with adgroups on server
$adGroups = updateAdGroups($campaigns);

// update the local file(database) with ads on server
updateAds($adGroups);

