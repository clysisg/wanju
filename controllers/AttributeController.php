<?php
namespace app\controllers;


use app\common\logic\AttributeLogic;
use yii;

class AttributeController extends BackendController
{

    /**
     * 属性列表
     * @return string
     */
    public function actionIndex()
    {
        $query = Yii::$app->request->get('form', []);
        $page = Yii::$app->request->get('page', 1);
        $attrLogic = new AttributeLogic();
        $this->data['breadcrumbs'] = '属性设置';
        return $this->render('index', [
            'attributes' => $attrLogic->getAttributes($query, $page),
            'query' => $query
        ]);
    }

    /**
     * 属性更新页面
     * @return string
     */
    public function actionEdit()
    {
        $id = Yii::$app->request->get('attr_id');
        $attrLogic = new AttributeLogic();
        $attribute = $attrLogic->getById($id);
        $this->data['breadcrumbs'] = ['属性设置' => U('attribute/index'), $attribute['attr_name'] => '', '更新' => ''];
        return $this->render('edit', [
            'attribute' => $attribute,
            'actionUrl' => U('attribute/update'),
            'attr_input_type_list' => $attrLogic::$attr_input_type_list
        ]);
    }

    /**
     * 属性更新
     */
    public function actionUpdate()
    {
        $params = Yii::$app->request->post('form');
        $id = Yii::$app->request->post('attr_id');
        $logicRes = AttributeLogic::editAttributeById($id, $params);
        if ($logicRes) {
            Yii::$app->getSession()->setFlash('success','更新属性成功');
            jump('attribute/index');
        } else {
            Yii::$app->getSession()->setFlash('error','更新属性失败');
            $this->goBack(Yii::$app->request->referrer);
        }
    }

    /**
     * 新增大类页面
     * @return string
     */
    public function actionAdd()
    {
        $this->data['breadcrumbs'] = ['属性设置' => U('attribute/index'), '新增大类' => ''];
        return $this->render('add', [
            'actionUrl' => U('attribute/create'),
            'attr_input_type_list' => AttributeLogic::$attr_input_type_list
        ]);
    }

    /**
     * 新增大类
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->post('form');
        $logicRes = AttributeLogic::addAttribute($params);
        if ($logicRes) {
            Yii::$app->getSession()->setFlash('success','新增属性成功');
            jump('attribute/index');
        } else {
            Yii::$app->getSession()->setFlash('error','新增属性失败');
            $this->goBack(Yii::$app->request->referrer);
        }
    }

    /**
     * 删除大类
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->get('attr_id');
        $attr = new AttributeLogic();
        $logicRes = $attr->deleteById($id);
        if ($logicRes) {
            Yii::$app->getSession()->setFlash('success','删除属性成功');
        } else {
            Yii::$app->getSession()->setFlash('error','删除属性失败');
        }
        $this->goBack(Yii::$app->request->referrer);
    }

    /**
     * 小类列表
     * @return string
     */
    public function actionSubIndex()
    {
        $query = Yii::$app->request->get('form', []);
        $page = Yii::$app->request->get('page', 1);
        $attr_id = Yii::$app->request->get('attr_id', 1);
        $attrLogic = new AttributeLogic();
        $attributes = $attrLogic->getSubAttributes($attr_id, $query, $page);
        if (!$attributes) {
            Yii::$app->getSession()->setFlash('error','未知错误');
            $this->goBack();
        }
        $this->data['breadcrumbs'] = ['属性设置' => U('attribute/index'), $attributes['attr_name'] => '', '小类' => ''];
        return $this->render('sub_index', [
            'attributes' => $attributes,
            'query' => $query,
            'attr_id' => $attr_id
        ]);
    }

    /**
     * 更新小类页面
     * @return string
     */
    public function actionSubEdit()
    {
        $id = Yii::$app->request->get('id');
        $attrLogic = new AttributeLogic();
        $attribute = $attrLogic->getValueById($id);
        $attr = $attrLogic->getById($attribute->attr_id);
        $this->data['breadcrumbs'] = ['属性设置' => U('attribute/index'),
            $attr['attr_name'] => U(['attribute/sub-index', 'attr_id' => $attribute->attr_id]),
            $attribute['attr_value_name'] => '', '更新' => ''];
        return $this->render('sub_edit', [
            'attribute' => $attribute,
            'actionUrl' => U('attribute/sub-update'),
            'attr_input_type_list' => $attrLogic::$attr_input_type_list
        ]);
    }

    /**
     * 更新小类
     */
    public function actionSubUpdate()
    {
        $params = Yii::$app->request->post('form');
        $id = Yii::$app->request->post('id');
        $logicRes = AttributeLogic::editAttributeValueById($id, $params);
        if ($logicRes) {
            Yii::$app->getSession()->setFlash('success','更新属性成功');
            jump(['attribute/sub-index','attr_id' => $logicRes]);
        } else {
            Yii::$app->getSession()->setFlash('error','更新属性失败');
            $this->goBack(Yii::$app->request->referrer);
        }
    }

    /**
     * 新增小类页面
     * @return string
     */
    public function actionSubAdd()
    {
        $id = Yii::$app->request->get('attr_id');
        $attribute = AttributeLogic::getById($id);
        $this->data['breadcrumbs'] = ['属性设置' => U('attribute/index'),
            $attribute['attr_name'] => U(['attribute/sub-index', 'attr_id' => $id]),
            '新增' => ''];
        return $this->render('sub_add', [
            'actionUrl' => U('attribute/sub-create'),
            'attr_id' => $id
        ]);
    }

    /**
     * 新增小类
     */
    public function actionSubCreate()
    {
        $params = Yii::$app->request->post('form');
        $logicRes = AttributeLogic::addAttributeValue($params);
        if ($logicRes) {
            Yii::$app->getSession()->setFlash('success','新增属性成功');
            jump(['attribute/sub-index','attr_id' => $params['attr_id']]);
        } else {
            Yii::$app->getSession()->setFlash('error','新增属性失败');
            $this->goBack(Yii::$app->request->referrer);
        }
    }

    /**
     * 删除小类
     */
    public function actionSubDelete()
    {
        $id = Yii::$app->request->get('id');
        $attr = new AttributeLogic();
        $logicRes = $attr->deleteValueById($id);
        if ($logicRes) {
            Yii::$app->getSession()->setFlash('success','删除属性成功');
        } else {
            Yii::$app->getSession()->setFlash('error','删除属性失败');
        }
        $this->goBack(Yii::$app->request->referrer);
    }
}