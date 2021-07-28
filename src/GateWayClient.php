<?php

namespace Wuchuanbin\HaichuanClient;

use Doctrine\Common\Cache\FilesystemCache;
use GuzzleHttp\Client;

class GateWayClient
{
    private $_client_id;
    private $_client_secret;
    private $_gateway = 'https://gateway.haichuanlife.com';

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->GatewayToken();
    }

    private function GatewayToken(){
        $cache = new FilesystemCache('/tmp/haichuan_client');
        $res = $cache->fetch('access_token');
        if(!$res){
            $url = '/openapi/Authorize/v1/GetAccessToken';
            $post = [
                'client_id'=>$this->_client_id,
                'client_secret'=>$this->_client_secret,
                'grant_type'=>'client_credentials'
            ];
            $data = $this->_RequestCloud($url,$post,false);
            $cache->save('access_token',$data['access_token'],3500);
            return $data['access_token'];
        } else {
            return $res;
        }
    }

    public function RequestCloud($url,$param=[],$auth=true){
        $client = new Client();
        $option = [];
        if($auth){
            $option['headers'] = ['Authorization'=>'Bearer '.$this->getAccessToken()];
            if(!empty($param)){
                $option['form_params'] = ['data'=>json_encode($param)];
            }
        } else {
            $option['form_params'] = $param;
        }

        $option['headers']['User-Agent'] = 'Gateway Client V1.0';
        $res = $client->request('POST',$this->_gateway.$url,$option);
        if($res->getStatusCode()==200){
            return json_decode($res->getBody(),true);
        } else {
            return [];
        }
    }

    /**
     * @param mixed $client_id
     */
    public function setClientId($client_id): void
    {
        $this->_client_id = $client_id;
    }

    /**
     * @param mixed $client_secret
     */
    public function setClientSecret($client_secret): void
    {
        $this->_client_secret = $client_secret;
    }

}