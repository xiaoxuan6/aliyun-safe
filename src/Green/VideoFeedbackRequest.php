<?php
/**
 * This file is part of aliyun safe package.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace James\AliGreen\Green;

use RoaAcsRequest;

class VideoFeedbackRequest extends RoaAcsRequest
{
    public function __construct()
    {
        parent::__construct('Green', '2017-01-12', 'VideoFeedback');
        $this->setUriPattern('/green/video/feedback');
        $this->setMethod('POST');
    }

    private $clientInfo;

    public function getClientInfo()
    {
        return $this->clientInfo;
    }

    public function setClientInfo($clientInfo)
    {
        $this->clientInfo = $clientInfo;
        $this->queryParameters['ClientInfo'] = $clientInfo;
    }
}
