<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Models\Admin;

$app->get('/', function (Request $req, Response $res)
{
    Admin::create([]);
    return $res->withJson('Hello World');
});
