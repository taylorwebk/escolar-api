<?php
namespace Controllers;

use \Models\Admin;
use \Models\Curso;
use \Models\Utils;
use \Models\Response;

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
                'Ups... tuvimos un problema con el CI o password ingresados, por favor intente de nuevo'
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
}
