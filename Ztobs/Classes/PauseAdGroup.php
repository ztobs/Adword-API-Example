<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/21/2017
 * Time: 11:03 AM
 */

namespace Ztobs\Classes;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201708\cm\AdGroup;
use Google\AdsApi\AdWords\v201708\cm\AdGroupOperation;
use Google\AdsApi\AdWords\v201708\cm\AdGroupService;
use Google\AdsApi\AdWords\v201708\cm\AdGroupStatus;
use Google\AdsApi\AdWords\v201708\cm\Operator;

/**
 * This example removes an ad group. To get ad groups, run GetAdGroups.php.
 */
class PauseAdGroup {

    public static function run(AdWordsServices $adWordsServices,
                               AdWordsSession $session, $adGroupId) {
        $adGroupService = $adWordsServices->get($session, AdGroupService::class);

        $operations = [];
        // Create ad group with REMOVED status.
        $adGroup = new AdGroup();
        $adGroup->setId($adGroupId);
        $adGroup->setStatus(AdGroupStatus::PAUSED);

        // Create ad group operation and add it to the list.
        $operation = new AdGroupOperation();
        $operation->setOperand($adGroup);
        $operation->setOperator(Operator::SET);
        $operations[] = $operation;

        // Remove the ad group on the server.
        $result = $adGroupService->mutate($operations);

        $adGroup = $result->getValue()[0];
        //printf("Ad group with ID %d was removed.\n", $adGroup->getId());
    }

}

