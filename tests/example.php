<?php
namespace X2nx\WeworkFinance;

require('vendor/autoload.php');

use Exception;

try {
    $sdk = new SDK([
        'provider'  => 'api', // ffi ext api 三种方式可选 默认api方式
        'corpid'    => 'xxxxxxxxxxxxxxxxxxxx',
        'secret'    => 'xxxxxxxxxxxxxxxxxxxx',
        'private_keys' => [
            '版本号' => '解密私钥'
        ]
    ]);

    $data = $sdk->getDecryptChatData(0, 50, 3);

    if (!empty($data)) {
        foreach ($data as $val) {
            if (isset($val['msgtype']) && $val['msgtype'] === 'image' && !empty($val['image']['sdkfileid'])) {
                $file = $sdk->getMediaData($val['image']['sdkfileid'], 'png', $val['image']);
                // print_r($file);die;
            }
        }
    }
    print_r($data);die;
} catch (Exception $e) {
    print_r([
        $e->getMessage(),
        $e->getCode(),
    ]);
}