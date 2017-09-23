<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 2:34 PM
 */
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201708\cm\AdGroupAd;
use Google\AdsApi\AdWords\v201708\cm\AdGroupAdOperation;
use Google\AdsApi\AdWords\v201708\cm\AdGroupAdService;
use Google\AdsApi\AdWords\v201708\cm\ApiException;
use Google\AdsApi\AdWords\v201708\cm\ExemptionRequest;
use Google\AdsApi\AdWords\v201708\cm\ExpandedTextAd;
use Google\AdsApi\AdWords\v201708\cm\Operator;
use Google\AdsApi\AdWords\v201708\cm\PolicyViolationError;

/**
 * This example adds an expanded text ad to an ad group. To get ad groups,
 * run GetAdGroups.php.
 */
class AddAds {


    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $adGroupId, $ads) {
        //$session->setValidateOnly(true);
        $adGroupAdService =
            $adWordsServices->get($session, AdGroupAdService::class);

        $operations = [];

         // Create an expanded text ad.
        foreach ($ads as $ad)
        {
            $expandedTextAd = new ExpandedTextAd();
            $expandedTextAd->setHeadlinePart1($ad->headline1);
            $expandedTextAd->setHeadlinePart2($ad->headline2);
            $expandedTextAd->setDescription($ad->description);
            $expandedTextAd->setFinalUrls($ad->finalUrls);
            $expandedTextAd->setPath1($ad->path1);
            $expandedTextAd->setPath2($ad->path2);

            // Create ad group ad.
            $adGroupAd = new AdGroupAd();
            $adGroupAd->setAdGroupId($adGroupId);
            $adGroupAd->setAd($expandedTextAd);
            // Optional: Set additional settings.
            //if($ad->status != "active") $adGroupAd->setStatus(AdGroupAdStatus::PAUSED);

            // Create ad group ad operation and add it to the list.
            $operation = new AdGroupAdOperation();
            $operation->setOperand($adGroupAd);
            $operation->setOperator(Operator::ADD);
            $operations[] = $operation;
        }


            $result = $adGroupAdService->mutate($operations);

        // Print out some information about the created ad group ad.
        foreach ($result->getValue() as $adGroupAd) {
            $results[] = array('id'=>$adGroupAd->getAd()->getId(), 'name'=>$adGroupAd->getAd()->getHeadlinePart1());
        }
        if($results) return $results;
    }


}


