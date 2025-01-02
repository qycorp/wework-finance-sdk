<?php
namespace X2nx\WeworkFinance;

use Exception;

class SDK {
    /**
     * @var $config = [];
     */
    protected $config = [];

    /**
     * @var string 指针
     */
    protected $sdk;

    protected $providers = [
        'api' => \X2nx\WeworkFinance\API::class,
        'ffi' => \X2nx\WeworkFinance\FFI::class,
        'ext' => \X2nx\WeworkFinance\EXT::class,
    ];

    public function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
        if (!isset($this->config['corpid'])) {
            throw new \Exception('缺少配置:corpid');
        }
        if (!isset($this->config['secret'])) {
            throw new \Exception('缺少配置:secret');
        }
        if (!isset($this->config['private_keys'])) {
            throw new \Exception('缺少配置:private_keys');
        }

        $this->config['provider'] = empty($config['provider']) ? 'api' : $config['provider'];

        if (isset($this->providers[$this->config['provider']])) {
            $this->sdk = new $this->providers[$this->config['provider']]($this->config);
        }
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->sdk, $name)) {
            return call_user_func_array([$this->sdk, $name], $arguments);
        }
        throw new \Exception('Method not defined. method:' . $name);
    }

    /**
     * 获取会话解密记录数据.
     *
     * @param int $seq 起始位置
     * @param int $limit 限制条数
     * @param int $retry 重试次数
     *
     * @return array ...
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function getDecryptChatData(int $seq, int $limit, int $retry = 0): array
    {
        $privateKeys = $this->config['private_keys'];

        if (isset($this->config['provider']) && $this->config['provider'] === 'api') {
            return $this->sdk->getDecryptChatData($seq, $limit, $retry);
        }

        try {
            $chatData = json_decode($this->sdk->getChatData($seq, $limit), true)['chatdata'];
            $newChatData = [];
            $lastSeq = 0;
            foreach ($chatData as $i => $item) {
                $lastSeq = $item['seq'];
                if ( ! isset($privateKeys[$item['publickey_ver']])) {
                    continue;
                }
                $decryptRandKey = null;
                openssl_private_decrypt(
                    base64_decode($item['encrypt_random_key']),
                    $decryptRandKey,
                    $privateKeys[$item['publickey_ver']],
                    OPENSSL_PKCS1_PADDING
                );
                if ($decryptRandKey === null) {
                    continue;
                }
                $newChatData[$i] = json_decode($this->sdk->decryptData($decryptRandKey, $item['encrypt_chat_msg']), true);
                $newChatData[$i]['seq'] = $item['seq'];
            }
            if ( ! empty($chatData) && empty($newChatData) && $retry && $retry < 10) {
                return $this->getDecryptChatData($lastSeq, $limit, ++$retry);
            }

            return $newChatData;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}
