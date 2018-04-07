<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/17/2017
 * Time: 3:04 PM
 */

namespace Ztobs\Classes;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201802\cm\Ad;
use Google\AdsApi\AdWords\v201802\cm\AdGroupAd;
use Google\AdsApi\AdWords\v201802\cm\AdGroupAdOperation;
use Google\AdsApi\AdWords\v201802\cm\AdGroupAdService;
use Google\AdsApi\AdWords\v201802\cm\AdGroupAdStatus;
use Google\AdsApi\AdWords\v201802\cm\Operator;
use Google\AdsApi\Common\OAuth2TokenBuilder;

/**
 * This class pauses an ad. To get expanded text ads, run
 * GetExpandedTextAds.php.
 */
class PauseAd {


    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $adGroupId, $adId) {
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

        // Update the status to PAUSED.
        $adGroupAd->setStatus(AdGroupAdStatus::PAUSED);

        // Create ad group ad operation and add it to the list.
        $operation = new AdGroupAdOperation();
        $operation->setOperand($adGroupAd);
        $operation->setOperator(Operator::SET);
        $operations[] = $operation;

        // Pause the ad on the server.
        $adGroupAd = $adGroupAdService->mutate($operations)->getValue()[0];

    }

}
