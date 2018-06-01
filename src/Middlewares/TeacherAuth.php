<?php
namespace Middlewares;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Models\Utils;
use \Models\Profesor;
use \Models\Response as Res;

class TeacherAuth
{
  public function __invoke (Request $req, Response $res, $next) {
    $tokenarr = $req->getHeader('Authorization');
    if (count($tokenarr) == 0) {
      $respuesta = Res::BadRequest('No se reconoce el token en los headers.');
      return $res->withJson($respuesta);
    }
    $data = Utils::decodeToken($tokenarr[0]);
    if ($data == false) {
      $response = Res::Unauthorized(
        'Token expirado.',
        'Ha estado inactivo por un largo tiempo, por favor ingrese de nuevo'
      );
      return $res->withJson($response);
    }
    //var_dump($next['logger']);
    $prof = Profesor::Where('ci', $data['ci'])->firstOrFail();
    $req = $req->withAttribute('prof', $prof);
    $res = $next($req, $res);
    // HERE GOES THE ACTIONS AFTER THE REQUEST
    return $res;
  }
}
