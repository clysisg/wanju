<?php
namespace app\controllers;

use app\common\corelib\Excel;
use yii;
use app\common\logic\AdminLogic;


/**
 * 控制器基类，会检查是否登录
 */
class BackendController extends BaseController
{

    public $exceptAuthors = ['index', 'login'];
    public $exceptActions = ['getzone'];


    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        $this->_checkLogin();
        $this->_checkPrivilege($action);
        $this->layout = '../layout/main';
        unset($_GET['menu_id']);
        unset($_GET['s']);
        return parent::beforeAction($action);
    }

    /**
     * 检查是否登录
     */
    public function _checkLogin()
    {
        $userinfo = \yii::$app->session->get('user');
        $uid = $userinfo['uid'];
        $role = $userinfo['role'];
        if ($uid > 0 && $role == 'PADMIN') {
            $username = $userinfo['username'];
            $this->data['header_username'] = $username;
            return true;
        } else {
            \yii::$app->session->remove('user');
            jump('login/index');
        }

    }

    /**
     * 检查权限
     */
    public function _checkPrivilege($action)
    {
        if(in_array($action->id, $this->exceptActions))
            return true;
        $result = false;
        $userinfo = \yii::$app->session->get('user');
        $adminId = $userinfo['uid'];

        if ($userinfo['username'] == 'Adminstor') {
            return true;
        }
        if ($adminId > 0) {
            $adminlogic = new AdminLogic();
            $adminAuthorIds = $adminlogic->getAdminAuthorIds($adminId);
            if (!empty($adminAuthorIds)) {
                $action = strtolower($this->id . '/' . $action->id);
                $adminAuthor = $adminlogic->getAdminAuthorByAction($action);
                if (abs($adminAuthor['id']) > 0 && in_array($adminAuthor['id'], explode(',', $adminAuthorIds))) {
                    $result = true;
                }
            }
        }

        if (!$result && !in_array($this->id, $this->exceptAuthors)) {
            jump(['index/error']);
        }
        return $result;
    }

    /**
     * @param $list
     * @param $dataname
     * @param $action
     * @return mixed
     */
    public function getExportUrl($list, $dataname, $action = 'down')
    {
        switch ($dataname) {
            case 'delItem' :
                $key_arr = $this->excelTitle['delItem'];
                $fileName = '已删除商品列表';
                break;
            case 'cloudItem' :
                $key_arr = $this->excelTitle['cloudItem'];
                $fileName = '云市场商品列表';
                break;
            case 'allItem' :
                $key_arr = $this->excelTitle['allItem'];
                $fileName = '全部商品列表';
                break;
            case 'allUser' :
                $key_arr = $this->excelTitle['user'];
                $fileName = '全部用户列表';
                break;
            case 'seller' :
                $key_arr = $this->excelTitle['user'];
                $fileName = '供应商列表';
                break;
            case 'subUser' :
                $key_arr = $this->excelTitle['user'];
                $fileName = '子账号列表';
                break;
            case 'allVendor' :
                $key_arr = $this->excelTitle['allVendor'];
                $fileName = '店铺列表';
                break;
            case 'vendorSubUser' :
                $key_arr = $this->excelTitle['vendorSubUser'];
                $fileName = '店铺子账号列表';
                break;
            case 'vendorCustomer' :
                $key_arr = $this->excelTitle['vendorCustomer'];
                $fileName = '店铺客户列表';
                break;
            case 'vendorItem' :
                $key_arr = $this->excelTitle['vendorItem'];
                $fileName = '店铺商品列表';
                break;
            case 'itemSaleList' :
                $key_arr = $this->excelTitle['itemSaleList'];
                $fileName = '商品销售明细列表';
                break;
            default :
                return '';
        }
        $data = [];
        foreach ($list as $k => $v) {
            $temp = [];
            foreach ($key_arr as $vv) {
                if (array_key_exists($vv['key'], $v)) {
                    $temp[$vv['key']] = $v[$vv['key']];
                } else {
                    $temp[$vv['key']] = '';
                }
            }
            $data[] = $temp;
        }
        $dataName = $dataname.'List';
        $excel = new Excel();
        $result = $excel->write_excel($fileName, $dataName, $key_arr, [], $data, $action);
        return $result['url'];
    }

    private $excelTitle = [
        'delItem' => [
            ['key' => 'display_order', 'name' => '#', 'type' => 'int'],
            ['key' => 'vendor_name', 'name' => '所属商铺', 'type' => 'string'],
            ['key' => 'item_id', 'name' => '商品ID', 'type' => 'int'],
            ['key' => 'item_name', 'name' => '商品名称', 'type' => 'string'],
            ['key' => 'item_no', 'name' => '商品款号', 'type' => 'string'],
            ['key' => 'gc_name', 'name' => '商品类别', 'type' => 'string'],
            ['key' => 'item_bacth_price', 'name' => '商品价格', 'type' => 'float.2'],
            ['key' => 'item_stock_before_del', 'name' => '删除时库存剩余量', 'type' => 'int'],
            ['key' => 'item_sale_num', 'name' => '总销量', 'type' => 'int'],
            ['key' => 'input_date', 'name' => '录入商品时间', 'type' => 'string'],
            ['key' => 'modify_date', 'name' => '修改商品时间', 'type' => 'string'],
            ['key' => 'delete_date', 'name' => '删除商品时间', 'type' => 'string']
        ],
        'cloudItem' => [
            ['key' => 'display_order', 'name' => '#', 'type' => 'int'],
            ['key' => 'vendor_name', 'name' => '所属商铺', 'type' => 'string'],
            ['key' => 'item_id', 'name' => '商品ID', 'type' => 'int'],
            ['key' => 'item_name', 'name' => '商品名称', 'type' => 'string'],
            ['key' => 'item_no', 'name' => '商品款号', 'type' => 'string'],
            ['key' => 'gc_name', 'name' => '商品类别', 'type' => 'string'],
            ['key' => 'item_bacth_price', 'name' => '商品价格', 'type' => 'float.2'],
            ['key' => 'item_stock', 'name' => '当前总库存量', 'type' => 'int'],
            ['key' => 'channel_name', 'name' => '销售渠道', 'type' => 'string'],
            ['key' => 'cloud_sale_num', 'name' => '云市场总销量', 'type' => 'int'],
            ['key' => 'input_date', 'name' => '录入商品时间', 'type' => 'string'],
            ['key' => 'modify_date', 'name' => '修改商品时间', 'type' => 'string'],
            ['key' => 'onshelf_date', 'name' => '上架云市场时间', 'type' => 'string']
        ],
        'allItem' => [
            ['key' => 'display_order', 'name' => '#', 'type' => 'int'],
            ['key' => 'vendor_name', 'name' => '所属商铺', 'type' => 'string'],
            ['key' => 'item_id', 'name' => '商品ID', 'type' => 'int'],
            ['key' => 'item_name', 'name' => '商品名称', 'type' => 'string'],
            ['key' => 'item_no', 'name' => '商品款号', 'type' => 'string'],
            ['key' => 'gc_name', 'name' => '商品类别', 'type' => 'string'],
            ['key' => 'item_bacth_price', 'name' => '商品价格', 'type' => 'float.2'],
            ['key' => 'item_stock', 'name' => '当前总库存量', 'type' => 'int'],
            ['key' => 'item_status_txt', 'name' => '商品状态', 'type' => 'string'],
            ['key' => 'item_sale_num', 'name' => '总销量', 'type' => 'int'],
            ['key' => 'input_date', 'name' => '录入商品时间', 'type' => 'string'],
            ['key' => 'modify_date', 'name' => '修改商品时间', 'type' => 'string'],
            ['key' => 'onshelf_date', 'name' => '上架云市场时间', 'type' => 'string'],
            ['key' => 'offshelf_date', 'name' => '下架云市场时间', 'type' => 'string']
        ],
        'user' => [
            ['key' => 'display_order', 'name' => '#', 'type' => 'int'],
            ['key' => 'user_id', 'name' => '用户ID', 'type' => 'int'],
            ['key' => 'user_name', 'name' => '用户名', 'type' => 'string'],
            ['key' => 'user_identity_name', 'name' => '用户身份', 'type' => 'string'],
            ['key' => 'reg_mobile', 'name' => '电话号码', 'type' => 'string'],
            ['key' => 'reg_email', 'name' => '邮箱', 'type' => 'string'],
            ['key' => 'is_bind_wechat', 'name' => '微信绑定', 'type' => 'string'],
            ['key' => 'is_bind_qq', 'name' => 'QQ绑定', 'type' => 'string'],
            ['key' => 'reg_date', 'name' => '注册时间', 'type' => 'string'],
            ['key' => 'reg_channel', 'name' => '注册渠道', 'type' => 'string'],
            ['key' => 'last_login_date', 'name' => '最后一次登录时间', 'type' => 'string'],
        ],
        'allVendor' => [
            ['key' => 'vendor_id', 'name' => '店铺id', 'type' => 'int'],
            ['key' => 'vendor_name', 'name' => '店铺名称', 'type' => 'string'],
            ['key' => 'owner_name', 'name' => '店主姓名', 'type' => 'string'],
            ['key' => 'username', 'name' => '用户名', 'type' => 'string'],
            ['key' => 'reg_mobile', 'name' => '电话号码', 'type' => 'string'],
            ['key' => 'address', 'name' => '地址', 'type' => 'string'],
            ['key' => 'market_no', 'name' => '档口号', 'type' => 'string'],
            ['key' => 'market', 'name' => '所属批发市场', 'type' => 'string'],
            ['key' => 'input_date', 'name' => '开通店铺时间', 'type' => 'string'],
            ['key' => 'use_pos', 'name' => '是否使用POS', 'type' => 'string'],
        ],
        'vendorSubUser' => [
            ['key' => 'user_id', 'name' => '用户ID', 'type' => 'int'],
            ['key' => 'username', 'name' => '用户名', 'type' => 'string'],
            ['key' => 'input_date', 'name' => '员工加入时间', 'type' => 'string'],
            ['key' => 'role_name', 'name' => '角色名称', 'type' => 'string'],
            ['key' => 'auth_show', 'name' => '店铺权限', 'type' => 'string']
        ],
        'vendorCustomer' => [
            ['key' => 'to_user_id', 'name' => '用户ID', 'type' => 'int'],
            ['key' => 'username', 'name' => '用户名', 'type' => 'string'],
            ['key' => 'name', 'name' => '客户名称', 'type' => 'string'],
            ['key' => 'arrear_fee', 'name' => '当前店铺欠款金额', 'type' => 'string'],
            ['key' => 'total_fee', 'name' => '交易总金额', 'type' => 'string'],
            ['key' => 'online_fee', 'name' => '线上交易金额', 'type' => 'string'],
            ['key' => 'online_count', 'name' => '线上交易次数', 'type' => 'string'],
            ['key' => 'offline_fee', 'name' => '线下交易金额', 'type' => 'string'],
            ['key' => 'offline_count', 'name' => '线下交易次数', 'type' => 'string'],
            ['key' => 'last_channel', 'name' => '最近一次交易渠道', 'type' => 'string'],
        ],
        'vendorItem' => [
            ['key' => 'display_order', 'name' => '#', 'type' => 'int'],
            ['key' => 'item_id', 'name' => '商品ID', 'type' => 'int'],
            ['key' => 'item_name', 'name' => '商品名称', 'type' => 'string'],
            ['key' => 'item_no', 'name' => '商品款号', 'type' => 'string'],
            ['key' => 'gc_name', 'name' => '商品类别', 'type' => 'string'],
            ['key' => 'item_bacth_price', 'name' => '商品价格', 'type' => 'float.2'],
            ['key' => 'item_stock', 'name' => '当前总库存量', 'type' => 'int'],
            ['key' => 'item_status_txt', 'name' => '商品状态', 'type' => 'string'],
            ['key' => 'item_sale_num', 'name' => '总销量', 'type' => 'int'],
            ['key' => 'input_date', 'name' => '录入商品时间', 'type' => 'string'],
            ['key' => 'modify_date', 'name' => '修改商品时间', 'type' => 'string'],
            ['key' => 'onshelf_date', 'name' => '上架云市场时间', 'type' => 'string'],
            ['key' => 'offshelf_date', 'name' => '下架云市场时间', 'type' => 'string']
        ],
        'itemSaleList' => [
            ['key' => 'display_order',      'name' => '#',            'type' => 'int'],
            ['key' => 'vendor_name',        'name' => '所属商铺',      'type' => 'string'],
            ['key' => 'item_id',            'name' => '商品ID',       'type' => 'int'],
            ['key' => 'item_name',          'name' => '商品名称',      'type' => 'string'],
            ['key' => 'item_no',            'name' => '商品款号',      'type' => 'string'],
            ['key' => 'item_sku',           'name' => 'SKU',          'type' => 'string'],
            ['key' => 'real_item_price',    'name' => '实际销售单价',   'type' => 'float.2'],
            ['key' => 'item_num',           'name' => '销售量',        'type' => 'int'],
            ['key' => 'real_sale_amount',   'name' => '实际销售总价',   'type' => 'float.2'],
            ['key' => 'channel_name',       'name' => '销售渠道',      'type' => 'string'],
            ['key' => 'order_date',         'name' => '销售时间',      'type' => 'string'],
            ['key' => 'recv_address',       'name' => '收货地址',      'type' => 'string'],
            ['key' => 'customer_wy_user_id','name' => '采购商用户ID',   'type' => 'string'],
            ['key' => 'customer_name',      'name' => '客户名称',      'type' => 'string']
        ]
    ];

    /**
     * 后台统一返回json格式
     * @param integer $code 错误代码
     * @param string $msg 错误信息
     * @param array $data 返回数据
     * @return array
     */
    public function jsonResponse($code=0, $msg='', $data=[])
    {
        $response = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];

        return json_encode($response);
    }

    public function checkParams($array = [])
    {
        if (!empty($array)) {
            foreach ($array as $value) {
                if (!isset($this->params[$value]) || $this->params[$value] === '') return false;
            }
        }
        return true;
    }
}