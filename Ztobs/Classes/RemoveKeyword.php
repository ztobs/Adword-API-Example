<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 10/2/2017
 * Time: 6:49 AM
 */

namespace Ztobs\Classes;


use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201708\cm\AdGroupCriterion;
use Google\AdsApi\AdWords\v201708\cm\AdGroupCriterionOperation;
use Google\AdsApi\AdWords\v201708\cm\AdGroupCriterionService;
use Google\AdsApi\AdWords\v201708\cm\Criterion;
use Google\AdsApi\AdWords\v201708\cm\Operator;

/**
 * This class removes a keyword.
 */
class RemoveKeyword {


    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $adGroupId, $criterionId) {
        $adGroupCriterionService =
            $adWordsServices->get($session, AdGroupCriterionService::class);

        // Create criterion using an existing ID. Use the base class Criterion
        // instead of Keyword to avoid having to set keyword-specific fields.
        $criterion = new Criterion();
        $criterion->setId($criterionId);

        // Create an ad group criterion.
        $adGroupCriterion = new AdGroupCriterion();
        $adGroupCriterion->setAdGroupId($adGroupId);
        $adGroupCriterion->setCriterion($criterion);

        // Create an ad group criterion operation and add it the operations list.
        $operation = new AdGroupCriterionOperation();
        $operation->setOperand($adGroupCriterion);
        $operation->setOperator(Operator::REMOVE);
        $operations = [$operation];

        // Remove criterion on the server.
        $result = $adGroupCriterionService->mutate($operations);

        // Print out some information for the removed keyword.
        $adGroupCriterion = $result->getValue()[0];
            //$adGroupCriterion->getCriterion()->getId();
    }

}

