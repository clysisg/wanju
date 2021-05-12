<?php
/**
 * Created by PhpStorm.
 * User: yikai
 * Date: 2016/8/1
 * Time: 15:06
 */

namespace app\controllers;

use \app\common\logic\AuthorLogic;
use Yii;

class AuthorController extends BackendController{

    /*
     * 权限列表
     */
    public function actionIndex(){
        $author = new AuthorLogic();
        $author = $author->getAuthorList();
        $data=$this->data;
        $data['list'] = $author;
        return $this->render('index',['data'=>$data]);
    }

    /*
     * 修改权限
     */
    public function actionEdit(){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
               $author=new AuthorLogic();
               $res = $author->saveAdminAuthorInfo($post);
               if($res)
                   YII::$app->session->setFlash('success','修改成功');
               else
                   YII::$app->session->setFlash('error','修改失败');

            return $this->redirect(U('author/index'));
        }else{
            $id=Yii::$app->request->get('id');
            if(!$id) $this->redirect('/');
            $author = new AuthorLogic();
            $author = $author->getAdminAuthorInfo($id);
            $data=$this->data;
            $data['author'] = $author;
            return $this->render('edit',['data'=>$data]);
        }
    }
    /*
     * 添加权限
     */
    public function actionAdd(){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
               $author=new AuthorLogic();
               $res = $author->saveAdminAuthorInfo($post);
               if($res)
                   YII::$app->session->setFlash('success','添加成功');
               else
                   YII::$app->session->setFlash('error','添加失败');

            return $this->redirect(U('author/index'));
        }else{

            $data=$this->data;
            return $this->render('add',['data'=>$data]);
        }
    }
    /*
     * 删除权限
     */
    public function actionDelete(){
        $id = Yii::$app->request->get('id');
        if($id){
           $author = new AuthorLogic();
           $res = $author->deleteAuthor($id);
           if($res)
               YII::$app->session->setFlash('success','删除成功');
           else
               YII::$app->session->setFlash('error','删除失败');
        }
        return $this->redirect(U('author/index'));
    }

}