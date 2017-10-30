<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/17/2017
 * Time: 1:54 PM
 */


/*
 * Available variables:
 * {{productName}}
 * {{productShortName}}
 * {{productPrice}}
 * {{productDiscountInPercent}}
 */


$variation = array(
    0   =>  array(
        "headline1" => "Buy {{productShortName}} now",
        "headline2" => "only for {{productPrice}} €"
    ),
    1   => array(
        "headline1" => "Cheap {{productShortName}}",
        "headline2" => "save -{{productDiscountInPercent}}"
    )
);

