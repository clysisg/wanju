<?php
namespace app\common\logic;

use \app\models\Admin;
use \app\models\AdminRole;
/**
* 
*/
class AdminRoleLogic
{

	public function getAdminRoleList()
    {
        $result = AdminRole::find()->orderBy('id desc')->asArray()->All();
        return $result;
    }

	public function getAdminRoleInfo($id)
    {
        $result = AdminRole::find()->where(['id'=>$id])->asArray()->one();
        return $result;
    }

	public function saveAdminRoleInfo($data)
    {
        $role=null;
        if($data && $data['id'] && !empty($data['id'])) $role = AdminRole::find()->where(['id'=>$data['id']])->one();
        if($role){
            $role->name=$data['name'];
            $role->status=$data['status'];
            $role->remark = $data['remark'];
            $role->update_date=date('Y-m-d H:i:s');
            $result = $role->save();
        }else{
            $adminRole = new AdminRole();
            $adminRole->name=$data['name'];
            $adminRole->status=$data['status'];
            $adminRole->remark = $data['remark'];
            $adminRole->create_date=date('Y-m-d H:i:s');
            $adminRole->update_date=date('Y-m-d H:i:s');
            $result = $adminRole->save();
        }
        return $result;
    }

    public function deleteAdminRole($id)
    {
        $result = false;
        $role = AdminRole::find()->where(['id'=>$id])->one();
        if($role){
            $admin = Admin::find()->where(['id'=>$role['id']])->one();
            if(!$admin) $result = $role->delete();
        }
        return $result;
    }

    public function saveRoleAuthor($roleId,$author_id){

        $role = AdminRole::find()->where(['id'=>$roleId])->one();
        $role->author_id = $author_id;
        $role->update_date=date('Y-m-d H:i:s');
        $result = $role->save();
        return $result;
    }
}
