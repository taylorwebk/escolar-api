<?php
namespace Controllers;

use \Models\Admin;
use \Models\Curso;
use \Models\Cursa;
use \Models\Utils;
use \Models\Response;
use \Models\Estudiante;
use \Models\Apoderado;
use \Models\Inscribe;
use \Models\Instruye;
use \Models\Materia;
use \Models\Profesor;
use \Models\Periodo;
use \Models\Horario;
use \Models\Comunicado;
use \Models\Trabajo;
use \Models\Gestion;

class ReportC
{
  public static function teacherReport() {
    $docs = Profesor::orderBy('appat')->get();
    return [
      'docentes' => $docs
    ];
  }
  public static function courseList($year, $cursoId) {
    $year = Gestion::where('nro', $year)->first();
    $curso = Curso::find($cursoId);
    $curso->inscribes->each(function($inscribe) {
      $inscribe->estudiante;
    });
    $docs = [
      'nro'   => $curso->nro,
      'par'   => $curso->paralelo,
      'gestion'=> $year,
      'estudiantes' => $curso->inscribes->filter(function($inscribe) use ($year) {
        return $inscribe->gestion_id == $year->id;
      })->map(function($inscribe) {
        return [
          'nombres' => $inscribe->estudiante->nombres,
          'appat'   => $inscribe->estudiante->appat,
          'apmat'   => $inscribe->estudiante->apmat,
          'ci'      => $inscribe->estudiante->ci
        ];
      })
    ];
    return $docs;
  }
  public static function subjectGrades($year, $cursoId, $materiaId) {
    $year = Gestion::where('nro', $year)->first();
    $curso = Curso::find($cursoId);
    $materia = Materia::find($materiaId);
    $cursa = Cursa::where([
      ['curso_id', '=', $cursoId],
      ['materia_id', '=', $materiaId]
    ])->first();
    $instruye = Instruye::where([
      ['cursa_id', '=', $cursa->id],
      ['gestion_id', '=', $year->id]
    ])->first();
    $prof = $instruye->profesor;
    //return [$curso->inscribes->where('gestion_id', 2), $instruye->trabajos->where('id', 3)];
    $bim1SubjectsIds = $instruye->trabajos->where('bimestre_id', 1)->map(function($trabajo) {return $trabajo->id;});
    $bim2SubjectsIds = $instruye->trabajos->where('bimestre_id', 2)->map(function($trabajo) {return $trabajo->id;});
    $bim3SubjectsIds = $instruye->trabajos->where('bimestre_id', 3)->map(function($trabajo) {return $trabajo->id;});
    $bim4SubjectsIds = $instruye->trabajos->where('bimestre_id', 4)->map(function($trabajo) {return $trabajo->id;});
    $estudiantes = $curso->inscribes->where('gestion_id', $year->id)->map(function($inscribe) use (
      $bim1SubjectsIds, $bim2SubjectsIds, $bim3SubjectsIds, $bim4SubjectsIds
    ) {
      $estudiante = $inscribe->estudiante;
      return [
        'id'      => $inscribe->estudiante->id,
        'nombre' => $estudiante->appat.' '.$estudiante->apmat.' '.$estudiante->nombres,
        'bim1'    => round($estudiante->trabajos->whereIn('id', $bim1SubjectsIds)->avg('pivot.nota')),
        'bim2'    => round($estudiante->trabajos->whereIn('id', $bim2SubjectsIds)->avg('pivot.nota')),
        'bim3'    => round($estudiante->trabajos->whereIn('id', $bim3SubjectsIds)->avg('pivot.nota')),
        'bim4'    => round($estudiante->trabajos->whereIn('id', $bim4SubjectsIds)->avg('pivot.nota'))
      ];
    });
    return [
      'nro'       => $curso->nro,
      'gestion'     => $year->nro,
      'par'    => $curso->paralelo,
      'profesor'    => $prof->appat.' '.$prof->apmat.' '.$prof->nombres,
      'materian1'   => $materia->nombre,
      'materian2'   => $materia->nombremin,
      'materiac'    => $materia->campo,
      'estudiantes' => $estudiantes
    ];
  }
  public static function grades($year, $cursoId) {
    $year = Gestion::where('nro', $year)->first();
    $curso = Curso::find($cursoId);
    $materias = Materia::with(['cursas' => function($query) use ($curso) {
      $query->where('curso_id', '=', $curso->id);
    }])->get()->filter(function($materia) {
      return $materia->cursas->count() > 0;
    });
    $estudiantes = $curso->inscribes->where('gestion_id', $year->id)->where('curso_id', $curso->id)->map(
      function($inscribe) {
        return Estudiante::find($inscribe->estudiante_id);
      }
    );
    $est = $estudiantes->map(function($estudiante) use ($materias, $curso, $year) {
      $notas = $materias->map(function($materia) use ($estudiante, $curso, $year) {
        $cursa = Cursa::where([
          ['curso_id', '=', $curso->id],
          ['materia_id', '=', $materia->id]
        ])->first();
        $instruye = Instruye::where([
          ['cursa_id', '=', $cursa->id],
          ['gestion_id', '=', $year->id]
        ])->first();
        $bim1SubjectsIds = $instruye->trabajos->where('bimestre_id', 1)->map(function($trabajo) {return $trabajo->id;});
        $bim2SubjectsIds = $instruye->trabajos->where('bimestre_id', 2)->map(function($trabajo) {return $trabajo->id;});
        $bim3SubjectsIds = $instruye->trabajos->where('bimestre_id', 3)->map(function($trabajo) {return $trabajo->id;});
        $bim4SubjectsIds = $instruye->trabajos->where('bimestre_id', 4)->map(function($trabajo) {return $trabajo->id;});
        return [
          'nombre'  => $materia->nombre,
          'campo'   => $materia->campo,
          'bim1'    => round($estudiante->trabajos->whereIn('id', $bim1SubjectsIds)->avg('pivot.nota')),
          'bim2'    => round($estudiante->trabajos->whereIn('id', $bim2SubjectsIds)->avg('pivot.nota')),
          'bim3'    => round($estudiante->trabajos->whereIn('id', $bim3SubjectsIds)->avg('pivot.nota')),
          'bim4'    => round($estudiante->trabajos->whereIn('id', $bim4SubjectsIds)->avg('pivot.nota'))
        ];
      });
      return [
        'nombre'  => $estudiante->appat.' '.$estudiante->apmat.' '.$estudiante->nombres,
        'curso'   => $curso->nro,
        'paralelo'=> $curso->paralelo,
        'notas'   => $notas
      ];
    });
    return ['estudiantes' => $est->values()];
  }
}