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
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201708\cm\AdGroupCriterionService;
use Google\AdsApi\AdWords\v201708\cm\CriterionType;
use Google\AdsApi\AdWords\v201708\cm\OrderBy;
use Google\AdsApi\AdWords\v201708\cm\Paging;
use Google\AdsApi\AdWords\v201708\cm\Predicate;
use Google\AdsApi\AdWords\v201708\cm\PredicateOperator;
use Google\AdsApi\AdWords\v201708\cm\Selector;
use Google\AdsApi\AdWords\v201708\cm\SortOrder;
use Google\AdsApi\Common\OAuth2TokenBuilder;
/**
 * This example gets all keywords in an ad group. To get ad groups, run
 * GetAdGroups.php.
 */
class GetKeywords
{
    public static function run(
        AdWordsServices $adWordsServices,
        AdWordsSession $session,
        $adGroupId,
        $page_limit=1000
    ) {
        $adGroupCriterionService = $adWordsServices->get($session, AdGroupCriterionService::class);
        // Create a selector to select all keywords for the specified ad group.
        $selector = new Selector();
        $selector->setFields(
            ['Id', 'CriteriaType', 'KeywordMatchType', 'KeywordText', 'Status']
        );
        $selector->setOrdering([new OrderBy('KeywordText', SortOrder::ASCENDING)]);
        $selector->setPredicates(
            [
                new Predicate('AdGroupId', PredicateOperator::IN, [$adGroupId]),
                new Predicate(
                    'CriteriaType',
                    PredicateOperator::IN,
                    [CriterionType::KEYWORD]
                )
            ]
        );
        $selector->setPaging(new Paging(0, $page_limit));
        $totalNumEntries = 0;
        $results = [];

        do {
            // Retrieve keywords one page at a time, continuing to request pages
            // until all keywords have been retrieved.
            $page = $adGroupCriterionService->get($selector);
            // Print out some information for each keyword.
            if ($page->getEntries() !== null) {
                $totalNumEntries = $page->getTotalNumEntries();
                foreach ($page->getEntries() as $adGroupCriterion) {

                    $results[] = array(
                            'id'            =>  $adGroupCriterion->getCriterion()->getId(),
                            'keyword'       =>  $adGroupCriterion->getCriterion()->getText(),
                            'type'          =>  $adGroupCriterion->getCriterion()->getMatchType()
                    );

                }
            }
            $selector->getPaging()->setStartIndex(
                $selector->getPaging()->getStartIndex() + $page_limit
            );
        } while ($selector->getPaging()->getStartIndex() < $totalNumEntries);

        return $results;
    }
}