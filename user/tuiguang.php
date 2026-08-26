<?php
$is_defend = true;
require '../includes/common.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

$title = '推广赚钱';
include 'head.php';
if ($conf['fenzhan_tg'] != 1) {
    showmsg("未开启推广图功能！", 4);
}
?>
<div class="wrapper">
    <div class="col-sm-12">
<?php
if ($userrow['power'] == 0) {
    showmsg('你没有权限使用此功能！', 3);
}
if (!$userrow['siteurl']) {
    showmsg('当前分站还未绑定域名', 3);
}
$url = parse_site_url($userrow['siteurl']) . '?' . rand(1, 999);
if ($conf['dwz_api'] != 0) {
    $result = getUrlDwz($url);
    if ($result['code'] == 0) {
        $turl = $result['url'];
    }
}

if (!isset($turl)) {
    $turl = $url;
}

function unicodeEncode($strLong)
{
    $strArr     = preg_split('/(?<!^)(?!$)/u', $strLong); //拆分字符串为数组(含中文字符)
    $resUnicode = '';
    foreach ($strArr as $str) {
        $bin_str = '';
        $arr     = is_array($str) ? $str : str_split($str); //获取字符内部数组表示,此时$arr应类似array(228, 189, 160)
        foreach ($arr as $value) {
            $bin_str .= decbin(ord($value)); //转成数字再转成二进制字符串,$bin_str应类似111001001011110110100000,如果是汉字"你"
        }
        $bin_str = preg_replace('/^.{4}(.{4}).{2}(.{6}).{2}(.{6})$/', '$1$2$3', $bin_str); //正则截取, $bin_str应类似0100111101100000,如果是汉字"你"
        $unicode = dechex(bindec($bin_str)); //返回unicode十六进制
        $_sup    = '';
        for ($i = 0; $i < 4 - strlen($unicode); $i++) {
            $_sup .= '0'; //补位高字节 0
        }
        $str = '\\u' . $_sup . $unicode; //加上 \u  返回
        $resUnicode .= $str;
    }
    return $resUnicode;
}

$tuiguangTextArr = [
    unicodeEncode('各类QQ会员、生活权益、视频会员、外卖卡券等等, 欢迎收藏！<br><br>
                                        自助下单地址：'),
    unicodeEncode('1998年马化腾创立腾讯QQ，让你注册，你不注册。 现在一个5位数的QQ几万。2003年马云说开 淘宝店不要钱，让你开店，你不开。10年后淘宝造就了无数个亿万富翁。2009年曹国 伟创立微博，让你开通，你不开。如今一个微博搞笑排行榜年净赚1500万，2018年我 给你一个刷业务网站，再不好好珍惜的话，好好想想你还会错过什么？<br><br>
                                        自助下单地址：'),
    unicodeEncode('有时候你想约个炮，却不小心谈了场恋爱。有时候你想好好谈个恋爱，却发现只是约了个炮，世界那么大，床却那么小， 床上的两个人曾经那么好，却不能到老。我喜欢牵了手就能成婚的故事，却活在了一个上了床也没有结果的时代！ 有些人总埋怨泡不上妞、回头看看你的QQ，不是QQ会员，也不是QQ黄钻！谁会跟你走？在这个扣扣上遍地是会员， 空间满屏是黄钻的年代，不如加入我们吧！代唰欢迎您的加盟，会员钻全网最低价，还能免费抽年费，运气好还能成永久成绝版，价格就像我们宣传语那么直白！<br><br>自助下单地址：'),
    unicodeEncode('还在互赞QQ名片？空间说说？空间留言？多费时间呀？给你们个网站无需注册，直接下单！
自助下单地址：'),
    unicodeEncode('[QQ红包]恭喜发财<br>一个能购买美食外卖卡券的网站价格低到冰点！！！现在老板疯了，疯狂送福利中，你还不赶快来下一单？还能搭建分站赚零花钱！这么便宜实惠还有优质售后的平台确定不来？<br><br>
                                        自助下单地址：'),
]

?>
            <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title"><b>推广赚钱</b></h3></div>
                <div class="panel-body">
                    <p>① 首先在 <a href="shoplist.php" class="label label-primary">商品管理</a> 一键提升价格，增加提成（建议提成比例不要超过10），也可不提升价格薄利多销，根据自己需求进行选择！</p>
                    <p>② 将以下图片保存至本地或者复制文字广告，在QQ好友、QQ群、QQ空间、微信好友、微信朋友圈、贴吧、论坛等地方发表！</p>
                    <p>③ 用户扫描下面任一一张二维码或访问任一文字广告内连接均可进入您的网站，下单均可获得提成哦~</p>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
            <div class="panel-heading">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#pic" data-toggle="tab"><i class="fa fa-image"></i> 图片广告</a></li>
                <li><a href="#text" data-toggle="tab"><i class="fa fa-file-text"></i> 文字广告</a></li>
            </ul>
            <a href="javascript:void(0);" onclick="TgTips()" class="btn btn-primary btn-sm pull-right" style="top:8px;right:28px;position: absolute!important;">投稿</a>
            </div>
            <div class="panel-body">
                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade in active" id="pic">
                        <div class="row">
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片①</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./tgimg/tgimg.php?id=1&url=<?php echo $turl ?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片②</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./tgimg/tgimg.php?id=2&url=<?php echo $turl ?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片③</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./tgimg/tgimg.php?id=3&url=<?php echo $turl ?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片④</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./tgimg/tgimg.php?id=4&url=<?php echo $turl ?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片⑤</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./tgimg/tgimg.php?id=5&url=<?php echo $turl ?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片⑥</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./tgimg/tgimg.php?id=6&url=<?php echo $turl ?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade in" id="text">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告①</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-a">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-a">
                                        <script type="text/javascript">
                                        document.write(unescape('<?php echo $tuiguangTextArr[0] ?>'));
                                        </script><?php echo $turl ?>
                                    </p>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告②</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-b">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-b">
                                        <script type="text/javascript">
                                        document.write(unescape('<?php echo $tuiguangTextArr[1] ?>'));
                                        </script><?php echo $turl ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告③</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-c">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-c">
                                        <script type="text/javascript">
                                        document.write(unescape('<?php echo $tuiguangTextArr[2] ?>'));
                                        </script><?php echo $turl ?></p>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告④</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-d">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-d">
                                        <script type="text/javascript">
                                        document.write(unescape('<?php echo $tuiguangTextArr[3] ?>'));
                                        </script><?php echo $turl ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告⑤</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-e">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-e">
                                        <script type="text/javascript">
                                        document.write(unescape('<?php echo $tuiguangTextArr[4] ?>'));
                                        </script><?php echo $turl ?></p>
                                </div>
                            </div>
                            <!-- <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告⑥</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-f">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-f" class="text-center">
                                        <script type="text/javascript">
                                        document.write(unescape(''));
                                        </script>
                                    </p>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo $cdnpublic ?>layer/2.3/layer.js"></script>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
function CunTips() {
    layer.alert('保存方法：<br><b>手机</b>：长按图片即可将图片保存至本地！(需要在浏览器才能保存哦)<br><b>电脑</b>：鼠标指针放在图片上并点击右键»图片另存为，即可保存！', {
        icon: 6,
        title: '小提示',
        skin: 'layui-layer-molv layui-layer-wxd'
    })
}
function TgTips() {
    layer.alert('若您有更好的图文广告模板，文字广告语，均可联系客服进行投稿哦~<br>期待下一个投稿的您~！', {
        icon: 6,
        title: '小提示',
        skin: 'layui-layer-molv layui-layer-wxd'
    })
}
$(document).ready(function(){
    var clipboard = new Clipboard('#copy-btn');
        clipboard.on('success', function(e) {
            layer.msg('复制成功！',{time: 1000, icon: 1});
        });
        clipboard.on('error', function(e) {
            layer.msg('复制失败！建议更换其他最新版浏览器！',{time: 2000, icon: 2});
        });
})
</script>

<?php include 'footer.php'?>