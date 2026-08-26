"use strict";
var serverPath = 'string' === typeof serverPath ? serverPath : './';
var template_virtualdata = $("input[name=_template_virtualdata]").val();
var template_showsales = $("input[name=_template_showsales]").val();
var template_store_classbtn = $("input[name=_template_store_classbtn]").val();
var curr_time = $("input[name=_curr_time]").val();
var swiper = null;
var show_tag = null,
    show_tag_html = null,
    buy = null;

function isWap() {
    var isMobile = false;
    if (navigator.userAgent.match(new RegExp('phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone', 'i')) !== null) {
        // console.log('移动端');
        isMobile = true;
    }
    // if (document.body.clientWidth < 800) {
    //     isMobile = true;
    // }
    console.log('是否移动端：', (isMobile ? '是' : '否'));
    return isMobile
}
$(function () {
    //排序点击
    $(".goods_sort .item").on("click", function () {
        var sort = $(this).data("order"); //获取排序类型
        if (!sort) {
            return false;
        }
        var sort_type = $(this).data("sort"); //获取类型
        if (sort_type == "DESC") {
            var sort_type_new = "ASC";
        } else {
            var sort_type_new = "DESC";
        }
        //移除其他已点击
        $(".goods_sort div").attr("class", "item item-price");
        $(this).addClass(sort_type);
        $(this).data("sort", sort_type_new);
        $('.goods_sort div').removeClass('on');
        $(this).addClass("on");
        $("input[name=_sort_type]").val(sort);
        $("input[name=_sort]").val(sort_type);
        get_goods();
    });
    //切换下一页
    $(".class .swiper-button-next").on("click", function () {
        if ('object' == typeof swiper) {
            if (swiper.activeIndex + 1 == swiper.slides.length) {
                layer.msg('没有更多分类了~');
                return;
            }
            swiper.slideNext();
        }
    });
    //切换上一页
    $(".class .swiper-button-prev").on("click", function () {
        if ('object' == typeof swiper) {
            if (swiper.activeIndex == 0) {
                layer.msg('已经是第一页分类了~');
                return;
            }
            swiper.slidePrev();
        }
    });
    if ($(".swiper-wrapper .content-slide").length > 1) {
        swiper = new Swiper('.swiper-container', {
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + (index + 1) + '</span>';
                },
                // nextEl: '.swiper-button-next', //自动隐藏
                // prevEl: '.swiper-button-prev', //自动隐藏
            },
            mousewheel: true,
            keyboard: true,
            // loop:true,
        });
        console.log(template_store_classbtn);
        //分类分页器
        if (template_store_classbtn) {
            $(".swiper-button-next").show();
            $(".swiper-button-prev").show();
        }
    }
    jQuery(function ($) {
        $(window).resize(function () {
            var width = $('#js-com-header-area').width();
            $('.touchslider-item a').css('width', width);
            $('.touchslider-viewport').css('height', 200 * (width / 640));
        }).resize();
    });
    if (template_virtualdata == 1) {
        //ka();
    }
    get_goods();
    $(".get_cat").on("click", function () {
        var cid = $(this).data("cid");
        var name = $(this).data("name");
        if ($(this).hasClass("shop_active")) {
            //return false;
        }
        $('.device .content-slide a').removeClass('shop_active');
        $("input[name=kw]").val("");
        $("input[name=_cid]").val(cid);
        $("input[name=_cidname]").val(name);
        get_goods();
        $(this).addClass('shop_active');
        history.replaceState({}, null, './?cid=' + cid);
    });
    //点击搜索拦截
    $("#goods_search").submit(function (e) {
        var km = $("input[name=kw]").val();
        if (km == "") {
            layer.msg("请输入关键词进行查询");
            return false;
        }
        $("input[name=_cid]").val("");
        $("input[name=_cidname]").val("");
        $(".catname_show").html("正在获取数据");
        $(".show_class").hide();
        $('.device .content-slide a').removeClass('shop_active');
        get_goods();
        document.activeElement.blur();
        return false;
    });
    if ($.cookie('goods_list_style') == 'list') {
        $("#listblock").data("state", "gongge");
        $("#listblock").removeClass("icon-sort");
        $("#listblock").addClass("icon-app");
        $("#goods-list-container").removeClass("block three");
    }
    /*点击切换风格*/
    $("#listblock").on("click", function () {
        var index = layer.msg('加载中', {
            icon: 16,
            shade: 0.01
        });
        var attr = $(this).data("state");
        if (attr == 'gongge') {
            $(this).data("state", "list");
            $(this).removeClass("icon-app");
            $(this).addClass("icon-sort");
            $("#goods-list-container").addClass("block three");
        } else {
            $(this).data("state", "gongge");
            $(this).removeClass("icon-sort");
            $(this).addClass("icon-app");
            $("#goods-list-container").removeClass("block three");
        }
        //设置cookie
        var cookietime = new Date();
        cookietime.setTime(cookietime.getTime() + (86400));
        $.cookie('goods_list_style', attr, {
            expires: cookietime
        });
        layer.close(index);
    });
    //弹窗广告
    if (!$.cookie('op')) {
        $('.tzgg').show();
        $.cookie('op', false, {
            expires: 1
        });
    }
    /**
     * 兼容iphone
     * @type {number | boolean | *}
     */
    var isIphoneX = window.devicePixelRatio && window.devicePixelRatio === 3 && window.screen.width === 375 && testUA('iPhone');
    if (isIphoneX && window.history.length <= 2) {
        // document.body.classList.add('fix-iphonex-bottom');
        //        $(".fui-navbar,.cart-list,.fui-footer,.fui-content.navbar").addClass('iphonex')
        $(".fui-navbar").css("bottom", "0px");
    } else {
        $(".fui-navbar,.cart-list,.fui-footer,.fui-content.navbar").removeClass('iphonex');
    }
});

function ka() {
    setInterval("get_data()", 6000);
}

function get_data() {
    $.ajax({
        type: "get",
        url: serverPath + "other/getdatashow.php",
        async: true,
        dataType: 'json',
        success: function (data) {
            if (data.code == 1) {
                $('#xn_text').text(data.text + " " + data.time + '前');
                $('#xn_text').fadeIn(1000);
                setTimeout("$('#xn_text').fadeOut(1000);", 4000);
            }
        }
    });
}

function testUA(str) {
    return navigator.userAgent.indexOf(str) > -1
}

function load(text = "加载中") {
    var index = layer.msg(text, {
        icon: 16,
        shade: 0.01
    });
}

function getGoods(cid, name) {
    $("input[name=_cidname]").val(name);
    $("input[name=_cid]").val(cid);
    get_goods();
}
//获取商品
function get_goods() {
    $("#goods_list").remove();
    $(".flow_load").append("<div id=\"goods_list\" ></div>");
    layui.use(['flow'], function () {
        var flow = layui.flow;
        var cid = $("input[name=_cid]").val();
        var name = $("input[name=_cidname]").val();
        var kw = $("input[name=kw]").val();
        var sort_type = $("input[name=_sort_type]").val();
        var sort = $("input[name=_sort]").val();
        var mb = testUA('Safari') ? 180 : 100;
        var end = kw ? "没有更多数据了" : " ";
        var limit = 9;
        if (name != "") {
            load();
        }
        //写入数据
        $(".show_class").show();
        flow.load({
            elem: '#goods_list', //流加载容器
            isAuto: true,
            mb: mb,
            isLazyimg: true,
            end: end,
            done: function (page, next) { //执行下一页的回调
                var lis = [];
                //以jQuery的Ajax请求为例，请求下一页数据（注意：page是从2开始返回）
                $.ajax({
                    type: "post",
                    url: serverPath + "ajax.php?act=gettoolnew",
                    data: {
                        page: page,
                        limit: limit,
                        cid: cid,
                        kw: kw,
                        sort_type: sort_type,
                        sort: sort
                    },
                    dataType: 'json',
                    success: function (res) {
                        $(".tag_name").hide();
                        $(".tag_name ul").html("");
                        //假设你的列表返回在data集合中
                        layui.each(res.data, function (index, item) {
                            if (res.class) {
                                //子分类处理
                                var html = '<a class="fui-goods-item" title="' + item.name + '" onclick="getGoods(' + item.cid + ',\'' + item.name + '\')">';
                                html += '<div class="image">';
                                if (!item.shopimg) {
                                    item.shopimg = serverPath + "assets/template/store/picture/error_img.png"
                                }
                                html += '<img class="lazy" lay-src="' + item.shopimg + '" onerror="this.src=\'' + serverPath + 'assets/template/store/picture/error_img.png\'" src="' + serverPath + 'assets/template/store/picture/loadimg.gif" alt="' + item.name + '">';
                                html += '</div>';
                                html += '<div class="detail" style="height:unset;">';
                                html += '<div class="subclassname" style="color: #000000;">' + item.name + '</div>';
                                html += '<div class="subclass" style="margin-top: 0.2rem;"><span class="btn1">进入该分类</span></div>';
                                html += '</div>';
                                html += '</a>';
                            } else {
                                html = '<a class="fui-goods-item" title="' + item.name + '" href="./?mod=buy&tid=' + item.tid + '">';
                                html += '<div class="image">';
                                if (!item.shopimg) {
                                    item.shopimg = serverPath + "assets/template/store/picture/error_img.png"
                                }
                                if ('undefined' != typeof item.show_tag && item.show_tag) {
                                    show_tag = item.show_tag;
                                } else {
                                    if ((curr_time - item.addtime) <= 259200) {
                                        show_tag = "新款";
                                    } else {
                                        show_tag = "";
                                    }
                                }
                                //显示商品标签
                                show_tag_html = "";
                                if (show_tag) {
                                    show_tag_html = '<div style="transform: rotate(-45deg);background-color: #FF0000;color:#FFFFFF;width: 100px;text-align: center;margin-top: 15px;margin-left: -27px;font-size: 14px;position: absolute;">' + show_tag + '</div>';
                                }
                                var shoukong = '';
                                var kucun = '';
                                var kucunHtml = '';
                                //库存为0的
                                if (item.is_stock_err == 1 || item.stock_open == 1 && item.stock <= 0) {
                                    shoukong = '<img  class="lazy" lay-src="' + serverPath + 'assets/template/store/picture/ysb.png" alt="" style="width:100%;top: 0;position: absolute;height:100%">';
                                    kucun = '已售罄';
                                } else if (item.stock > 0) {
                                    if (item.stock >= 100) {
                                        kucun = '库存充足';
                                    } else {
                                        kucun = '库存' + item.stock + '份';
                                    }
                                } else {
                                    kucun = '库存不限';
                                }
                                html += '' + show_tag_html + '<img class="lazy" lay-src="' + item.shopimg + '" onerror="this.src=\'' + serverPath + 'assets/template/store/picture/error_img.png\'" src="' + serverPath + 'assets/template/store/picture/loadimg.gif" alt="' + item.name + '">' + shoukong + '';
                                html += '</div>';
                                html += '<div class="detail" style="height:unset;">';
                                html += '<div class="name" style="color: #000000;">' + item.name + '</div>';
                                // html += '<div style="line-height:0.7rem;height:0.7rem;color:#b2b2b2;font-size:0.6rem;margin-top: .2rem;">' + kucun + '</div>';
                                if (item.stock_open == 1 && item.stock == 0) {
                                    buy = '<div><span class="buy quehuo">缺货</span></div>';
                                } else if (item.active == 1) {
                                    buy = '<div><span class="buy yxj">已下架</span></div>';
                                } else {
                                    if (item.price <= 0) {
                                        buy = '<div><span class="buy">领取</span></div>';
                                    } else {
                                        buy = '<div><span class="buy">购买</span></div>';

                                    }
                                }

                                html += '<div class="info1" style="display:flex; margin-top: 0.35rem;">';
                                html += '<div class="price" style="flex: 1;"><span class="text" style="color: #ff5555;"> <p class="minprice">￥' + item.price + '</p> </span></div>';

                                if (item.stock_open == 1) {
                                    if (item.stock > 199) {
                                        kucunHtml = '<div class="kucun kucun-cz">库存充足</div>';
                                    } else if (item.stock > 10) {
                                        kucunHtml = '<div class="kucun kucun-cz">库存' + item.stock + '份</div>';
                                    } else if (item.stock > 4) {
                                        kucunHtml = '<div class="kucun kucun-sl">库存' + item.stock + '份</div>';
                                    } else if (item.stock > 0) {
                                        kucunHtml = '<div class="kucun kucun-jz">库存' + item.stock + '份</div>';
                                    } else {
                                        kucunHtml = '<div class="kucun kucun-qh">库存缺货</div>';
                                        //kucunHtml += '<div><span class="kucun kucun-qh">库存缺货</span></div>';
                                    }
                                } else {
                                    if (shoukong) {
                                        kucunHtml = '<div class="kucun kucun-qh">' + kucun + '</div>';
                                    }
                                    else {
                                        kucunHtml = '<div class="kucun kucun-cz">' + kucun + '</div>';
                                    }
                                    //kucunHtml = '<div><span class="kucun kucun-cz">' + kucun + '</span></div>';
                                }

                                if (item.value < 1) {
                                    item.value = 1;
                                }

                                if ('undefined' == typeof item.unit || item.unit == '') {
                                    item.unit = '个';
                                }

                                html += '</div>';
                                html += '<div class="info2" style="display: flex;">';
                                html += '   <div class="value">每份' + item.value + item.unit + '</div>';
                                html += kucunHtml;

                                if (template_showsales == 1) {
                                    html += '</div>';
                                    html += '<div class="info2" style="display: flex;">';
                                    if (item.sale > 0) {
                                        html += '<div class="sales">销量' + item.sale + '+</div>';
                                    } else {
                                        html += '<div class="sales">销量' + item.sale + '</div>';
                                    }
                                    html += buy + '</div>';

                                } else {
                                    // html += '</div>';
                                    // html += '<div class="info2" style="display: flex; min-height: 22px;">';
                                    // html += buy + '</div>';
                                    html += '</div>';
                                }
                                html += '</div>';
                                html += '</a>';
                            }
                            lis.push(html);
                        });
                        if (res.class == 1) {
                            $(".catname_show").html('该分类下共有<font style="color:#ed414a;">' + res.total + '</font>个子分类');
                        } else {
                            if (name == "" || cid == 0) {
                                $(".catname_show").html('系统共有<font style="color:#ed414a;">' + res.total + '</font>个商品');
                            } else {
                                $(".catname_show").html('<font style="color:#ed414a;">' + name + '</font>共有<font style="color:#ed414a;">' + res.total + '</font>个商品');
                            }
                            if (kw != "") {
                                $(".catname_show").html('包含<font style="color:#ed414a;">' + kw + '</font>共有<font style="color:#ed414a;">' + res.total + '</font>个商品');
                            }
                        }
                        layer.closeAll();
                        next(lis.join(''), page < res.pages);
                    },
                    error: function (data) {
                        layer.msg("获取数据超时");
                        layer.close(index);
                        return false;
                    }
                });
            }
        });
    });
}
var audio_init = {
    changeClass: function (target, id) {
        var className = $(target).attr('class');
        var ids = document.getElementById(id);
        (className == 'on') ? $(target).removeClass('on').addClass('off') : $(target).removeClass('off').addClass('on');
        (className == 'on') ? ids.pause() : ids.play();
    },
    play: function () {
        document.getElementById('media').play();
    }
}
if ($('#audio-play').is(':visible')) {
    audio_init.play();
}
/*layui.use(['util'], function(){
    var util = layui.util;
    //固定块客服
    util.fixbar({
        bar1: true
        ,bar2: true
        ,css: {right:8,bottom: '25%','z-index':1}
        ,bgcolor: '#393D49'
        ,click: function(type){
          if(type === 'bar1'){
            window.location.href = ("./?mod=kf");
          } else if(type === 'bar2') {
            window.location.href = ("./?mod=articlelist");
          }
        }
    });
});*/