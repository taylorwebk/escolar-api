<?php
namespace Models;
use \Firebase\JWT\JWT;
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
    public static function implodeFields($fields) {
        return 'No se reconocen uno o varios de los campos: '. implode(', ', $fields);
    }
}
