<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/21/2017
 * Time: 1:02 PM
 */

namespace Ztobs\Classes;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201708\cm\AdGroupCriterionOperation;
use Google\AdsApi\AdWords\v201708\cm\AdGroupCriterionService;
use Google\AdsApi\AdWords\v201708\cm\BiddableAdGroupCriterion;
use Google\AdsApi\AdWords\v201708\cm\BiddingStrategyConfiguration;
use Google\AdsApi\AdWords\v201708\cm\CpcBid;
use Google\AdsApi\AdWords\v201708\cm\Keyword;
use Google\AdsApi\AdWords\v201708\cm\KeywordMatchType;
use Google\AdsApi\AdWords\v201708\cm\Money;
use Google\AdsApi\AdWords\v201708\cm\NegativeAdGroupCriterion;
use Google\AdsApi\AdWords\v201708\cm\Operator;
use Google\AdsApi\AdWords\v201708\cm\UserStatus;
use Google\AdsApi\Common\OAuth2TokenBuilder;

/**
 * This example adds keywords to an ad group. To get ad groups run
 * GetAdGroups.php.
 */
class AddKeywords {

    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $adGroupId, $keywordsArr, $type, $finalUrl, $keywordBid) {
        $adGroupCriterionService =
            $adWordsServices->get($session, AdGroupCriterionService::class);

        $operations = [];

        foreach ($keywordsArr as $keywordString)
        {
            // Create the first keyword criterion.
            $keyword = new Keyword();
            $keyword->setText($keywordString);

            if($type == "EXACT") $keyword->setMatchType(KeywordMatchType::EXACT);
            if($type == "PHRASE") $keyword->setMatchType(KeywordMatchType::PHRASE);
            if($type == "BROAD") $keyword->setMatchType(KeywordMatchType::BROAD);

            // Create biddable ad group criterion.
            $adGroupCriterion = new BiddableAdGroupCriterion();
            $adGroupCriterion->setAdGroupId($adGroupId);
            $adGroupCriterion->setCriterion($keyword);

            // Set additional settings (optional).
            //$adGroupCriterion->setUserStatus(UserStatus::PAUSED);
            $adGroupCriterion->setFinalUrls([$finalUrl]);

            // Set bids (optional).
            $bid = new CpcBid();
            $money = new Money();
            $money->setMicroAmount($keywordBid);
            $bid->setBid($money);
            $biddingStrategyConfiguration = new BiddingStrategyConfiguration();
            $biddingStrategyConfiguration->setBids([$bid]);
            $adGroupCriterion->setBiddingStrategyConfiguration(
                $biddingStrategyConfiguration);

            // Create an ad group criterion operation and add it to the list.
            $operation = new AdGroupCriterionOperation();
            $operation->setOperand($adGroupCriterion);
            $operation->setOperator(Operator::ADD);
            $operations[] = $operation;
        }


        $results = [];

        // Create the ad group criteria on the server and print out some information
        // for each created ad group criterion.
        $result = $adGroupCriterionService->mutate($operations);
        foreach ($result->getValue() as $adGroupCriterion) {
            $id= floatval($adGroupCriterion->getCriterion()->getId());
            $text = $adGroupCriterion->getCriterion()->getText();
            $type = $adGroupCriterion->getCriterion()->getMatchType();
            $results[] = array('id'=>$id, 'text'=>$text, 'type'=>$type);
        }

        return $results;
    }

}

