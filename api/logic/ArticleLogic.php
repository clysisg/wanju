<?php
/**
 * Created by PhpStorm.
 * User: wjj
 * Date: 2016/11/1
 */

namespace app\logic;



use app\models\Article;
use app\models\Feedback;
use app\models\Help;
use yii;

class ArticleLogic
{
    public function getHelp()
    {
        return Help::find()->orderBy('input_date')->asArray()->all();
    }

    public function getArticle($type,$id){
        if($type=="help"){
            $res = Help::findOne($id);
        }elseif($type=="agreement"){
            $res = Article::findOne(['title'=>'用户协议']);
        }else{
            $res = Article::findOne(['title'=>'关于我们']);
        }
        return $res->content;
    }

    public function saveFeedback($uid,$content){
        $feedback = new Feedback();
        $feedback->user_id = $uid;
        $feedback->feedback_content = $content;
        $feedback->input_date = date('Y-m-d H:i:s');
        return $feedback->save();
    }


}