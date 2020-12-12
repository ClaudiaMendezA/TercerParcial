<?php
function ia($arr){
    $r = "";
    $r = "<pre>". print_r($arr, true) . "</pre>";
    return $r;
}

function query($sql, $conn){
    $q = $conn->query($sql);
    $arr = $q->fetchAll();
    return $arr;
}

function validaToken($token, $bd, $conn){
    $arrT = array('valido' => false);
    $sql = "SELECT * FROM     
        (SELECT id, usuario, tipo_usuario_id, status FROM {$bd}.usuarios WHERE status = 1) u 
        JOIN (SELECT * FROM {$bd}.tokens WHERE active = 1 AND token = '{$token}') t 
        ON(u.id = t.usuario_id)";
    $arr = query($sql, $conn);
    if(count($arr) == 1){
        $arru = $arr[0];
        if(!empty($arru['id'])){
            $factual = strtotime(date('Y-m-d H:i:s', time()));
            $fexpira = strtotime($arru['expires']);
            if($fexpira >= $factual){
                $arrT['valido'] = true;
                $arrT['usuario_id'] = $arru['id'];
                $arrT['usuario'] = $arru['usuario'];
            }else{
                $d = [
                    'active' => 0,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'token' => $arru['token']
                ];
                $sql = "UPDATE {$bd}.tokens SET active=:active, updated_at=:updated_at 
                WHERE token=:token";
                $st = $conn->prepare($sql);
                $st->execute($d);
            }
        }
    }
    return $arrT;
}

?>