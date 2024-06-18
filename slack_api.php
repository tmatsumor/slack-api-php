<?php
namespace tmatsumor\slack_api_php;
if(!class_exists('\tmatsumor\http_requests_php\HttpRequests')){
    require_once(__DIR__.'/http_requests_php/http_requests.php');
}

class SlackAPI extends \tmatsumor\http_requests_php\HttpRequests
{
    const SLACK_URL = 'https://slack.com/api/';
    const TMP_IMG_PATH = '/var/tmp/image_for_slack_api_php';
    private $token;

    public function __construct($auth_token) {
        $this->token = $auth_token;
    }

    public function postMessage($channel, $text) {
        $p = http_build_query([
            'token'   => $this->token,
            'channel' => $channel,
            'text'    => $text
        ]);
        return $this->post(self::SLACK_URL.'chat.postMessage', $p);
    }

    public function fileUpload($channel, $text, $imgurl) {
        file_put_contents(self::TMP_IMG_PATH, file_get_contents($imgurl)); // store image file once
        $mime = getimagesize(self::TMP_IMG_PATH)['mime'];
        $file = new \CurlFile(self::TMP_IMG_PATH, $mime,                 // create curl file object
                    date('Ymd_His').'.'.str_replace('image/', '', $mime));    // file name: Ymd_His
        $p = [
            'token'    => $this->token,
            'channels' => $channel,
            'file'     => $file,
            'title'    => '　',                                    // hide its file title of a post
            'initial_comment' => $text
        ];
        $res = $this->post(self::SLACK_URL.'files.upload', $p);
        unlink(self::TMP_IMG_PATH);        // remove image file in tmp directory after slack upload
        return $res;
    }
}
