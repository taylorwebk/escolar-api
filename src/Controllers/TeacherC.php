<?php
namespace Controllers;

use \Illuminate\Support\Collection;
use \Models\Utils;
use \Models\Response;
use \Models\Profesor;
use \Models\Cursa;
use \Models\Instruye;
use \Models\Trabajo;
use \Models\Estudiante;

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
    $yearId = Utils::getCurrentYear()->id;
    $prof->instruyescurrent->each(function($instruye) {
      $instruye->cursa->curso;
      $instruye->cursa->materia;
    });
    $firstCourse = $prof->instruyescurrent->first();
    if ($firstCourse) {
      $firstCourse->trabajos->each(function($trabajo) {
        $trabajo->bimestre;
      });
      $trabajosId = $firstCourse->trabajos->map(function($trabajo) {
        return $trabajo->id;
      });
      $firstCourse->cursa->curso->inscribescurrent->each(function($inscribe) use ($trabajosId) {
        $inscribe->estudiante->trabajos->whereIn('id', $trabajosId);
      });
      $firstCourseParsed = [
        'id'    => $firstCourse->cursa->id,
        'nro'   => $firstCourse->cursa->curso->nro,
        'par'   => $firstCourse->cursa->curso->paralelo,
        'mat'   => $firstCourse->cursa->materia->nombre,
        'campo' => $firstCourse->cursa->materia->campo,
        'trabajos' => $firstCourse->trabajos->map(function($trabajo) {
          return [
            'id'      => $trabajo->id,
            'nombre'  => $trabajo->nombre,
            'fecha'   => $trabajo->fecha,
            'bimestre'=> $trabajo->bimestre->nro 
          ];
        }),
        'estudiantes' => $firstCourse->cursa->curso->inscribescurrent->map(function($inscribe) {
          return [
            'id'      => $inscribe->estudiante->id,
            'nombres' => $inscribe->estudiante->nombres,
            'appat'   => $inscribe->estudiante->appat,
            'apmat'   => $inscribe->estudiante->apmat,
            'ci'      => $inscribe->estudiante->ci,
            'trabajos'=> $inscribe->estudiante->trabajos->map(function($trabajo) {
              return [
                'id'  => $trabajo->id,
                'nota'=> $trabajo->pivot->nota
              ];
            })
          ];
        })
      ];
      $cursos = $prof->instruyescurrent->map(function($instruye) {
        return [
          'id'    => $instruye->cursa->id,
          'nro'   => $instruye->cursa->curso->nro,
          'par'   => $instruye->cursa->curso->paralelo,
          'mat'   => $instruye->cursa->materia->nombre,
          'matid' => $instruye->cursa->materia->id,
          'campo' => $instruye->cursa->materia->campo
        ];
      });
    } else {
      $cursos = [];
      $firstCourseParsed = [];
    }
    $resp = [
      "profesor"=> [
        "id"      => $prof->id,
        "nombres" => $prof->nombres,
        "appat"   => $prof->appat,
        "apmat"   => $prof->apmat,
        "ci"      => $prof->ci,
        "dir"     => $prof->dir
      ],
      "cursos"  => $cursos,
      "curso_actual"  => $firstCourseParsed
    ];
    $tokenstr = Utils::generateToken($prof->id, $prof->ci);
    return Response::OKWhitToken(
      'Login correcto',
      'Bienvenido/a, '.$prof->nombres,
      $tokenstr,
      $resp
    );
  }
  public static function addHomeWork($prof, $data) {
    $fields = ['id', 'nombre'];
    if (!Utils::validateData($data, $fields)) {
      return Response::BadRequest(Utils::implodeFields($fields));
    }
    $cursa = Cursa::find($data['id']);
    if (!$cursa) {
      return Response::BadRequest('Tabla cursa, no existe ID:'.$data['id']);
    }
    $bimId = Utils::getCurrentBimester()->id;
    $yearId = Utils::getCurrentYear()->id;
    $instruye = Instruye::where([
      ['cursa_id', '=', $cursa->id],
      ['profesor_id', '=', $prof->id],
      ['gestion_id', '=', $yearId]
    ])->first();
    $trabajo = Trabajo::create([
      'nombre'  => $data['nombre'],
      'fecha'   => date('Y-m-d'),
      'bimestre_id' => $bimId,
      'instruye_id' => $instruye->id
    ]);
    return Response::OKWhitToken(
      'Todo OK',
      'Trabajo: '.$trabajo->nombre.' creado en fecha: '.$trabajo->fecha,
      Utils::generateToken($prof->id, $prof->ci),
      $trabajo
    );
  }
  public static function getCourseInfo($prof, $id) {
    $yearId = Utils::getCurrentYear()->id;
    $firstCourse = Instruye::where([
      ['profesor_id', '=', $prof->id],
      ['gestion_id', '=', $yearId],
      ['cursa_id', '=', $id]
    ])->first();
    if (!$firstCourse) {
      return Response::BadRequest('No existe el id: '.$id);
    }
    $firstCourse->trabajos->each(function($trabajo) {
      $trabajo->bimestre;
    });
    $trabajosId = $firstCourse->trabajos->map(function($trabajo) {
      return $trabajo->id;
    });
    $firstCourse->cursa->curso->inscribescurrent->each(function($inscribe) use ($trabajosId) {
      $inscribe->estudiante->trabajos->whereIn('id', $trabajosId);
    });
    $firstCourseParsed = [
      'id'    => $firstCourse->cursa->id,
      'nro'   => $firstCourse->cursa->curso->nro,
      'par'   => $firstCourse->cursa->curso->paralelo,
      'mat'   => $firstCourse->cursa->materia->nombre,
      'campo' => $firstCourse->cursa->materia->campo,
      'trabajos' => $firstCourse->trabajos->map(function($trabajo) {
        return [
          'id'      => $trabajo->id,
          'nombre'  => $trabajo->nombre,
          'fecha'   => $trabajo->fecha,
          'bimestre'=> $trabajo->bimestre->nro 
        ];
      }),
      'estudiantes' => $firstCourse->cursa->curso->inscribescurrent->map(function($inscribe) {
        return [
          'id'      => $inscribe->estudiante->id,
          'nombres' => $inscribe->estudiante->nombres,
          'appat'   => $inscribe->estudiante->appat,
          'apmat'   => $inscribe->estudiante->apmat,
          'ci'      => $inscribe->estudiante->ci,
          'trabajos'=> $inscribe->estudiante->trabajos->map(function($trabajo) {
            return [
              'id'  => $trabajo->id,
              'nota'=> $trabajo->pivot->nota
            ];
          })
        ];
      })
    ];
    $tokenstr = Utils::generateToken($prof->id, $prof->ci);
    return Response::OKWhitToken(
      'Login correcto',
      'Bienvenido/a, '.$prof->nombres,
      $tokenstr,
      $firstCourseParsed
    );
  }
  public static function setGrades($prof, $data) {
    $fields = ['notas'];
    if (!Utils::validateData($data, $fields)) {
        return Response::BadRequest(Utils::implodeFields($fields));
    }
    foreach ($data['notas'] as $idstudent => $trabajos) {
        $student = Estudiante::find($idstudent);
        foreach ($trabajos as $idtrabajo => $nota) {
            if ($student->trabajos->contains($idtrabajo)) {
                $student->trabajos()->updateExistingPivot($idtrabajo, ['nota' => $nota]);
            } else {
                $student->trabajos()->attach($idtrabajo, ['nota' => $nota]);
            }
        }
    }
    return Response::OKWhitToken(
        'Todo OK',
        'Notas guardadas',
        Utils::generateToken($prof->id, $prof->ci),
        null
    );
  }
  public static function updateProfile($prof, $data) {
    $fields = ['dir', 'password'];
    if (!Utils::validateData($data, $fields)) {
      return Response::BadRequest(Utils::implodeFields($fields));
    }
    $prof->dir = $data['dir'];
    if ($data['password'] != '') {
      $prof->password = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    $prof->save();
    return Response::OKWhitToken(
      'Todo OK',
      'Datos actualizados',
      Utils::generateToken($prof->id, $prof->ci),
      $prof
    );
  }
}
