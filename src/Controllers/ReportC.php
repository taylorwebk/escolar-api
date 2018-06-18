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

class ReportC
{
  public static function teacherReport() {
    $docs = Profesor::orderBy('appat')->get();
    return [
      'docentes' => $docs
    ];
  }
}