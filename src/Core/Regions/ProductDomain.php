<?php
/**
 * This file is part of aliyun safe package.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
class ProductDomain
{
    private $productName;
    private $domainName;

    public function __construct($product, $domain)
    {
        $this->productName = $product;
        $this->domainName = $domain;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    public function getDomainName()
    {
        return $this->domainName;
    }

    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }
}
