<?php
/**
 * 自助增加域名
 **/
include "../includes/common.php";
$title = '自助增加域名';
include './head.php';
if (!empty($userrow['siteurl2'])) {
    showmsg('你的网站域名绑定已达上限', 3);
}

if ($isLogin2 != 1) {
    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

?>
<div class="wrapper-md control">
    <div class="row row-sm">
    <div class="col-sm-12 col-md-8 col-lg-6 center-block" style="float: none;">
        <?php
if ($userrow['power'] == 0) {
    showmsg('你没有权限使用此功能！', 3);
}

if (empty($conf['fenzhan_editd_open'])) {
    showmsg('未开启自助更换域名功能', 3);
}

$siteurls = explode(',', $conf['fenzhan_domain']);
$select   = '';
foreach ($siteurls as $siteurl) {
    $select .= '<option value="' . $siteurl . '">' . $siteurl . '</option>';
}
if (empty($select)) {
    showmsg('请先到后台分站设置，填写可选分站域名', 3);
}
?>
        <div class="panel panel-default">
            <form class="form-horizontal devform">
                <ul id="myTab" class="nav nav-tabs">
                        <li style="width:100%;text-align: center"><a href="#domain2" data-toggle="tab">自助增加域名</a></li>
                </ul>
                <div class="panel-body">
                    <input type="hidden" name="type" value="2">

                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">域名前缀</label>
                        <div class="col-sm-8">
                            <input type="text" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" name="qz"
                                   class="form-control" required data-parsley-length="[2,8]"
                                   placeholder="输入你想要的二级前缀">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">新的域名</label>
                        <div class="col-sm-8">
                            <select name="domain" class="form-control"><?php echo $select ?></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">所需费用</label>
                        <div class="col-sm-8">
                            <span style="font-size:24px;font-weight:700;color: #f40;"
                                  id="need"><?php echo $conf['fenzhan_editd_price'] ?></span> 元
                        </div>
                    </div>
                    <div class="form-group">

                        <div class="col-sm-offset-3 col-sm-8"><input type="button" id="submit" value="提交"
                                                                     class="btn btn-success form-control"/><br/>
                        </div>
            </form>
        </div>
    </div>
</div>
</div>
<?php include './foot.php';?>
<script>
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $("input[name=type]").val($(e.target).text().includes('主') ? 1 : 2);
    });
    $("#submit").click(function () {
        const type = $("input[name=type]").val(), qz = $("input[name=qz]").val(),
            domain = $("select[name=domain]").val();
        if (qz == '') {
            layer.msg('前缀不可为空');
            return false;
        }
        layer.confirm('是否确定新增该域名？', {icon: 3}, function () {
            const ii = layer.load();
            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    type,
                    qz,
                    domain
                },
                url: "ajax.php?act=ndomain",
                success: function (data) {
                    layer.close(ii);
                    if (data.code == 0) {
                        type == 1 ? $("input[name=domain]").val(data.domain) : $("input[name=domain2]").val(data.domain)
                        $("input[name=qz]").val('');
                        layer.alert(data.msg, {icon: 1});
                        if (type == 2 && $("#domain2").is(':hidden')) {
                            $("#domain2").removeClass('hidden')
                        }
                    } else {
                        layer.alert(data.msg, {
                            icon: 2
                        });
                    }
                },
                error: function (data) {
                    layer.msg('服务器错误', {
                        icon: 2
                    });
                }
            });
        });
    });
</script>
</body>
</html>