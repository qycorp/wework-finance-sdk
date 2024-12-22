<?php
namespace Qycorp\WeworkFinance;

use Exception;

class FFI {
    /**
     * @var FFI
     */
    protected $ffi;

    protected $config = [
        'sdkpath'   => '',
        'timeout'   => 30,
    ];

    /**
     * @var string 指针
     */
    protected $sdk;

    /**
     * @var string C语言头
     */
    protected $cHeader = 'WeWorkFinanceSdk_C.h';

    /**
     * @var string C语言库
     */
    protected $cLib = 'libWeWorkFinanceSdk_C.so';

    public function __construct($config = []) {
        
        if (!extension_loaded('ffi')) {
            throw new \Exception('缺少ext-ffi扩展');
        }
        
        $this->config = array_merge($this->config, $config);

        if ( !isset($this->config['corpid'])) {
            throw new \Exception('缺少配置:corpid');
        }
        if ( !isset($this->config['secret'])) {
            throw new \Exception('缺少配置:secret');
        }

        if ( !isset($this->config['proxy'])) {
            $this->config['proxy'] = '';
        }

        if ( !isset($this->config['passwd'])) {
            $this->config['passwd'] = '';
        }

        if (empty($config['sdkpath'])) {
            $this->config['sdkpath'] = $config['sdkpath'] = dirname(__FILE__) .'/../lib/';
        }

        $sdkpath = $this->config['sdkpath'];

        $this->cHeader  = $sdkpath.$this->cHeader;
        $this->cLib     = $sdkpath.$this->cLib;

        if (!file_exists($this->cHeader) || !file_exists($this->cLib)) {
            throw new \Exception('未找到企业微信C语言扩展');
        }
        // 引入ffi
        $this->ffi = \FFI::cdef(file_get_contents($this->cHeader), $this->cLib);
        $this->sdk = $this->ffi->NewSdk();
        // 初始化
        $init = $this->ffi->Init($this->sdk, $this->config['corpid'], $this->config['secret']);

        if ($init !== 0) {
            throw new \Exception('ffi:Init() 初始化错误');
        }
    }

    public function __destruct()
    {
        // 释放sdk
        $this->sdk instanceof FFI && $this->ffi->DestroySdk($this->sdk);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception ...
     */
    public function getChatData(int $seq, int $limit, int $timeout = 0): string
    {
        if (!empty($timeout)) {
            $this->config['timeout'] = $timeout;
        }
        // 初始化buffer
        $chatDatas = $this->ffi->NewSlice();
        // 拉取内容
        $sdk = $this->ffi->GetChatData($this->sdk, $seq, $limit, $this->config['proxy'], $this->config['passwd'], $this->config['timeout'], $chatDatas);
        if ($sdk !== 0) {
            throw new \Exception(sprintf('GetChatData err res:%d', $sdk));
        }
        $resStr = \FFI::string($chatDatas->buf);
        // 释放buffer
        $this->ffi->FreeSlice($chatDatas);
        $chatDatas->len = 0;

        return $resStr;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception ...
     */
    public function decryptData(string $randomKey, string $encryptStr): string
    {
        // 初始化buffer
        $msg = $this->ffi->NewSlice();
        $res = $this->ffi->DecryptData($randomKey, $encryptStr, $msg);
        if ($res !== 0) {
            throw new \Exception(sprintf('RsaDecryptChatData err res:%d', $res));
        }
        $resStr = \FFI::string($msg->buf);
        // 释放buffer
        $this->ffi->FreeSlice($msg);
        $msg->len = 0;

        return $resStr;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
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
            $this->downloadMediaData($sdkFileId, $path);
        } catch (\Exception $e) {
            throw new \Exception('获取文件失败' . $e->getMessage(), $e->getCode());
        }

        return new \SplFileInfo($path);
    }

    /**
     * 下载媒体资源.
     *
     * @param string $sdkFileId file id
     * @param string $path 文件路径
     *
     * @throws Exception
     */
    protected function downloadMediaData(string $sdkFileId, string $path): void
    {
        $indexBuf = '';

        while (true) {
            // 初始化buffer MediaData_t*
            $media = $this->ffi->NewMediaData();
            // 拉取内容
            $res = $this->ffi->GetMediaData($this->sdk, $indexBuf, $sdkFileId, $this->config['proxy'], $this->config['passwd'], $this->config['timeout'], $media);
            if ($res !== 0) {
                $this->ffi->FreeMediaData($media);
                throw new \Exception(sprintf('GetMediaData err res:%d\n', $res));
            }
            // buffer写入文件
            $handle = fopen($path, 'ab+');
            if ( !$handle ) {
                throw new \Exception(sprintf('打开文件失败:%s', $path));
            }
            fwrite($handle, \FFI::string($media->data, $media->data_len), $media->data_len);
            fclose($handle);
            // 完成下载
            if ($media->is_finish === 1) {
                $this->ffi->FreeMediaData($media);
                break;
            }
            // 重置文件指针
            $indexBuf = \FFI::string($media->outindexbuf);
            $this->ffi->FreeMediaData($media);
        }
    }
}