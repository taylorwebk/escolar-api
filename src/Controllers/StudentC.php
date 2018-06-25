<?php
namespace Controllers;

use \Models\Utils;
use \Models\Response;
use \Models\Estudiante;
use \Models\Inscribe;
use \Models\Cursa;
use \Models\Trabajo;

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
  public static function getSchedule($student) {
    $year = Utils::getCurrentYear();
    $inscribe = Inscribe::where([
      ['gestion_id', '=', $year->id],
      ['estudiante_id', '=', $student->id]
    ])->first();
    if (!$inscribe) {
      return Response::BadRequest('Error, gestion: '.$year->id.' no encontrado o estudiante no inscrito');
    }
    $inscribe->curso->cursas->each(function($cursa) use ($year) {
      $cursa->materia;
      $cursa->instruyes->where('gestion_id', $year->id);
      $cursa->instruyes->each(function($instruye) {
        $instruye->profesor;
      });
    });
    $resp = [
      "gestion"   => $year->nro,
      "fecha"     => $inscribe->fecha,
      "curso"     => $inscribe->curso->nro,
      "paralelo"  => $inscribe->curso->paralelo,
      "materias"  => $inscribe->curso->cursas->map(function($cursa) {
        $instruye = $cursa->instruyes->first();
        $nprof = $instruye ? $instruye->profesor->nombres : null;
        $approf = $instruye ? $instruye->profesor->appat : null;
        $amprof = $instruye ? $instruye->profesor->apmat : null;
        $nombreprof = $instruye ? $nprof.' '.$approf.' '.$amprof : 'Sin Designar';
        return [
          "id"      => $cursa->id,
          "nombre"  => $cursa->materia->nombre,
          "nombre2" => $cursa->materia->nombremin,
          "profesor"   => $nombreprof
        ];
      })
    ];
    return Response::OK('Todo OK', 'Datos actualizados', $resp);
  }
  public static function getHomeworks($student, $id) {
    $year = Utils::getCurrentYear();
    $cursa = Cursa::find($id);
    $cursa->curso;
    $cursa->materia;
    $instruye = $cursa->instruyes->where('gestion_id', $year->id)->first();
    $nprof = $instruye ? $instruye->profesor->nombres : null;
    $approf = $instruye ? $instruye->profesor->appat : null;
    $amprof = $instruye ? $instruye->profesor->apmat : null;
    $nombreprof = $instruye ? $nprof.' '.$approf.' '.$amprof : 'Sin Designar';
    $trabajos = [];
    if ($instruye) {
      if ($instruye->trabajos->count() > 0) {
        $instruye->trabajos->each(function($trabajo) {
          $trabajo->bimestre;
          $trabajo->estudiantes;
        });
        $trabajos = $instruye->trabajos->map(function($trabajo) use ($student) {
          @$nota = $trabajo->estudiantes->where('id', $student->id)->first()->pivot->nota;
          return [
            "nombre"    => $trabajo->nombre,
            "fecha"     => $trabajo->fecha,
            "bimestre"  => $trabajo->bimestre->nro,
            "nota"=> $nota
          ];
        });
      }
    }
    $resp = [
      "gestion"     => $year->nro,
      "curso"       => $cursa->curso->nro,
      "paralelo"    => $cursa->curso->paralelo,
      "nomMateria"  => $cursa->materia->nombre,
      "nom2Materia" => $cursa->materia->nombremin,
      "campMateria" => $cursa->materia->campo,
      "profesor"    => $nombreprof,
      "trabajos"    => $trabajos
    ];
    return Response::OK('todo ok', 'Trabajos de '.$cursa->materia->nombre.' cargados.', $resp);
  }
  public static function changePassword($student, $data) {
    $fields = ['password'];
    if (!Utils::validateData($data, $fields)) {
      return Response::BadRequest(Utils::validateData($fields));
    }
    $student->password  = password_hash($data['password'], PASSWORD_DEFAULT);
    $student->save();
    return Response::OK('ok', 'Contraseña cambiada', null);
  }
}
