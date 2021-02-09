<?php

namespace resumePush;

use app\common\util\requests;
use GuzzleHttp\Client;

class BossZhipin
{
    public           $cookie   = '';
    protected        $error    = '';
    protected static $instance = null;
    public           $limit    = 3;

    public function __construct($cookie)
    {
        $this->cookie = $cookie;
    }

    public static function instance($option)
    {
        if (!self::$instance) {
            self::$instance = new static($option);
        }
        return self::$instance;
    }

    public function run($resumepath, $plan = 1)
    {
        $list = $this->getResumeList();
        if ($list) {
            if ($plan == 1 && count($list) >= $this->limit) {
                // 删1传1
                $item = array_shift($list);
                $this->deleteResume($item['resumeId']);
            } else if (($plan == 2 && count($list) >= $this->limit) || $plan == 3) {
                // 清空简历
                foreach ($list as $item) {
                    $this->deleteResume($item['resumeId']);
                    usleep(50000);
                }
            }
        }
        $preview = $this->uploadResume($resumepath);
        if (!$preview) {
            $this->error = '没有简历预览地址 保存失败';
            return false;
        }
        return $this->saveResume($preview);
    }

    public function uploadResume($path, $filename = null)
    {
        $client = new Client();
//        $zptoken = $this->gettoken();
        $url = 'https://www.zhipin.com/wapi/zpupload/resume/uploadFile.json';
//        $url= 'http://127.0.0.1/tpspider/';
        if (!$filename) $filename = basename($path);
        $response = $client->request('POST', $url, [
            'headers'   => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3861.400 QQBrowser/10.7.4313.400',
                'Cookie'     => $this->cookie,
                'Accept'     => 'application/json, text/plain, */*',
//                'zp_token'     => $zptoken,
                'origin'     => 'https://www.zhipin.com',
                'referer'    => 'https://www.zhipin.com/web/geek/recommend',
            ],
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($path, 'r'),
                    'filename' => $filename,
                ],
                [
                    'name'     => 'fileType',
                    'contents' => 1,
                ],
            ]]);

        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);
        if (!$data) throw new \Exception('json 解析失败');
        if ($data['code'] === 0 && @$data['zpData']['previewUrl']) {
            return $data['zpData']['previewUrl'];
        }
        $this->error = @$data['message'];
        return false;
    }

    public function saveResume($previewUrl)
    {
        $api      = "https://www.zhipin.com/wapi/zpgeek/resume/attachment/save.json?previewUrl={$previewUrl}&annexType=0";
        $client   = new Client();
        $response = $client->request('POST', $api, [
            'headers' => [
                'Cookie'     => $this->cookie,
                'origin'     => 'https://www.zhipin.com',
                'referer'    => 'https://www.zhipin.com/web/geek/recommend',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3861.400 QQBrowser/10.7.4313.400',
            ],
        ]);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);
        if (!$data) {
            throw  new \Exception('json解析失败');
        }

        if ($data['code'] === 0) {
            return true;
        }
        $this->error = @$data['message'];
        return false;
    }

    public function deleteResume($id)
    {
        $api      = "https://www.zhipin.com/wapi/zpgeek/resume/attachment/delete.json";
        $client   = new Client();
        $response = $client->request('POST', $api, [
            'headers'     => [
                'Cookie'     => $this->cookie,
                'origin'     => 'https://www.zhipin.com',
                'referer'    => 'https://www.zhipin.com/web/geek/recommend',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3861.400 QQBrowser/10.7.4313.400',
            ],
            'form_params' => [
                'resumeId' => $id,
            ]
        ]);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);
        if (!$data) {
            throw  new \Exception('json解析失败');
        }
        if ($data['code'] === 0) {
            return true;
        }
        $this->error = @$data['message'];
        return false;
    }

    public function getResumeList()
    {
        $api      = "https://www.zhipin.com/wapi/zpgeek/resume/sidebar.json";
        $client   = new Client();
        $response = $client->request('GET', $api, [
            'headers' => [
                'Cookie'     => $this->cookie,
                'origin'     => 'https://www.zhipin.com',
                'referer'    => 'https://www.zhipin.com/web/geek/recommend',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3861.400 QQBrowser/10.7.4313.400',
            ]
        ]);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);
        if (!$data) {
            throw  new \Exception('json解析失败');
        }

        if ($data['code'] === 0) {
            return $data['zpData']['attachmentList'];
        }
        $this->error = @$data['message'];
        return false;
    }

    public function getToken()
    {
        requests::set_cookies($this->cookie);
        $response = requests::get('https://www.zhipin.com/wapi/zppassport/get/zpToken');
        $data     = json_decode($response, true);
        return $data['zpData']['token'];
    }

    public function getError()
    {
        return $this->error;
    }
}