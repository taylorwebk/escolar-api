<?php
namespace Middlewares;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Models\Utils;
use \Models\Estudiante;
use \Models\Response as Res;

class StudentAuth
{
  public function __invoke (Request $req, Response $res, $next) {
    $tokenarr = $req->getHeader('Authorization');
    if (count($tokenarr) == 0) {
      $respuesta = Res::BadRequest('No se reconoce el token en los headers.');
      return $res->withJson($respuesta);
    }
    $data = Utils::decodeStudentToken($tokenarr[0]);
    $student = Estudiante::Where('username', $data['username'])->firstOrFail();
    $req = $req->withAttribute('student', $student);
    $res = $next($req, $res);
    // HERE GOES THE ACTIONS AFTER THE REQUEST
    return $res;
  }
}
