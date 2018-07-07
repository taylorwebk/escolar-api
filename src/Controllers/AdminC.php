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

class AdminC
{
    public static function getStats($admin) {
        $currentYear = Utils::getCurrentYear();
        $currentBim = Utils::getCurrentBimester();
        $resp = [
            'inscritos'     => Inscribe::where('gestion_id', $currentYear->id)->count(),
            'docentes'      => Profesor::count(),
            'trabajos'      => Trabajo::where('bimestre_id', $currentBim->id)->count(),
            'gestion'       => $currentYear->nro,
            'bimestre'      => $currentBim->nro
        ];
        return Response::OKWhitToken(
            'todo OK',
            'ok',
            Utils::generateToken($admin->id, $admin->ci),
            $resp
        );
    }
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
    public static function updateProfile($admin, $data) {
        $fields = ['cel', 'password'];
        if (!Utils::validateData($data, $fields)) {
            return Response::BadRequest(Utils::implodeFields($fields));
        }
        $admin->cel = $data['cel'];
        $admin->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $admin->save();
        return Response::OKWhitToken(
            'Todo OK',
            'Datos actualizados',
            Utils::generateToken($admin->id, $admin->ci),
            $admin
        );
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
            $nroEst = Estudiante::count();
            $nroProf = Profesor::count();
            $arrayResponse = [
                'gestion' => Utils::getCurrentYear()->nro,
                'bimestre' => Utils::getCurrentBimester()->id,
                'materias' => Materia::select('id', 'nombre')->get(),
                'nro_est' => $nroEst,
                'nro_prof' => $nroProf,
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
            'dir'       => $data['dir'],
            'password'  => password_hash($data['ci'], PASSWORD_DEFAULT)
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
        $currYearId = Utils::getCurrentYear()->id;
        if (!$curse) {
            return Response::BadRequest('No existe el curso con ID:'.$id);
        }
        $response = [];
        $response['id_curso'] = $curse->id;
        $response['materias'] = $curse->cursas->map(function ($item) use ($currYearId) {
            $profs = $item->materia->profesores->map(function($prof) use ($currYearId) {
                return [
                    'id'    => $prof->id,
                    'nombre'=> $prof->nombres . ' ' . $prof->appat
                ];
            });
            return [
                'id_materia'    => $item->materia->id,
                'literal'       => $item->materia->nombre,
                'profesores'    => $profs,
                'profesor'      => @ $item->instruyes->where('gestion_id', $currYearId)->last()->profesor->id
            ];
        });
        $mats = Cursa::where('curso_id', $id)->get();
        $idscursa = $mats->map(function($mat) {
            return $mat->id;
        });
        $horarios = Horario::whereIn('cursa_id', $idscursa)->get();
        $response['horario'] = $pers->reduce(function ($res, $per) use ($horarios, $currYearId) {
            $materia = null;
            if ($horarios->contains('periodo_id', $per->id)) {
                @ $materia = $horarios->where('periodo_id', $per->id)->where('gestion_id', $currYearId)->last()->cursa->materia;
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
            $instruye = Instruye::where([
                ['cursa_id', '=', $cursa->id],
                ['gestion_id', '=', $yearId]
            ])->first();
            if ($instruye) {
                $instruye->profesor_id = $idprof;
                $instruye->save();
            } else {
                Instruye::create([
                    'cursa_id'      => $cursa->id,
                    'profesor_id'   => $idprof,
                    'gestion_id'    => $yearId
                ]);
            }
        }
        Horario::whereIn('cursa_id', Cursa::select('id')->where('curso_id', $id)->get())->where('gestion_id', $yearId)->delete();
        foreach ($data['periodos'] as $idper => $idmat) {
            $cursa = Cursa::where([
                ['materia_id', '=', $idmat],
                ['curso_id', '=', $id]
            ])->first();
            Horario::create([
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
    public static function getTeachers($admin) {
        $teachers = Profesor::with('materias')->get();
        $teachersres = $teachers->map(function($teacher) {
            $teach = [];
            $teach['id'] = $teacher->id;
            $teach['nombres'] = $teacher->nombres;
            $teach['appat'] = $teacher->appat;
            $teach['apmat'] = $teacher->apmat;
            $teach['ci'] = $teacher->ci;
            $teach['dir'] = $teacher->dir;
            $teach['materias'] = $teacher->materias->map(function($materia) {
                return [
                    'id'        => $materia->id,
                    'nombre'    => $materia->nombre,
                    'estado'    => $materia->pivot->estado
                ];
            });
            return $teach;
        });
        return Response::OKWhitToken(
            'todo ok',
            'Lista de docentes cargado',
            Utils::generateToken($admin->id, $admin->ci),
            $teachersres
        );
    }
    public static function updateTeacher($admin, $data, $id) {
        $fields = ['nombres', 'appat', 'apmat', 'ci', 'dir', 'materias', 'password'];
        if (!Utils::validateData($data, $fields)) {
            return Response::BadRequest(Utils::implodeFields($fields));
        }
        $teacher = Profesor::find($id);
        if (!$teacher) {
            return Response::BadRequest('No existe el profesor con ID: ' . $id);
        }
        $newpass = $data['password'] == ''? $teacher->password: password_hash($data['password'], PASSWORD_DEFAULT);
        $teacher->nombres = $data['nombres'];
        $teacher->appat = $data['appat'];
        $teacher->apmat = $data['apmat'];
        $teacher->ci = $data['ci'];
        $teacher->dir = $data['dir'];
        $teacher->password = $newpass;
        $teacher->save();
        $mats = array_reduce($data['materias'], function($res, $mat) {
            $res[$mat] = ['estado' => 1];
            return $res;
        }, []);
        $teacher->materias()->sync($mats);
        return Response::OKWhitToken(
            'Actualización correcta',
            'Docente: ' . $teacher->nombres . ' actualizado correctamente.',
            Utils::generateToken($admin->id, $admin->ci),
            null
        );
    }
    public static function getStudents($admin) {
        $yearId = Utils::getCurrentYear()->id;
        $students = Estudiante::select('id', 'nombres', 'appat', 'apmat', 'ci', 'username')
                    ->whereIn('id', Inscribe::select('estudiante_id')->where('gestion_id', $yearId)->get())
                    ->get();
        return Response::OKWhitToken(
            'Todo OK',
            'Lista de estudiantes obtenida correctamente.',
            Utils::generateToken($admin->id, $admin->ci),
            $students
        );
    }
    public static function getStudent($admin, $id) {
        $yearId = Utils::getCurrentYear()->id;
        $student = Estudiante::where('id', $id)->with([
            'apoderado',
            'mainInscribe'
        ])->first();
        $insc = $student->mainInscribe->first();
        $response = [
            'id'        => $student->id,
            'ci'        => $student->ci,
            'nombres'   => $student->nombres,
            'appat'     => $student->appat,
            'apmat'     => $student->apmat,
            'username'  => $student->username,
            'dir'       => $student->dir,
            'nrocel'    => $student->nrocel,
            'apoderado' => $student->apoderado,
            'fecha'  => $insc->fecha,
            'curso'     => $insc->curso
        ];
        return Response::OKWhitToken(
            'Todo OK',
            'Datos del estudiante obtenidos correctamente.',
            Utils::generateToken($admin->id, $admin->ci),
            $response
        );
    }
    public static function updateStudent($admin, $data, $id) {
        $fields = ['nombres', 'appat', 'apmat', 'ci', 'dir', 'nrocel', 'aponombre', 'aponro', 'apopar'];
        if (!Utils::validateData($data, $fields)) {
            return Response::BadRequest(Utils::implodeFields($fields));
        }
        $student = Estudiante::find($id);
        $apo = Apoderado::where('id', $student->apoderado_id)->first();
        $student->nombres = $data['nombres'];
        $student->appat = $data['appat'];
        $student->apmat = $data['apmat'];
        $student->ci = $data['ci'];
        $student->dir = $data['dir'];
        $student->nrocel = $data['nrocel'];
        $student->save();
        $apo->nombre = $data['aponombre'];
        $apo->nroref = $data['aponro'];
        $apo->parentesco = $data['apopar'];
        $apo->save();
        return Response::OKWhitToken(
            'Todo OK',
            'Datos del estudiante actualizados.',
            Utils::generateToken($admin->id, $admin->ci),
            null
        );
    }
    public static function newNotice($admin, $data) {
        $fields = ['titulo', 'cont'];
        if (!Utils::validateData($data, $fields)) {
            return Response::BadRequest(Utils::implodeFields($fields));
        }
        $comunicado = Comunicado::create([
            'admin_id'  => $admin->id,
            'fecha'     => date('Y-m-d'),
            'hora'      => date('H:i:s'),
            'titulo'    => $data['titulo'],
            'cont'      => $data['cont'],
            'remitente' => $admin->nombres
        ]);
        return Response::OKWhitToken(
            'todo OK',
            'Comunicado creado',
            Utils::generateToken($admin->id, $admin->ci),
            null
        );
    }
    public static function getNotices() {
        return Response::OK('OK', 'todo OK', Comunicado::orderBy('id', 'desc')->get());
    }
    public static function deleteNotice($admin, $id) {
        Comunicado::find($id)->delete();
        return Response::OKWhitToken('ok', 'todo ok', Utils::generateToken($admin->id, $admin->ci), null);
    }
    public static function newBimester($admin) {
        $bim = Utils::getCurrentBimester();
        $year = Utils::getCurrentYear();
        if ($bim->nro == 4) {
            $year->bimestres()->updateExistingPivot($bim->id, ['active' => 0]);
            $newYear = Gestion::create([
                'nro'   => $year->nro + 1
            ]);
            $newYear->bimestres()->attach(1, ['active' => 1]);
        } else {
            $year->bimestres()->updateExistingPivot($bim->id, ['active' => 0]);
            $year->bimestres()->attach($bim->id+1, ['active' => 1]);
        }
        return Response::OKWhitToken(
            'todo OK',
            'ok',
            Utils::generateToken($admin->id, $admin->ci),
            null
        );
    }
}