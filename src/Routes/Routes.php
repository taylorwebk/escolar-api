<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Controllers\AdminC;

$app->group('/admin', function () use ($app) {
    $app->get('/cursos', function (Request $req, Response $res) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::GetCourses($admin);
        return $res->withJson($result);
    });
    $app->post('/cursos', function (Request $req, Response $res)
    {
        $admin = $req->getAttribute('admin');
        $result = AdminC::EnableCourses($admin, $req->getParsedBody());
        return $res->withJson($result);
    });

})->add(new \Middlewares\AdminAuth($container['logger']));

$app->get('/bugsbunny', function (Request $req, Response $res)
{
    AdminC::Add('Bugs', 'Bunny', 'Bunny', 77777777, 1234567, 'bugsbunny');
    return $res->withJson('Hello World');
});
$app->post('/admin/login', function (Request $req, Response $res){
    $result = AdminC::Login($req->getParsedBody());
    return $res->withJson($result);
});