<?php
if( Yii::$app->getSession()->hasFlash('success') ) {
    echo yii\bootstrap\Alert::widget([
        'options' => [
            'class' => 'alert-success', //这里是提示框的class
        ],
        'body' => Yii::$app->getSession()->getFlash('success'), //消息体
    ]);
    echo '<script>setTimeout(function(){$(\'[data-dismiss="alert"]\').alert(\'close\');},2000);</script>';
}
if( Yii::$app->getSession()->hasFlash('error') ) {
    echo yii\bootstrap\Alert::widget([
        'options' => [
            'class' => 'alert-error',
        ],
        'body' => Yii::$app->getSession()->getFlash('error'),
    ]);
    echo '<script>setTimeout(function(){$(\'[data-dismiss="alert"]\').alert(\'close\');},2000);</script>';
}