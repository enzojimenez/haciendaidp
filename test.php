<?php
// Carga de paquetes (Composer)
require_once __DIR__ . '/vendor/autoload.php';

use App\Hacienda\Token;

// Credenciales de usuario IdP de Hacienda, creados desde el ATV
$usuario = 'cpf-XX-XXXX-XXXX@XXXX.comprobanteselectronicos.go.cr';
$contrasena = 'XXXXXXXXXXXXXXXXX';

try {
    // Se instancia la clase pasando las credenciales
    $token = new Token($usuario, $contrasena);

    // Método getToken devuelve el contenido del archivo JSON almacenado
    $token_data = $token->getToken();

    // Se usa el access_token respectivo
    $access_token = $token_data['access_token'];

} catch (\GuzzleHttp\Exception\GuzzleException $guzzle_exception) {
    // Manejo de excepciones de Guzzle
} catch (\Exception $ex) {
    // Manejo de cualquier otra excepción
}
