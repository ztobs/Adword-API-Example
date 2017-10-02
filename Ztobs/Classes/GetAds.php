<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/16/2017
 * Time: 10:33 AM
 */

namespace Ztobs\Classes;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201708\cm\AdGroupAdService;
use Google\AdsApi\AdWords\v201708\cm\AdGroupAdStatus;
use Google\AdsApi\AdWords\v201708\cm\AdType;
use Google\AdsApi\AdWords\v201708\cm\OrderBy;
use Google\AdsApi\AdWords\v201708\cm\Paging;
use Google\AdsApi\AdWords\v201708\cm\Predicate;
use Google\AdsApi\AdWords\v201708\cm\PredicateOperator;
use Google\AdsApi\AdWords\v201708\cm\Selector;
use Google\AdsApi\AdWords\v201708\cm\SortOrder;

/**
 * This example gets expanded text ads in an ad group. To add expanded text ads,
 * run AddExpandedTextAds.php. To get ad groups, run GetAdGroups.php.
 */
class GetAds {

    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $adGroupId, $page_limit=500) {
        $adGroupAdService =
            $adWordsServices->get($session, AdGroupAdService::class);

        // Create a selector to select all ads for the specified ad group.
        $selector = new Selector();
        $selector->setFields(
            ['Id', 'Status', 'HeadlinePart1', 'HeadlinePart2', 'Description']);
        $selector->setOrdering([new OrderBy('Id', SortOrder::ASCENDING)]);
        $selector->setPredicates([
            new Predicate('AdGroupId', PredicateOperator::IN, [$adGroupId]),
            new Predicate('AdType', PredicateOperator::IN,
                [AdType::EXPANDED_TEXT_AD]),
            new Predicate('Status', PredicateOperator::IN,
                [AdGroupAdStatus::ENABLED, AdGroupAdStatus::PAUSED])
        ]);
        $selector->setPaging(new Paging(0, $page_limit));

        $results = null;
        $totalNumEntries = 0;
        do {
            // Retrieve ad group ads one page at a time, continuing to request pages
            // until all ad group ads have been retrieved.
            $page = $adGroupAdService->get($selector);

            // Print out some information for each ad group ad.
            if ($page->getEntries() !== null) {
                $totalNumEntries = $page->getTotalNumEntries();
                foreach ($page->getEntries() as $adGroupAd) {

                    $results[] = array(
                            'id'            =>  $adGroupAd->getAd()->getId(),
                            'status'        =>  $adGroupAd->getStatus(),
                            'headlinePart1' =>  $adGroupAd->getAd()->getHeadlinePart1(),
                            'headlinePart2' =>  $adGroupAd->getAd()->getHeadlinePart2(),
                            'description'   =>  $adGroupAd->getAd()->getDescription(),
                            'adGroupId'     =>  $adGroupId
                    );
                }
            }

            $selector->getPaging()->setStartIndex(
                $selector->getPaging()->getStartIndex() + $page_limit);
        } while ($selector->getPaging()->getStartIndex() < $totalNumEntries);

        return $results;
    }

}


