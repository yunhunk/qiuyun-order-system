<?php

use function PHPSTORM_META\expectedArguments;

include '../includes/common.php';
$title = '系统自检中心';
checkLogin();

$scriptpath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
define('ADMIN_DIR', ROOT . trim($scriptpath, '/') . '/');

function checkDatabase()
{

    global $DB;
    $dir  = ROOT . 'install/';
    $data = [];

    $sqls  = file_get_contents($dir . 'import.sql');
    $count = 0;
    $ok    = 0;
    $error = [];
    if ($sqls) {
        $sqls = preg_replace('/;[\s]+\n/', ";\n\n", $sqls);
        $arr  = explode(";\n", $sqls);
        foreach ($arr as $key => $sql) {
            $sql = trim($sql);
            if (empty($sql)) {
                continue;
            }
            $count++;
            if ($DB->query($sql)) {
                $ok++;
            } else {
                $error[] = "第" . ($key + 1) . "行错误：" . $DB->error() . "[SQL：{$sql}]";
            }
        }
        $msg = "共{$count}条语句，成功执行{$ok}条<br/>" . ($count > 0 && $ok == 0 && count($error) > 0 ? "" . implode("\n|-", $error) : '');
    } else {
        $msg = "共0条语句, 成功执行0条";
    }

    $data[] = [
        'name' => '升级Plus数据包',
        'msg'  => $msg,
    ];

    if (is_dir($dir)) {
        $files = scandir($dir);
        if ($files) {
            foreach ($files as $key => $name) {
                if ($name == '.' || $name == '..') {
                    continue;
                }

                if (preg_match('/update_(\d+)\.sql/', $name, $match) && $match[1] < 2000) {
                    $sqls  = file_get_contents($dir . $name);
                    $count = 0;
                    $ok    = 0;
                    $error = [];
                    if ($sqls) {
                        $sqls = preg_replace('/;[\s]+\n/', ";\n\n", $sqls);
                        $arr  = explode(";\n", $sqls);
                        foreach ($arr as $key => $sql) {
                            $sql = trim($sql);
                            if (empty($sql)) {
                                continue;
                            }
                            $count++;
                            if ($DB->query($sql)) {
                                $ok++;
                            } else {
                                $error[] = "第" . ($key + 1) . "行错误：" . $DB->error() . "[SQL：{$sql}]";
                            }
                        }
                        $msg = "共{$count}条语句，成功执行{$ok}条<br/>" . ($count > 0 && $ok == 0 && count($error) > 0 ? "" . implode("\n|-", $error) : '');
                    } else {
                        $msg = "共0条语句，成功执行0条";
                    }

                    $data[] = [
                        'name' => $name,
                        'msg'  => $msg,
                    ];
                }
            }
        }
    } else {
        $data[] = [
            'name' => '提示',
            'msg'  => '未找到数据库更新文件目录[install]',
        ];
    }

    return $data;
}

$act = isset($_GET['act']) ? input('get.act', 1) : null;
if ($act == "checkSystem") {
    $step         = intval(input('post.step', 1));
    $check_option = input('post.check_option', 1);
    if ($step > 1) {
        $result = ['code' => 1, 'msg' => 'succ', 'data' => [], 'speed' => 2500];
    } else {
        $data = [];
        if (isset($check_option['sql']) && $check_option['sql'] == 1) {
            $data[] = [
                'title' => '数据库修复',
                'data'  => checkDatabase(),
            ];
        }
        $result = ['code' => 0, 'msg' => 'succ', 'step' => $step + 1, 'data' => $data, 'speed' => 2500];
    }
    exit(json_encode($result));
}

include './head.php';

echo '
    <link rel="stylesheet" href="./assets/css/mtlog_v1.css?v=' . $jsver . '">
    <style>
    .cmckb-xs span:last-child {
        padding-left: 4px;
        line-height: 18px;
    }
    </style>
    <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px">
      <div class="block">
        <div class="block-title"><h3 class="panel-title"><?php echo $title?></h3></div>
        <div class="">
        <div class="alert alert-info">
        该工具可解决数据库SQL语句未正常执行更新导致的系统功能错误, <span style="color:red;">如出现数据库错误提示请无视</span><br>
        </div>
        <div class="alert" id="check-msg" style="background-color: #F8F8FF;color: #0c69c6; min-height: 320px; overflow: scroll;  overflow-x: visible;">
            <main class="panel_list_page met-log py-4 py-md-5">
            <ul class="met-log-list list-unstyled pl-md-5 ml-md-5" id="check-list">

            </ul>
            </main>
        </div>
        <form id="optionForm" role="form">
        <div class="form-group">
              <label class="col-sm-2 control-label">修复选项</label>
              <div class="col-sm-10" style="margin-top: -4px;  padding-left: 0;">
                  <input class="inp-cmckb-xs" name="check_option[sql]" checked="checked" id="check_option_sql" type="checkbox" value="1" onclick="chenmObj.setVal(this)" style="display: none;"/><label class="cmckb-xs" for="check_option_sql"><span><svg width="12px" height="10px"><use xlink:href="#checkSvg"></use></svg></span><span>数据库</span></label>
              </div>
        </div><br/>
        </form>
        <!--SVG Sprites-->
        <svg class="inline-svg">
          <symbol id="checkSvg" viewbox="0 0 12 10">
            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
          </symbol>
        </svg>
        <p class=""><a class="btn btn-primary form-control" id="checkBtn">点击开始修复</a></p>
        </div>
      </div>
    </div>
  </div>
';

echo <<<'scriptText'
<script type="text/javascript">
var chenmObj = {
    checkSystem: function(step) {
        $("#checkBtn").attr('disabled', true);
        if (step == 1) {
            $("#check-list").html(`<li class="position-relative pl-3 pl-md-4 py-3 py-md-4">
                <span class="rounded-circle p-1 position-absolute bg-white text-center font-weight-bold">&sdot;</span>
                <dl class="mb-0">
                    <dt><h4 class="title">开始修复</h4></dt>
                    <dd class="card p-3 mb-0 mt-3 bg-light oya met-scrollbar">
                    <div class="met-editor font-size-14 text-muted"><p>初始化：修复工具初始化成功！</p></div>
                    </dd>
                </dl>
            </li>`);
        }

        var optionForm = $("#optionForm").serialize();
        if ("" != optionForm) {
            optionForm = "&" + optionForm;
        }
        $("#checkBtn").html('Loading');
        $.ajax({
            type: 'POST',
            url: '?act=checkSystem',
            dataType: 'json',
            data: "step=" + step + optionForm,
            success: function(data) {
                if (data.code >= 0) {
                    if (data.code == 0) {
                        $.each(data.data, function(i, item) {
                            chenmObj.getList(item);
                        });
                        setTimeout(function() {
                            chenmObj.checkSystem(data.step);
                        }, data.speed ? data.speed : 1500);
                    }
                    else if (data.code == 1) {
                        $("#check-list").append(`<li class="position-relative pl-3 pl-md-4 py-3 py-md-4">
                            <span class="rounded-circle p-1 position-absolute bg-white text-center font-weight-bold">&sdot;</span>
                            <dl class="mb-0">
                                <dt><h4 class="title">检测完成</h4></dt>
                                <dd class="card p-3 mb-0 mt-3 bg-light oya met-scrollbar">
                                <div class="met-editor font-size-14 text-muted"><p style="color:green">修复执行已完成！</p><p style="color:red">该修复工具只是执行一遍历史的所有数据库SQL更新语句，如遇报错请忽略</p><p style="color:red">如果多次没有修复你的问题，请下载更新包覆盖后再修复数据库</p></div>
                                </dd>
                            </dl>
                        </li>`);
                        $("#checkBtn").attr('disabled', false);
                        $("#check-msg").scrollTop($("#check-list").height(), 150);
                        $("#checkBtn").html('重新检测一遍');
                    }
                } else {
                    layer.alert(data.msg);
                    $("#checkBtn").attr('disabled', false);
                    $("#checkBtn").html('重新检测一遍');
                }
            },
            error: function(data) {
                layer.msg('服务器错误或请求超时');
                $("#checkBtn").attr('disabled', false);
                return false;
            }
        });
    },
    getList: function(data) {
        var html = '<li class="position-relative pl-3 pl-md-4 py-3 py-md-4"><span class="rounded-circle p-1 position-absolute bg-white text-center font-weight-bold">&sdot;</span><dl class="mb-0"><h4 class="title">' + data.title + '</h4></dt><dd class="card p-3 mb-0 mt-3 bg-light oya"><div class="font-size-14 text-muted">';
        $.each(data.data, function(i, item) {
            html += '<p>' + (i + 1) + '.&nbsp;' + item.name + '&nbsp;&nbsp; ' + item.msg  + '</p>';
        });
        html += '</div></dd></dl></li>';
        $("#check-list").append(html);
        $("#check-msg").scrollTop($("#check-list").height(), 150);
    },
    setVal: function(el) {
        if (el.value == '1') {
            $(el).val('0');
        } else {
            $(el).val('1');
        }
    }
};
$(document).on("click", "#checkBtn", function(event) {
	event.preventDefault();
	/* Act on the event */
    chenmObj.checkSystem(1);
});
</script>
scriptText;
