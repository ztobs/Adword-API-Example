<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/19/2017
 * Time: 7:25 AM
 */

namespace Ztobs\Classes;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201802\cm\AdServingOptimizationStatus;
use Google\AdsApi\AdWords\v201802\cm\AdvertisingChannelType;
use Google\AdsApi\AdWords\v201802\cm\BiddingStrategyConfiguration;
use Google\AdsApi\AdWords\v201802\cm\BiddingStrategyType;
use Google\AdsApi\AdWords\v201802\cm\Budget;
use Google\AdsApi\AdWords\v201802\cm\BudgetBudgetDeliveryMethod;
use Google\AdsApi\AdWords\v201802\cm\BudgetOperation;
use Google\AdsApi\AdWords\v201802\cm\BudgetService;
use Google\AdsApi\AdWords\v201802\cm\Campaign;
use Google\AdsApi\AdWords\v201802\cm\CampaignOperation;
use Google\AdsApi\AdWords\v201802\cm\CampaignService;
use Google\AdsApi\AdWords\v201802\cm\CampaignStatus;
use Google\AdsApi\AdWords\v201802\cm\FrequencyCap;
use Google\AdsApi\AdWords\v201802\cm\GeoTargetTypeSetting;
use Google\AdsApi\AdWords\v201802\cm\GeoTargetTypeSettingNegativeGeoTargetType;
use Google\AdsApi\AdWords\v201802\cm\GeoTargetTypeSettingPositiveGeoTargetType;
use Google\AdsApi\AdWords\v201802\cm\Level;
use Google\AdsApi\AdWords\v201802\cm\ManualCpcBiddingScheme;
use Google\AdsApi\AdWords\v201802\cm\Money;
use Google\AdsApi\AdWords\v201802\cm\NetworkSetting;
use Google\AdsApi\AdWords\v201802\cm\Operator;
use Google\AdsApi\AdWords\v201802\cm\TimeUnit;

/**
 * This example adds campaigns.
 */
class AddCampaign {

    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $name, $budget_amount, $cap=null) {
        $budgetService = $adWordsServices->get($session, BudgetService::class);

        // Create the shared budget (required).
        $budget = new Budget();
        $budget->setName($name.' Budget');
        $money = new Money();
        $money->setMicroAmount($budget_amount);
        $budget->setAmount($money);
        $budget->setDeliveryMethod(BudgetBudgetDeliveryMethod::STANDARD);

        $operations = [];

        // Create a budget operation.
        $operation = new BudgetOperation();
        $operation->setOperand($budget);
        $operation->setOperator(Operator::ADD);
        $operations[] = $operation;

        // Create the budget on the server.
        $result = $budgetService->mutate($operations);
        $budget = $result->getValue()[0];

        $campaignService = $adWordsServices->get($session, CampaignService::class);

        $operations = [];

        // Create a campaign with required and optional settings.
        $campaign = new Campaign();
        $campaign->setName($name);
        $campaign->setAdvertisingChannelType(AdvertisingChannelType::SEARCH);

        // Set shared budget (required).
        $campaign->setBudget(new Budget());
        $campaign->getBudget()->setBudgetId($budget->getBudgetId());

        // Set bidding strategy (required).
        $biddingStrategyConfiguration = new BiddingStrategyConfiguration();
        $biddingStrategyConfiguration->setBiddingStrategyType(
            BiddingStrategyType::MANUAL_CPC);

        // You can optionally provide a bidding scheme in place of the type.
//        $biddingScheme = new ManualCpcBiddingScheme();
//        $biddingScheme->setEnhancedCpcEnabled(false);
//        $biddingStrategyConfiguration->setBiddingScheme($biddingScheme);
//
        $campaign->setBiddingStrategyConfiguration($biddingStrategyConfiguration);

        // Set network targeting (optional).
        $networkSetting = new NetworkSetting();
        $networkSetting->setTargetGoogleSearch(true);
        $networkSetting->setTargetSearchNetwork(true);
        $networkSetting->setTargetContentNetwork(true);
        $campaign->setNetworkSetting($networkSetting);

        // Set additional settings (optional).
        // Recommendation: Set the campaign to PAUSED when creating it to stop
        // the ads from immediately serving. Set to ENABLED once you've added
        // targeting and the ads are ready to serve.
//        $campaign->setStatus(CampaignStatus::PAUSED);
//        $campaign->setStartDate(date('Ymd', strtotime('+1 day')));
//        $campaign->setEndDate(date('Ymd', strtotime('+1 month')));
//        $campaign->setAdServingOptimizationStatus(
//            AdServingOptimizationStatus::ROTATE);

        // Set frequency cap (optional).
        if($cap)
        {
            $frequencyCap = new FrequencyCap();
            $frequencyCap->setImpressions(intval($cap));
            $frequencyCap->setTimeUnit(TimeUnit::DAY);
            $frequencyCap->setLevel(Level::ADGROUP);
            $campaign->setFrequencyCap($frequencyCap);
        }


        // Set advanced location targeting settings (optional).
//        $geoTargetTypeSetting = new GeoTargetTypeSetting();
//        $geoTargetTypeSetting->setPositiveGeoTargetType(
//            GeoTargetTypeSettingPositiveGeoTargetType::DONT_CARE);
//        $geoTargetTypeSetting->setNegativeGeoTargetType(
//            GeoTargetTypeSettingNegativeGeoTargetType::DONT_CARE);
//        $campaign->setSettings([$geoTargetTypeSetting]);

        // Create a campaign operation and add it to the operations list.
        $operation = new CampaignOperation();
        $operation->setOperand($campaign);
        $operation->setOperator(Operator::ADD);
        $operations[] = $operation;


        // Create the campaigns on the server and print out some information for
        // each created campaign.
        $result = $campaignService->mutate($operations);
        foreach ($result->getValue() as $campaign) {
            $result = array('id'=>$campaign->getId(), 'name'=>$campaign->getName());
        }

        return $result;
    }

}

