<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 11:16 AM
 */

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201708\cm\AdGroup;
use Google\AdsApi\AdWords\v201708\cm\AdGroupOperation;
use Google\AdsApi\AdWords\v201708\cm\AdGroupService;
use Google\AdsApi\AdWords\v201708\cm\AdGroupStatus;
use Google\AdsApi\AdWords\v201708\cm\BiddingStrategyConfiguration;
use Google\AdsApi\AdWords\v201708\cm\CpcBid;
use Google\AdsApi\AdWords\v201708\cm\CriterionTypeGroup;
use Google\AdsApi\AdWords\v201708\cm\Money;
use Google\AdsApi\AdWords\v201708\cm\Operator;
use Google\AdsApi\AdWords\v201708\cm\TargetingSetting;
use Google\AdsApi\AdWords\v201708\cm\TargetingSettingDetail;
use Google\AdsApi\AdWords\v201708\cm\ApiException;
use Google\AdsApi\AdWords\v201708\cm\ExemptionRequest;
use Google\AdsApi\AdWords\v201708\cm\PolicyViolationError;

/**
 * This example adds ad groups to a campaign. To get campaigns, run
 * GetCampaigns.php.
 */
class AddAdGroup {


    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $campaignId, $groupName, $myBid, $status ) {
        //$session->setValidateOnly(true);
        $adGroupService = $adWordsServices->get($session, AdGroupService::class);

        $operations = [];

        // Create an ad group with required and optional settings.
        $adGroup = new AdGroup();
        $adGroup->setCampaignId($campaignId);
        $adGroup->setName($groupName);

        // Set bids (required).
        $bid = new CpcBid();
        $money = new Money();
        $money->setMicroAmount($myBid);
        $bid->setBid($money);
        $biddingStrategyConfiguration = new BiddingStrategyConfiguration();
        $biddingStrategyConfiguration->setBids([$bid]);
        $adGroup->setBiddingStrategyConfiguration($biddingStrategyConfiguration);

        // Set additional settings (optional).
        if(trim($status) == "Active") $adGroup->setStatus(AdGroupStatus::ENABLED);
        else $adGroup->setStatus(AdGroupStatus::PAUSED);

        // Targeting restriction settings. Depending on the criterionTypeGroup
        // value, most TargetingSettingDetail only affect Display campaigns.
        // However, the USER_INTEREST_AND_LIST value works for RLSA campaigns -
        // Search campaigns targeting using a remarketing list.
        $targetingSetting = new TargetingSetting();
        $details = [];
        // Restricting to serve ads that match your ad group placements.
        // This is equivalent to choosing "Target and bid" in the UI.
        $details[] =
            new TargetingSettingDetail(CriterionTypeGroup::PLACEMENT, false);
        // Using your ad group verticals only for bidding. This is equivalent
        // to choosing "Bid only" in the UI.
        $details[] = new TargetingSettingDetail(CriterionTypeGroup::VERTICAL, true);
        $targetingSetting->setDetails($details);
        $adGroup->setSettings([$targetingSetting]);

        // Create an ad group operation and add it to the operations list.
        $operation = new AdGroupOperation();
        $operation->setOperand($adGroup);
        $operation->setOperator(Operator::ADD);
        $operations[] = $operation;




        // Create the ad groups on the server and print out some information for
        // each created ad group.
        $result = $adGroupService->mutate($operations);

        foreach ($result->getValue() as $adGroup) {
            $adGroupId = $adGroup->getId();
        }
        return $adGroupId;
    }

}


