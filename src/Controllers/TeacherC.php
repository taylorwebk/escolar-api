<?php
namespace Controllers;

use \Models\Utils;
use \Models\Response;
use \Models\Profesor;

class TeacherC
{
  public static function Login($data)
  {
    $fields = ['ci', 'password'];
    if (!Utils::validateData($data, $fields)) {
      return Response::BadRequest(Utils::implodeFields($fields));
    }
    $prof = Profesor::where('ci', $data['ci'])->first();
    if (!$prof) {
      return Response::Unauthorized(
        'Ci no existe',
        'Verifica los datos de ingreso e intenta de nuevo'
      );
    }
    if (!password_verify($data['password'], $prof->password)) {
      return Response::Unauthorized(
        'password incorrecto',
        'Verifica los datos de ingreso e intenta de nuevo'
      );
    }
    $tokenstr = Utils::generateToken($prof->id, $prof->ci);
    return Response::OKWhitToken(
      'Login correcto',
      'Bienvenido/a, '.$prof->nombres,
      $tokenstr,
      $prof
    );
  }
}
