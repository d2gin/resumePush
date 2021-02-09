<?php

namespace resumePush;

use GuzzleHttp\Client;
use QL\QueryList;

class Lagou
{
    public           $cookie   = '';
    protected        $error    = '';
    protected static $instance = null;
    public           $limit    = 4;

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

    /**
     * @param int $plan 1：删1传1 2：清空传1
     */
    public function run($resumepath, $plan = 1)
    {
        $info = $this->getInfo();
        if ($info['resume_list']) {
            if ($plan == 1 && count($info['resume_list']) >= $this->limit) {
                $item = array_shift($info['resume_list']);
                $this->deleteResume($item['id']);
                usleep(500000);
            } else if ($plan == 2 && count($info['resume_list']) >= $this->limit) {
                foreach ($info['resume_list'] as $item) {
                    $this->deleteResume($item['id']);
                }
            }
        }
        return $this->uploadResume($resumepath);
    }

    public function uploadResume($path, $filename = null)
    {
        $url    = "https://www.lagou.com/nearBy/updateMyResume";
        $client = new Client();

        $response = $client->request('POST', $url, [
            'headers'   => [
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3861.400 QQBrowser/10.7.4313.400',
                'cookie'     => $this->cookie,
                'accept'     => 'application/json, text/plain, */*',
                'origin'     => 'https://www.lagou.com',
                'referer'    => 'https://www.lagou.com/wn/resume/myresume',
//                'x-anit-forge-code'  => 'c1907dbf-4f7b-4aba-8706-fded22c91fbd',
//                'x-anit-forge-token' => '85a355a4-99d4-46e9-b925-f053f58641eb'
            ],
            'multipart' => [
                [
                    'name'     => 'newResume',
                    'contents' => fopen($path, 'r'),
                    'filename' => $filename,
                ],
                [
                    'name'     => 'userId',
                    'contents' => '',
                ],
                [
                    'name'     => 'forPreview',
                    'contents' => 1,
                ],
                [
                    'name'     => 'nearbyType',
                    'contents' => 1,
                ],
                [
                    'name'     => 'fromPage',
                    'contents' => 5,
                ],
            ]]);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);
        if (!$data) throw new \Exception('json 解析失败');
        if ($data['code'] === 0) {
            return true;
        }
        $this->error = @$data['msg'];
        return false;
    }

    public function deleteResume($id)
    {
        $url      = "https://www.lagou.com/nearBy/delNearBy.json?id={$id}";
        $client   = new Client();
        $response = $client->get($url, [
            'headers' => [
                'cookie'     => $this->cookie,
                'referer'    => 'https://www.lagou.com/wn/resume/myresume',
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3861.400 QQBrowser/10.7.4313.400',
            ]
        ]);
        $json     = $response->getBody()->getContents();
        $data     = json_decode($json, true);
        if (!$data) throw new \Exception('json 解析失败');
        if (@$data['code'] === 0) {
            return true;
        }
        return false;
    }

    public function getInfo()
    {
        $url    = "https://www.lagou.com/wn/resume/myresume";
        $client = new Client();

        $response = $client->request('GET', $url, [
            'headers' => [
                'cookie'     => $this->cookie,
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3861.400 QQBrowser/10.7.4313.400',
                'origin'     => 'https://www.lagou.com',
                'referer'    => 'https://www.lagou.com/wn/resume/myresume',
            ]
        ]);
        $body     = $response->getBody()->getContents();
        $ql       = QueryList::html($body);
        $json     = trim($ql->find('script#__NEXT_DATA__')->html());
        $data     = json_decode($json, true);
        return [
            'resume_list' => $data['props']['resumeInfo']['nearbyResumes'],
        ];
    }
    public function getError()
    {
        return $this->error;
    }

}