<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/16/2017
 * Time: 11:35 PM
 */

namespace Ztobs\Classes;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201802\cm\Ad;
use Google\AdsApi\AdWords\v201802\cm\AdGroupAd;
use Google\AdsApi\AdWords\v201802\cm\AdGroupAdOperation;
use Google\AdsApi\AdWords\v201802\cm\AdGroupAdService;
use Google\AdsApi\AdWords\v201802\cm\Operator;

/**
 * This example removes an ad. To get text ads, run GetExpandedTextAds.php.
 */
class RemoveAd {


    public static function run(
        AdWordsServices $adWordsServices,
        AdWordsSession $session, 
        $adGroupId, 
        $adId
        ) {
        $adGroupAdService =
            $adWordsServices->get($session, AdGroupAdService::class);

        $operations = [];
        // Create ad using an existing ID. Use the base class Ad instead of TextAd
        // to avoid having to set ad-specific fields.
        $ad = new Ad();
        $ad->setId($adId);

        // Create ad group ad.
        $adGroupAd = new AdGroupAd();
        $adGroupAd->setAdGroupId($adGroupId);
        $adGroupAd->setAd($ad);

        // Create ad group ad operation and add it to the list.
        $operation = new AdGroupAdOperation();
        $operation->setOperand($adGroupAd);
        $operation->setOperator(Operator::REMOVE);
        $operations[] = $operation;

        // Remove the ad on the server.
        $result = $adGroupAdService->mutate($operations);

        $adGroupAd = $result->getValue()[0];

    }


}


