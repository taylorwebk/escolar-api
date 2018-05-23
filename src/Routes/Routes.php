<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Controllers\AdminC;
use \Controllers\StudentC;

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
    $app->post('/inscripcion', function (Request $req, Response $res) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::RegisterStudent($admin, $req->getParsedBody());
        return $res->withJson($result);
    });
    $app->post('/profesor', function (Request $req, Response $res) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::RegisterTeacher($admin, $req->getParsedBody());
        return $res->withJson($result);
    });
    $app->get('/horario/curso/{id:[0-9]+}', function (Request $req, Response $res, $args)
    {
        $admin = $req->getAttribute('admin');
        $result = AdminC::getCourseSchedule($admin, $args['id']);
        return $res->withJson($result);
    });
    $app->post('/horario/curso/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::setCourserSchedule($admin, $req->getParsedBody(), $args['id']);
        return $res->withJson($result);
    });
    $app->get('/profesores', function(Request $req, Response $res) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::getTeachers($admin);
        return $res->withJson($result);
    });
    $app->put('/profesor/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::updateTeacher($admin, $req->getParsedBody(), $args['id']);
        return $res->withJson($result);
    });
    $app->get('/estudiantes', function(Request $req, Response $res) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::getStudents($admin);
        return $res->withJson($result);
    });
    $app->get('/estudiante/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::getStudent($admin, $args['id']);
        return $res->withJson($result);
    });
    $app->put('/estudiante/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::updateStudent($admin, $req->getParsedBody(), $args['id']);
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
$app->post('/student/login', function (Request $req, Response $res) {
    $result = StudentC::Login($req->getParsedBody());
    return $res->withJson($result);
});