<?php
/**
 * Created by PhpStorm.
 * User: donztobs
 * Date: 10/21/17
 * Time: 11:41 AM
 */


$arr = ["hjdhf", "kjdjfkdf", "jsjfsjdf"];

array_map("utf8_encode", $arr);

print_r($arr);