<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 12:08 PM
 */
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201708\cm\CampaignService;
use Google\AdsApi\AdWords\v201708\cm\OrderBy;
use Google\AdsApi\AdWords\v201708\cm\Paging;
use Google\AdsApi\AdWords\v201708\cm\Selector;
use Google\AdsApi\AdWords\v201708\cm\SortOrder;

/**
 * This example gets all campaigns. To add a campaign, run AddCampaign.php.
 */
class GetCampaigns {


    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $page_limit=500) {
        $campaignService = $adWordsServices->get($session, CampaignService::class);

        // Create selector.
        $selector = new Selector();
        $selector->setFields(['Id', 'Name']);
        //$selector->setOrdering([new OrderBy('Name', SortOrder::ASCENDING)]);
        $selector->setPaging(new Paging(0, intval($page_limit)));



        $totalNumEntries = 0;
        do {
            // Make the get request.
            $page = $campaignService->get($selector);


            // Display results.
            if ($page->getEntries() !== null) {
                $totalNumEntries = $page->getTotalNumEntries();
                foreach ($page->getEntries() as $campaign) {

                    $results[] = array($campaign->getId()=>$campaign->getName());
                }
            }

            // Advance the paging index.
            $selector->getPaging()->setStartIndex(
                $selector->getPaging()->getStartIndex() + intval($page_limit));
        } while ($selector->getPaging()->getStartIndex() < $totalNumEntries);

        return $results;
    }


}

