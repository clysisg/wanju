<?php
namespace app\controllers;
use yii\web\Controller;

class ErrController extends Controller {

	public function actionIndex() {
		$this->redirect('/');
	}
}