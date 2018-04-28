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

class AdminC
{
    public static function Add($nombre, $appat, $apmat, $cel, $ci, $password)
    {
        Admin::create([
                'nombres'   =>  $nombre,
                'appat'     =>  $appat,
                'apmat'     =>  $apmat,
                'cel'       =>  $cel,
                'ci'        =>  $ci,
                'password'  =>  password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
    public static function Login($data)
    {
        $fields = ['ci', 'password'];
        if (!Utils::validateData($data, $fields)) {
            return Response::BadRequest(
                Utils::implodeFields($fields)
            );
        }
        $admin = Admin::Where('ci', $data['ci'])->first();
        if(!$admin) {
            return Response::Unauthorized(
                'ci inválido.',
                'El CI ingresado no ha sido identificado'
            );
        }
        if (password_verify($data['password'], $admin->password)) {
            $tokenstr = Utils::generateToken($admin->id, $admin->ci);
            $hora = (int)date('G');
            $saludo = '';
            if ($hora <= 12) {
                $saludo = 'Buenos días ';
            } else {
                $saludo = $hora <= 18 ? 'Buenas tardes ' : 'Buenas Noches';
            }
            $arrayResponse = [
                'gestion' => Utils::getCurrentYear()->nro,
                'bimestre' => Utils::getCurrentBimester(),
                'materias' => Materia::select('id', 'nombre')->get(),
                'admin' => $admin
            ];
            return Response::OKWhitToken(
                'Login correcto',
                $saludo.$admin->nombres,
                $tokenstr,
                $arrayResponse
            );
        } else {
            return Response::Unauthorized(
                'ci o password inválidos.',
                'Verifique que los datos de ingreso sean correctos.'
            );
        }
    }
    public static function GetCourses($admin)
    {
        $cursos = Curso::all();
        $cursos = $cursos->reduce(function($carry, $item) {
            $par = [
                'id'        => $item->id,
                'paralelo'  => $item->paralelo,
                'estado'    => $item->estado
            ];
            if (!Utils::inMultiarray($item->nro, $carry, 'curso')) {
                array_push($carry, [
                    'curso' => $item->nro,
                    'paralelos' => []
                ]);
            }
            array_push($carry[count($carry) - 1]['paralelos'], $par);
            return $carry;
        }, []);
        $tokenstr = Utils::generateToken($admin->id, $admin->ci);
        return Response::OKWhitToken(
            'Cursos obtenido correctamente',
            'Cursos cargados',
            $tokenstr,
            $cursos
        );
    }
    public static function EnableCourses($admin, $data)
    {
        $fields = ['habilitados'];
        if (!Utils::validateData($data, $fields)) {
            return Response::BadRequest(Utils::implodeFields($fields));
        }
        if (gettype($data['habilitados']) != 'array') {
            return Response::BadRequest('El campo habilitados debe ser un Array');
        }
        $cursos = Curso::all();
        foreach ($cursos as $curso) {
            if (in_array($curso->id, $data['habilitados'])) {
                $curso->estado = 1;
                $curso->save();
            } else {
                $curso->estado = 0;
                $curso->save();
            }
        }
        $cursos = $cursos->reduce(function($carry, $item) {
            $par = [
                'id'        => $item->id,
                'paralelo'  => $item->paralelo,
                'estado'    => $item->estado
            ];
            if (!Utils::inMultiarray($item->nro, $carry, 'curso')) {
                array_push($carry, [
                    'curso' => $item->nro,
                    'paralelos' => []
                ]);
            }
            array_push($carry[count($carry) - 1]['paralelos'], $par);
            return $carry;
        }, []);
        return Response::OKWhitToken(
            'Cursos habilitados y deshabilitados correctamente',
            'Cambios guardados...',
            Utils::generateToken($admin->id, $admin->ci),
            $cursos
        );
    }
    public static function RegisterStudent($admin, $data)
    {
        $fields = ['cursoid', 'nombres', 'appat', 'apmat', 'ci', 'dir', 'cel', 'aponombre', 'apocel', 'apopar'];
        if(!Utils::validateData($data, $fields)) {
            return Response::BadRequest(Utils::implodeFields($fields));
        }
        $username = Utils::generateUsername($data['nombres'], $data['appat']);
        $curso = Curso::find($data['cursoid']);
        if (!$curso) {
            return Response::BadRequest('No existe el curso con ID: ' . $data['cursoid']);
        }
        $apoderado = Apoderado::create([
            'nombre'    => $data['aponombre'],
            'nroref'    => $data['apocel'],
            'parentesco'=> $data['apopar']
        ]);
        $student = Estudiante::create([
            'apoderado_id' => $apoderado->id,
            'ci'        => $data['ci'],
            'nombres'   => $data['nombres'],
            'appat'     => $data['appat'],
            'apmat'     => $data['apmat'],
            'username'  => $username,
            'dir'       => $data['dir'],
            'nrocel'    => $data['cel'],
            'password'  => password_hash($username, PASSWORD_DEFAULT)
        ]);
        $currentYear = Utils::getCurrentYear();
        Inscribe::create([
            'estudiante_id' => $student->id,
            'curso_id'      => $curso->id,
            'gestion_id'    => $currentYear->id,
            'fecha'         => date('Y-m-d')
        ]);
        return Response::OKWhitToken(
            'Registro correcto',
            'Estudiante: ' . $student->nombres . ' inscrito correctamente en el curso: ' . $curso->nro . '° de Secundaria, paralelo: ' . $curso->paralelo,
            Utils::generateToken($admin->id, $admin->ci),
            $student->username
        );
    }
    public static function RegisterTeacher($admin, $data) {
        $fields = ['nombres', 'appat', 'apmat', 'ci', 'dir', 'materias'];
        if (!Utils::validateData($data, $fields)) {
            return Response::BadRequest(Utils::implodeFields($fields));
        }
        $prof = Profesor::create([
            'nombres'   => $data['nombres'],
            'appat'     => $data['appat'],
            'apmat'     => $data['apmat'],
            'ci'        => $data['ci'],
            'dir'       => $data['dir']
        ]);
        $mats = array_reduce($data['materias'], function($res, $mat) {
            $res[$mat] = ['estado' => 1];
            return $res;
        }, []);
        $prof->materias()->attach($mats);
        return Response::OKWhitToken(
            'Registro exitoso',
            'Docente: ' . $prof->nombres . ' registrado en el sistema.',
            Utils::generateToken($admin->id, $admin->ci),
            null
        );
    }
    public static function getCourseSchedule($admin, $id)
    {
        $pers = Periodo::with('dia')->get();
        $curse = Curso::select('id')->where('id', $id)->with(['cursas.materia.profesores'])->first();
        if (!$curse) {
            return Response::BadRequest('No existe el curso con ID:'.$id);
        }
        $response = [];
        $response['id_curso'] = $curse->id;
        $response['materias'] = $curse->cursas->map(function ($item) {
            $profs = $item->materia->profesores->map(function($prof) {
                return [
                    'id'    => $prof->id,
                    'nombre'=> $prof->nombres . ' ' . $prof->appat
                ];
            });
            return [
                'id'            => $item->materia->id,
                'materia'       => $item->materia->nombre,
                'profesores'    => $profs,
                'profesor'      => @ $item->instruyes->firstWhere('gestion_id', Utils::getCurrentYear()->id)->profesor->id
            ];
        });
        $mats = Cursa::where('curso_id', $id)->get();
        $idscursa = $mats->map(function($mat) {
            return $mat->id;
        });
        $horarios = Horario::whereIn('cursa_id', $idscursa)->get();
        $response['horario'] = $pers->reduce(function ($res, $per) use ($horarios) {
            $materia = null;
            if ($horarios->contains('periodo_id', $per->id)) {
                $materia = $horarios->where('periodo_id', $per->id)->first()->cursa->materia;
            }
            $daykey = $per->dia->literal;
            $periodo = [
                'id'            => $per->id,
                'nro'           => $per->nro,
                'id_materia'    => $materia?$materia->id:null,
                'literal'       => $materia?$materia->nombre:null
            ];
            if(!Utils::inMultiarray($daykey, $res, 'dia')) {
                array_push($res, [
                    'dia'       => $daykey,
                    'periodos'  => []
                ]);
            }
            array_push($res[count($res) - 1]['periodos'], $periodo);
            return $res;
        }, []);
        return Response::OKWhitToken(
            'todo ok',
            'Horario obtenido satisfactoriamente',
            Utils::generateToken($admin->id, $admin->ci),
            $response
        );
    }
    public static function setCourserSchedule($admin, $data, $id)
    {
        $fields = ['materias', 'periodos'];
        if (!Utils::validateData($data, $fields)) {
            return Response::BadRequest(Utils::implodeFields($fields));
        }
        $yearId = Utils::getCurrentYear()->id;
        foreach ($data['materias'] as $idmat => $idprof) {
            $cursa = Cursa::where([
                ['materia_id', '=', $idmat],
                ['curso_id', '=', $id]
            ])->first();
            $instruye = Instruye::create([
                'cursa_id'      => $cursa->id,
                'profesor_id'   => $idprof,
                'gestion_id'    => $yearId
            ]);
        }
        foreach ($data['periodos'] as $idper => $idmat) {
            $cursa = Cursa::where([
                ['materia_id', '=', $idmat],
                ['curso_id', '=', $id]
            ])->first();
            $horario = Horario::create([
                'cursa_id'      => $cursa->id,
                'periodo_id'    => $idper,
                'gestion_id'    => $yearId
            ]);
        }
        return Response::OKWhitToken(
            'todo ok',
            'Horario guardado.',
            Utils::generateToken($admin->id, $admin->ci),
            null
        );
    }
}