<?php
include "../includes/common.php";
$addsalt = md5(rand(1111, 9999) . x_real_ip() . time());
session_set($addsalt, 1200);
$x          = new \core\HieroGlyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset=utf-8>
    <meta http-equiv=X-UA-Compatible content="IE=edge,chrome=1">
    <meta name=viewport content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <link rel=icon href=favicon.ico>
    <title></title>
    <style>
        html {
            background: #f5f7f9;
        }
    </style>
    <link href=static/css/chunk-024f47e0.5c5c944a.css rel=prefetch>
    <link href=static/css/chunk-0ab830e0.e2611c64.css rel=prefetch>
    <link href=static/css/chunk-0eb1d266.de96ec80.css rel=prefetch>
    <link href=static/css/chunk-107f1596.3d9f69bd.css rel=prefetch>
    <link href=static/css/chunk-17934af9.464788be.css rel=prefetch>
    <link href=static/css/chunk-2a71e460.b0ebe3d2.css rel=prefetch>
    <link href=static/css/chunk-329271bc.e5c5ba0c.css rel=prefetch>
    <link href=static/css/chunk-46a48471.0b8d2f46.css rel=prefetch>
    <link href=static/css/chunk-51b561b8.edf2ac7a.css rel=prefetch>
    <link href=static/css/chunk-5b122896.d85996c9.css rel=prefetch>
    <link href=static/css/chunk-6517fd41.8f6f440b.css rel=prefetch>
    <link href=static/css/chunk-7bb15daa.df853cc7.css rel=prefetch>
    <link href=static/css/chunk-89bab170.de939387.css rel=prefetch>
    <link href=static/css/chunk-966f79c6.792bcff4.css rel=prefetch>
    <link href=static/css/chunk-97e2d138.df853cc7.css rel=prefetch>
    <link href=static/css/chunk-c67c700c.f65d8262.css rel=prefetch>
    <link href=static/js/chunk-024f47e0.9e0c18ea.js rel=prefetch>
    <link href=static/js/chunk-0ab830e0.d3702a36.js rel=prefetch>
    <link href=static/js/chunk-0eb1d266.70f158b9.js rel=prefetch>
    <link href=static/js/chunk-107f1596.80d06157.js rel=prefetch>
    <link href=static/js/chunk-17934af9.0dd5925d.js rel=prefetch>
    <link href=static/js/chunk-2a71e460.c13e4df4.js rel=prefetch>
    <link href=static/js/chunk-329271bc.f8fa8f43.js rel=prefetch>
    <link href=static/js/chunk-46a48471.a30a4c1c.js rel=prefetch>
    <link href=static/js/chunk-51b561b8.e2c1e69e.js rel=prefetch>
    <link href=static/js/chunk-5b122896.b59b079a.js rel=prefetch>
    <link href=static/js/chunk-6517fd41.c98c5b0c.js rel=prefetch>
    <link href=static/js/chunk-70a6e3a8.46473a85.js rel=prefetch>
    <link href=static/js/chunk-7bb15daa.05807a5a.js rel=prefetch>
    <link href=static/js/chunk-89bab170.936426c7.js rel=prefetch>
    <link href=static/js/chunk-966f79c6.475a38ea.js rel=prefetch>
    <link href=static/js/chunk-97e2d138.3734545f.js rel=prefetch>
    <link href=static/js/chunk-c67c700c.19b26e1c.js rel=prefetch>
    <link href=static/css/app.baecda61.css rel=preload as=style>
    <link href=static/css/chunk-vendors.515032f2.css rel=preload as=style>
    <link href=static/js/app.7d3e190e.js rel=preload as=script>
    <link href=static/js/chunk-vendors.2cecce4f.js rel=preload as=script>
    <link href=static/css/chunk-vendors.515032f2.css rel=stylesheet>
    <link href=static/css/app.baecda61.css rel=stylesheet>
</head>

<body><noscript><strong>We're sorry but doesn't work properly without JavaScript enabled. Please enable it to
            continue.</strong></noscript><input id=web type=hidden name="" value={}>
    <script>var web = JSON.parse(document.querySelector("#web").value);</script>
    <div id=chatv1></div>
    <script>var hashsalt=<?php echo $addsalt_js ?>;</script>
    <script src=static/js/chunk-vendors.2cecce4f.js></script>
    <script src=static/js/app.7d3e190e.js></script>
</body>

</html>