<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;
use yii\helpers\Security;
use backend\models\Rol;
use yii\helpers\ArrayHelper;
use backend\models\Estado;
use backend\models\TipoUsuario;
use frontend\models\Perfil;

/**
* User model
*
* @property integer $id
* @property string $username
* @property string $password_hash
* @property string $password_reset_token
* @property string $email
* @property string $auth_key
* @property integer $rol_id
* @property integer $estado_id
* @property integer $tipo_usuario_id
* @property integer $created_at
* @property integer $updated_at
* @property string $password write-only password
*/
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
               'class' => 'yii\behaviors\TimestampBehavior',
               'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['estado_id', 'default', 'value' => ValorHelpers::getEstadoId('Activo')],
            [['estado_id'],'in', 'range'=>array_keys($this->getEstadoLista())],
            ['rol_id', 'default', 'value' => 1],
            ['tipo_usuario_id', 'default', 'value' => 1],
            [['tipo_usuario_id'],'in', 'range'=>array_keys($this->getTipoUsuarioLista())],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique'],
        ];
    }
    public function attributeLabels()
    {
        return [
            /* Sus otras etiquetas de atributo */
            'rolNombre' => Yii::t('app', 'Rol'),
            'estadoNombre' => Yii::t('app', 'Estado'),
            'perfilId' => Yii::t('app', 'Perfil'),
            'perfilLink' => Yii::t('app', 'Perfil'),
            'userLink' => Yii::t('app', 'User'),
            'username' => Yii::t('app', 'User'),
            'tipoUsuarioNombre' => Yii::t('app', 'Tipo Usuario'),
            'tipoUsuarioId' => Yii::t('app', 'Tipo Usuario'),
            'userIdLink' => Yii::t('app', 'ID'),
        ];
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'estado_id' => ValorHelpers::getEstadoId('Activo')]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'estado_id' => ValorHelpers::getEstadoId('Activo')]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'estado_id' => ValorHelpers::getEstadoId('Activo'),
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
       if (empty($token)) {
            return false;
       }
       $expire = Yii::$app->params['user.passwordResetTokenExpire'];
       $parts = explode('_', $token);
       $timestamp = (int) end($parts);
       return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public function getPerfil()
    {
        return $this->hasOne(Perfil::className(), ['user_id' => 'id']);
    }
     /**
    * @getPerfilId
    *
    */
    public function getPerfilId()
    {
    return $this->perfil ? $this->perfil->id : 'ninguno';
    }
    
    /**
    * @getPerfilLink
    *
    */
    public function getPerfilLink()
    {
        $url = Url::to(['perfil/view', 'id'=>$this->perfilId]); 
        $opciones = [];
        return Html::a($this->perfil ? 'perfil' : 'ninguno', $url, $opciones);
    }
    
    //relacion get rol
    public function getRol()
    {
        return $this->hasOne(Rol::className(),['id' => 'rol_id']);
    }
    
    //get rol nombre
    public function getRolNombre()
    {
        return $this->rol ? $this->rol->rol_nombre : '- sin rol -';
    }
    
    //get lista de roles para lista desplegable
    public static function getRolLista()
    {
        $dropciones = Rol::find()->asArray()->all();
        return ArrayHelper::map($dropciones, 'id', 'rol_nombre');
    }
    
    //relacion get estado
    public function getEstado()
    {
        return $this->hasOne(Estado::className(), ['id' =>'estado_id']);
    }
    
    //get estado nombre
    public function getEstadoNombre()
    {
        return $this->estado ? $this->estado->estado_nombre : '- sin estado -';
    }
    
    //get lista de estados para lista desplegable
    public static function getEstadoLista()
    {
        $dropciones = Estado::find()->asArray()->all();
        return ArrayHelper::map($dropciones, 'id', 'estado_nombre');
    }
    public function getTipoUsuario()
    {
        return $this->hasOne(TipoUsuario::className(), ['id' => 'tipo_usuario_id']);
    }
    //get tipo usuario nombre
    public function getTipoUsuarioNombre()
    {
        return $this->tipoUsuario ? $this->tipoUsuario->tipo_usuario_nombre : '- sin tipo usuario -';
    }
    //get lista de tipos de usuario para lista desplegable
    public static function getTipoUsuarioLista()
    {
        $dropciones = TipoUsuario::find()->asArray()->all();
        return ArrayHelper::map($dropciones, 'id', 'tipo_usuario_nombre');
    }
    //get tipo usuario id
    public function getTipoUsuarioId()
    {
        return $this->tipoUsuario ? $this->tipoUsuario->id : 'ninguno';
    }
    
    /**
    * get user id Link
    *
    */
    public function getUserIdLink()
    {
        $url = Url::to(['user/update', 'id'=>$this->id]);
        $opciones = [];
        return Html::a($this->id, $url, $opciones);
    }
    /**
    * @getUserLink
    *
    */
    public function getUserLink()
    {
        $url = Url::to(['user/view', 'id'=>$this->id]);
        $opciones = [];
        return Html::a($this->username, $url, $opciones);
    }
}
