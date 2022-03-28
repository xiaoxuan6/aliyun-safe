<?php
/**
 * This file is part of PHP CS Fixer.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
class DefaultProfile implements IClientProfile
{
    private static $profile;
    private static $endpoints;
    private static $credential;
    private static $regionId;
    private static $acceptFormat;

    private static $isigner;
    private static $iCredential;

    private function __construct($regionId, $credential)
    {
        self::$regionId = $regionId;
        self::$credential = $credential;
    }

    public static function getProfile($regionId, $accessKeyId, $accessSecret)
    {
        $credential = new Credential($accessKeyId, $accessSecret);
        self::$profile = new DefaultProfile($regionId, $credential);

        return self::$profile;
    }

    public function getSigner()
    {
        if (self::$isigner == null) {
            self::$isigner = new ShaHmac1Signer();
        }

        return self::$isigner;
    }

    public function getRegionId()
    {
        return self::$regionId;
    }

    public function getFormat()
    {
        return self::$acceptFormat;
    }

    public function getCredential()
    {
        if (self::$credential == null && self::$iCredential != null) {
            self::$credential = self::$iCredential;
        }

        return self::$credential;
    }

    public static function getEndpoints()
    {
        if (self::$endpoints == null) {
            self::$endpoints = EndpointProvider::getEndpoints();
        }

        return self::$endpoints;
    }

    public static function addEndpoint($endpointName, $regionId, $product, $domain)
    {
        if (self::$endpoints == null) {
            self::$endpoints = self::getEndpoints();
        }
        $endpoint = self::findEndpointByName($endpointName);
        if ($endpoint == null) {
            self::addEndpoint_($endpointName, $regionId, $product, $domain);
        } else {
            self::updateEndpoint($regionId, $product, $domain, $endpoint);
        }
    }

    public static function findEndpointByName($endpointName)
    {
        foreach (self::$endpoints as $key => $endpoint) {
            if ($endpoint->getName() == $endpointName) {
                return $endpoint;
            }
        }
    }

    private static function addEndpoint_($endpointName, $regionId, $product, $domain)
    {
        $regionIds = [$regionId];
        $productsDomains = [new ProductDomain($product, $domain)];
        $endpoint = new Endpoint($endpointName, $regionIds, $productDomains);
        array_push(self::$endpoints, $endpoint);
    }

    private static function updateEndpoint($regionId, $product, $domain, $endpoint)
    {
        $regionIds = $endpoint->getRegionIds();
        if (! in_array($regionId, $regionIds)) {
            array_push($regionIds, $regionId);
            $endpoint->setRegionIds($regionIds);
        }

        $productDomains = $endpoint->getProductDomains();
        if (self::findProductDomain($productDomains, $product, $domain) == null) {
            array_push($productDomains, new ProductDomain($product, $domain));
        }
        $endpoint->setProductDomains($productDomains);
    }

    private static function findProductDomain($productDomains, $product, $domain)
    {
        foreach ($productDomains as $key => $productDomain) {
            if ($productDomain->getProductName() == $product && $productDomain->getDomainName() == $domain) {
                return $productDomain;
            }
        }

        return null;
    }
}
