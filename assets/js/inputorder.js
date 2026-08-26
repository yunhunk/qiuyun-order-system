function get_shuoshuo_input(id,uin,km,page){
	km = km || 0;
	page = page || 1;
	if(id=='' || id==undefined)id = "edit_inputvalue2";
	if(uin=='' || uin==undefined)uin = $("#edit_inputvalue").val();
	if(uin==''){
		alert('请先填写QQ号！');return false;
	}

	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "GET",
		url : "ajax.php?act=getshuoshuo&uin="+uin+"&page="+page+"&hashsalt="+hashsalt,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				var addstr='';
				$.each(data.data, function(i, item){
					addstr+='<option value="'+item.tid+'">'+item.content+'</option>';
				});
				var nextpage = page+1;
				var lastpage = page>1?page-1:1;
				if($('#display_shuoshuo').length > 0){
					$('#display_shuoshuo').html('<div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="get_shuoshuo_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+lastpage+')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo_input(\''+id+'\');">'+addstr+'</select><div class="input-group-addon onclick" title="下一页" onclick="get_shuoshuo_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+nextpage+')"><i class="fa fa-chevron-right"></i></div></div>');
				}
				else{
					$('#edit_inputsname').append('<div class="form-group" id="display_shuoshuo"><div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="get_shuoshuo_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+lastpage+')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo_input(\''+id+'\');">'+addstr+'</select><div class="input-group-addon onclick" title="下一页" onclick="get_shuoshuo_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+nextpage+')"><i class="fa fa-chevron-right"></i></div></div></div>');
				}
				set_shuoshuo_input(id);
			}else{
				alert(data.msg);
			}
		} 
	});
}

function set_shuoshuo_input(id){
	var shuoid = $('#shuoid').val();
	$('#'+id).val(shuoid);
}

function get_rizhi_input(id,uin,km,page){
	km = km || 0;
	page = page || 1;
	if(id=='' || id==undefined)id = "edit_inputvalue2";
	if(uin=='' || uin==undefined)uin = $("#edit_inputvalue").val();
	if(uin==''){
		alert('请先填写QQ号！');return false;
	}
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "GET",
		url : "ajax.php?act=getrizhi&uin="+uin+"&page="+page+"&hashsalt="+hashsalt,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				var addstr='';
				$.each(data.data, function(i, item){
					addstr+='<option value="'+item.blogId+'">'+item.title+'</option>';
				});
				var nextpage = page+1;
				var lastpage = page>1?page-1:1;
				if($('#show_rizhi').length > 0){
					$('#show_rizhi').html('<div class="input-group"><div class="input-group-addon onclick" onclick="get_rizhi_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+lastpage+')"><i class="fa fa-chevron-left"></i></div><select id="blogid" class="form-control" onchange="set_rizhi_input(\''+id+'\');">'+addstr+'</select><div class="input-group-addon onclick" onclick="get_rizhi_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+nextpage+')"><i class="fa fa-chevron-right"></i></div></div>');
				}else{
					if(km==1){
						$('#km_edit_inputsname').append('<div class="form-group" id="show_rizhi"><div class="input-group"><div class="input-group-addon onclick" onclick="get_rizhi_input(\''+id+'\',$(\'#km_edit_inputvalue\').val(),'+km+','+lastpage+')"><i class="fa fa-chevron-left"></i></div><select id="blogid" class="form-control" onchange="set_rizhi_input(\''+id+'\');">'+addstr+'</select><div class="input-group-addon onclick" onclick="get_rizhi_input(\''+id+'\',$(\'#km_edit_inputvalue\').val(),'+km+','+nextpage+')"><i class="fa fa-chevron-right"></i></div></div></div>');
					}else{
						$('#edit_inputsname').append('<div class="form-group" id="show_rizhi"><div class="input-group"><div class="input-group-addon onclick" onclick="get_rizhi_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+lastpage+')"><i class="fa fa-chevron-left"></i></div><select id="blogid" class="form-control" onchange="set_rizhi_input(\''+id+'\');">'+addstr+'</select><div class="input-group-addon onclick" onclick="get_rizhi_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+nextpage+')"><i class="fa fa-chevron-right"></i></div></div></div>');
					}
				}
				set_rizhi_input(id);
			}else{
				alert(data.msg);
			}
		} 
	});
}
function set_rizhi_input(id){
	var blogid = $('#blogid').val();
	$('#'+id).val(blogid);
}

function getsongid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('.qq.com')<0){alert('请输入正确的歌曲的分享链接！');return false;}
	try{
		var songid = songurl.split('s=')[1].split('&')[0];
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的歌曲的分享链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
}
function getkuaishouid_input(){
	var kuauishouurl=$("#edit_inputvalue").val();
	
	if (kuauishouurl.indexOf('http')!==(-1) && kuauishouurl.indexOf('复制')!==(-1)) {
      var kuauishouurl="http"+kuauishouurl.split("http")[1].split("复制")[0];
      kuauishouurl=kuauishouurl.replace(/^\s*|\s*$/g,"");
    }
    else if (kuauishouurl.indexOf('http')!==(-1)) {
      var kuauishouurl="http"+kuauishouurl.split("http")[1];
      kuauishouurl=kuauishouurl.replace(/^\s*|\s*$/g,"");
    }
    else{
       return alert('请输入正确的快手作品链接！');
    }

	if(kuauishouurl==''){alert('请确保作品链接不能为空！');return false;}
	if(kuauishouurl.indexOf('http')<0){alert('请输入正确的作品链接！');return false;}
	if(kuauishouurl.indexOf('/s/')>0){
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "ajax.php?act=getkuaishou",
			data : {url:kuauishouurl},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					$('#edit_inputvalue').val(data.authorid);$('#edit_inputvalue').attr('disabled',true);
					if($('#edit_inputvalue2').length>0){
						$('#edit_inputvalue2').val(data.videoid);
						$('#edit_inputvalue2').attr('disabled',true);
					}
					layer.msg('ID获取成功！提交下单即可');
				}else{
					alert(data.msg);return false;
				}
			}
		});
	}else{
		try{
			if(kuauishouurl.indexOf('userId=')>0){
				var authorid = kuauishouurl.split('userId=')[1].split('&')[0];
			}else{
				var authorid = kuauishouurl.split('photo/')[1].split('/')[0];
			}
			if(kuauishouurl.indexOf('photoId=')>0){
				var videoid = kuauishouurl.split('photoId=')[1].split('&')[0];
			}else{
				var videoid = kuauishouurl.split('photo/')[1].split('/')[1].split('?')[0];
			}

			layer.msg('ID获取成功！提交下单即可');
		}catch(e){
			alert('请输入正确的快手作品链接！');return false;
		}
		$('#edit_inputvalue').val(authorid);
		if($('#edit_inputvalue2').length>0){
			$('#edit_inputvalue2').val(videoid);
			$('#edit_inputvalue2').attr('disabled',true);
		}
	}
}
function get_kuaishou_input(id,ksid){
	getkuaishouid_input()
}
function gethuoshanid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('/s/')>0){
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "ajax.php?act=gethuoshan",
			data : {url:songurl},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					$('#edit_inputvalue').val(data.songid);
					$('#edit_inputvalue').attr('disabled',true);
					layer.msg('ID获取成功！提交下单即可');
				}else{
					alert(data.msg);return false;
				}
			}
		});
	}else{
		try{
			if(songurl.indexOf('video/')>0){
				var songid = songurl.split('video/')[1].split('/')[0];
			}else if(songurl.indexOf('item/')>0){
				var songid = songurl.split('item/')[1].split('/')[0];
			}else if(songurl.indexOf('room/')>0){
				var songid = songurl.split('room/')[1].split('/')[0];
			}else{
				var songid = songurl.split('user/')[1].split('/')[0];
			}
			$('#edit_inputvalue').attr('disabled',true);
			layer.msg('ID获取成功！提交下单即可');
		}catch(e){
			alert('请输入正确的链接！');return false;
		}
		$('#edit_inputvalue').val(songid);
	}
}
function getdouyinid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('/v.douyin.com/')>0 || songurl.indexOf('/s/')>0){
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "ajax.php?act=getdouyin",
			data : {url:songurl},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					$('#edit_inputvalue').val(data.videoid);
					$('#edit_inputvalue').attr('disabled',true);
					layer.msg('ID获取成功！提交下单即可');
				}else{
					alert(data.msg);return false;
				}
			}
		});
	}else{
	try{
		if(songurl.indexOf('video/')>0){
			var songid = songurl.split('video/')[1].split('/')[0];
		}else if(songurl.indexOf('music/')>0){
			var songid = songurl.split('music/')[1].split('/')[0];
		}else{
			var songid = songurl.split('user/')[1].split('/')[0];
		}
		$('#edit_inputvalue').attr('disabled',true);
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
	}
}

function gettoutiaoid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	try{
		if(songurl.indexOf('user/')>0){
			var songid = songurl.split('user/')[1].split('/')[0];
		}else{
			var songid = songurl.split('profile/')[1].split('/')[0];
		}
		$('#edit_inputvalue').attr('disabled',true);
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
}
function getweishiid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('.qq.com')<0){alert('请输入正确的链接！');return false;}
	try{
		if(songurl.indexOf('feed/')>0){
			var songid = songurl.split('feed/')[1].split('/')[0];
		}else if(songurl.indexOf('personal/')>0){
			var songid = songurl.split('personal/')[1].split('/')[0];
		}else{
			var songid = songurl.split('id=')[1].split('&')[0];
		}
		$('#edit_inputvalue').attr('disabled',true);
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
}
function getxiaohongshuid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('/t.cn/')>0){
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "ajax.php?act=getxiaohongshu",
			data : {url:songurl},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					$('#edit_inputvalue').val(data.songid);
					layer.msg('ID获取成功！提交下单即可');
				}else{
					alert(data.msg);return false;
				}
			}
		});
	}else{
	if(songurl.indexOf('xiaohongshu.com')<0 && songurl.indexOf('pipix.com')<0){alert('请输入正确的链接！');return false;}
	try{
		var songid = songurl.split('item/')[1].split('?')[0];
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的链接！');return false;
	}
	}
	$('#edit_inputvalue').val(songid);
}
function getbilibiliid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('bilibili.com')<0){alert('请输入正确的视频链接！');return false;}
	try{
		var songid = songurl.split('video/av')[1].split('/')[0];
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的视频链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
}
function getzuiyouid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('izuiyou.com')<0){alert('请输入正确的帖子链接！');return false;}
	try{
		var songid = songurl.split('detail/')[1].split('?')[0];
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的帖子链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
}
function getmeipaiid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('meipai.com')<0){alert('请输入正确的视频链接！');return false;}
	try{
		var songid = songurl.split('media/')[1].split('?')[0];
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的视频链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
}
function getquanminid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('hao222.com')<0){alert('请输入正确的视频链接！');return false;}
	try{
		var songid = songurl.split('vid=')[1].split('&')[0];
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的视频链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
}
function getmeituid_input(){
	var songurl=$("#edit_inputvalue").val();
	if(songurl==''){alert('请确保每项不能为空！');return false;}
	if(songurl.indexOf('meitu.com')<0){alert('请输入正确的视频链接！');return false;}
	try{
		var songid = songurl.split('feed_id=')[1].split('&')[0];
		layer.msg('ID获取成功！提交下单即可');
	}catch(e){
		alert('请输入正确的视频链接！');return false;
	}
	$('#edit_inputvalue').val(songid);
}
function getCommentList_input(id,aweme_id,km,page){
	km = km || 0;
	page = page || 1;
	if(aweme_id==''){
		alert('请先填写抖音作品ID！');return false;
	}
	if(aweme_id.length != 19){
		alert('抖音作品ID填写错误');return false;
	}
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "GET",
		url : "https://api.douyin.qlike.cn/api.php?act=getCommentList_input&aweme_id="+aweme_id+"&page="+page,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.total != 0){
				var addstr='';
				$.each(data.comments, function(i, item){
					addstr+='<option value="'+item.cid+'">[昵称 => '+item.user.nickname+'][内容 => '+item.text+'][赞数量=>'+item.digg_count+']</option>';
				});
				var nextpage = page+1;
				var lastpage = page>1?page-1:1;
				if($('#show_shuoshuo').length > 0){
					$('#show_shuoshuo').html('<div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="getCommentList_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+lastpage+')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo_input(\''+id+'\');">'+addstr+'</select><div class="input-group-addon onclick" title="下一页" onclick="getCommentList_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+nextpage+')"><i class="fa fa-chevron-right"></i></div></div>');
				}else{
					if(km==1){
						$('#km_edit_inputsname').append('<div class="form-group" id="show_shuoshuo"><div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="getCommentList_input(\''+id+'\',$(\'#km_edit_inputvalue\').val(),'+km+','+lastpage+')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo_input(\''+id+'\');">'+addstr+'</select><div class="input-group-addon onclick" title="下一页" onclick="getCommentList_input(\''+id+'\',$(\'#km_edit_inputvalue\').val(),'+km+','+nextpage+')"><i class="fa fa-chevron-right"></i></div></div></div>');
					}else{
						$('#edit_inputsname').append('<div class="form-group" id="show_shuoshuo"><div class="input-group"><div class="input-group-addon onclick" title="上一页" onclick="getCommentList_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+lastpage+')"><i class="fa fa-chevron-left"></i></div><select id="shuoid" class="form-control" onchange="set_shuoshuo_input(\''+id+'\');">'+addstr+'</select><div class="input-group-addon onclick" title="下一页" onclick="getCommentList_input(\''+id+'\',$(\'#edit_inputvalue\').val(),'+km+','+nextpage+')"><i class="fa fa-chevron-right"></i></div></div></div>');
					}
				}
				set_shuoshuo_input(id);
			}else{
				alert('您的作品好像没人评论');
			}
		},
		error: function(a) {
			layer.close(ii);
			alert('网络错误，请稍后重试');
		}
	});
}

function checkInput_input() {
	if($("#edit_inputname").html() == '快手ID'||$("#edit_inputname").html() == '快手作品链接'||$("#edit_inputname").html() == '快手视频连接'||$("#edit_inputname").html() == '快手ＩＤ'||$("#edit_inputname").html() == '快手用户ID'||$("#edit_inputname").html().indexOf('ks')>=0||$("#edit_inputname").html().indexOf('块手')>=0||$("#edit_inputname").html().indexOf('K手')>=0){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getkuaishouid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '歌曲ID'||$("#edit_inputname").html() == '歌曲ＩＤ'||$("#edit_inputname").html() == '全民K歌歌曲链接'||$("#edit_inputname").html() == '歌曲链接'||$("#edit_inputname").html() == 'K歌歌曲链接'){
		if($("#edit_inputvalue").val().indexOf("s=") ==(-1)){
			if($("#edit_inputvalue").val().length != 12 && $("#edit_inputvalue").val().length != 16){
				alert('请输入正确的K歌作品链接，会自动获取哦！');
				return false;
			}
		}
		else if($("#edit_inputvalue").val()!=''){
			getsongid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '火山ID'||$("#edit_inputname").html() == '火山作品ID'||$("#edit_inputname").html() == '火山视频ID'||$("#edit_inputname").html() == '火山ＩＤ'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			gethuoshanid_input();
		}
	}
	else if($("#edit_inputname").html() == '抖音ID'||$("#edit_inputname").html() == '抖音作品ID'||$("#edit_inputname").html() == '抖音视频ID'||$("#edit_inputname").html() == '抖音ＩＤ'||$("#edit_inputname").html() == '抖音主页ID'||$("#edit_inputname").html() == '抖音作品链接'||$("#edit_inputname").html() == '抖音视频链接'||$("#edit_inputname").html().indexOf('抖喑')>=0||$("#edit_inputname").html().indexOf('dy')>=0||$("#edit_inputname").html().indexOf('DY')>=0){

		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getdouyinid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '微视ID'||$("#edit_inputname").html() == '微视作品ID'||$("#edit_inputname").html() == '微视ＩＤ'||$("#edit_inputname").html() == '微视主页ID'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getweishiid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '头条ID'||$("#edit_inputname").html() == '头条ＩＤ'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			gettoutiaoid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '小红书ID'||$("#edit_inputname").html() == '小红书作品ID'||$("#edit_inputname").html() == '皮皮虾ID'||$("#edit_inputname").html() == '皮皮虾作品ID'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getxiaohongshuid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '美拍ID'||$("#edit_inputname").html() == '美拍ＩＤ'||$("#edit_inputname").html() == '美拍作品ID'||$("#edit_inputname").html() == '美拍视频ID'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getmeipaiid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '哔哩哔哩视频ID'||$("#edit_inputname").html() == '哔哩哔哩ID'||$("#edit_inputname").html() == '哔哩视频ID'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getbilibiliid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '最右帖子ID'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getzuiyouid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '全民视频ID'||$("#edit_inputname").html() == '全民小视频ID'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getquanminid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname").html() == '美图作品ID'||$("#edit_inputname").html() == '美图视频ID'){
		if($("#edit_inputvalue").val()!='' && $("#edit_inputvalue").val().indexOf('http')>=0){
			getmeituid_input();
		}
		else{
			alert('请输入正确的链接');
		}
	}
	else if($("#edit_inputname2").html() == '说说ID' ||($("#edit_inputname2").html()=='说说ＩＤ')) {
		get_shuoshuo_input();
	}
}
