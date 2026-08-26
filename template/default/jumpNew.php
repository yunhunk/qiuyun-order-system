<?php
$sitename = '站点温馨提醒';
$msg[1]   = '最困难的事情就是认识自己。——希腊';
$msg[2]   = '有勇气承担命运这才是英雄好汉。——黑塞';
$msg[3]   = '生命不可能有两次，但许多人连一次也不善于度过。——吕凯特';
$msg[4]   = '意志是一个强壮的盲人，倚靠在明眼的跛子肩上。——叔本华';
$msg[5]   = '坚持意志伟大的事业需要始终不渝的精神。——伏尔泰';
$msg[6]   = '古之立大事者，不惟有超世之才，亦必有坚忍不拔之志。——苏轼';
$msg[7]   = '不要回避苦恼和困难，挺起身来向它挑战，进而克服它。——池田大作';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible"/>
        <meta content="webkit" name="renderer"/>
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
        <title>
            <?php echo $sitename ?>
        </title>
    </head>
    <body>
        <center>
            <br/>
            <br/>
            <br/>
            <h3>
                站点温馨提醒！
            </h3>
            <b>
                <br/>
                该浏览器暂不支持此网站
                <br/>
                请点击右上方按钮使用其他外部浏览器打开
            </b>
        </center>
        <br/>
        <br/>
        <br/>
        <br/>
        <?php echo $msg[rand(1, 7)] ?>
    </body>
</html>