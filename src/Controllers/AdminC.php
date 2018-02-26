<?php
namespace Controllers;

use \Models\Admin;
use \Models\Curso;
use \Models\Utils;
use \Models\Response;
use \Models\Estudiante;
use \Models\Apoderado;
use \Models\Inscribe;

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
            return Response::OKWhitToken(
                'Login correcto',
                $saludo.$admin->nombres,
                $tokenstr,
                $admin
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
}
