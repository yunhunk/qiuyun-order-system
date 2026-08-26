<?php

include_once '../includes/common.php';

if ($conf['file_type'] == 1) {
    if (!preg_match('/^(http|https):\/\/([\w\_\-\.]+)\.([a-zA-Z]{1,5})\/$/', $conf['file_ftp_url'])) {
        exit('{"code":-1,"msg":"上传失败，ftp静态图片站地址不正确！"}');
    }

    $inputName = 'filedata'; //表单文件域name
    $filename  = 'goodsdetail_' . substr(md5(rand(1, 9999) . time()), 0, 16) . '.png';
    $ret       = uploadFile($inputName, $filename, 'goodsdetail');
    if ($ret['code'] == 0) {
        $result = array(
            'errno' => 0,
            'msg'   => '上传成功',
            'data'  => [
                $ret['imgSrc'],
            ],
        );
    } else {
        $result = array(
            'errno' => 1,
            'msg'   => $ret['msg'],
            'data'  => array(),
        );
    }
    exit(json_encode($result));
} else {
    if (!defined('DIRECTORY_SEPARATOR')) {
        define('DIRECTORY_SEPARATOR', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "\\" : '/');
    }
    $inputName = 'filedata'; //表单文件域name
    $attachDir = ROOT . '/assets/upload'; //上传文件保存路径，结尾不要带/
    $exts      = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'wbmp', 'bmp']; //支持的图片格式
    if (!is_dir($attachDir)) {
        @mkdir($attachDir, 0755);
    }

    if (!is_dir($attachDir)) {
        exit(json_encode(array(
            'errno' => 1,
            'msg'   => '文件上传路径不存在且创建无权限，请手动在站点运行目录下创建[assets/upload/]目录',
            'data'  => array(),
        )));
    }
    $attachUrl     = $weburl . 'assets/upload'; //文件url路径
    $dirType       = 1; //2:按天存入目录 2:按月存入目录 3:按扩展名存目录  建议使用按天存
    $maxAttachSize = 3 * 1024 * 1024; //最大上传大小，默认是3M
    $upExt         = 'jpg,jpeg,gif,png'; //上传扩展名
    $msgType       = 2; //返回上传参数的格式：1，只返回url，2，返回参数数组
    $immediate     = isset($_GET['immediate']) ? $_GET['immediate'] : 0; //立即上传模式，仅为演示用
    ini_set('date.timezone', 'Asia/Shanghai'); //时区

    $err       = "";
    $msg       = "''";
    $tempFile  = @$_FILES[$inputName]['tmp_name'];
    $tempPath  = $attachDir . '/' . date("YmdHis") . mt_rand(10000, 99999) . '.tmp';
    $localName = '';

    if (isset($_SERVER['HTTP_CONTENT_DISPOSITION']) && preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i', $_SERVER['HTTP_CONTENT_DISPOSITION'], $info)) {
        //HTML5上传
        file_put_contents($tempPath, file_get_contents("php://input"));
        $localName = urldecode($info[2]);
    } else {
        //标准表单式上传
        $upfile = @$_FILES[$inputName];
        if (!isset($upfile)) {
            $err = '文件域的name错误';
        } elseif (!empty($upfile['error'])) {
            switch ($upfile['error']) {
                case '1':
                    $err = '文件大小超过了php.ini定义的upload_max_filesize值';
                    break;
                case '2':
                    $err = '文件大小超过了HTML定义的MAX_FILE_SIZE值';
                    break;
                case '3':
                    $err = '文件上传不完全';
                    break;
                case '4':
                    $err = '无文件上传';
                    break;
                case '6':
                    $err = '服务器缺少临时文件夹';
                    break;
                case '7':
                    $err = '写文件失败';
                    break;
                case '8':
                    $err = '上传被其它扩展中断';
                    break;
                case '999':
                default:
                    $err = '无有效错误代码';
            }
        } elseif (empty($upfile['tmp_name']) || $upfile['tmp_name'] == 'none') {
            $err = '无文件上传';
        } elseif (empty($upfile['tmp_name']) || $upfile['tmp_name'] == 'none') {
            $err = '无文件上传';
        } else {
            $file_type = explode('/', $_FILES[$inputName]['type'])[1];
            if (in_array($file_type, $exts)) {
                if ($file_type == 'gif') {
                    $img  = imagecreatefromgif($upfile['tmp_name']);
                    $scsk = imagegif($img, $tempPath, 100);
                    imagedestroy($img);
                } elseif ($file_type == 'webp') {
                    $img  = imagecreatefromwebp($upfile['tmp_name']);
                    $scsk = imagewebp($img, $tempPath, 100);
                    imagedestroy($img);
                } elseif ($file_type == 'wbmp') {
                    $img  = imagecreatefromwbmp($upfile['tmp_name']);
                    $scsk = imagewbmp($img, $tempPath, 100);
                    imagedestroy($img);
                } elseif ($file_type == 'png') {
                    $img  = imagecreatefrompng($upfile['tmp_name']);
                    $scsk = imagepng($img, $tempPath, 9);
                    imagedestroy($img);
                } elseif ($file_type == 'bmp' && function_exists('imagecreatefrombmp')) {
                    $img  = imagecreatefrombmp($upfile['tmp_name']);
                    $scsk = imagebmp($img, $tempPath, 9);
                    imagedestroy($img);
                } else {
                    $scsk = copy($upfile['tmp_name'], $tempPath);
                }

                if ($scsk) {
                    $localName = $upfile['name'];
                } else {
                    if (!is_file($upfile['tmp_name'])) {
                        $err = '上传的文件已损坏或不存在！';
                    } else {
                        $err = '该文件格式不支持上传或已损坏[' . $file_type . ']';
                    }
                }
            } else {
                $err = '不支持的文件格式' . $file_type . '，仅支持' . implode(',', $exts);
            }
        }
    }
    if ($err == '') {
        $fileInfo  = pathinfo($localName);
        $extension = $fileInfo['extension'];
        if (preg_match('/^(' . str_replace(',', '|', $upExt) . ')$/i', $extension)) {
            $bytes = filesize($tempFile);
            if ($bytes > $maxAttachSize) {
                $err = '您上传的文件大小为' . formatBytes($bytes) . '，已超过最大' . formatBytes($maxAttachSize);
            } else {
                switch ($dirType) {
                    case 1:$attachSubDir = 'day_' . date('ymd');
                        break;
                    case 2:$attachSubDir = 'month_' . date('ym');
                        break;
                    case 3:$attachSubDir = 'ext_' . $extension;
                        break;
                }

                $attachDir = $attachDir . '/' . $attachSubDir;
                if (!is_dir($attachDir)) {
                    @mkdir($attachDir, 0777);
                    @fclose(fopen($attachDir . '/index.htm', 'w'));
                }
                PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
                $newFilename = date("YmdHis") . mt_rand(1000, 9999) . '.' . $extension;
                $targetPath  = $attachDir . '/' . $newFilename;
                $targetPath  = str_replace('/', DIRECTORY_SEPARATOR, $targetPath);
                $fileUrl     = $attachUrl . '/' . $attachSubDir . '/' . $newFilename;
                //rename($tempPath, $targetPath);
                if (copy($tempFile, $targetPath)) {
                    @chmod($targetPath, 0755);
                    @chmod($targetPath, 0755);
                    $targetPath = jsonString($targetPath);
                    if ($immediate == '1') {
                        $targetPath = '!' . $targetPath;
                    }

                    if ($msgType == 1) {
                        $msg = $targetPath;
                    } else {
                        $msg = $fileUrl; //id参数固定不变，仅供演示，实际项目中可以是数据库ID
                    }
                } else {
                    $err = '上传文件失败，目标路径：[' . $targetPath . ']'; //id参数固定不变，仅供演示，实际项目中可以是数据库ID
                }
            }
        } else {
            $err = '上传文件扩展名必需为：' . $upExt;
        }

        @unlink($tempPath);
    }

    if (!$err) {
        $result = array('errno' => 0, 'msg' => "succ", 'data' => array($msg));
    } else {
        $result = array('errno' => 1, 'msg' => jsonString($err), 'data' => array($msg));
    }
}

echo json_encode($result);

function jsonString($str)
{
    return preg_replace("/([\\\\\/'])/", '\\\$1', $str);
}
function formatBytes($bytes)
{
    if ($bytes >= 1073741824) {
        $gb = round($bytes / 1073741824, 0);
        $mb = sprintf('%.2f', ($bytes - 1073741824 * $gb) / 1073741824);
        return ($gb + $mb) . 'GB';
    } elseif ($bytes >= 1048576) {
        $mb = round($bytes / 1024 / 1024, 0);
        $kb = sprintf('%.2f', ($bytes - 1048476 * $mb) / 1024 / 1024);
        return ($mb + $kb) . 'MB';
    } elseif ($bytes >= 1024) {
        $kb = sprintf('%.2f', $bytes / 1024);
        return $kb . 'KB';
    } else {
        $bytes = $bytes . 'B';
    }
    return $bytes;
}
