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
        $session->setValidateOnly(true);
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

        $violatingText = "";

        try {
            // Try creating an ad group ad on the server.
            $result = $adGroupAdService->mutate($operations);
        } catch (ApiException $apiException) {
            $operationIndicesToRemove = [];
            foreach ($apiException->getErrors() as $error) {
                // Get the index of the failed operation from the error's field path
                // elements.
                $fieldPathElements = $error->getFieldPathElements();
                $firstFieldPathElement = null;
                if ($fieldPathElements !== null && count($fieldPathElements) > 0) {
                    $firstFieldPathElement = $fieldPathElements[0];
                }
                if ($firstFieldPathElement === null
                    || $firstFieldPathElement->getField() !== 'operations'
                    || $firstFieldPathElement->getIndex() === null) {
                    // If the operation index is not present on the first error field
                    // path element, then there's no way to determine which operation to
                    // remove, so simply throw the exception.
                    throw $apiException;
                }
                $operationIndex = $firstFieldPathElement->getIndex();
                $operation = $operations[$operationIndex];
                if ($error instanceof PolicyViolationError) {
//                    printf("Ad with headline part 1 '%s' violated %s policy '%s'.\n",
//                        $operation->getOperand()->getAd()->getHeadlinePart1(),
//                        $error->getIsExemptable() ? 'exemptable' : 'non-exemptable',
//                        $error->getExternalPolicyName()
//                    );

                    if ($error->getIsExemptable() === true) {
                        // $error->getKey()->getPolicyName()
                        $violatingText = $error->getKey()->getViolatingText();
                        $operation->setExemptionRequests(
                            [new ExemptionRequest($error->getKey())]);
                    } else {
                        // Remove non-exemptable operation.
                        //print "Removing non-exemptable operation from the request.\n";
                        $operationIndicesToRemove[] = $operationIndex;
                    }
                } else {
                    // Non-policy error returned.
                    $operationIndicesToRemove[] = $operationIndex;
                }
            }
            $operationIndicesToRemove = array_unique($operationIndicesToRemove);
            rsort($operationIndicesToRemove, SORT_NUMERIC);
            foreach ($operationIndicesToRemove as $operationIndex) {
                unset($operations[$operationIndex]);
            }
        }

        if (count($operations) > 0) {
            // Make the mutate request to really add an ad group ad.
            $session->setValidateOnly(false);
            $result = $adGroupAdService->mutate($operations);

            // Print out some information about the created ad group ad.
            foreach ($result->getValue() as $adGroupAd) {
                $results[] = array('id'=>$adGroupAd->getAd()->getId(), 'name'=>$adGroupAd->getAd()->getHeadlinePart1());
            }

            return $results;
        } else {

        }

    }


}


