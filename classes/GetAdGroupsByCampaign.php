<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/16/2017
 * Time: 7:42 AM
 */

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201708\cm\AdGroupService;
use Google\AdsApi\AdWords\v201708\cm\OrderBy;
use Google\AdsApi\AdWords\v201708\cm\Predicate;
use Google\AdsApi\AdWords\v201708\cm\PredicateOperator;
use Google\AdsApi\AdWords\v201708\cm\Paging;
use Google\AdsApi\AdWords\v201708\cm\Selector;
use Google\AdsApi\AdWords\v201708\cm\SortOrder;

/**
 * This example gets all ad groups in a campaign. To get campaigns, run
 * GetCampaigns.php.
 */
class GetAdGroupsByCampaign {


    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $campaignId, $page_limit=500) {
        $adGroupService = $adWordsServices->get($session, AdGroupService::class);

        // Create a selector to select all ad groups for the specified campaign.
        $selector = new Selector();
        $selector->setFields(['Id', 'Name']);
        $selector->setOrdering([new OrderBy('Name', SortOrder::ASCENDING)]);
        $selector->setPredicates(
            [new Predicate('CampaignId', PredicateOperator::IN, [$campaignId])]);
        $selector->setPaging(new Paging(0, $page_limit));

        $totalNumEntries = 0;
        $results = [];
        do {
            // Retrieve ad groups one page at a time, continuing to request pages
            // until all ad groups have been retrieved.
            $page = $adGroupService->get($selector);

            // Print out some information for each ad group.
            if ($page->getEntries() !== null) {
                $totalNumEntries = $page->getTotalNumEntries();
                foreach ($page->getEntries() as $adGroup) {

                        $results[] = array("id"=>$adGroup->getId(), "name"=>$adGroup->getName());
                }
            }

            $selector->getPaging()->setStartIndex(
                $selector->getPaging()->getStartIndex() + $page_limit);
        } while ($selector->getPaging()->getStartIndex() < $totalNumEntries);

        return $results;
    }


}

