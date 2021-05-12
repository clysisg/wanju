<?php
    $page = $pager['page'];
    $limit = $pager['limit'];
    $total = $pager['total'];
    $pages = $total > 0 ? ceil( $total / $limit ) : 1;
$url = U( $pager['url'] );
$query = array();
foreach( $pager['query'] as $key => $val ){
$query[] = 'form[' . $key . ']=' . $val;
}
$query = implode( '&', $query );
!empty( $query ) && $query = '&' . $query;

$params = '';
if (isset($pager['params']) && !empty($pager['params'])) {
    $params = '&';
    foreach($pager['params'] as $key => $val ){
        $params .= $key . '=' . $val;
    }
}

$links = 3; //链接数量
$start = max( 1, $page - intval( $links / 2 ) );
$end = min( $start + $links - 1, $pages );
$start = max( 1, $end - $links + 1 );
?>
<div class="dataTables_info" id="datatable-buttons_info" role="status" aria-live="polite">共<?php echo $total;?>条记录</div>
<div class="dataTables_paginate paging_simple_numbers">
    <div class="btn-toolbar">
        <div class="btn-group">
            <?php if( $page > 1 ):?>
            <a href="<?php echo $url . '?page= ' . ( $page - 1 ) . $query . $params;?>" class="btn" type="button">
                &lt;</a>
            <?php endif;?>
            <?php if( $page > 3 ):?>
            <a href="<?php echo $url . '?page=1' . $query . $params;?>" class="btn" type="button">1 ...</a>
            <?php endif;?>
            <?php for( $i = $start; $i <= $end; $i++ ): ?>
            <?php if($i == $page):?>
            <span class="btn btn-danger"><?php echo $i; ?></span>
            <?php else:?>
            <a href="<?php echo $url . '?page=' . $i . $query . $params;?>" class="btn" type="button"><?php echo $i; ?></a>
            <?php endif;?>
            <?php endfor; ?>
            <?php if( $page < $pages - 3 ):?>
            <a href="<?php echo $url . '?page=' . $pages . $query . $params;?>" class="btn"
               type="button">... <?php echo $pages;?></a>
            <?php endif;?>
            <?php if( $page < $pages ):?>
            <a href="<?php echo $url . '?page= ' . ( $page + 1 ) . $query . $params;?>" class="btn" type="button">
                &gt;</a>
            <?php endif;?>
        </div>
    </div>
</div>