<?php
namespace Controllers;

use \Models\Utils;
use \Models\Response;
use \Models\Estudiante;

class StudentC
{
  public static function Login($data)
  {
    $fields = ['username', 'password'];
    if (!Utils::validateData($data, $fields)) {
      return Response::BadRequest(Utils::implodeFields($fields));
    }
    $student = Estudiante::where('username', $data['username'])->first();
    if (!$student) {
      return Response::Unauthorized(
        'username no existe',
        'Verifica los datos de ingreso e intenta de nuevo'
      );
    }
    if (!password_verify($data['password'], $student->password)) {
      return Response::Unauthorized(
        'password incorrecto',
        'Verifica los datos de ingreso e intenta de nuevo'
      );
    }
    $student->apoderado;
    $tokenstr = Utils::generateStudentToken($student->id, $student->username);
    return Response::OKWhitToken(
      'Login correcto',
      'Bienvenido/a, '.$student->nombres,
      $tokenstr,
      $student
    );
  }
}
