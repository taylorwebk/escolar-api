<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Controllers\AdminC;
use \Controllers\StudentC;
use \Controllers\TeacherC;
use \Controllers\ReportC;
use \Models\Utils;

$app->group('/admin', function () use ($app) {
    $app->get('/stats', function(Request $req, Response $res) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::getStats($admin);
        return $res->withJson($result);
    });
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
    $app->post('/comunicado', function(Request $req, Response $res) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::newNotice($admin, $req->getParsedBody());
        return $res->withJson($result);
    });
    $app->delete('/comunicado/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $admin = $req->getAttribute('admin');
        $result = AdminC::deleteNotice($admin, $args['id']);
        return $res->withJson($result);
    });
    $app->get('/bimestre', function(Request $req, Response $res) {
        $result = AdminC::newBimester($req->getAttribute('admin'));
        return $res->withJson($result);
    });
})->add(new \Middlewares\AdminAuth());

$app->group('/prof', function() use ($app) {
    $app->post('/trabajo', function(Request $req, Response $res) {
        $prof = $req->getAttribute('prof');
        $result = TeacherC::addHomeWork($prof, $req->getParsedBody());
        return $res->withJson($result);
    });
    $app->get('/curso/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $prof = $req->getAttribute('prof');
        $result = TeacherC::getCourseInfo($prof, $args['id']);
        return $res->withJson($result);
    });
    $app->post('/notas', function(Request $req, Response $res) {
        $prof = $req->getAttribute('prof');
        $result = TeacherC::setGrades($prof, $req->getParsedBody());
        return $res->withJson($result);
    });
})->add(new \Middlewares\TeacherAuth());

$app->group('/est', function() use ($app) {
    $app->get('/materias', function(Request $req, Response $res) {
        $student = $req->getAttribute('student');
        $result = StudentC::getSchedule($student);
        return $res->withJson($result);
    });
    $app->get('/materia/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $student = $req->getAttribute('student');
        $result = StudentC::getHomeworks($student, $args['id']);
        return $res->withJson($result);
    });
    $app->post('/password', function(Request $req, Response $res) {
        $student = $req->getAttribute('student');
        $result = StudentC::changePassword($student, $req->getParsedBody());
        return $res->withJson($result);
    });
})->add(new \Middlewares\StudentAuth());

$app->group('/reporteo', function() use ($app) {
    $app->get('/docente', function($req, $res) {
        $data = ReportC::teacherReport();
        $result = $this->view->render($res, 'Docentes.phtml', $data);
        return $result;
    });
    $app->get('/curso/{year:[0-9]+}/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $result = $this->view->render($res, 'Static.phtml');
        return $result;
    });
    $app->get('/materia/{year:[0-9]+}/{id:[0-9]+}/{mat:[0-9]+}', function(Request $req, Response $res, $args) {
        $result = $this->view->render($res, 'Static.phtml');
        return $result;
    });
    $app->get('/boletin/{year:[0-9]+}/{id:[0-9]+}/{nros:[0-9]+}', function(Request $req, Response $res, $args) {
        $result = $this->view->render($res, 'Static.phtml');
        return $result;
    });
    $app->get('/boletin/{year:[0-9]+}/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        $result = $this->view->render($res, 'Static.phtml');
        return $result;
    });
});
$app->group('/reporte', function() use ($app) {
    $app->get('/docente', function(Request $req, Response $res) {
        return Utils::toPdf($this->mpdf, $res, '/reporteo/docente');
    });
    $app->get('/curso/{year:[0-9]+}/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        return Utils::toPdf($this->mpdf, $res, '/reporteo/curso/'.$args['year'].'/'.$args['id']);
    });
    $app->get('/materia/{year:[0-9]+}/{id:[0-9]+}/{mat:[0-9]+}', function(Request $req, Response $res, $args) {
        return Utils::toPdf($this->mpdf, $res, '/reporteo/materia/'.$args['year'].'/'.$args['id']);
    });
    $app->get('/boletin/{year:[0-9]+}/{id:[0-9]+}/{nros:[0-9]+}', function(Request $req, Response $res, $args) {
        return Utils::toPdf($this->mpdf, $res, '/reporteo/boletin/'.$args['year'].'/'.$args['id'].'/'.$args['nros']);
    });
    $app->get('/boletin/{year:[0-9]+}/{id:[0-9]+}', function(Request $req, Response $res, $args) {
        return Utils::toPdf($this->mpdf, $res, '/reporteo/boletin/'.$args['year'].'/'.$args['id']);
    });
});

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
$app->post('/prof/login', function (Request $req, Response $res) {
    $result = TeacherC::Login($req->getParsedBody());
    return $res->withJson($result);
});
$app->get('/comunicados', function(Request $req, Response $res) {
    $result = AdminC::getNotices();
    return $res->withJson($result);
});