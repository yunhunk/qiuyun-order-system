<?php
error_reporting(E_ALL);
include './lib/vaptcha.class.php';
$msg = '';
if(isset($_REQUEST['offline_action'])){
    if(isset($_GET['v'])) {
        echo Vaptcha::offline($_GET['offline_action'], $_GET['callback'], $_GET['v'], $_GET['knock']);
    } else {
        echo Vaptcha::offline($_GET['offline_action'], $_GET['callback']);
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $token = $_POST['token'];
        $scene = $_POST['scene'];
        $result =  Vaptcha::validate($token, $scene);
        print_r($result);
    }
}
?>