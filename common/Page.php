<?php
/**
 * Created by PhpStorm.
 * User: jimzhou
 * Date: 2016/5/20
 * Time: 13:28
 */
namespace app\common;

class Page{
    public $count;
    public $pageSize=50;

    public function __construct($count,$pageSize)
    {
        $this->count = $count;
        $this->pageSize = $pageSize;
    }

    public function show(){
        $curr_page = isset($_GET['page'])?$_GET['page']:1;
        unset($_GET['page']);
        $url = 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')).'?'.http_build_query($_GET);
        $result ='<div class="btn-toolbar"><div class="btn-group">';
        $page = ceil($this->count/$this->pageSize);
        for($i=1;$i<=$page;$i++){
            if($curr_page == $i)
                $result .= '<a href="'.$url.'&page='.$i.'" class="btn btn-danger" type="button">'.$i.'</a>';
            else
                $result .= '<a href="'.$url.'&page='.$i.'" class="btn btn-default" type="button">'.$i.'</a>';
        }
        $result .='</div></div>';
        return $result;
    }
}