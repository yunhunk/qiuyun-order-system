<?php
include '../includes/common.php';
$title = '移动更新文件';

$scriptpath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$admin_path = substr($scriptpath, strrpos($scriptpath, '/') + 1);

include './head.php';

if (is_dir(ROOT . 'admuser')) {
    if ($admin_path != 'admuser') {
        //移动后台文件到当前后台目录
        \core\File::moveDir(ROOT . 'admuser', ROOT . $admin_path);
        //移动后删除
        \core\File::delFiles(ROOT . 'admuser');
        rmdir(ROOT . 'admuser');
    }
    saveSetting('updatemovetime', time() + 600);
    showmsg('检测到有新的覆盖包上传，正在更新...<br/>当前自动更新程序后台成功，请返回上一页', 1);
} else {
    showmsg('未检查到后台的更新文件夹[admuser]，无需移动！<br/>如需移动，请下载更新上传解压到目录[' . ROOT . ']', 1);
}
