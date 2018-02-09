<?php
namespace Models;
class Response
{
  public static function OK($devmsg, $usrmsg, $content)
  {
    $response['code'] = 200;
    $response['devmsg'] = $devmsg;
    $response['usrmsg'] = $usrmsg;
    $response['content'] = $content;
    return $response;
  }
  public static function OKWhitToken($devmsg, $usrmsg, $token, $content)
  {
    $response['code'] = 200;
    $response['devmsg'] = $devmsg;
    $response['usrmsg'] = $usrmsg;
    $response['content'] = [
      'token' => $token,
      'data'  => $content
    ];
    return $response;
  }
  public static function Unauthorized($devmsg, $usrmsg)
  {
    $response['code'] = 401;
    $response['devmsg'] = $devmsg;
    $response['usrmsg'] = $usrmsg;
    return $response;
  }
  public static function BadRequest($devmsg)
  {
    $response['code'] = 400;
    $response['devmsg'] = $devmsg;
    $response['usrmsg'] = 'Error desconocido, por favor contacte al desarrollador, codigo: 400';
    return $response;
  }
  public static function InternarServerError($devmsg) {
    $response['code'] = 500;
    $response['devmsg'] = $devmsg;
    $response['usrmsg'] = 'Error desconocido, por favor contacte al desarrollador, codigo: 500';
    return $response;
  }
}
