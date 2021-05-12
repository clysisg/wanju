<?php
namespace app\common\logic;

use \app\models\Admin;
use \app\models\AdminRole;
use \app\models\AdminAuthor;
/**
* 
*/
class AdminLogic
{
    public function updateAdminPass($adminid,$pass_hash){
        $admin = Admin::findOne(['id'=>$adminid]);
        $admin->password_hash = $pass_hash;
        $admin->update_date = date('Y-m-d H:i:s');
        $admin->save();
    }

    public function updateLogin($adminid){
        $admin = Admin::findOne(['id'=>$adminid]);
        $admin->last_login = date('Y-m-d H:i:s');
        $admin->save();
    }

	public function getAdminList($page,$limit)
    {
        $count = Admin::find()->where('username <> "Adminstor"')->orderBy('id desc')->asArray()->count();
        $list = Admin::find()->where('username <> "Adminstor"')->orderBy('id desc')->asArray()->offset(($page-1)*$limit)->limit($limit)->All();
        $adminrole=AdminRole::find()->asArray()->All();
        if($adminrole){
            $roleArray=[];
            foreach( $adminrole as $row ) {
              $roleArray[ $row[ 'id' ] ] = $row[ 'name' ];
            }
            if($roleArray){
                foreach( $list as &$row ) {
                    $row[ 'roles' ] = join( ",", array_intersect_key( $roleArray, array_flip( explode( ',', trim( $row[ 'admin_role_id' ], ',' ) ) ) ) );
                }
            }
        }
        $result = [
            'list'  => $list,
            'pager' => [
                    'total' => $count,
                    'page' =>  $page,
                    'limit' => $limit,
                    'url' => 'admin/index',
                    'query' => []
            ]
        ];
        return $result;
    }

	public function getAdminInfo($id)
    {
        $result = Admin::find()->where(['id'=>$id])->asArray()->one();
        return $result;
    }

	public function getAdminInfoByName($name,$where=[])
    {
        $result = Admin::find()->where([ 'and',['username'=>$name] ,$where ])->asArray()->one();
        return $result;
    }

	public function saveAdminInfo($data)
    {
        $admin=null;
        if($data && $data['id'] && !empty($data['id'])) $admin = Admin::find()->where(['id'=>$data['id']])->one();
        if($admin){
            $admin->username   = $data['username'];
            $admin->status = $data['status'];
            $admin->admin_role_id = $data['admin_role_id'];
            if(!empty($data['password'])) $admin->password_hash = md5($admin['auth_key'].md5($data['password']));
            $admin->update_date = date('Y-m-d H:i:s');
            $result = $admin->save();
        }else{
            $admin = new Admin();
            $admin->username   = $data['username'];
            $admin->status = $data['status'];
            $admin->admin_role_id = $data['admin_role_id'];
            $admin->auth_key = $data['auth_key'];
            $admin->password_hash = md5($data['auth_key'].md5($data['password']));
            $admin->create_date = date('Y-m-d H:i:s');
            $admin->update_date = date('Y-m-d H:i:s');
            $result = $admin->save();
        }
        return $result;
    }

    public function deleteAdmin($id)
    {
        $result = false;
        $admin = Admin::find()->where(['id'=>$id])->one();
        if($admin){
             $result = $admin->delete();
        }
        return $result;
    }

    public function getAdminAuthorIds( $adminId ) {
        $return = '';
        $admin = $this->getAdminInfo( $adminId );
        $roleIds = trim( $admin[ 'admin_role_id' ], ',' );
        if ( !empty( $roleIds ) ) {
            $rows = AdminRole::find()->where( 'id in ('.$roleIds.') and status=1' )->all();
            $array = array();
            foreach( $rows as $row ) {
               $authorIds = trim( $row[ 'author_id' ], ',' );
               if ( !empty( $authorIds ) ) {
                  $array = array_merge( $array, explode( ',', $authorIds ) );
                }
            }
            if ( !empty( $array ) ) {
                $return = join( ',', $array );
            }
        }
        return $return;
    }

    public function getAdminAuthorByAction( $action ) {
         return AdminAuthor::find()->where( ['action'=> strtolower( $action )] )->one();
    }
}
