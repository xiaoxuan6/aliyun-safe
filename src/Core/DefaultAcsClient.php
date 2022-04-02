<?php
/**
 * This file is part of aliyun safe package.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
class DefaultAcsClient implements IAcsClient
{
    public $iClientProfile;
    public $__urlTestFlag__;

    public function __construct($iClientProfile)
    {
        $this->iClientProfile = $iClientProfile;
        $this->__urlTestFlag__ = false;
    }

    public function getAcsResponse($request, $iSigner = null, $credential = null, $autoRetry = true, $maxRetryNumber = 3)
    {
        $httpResponse = $this->doActionImpl($request, $iSigner, $credential, $autoRetry, $maxRetryNumber);
        $respObject = $this->parseAcsResponse($httpResponse->getBody(), $request->getAcceptFormat());
        if ($httpResponse->isSuccess() == false) {
            $this->buildApiException($respObject, $httpResponse->getStatus());
        }

        return $respObject;
    }

    private function doActionImpl($request, $iSigner = null, $credential = null, $autoRetry = true, $maxRetryNumber = 3)
    {
        if ($this->iClientProfile == null && ($iSigner == null || $credential == null
            || $request->getRegionId() == null || $request->getAcceptFormat() == null)) {
            throw new ClientException('No active profile found.', 'SDK.InvalidProfile');
        }
        if ($iSigner == null) {
            $iSigner = $this->iClientProfile->getSigner();
        }
        if ($credential == null) {
            $credential = $this->iClientProfile->getCredential();
        }
        $request = $this->prepareRequest($request);
        $domain = EndpointProvider::findProductDomain($request->getRegionId(), $request->getProduct());
        if ($domain == null) {
            throw new ClientException('Can not find endpoint to access.', 'SDK.InvalidRegionId');
        }
        $requestUrl = $request->composeUrl($iSigner, $credential, $domain);

        if ($this->__urlTestFlag__) {
            throw new ClientException($requestUrl, 'URLTestFlagIsSet');
        }

        if (count($request->getDomainParameter()) > 0) {
            $httpResponse = HttpHelper::curl($requestUrl, $request->getMethod(), $request->getDomainParameter(), $request->getHeaders());
        } else {
            $httpResponse = HttpHelper::curl($requestUrl, $request->getMethod(), $request->getContent(), $request->getHeaders());
        }

        while (500 <= $httpResponse->getStatus() && $autoRetry && $retryTimes < $maxRetryNumber) {
            $retryTimes = 1;
            $requestUrl = $request->composeUrl($iSigner, $credential, $domain);

            if (count($request->getDomainParameter()) > 0) {
                $httpResponse = HttpHelper::curl($requestUrl, $request->getDomainParameter(), $request->getHeaders());
            } else {
                $httpResponse = HttpHelper::curl($requestUrl, $request->getMethod(), $request->getContent(), $request->getHeaders());
            }
            $retryTimes++;
        }

        return $httpResponse;
    }

    public function doAction($request, $iSigner = null, $credential = null, $autoRetry = true, $maxRetryNumber = 3)
    {
        trigger_error('doAction() is deprecated. Please use getAcsResponse() instead.', E_USER_NOTICE);

        return $this->doActionImpl($request, $iSigner, $credential, $autoRetry, $maxRetryNumber);
    }

    private function prepareRequest($request)
    {
        if ($request->getRegionId() == null) {
            $request->setRegionId($this->iClientProfile->getRegionId());
        }
        if ($request->getAcceptFormat() == null) {
            $request->setAcceptFormat($this->iClientProfile->getFormat());
        }
        if ($request->getMethod() == null) {
            $request->setMethod('GET');
        }

        return $request;
    }

    private function buildApiException($respObject, $httpStatus)
    {
        throw new ServerException($respObject->Message, $respObject->Code, $httpStatus, $respObject->RequestId);
    }

    private function parseAcsResponse($body, $format)
    {
        if ($format == 'JSON') {
            $respObject = json_decode($body);
        } elseif ($format == 'XML') {
            $respObject = @simplexml_load_string($body);
        } elseif ($format == 'RAW') {
            $respObject = $body;
        }

        return $respObject;
    }
}
