<?php
namespace App\Hacienda;

class Api {
    protected $base;
    // Payload para envio de comprobantes
    protected $mensaje;
    protected $respuesta;
    protected $ambiente;
    // Http Info
    public $http_timeout;
    public $http_codigo;
    public $http_estado;
    public $http_data;

    /**
     * @return mixed
     */
    public function getRespuesta()
    {
        return $this->respuesta;
    }

    /**
     * @return mixed
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Api constructor.
     * @param $ambiente
     */
    public function __construct($ambiente) {
        $this->http_timeout = 60;
        $this->ambiente = ($ambiente == "stag") ? "-sandbox" : "";
    }

    /**
     * @param $clave
     * @param $ambiente
     * @param $token
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function ChequearComprobante($clave, $token){
        $cliente = new \GuzzleHttp\Client([ 'base_uri' => 'https://api.comprobanteselectronicos.go.cr' ]);
        try {
            $respuesta = $cliente->request('GET','/recepcion'.$this->ambiente.'/v1/recepcion/'.$clave,
                [
                    'headers' => [
                        'Authorization' => $token['token_type'] . ' ' . $token['access_token'],
                ],
                'connect_timeout' => $this->http_timeout,
                'timeout' => $this->http_timeout,
                //'on_stats' => function (\GuzzleHttp\TransferStats $stats) {
                //    echo 'R: ' . $stats->getHandlerStats()['total_time'] . PHP_EOL;
                //    echo 'C: ' . $stats->getHandlerStats()['connect_time'] . PHP_EOL;
                //}
            ]);
            $this->http_codigo = $respuesta->getStatusCode();
            $this->http_estado = $respuesta->getReasonPhrase();
            $this->respuesta = json_decode($respuesta->getBody(),true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // 4XX
            // 400 : "Bad Request" => "Authorization field missing" - Faltan parámetros
            // 401 : "Unauthorized" => "Key not authorized: Token is expired"
            // 405 : "Method Not Allowed" => GET en lugar de POST
            $this->http_codigo = $e->getResponse()->getStatusCode();
            $this->http_estado = $e->getResponse()->getReasonPhrase();
            $this->http_data = (NULL != $e->getResponse()->getHeader('X-Error-Cause')) ? utf8_encode($e->getResponse()->getHeader('X-Error-Cause')[0]) : NULL;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // 5XX
            $this->http_codigo = $e->getResponse()->getStatusCode();
            $this->http_estado = $e->getResponse()->getReasonPhrase();
            $this->http_data = (NULL != $e->getResponse()->getHeader('X-Error-Cause')) ? utf8_encode($e->getResponse()->getHeader('X-Error-Cause')[0]) : NULL;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->http_codigo = 0;
            $this->http_estado = 'Timeout';
            $this->http_data = 'Timeout';
        }
    }

    /**
     * @param $emisor
     * @param $token
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function ChequearComprobantes($emisor, $token, $limit = 50, $offset = 0){
        $cliente = new \GuzzleHttp\Client([ 'base_uri' => 'https://api.comprobanteselectronicos.go.cr' ]);
        try {
            $respuesta = $cliente->request('GET','/recepcion'.$this->ambiente.'/v1/comprobantes?emisor='.$emisor.'&limit='.$limit.'&offset='.$offset,
                [
                    'headers' => [
                        'Authorization' => $token['token_type'] . ' ' . $token['access_token'],
                ],
                'connect_timeout' => $this->http_timeout,
                'timeout' => $this->http_timeout,
                //'on_stats' => function (\GuzzleHttp\TransferStats $stats) {
                //    echo 'R: ' . $stats->getHandlerStats()['total_time'] . PHP_EOL;
                //    echo 'C: ' . $stats->getHandlerStats()['connect_time'] . PHP_EOL;
                //}
            ]);
            $this->http_codigo = $respuesta->getStatusCode();
            $this->http_estado = $respuesta->getReasonPhrase();
            $this->respuesta = json_decode($respuesta->getBody(),true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // 4XX
            // 400 : "Bad Request" => "Authorization field missing" - Faltan parámetros
            // 401 : "Unauthorized" => "Key not authorized: Token is expired"
            // 405 : "Method Not Allowed" => GET en lugar de POST
            $this->http_codigo = $e->getResponse()->getStatusCode();
            $this->http_estado = $e->getResponse()->getReasonPhrase();
            $this->http_data = (NULL != $e->getResponse()->getHeader('X-Error-Cause')) ? utf8_encode($e->getResponse()->getHeader('X-Error-Cause')[0]) : NULL;
            //var_dump($e->getResponse());
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // 5XX
            $this->http_codigo = $e->getResponse()->getStatusCode();
            $this->http_estado = $e->getResponse()->getReasonPhrase();
            $this->http_data = (NULL != $e->getResponse()->getHeader('X-Error-Cause')) ? utf8_encode($e->getResponse()->getHeader('X-Error-Cause')[0]) : NULL;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->http_codigo = 0;
            $this->http_estado = 'Timeout';
            $this->http_data = 'Timeout';
        }
    }
}