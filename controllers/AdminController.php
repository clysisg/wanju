<?php
/**
 * Created by PhpStorm.
 * User: yikai
 * Date: 2016/8/1
 * Time: 15:06
 */

namespace app\controllers;

use \app\common\logic\AdminLogic;
use \app\common\logic\AdminRoleLogic;
use Yii;
use yii\base\Security;

class AdminController extends BackendController{

    /*
     * 管理员列表
     */
    public function actionIndex(){
        $page = Yii::$app->request->get('page', 1 );
        $pageSize = Yii::$app->request->get('pageSize',10);
        $adminrole=new AdminLogic();
        $admin=$adminrole->getAdminList($page,$pageSize);
        $data=$this->data;
        $data['admin']=$admin;
        return $this->render('index',['data'=>$data]);
    }

    /*
     * 修改管理员
     */
    public function actionEdit(){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if( isset($post['id']) && isset($post['password']) && isset($post['username']) && isset($post['role']) && isset($post['status']) ){

               if(!isset($post['role'])  || empty($post['username'])){
                  YII::$app->session->setFlash('error','管理员账号和角色不能为空');
               }elseif($post['password']!=$post['checkpassword']){
                   YII::$app->session->setFlash('error','密码和确认密码不一致');
               }else{
                   $post[ 'admin_role_id' ] = ',' . join( ',', $post[ 'role' ] ) . ',';
                   $adminrole=new AdminLogic();
                   $res = $adminrole->saveAdminInfo($post);
                   if($res)
                       YII::$app->session->setFlash('success','修改成功');
                   else
                       YII::$app->session->setFlash('error','修改失败');
               }
            }
            return $this->redirect(U('admin/index'));
        }else{
            $id=Yii::$app->request->get('id');
            if(!$id) $this->redirect('/');
            $admin = new AdminLogic();
            $admin = $admin->getAdminInfo($id);
            $adminrole=new AdminRoleLogic();
            $role = $adminrole->getAdminRoleList();
            $data=$this->data;
            $data['admin'] = $admin;
            $data['role']  = $role;
            return $this->render('edit',['data'=>$data]);
        }
    }
    /*
     * 添加管理员
     */
    public function actionAdd(){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if( isset($post['password']) && isset($post['username']) && isset($post['status']) ){
               if( !isset($post['role']) || empty($post['password']) || empty($post['username']) ){
                  YII::$app->session->setFlash('error','管理员密码和角色不能为空');
               }elseif($post['password']!=$post['checkpassword']){
                   YII::$app->session->setFlash('error','密码和确认密码不一致');
               }else{
                   $security = new Security();
                   $auth_key= $security->generateRandomString();
                   $post['auth_key'] = $auth_key;
                   $post[ 'admin_role_id' ] = ',' . join( ',', $post[ 'role' ] ) . ',';
                   $adminrole=new AdminLogic();
                   $res = $adminrole->saveAdminInfo($post);
                   if($res)
                       YII::$app->session->setFlash('success','添加成功');
                   else
                       YII::$app->session->setFlash('error','添加失败');
                   return $this->redirect(U('admin/index'));
               }
            }
        }
        $adminrole=new AdminRoleLogic();
        $role = $adminrole->getAdminRoleList();
        $data = $this->data;
        $data['role'] = $role;
        return $this->render('add',['data'=>$data]);

    }
    /*
     * 删除管理员
     */
    public function actionDelete(){
        $id = Yii::$app->request->get('id');
        if($id){
           $adminrole=new AdminLogic();
           $res = $adminrole->deleteAdmin($id);
           if($res)
               YII::$app->session->setFlash('success','删除成功');
           else
               YII::$app->session->setFlash('error','删除失败');
        }
        return $this->redirect(U('admin/index'));
    }

}