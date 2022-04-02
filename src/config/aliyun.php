<?php
/**
 * This file is part of aliyun safe package.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
return [
    // 阿里云 accessKeyId
    'accessKeyId' => '******',
    // 阿里云 accessKeySecret
    'accessKeySecret' => '******',
    // 支持的场景有：porn（色情）、terrorism（暴恐）、qrcode（二维码）、ad（图片广告）、 ocr（文字识别）
    'scenes' => ['ad', 'porn', 'terrorism', 'qrcode'],
    // 地区 上海
    'region' => 'cn-shanghai',

    // 自定义 text 违规内容
    'content' => [
        'cnm',
        'sb'
    ]
];
