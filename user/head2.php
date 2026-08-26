<?php
@header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8" />
  <title><?php echo $title ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="renderer" content="webkit">
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/cmui/css/main.css?v=0.20">
  <link rel="stylesheet" href="<?php echo $cdnserver; ?>assets/user/css/animate.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnserver; ?>assets/user/css/app.css" type="text/css" />
  <script src="<?php echo $cdnpublic ?>jquery/3.1.1/jquery.min.js"></script>
  <script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="<?php echo $cdnserver; ?>assets/user/js/app.js"></script>
  <script src="<?php echo $cdnpublic ?>layer/3.4.0//layer.js"></script>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>