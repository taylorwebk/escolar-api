<?php
namespace Models;
use \Firebase\JWT\JWT;
use \Models\Estudiante;
use \Models\Gestion;
class Utils
{
    /**
     * Generates a token based in a id and ci of user.
     */
    public static function generateToken($id, $ci) {
        $userdata = [
            'id' => $id,
            'ci' => $ci
        ];
        $data = [
            'exp' => time() + 60*100,
            'data' => $userdata
        ];
        $tokenstr = JWT::encode($data, PRIVATEKEY, 'HS512');
        return $tokenstr;
    }
    public static function generateStudentToken($id, $username) {
        $userdata = [
            'id'    => $id,
            'username'  => $username
        ];
        $data = [
            'data' => $userdata
        ];
        $tokenstr = JWT::encode($data, PRIVATEKEY, 'HS512');
        return $tokenstr;
    }
    public static function decodeToken($tokenstr) {
        try {
            $data = JWT::decode($tokenstr, PRIVATEKEY, ['HS512'])->data;
        } catch(\Firebase\JWT\ExpiredException $e) {
            return 'token';
        } catch(\Exception $e) {
            return $e->getMessage();
        }
        return $data;
    }
    public static function validateData($data, $fields)
    {
        foreach ($fields as $value) {
            if (! isset($data[$value])) {
                return false;
            }
        }
        return true;
    }
    public static function generateUsername($name, $appat) {
        $sname = strtolower(trim($name));
        $username = substr($sname, 0, 1);
        if(strlen($appat) > 0) {
            $sappat = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $appat);
            $username = $username . strtolower(trim($sappat));
        }
        $nro = Estudiante::where('username', 'LIKE', $username.'%')->count() + 1;
        $username = $username . $nro;
        return $username;
    }
    public static function getCurrentYear()
    {
        $years = Gestion::all();
        $ryear = null;
        $years->each(function($year) use(&$ryear) {
            $year->bimestres->each(function ($bimestre) use(&$ryear, $year) {
                if($bimestre->pivot->active == 1) {
                    $ryear = $year;
                    return false;
                }
            });
        });
        return $ryear;
    }
    public static function implodeFields($fields) {
        return 'No se reconocen uno o varios de los campos: '. implode(', ', $fields);
    }
    public static function inMultiarray($elem, $array)
    {
        $top = sizeof($array) - 1;
        $bottom = 0;
        while($bottom <= $top)
        {
            if ($array[$bottom]['curso'] == $elem) {
                return true;
            }        
            $bottom++;
        }        
        return false;
    }
}
