<?php
$theme = substr($this->context->data['theme'],0,-1);
$this->context->data['theme'] = $theme;
$csrf = \yii::$app->request->csrfToken;
$this->context->data['csrf'] = $csrf;
$data['data'] = $this->context->data;
$data['breadcrumbs'] = $this->context->data['breadcrumbs'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>点评投资后台管理中心</title>
    <?php echo Yii::$app->view->render('/com/head.html',$data);?>
</head>
<body class="nav-md">

<div class="container body">
    <div class="main_container">
        <!-- 左侧导航 -->
        <?php echo Yii::$app->view->render('/com/sidebar.html',$data);?>
        <!-- /左侧导航 -->

        <!-- 头部 -->
        <?php echo Yii::$app->view->render('/com/header.html',$data);?>
        <!-- /头部 -->


        <!-- 页面内容 -->
        <div class="right_col" role="main">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12 clearfix" style="margin-bottom: 10px;">
                    <?php
                    if($data['breadcrumbs']) {
                        if (is_string($data['breadcrumbs'])) echo $data['breadcrumbs'];
                        elseif (is_array($data['breadcrumbs'])) {
                            $breadcrumbs = '';
                            foreach ($data['breadcrumbs'] as $k => $v) {
                                if ($v) {
                                    $breadcrumbs .= '<a href="' . $v . '">' . $k . '</a> > ';
                                } else {
                                    $breadcrumbs .= $k . ' > ';
                                }
                            }
                            echo trim($breadcrumbs, '> ');
                        }
                    }
                    ?>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <?php echo Yii::$app->view->render('/com/alert.php');?>
                    <?php echo $content;?>
                </div>
            </div>
        </div>
        <!-- /页面内容 -->
    </div>
</div>

<!-- 底部开始 -->
<?php echo Yii::$app->view->render('/com/footer.html',$data);?>
<!-- 底部结束 -->
<!-- 核心框架插件开始 -->
<?php echo Yii::$app->view->render('/com/foot.html',$data);?>
<!-- 核心框架插件结束 -->
</body>

</html>