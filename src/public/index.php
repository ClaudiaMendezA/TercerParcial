<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Slim;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['db']['dbms'] = $dbms;
$config['db']['host'] = $host;
$config['db']['user'] = $user;
$config['db']['pass'] = $pass;
$config['db']['dbname'] = $db;
 
$app = new \Slim\App([
    'settings' => $config
]);

$container = $app->getContainer();

$container['db'] = function($c){
    $bd = $c['settings']['db'];
    $pdo = new PDO("{$bd['dbms']}:host={$bd['host']};dbname={$bd['dbname']};charset=utf8", 
        $bd['user'], $bd['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$app->get('/hola/{nombre}', function (Request $request, Response $response, array $args) {
    $nombre = $args['nombre'];
    $arr = array("sucess"=>true, "metodo"=>"GET", "message"=>"Hola, {$nombre}");
    $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
    $newResponse = $response->withHeader(
        'Content-Type', 'application/json; charset=UTF-8'
    );

    return $newResponse;
});


$app->get('/alumno/{id}/calificaciones', function (Request $request, Response $response, array $args) {
    
    $conn = $this->db;
    $db = $GLOBALS['db'];
    $status_http = 200;

    $data = $request->getQueryParams();
    $arrJ = str_replace("'", "\"", $data);
    if(!isset($arrJ['token'])){ $arrJ['token'] = ""; }
    $token = $arrJ['token'];
    $arr = array();

    if(!empty($token)){
       
        $arrT = validaToken($token, $db, $conn); 
        if($arrT['valido']){

            $idAlumno = $args['id'];

            $sql = "
SELECT 
    ma.nombre as nombreMateria, ma.clave_mat as claveMateria, ev.calificacion as calificacion
FROM
        {$db}.evaluaciones AS ev
    INNER JOIN
        {$db}.materias AS ma ON ev.clave_mat = ma.clave_mat
WHERE
    ev.clave_alu = {$idAlumno}
";
            $respuesta = query($sql, $conn); 
            $arr['success'] = true;
            $arr['data'] = $respuesta;
        }else{
            $arr = array("errors"=> array("code"=>401, "detail" => "Token invalido"));
            $status_http = 401;
        }
        
        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }

        return $newResponse;
    }
});

$app->post('/alumno/{id}/calificaciones', function (Request $request, Response $response, array $args) {
    $conn = $this->db;
    $db = $GLOBALS['db'];
    $status_http = 200;

    $data = $request->getParsedBody();
    $arrJ = str_replace("'", "\"", $data);
    if(!isset($arrJ['token'])){ $arrJ['token'] = ""; }
    $token = $arrJ['token'];
    $arr = array();

    if(!empty($token)){
        
        $arrT = validaToken($token, $db, $conn); 
        if($arrT['valido']){

            $idAlumno = $args['id'];
            $fmod = date("Y-m-d H:i:s");

            $sql = "
INSERT INTO
    {$db}.evaluaciones
    (clave_alu, id_curso, id_salon, clave_mat, calificacion, fmod)
VALUES
    ('{$idAlumno}', '{$data['id_curso']}', '{$data['id_salon']}', '{$data['clave_mat']}',
    '{$data['calificacion']}', '{$fmod}');
";
            $respuesta = $conn->prepare($sql);
            $respuesta->execute();
            $arr['success'] = true;
        }else{
            $arr = array("errors"=> array("code"=>401, "detail" => "Token invalido"));
            $status_http = 401;
        }
        
        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }

        return $newResponse;
    }
});

$app->put('/alumno/{id}/calificaciones', function (Request $request, Response $response, array $args) {
    
    $conn = $this->db;
    $db = $GLOBALS['db'];
    $status_http = 200;

    
    $data = $request->getParsedBody();
    $arrJ = str_replace("'", "\"", $data);
    if(!isset($arrJ['token'])){ $arrJ['token'] = ""; }
    $token = $arrJ['token'];
    $arr = array();

    if(!empty($token)){
        
        $arrT = validaToken($token, $db, $conn); 
        if($arrT['valido']){
            
            $idAlumno = $args['id'];

 
            $sql = " 
UPDATE 
        {$db}.evaluaciones as ev
SET ev.calificacion = '{$data['calificacion']}'; 
WHERE
    ev.clave_alu = {$idAlumno}
    ev.calve_mat = {$data['clave_mat']}
";

 $respuesta = $conn->prepare($sql);
            $respuesta->execute();
            $arr['success'] = true;
        }else{
            $arr = array("errors"=> array("code"=>401, "detail" => "Token invalido"));
            $status_http = 401;
        }
        
        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }

        return $newResponse;
    }
});

$app->delete('/alumno/{id}/calificaciones', function (Request $request, Response $response, array $args) {
    
    $conn = $this->db;
    $db = $GLOBALS['db'];
    $status_http = 200;

    $data = $request->getParsedBody();
    $arrJ = str_replace("'", "\"", $data);
    if(!isset($arrJ['token'])){ $arrJ['token'] = ""; }
    $token = $arrJ['token'];
    $arr = array();

    if(!empty($token)){
        // token existe
        $arrT = validaToken($token, $db, $conn); 
        if($arrT['valido']){
 
            $idAlumno = $args['id'];

            $sql = "
DELETE FROM  {$db}.evaluaciones
WHERE clave_alu = '{$idAlumno}'";

            $respuesta = $conn->prepare($sql);
            $respuesta->execute();
            $arr['success'] = true;
        } else {
            $arr = array("errors"=> array("code"=>401, "detail" => "Token invalido"));
            $status_http = 401;
        }
        
        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }

        return $newResponse;
    }
});


$app->post('/login', function (Request $request, Response $response, array $args) {
    $conn = $this->db;
    $db = $GLOBALS['db'];

    $data = $request->getParsedBody();
    $arrJ = str_replace("'", "\"", $data);
    $us = $arrJ['usuario'];
    $pass = $arrJ['passwd'];
    $status_http = 200;

    if(!empty($us) && !empty($pass)){
        $sql = "SELECT * FROM     
        (SELECT id, usuario, password, tipo_usuario_id, status FROM {$db}.usuarios 
        WHERE usuario = '{$us}' AND status = 1) u 
        LEFT JOIN (SELECT * FROM {$db}.tokens WHERE active = 1) t 
        ON(u.id = t.usuario_id)";
        $arr = query($sql, $conn);
        if(count($arr) == 1){
            $arru = $arr[0];
            if(password_verify($pass, $arru['password'])){
                if(empty($arru['token'])){
                    $factual = strtotime(date('Y-m-d H:i:s', time()));
                    $arru['expires'] = date("Y-m-d h:i:s", strtotime("+1 month", $factual));
                    $arru['token'] = bin2hex(random_bytes(32));
                    $d = [
                        'token' => $arru['token'],
                        'usuario_id' => $arru['id'],
                        'expires' => $arru['expires'],
                        'active' => 1,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                    $sql = "INSERT INTO {$db}.tokens SET token=:token, usuario_id=:usuario_id, 
                    expires=:expires, active=:active, created_at=:created_at, updated_at=:updated_at ";
                    $st = $conn->prepare($sql);
                    $st->execute($d);
                }
                $arr = array('token' => $arru['token'], "expire_time" => $arru['expires']);
            }else{
                $arr = array('errors' => array("code" => "226", "detail"=>"Error de contraseña"));
                $status_http = 401;
            }
        }else{
            $arr = array('errors' => array("code" => "228", "detail"=>"Usuario no localizado"));
            $status_http = 401;
        }

        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)
            ->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }
        return $newResponse;
    }
});

$app->post('/registro', function (Request $request, Response $response, array $args) {
    $conn = $this->db;
    $db = $GLOBALS['db'];

    $data = $request->getParsedBody();
    $arrJ = str_replace("'", "\"", $data);
    $status_http = 200;

    if(!empty($us = $arrJ['usuario']) && !empty($us = $arrJ['email'])){
        $sql = "SELECT id, usuario, password, email, tipo_usuario_id, status 
        FROM {$db}.usuarios 
        WHERE usuario = '{$arrJ['usuario']}' OR email = '{$arrJ['email']}'";
        $arr = query($sql, $conn);
        if(count($arr) < 1){
            $factual = strtotime(date('Y-m-d H:i:s', time()));
            $arru['expires'] = date("Y-m-d h:i:s", strtotime("+1 month", $factual));
            $arru['token'] = bin2hex(random_bytes(32));
            $d = [
                'nombres' => $arrJ['nombres'],
                'ap_paterno' => $arrJ['ap_paterno'],
                'ap_materno' => $arrJ['ap_materno'],
                'usuario' => $arrJ['usuario'],
                'password' => password_hash($arrJ['password'], PASSWORD_DEFAULT),
                'email' => $arrJ['email'],
                'tipo_usuario_id' => 3,
                'estado_id' => $arrJ['estado'],
                'dependencia_id' => $arrJ['dependencia'],
                'codigo_verificacion' => sha1(md5("{$factual}{$arrJ['usuario']}")),
                'token' => $arru['token'],
                'token_expira' => $arru['expires'],
                'status' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];
            $sql = "INSERT INTO {$db}.usuarios SET nombres=:nombres, ap_paterno=:ap_paterno,
            ap_materno=:ap_materno, usuario=:usuario, password=:password, email=:email, 
            tipo_usuario_id=:tipo_usuario_id, estado_id=:estado_id, dependencia_id=:dependencia_id,
            codigo_verificacion=:codigo_verificacion, token=:token, 
            token_expira=:token_expira, status=:status, created_at=:created_at, updated_at=:updated_at ";
            $st = $conn->prepare($sql);
            $st->execute($d);
            $error = $conn->errorInfo();
            if(intval($error[0]) != 0){
                $arr = array('errors' => array("code" => "230", 
                "detail"=>"Error al insertar Usuario, {$error[2]}"));
                $status_http = 401;
            }else{
                $arr = array('success' => true, "detail"=>"Usuario insertado correctamente");
            }
        }else{
            $arr = array('errors' => array("code" => "228", "detail"=>"Usuario o email ya existe"));
            $status_http = 401;
        }

        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)
            ->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }
        return $newResponse;
    }
});

$app->get('/alumnos/{matricula}', function (Request $request, Response $response, array $args) {
    
    $conn = $this->db;
    $db = $GLOBALS['db'];
    $matricula = $args['matricula'];
    $status_http = 200;
    
    $data = $request->getQueryParams();
    $arrJ = str_replace("'", "\"", $data);
    if(!isset($arrJ['token'])){ $arrJ['token'] = ""; }
    $token = $arrJ['token'];
    $arr = array();
    
    if(!empty($token)){
        $arrT = validaToken($token, $db, $conn);
        if($arrT['valido']){
            $sql = "SELECT * FROM {$db}.alumnos WHERE clave_alu = '{$matricula}' ORDER BY 3, 4, 5";
            $arrAlu = query($sql, $conn);
            $arr['sucess'] = true;
            $arr['data'] = $arrAlu;
        }else{
            $arr = array("errors"=> array("code"=>401, "detail" => "Token invalido"));
            $status_http = 401;
        }
        
        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)
            ->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }

        return $newResponse;
    }
});

$app->post('/alumnos', function (Request $request, Response $response, array $args) {
    
    $conn = $this->db;
    $db = $GLOBALS['db'];
    $status_http = 200;

    $data = $request->getParsedBody();
    $arrJ = str_replace("'", "\"", $data);
    if(!isset($arrJ['token'])){ $arrJ['token'] = ""; }
    $token = $arrJ['token'];
    $arr = array();
    
    if(!empty($token)){
        $arrT = validaToken($token, $db, $conn);
        if($arrT['valido']){
            $sql = "SELECT * FROM {$db}.alumnos WHERE clave_alu = '{$arrJ['clave_alu']}'";
            $arrAlu = query($sql, $conn);
            if(count($arrAlu)>= 1){
                $arr = array("errors"=> array("code"=>228, "detail" => "Matricula ya existe"));
                $status_http = 401;
            }else{
                $validos = 1; $arr_empty = array();
                
                foreach($GLOBALS['arr_campos_alumno_nn'] as $k=>$campo){
                    if(empty($arrJ[$campo])){
                        $arr_empty[$campo] = "No puede ser nulo";
                        $validos = 0;
                    }
                }
                if($validos){
                    $d = array();
                    $sql = "INSERT INTO {$db}.alumnos SET ";
                    foreach($GLOBALS['arr_campos_alumno'] as $k=>$campo){
                        if(isset($arrJ[$campo])){
                            $d[$campo] = $arrJ[$campo];
                            $sql .= "{$campo}=:{$campo}, ";
                        }
                    }
                    $d['fedita'] = date("Y-m-d H:i:s");
                    $sql .= "fedita=:fedita ";
                    $st = $conn->prepare($sql);
                    $st->execute($d);
                    $error = $conn->errorInfo();
                    if(intval($error[0]) != 0){
                        $arr = array('errors' => array("code" => "230", 
                        "detail"=>"Error al insertar Alumno, {$error[2]}"));
                        $status_http = 401;
                    }else{
                        $arr = array('success' => true, "detail"=>"Alumno insertado correctamente");
                    }
                }else{
                    $arr = array("errors"=> array("code"=>221, "detail" => $arr_empty));
                    $status_http = 401;
                } #if($validos)
            } #if(count($arrAlu)
        }else{
            $arr = array("errors"=> array("code"=>401, "detail" => "Token invalido"));
            $status_http = 401;
        }
        
        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)
            ->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }

        return $newResponse;
    }
});

$app->put('/alumnos/{matricula}', function (Request $request, Response $response, array $args) {
    
    $conn = $this->db;
    $db = $GLOBALS['db'];
    $status_http = 200;

    $matricula = $args['matricula'];
    $data = $request->getParsedBody();
    $arrJ = str_replace("'", "\"", $data);
    if(!isset($arrJ['token'])){ $arrJ['token'] = ""; }
    $token = $arrJ['token'];
    $arr = array();
    
    if(!empty($token)){
        $arrT = validaToken($token, $db, $conn);
        if($arrT['valido']){
            $sql = "SELECT * FROM {$db}.alumnos WHERE clave_alu = '{$matricula}'";
            $arrAlu = query($sql, $conn);
            if(count($arrAlu)!= 1){
                $arr = array("errors"=> array("code"=>230, "detail" => "Matricula no existe"));
                $status_http = 401;
            }else{
                $validos = 1; $arr_empty = array();
                
                foreach($GLOBALS['arr_campos_alumno_nn'] as $k=>$campo){
                    if(empty($arrJ[$campo])){
                        $arr_empty[$campo] = "No puede ser nulo";
                        $validos = 0;
                    }
                }
                if($validos){
                    $d = array();
                    $sql = "UPDATE {$db}.alumnos SET ";
                    foreach($GLOBALS['arr_campos_alumno'] as $k=>$campo){
                        if(isset($arrJ[$campo])){
                            $d[$campo] = $arrJ[$campo];
                            $sql .= "{$campo}=:{$campo}, ";
                        }
                    }
                    $d['fedita'] = date("Y-m-d H:i:s");
                    $d['clave_alu'] = $matricula;
                    $sql .= "fedita=:fedita ";
                    $sql .= " WHERE clave_alu=:clave_alu";
                    $st = $conn->prepare($sql);
                    $st->execute($d);
                    $error = $conn->errorInfo();
                    if(intval($error[0]) != 0){
                        $arr = array('errors' => array("code" => "230", 
                        "detail"=>"Error al editar Alumno, {$error[2]}"));
                        $status_http = 401;
                    }else{
                        $arr = array('success' => true, "detail"=>"Alumno editado correctamente");
                    }
                }else{
                    $arr = array("errors"=> array("code"=>221, "detail" => $arr_empty));
                    $status_http = 401;
                } #if($validos)
            } #if(count($arrAlu)
        }else{
            $arr = array("errors"=> array("code"=>401, "detail" => "Token invalido"));
            $status_http = 401;
        }
        
        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)
            ->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }

        return $newResponse;
    }
});

$app->delete('/alumnos/{matricula}', function (Request $request, Response $response, array $args) {
    
    $conn = $this->db;
    $db = $GLOBALS['db'];
    $status_http = 200;

    $matricula = $args['matricula'];
    $data = $request->getParsedBody();
    $arrJ = str_replace("'", "\"", $data);
    if(!isset($arrJ['token'])){ $arrJ['token'] = ""; }
    $token = $arrJ['token'];
    $arr = array();
    
    if(!empty($token)){
        $arrT = validaToken($token, $db, $conn);
        if($arrT['valido']){
            $sql = "SELECT * FROM {$db}.alumnos WHERE clave_alu = '{$matricula}'";
            $arrAlu = query($sql, $conn);
            if(count($arrAlu)!= 1){
                $arr = array("errors"=> array("code"=>230, "detail" => "Matricula no existe"));
                $status_http = 401;
            }else{
                $d = array();
                $d['clave_alu'] = $matricula;
                $sql = "DELETE FROM  {$db}.alumnos WHERE clave_alu=:clave_alu ";
                $st = $conn->prepare($sql);
                $st->execute($d);
                $error = $conn->errorInfo();
                if(intval($error[0]) != 0){
                    $arr = array('errors' => array("code" => "230", 
                    "detail"=>"Error al eliminar Alumno, {$error[2]}"));
                    $status_http = 401;
                }else{
                    $arr = array('success' => true, "detail"=>"Alumno eliminado correctamente");
                }
                
            } #if(count($arrAlu)
        }else{
            $arr = array("errors"=> array("code"=>401, "detail" => "Token invalido"));
            $status_http = 401;
        }
        
        $response->getBody()->write(json_encode($arr,JSON_UNESCAPED_UNICODE));
        $newResponse = $response->withHeader(
            'Content-Type', 'application/json; charset=UTF-8'
        );

        if($status_http != 200){
            $newResponse = $response->withStatus($status_http)
            ->withHeader(
                'Content-Type', 'application/json; charset=UTF-8'
            );
        }

        return $newResponse;
    }
});

$app->run();