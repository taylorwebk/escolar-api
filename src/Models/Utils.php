<?php
namespace Models;
use \Lcobucci\JWT\Builder;
use \Lcobucci\JWT\ValidationData;
use \Lcobucci\JWT\Parser;
use \Models\Estudiante;
use \Models\Gestion;
class Utils
{
    /**
     * Generates a token based in a id and ci of user.
     */
    public static function generateToken($id, $ci) {
        $currentTime = time();
        $tokenstr = (new Builder())
            ->setIssuer(IP)
            ->setIssuedAt($currentTime)
            ->setExpiration($currentTime + 90 * 60)
            ->set('id', $id)
            ->set('ci', $ci)
            ->getToken();                    
        return (string) $tokenstr;
    }
    public static function generateStudentToken($id, $username) {
        $tokenstr = (new Builder())
            ->setIssuer(IP)
            ->setIssuedAt(time())
            ->set('id', $id)
            ->set('username', $username)
            ->getToken();
        return (string) $tokenstr;
    }
    public static function decodeToken($tokenstr) {
        $token = (new Parser())->parse((string)$tokenstr);
        $data = new ValidationData();
        $data->setIssuer(IP);
        if (!$token->isExpired()) {
            return [
                'id' => $token->getClaim('id'),
                'ci' => $token->getClaim('ci')
            ];
        }
        return false;            
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
    public static function getCurrentBimester() {
        $years = Gestion::all();
        $bim = null;
        $years->each(function($year) use(&$bim) {
            $year->bimestres->each(function ($bimestre) use(&$bim, $year) {
                if($bimestre->pivot->active == 1) {
                    $bim = $bimestre->nro;
                    return false;
                }
            });
        });
        return $bim;
    }
    public static function getCurrentYear() {
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
