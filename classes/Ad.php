<?php

/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 9/10/2017
 * Time: 3:03 PM
 */

class Ad
{
    public $headline1;
    public $headline2;
    public $description;
    public $finalUrls;
    public $path1;
    public $path2;
    public $productId;
    public $status;

    public function __construct($productId, $headline1, $headline2, $description, $finalUrls, $status, $path1=NULL, $path2=NULL)
    {
        $this->headline1 = $headline1;
        $this->headline2 = $headline2;
        $this->description = $description;
        $this->finalUrls = $finalUrls;
        $this->path1 = $path1;
        $this->path2 = $path2;
        $this->productId = $productId;
        $this->status = $status;
    }
}