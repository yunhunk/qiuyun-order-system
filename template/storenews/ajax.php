<?php
if (!defined('IN_CRONLITE')) die();

$act = isset($_GET['act']) ? $_GET['act'] : null;

switch($act) {
    case 'getCategory2':
        $pcid = intval($_GET['pcid']);
        // 获取二级分类数据
        $sub_class = $DB->query("SELECT * FROM pre_class WHERE active=1 AND parent_cid='$pcid' ORDER BY sort ASC");
        $data = [];
        while($row = $sub_class->fetch()){
            if($is_fenzhan && in_array($row['cid'], $classhide)) continue;
            $data[] = $row;
        }
        $result = array('code'=>0, 'msg'=>'success', 'data'=>$data);
        exit(json_encode($result));
        break;
        
    default:
        $result = array('code'=>-1, 'msg'=>'No Act');
        exit(json_encode($result));
        break;
} 