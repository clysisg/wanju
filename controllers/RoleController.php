<?php
/**
 * Created by PhpStorm.
 * User: yikai
 * Date: 2016/8/1
 * Time: 15:06
 */

namespace app\controllers;

use \app\common\logic\AdminRoleLogic;
use \app\common\logic\AuthorLogic;
use Yii;
use yii\web\User;

class RoleController extends  BackendController{

    public function actionIndex(){
        $adminrole=new AdminRoleLogic();
        $role=$adminrole->getAdminRoleList();
        $data=$this->data;
        $data['role']=$role;
        return $this->render('index',['data'=>$data]);
    }

    public function actionEdit(){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if( isset($post['id']) && isset($post['name']) && isset($post['status']) ){
               if(empty($post['name'])){
                   YII::$app->session->setFlash('error','名称不能为空');
               }else{
                   $adminrole=new AdminRoleLogic();
                   $res = $adminrole->saveAdminRoleInfo($post);
                   if($res)
                       YII::$app->session->setFlash('success','修改成功');
                   else
                       YII::$app->session->setFlash('error','修改失败');
               }
            }
            return $this->redirect(U('role/index'));
        }else{
            $id=Yii::$app->request->get('id');
            if(!$id) $this->redirect('/');
            $adminrole=new AdminRoleLogic();
            $role=$adminrole->getAdminRoleInfo($id);
            $data=$this->data;
            $data['role']=$role;
            return $this->render('edit',['data'=>$data]);
        }
    }

    public function actionAdd(){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if( isset($post['id']) && isset($post['name']) && isset($post['status']) ){
               if(empty($post['name'])){
                   YII::$app->session->setFlash('error','名称不能为空');
               }else{
                   $adminrole=new AdminRoleLogic();
                   $res = $adminrole->saveAdminRoleInfo($post);
                   if($res)
                       YII::$app->session->setFlash('success','添加成功');
                   else
                       YII::$app->session->setFlash('error','添加失败');
                   return $this->redirect(U('role/index'));
               }
            }
        }
        return $this->render('add',['data'=>$this->data]);

    }

    public function actionDelete(){
        $id = Yii::$app->request->get('id');
        if($id){
           $adminrole=new AdminRoleLogic();
           $res = $adminrole->deleteAdminRole($id);
           if($res) YII::$app->session->setFlash('success','删除成功');
           else  YII::$app->session->setFlash('error','删除失败，该角色已关联账号');
        }
        return $this->redirect(U('role/index'));
    }

    public function actionAuthor(){

        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post('form');
            if(isset($post['author_id']) && $post['author_id']){
                $author_id = ','.join(',',$post['author_id']).',';
            }else{
                 $author_id = '';
            }
            $adminRole = new AdminRoleLogic();
            $result = $adminRole->saveRoleAuthor($post['id'],$author_id);
            if($result)
                YII::$app->session->setFlash('success','保存成功');
            else
                YII::$app->session->setFlash('error','保存失败');
            return $this->redirect(U('role/index'));
        }
        $id=Yii::$app->request->get('id');
        if(!$id) $this->redirect('/');

        $AdminRoleLogic = new AdminRoleLogic();
        $role = $AdminRoleLogic->getAdminRoleInfo($id);

        if(!$role) $this->redirect('/');

        $AuthorLogic = new AuthorLogic();
        $author = $AuthorLogic->getAuthorTree();
        $data = $this->data;
        $data['author'] = $author;
        $data['role'] = $role;
        $data['author_id'] = trim($role['author_id'],',');

        return $this->render('author',['data'=>$data]);
    }
}