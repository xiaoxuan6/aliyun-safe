<?php
/**
 * This file is part of aliyun safe package.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
interface IClientProfile
{
    public function getSigner();

    public function getRegionId();

    public function getFormat();

    public function getCredential();
}
