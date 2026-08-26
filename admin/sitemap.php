<?php
include '../includes/common.php';
$title = '网站地图生成工具';
checkLogin();

checkAuthority('super');

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == "sitemap") {
    $sitemap_type       = (int) trim(daddslashes(strip_tags($_POST['sitemap_type'])));
    $sitemap_urlpattern = trim(daddslashes(strip_tags($_POST['sitemap_urlpattern'])));
    $sitemap_priority   = floatval(daddslashes(strip_tags($_POST['sitemap_priority'])));
    if (empty($sitemap_urlpattern)) {
        exit('{"code":-1,"msg":"生成链接规则不能为空！"}');
    } else {

        $xml = <<<'xml'
<?xml version="1.0" encoding="utf-8"?>
	<urlset>
xml;
        if (!is_dir(ROOT . 'spider')) {
            mkdir(ROOT . 'spider');
        }

        if ($sitemap_type == 1) {
            $count = $DB->count("SELECT count(*) FROM pre_class WHERE active='1'");
            $rs    = $DB->query("SELECT * FROM pre_class WHERE active='1' order by sort asc");
            if ($count > 0 && $rs) {
                $data = $DB->fetchAll($rs);
            }

            foreach ($data as $row) {
                $url = $sitemap_urlpattern;
                $url = str_replace('[siteurl]', $_SERVER['HTTP_HOST'], $url);
                $url = str_replace('[cid]', $row['cid'], $url);
                $xml .= "\n\t\t<url>\n";
                $xml .= "\t\t\t<loc>" . $url . "</loc>\n";
                $xml .= "\t\t\t<lastmod>" . date("Y-m-d") . "</lastmod>\n";
                $xml .= "\t\t\t<changefreq>daily</changefreq>\n";
                $xml .= "\t\t\t<priority>" . $sitemap_priority . "</priority>\n";
                $xml .= "\t\t</url>";
            }

            $xml .= "\n\t</urlset>";

            $filePath = ROOT . 'spider/sitemap.xml';
        } elseif ($sitemap_type == 3) {
            $count = $DB->count("SELECT count(*) FROM pre_message WHERE active='1'");
            $rs    = $DB->query("SELECT id FROM pre_message WHERE active='1' order by sort asc");
            if ($count > 0 && $rs) {
                $data = $DB->fetchAll($rs);
            }

            $url = $sitemap_urlpattern;
            $url = str_replace('[siteurl]', $_SERVER['HTTP_HOST'], $url);
            $url = str_replace('[aid]', 'index', $url);
            $url = str_replace('[cid]', 'index', $url);
            $xml .= "\n\t\t<url>\n";
            $xml .= "\t\t\t<loc>" . $url . "</loc>\n";
            $xml .= "\t\t\t<lastmod>" . date("Y-m-d") . "</lastmod>\n";
            $xml .= "\t\t\t<changefreq>daily</changefreq>\n";
            $xml .= "\t\t\t<priority>" . $sitemap_priority . "</priority>\n";
            $xml .= "\t\t</url>";

            foreach ($data as $row) {
                $url = $sitemap_urlpattern;
                $url = str_replace('[siteurl]', $_SERVER['HTTP_HOST'], $url);
                $url = str_replace('[aid]', $row['id'], $url);
                $url = str_replace('[cid]', $row['id'], $url);
                $xml .= "\n\t\t<url>\n";
                $xml .= "\t\t\t<loc>" . $url . "</loc>\n";
                $xml .= "\t\t\t<lastmod>" . date("Y-m-d") . "</lastmod>\n";
                $xml .= "\t\t\t<changefreq>daily</changefreq>\n";
                $xml .= "\t\t\t<priority>" . $sitemap_priority . "</priority>\n";
                $xml .= "\t\t</url>";
            }

            $xml .= "\n\t</urlset>";

            $filePath = ROOT . 'spider/sitemap_message.xml';
        } else {
            exit('{"code":-1,"msg":"生成类型错误"}');
        }

        $fileName = explode('spider/', $filePath)[1];

        if (file_put_contents($filePath, $xml)) {
            $result = array("code" => 0, "msg" => '生成' . $fileName . '网站地图成功， 本次共' . $count . '个页面！', "data" => $data);
        } else {
            $result = array("code" => -1, "msg" => '生成网站地图失败，请检查文件权限！', "filePath" => $filePath);
        }
        exit(json_encode($result));
    }
}

echo "\n<!--
# 网站地图生成SEO优化小工具V1.01 By 星河
-->\n";

include './head.php';

echo ' <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px">

	  <div class="block">
        <div class="block-title"><h3 class="panel-title">' . $title . '</h3></div>
        <div class="">
		<div class="alert alert-info">
            使用此功能可一键生成网站XML地图[sitemap.xml]，方便站长自动推送网站页面，提升SEO效益
        </div>
		    <div class="form-group hide">
				<div class="input-group"><div class="input-group-addon">站点URL</div>
				<input type="text" value="" class="form-control" placeholder="http://www.qq.com/" required/>
				<div class="input-group-addon" onclick="checkurl()"><small>检测连通性</small></div>
			</div></div>
			<div class="form-group">
				<div class="input-group"><div class="input-group-addon">生成类型</div>
				<select class="form-control" id="sitemap_type">
				<option value="1">商品分类(仅上架中的)</option>
				<option value="2">商品列表(仅上架中的)</option>
				<option value="3">文章列表(仅已显示的)</option>
				</select>
			</div></div>
			<div class="form-group">
				<div class="input-group"><div class="input-group-addon">URL规则</div>
				<input type="text" id="sitemap_urlpattern" value="http://[siteurl]/class/[cid].html" class="form-control" placeholder="http://[siteurl]/class/[cid].html" required/>
			</div></div>
			<div class="form-group">
				<div class="input-group"><div class="input-group-addon">优先权值</div>
				<input type="text" id="sitemap_priority" value="" class="form-control" placeholder="优先权标签，优先权值0.0-1.0" required/>
			</div></div>
            <p><a onclick="submit()" class="btn btn-primary form-control"/>提交操作</a></p>
        </div>
      </div>
    </div>
  </div>
<script src="//cdn.staticfile.org/layer/2.3/layer.js"></script>
<script>
function submit() {
	var sitemap_type		= $("#sitemap_type").val();
	var sitemap_urlpattern	= $("#sitemap_urlpattern").val();
	var sitemap_priority	= $("#sitemap_priority").val();
	if(sitemap_urlpattern.indexOf(\'http\')>=0){
		var ii = layer.load(2, {shade:[0.1,\'#fff\']});
		$.ajax({
			type : "POST",
			url : "?act=sitemap",
			data : {
				sitemap_type:sitemap_type,
				sitemap_urlpattern:sitemap_urlpattern,
				sitemap_priority:sitemap_priority
			},
			dataType : \'json\',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					layer.msg(data.msg);
				}
				else{
					layer.alert(data.msg);
				}
			} ,
			error:function(data){
				layer.close(ii);
				layer.msg(\'目标URL连接超时\');
				return false;
			}
		});
	}else{
		layer.alert(\'URL规则必须以 http:// 开头，以 / 结尾\');
	}
}
</script>';

include 'footer.php';
