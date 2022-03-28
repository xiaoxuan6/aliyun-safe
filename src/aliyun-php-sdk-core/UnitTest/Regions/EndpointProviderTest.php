<?php
/**
 * This file is part of PHP CS Fixer.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
include_once '../../Config.php';

class EndpointProviderTest extends PHPUnit_Framework_TestCase
{
    public function testFindProductDomain()
    {
        $this->assertEquals('ecs.aliyuncs.com', EndpointProvider::findProductDomain('cn-hangzhou', 'Ecs'));
    }
}
