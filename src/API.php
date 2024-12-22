<?php
namespace Qycorp\WeworkFinance;

use Exception;

class API {
    protected $api = 'http://127.0.0.1:7149';

    protected $config = [];

    public function __construct($config) {
        $this->config = [
            'corpid'        => $config['corpid'],
            'secret'        => $config['secret'],
            'private_key'   => current($config['private_keys']),
        ];
        $api = $this->api . '/health_check';

        $state = $this->request($api);

        if (empty($state['ret']) || $state['msg'] !== 'OK') {
            throw new \Exception('请检查API服务是否正常');
        }
    }

    public function getDecryptChatData(int $seq, int $limit, int $retry = 0): array
    {
        $api = $this->api . '/get_chat_data';

        $params = json_encode(array_merge($this->config, [
            'seq'   => $seq,
            'limit' => $limit,
        ]));

        $result = $this->request($api, $params, 'POST', [
            CURLOPT_HTTPHEADER => [
                'content-type: application/json'
            ]
        ]);

        $data = $chatData = [];

        if ($result['ret']) {
            $data = json_decode($result['msg'], true);
            if (empty($data['err_code']) && !empty($data['data'])) {
                foreach ($data['data'] as $key => $chat) {
                    $chatData[$key] = $chat['message'];
                }
            }
        }

        return $chatData;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getMediaData(string $sdkFileId, string $ext, $options = []): \SplFileInfo
    {
        $api = $this->api . '/get_media_data';

        $params = json_encode(array_merge($this->config, [
            'sdk_file_id'   => $sdkFileId,
        ]));

        $request = $this->request($api, $params, 'POST', [
            CURLOPT_HTTPHEADER => [
                'content-type: application/json'
            ]
        ]);


        if (!$request['ret']) {
            throw new \Exception($request['msg']);
        }

        $result = json_decode($request['msg'], true);

        if (!empty($result['err_code'])) {
            throw new \Exception($result['data']);
        }

        $stream = base64_decode($result['data']);

        $filename = md5($sdkFileId);

        if (isset($options['md5sum'])) {
            $filename = $options['md5sum'];
        }
        $path = empty($this->config['savepath']) ? sys_get_temp_dir() . DIRECTORY_SEPARATOR : $this->config['savepath'];
        $ext && $filename.='.' . $ext;
        $path.= $filename;
        try {
            // buffer写入文件
            $handle = fopen($path, 'ab+');
            if (! $handle) {
                throw new \RuntimeException(sprintf('打开文件失败:%s', $path));
            }
            fwrite($handle, $stream, strlen($stream));
            fclose($handle);
        } catch (\Exception $e) {
            throw new \Exception('获取文件失败' . $e->getMessage(), $e->getCode());
        }

        return new \SplFileInfo($path);
    }

    /**
     * CURL发送Request请求,含POST和REQUEST
     * @param string $url     请求的链接
     * @param mixed  $params  传递的参数
     * @param string $method  请求的方法
     * @param mixed  $options CURL的参数
     * @return array
     */
    public static function request($url, $params = [], $method = 'POST', $options = [])
    {
        $method = strtoupper($method);
        $protocol = substr($url, 0, 5);
        $query_string = is_array($params) ? http_build_query($params) : $params;

        $ch = curl_init();
        $defaults = [];
        if ('GET' == $method) {
            $geturl = $query_string ? $url . (stripos($url, "?") !== false ? "&" : "?") . $query_string : $url;
            $defaults[CURLOPT_URL] = $geturl;
        } else {
            $defaults[CURLOPT_URL] = $url;
            if ($method == 'POST') {
                $defaults[CURLOPT_POST] = 1;
            } else {
                $defaults[CURLOPT_CUSTOMREQUEST] = $method;
            }
            $defaults[CURLOPT_POSTFIELDS] = $params;
        }

        $defaults[CURLOPT_HEADER] = false;
        $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
        $defaults[CURLOPT_FOLLOWLOCATION] = true;
        $defaults[CURLOPT_RETURNTRANSFER] = true;
        $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
        $defaults[CURLOPT_TIMEOUT] = 3;

        // disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if ('https' == $protocol) {
            $defaults[CURLOPT_SSL_VERIFYPEER] = false;
            $defaults[CURLOPT_SSL_VERIFYHOST] = false;
        }

        curl_setopt_array($ch, (array)$options + $defaults);

        $result = curl_exec($ch);
        $err = curl_error($ch);

        if (false === $result || !empty($err)) {
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            return [
                'ret'   => false,
                'errno' => $errno,
                'msg'   => $err,
                'info'  => $info,
            ];
        }
        curl_close($ch);
        return [
            'ret' => true,
            'msg' => $result,
        ];
    }
}