<?php
/**
 * @author Gabriel Moura <blx32@srmoura.com.br>
 * @copyright 2015-2017 SrMoura
 */
/*
 * $appMetricaApi = new AppMetricaApi();
$labelId = $appMetricaApi->createLabel("My Super Label");
$appMetricaApi->getLabels();
$labelId = $appMetricaApi->getLabelIdByName("My Super Label");

$appId = "14350";
$appMetricaApi->moveApp2Label($appId, $labelId);
$appMetricaApi->deleteAppFromLabel($appId);
$appMetricaApi->deleteLabel($appId);
 */
namespace Blx32\telegram;
/**
 * Class AppMetricaApi
 * @package Blx32\telegram
 */

class AppMetricaApi
{

    private $curl;

    const DATA_DEFAULT_TIMEZONE = 'Asia/Irkutsk';
    private $oauthToken;

    function __construct($oauthToken = null)
    {
        if (empty($oauthToken)) {
            $this->oauthToken = Yii::$app->params['appMetricaApiOauthToken'];
        } else {
            $this->oauthToken = $oauthToken;
        }

        $this->curl = curl_init();
    }

    private function request($url, $method = "POST", $data = null)
    {
        $url .= '?oauth_token=' . $this->oauthToken;

        curl_reset($this->curl);

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($data)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, Json::encode($data));
        }

        $out = curl_exec($this->curl);
        $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            return Json::decode($out);
        }
        throw new HttpException("Error in request appmetrica, response code: $code\ndata:\n$out");
    }

    /**
     * Create label with given name
     * @param $name string Label name
     * @return string Label id
     * @throws HttpException
     */
    public function createLabel($name)
    {
        $url = 'https://beta.api-appmetrika.yandex.ru/management/v1/labels';
        $data = ['label' => ['name' => $name]];
        $result = $this->request($url, "POST", $data);
        return $result['id'];
    }

    /**
     * Get your labels
     * @return mixed Your labels list
     * (
     * [id] => 12720
     * [name] => Traffic Racer
     * ),
     * (
     * [id] => 13125
     * [name] => Floppy
     * )
     * @throws HttpException
     */
    public function getLabels()
    {
        $url = "https://beta.api-appmetrika.yandex.ru/management/v1/labels";
        $result = $this->request($url, "GET");
        return isset($result['labels']) ? $result['labels'] : null;
    }

    /**
     * Get label id by name
     * @param $name string Label name
     * @return string Label id
     * @throws HttpException
     */
    public function getLabelIdByName($name)
    {
        $labels = $this->getLabels();
        if (!empty($labels)) {
            foreach ($labels as $label) {
                if ($label['name'] == $name) {
                    return $label['id'];
                }
            }
        }
        return null;
    }

    public function deleteLabel($labelId)
    {
        $url = "https://beta.api-appmetrika.yandex.ru/management/v1/label/$labelId";
        return $this->request($url, "DELETE");
    }

    public function renameLabel($labelId, $name)
    {
        $url = "https://beta.api-appmetrika.yandex.ru/management/v1/label/$labelId";
        $data = ['label' => ['name' => $name]];
        $this->request($url, "PUT", $data);
    }

    public function moveApp2Label($appId, $labelId)
    {
        $url = "https://beta.api-appmetrika.yandex.ru/management/v1/label/$labelId/link/$appId";
        $this->request($url);
    }

    public function deleteAppFromLabel($appId)
    {
        $url = "https://beta.api-appmetrika.yandex.ru/management/v1/labels/unlink/$appId";
        $this->request($url);
    }

}