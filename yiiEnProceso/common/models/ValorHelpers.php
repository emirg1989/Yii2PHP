<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use yii;
use backend\models\Rol;
use backend\models\Estado;
use backend\models\TipoUsuario;
use common\models\User;

/**
 * Description of ValorHelpers
 *
 * @author Emir89
 */
class ValorHelpers {
    
    //retorna true o false segun si coincide o no el rol.
    //se usa if(ValorHelpers::rolCoincide('Admin')){}
    public static function rolCoincide($rol_nombre)
    {
        //cuando escribimos rol lo que se hace en verdad es un getRol()
        $userTieneRolNombre = \Yii::$app->user->identity->rol->rol_nombre;
        //si el user tiene rol nombre es igual a rol_nombre devuelve true sino false
        return $userTieneRolNombre == $rol_nombre ? true : false;
    }
    
    
    public static function getUsersRolValor($userId=null)
    {
        if($userId == null){
            $usersRolValor = \Yii::$app->user->identity->rol->rol_valor;
            return isset($usersRolValor) ? $usersRolValor : false;
        }else{
            
            $user = User::findOne($userId); //esta linea se traduce como Select id FROM User WHERE id = $userId;
            $usersRolValor = $user->rol_id;
            return isset($usersRolValor) ? $usersRolValor : false;
        }
    }
    
    //este metodo en base al nombre del rol nos devuelve el valor de dicho rol
    public static function getRolValor($rol_nombre)
    {
        $rol = Rol::find('rol_valor')//se utiliza find en lugar de findOne por el hecho de que findOne es un atajo para buscar claves primarias
                ->where(['rol_nombre' => $rol_nombre])
                ->one();
        return isset($rol->rol_valor) ? $rol->rol_valor : false;
    }
    
    public static function esRolNombreValido($rol_nombre)
    {
        $rol = Rol::find('rol_nombre')
        ->where(['rol_nombre' => $rol_nombre])
        ->one();
        return isset($rol->rol_nombre) ? true : false;
    }
    
    public static function estadoCoincide($estado_nombre)
    {
        $userTieneEstadoName = Yii::$app->user->identity->estado->estado_nombre;
        return $userTieneEstadoName == $estado_nombre ? true : false;
    }
    
    public static function getEstadoId($estado_nombre)
    {
        $estado = Estado::find('id')
        ->where(['estado_nombre' => $estado_nombre])
        ->one();
        return isset($estado->id) ? $estado->id : false;
    }
    
    public static function tipoUsuarioCoincide($tipo_usuario_nombre)
    {
        $userTieneTipoUsurioName = Yii::$app->user->identity
        ->tipoUsuario->tipo_usuario_nombre;
        return $userTieneTipoUsurioName == $tipo_usuario_nombre ? true : false;
    }
}
