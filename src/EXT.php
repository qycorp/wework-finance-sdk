<?php
namespace Qycorp\WeworkFinance;

use Exception;

class EXT {
     /**
     * @var array
     */
    protected $config = [];

    /**
     * @var \WxworkFinanceSdk
     */
    private $sdk;
    
    /**
     * 获取 php-ext-sdk.
     * @param array $config ...
     */
    public function __construct(array $config = [])
    {
        if (! extension_loaded('wxwork_finance_sdk')) {
            throw new Exception('缺少ext-wxwork_finance_sdk扩展');
        }

        $this->config = array_merge($this->config, $config);
        if (! isset($this->config['corpid'])) {
            throw new Exception('缺少配置:corpid');
        }
        if (! isset($this->config['secret'])) {
            throw new Exception('缺少配置:secret');
        }
        $options = ['timeout' => 30];
        if (isset($this->config['proxy'])) {
            $options['proxy_host'] = $this->config['proxy'];
        }
        if (isset($this->config['passwd'])) {
            $options['proxy_password'] = $this->config['passwd'];
        }
        if (isset($this->config['timeout'])) {
            $options['timeout'] = $this->config['timeout'];
        }

        $this->sdk = new \WxworkFinanceSdk(
            $this->config['corpid'],
            $this->config['secret'],
            $options
        );
    }
    /**
     * {@inheritdoc}
     */
    public function getChatData(int $seq, int $limit): string
    {
        return $this->sdk->getChatData($seq, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function decryptData(string $randomKey, string $encryptStr): string
    {
        return $this->sdk->decryptData($randomKey, $encryptStr);
    }

    /**
     * {@inheritdoc}
     * @throws FinanceSDKException
     */
    public function getMediaData(string $sdkFileId, string $ext, $options = []): \SplFileInfo
    {
        $filename = md5($sdkFileId);

        if (isset($options['md5sum'])) {
            $filename = $options['md5sum'];
        }
        $path = empty($this->config['savepath']) ? sys_get_temp_dir() . DIRECTORY_SEPARATOR : $this->config['savepath'];
        $ext && $filename.='.' . $ext;
        $path.= $filename;
        try {
            $this->sdk->downloadMedia($sdkFileId, $path);
        } catch (Excption $e) {
            throw new Excption('获取文件失败' . $e->getMessage(), $e->getCode());
        }
        return new \SplFileInfo($path);
    }
}