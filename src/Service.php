<?php


namespace liyifei\baiduface;


use yii\base\Component;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;
use yii\web\Request;

class Service extends Component
{
    public $apiKey;

    public $secretKey;

    public $accessTokenFilePath;

    public $gateway = 'https://aip.baidubce.com/rest/2.0/face/v3';

    private $_accessToken;

    public function init()
    {
        $this->_accessToken = $this->_accesstoken();
    }

    public function policeValidation($image, $imageType, $idCardNumber, $name, $qualityControl, $livenessControl)
    {
        return $this->request('person/verify', [
            'image' => $image,
            'image_type' => $imageType,
            'id_card_number' => $idCardNumber,
            'name' => $name,
            'quality_control' => $qualityControl,
            'liveness_control' => $livenessControl
        ]);
    }

    public function request($service, $data)
    {
        $url = $this->gateway . '/' . $service;
        $client = new Client();
        $request = $client->createRequest();
        $request->setFormat(Client::FORMAT_JSON);
        $request->setMethod('POST');
        $request->setData($data);
        $request->setUrl($url . '?access_token=' . $this->_accessToken);

        $response = $request->send();
        if ($response->isOk) {
            $result = json_decode($response->content, 1);
            return $result;
        } else {
            throw new BadRequestHttpException($request->content);
        }
    }

    private function _accessToken()
    {
        if (file_exists($this->accessTokenFilePath)) {
            $jsonStr = file_get_contents($this->accessTokenFilePath);
            $json = json_decode($jsonStr, 1);
            if ($json && $json['expires_at'] > time()) {
                return $json['access_token'];
            }
        }

        $url = 'https://aip.baidubce.com/oauth/2.0/token';
        $client = new Client();
        $request = $client->createRequest();
        $request->setMethod('GET');
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->apiKey,
            'client_secret' => $this->secretKey
        ];
        $url .= '?' . http_build_query($data);
        $request->setUrl($url);
        $response = $request->send();

        if ($response->isOk) {
            $result = json_decode($response->content, 1);
            $data = [
                'access_token' => $result['access_token'],
                'expires_at' => time() + $result['expires_in']
            ];
            file_put_contents($this->accessTokenFilePath, json_encode($data));
        } else {
            throw new BadRequestHttpException($request->content);
        }
    }
}