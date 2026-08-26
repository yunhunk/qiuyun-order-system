 setTimeout(function (){
    var $Date=new Date();
		var $day=$Date.getDate();
		var $month=$Date.getMonth()+1;
		var today=$month+"月"+$day+"日";
	  var hotBtn='<div align="center" style="color:red;font-size: 3;"> <b>'+today+' <a onclick="getHotlist()" href="#hot" id="hotshop" data-toggle="tab">平台热销商品</a></b></div><br>';
    var hotHtml=' <!--今日推荐-->'
               + '<div class="tab-pane fade fade-up in" id="hot">'
               + '   <div class="panel panel-body" id="hotlist">'
               + '   <div class="list-group-item text-center"><h4 style="color:red">以下为今日推荐商品欢迎选购(｡･ω･｡)<h4></div><br>'
               + '   </div>'
               + '</div> '
               + '<!--今日推荐-->';
   var dom=$('<div class="block-content tab-content">');
   if (dom.length>0) {
   	  if ($('#hotshop').length>0) {$('#hotshop').parent().remove();}
   	  if ($('#hot').length>0) {$('#hot').remove();}
   	   $('#shop').before(hotBtn);
   	   $('#shop').after(hotHtml);
   }
   else{
   	  var dom2=$('<div id="myTabContent" class="tab-content">');
			if (dom2.length>0) {
				  if ($('#hotshop').length>0) {$('#hotshop').parent().remove();}
   	      if ($('#hot').length>0) {$('#hot').remove();}
   	      $('#onlinebuy').before(hotBtn);
   	      $('#onlinebuy').after(hotHtml);
			}
	    else{
	    	console.log("该模板未匹配到预设节点，无法加入今日推荐功能，请联系作者反馈！");
	    }    
   }
   console.log("今日推荐商品插件运行完毕！");

 },500);


function getHotlist(){
   $("#tid").val('');
		$("#tid").empty();
		if($('a[href="#shop"]').length>0){
			$('a[href="#shop"]').parents('li').removeClass('active');
		}
		else{
			$('a[href="#onlinebuy"]').parents('li').removeClass('active');
		}
   $("#hotlist").html('<div class="list-group-item text-center"><h4 style="color:red">以下为今日推荐商品欢迎选购(｡･ω･｡)<h4></div><br>');
	 $("#tid").append('<option value="0">请选择今日推荐商品</option>');
	  var ii = layer.load(0, {shade:[0.1,'#fff']});
		$.ajax({
			type : "GET",
			url : "hotAjax.php?act=get_hotshop",
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					var num = 0;
					var boxheight = null;
					
					$.each(data.data, function (i, res) {
						i++;
						price=res.price;
						//./test.php?cid='+res.cid+'&tid='+res.tid+'
						$("#hotlist").append('<div class="col-xs-6 col-sm-4 col-md-3 layui-anim layui-anim-scaleSpring hotlist hotlist'+i+'" data-anim="layui-anim-upbit" style="padding:0 5px"><a data-toggle="tab" href="#onlinebuy" onclick="getshop('+res.tid+','+res.cid+');"><div class="thumbnail" style=""><center style="margin-top:0;"><span style="font-weight:bold;color:red;margin-top:0px;padding:0;">'+getMsg(price,res.cid)+'</span><hr class="layui-bg-red" style="width:100%;margin:2px auto;"><img src="'+res.shopimg+'" style="width:80px;height:90px;border-radius: 15px" onerror="this.src=\'assets/img/Product/noimg.png\'"><hr class="layui-bg-blue" style="width:100%;margin:4px auto;">'+generate(res.name)+'<hr class="layui-bg-red" style="width:100%;margin:4px auto;"><span style="font-weight:bold;color:red">￥'+price+' <img style="width:22px;margin-top:-2px;" src="assets/hotFree/hot.gif"></span><br>[立即选购]</center></div></a></div>');
						num++;
						if(i%2==0){
							i2=i-1;
							lastheight=parseInt($(".hotlist"+i2).css("height"));
							thisheight=parseInt($(".hotlist"+i).css("height"));
							console.log(lastheight+'<=>'+thisheight);
							if(lastheight>thisheight){
								var boxmargin=parseInt($(".hotlist"+i).css('margin-bottom'));
								boxheight=lastheight-thisheight+boxmargin;
								$(".hotlist"+i).css('margin-bottom',boxheight+'px');
								console.log(".hotlist"+i+'=>'+boxheight);
							}
						}
					});
					//$(".hotlist").css('height',boxheight+'px');
					$("#shoplist").val(0);
					/*getPoint();*/
					//if(num==0 && cid!=0)layer.msg('该分类下没有商品');
				}else{
					layer.alert(data.msg);
				}
			},
			error:function(data){
				layer.close(ii);
				layer.msg('服务器错误');
				return false;
			}
		});

}


function getshop(tid,cid) {
	$("#cid").val(cid);
	if($('a[href="#shop"]').length>0){
		$('a[href="#shop"]').tab('show');
	}
	else{
		$('a[href="#onlinebuy"]').tab('show');
	}
	var ii =layer.load(2, {shade:[0.1,'#fff']});
	$("#tid").empty();
	$("#tid").append('<option value="0">请选择商品</option>');
	$.ajax({
		type : "GET",
		url : "ajax.php?act=gettool&cid="+cid,
		dataType : 'json',
		timeout: 1500,
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				var num = 0;
				$.each(data.data, function (i, res) {
					$("#tid").append('<option name="'+res.name+'" value="'+res.tid+'" cid="'+res.cid+'" price="'+res.price+'" amount="'+res.value+'" alert="'+escape(res.alert)+'" desc="'+escape(res.desc)+'" inputname="'+res.input+'" inputsname="'+res.inputs+'" multi="'+res.multi+'" shopimg="'+res.shopimg+'" isfaka="'+res.isfaka+'" is_curl="'+res.is_curl+'">'+res.name+'</option>');
					num++;
				});
				$("#tid").append('<option name="" value="1" cid="-1" price="8888" amount="1" alert="" inputname="QQ账号" inputsname="" multi="1" isfaka="0">『提示』没有更多商品了(｡･ω･｡)</option>');
				$("#tid").val(tid);
				getPoint();
				//if(num==0 && cid!=0)alert('该分类下没有商品');
			}else{
				alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
	layer.msg('祝您购物愉快！', {icon: 6,time: 600,shade : 0});
}

function getMsg(price,cid){
		return '▶特价商品◀';
}

function getMsg2(price,cid){
	price=Number(price);
	var zk_blackcid_arr=zk_blackcid.split(",");
	
	if(zk_blackcid_arr.in_array(cid)){
		return '▶特价商品◀';
	}
	else if(price>=zk_min_money){
		return '▶有折扣哦◀';
	}
	else 
		return '▶满减打折◀';
}
function MakeHex(x) {
	if((x >= 0) && (x <= 9)){
		return x;
	}else{
		switch(x) {
		case 10: return "A"; 
		case 11: return "B";  
		case 12: return "C";  
		case 13: return "D";  
		case 14: return "E";  
		case 15: return "F";  
		  }
	}
}
function MakeNum(str) {
	if((str >= 0) && (str <= 9)){
		return str;
	}
	switch(str.toUpperCase()) {
	case "A": return 10;
	case "B": return 11;
	case "C": return 12;
	case "D": return 13;
	case "E": return 14;
	case "F": return 15;
	}
}
function MakeNum(str) {
	if((str >= 0) && (str <= 9)){
		return str;
	}
	switch(str.toUpperCase()) {
	case "A": return 10;
	case "B": return 11;
	case "C": return 12;
	case "D": return 13;
	case "E": return 14;
	case "F": return 15;
	}
}
function HexToNum(hex) {
	tens = MakeNum(hex.substring(0,1));
	ones = 0;
	ones=MakeNum(hex.substring(1,2));
	num = (tens * 16) + (ones * 1);
	return num;
}
function NumToHex(strNum) {
	var base,rem,baseS,remS;
	base = strNum / 16;
	rem = strNum % 16;
	base = base - (rem / 16);
	baseS = MakeHex(base);
	remS = MakeHex(rem);
	hex = baseS + '' + remS;
	return hex;
}
function generate(name){
	scolor=('00000'+(Math.random()*0x1000000<<0).toString(16)).slice(-6);
	ecolor=('00000'+(Math.random()*0x1000000<<0).toString(16)).slice(-6);
	r1=HexToNum(scolor.substring(0,2));
	g1=HexToNum(scolor.substring(2,4));
	b1=HexToNum(scolor.substring(4,6));
	r2=HexToNum(ecolor.substring(0,2));
	g2=HexToNum(ecolor.substring(2,4));
	b2=HexToNum(ecolor.substring(4,6));
	r_step=(r1-r2-((r1-r2)%name.length))/name.length;
	g_step=(g1-g2-((g1-g2)%name.length))/name.length;
	b_step=(b1-b2-((b1-b2)%name.length))/name.length;
	if(r_step==0){r_step=3;}
	if(g_step==0){g_step=3;}
	if(b_step==0){b_step=3;}
	var str2='';
	r_color=r1;
	g_color=g1;
	b_color=b1;
	for(var i=0;i<name.length;i++){
		cur_str=name.substring(i,i+1);
		r_color=r_color-r_step;
		g_color=g_color-g_step;
		b_color=b_color-b_step;
		if(r_color>=255||r_color<0){r_color=r1;}
		if(g_color>=255||g_color<0){g_color=g1;}
		if(b_color>=255||b_color<0){b_color=b1;}
		cur_color=NumToHex(r_color)+''+NumToHex(g_color)+''+NumToHex(b_color)
		if(cur_str=='\n'){
			str2+='<br>';
		}else{
			str2+='<font color=#' +cur_color+ '>' + cur_str + '</font>';
		}
	}
	return str2;
}
