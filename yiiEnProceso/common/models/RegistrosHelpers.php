<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use yii;
/**
 * Description of RegistrosHelpers
 *
 * @author Emir89
 */
class RegistrosHelpers {
    
    //metodo que determina si tiene o no perfil y si lo necesita
    public static function userTiene($modelo_nombre)
    {
       $conexion = \Yii::$app->db;
       $userid = Yii::$app->user->identity->id;
       $sql = "SELECT id FROM $modelo_nombre WHERE user_id=:userid";
       $comando = $conexion->createCommand($sql);
       $comando->bindValue(":userid", $userid);
       $resultado = $comando->queryOne();
       if ($resultado == null) {
            return false;
       } else {
       return $resultado['id'];
       } 
    }
}
