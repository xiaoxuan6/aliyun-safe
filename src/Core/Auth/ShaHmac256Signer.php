<?php
/**
 * This file is part of aliyun safe package.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
class ShaHmac256Signer implements ISigner
{
    public function signString($source, $accessSecret)
    {
        return	base64_encode(hash_hmac('sha256', $source, $accessSecret, true));
    }

    public function getSignatureMethod()
    {
        return 'HMAC-SHA256';
    }

    public function getSignatureVersion()
    {
        return '1.0';
    }
}
