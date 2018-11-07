<?php
namespace App\Hacienda;

class Token {
    // URL base del servidor IdP
    protected $url_base = 'https://idp.comprobanteselectronicos.go.cr';

    // Ruta del servidor IdP
    // Producción = /auth/realms/rut/protocol/openid-connect/token
    // Sandbox    = /auth/realms/rut-stag/protocol/openid-connect/token
    protected $url_idp;

    // Producción = api-prod
    // Sandbox    = api-stag
    protected $cliente_id;
    
    public $ambiente;

    // Usuario generado desde el ATV
    protected $usuario;

    // Contraseña generada desde el ATV
    protected $contrasena;

    // Token Info
    protected $token_archivo;
    protected $token_estado;
    protected $token_data;

    // Http Info
    public $http_codigo;
    public $http_estado;
    public $http_data;

    /**
     * Al instanciar la clase de debe pasar usuario, contrasena y ambiente
     * Token constructor.
     * @param string $usuario
     * @param string $contrasena
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function __construct($usuario, $contrasena) {
        // Se extrae ambiente del usuario
        $this->ambiente = substr($usuario,(1+strpos($usuario,"@")),4);
        $ambiente_idp = ($this->ambiente == "stag") ? "-stag" : "";
        $this->url_idp = "/auth/realms/rut".$ambiente_idp."/protocol/openid-connect/token";
        $this->cliente_id = "api-".$this->ambiente;
        $this->usuario = $usuario;
        $this->contrasena = $contrasena;
        // Archivo JSON con token del usuario
        $this->token_archivo = __DIR__ . '/../Tokens/' . strtok($this->usuario,"@") . '_' . $this->ambiente;
        // Se inicia el archivo del token si no esta seteado
        if (!file_exists($this->token_archivo)) {
            if($this->Crear($this->Obtener())){
                $this->token_estado = 'CREADO';
            }
        } else {
            // Si existe, carga el contenido en memoria.
            $this->token_data = json_decode(file_get_contents($this->token_archivo),true);
            // Valida si el token aún es válido (dura 300 segundos)
            if (!$this->TokenValido()) {
                // Valida si el refresh token aún es válido (dura 3600 segundos)
                if (!$this->RefreshValido()) {
                    // En caso de que todo este invalido se obtiene token nuevo
                    if($this->Crear($this->Obtener())) {
                        $this->token_estado = 'NUEVO';
                    }
                } else {
                    // Si aún se puede refrescar, entonces se refresca
                    if($this->Crear($this->Refrescar())) {
                        $this->token_estado = 'REFRESCADO';
                    }
                }
            } else {
                $this->http_codigo = 200;
                $this->token_estado = 'VALIDO';
            }
        }
    }

    /**
     * @return string
     */
    public function getTokenEstado()
    {
        return $this->token_estado;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token_data;
    }

    /**
     * @param $resultado
     * @return bool
     * @desc En caso de respuesta o resultado satisfactorio, entonces setea Token
     */
    function Crear($resultado) {
        if (floor($this->http_codigo / 100) == 2){
            $this->token_data = json_decode($this->http_data,true);
            $this->token_data['token_time'] = time();
            $this->token_data['refresh_time'] = time();
            file_put_contents($this->token_archivo, json_encode($this->token_data));
            return true;
        }
        unlink($this->token_archivo);
        $this->token_archivo = NULL;
        $this->token_data = NULL;
        $this->token_estado = 'ERROR';
        return false;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @desc Parametros para obtener Access Token
     */
    function Obtener() {
        $this->Idp([
            'grant_type' => 'password',
            'client_id' => $this->cliente_id,
            'username' => $this->usuario,
            'password' => $this->contrasena
        ]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @desc Parametros para refrescar Access Token
     */
    function Refrescar() {
        $this->Idp([
            'grant_type' => 'refresh_token',
            'client_id' => $this->cliente_id,
            'refresh_token' => $this->token_data['refresh_token']
        ]);
    }

    /**
     * @return bool
     * @desc Valida si el Access Token aun es válido (300 segundos)
     */
    function TokenValido() {
        return ((time() - (int)$this->token_data['token_time']) < (int)$this->token_data['expires_in']);
    }

    /**
     * @return bool
     * @desc Valida si el refresh token aun es válido (14400 segundos)
     */
    function RefreshValido() {
        return ((time() - (int)$this->token_data['refresh_time']) < (int)$this->token_data['refresh_expires_in']);
    }

    /**
     * @param $params
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @desc Hace un llamado POST al IdP de Hacienda
     */
    function Idp($params) {
        $cliente = new \GuzzleHttp\Client([ 'base_uri' => $this->url_base ]);
        try {
            $respuesta = $cliente->request('POST', $this->url_idp, [
                'form_params' => $params
            ]);
            // 2XX
            // 200 es un TOKEN satisfactorio
            $this->http_codigo = $respuesta->getStatusCode();
            $this->http_estado = $respuesta->getReasonPhrase();
            $this->http_data = $respuesta->getBody();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // 4XX
            // 400 : "Bad Request" => Faltan parámetros
            // 401 : "Unauthorized" => "Invalid user credentials"
            // 405 : "Method Not Allowed" => GET en lugar de POST
            $this->http_codigo = $e->getResponse()->getStatusCode();
            $this->http_estado = $e->getResponse()->getReasonPhrase();
            $this->http_data = $e->getMessage();
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // 5XX
            $this->http_codigo = $e->getResponse()->getStatusCode();
            $this->http_estado = $e->getResponse()->getReasonPhrase();
            $this->http_data = $e->getMessage();
        }
    }
}
