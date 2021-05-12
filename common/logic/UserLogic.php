<?php
namespace app\common\logic;

use app\models\Company;
use app\models\User;
use app\models\Investor;
use yii\db\Query;

use JPush\Client as JPush;

class UserLogic
{
    public function getInfoById($id)
    {
        $query = new Query();
        $user  = $query
            ->select('u.*,i.*')
            ->from('{{%investor}} i')
            ->leftJoin('{{%user}} u', 'i.uid=u.id')
            ->where(['id'=>$id])->one();
        return $user;
    }

    public function getCompanyInfoById($id)
    {
        $query = new Query();
        $user  = $query
            ->select('u.*,i.*,r1.region_code as province_id,r1.region_code as province_code,r2.region_code as city_id,r2.region_code as city_code')
            ->from('{{%company}} i')
            ->leftJoin('{{%user}} u', 'i.uid=u.id')
            ->leftJoin('{{%region}} r1', 'i.province_id=r1.region_code')
            ->leftJoin('{{%region}} r2', 'i.city_id=r2.region_code')
            ->where(['id'=>$id])->one();
        return $user;
    }

    public function saveStatus($id)
    {
        $user = User::find()->where(['id' => $id])->one();
        if($user->status){
            $status = 0;
            $need_logout = $user->device;
            $app_key = \Yii::$app->params['jpush']['app_key'];
            $master_secret = \Yii::$app->params['jpush']['master_secret'];
            $client = new JPush($app_key, $master_secret);
            $client->push()
                ->setPlatform('all')
                ->addRegistrationId($need_logout)
                ->message($need_logout,['title'=>'need_logout','content_type'=>'1','extras'=>''])
                ->send();
        }else{
            $status = 1;
        }
        $user->status = $status;
        $user->save();
        return true;
    }

    public function savePass($id)
    {
        $user = User::find()->where(['id' => $id])->one();
        $user->status = 1;
        $user->save();
        $app_key = \Yii::$app->params['jpush']['app_key'];
        $master_secret = \Yii::$app->params['jpush']['master_secret'];
        $client = new Jpush($app_key, $master_secret);
        $client->push()
            ->setPlatform('all')
            ->addRegistrationId($user->device)
            ->message($user->device,['title'=>'pass','content_type'=>'1','extras'=>''])
            ->send();
        return true;
    }

    public function saveReject($id)
    {
        $user = User::find()->where(['id' => $id])->one();
        $user->status = 2;
        $user->save();
        $app_key = \Yii::$app->params['jpush']['app_key'];
        $master_secret = \Yii::$app->params['jpush']['master_secret'];
        $client = new Jpush($app_key, $master_secret);
        $client->push()
            ->setPlatform('all')
            ->addRegistrationId($user->device)
            ->message($user->device,['title'=>'pass','content_type'=>'1','extras'=>''])
            ->send();
        return true;
    }

    public function resetpwd($id){
        $user = User::find()->where(['id' => $id])->one();
        $user->password_hash = md5($user->auth_key.md5('123456'));
        $user->save();
        return true;
    }

    public function saveUser($id,$data,$upload){
        $user = User::find()->where(['id' => $id])->one();
        $investor = Investor::find()->where(['uid' => $id])->one();
        $trans = \Yii::$app->db->beginTransaction();
        $user->status = $data['status'];
        $investor->name = $data['name'];
        $investor->en_name = $data['en_name'];
        $investor->sex = $data['sex'];
        $investor->phone = $data['phone'];
        $investor->title = $data['title'];
        $investor->summary = $data['summary'];
        $arr = explode(',',$investor->business_card);
        foreach($upload AS $key=>$value){
            if($value){
                $arr[$key] = $value;
            }
        }
        $investor->business_card = implode(",", $arr);
        $re1 = $user->save();
        $re2 = $investor->save();
        if($re1 && $re2){
            $trans->commit();
            return true;
        }else {
            $trans->rollback();
            return false;
        }
    }

    public function saveCompany($id,$data,$upload){
        $user = User::find()->where(['id' => $id])->one();
        $company = Company::find()->where(['uid' => $id])->one();
        $trans = \Yii::$app->db->beginTransaction();
        $user->status = $data['status'];
        $company->name = $data['name'];
        $company->found_date = $data['found_date'];
        $company->money = $data['money'];
        $company->employee = $data['employee'];
        $company->phone = $data['phone'];
        $company->owner = $data['owner'];
        $company->summary = $data['summary'];
        $company->province_id = $data['province_id'];
        $company->city_id = $data['city_id'];
        $company->district_id = $data['district_id'];
        $company->address = $data['address'];
        $company->industry_id = $data['industry_id'];
        $company->market_id = $data['market_id'];
        //$company->market_id = $data['market_id'];
        $arr = explode(',',$company->certificate);
        foreach($upload AS $key=>$value){
            if($value){
                $arr[$key] = $value;
            }
        }
        $company->certificate = implode(",", $arr);
        $re1 = $user->save();
        $re2 = $company->save();
        if($re1 && $re2){
            $trans->commit();
            return true;
        }else {
            $trans->rollback();
            return false;
        }
    }

    //is_export=1导出数据的时候使用,不分页
    public function getUserList($filter, $is_export = 0)
    {
        $userQuery  = Investor::find()
        ->select('u.id,u.user_name,i.name,u.status,u.input_date,u.login_date')
        ->from('{{%investor}} i')
        ->leftJoin('{{%user}} u', 'i.uid=u.id');
//        ->where("u.status <> -1")
//        ->where("u.status <> 2");
        //默认返回全部用户的数量
        $totalCount = $userQuery->count(1);
        $where = [];
        if (!empty($filter['user_ids'])) {
            $where[] = ['u.id' => explode(',', $filter['user_ids'])];
        }
        if (!empty($filter['user_name'])) {
            $where[] = ['like', 'u.user_name', $filter['user_name']];
        }
        if (!empty($filter['name'])) {
            $where[] = ['like', 'i.name', $filter['name']];
        }
        if (isset($filter['status'])) {
            if($filter['status']==-1)
                $where[] =['=','u.status',-1] ;
            if($filter['status']==0)
                $where[] =['=','u.status',0] ;
            if($filter['status']==1)
                $where[] =['=','u.status',1] ;
        }

        /*if (!empty($filter['start_reg_date'])) {
            $where[] = ['>=', 'UNIX_TIMESTAMP(u.createtime)', strtotime($filter['start_reg_date'])];
        }
        if (!empty($filter['end_reg_date'])) {
            $where[] = ['<=', 'UNIX_TIMESTAMP(u.createtime)', strtotime($filter['end_reg_date'])];
        }
        if (!empty($filter['start_last_login_date'])) {
            $where[] = ['>=', 'UNIX_TIMESTAMP(u.last_login_date)', strtotime($filter['start_last_login_date'])];
        }
        if (!empty($filter['end_last_login_date'])) {
            $where[] = ['<=', 'UNIX_TIMESTAMP(u.last_login_date)', strtotime($filter['end_last_login_date'])];
        }*/
        if (!empty($filter['orderby'])) {
            $orderby = $filter['orderby'];
        } else {
            $orderby = 'u.input_date desc';
        }
        $page  = empty($filter['page'])  ?  1 : $filter['page'];
        $limit = empty($filter['limit']) ? 20 : $filter['limit'];
        if (!empty($where)) array_unshift($where, 'and');
        //搜索结果数量

        $searchCount = $userQuery->andWhere($where)->count(1);

        if ($is_export) {
            $userList = $userQuery->orderBy($orderby)->asArray()->all();
        } else {
            $userList = $userQuery->orderBy($orderby)->limit($limit)->offset(($page - 1) * $limit)->asArray()->all();
        }
        foreach ($userList as $key => $user) {
            if ($is_export) {
                //列表序号
                $userList[$key]['display_order'] = $key + 1;
            } else {
                //列表序号
                $userList[$key]['display_order'] = ($page - 1) * $limit + $key + 1;
            }


            $userList[$key]['role'] = '投资人';
            //用户详情链接
            $userList[$key]['user_detail_url'] = U(['investor/detail', 'user_id' => $user['id']]);
        }
        $pager = ['total' => $searchCount, 'page' => $page, 'limit' => $limit, 'url' => $filter['page_url'], 'query' => $filter];//
        return ['totalCount' => $totalCount, 'searchCount' => $searchCount, 'userList' => $userList, 'pager' => $pager];
    }

    public function getPassUserList($filter, $is_export = 0)
    {
        $userQuery  = Investor::find()
            ->select('u.id,u.user_name,i.name,i.en_name,i.phone,u.status,u.input_date,u.login_date')
            ->from('{{%investor}} i')
            ->leftJoin('{{%user}} u', 'i.uid=u.id');
        //默认返回全部用户的数量
        $totalCount = $userQuery->count(1);
        $where = [];
        if (!empty($filter['user_ids'])) {
            $where[] = ['u.id' => explode(',', $filter['user_ids'])];
        }
        if (!empty($filter['user_name'])) {
            $where[] = ['like', 'u.user_name', $filter['user_name']];
        }
        if (!empty($filter['name'])) {
            $where[] = ['like', 'i.name', $filter['name']];
        }
        if (isset($filter['status'])) {
            if($filter['status']==-1)
                $where[] =['=','u.status',-1] ;
            if($filter['status']==4){
                $where[] =['or',['u.status'=>0,'u.status'=>1]];
            }
            if($filter['status']==2)
                $where[] =['=','u.status',2] ;
        }

        /*if (!empty($filter['start_reg_date'])) {
            $where[] = ['>=', 'UNIX_TIMESTAMP(u.createtime)', strtotime($filter['start_reg_date'])];
        }
        if (!empty($filter['end_reg_date'])) {
            $where[] = ['<=', 'UNIX_TIMESTAMP(u.createtime)', strtotime($filter['end_reg_date'])];
        }
        if (!empty($filter['start_last_login_date'])) {
            $where[] = ['>=', 'UNIX_TIMESTAMP(u.last_login_date)', strtotime($filter['start_last_login_date'])];
        }
        if (!empty($filter['end_last_login_date'])) {
            $where[] = ['<=', 'UNIX_TIMESTAMP(u.last_login_date)', strtotime($filter['end_last_login_date'])];
        }*/
        if (!empty($filter['orderby'])) {
            $orderby = $filter['orderby'];
        } else {
            $orderby = 'u.input_date desc';
        }
        $page  = empty($filter['page'])  ?  1 : $filter['page'];
        $limit = empty($filter['limit']) ? 20 : $filter['limit'];
        if (!empty($where)) array_unshift($where, 'and');
        //搜索结果数量

        $searchCount = $userQuery->andWhere($where)->count(1);

        if ($is_export) {
            $userList = $userQuery->orderBy($orderby)->asArray()->all();
        } else {
            $userList = $userQuery->orderBy($orderby)->limit($limit)->offset(($page - 1) * $limit)->asArray()->all();
        }
        foreach ($userList as $key => $user) {
            if ($is_export) {
                //列表序号
                $userList[$key]['display_order'] = $key + 1;
            } else {
                //列表序号
                $userList[$key]['display_order'] = ($page - 1) * $limit + $key + 1;
            }


            $userList[$key]['role'] = '投资人';
            //用户详情链接
            $userList[$key]['user_detail_url'] = U(['investor/passdetail', 'user_id' => $user['id']]);
        }
        $pager = ['total' => $searchCount, 'page' => $page, 'limit' => $limit, 'url' => $filter['page_url'], 'query' => $filter];//
        return ['totalCount' => $totalCount, 'searchCount' => $searchCount, 'userList' => $userList, 'pager' => $pager];
    }

    public function getPassCompanyList($filter, $is_export = 0)
    {
        $userQuery  = Company::find()
            ->select('u.id,u.user_name,i.name,i.owner,r1.region_name as province,r2.region_name as city,u.status,u.input_date,u.login_date')
            ->from('{{%company}} i')
            ->leftJoin('{{%user}} u', 'i.uid=u.id')
            ->leftJoin('{{%region}} r1', 'i.province_id=r1.region_code')
            ->leftJoin('{{%region}} r2', 'i.city_id=r2.region_code');
        //默认返回全部用户的数量
        $totalCount = $userQuery->count(1);
        $where = [];
        if (!empty($filter['user_ids'])) {
            $where[] = ['u.id' => explode(',', $filter['user_ids'])];
        }
        if (!empty($filter['user_name'])) {
            $where[] = ['like', 'u.user_name', $filter['user_name']];
        }
        if (!empty($filter['name'])) {
            $where[] = ['like', 'i.name', $filter['name']];
        }
        if (isset($filter['status'])) {
            if($filter['status']==-1)
                $where[] =['=','u.status',-1] ;
            if($filter['status']==4){
                $where[] =['or',['u.status'=>0,'u.status'=>1]];
            }
            if($filter['status']==2)
                $where[] =['=','u.status',2] ;
        }

        /*if (!empty($filter['start_reg_date'])) {
            $where[] = ['>=', 'UNIX_TIMESTAMP(u.createtime)', strtotime($filter['start_reg_date'])];
        }
        if (!empty($filter['end_reg_date'])) {
            $where[] = ['<=', 'UNIX_TIMESTAMP(u.createtime)', strtotime($filter['end_reg_date'])];
        }
        if (!empty($filter['start_last_login_date'])) {
            $where[] = ['>=', 'UNIX_TIMESTAMP(u.last_login_date)', strtotime($filter['start_last_login_date'])];
        }
        if (!empty($filter['end_last_login_date'])) {
            $where[] = ['<=', 'UNIX_TIMESTAMP(u.last_login_date)', strtotime($filter['end_last_login_date'])];
        }*/
        if (!empty($filter['orderby'])) {
            $orderby = $filter['orderby'];
        } else {
            $orderby = 'u.input_date desc';
        }
        $page  = empty($filter['page'])  ?  1 : $filter['page'];
        $limit = empty($filter['limit']) ? 20 : $filter['limit'];
        if (!empty($where)) array_unshift($where, 'and');
        //搜索结果数量

        $searchCount = $userQuery->andWhere($where)->count(1);

        if ($is_export) {
            $userList = $userQuery->orderBy($orderby)->asArray()->all();
        } else {
            $userList = $userQuery->orderBy($orderby)->limit($limit)->offset(($page - 1) * $limit)->asArray()->all();
        }
        foreach ($userList as $key => $user) {
            if ($is_export) {
                //列表序号
                $userList[$key]['display_order'] = $key + 1;
            } else {
                //列表序号
                $userList[$key]['display_order'] = ($page - 1) * $limit + $key + 1;
            }


            $userList[$key]['role'] = '投资人';
            //用户详情链接
            $userList[$key]['user_detail_url'] = U(['company/passdetail', 'user_id' => $user['id']]);
        }
        $pager = ['total' => $searchCount, 'page' => $page, 'limit' => $limit, 'url' => $filter['page_url'], 'query' => $filter];//
        return ['totalCount' => $totalCount, 'searchCount' => $searchCount, 'userList' => $userList, 'pager' => $pager];
    }

    //is_export=1导出数据的时候使用,不分页
    public function getCompanyList($filter, $is_export = 0)
    {
        $userQuery  = Investor::find()
            ->select('u.id,u.user_name,i.name,u.status,i.market_id,u.input_date,u.login_date,c.name market_name')
            ->from('{{%company}} i')
            ->leftJoin('{{%user}} u', 'i.uid=u.id')
            ->leftJoin('{{%category}} c', 'c.category_id=i.market_id');
//            ->where("u.status <> -1")
//            ->where("u.status <> 2");
        //默认返回全部用户的数量
        $totalCount = $userQuery->count(1);
        $where = [];
        if (!empty($filter['user_ids'])) {
            $where[] = ['u.id' => explode(',', $filter['user_ids'])];
        }
        if (!empty($filter['user_name'])) {
            $where[] = ['like', 'u.user_name', $filter['user_name']];
        }
        if (!empty($filter['name'])) {
            $where[] = ['like', 'i.name', $filter['name']];
        }
        if (isset($filter['status'])) {
            if($filter['status']==-1)
                $where[] =['=','u.status',-1] ;
            if($filter['status']==0)
                $where[] =['=','u.status',0] ;
            if($filter['status']==1)
                $where[] =['=','u.status',1] ;
        }
        if (isset($filter['market_id'])) {
            if(!$filter['market_id']==0){
                $where[] =['=','i.market_id',$filter['market_id']] ;
            }
        }

        /*if (!empty($filter['start_reg_date'])) {
            $where[] = ['>=', 'UNIX_TIMESTAMP(u.createtime)', strtotime($filter['start_reg_date'])];
        }
        if (!empty($filter['end_reg_date'])) {
            $where[] = ['<=', 'UNIX_TIMESTAMP(u.createtime)', strtotime($filter['end_reg_date'])];
        }
        if (!empty($filter['start_last_login_date'])) {
            $where[] = ['>=', 'UNIX_TIMESTAMP(u.last_login_date)', strtotime($filter['start_last_login_date'])];
        }
        if (!empty($filter['end_last_login_date'])) {
            $where[] = ['<=', 'UNIX_TIMESTAMP(u.last_login_date)', strtotime($filter['end_last_login_date'])];
        }*/
        if (!empty($filter['orderby'])) {
            $orderby = $filter['orderby'];
        } else {
            $orderby = 'u.input_date desc';
        }
        $page  = empty($filter['page'])  ?  1 : $filter['page'];
        $limit = empty($filter['limit']) ? 20 : $filter['limit'];
        if (!empty($where)) array_unshift($where, 'and');
        //搜索结果数量

        $searchCount = $userQuery->andWhere($where)->count(1);

        if ($is_export) {
            $userList = $userQuery->orderBy($orderby)->asArray()->all();
        } else {
            $userList = $userQuery->orderBy($orderby)->limit($limit)->offset(($page - 1) * $limit)->asArray()->all();
        }
        foreach ($userList as $key => $user) {
            if ($is_export) {
                //列表序号
                $userList[$key]['display_order'] = $key + 1;
            } else {
                //列表序号
                $userList[$key]['display_order'] = ($page - 1) * $limit + $key + 1;
            }


            $userList[$key]['role'] = '企业';
            //用户详情链接
            $userList[$key]['user_detail_url'] = U(['company/detail', 'user_id' => $user['id']]);
        }
        $pager = ['total' => $searchCount, 'page' => $page, 'limit' => $limit, 'url' => $filter['page_url'], 'query' => $filter];//
        return ['totalCount' => $totalCount, 'searchCount' => $searchCount, 'userList' => $userList, 'pager' => $pager];
    }


    //获取导出用户表格属性
    public static function getUserListTableAttr($list_type) {
        //list_type: all全部用户列表,seller供应商列表,buyer采购商列表,child_account子账号列表
        switch ($list_type) {
            case 'seller':
                $fileName = '供应商列表';
                $dataName = 'vendoruserlist';
                break;
            case 'guest':
                $fileName = '采购商列表';
                $dataName = 'guestuserlist';
                break;
            case 'sub':
                $fileName = '子账号列表';
                $dataName = 'subaccountlist';
                break;

            default:

                $fileName = '全部用户列表';
                $dataName = 'alluserlist';
        }
         $keyArr = [
                    ['key' => 'display_order',      'name' => '#',             'type' => 'int'],
                    ['key' => 'user_id',            'name' => '用户ID',         'type' => 'int'],
                    ['key' => 'user_name',          'name' => '用户名',          'type' => 'string'],
                    ['key' => 'user_identity_name', 'name' => '用户身份',        'type' => 'string'],
                    ['key' => 'reg_mobile',         'name' => '电话号码',        'type' => 'string'],
                    ['key' => 'reg_email',          'name' => '邮箱',           'type' => 'string'],
                    ['key' => 'is_bind_wechat',     'name' => '微信绑定',        'type' => 'string'],
                    ['key' => 'is_bind_qq',         'name' => 'QQ绑定',         'type' => 'string'],
                    ['key' => 'reg_date',           'name' => '注册时间',        'type' => 'string'],
                    ['key' => 'reg_channel',        'name' => '注册渠道',        'type' => 'string'],
                    ['key' => 'last_login_date',    'name' => '最后一次登录时间', 'type' => 'string'],
                ];
        return ['keyArr' => $keyArr, 'fileName' => $fileName, 'dataName' => $dataName];
    }

}