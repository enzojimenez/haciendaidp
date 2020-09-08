<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \Slim\Http\Request;
use \Slim\Http\Response;
use App\Hacienda\Token;
use App\Hacienda\Api;

include_once __DIR__ . '/credenciales.php';
$api = new Slim\App();

$api->get("/comprobante/{ambiente}/{clave}", function (Request $request, Response $response, $args) {
    try {
        $token = new Token(USUARIO, CONTRASENA);
        $api = new Api($request->getAttribute("ambiente"));
        $api->ChequearComprobante($request->getAttribute("clave"), $token->getToken());
        return $response->withJson($api->getRespuesta());
    } catch (\GuzzleHttp\Exception\GuzzleException $guzzle_exception) {
        // Manejo de excepciones de Guzzle
        return $guzzle_exception->getMessage();
    } catch (\Exception $ex) {
        // Manejo de cualquier otra excepción
        return $ex->getMessage();
    }
});

$api->get("/comprobantes/{ambiente}/{emisor}", function (Request $request, Response $response, $args) {
    try {
        $token = new Token(USUARIO, CONTRASENA);
        $api = new Api($request->getAttribute("ambiente"));
        $api->ChequearComprobantes($request->getAttribute("emisor"), $token->getToken(),
            ($request->getParam('limit')) ? $request->getParam('limit') : 50,
            ($request->getParam('offset')) ? $request->getParam('offset') : 0);
        return $response->withJson($api->getRespuesta());
    } catch (\GuzzleHttp\Exception\GuzzleException $guzzle_exception) {
        // Manejo de excepciones de Guzzle
        return $guzzle_exception->getMessage();
    } catch (\Exception $ex) {
        // Manejo de cualquier otra excepción
        return $ex->getMessage();
    }
});

$api->run();