<?php

namespace app\controllers;

use app\common\logic\AdminLogic;

class LoginController extends BaseController
{
	public function beforeAction($action)
	{
		return parent::beforeAction($action);
	}
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'minLength' => 4,
                'maxLength' => 4
            ],
        ];
    }

	public function actionIndex()
	{
        $this->data['islogin'] = 0;
        $userinfo   = \yii::$app->session->get('user');
        if($userinfo)
        {
            \Yii::$app->session->setFlash('tips','您已经登录');
            $this->data['islogin'] = 1;
        }
        if(isset($_COOKIE['rememberme']) && $_COOKIE['rememberme']){
            $this->data['username'] = $_COOKIE['rememberme'];
        }else{
            $this->data['username'] = '';
        }
		$this->data['loginUrl'] = U('login/operate');
		$this->data['csrf']     = \yii::$app->request->csrfToken;
        $this->data['tips']     = \Yii::$app->session->getFlash('tips');
		return $this->renderPartial('index',['data'=>$this->data]);
	}

	public function actionOperate()
	{
		$user       = \yii::$app->request->post('username');
		$pwd        = \yii::$app->request->post('pwd');
		$rememberme = \yii::$app->request->post('rememberme');

		$verifycode        = \yii::$app->request->post('verifycode');
        $verify_res = $this->_checkVerifyCode($verifycode);
        if(!$verify_res)
        {
            \yii::$app->session->setFlash('tips','验证码不正确');
            jump('login/index');
            exit;
        }
        $check_res  = $this->_checkPwd($user,$pwd);
        if($check_res===1)
        {
            if($rememberme){
                setcookie('rememberme',$user);
            }else{
                setcookie('rememberme','');
            }
            jump('index/index');
        }
        else
        {
            \yii::$app->session->setFlash('tips','用户名或密码不正确');
            jump('login/index');
            exit;
        }

	}

    public function actionResetpwd()
    {
        $this->data['csrf']     = \yii::$app->request->csrfToken;
        $this->data['tips']     = \Yii::$app->session->getFlash('tips');
        return $this->renderPartial('resetPwd',['data'=>$this->data]);
    }

    public function actionOpresetpwd()
    {
        $user       = \yii::$app->request->post('username');
        $pwd        = \yii::$app->request->post('pwd');
        $newpwd        = \yii::$app->request->post('newpwd');
        $check_res  = $this->_checkPwd($user,$pwd);
        if($check_res!==1){
            \yii::$app->session->setFlash('tips','用户名或密码不正确');
            jump('login/resetpwd');
            exit;
        }else{
            $this->_resetpwd($user,$newpwd);
            jump('index/index');
        }
    }

    public function _resetpwd($user,$newpwd){
        $adminLogic     = new AdminLogic();
        $userinfo   = $adminLogic->getAdminInfoByName($user,['status'=>1]);

        $auth_key           = $userinfo['auth_key'];

        $ck1 = md5( $auth_key.md5( $newpwd) );

        $adminLogic->updateAdminPass($userinfo['id'],$ck1);

        $result_data['uid']         = $userinfo['id'];
        $result_data['username']    = $userinfo['username'];
        $result_data['role']        = 'PADMIN';
        \yii::$app->session->set('user',$result_data);
    }

    public function _checkVerifyCode($input)
    {
        $session_key = '__captcha/' . $this->getUniqueId() . '/captcha';
        $session = \yii::$app->getSession();
        $session->open();
        $verifycode = @$session[$session_key];
        if(strtoupper($input)==strtoupper($verifycode))
        {
            return true;
        }
        return false;
    }
    private function _checkPwd($user,$pwd)
    {
        $adminLogic     = new AdminLogic();
        $userinfo   = $adminLogic->getAdminInfoByName($user,['status'=>1]);
        if(!$userinfo) return -1;

        $auth_key           = $userinfo['auth_key'];
        $password_hash      = $userinfo['password_hash'];

        $ck1 = md5( $auth_key.md5( $pwd) );

        if( ($ck1 == $password_hash) )
        {
            $adminLogic->updateLogin($userinfo['id']);
            $result_data['uid']         = $userinfo['id'];
            $result_data['username']    = $userinfo['username'];
            $result_data['role']        = 'PADMIN';
            \yii::$app->session->set('user',$result_data);
            return 1;
        }
        return -2;
    }

    public function actionLogout()
    {
        \yii::$app->session->remove('user');
        jump('login/index');
    }

}
