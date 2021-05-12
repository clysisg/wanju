<?php
namespace app\logic;

use app\common\corelib\CacheHelper;
use app\models\Comment;
use app\models\CommentTrends;
use app\models\Company;
use app\models\Follow;
use app\models\Tag;
use app\models\Trends;
use app\models\User;
use app\models\Investor;
use app\models\UserFavorite;
use yii\base\Security;
use yii\db\Query;

use JPush\Client as JPush;

//include  __DIR__ . '/../common/corelib/rongyun/rongcloud.php';
use app\common\corelib\Easemob;

class UserLogic
{
    public function login($mobile,$pass,$registrationId)
    {

        $user = User::findOne(['user_name'=>$mobile]);
        if(!$user){
            return ['status'=>false,'msg'=>'您输入的号码未注册'];
        }
        $password_hash = md5($user->auth_key.md5($pass));
        $user = User::findOne(['user_name'=>$mobile,'password_hash'=>$password_hash]);
        if(!$user){
            return ['status'=>false,'msg'=>'您输入的密码与账号不符，请重新输入'];
        }else{
            if($user->status==0){
                return ['status'=>false,'msg'=>'账号已被禁用，暂不能登录'];
            }
            $user->device = $registrationId;
            $user->save();
            $token = $user->token;
            $user = User::find()->where(['id'=>$user->id])->asArray()->one();
        }
        if(!$need_logout = CacheHelper::get('login_'.$user['id'])){
            CacheHelper::set('login_'.$user['id'],$registrationId);
        }else{
            $app_key = \Yii::$app->params['jpush']['app_key'];
            $master_secret = \Yii::$app->params['jpush']['master_secret'];
            $client = new JPush($app_key, $master_secret);
            $client->push()
                ->setPlatform('all')
                ->addRegistrationId($need_logout)
                ->message($need_logout,['title'=>'need_logout','content_type'=>'1','extras'=>''])
                ->send();
            CacheHelper::set('login_'.$user['id'],$registrationId);
        }
        $type = $user['type'];
        $status = $user['status'];
        if($type!=-1){
            if($type){
                $user = Company::find()->where(['uid'=>$user['id']])->asArray()->one();
                $file = $user['logo_img'];
            }else{
                $user = Investor::find()->where(['uid'=>$user['id']])->asArray()->one();
                $file = $user['head_img'];
            }
            $houzhui = explode('.',$file);
            $houzhui = array_pop($houzhui);
            //$user['thumb'] = \Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui;

//            if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                $user['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
//            }else{
//                $user['thumb'] = $file;
//            }
            if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                $user['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
            }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                $user['thumb'] = $file;
            }else{
                $user['thumb'] = 'default-avatar.png';
            }
            if(!$file){
                $user['thumb'] = 'default-avatar.png';
            }
        }else{
            $user['uid']=$user['id'];
        }
        $user['token'] = $token;
        $user['type'] = $type;
        $user['status'] = $status;
        return $user;
    }

    public function getUser($mobile){
        $user = User::findOne(['user_name'=>$mobile]);
        if(!$user){
            return ['status'=>false,'msg'=>'您输入的号码未注册'];
        }else{
            return ['status'=>true,'msg'=>'您输入的号码已注册'];
        }
    }

    public function getUserinfo($uid){
        return $user = User::findOne(['id'=>$uid]);
    }
    public function getUserinfodetail($uid){
        $user = User::findOne(['id'=>$uid]);
        $type=$user->type;
        if($user->type==0){
            $user = Investor::find()->where(['uid'=>$uid])->asArray()->one();
        } elseif($user->type==1){
            $user = Company::find()->where(['uid'=>$uid])->asArray()->one();
            $user['title'] = '';
        }
        $user['type'] = $type;
        return $user;
    }

    public function setnewpass($mobile,$newpass){
        $user = User::findOne(['user_name'=>$mobile]);
        $password_hash = md5($user->auth_key.md5($newpass));
        $user->password_hash = $password_hash;
        return $user->save();
    }

    public function regist($mobile,$pass,$registrationId)
    {
        $user = new User();
        $user->user_name = $mobile;
        $security = new Security();
        $auth_key= $security->generateRandomString();
        $user->auth_key = $auth_key;
        $password_hash = md5($user->auth_key.md5($pass));
        $user->password_hash = $password_hash;
        $time = date('Y-m-d H:i:s');
        $user->input_date = $time;
        $user->login_date = $time;
        $user->device = $registrationId;
        $user->save();
        $user = User::findOne(['user_name'=>$mobile]);
        /*$RongCloud = new \RongCloud(\Yii::$app->params['rongyun']['appKey'],\Yii::$app->params['rongyun']['appSecret']);
        $result = $RongCloud->user()->getToken($user->id, $mobile, 'http://www.rongcloud.cn/images/logo.png');
        $result = json_decode($result);
        $user->token = $result->token;*/
        $options['client_id']='YXA6bRfCMNv6EeexUNtpi1mWJw';
        $options['client_secret']='YXA6QSu5N6tR8pkX1GVcJNvXksQKxrY';
        $options['org_name']='1161171208178407';
        $options['app_name']='diantou';
        $h=new Easemob($options);
        $h->createUser($user->id,$pass);
        $user->save();

        $user = User::find()->where(['user_name'=>$mobile])->asArray()->one();
        CacheHelper::set('login_'.$user['id'],$registrationId);
        return $user;
    }

    public function bind($mobile,$openid,$type,$registrationId){
        $user = User::findOne(['user_name'=>$mobile]);
        if($user){
            $time = date('Y-m-d H:i:s');
            $user->input_date = $time;
            $user->login_date = $time;
            $user->device = $registrationId;
            if($type=='qq'){
                $user->qq_openid = $openid;
            }else{
                $user->wechat_openid = $openid;
            }
            $user->save();

            $token = $user->token;
            $user = User::find()->where(['id'=>$user->id])->asArray()->one();
            if(!$need_logout = CacheHelper::get('login_'.$user['id'])){
                CacheHelper::set('login_'.$user['id'],$registrationId);
            }else{
                $app_key = \Yii::$app->params['jpush']['app_key'];
                $master_secret = \Yii::$app->params['jpush']['master_secret'];
                $client = new JPush($app_key, $master_secret);
                $client->push()
                    ->setPlatform('all')
                    ->addRegistrationId($need_logout)
                    ->message($need_logout,['title'=>'need_logout','content_type'=>'1','extras'=>''])
                    ->send();
                CacheHelper::set('login_'.$user['id'],$registrationId);
            }
            $type = $user['type'];
            $status = $user['status'];
            if($type!=-1){
                if($type){
                    $user = Company::find()->where(['uid'=>$user['id']])->asArray()->one();
                    $file = $user['logo_img'];
                }else{
                    $user = Investor::find()->where(['uid'=>$user['id']])->asArray()->one();
                    $file = $user['head_img'];
                }
                $houzhui = explode('.',$file);
                $houzhui = array_pop($houzhui);
                //$user['thumb'] = \Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui;

//                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                    $user['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
//                }else{
//                    $user['thumb'] = $file;
//                }
                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                    $user['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
                }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                    $user['thumb'] = $file;
                }else{
                    $user['thumb'] = 'default-avatar.png';
                }
                if(!$file){
                    $user['thumb'] = 'default-avatar.png';
                }
            }else{
                $user['uid']=$user['id'];
            }
            $user['token'] = $token;
            $user['type'] = $type;
            $user['status'] = $status;
            $options['client_id']='YXA6bRfCMNv6EeexUNtpi1mWJw';
            $options['client_secret']='YXA6QSu5N6tR8pkX1GVcJNvXksQKxrY';
            $options['org_name']='1161171208178407';
            $options['app_name']='diantou';
            $h=new Easemob($options);
            $h->createUser($user['id'],$openid);
            return $user;

        }else{
            $user = new User();
            $user->user_name = $mobile;
            $time = date('Y-m-d H:i:s');
            $user->input_date = $time;
            $user->login_date = $time;
            $user->device = $registrationId;
            if($type=='qq'){
                $user->qq_openid = $openid;
            }else{
                $user->wechat_openid = $openid;
            }
            $user->save();
            $user = User::findOne(['user_name'=>$mobile]);
            /*$RongCloud = new \RongCloud(\Yii::$app->params['rongyun']['appKey'],\Yii::$app->params['rongyun']['appSecret']);
            $result = $RongCloud->user()->getToken($user->id, $mobile, 'http://www.rongcloud.cn/images/logo.png');
            $result = json_decode($result);
            $user->token = $result->token;*/
            $options['client_id']='YXA6bRfCMNv6EeexUNtpi1mWJw';
            $options['client_secret']='YXA6QSu5N6tR8pkX1GVcJNvXksQKxrY';
            $options['org_name']='1161171208178407';
            $options['app_name']='diantou';
            $h=new Easemob($options);
            $h->createUser($user['id'],$openid);
            $user->save();
            $user = User::find()->where(['user_name'=>$mobile])->asArray()->one();
            CacheHelper::set('login_'.$user['id'],$registrationId);
            return $user;
        }

    }

    public function saveInvestor($uid,$headimg,$name,$en_name,$sex,$phone,$title,$summary,$business_card){
        $trans = \Yii::$app->db->beginTransaction();
        $is_exit = true;
        $investor = Investor::findOne(['uid'=>$uid]);
        if(!$investor){
            $investor = new Investor();
            $is_exit = false;
        }
        $investor->uid = $uid;
        $investor->name = $name;
        $investor->en_name = $en_name;
        $investor->phone = $phone;
        $investor->sex = $sex;
        $investor->title = $title;
        $investor->summary = $summary;
        $investor->head_img = $headimg;
        $investor->business_card = $business_card;
        $res1 = $investor->save();
        $investor = Investor::find()->where(['uid'=>$uid])->asArray()->one();
        $investor['type']=0;
        $user = User::findOne(['id'=>$uid]);
        $user->type = 0;
        if($is_exit){
            $user->status=-1;
        }

        $res2 =$user->save();
        $investor['token'] = $user->token;
        if($res1 && $res2){
            $trans->commit();
//            $RongCloud = new \RongCloud(\Yii::$app->params['rongyun']['appKey'],\Yii::$app->params['rongyun']['appSecret']);
//            $result = $RongCloud->user()->refresh($uid, $name, 'http://www.rongcloud.cn/images/logo.png');
            $options['client_id']='YXA6bRfCMNv6EeexUNtpi1mWJw';
            $options['client_secret']='YXA6QSu5N6tR8pkX1GVcJNvXksQKxrY';
            $options['org_name']='1161171208178407';
            $options['app_name']='diantou';
            $h=new Easemob($options);
            $h->editNickname($user->user_name,$name);
            return $investor;
        }else{
            $trans->rollback();
            return false;
        }

    }

    public function saveCompany($uid,$logoimg,$name,$found_date,$money,$owner,$employee,$province_id,$city_id,$district_id,$address,$industry,$market,$phone,$summary,$certificate){
        $trans = \Yii::$app->db->beginTransaction();
        $is_exit = true;
        $company = Company::findOne(['uid'=>$uid]);
        if(!$company){
            $company = new Company();
            $is_exit = false;
        }
        $company->uid = $uid;
        $company->name = $name;
        $company->logo_img = $logoimg;
        $company->phone = $phone;
        $company->found_date = $found_date;
        $company->money = $money;
        $company->owner = $owner;
        $company->employee = $employee;
        $company->province_id = $province_id;
        $company->city_id = $city_id;
        $company->district_id = $district_id;
        $company->address = $address;
        $company->industry_id = $industry;
        $company->market_id = $market;
        $company->summary = $summary;
        $company->certificate = $certificate;
        $res1 = $company->save();
        $company = Company::find()->where(['uid'=>$uid])->asArray()->one();
        $company['type']=1;
        $user = User::findOne(['id'=>$uid]);
        $user->type = 1;
        if($is_exit){
            $user->status=-1;
        }
        $res2 = $user->save();
        $company['token'] = $user->token;
        if($res1 && $res2){
            $trans->commit();
            /*$RongCloud = new \RongCloud(\Yii::$app->params['rongyun']['appKey'],\Yii::$app->params['rongyun']['appSecret']);
            $result = $RongCloud->user()->refresh($uid, $name, 'http://www.rongcloud.cn/images/logo.png');*/
            $options['client_id']='YXA6bRfCMNv6EeexUNtpi1mWJw';
            $options['client_secret']='YXA6QSu5N6tR8pkX1GVcJNvXksQKxrY';
            $options['org_name']='1161171208178407';
            $options['app_name']='diantou';
            $h=new Easemob($options);
            $h->editNickname($user->user_name,$name);
            return $company;
        }else{
            $trans->rollback();
            return false;
        }
    }

    public function getFavorite($uid,$skip,$limit){
        return CommentTrends::find()
            ->select('ct.id,ct.type,ct.input_date input_time,c.*,t.*,co.uid company_uid,i.uid investor_id,i.head_img,i.name investor_name,i.title,co.logo_img,co.name company_name,co1.name to_company_name,co1.logo_img to_company_img')
            ->from('{{%comment_trends}} ct')
            ->leftJoin('{{%comment}} c','c.comment_id=ct.id')
            ->leftJoin('{{%trends}} t','t.trends_id=ct.id')
            ->leftJoin('{{%investor}} i','i.uid = c.comment_by_uid')
            ->leftJoin('{{%company}} co1','co1.uid = c.comment_to_uid')
            ->leftJoin('{{%company}} co','co.uid = t.uid')
            ->leftJoin('{{%user_favorite}} uf','uf.comment_trends_id = ct.id')
            ->where(['uf.user_id'=>$uid])
            ->andWhere( 'c.status=1 OR c.status is null')
            ->groupBy('ct.id')
            ->offset($skip)
            ->limit($limit)
            ->orderBy('ct.input_date desc')
            ->asArray()
            ->all();
    }

    public function saveFavorite($uid,$id){
        $user_favorite = new UserFavorite();
        $user_favorite->user_id = $uid;
        $user_favorite->comment_trends_id = $id;
        return $user_favorite->save();
    }

    public function cancelFavorite($uid,$id){
        $user_favorite = UserFavorite::findOne(['user_id'=>$uid,'comment_trends_id'=>$id]);
        if($user_favorite){
            return $user_favorite->delete();
        }
        return false;
    }

    public function getTag($tagids){
        return Tag::find()->where(['tag_id'=>$tagids])->asArray()->all();
    }

    public function changephone($uid,$mobile){
        $user = User::findOne($uid);
        $user->user_name = $mobile;
        return $user->save();
    }

    public function delComment($uid,$id){
        $comment = Comment::findOne(['comment_by_uid'=>$uid,'comment_id'=>$id]);
        if($comment){
            $ct = CommentTrends::findOne(['id'=>$comment->comment_id]);
            if($ct){
                return $comment->delete() && $ct->delete();
            }
        }

    }

    public function delTrends($uid,$id){
        $trends = Trends::findOne(['uid'=>$uid,'trends_id'=>$id]);
        if($trends){
            $ct = CommentTrends::findOne(['id'=>$trends->trends_id]);
            if($ct){
                return $trends->delete() && $ct->delete();
            }
        }

    }

    public function getFollow($id,$uid){
        return Follow::find()
            ->where(['follow_by_uid'=>$uid,'follow_to_uid'=>$id])
            ->asArray()
            ->one();
    }

    public function saveImg($uid,$img){
        $user = User::findOne(['id'=>$uid]);
        if($user->type==0){
            $investor = Investor::findOne(['uid'=>$uid]);
            $investor->head_img = $img;
            $investor->save();
        }elseif($user->type==1){
            $company = Company::findOne(['uid'=>$uid]);
            $company->logo_img = $img;
            $company->save();
        }
    }

    public function changeName($uid,$name){
        $user = User::findOne(['id'=>$uid]);
        if($user->type==0){
            $investor = Investor::findOne(['uid'=>$uid]);
            $investor->name = $name;
            $investor->save();
        }elseif($user->type==1){
            $company = Company::findOne(['uid'=>$uid]);
            $company->name = $name;
            $company->save();
        }
        /*$RongCloud = new \RongCloud(\Yii::$app->params['rongyun']['appKey'],\Yii::$app->params['rongyun']['appSecret']);
        $result = $RongCloud->user()->refresh($uid, $name, 'http://www.rongcloud.cn/images/logo.png');*/
        $options['client_id']='YXA6bRfCMNv6EeexUNtpi1mWJw';
        $options['client_secret']='YXA6QSu5N6tR8pkX1GVcJNvXksQKxrY';
        $options['org_name']='1161171208178407';
        $options['app_name']='diantou';
        $h=new Easemob($options);
        $h->editNickname($user->user_name,$name);
    }

    public function changeEnname($uid,$en_name){
            $investor = Investor::findOne(['uid'=>$uid]);
            $investor->en_name = $en_name;
            $investor->save();
    }

    public function changeSex($uid,$sex){
        $investor = Investor::findOne(['uid'=>$uid]);
        $investor->sex = $sex;
        $investor->save();
    }

    public function changeCompanyInfo($uid,$info,$info_content){
        $company = Company::findOne(['uid'=>$uid]);
        $company->$info = $info_content;
        $company->save();
    }

    public function changeZone($uid,$province_id,$city_id,$district_id){
        $company = Company::findOne(['uid'=>$uid]);
        $company->province_id = $province_id;
        $company->city_id = $city_id;
        $company->district_id = $district_id;
        $company->save();
    }

    public function saveComment($uid,$id,$star,$tag,$content,$img){
        $ct = new CommentTrends();
        if($uid>=$id){
            $ct->uid = $id.','.$uid;
        }else{
            $ct->uid = $uid.','.$id;
        }
        $ct->type = 0;
        $time = date('Y-m-d H:i:s');
        $ct->input_date = $time;
        $ct->save();
        $comment = new Comment();
        $comment->comment_id = $ct->id;
        $comment->comment_by_uid = $uid;
        $comment->comment_to_uid = $id;
        $comment->status = 1;
        $comment->comment_date = $time;
        $comment->star = $star;
        $comment->tag = $tag;
        $comment->content = $content;
        $comment->images = $img;
        $comment->save();
    }

    public function openidlogin($openid,$registrationId,$type1){
        if($type1){
            $user = User::findOne(['wechat_openid'=>$openid]);
        }else{
            $user = User::findOne(['qq_openid'=>$openid]);
        }
        if(!$user){
            return false;
        }else{
            $user->device = $registrationId;
            $user->save();
            $token = $user->token;
            $user = User::find()->where(['id'=>$user->id])->asArray()->one();
            if(!$need_logout = CacheHelper::get('login_'.$user['id'])){
                CacheHelper::set('login_'.$user['id'],$registrationId);
            }else{
                $app_key = \Yii::$app->params['jpush']['app_key'];
                $master_secret = \Yii::$app->params['jpush']['master_secret'];
                $client = new JPush($app_key, $master_secret);
                $client->push()
                    ->setPlatform('all')
                    ->addRegistrationId($need_logout)
                    ->message($need_logout,['title'=>'need_logout','content_type'=>'1','extras'=>''])
                    ->send();
                CacheHelper::set('login_'.$user['id'],$registrationId);
            }
            $type = $user['type'];
            $status = $user['status'];
            if($type!=-1){
                if($type){
                    $user = Company::find()->where(['uid'=>$user['id']])->asArray()->one();
                    $file = $user['logo_img'];
                }else{
                    $user = Investor::find()->where(['uid'=>$user['id']])->asArray()->one();
                    $file = $user['head_img'];
                }
                $houzhui = explode('.',$file);
                $houzhui = array_pop($houzhui);
                //$user['thumb'] = \Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui;

//                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
//                    $user['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
//                }else{
//                    $user['thumb'] = $file;
//                }
                if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file.'_'.'100100'.'.'.$houzhui)){
                    $user['thumb'] = $file.'_'.'100100'.'.'.$houzhui;
                }elseif(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'web'.\Yii::$app->params['upload']['upload_path'].$file)){
                    $user['thumb'] = $file;
                }else{
                    $user['thumb'] = 'default-avatar.png';
                }
                if(!$file){
                    $user['thumb'] = 'default-avatar.png';
                }
            }else{
                $user['uid']=$user['id'];
            }
            $user['token'] = $token;
            $user['type'] = $type;
            $user['status'] = $status;
            return $user;
        }
    }

    public function findUser($mobile,$uid){
        $res = [];
        foreach($mobile AS $value){
            $user = User::find()->where(['user_name'=>$value])->asArray()->one();
            if($user['type']==0){
                $uu = Investor::find()
                    ->select('i.*,f.follow_id')
                    ->from('{{%investor}} i')
                    ->leftJoin('{{%follow}} f',"f.follow_by_uid=$uid and f.follow_to_uid=i.uid")
                    ->where(['uid'=>$user['id']])->asArray()->one();
                $uu['user_name'] = $user['user_name'];
                $uu['type'] = 0;
            }elseif($user['type']==1){
                $uu = Company::find()
                    ->select('i.*,f.follow_id')
                    ->from('{{%company}} i')
                    ->leftJoin('{{%follow}} f',"f.follow_by_uid=$uid and f.follow_to_uid=i.uid")
                    ->where(['uid'=>$user['id']])->asArray()->one();
                $uu['user_name'] = $user['user_name'];
                $uu['type'] = 1;
            }
            $res[] = $uu;
        }
        return $res;
    }



}