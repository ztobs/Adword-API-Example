<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 10/2/2017
 * Time: 7:33 AM
 */

namespace Ztobs\Classes;


use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201802\cm\AdGroupCriterionOperation;
use Google\AdsApi\AdWords\v201802\cm\AdGroupCriterionService;
use Google\AdsApi\AdWords\v201802\cm\BiddableAdGroupCriterion;
use Google\AdsApi\AdWords\v201802\cm\Criterion;
use Google\AdsApi\AdWords\v201802\cm\Operator;

/**
 * This class updates the final URL of a keyword. To get keywords, run
 * GetKeywords.php.
 */
class UpdateKeyword {

    public static function run(AdWordsServices $adWordsServices,
                                      AdWordsSession $session, $adGroupId, $criterionId, $finalUrl) {
        $adGroupCriterionService =
            $adWordsServices->get($session, AdGroupCriterionService::class);

        $operations = [];

        // Create ad group criterion.
        $adGroupCriterion = new BiddableAdGroupCriterion();
        $adGroupCriterion->setAdGroupId($adGroupId);
        // Create criterion using an existing ID. Use the base class Criterion
        // instead of Keyword to avoid having to set keyword-specific fields.
        $adGroupCriterion->setCriterion(new Criterion($criterionId));

        // Update final URL.
        $adGroupCriterion->setFinalUrls([$finalUrl]);

        // Create ad group criterion operation and add it to the list.
        $operation = new AdGroupCriterionOperation();
        $operation->setOperand($adGroupCriterion);
        $operation->setOperator(Operator::SET);
        $operations[] = $operation;

        // Update the keyword on the server.
        $result = $adGroupCriterionService->mutate($operations);

        $adGroupCriterion = $result->getValue()[0];
            // $adGroupCriterion->getCriterion()->getId(),
            // $adGroupCriterion->getFinalUrls()->getUrls()[0]

    }

}
