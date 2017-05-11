<?php
/**
 * @author Gabriel Moura <blx32@srmoura.com.br>
 * @copyright 2015-2017 SrMoura
 */
namespace Blx32\telegram;


class Botan
{

    /**
     * @var string Tracker url
     */
    protected $template_uri = 'https://api.botan.io/track?token=#TOKEN&uid=#UID&name=#NAME';
    /**
     * @var string Yandex AppMetrica application api_key
     */
    protected $token;
    /**
     * @var string
     */
    protected $shortener_uri = 'https://api.botan.io/s/?token=#TOKEN&user_ids=#UID&url=#URL';
    function __construct($token) {
        if (empty($token) || !is_string($token)) {
            throw new \Exception('Token should be a string', 2);
        }
        $this->token = $token;
    }

    public function shortenUrl($url, $user_id)
    {
        $request_url = str_replace(
            ['#TOKEN', '#UID', '#URL'],
            [$this->token, $user_id, urlencode($url)],
            $this->shortener_uri
        );
        $response = file_get_contents($request_url);
        return $response === false ? $url : $response;
    }

        public function track($message, $event_name = 'Message') {
        $uid = $message['from']['id'];
        $url = str_replace(
            ['#TOKEN', '#UID', '#NAME'],
            [$this->token, $uid, $event_name],
            $this->template_uri
        );
        $result = $this->request($url, $message);
        if ($result['error'] || $result['response']['status'] !== 'accepted') {
            throw new \Exception('Error Processing Request', 1);
        }
    }
    protected function request($url, $body) {
        $curlInstalled = function_exists('curl_version');
        $response = null;
        if ($curlInstalled) {
            $response = $this->curlRequest($url, $body);
        } else {
            $response = $this->streamContextRequest($url, $body);
        }
        $error = empty($response);
        $responseData = json_decode($response, true);
        return [
            'error' => $error,
            'response' => $responseData
        ];
    }
    private function curlRequest($url, $body) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($body)
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    private function streamContextRequest($url, $body) {
        $options = [
            'http' => [
                'header'  => 'Content-Type: application/json',
                'method'  => 'POST',
                'content' => json_encode($body)
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return $response;
    }
}