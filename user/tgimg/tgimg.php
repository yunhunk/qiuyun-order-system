<?php
//error_reporting(0);
$id  = isset($_GET['id']) ? intval($_GET['id']) : exit();
$url = isset($_GET['url']) ? trim($_GET['url']) : exit();
if ($id < 1 || $id > 6) {
    exit();
}

define('PATH', dirname(__FILE__) . '/');
$qrcode_path = dirname(dirname(PATH)) . '/includes/core/QRcode.php';
if (!is_file($qrcode_path)) {
    header('Content-type:text/html; charset=UTF-8');
    exit("Error: QRcode lib is not found！");
}

require $qrcode_path;

$backgrounds = array('adv1.jpg', 'adv2.jpg', 'adv3.jpg', 'adv4.jpg', 'adv5.jpg', 'adv6.jpg');

$imagesx = array(175, 175, 280, 260, 325, 270);

$imagesy = array(175, 125, 350, 245, 380, 330);

$imagesize = array(150, 150, 240, 300, 190, 260);

//图片一
$path_1 = PATH . 'img/' . $backgrounds[$id - 1];

$image_1 = imagecreatefromjpeg($path_1);
if ($image_1 == false) {
    header('Content-type:text/html; charset=UTF-8');
    exit("Error:Failed to load picture 1");
}

$QRcode = new \core\QRcode();

ob_start();
$QRcode->png($url, false, 'L', ($imagesize[$id - 1] / 20), 2);
$qrcodedata = ob_get_contents();
ob_end_clean();

$image_2 = imagecreatefromstring($qrcodedata);

//缩放二维码图片
$qrcodelength = $imagesize[$id - 1];
$image_3      = imagecreate($qrcodelength, $qrcodelength);
imagecolorallocate($image_3, 255, 255, 255);
imagecopyresampled($image_3, $image_2, 0, 0, 0, 0, $qrcodelength, $qrcodelength, imagesx($image_2), imagesy($image_2));

//合成图片
//imagecopymerge ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h , int $pct )---拷贝并合并图像的一部分
//将 src_im 图像中坐标从 src_x，src_y 开始，宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。两图像将根据 pct 来决定合并程度，其值范围从 0 到 100。当 pct = 0 时，实际上什么也没做，当为 100 时对于调色板图像本函数和 imagecopy() 完全一样，它对真彩色图像实现了 alpha 透明。
imagecopymerge($image_1, $image_3, $imagesx[$id - 1], $imagesy[$id - 1], 0, 0, $qrcodelength, $qrcodelength, 100);
// 输出合成图片
//imagepng($image[,$filename]) ― 以 PNG 格式将图像输出到浏览器或文件

header('Content-type:image/png');
imagepng($image_1);
