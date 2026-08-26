//插件市场方法 By 星河软件工作室
"use strict";
var $_GET = (function () {
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if (typeof (u[1]) == "string") {
        u = u[1].split("&");
        var get = {};
        for (var i in u) {
            var j = u[i].split("=");
            if (j[1].indexOf('#') >= 0) {
                get[j[0]] = j[1].split('#')[0];
            } else {
                get[j[0]] = j[1];
            }
        }
        return get;
    } else {
        return {};
    }
})();
var chenmObj = {
    pluginList: {},
    pluginNum: 0,
    noData: '',
    pluginLogin: false,
    siteConfig: [],
    interval1: null,
    notify_url: null,
    //初始化
    init: function () {
        if ($("head").length > 0) {
            $("head").append('<style>.layui-layer-content a{color: #2883d0;}.layui-layer-content a:hover,.layui-layer-content a:active{color: #2271b3;}p.plugin .plugin-attr{color:#fff;border-radius: 4px; margin-left: 5px;}p.plugin .plugin-attr:first-child{ margin-left: 0px;}</style>');
        } else {
            $("body").append('<style>.layui-layer-content a{color: #2883d0;}.layui-layer-content a:hover,.layui-layer-content a:active{color: #2271b3;}p.plugin .plugin-attr{color:#fff;border-radius: 4px; margin-left: 5px;}p.plugin .plugin-attr:first-child{ margin-left: 0px;}</style>');
        }
        chenmObj.bindEvent();
        chenmObj.getConfig();
        if (typeof noload == 'undefined' || noload == false) {
            if ($_GET['type']) {
                var type = $_GET['type'];
                if ($("li[type='" + type + "']").length > 0) {
                    setTimeout(function () {
                        $("li[type='" + type + "'] a").click();
                    }, 800);
                }
            } else {
                setTimeout(function () {
                    chenmObj.load('all');
                }, 1800);
            }
        }
        chenmObj.checkBind();
    },
    isWap: function () {
        var isMobile = false;
        if (navigator.userAgent.match(new RegExp('phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone', 'i'))) {
            // console.log('移动端');
            isMobile = true;
        }
        if (document.body.clientWidth < 600) {
            console.log('document.body.clientWidth', document.body.clientWidth);
            isMobile = true;
        }
        return isMobile
    },
    //绑定元素事件
    bindEvent: function () {
        $(document).on('click', '.preView', function () {
            var index = parseInt($(this).attr('index'));
            chenmObj.preView(chenmObj.pluginList[index]);
        });
        $(document).on('click', '.install', function () {
            var index = parseInt($(this).attr('index'));
            chenmObj.install(chenmObj.pluginList[index]);
        });
        $(document).on('click', '.view', function () {
            var index = parseInt($(this).attr('index'));
            chenmObj.viewDesc(chenmObj.pluginList[index]);
        });
        $(document).on('click', '.unInstall', function () {
            layer.msg('请前往【已装插件】更卸载！');
        });
        $(document).on('click', '#search', function () {
            var kw = $("#kw").val();
            chenmObj.load('all', kw);
        });
        $(document).on('click', 'a.reg', function () {
            chenmObj.reg();
        });
        $(document).on('click', '.updateBind', function () {
            var id = $(this).data('id');
            var authtype = $(this).data('authtype');
            var alias = $(this).data('alias');
            var name = $(this).data('name');
            var token = $(this).data('token');
            console.log('token', token);
            chenmObj.updateBind(id, authtype, alias, name, token);
        });
    },
    //加载插件
    load: function (type, kw) {
        kw = kw || '';
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "./ajax.php?act=plugin_getlist",
            dataType: "json",
            data: {
                type: type,
                kw: kw
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    //这里不用this指向和that变量代替，避免数据可能不会保存
                    chenmObj.pluginList = data.data;
                    chenmObj.noData = data.noData;
                    chenmObj.pluginNum = data.num;
                    chenmObj.loadOption();
                } else {
                    if (data.msg.indexOf('授权站') >= 0) {
                        layer.alert('贵服务器与当前授权节点网络不通，请重新登录并换一个节点再试！');
                    } else {
                        layer.alert(data.msg);
                    }
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg("获取插件列表失败，请稍后重试！");
                return false;
            }
        });
    },
    //预览插件/模板
    preView: function (item) {
        layer.photos({
            photos: item.preimg,
            anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
        });
    },
    //渲染插件列表
    loadOption: function () {
        var o = '';
        if (chenmObj.isWap() == false) {
            $("#pluginlist").html("");
            $.each(chenmObj.pluginList, function (i, item) {
                if (item.desc == "") {
                    item.desc = '暂无简介';
                }
                o = '<tr>';
                if (typeof item.icon == 'string') {
                    o += ' <td><img index="' + i + '" class="preView" onerror="this.src=\'' + chenmObj.noData + '\'" src="' + item.icon + '" width="80px"></td>';
                } else {
                    o += ' <td>' + item.id + '</td>';
                }
                o += ' <td>' + item.name + '</td>';
                if (item.type == 'template') {
                    o += ' <td><span class="plugin-attr btn btn-xs" style="background-color:#9156f8;color:#fff">模板</span></td>';
                } else if (item.type == 'shequ') {
                    o += ' <td><span class="btn btn-info btn-xs">对接</span></td>';
                } else if (item.type == 'fuwu') {
                    o += ' <td><span class="btn btn-danger btn-xs">服务</span></td>';
                } else {
                    o += ' <td><span class="plugin-attr btn btn-xs" style="background-color:#33afff;color:#fff">插件</span></td>';
                }
                o += ' <td>';
                o += '描述：' + item.desc;
                o += '<br><p class="plugin">';
                o += '<span class="plugin-attr btn btn-info btn-xs" data-tip="版本' + item.version + ' (build ' + item.build + ')">V' + item.version + '</span>';
                if (item.type == 'plugin' || item.type == 'shequ') {
                    var arr1 = item.template.split(',');
                    if (item.template === '*' || $.inArray(chenmObj.siteConfig['template'], arr1) >= 0) {
                        o += '<span class="plugin-attr btn btn-success btn-xs" data-tip="当前设置模板兼容">模板兼容</span>';
                    } else {
                        o += '<span class="plugin-attr btn btn-danger btn-xs" data-tip="当前设置模板可能会不兼容">模板兼容异常</span>';
                    }
                }
                if (typeof item.installed != 'undefined' && item.installed == 1) {
                    o += '<span class="plugin-attr btn btn-success btn-xs" data-tip="该插件/模板已安装">已安装</span>';
                } else {
                    o += '<span class="plugin-attr btn btn-info btn-xs" data-tip="该插件/模板可以安装">可安装</span>';
                }
                if (item.money <= 0) {
                    o += '<span class="plugin-attr btn btn-success btn-xs" data-tip="该插件/模板可免费使用">免费</span>';
                }
                o += '</p></td>';
                o += ' <td>' + item.author + '</td>';
                o += ' <td>' + item.authtypename + '</td>';
                o += ' <td>V' + item.version + '</td>';
                if (item.isbuy) {
                    o += ' <td><span class="btn btn-success btn-xs" data-tip="已购买，可直接安装">是</span></td>';
                } else {
                    o += ' <td><span class="btn btn-warning btn-xs" data-tip="未购买">否</span></td>';
                }
                if (item.money > 0) {
                    o += ' <td><span style="color:red">￥' + item.money + '</span></td>';
                } else {
                    o += ' <td><span style="color:green">免费</span></td>';
                }
                if (typeof item.installed != 'undefined' && item.installed == 1) {
                    o += '<td>';
                    o += ' <a index="' + i + '" class="unInstall btn btn-warning btn-xs">卸载</a>&nbsp;';
                    o += '<a index="' + i + '" href="#" class="view btn btn-info btn-xs">详情</a>&nbsp;';
                    if (item.preimg.data.length > 0) {
                        o += '<a index="' + i + '" href="#" class="preView btn btn-primary btn-xs">预览</a>&nbsp;';
                    }
                    o += '</td>';
                } else {
                    o += '<td>';
                    if (item.type == 'fuwu') {
                        var btnName = '购买';
                    } else {
                        var btnName = '安装';
                    }
                    if (item.money > 0) {
                        o += '<a index="' + i + '" class="install btn btn-success btn-xs">' + btnName + '</a>&nbsp;';
                    } else {
                        o += '<a index="' + i + '" class="install btn btn-success btn-xs">' + btnName + '</a>&nbsp;';
                    }
                    o += '<a index="' + i + '" href="#" class="view btn btn-info btn-xs">详情</a>&nbsp;';
                    if (item.preimg.data.length > 0) {
                        o += '<a index="' + i + '" href="#" class="preView btn btn-primary btn-xs">预览</a>&nbsp;';
                    }
                    o += '</td>';
                }
                o += '</tr>';
                $("#pluginlist").append(o);
            });
            if (chenmObj.pluginNum == 0) {
                o = '<tr>';
                o += ' <td colspan="6" class="text-center">一条数据也木有~</td>';
                o += '</tr>';
                $("#pluginlist").append(o);
            }
        } else {
            //手机端
            $("#pcTable").hide();
            $("#mobileTable").html("").show();
            $.each(chenmObj.pluginList, function (i, item) {
                o = '<div class="col-xs-6 col-sm-6 plugin-box">';
                o += '  <div class="plugin-list">';
                if ("" == item.icon) {
                    item.icon = 'assets/img/plugin/noData.png';
                }
                o += '      <div class="icon"><img index="' + i + '" class="preView" onerror="this.src=\'' + chenmObj.noData + '\'" src="' + item.icon + '"/></div>';
                o += '      <div class="desc">';
                o += '          <div class="name"><span>' + item.name + '</span></div>';
                o += '          <div class="author"><span>' + item.author + '</span></div>';
                o += '      </div>';
                o += '      <div class="price">';
                if (item.money > 0) {
                    o += '      <div class="money"><span>￥' + item.money + '</span></div>';
                } else {
                    o += '      <div class="money"><span class="free">免费</span></div>';
                }
                if (item.type == 'template') {
                    o += '          <div class="attr"><span class="template">模板</span></div>';
                } else {
                    o += '          <div class="attr"><span class="plugin">插件</span></div>';
                }
                o += '      </div>';
                if (item.type == 'fuwu') {
                    var btnName = '购买';
                } else {
                    var btnName = '安装';
                }
                o += '      <div class="option">';
                o += '          <a index="' + i + '" href="#"  class="install btn-success">' + btnName + '</a>';
                if (item.preimg.data.length > 0) {
                    o += '      <a index="' + i + '" href="#" class="preView btn-primary">预览</a>';
                }
                o += '          <a index="' + i + '" href="#" class="view btn-info">详情</a>';
                o += '      </div>';
                o += ' </div>';
                o += '</div>';
                $("#mobileTable").append(o);
            });
            if (chenmObj.pluginNum == 0) {
                o = '<div class="col-xs-6 col-sm-6">';
                o += '  <div class="plugin-list">';
                o += '      <div class="icon"><img src="' + chenmObj.noData + '"/></div>';
                o += '      <div class="desc">';
                o += '          <div class="name">暂无数据</div>';
                o += '          <div class="author"><span>官方</span></div>';
                o += '      </div>';
                o += '      <div class="price">';
                o += '          <div class="money"><span class="free">免费</span></div>';
                o += '          <div class="attr"><span>提示</span></div>';
                o += '      </div>';
                o += '      <div class="option">';
                o += '          <a href="#" onclick="return false" class="install btn-success">安装</a>';
                o += '          <a href="#" onclick="return false" class="view btn-info">详情</a>';
                o += '      </div>';
                o += ' </div>';
                o += '</div>';
                $("#mobileTable").append(o);
            }
        }
        window.runTip();
    },
    viewDesc: function (item) {
        if (undefined == item || 'object' != typeof item) {
            console.log('item', item);
            this._alert('插件信息异常，请稍后再试！');
            return;
        }
        this._alert({
            title: "查看详细说明",
            content: '<div class="panel-body">插件名称：' + item.name + '<br/>插件价格：' + item.money + '元<br/>插件版本：' + item.version + '<br/>插件作者：' + item.author + '<br/>插件类型：' + item.typename + '<br/>授权类型：' + item.authtypename + '<br/>插件详情：' + item.desc + (item.uplog ? '<br/>更新日志：' + item.uplog : '') + '</div>',
            btn: ['立即安装', '关闭详情'],
            yes: function () {
                chenmObj.install(item);
                return;
            },
            btn2: function (id) {
                layer.close(id);
                return;
            }
        });
    },
    //安装
    install: function (item) {
        if (undefined == item || 'object' != typeof item) {
            console.log('item', item);
            this._alert('插件信息异常，请稍后再试！');
            return;
        }
        var that = this;
        if (this.pluginLogin == false) {
            this._alert({
                title: '绑定平台账号提示',
                content: '<div class="panel-body"><h4>此操作需要先绑定授权平台账号，是否现在绑定？</h4></div>',
                btn: ['立即注册绑定', '我再想想'],
                yes: function (i) {
                    layer.close(i);
                    that.bindUser();
                },
                btn2: function () {
                    layer.msg("只有绑定账号后才能安装插件哦~");
                }
            });
        } else {
            if (['fuwu'].includes(item.type)) {
                //服务类型
                var area = [$(window).width() > 640 ? '480px' : '95%', 'auto'];
                layer.open({
                    type: 1,
                    area: area,
                    content: $("#template_service").html(),
                    btn: ['下单提交需求', '我再想想'],
                    yes: function (index) {
                        layer.close(index);
                        var data = {
                            id: item.id,
                            dirname: item.dirname,
                            type: item.type,
                            input1: $("#input1").val(),
                            input2: $("#input2").val(),
                            input3: $("#input3").val()
                        }
                        var ii = layer.load(2, {
                            shade: [0.1, '#fff']
                        });
                        $.ajax({
                            type: "POST",
                            url: "./ajax.php?act=plugin_install",
                            dataType: "json",
                            data: data,
                            success: function (data) {
                                layer.close(ii);
                                if (data.code == 0) {
                                    layer.msg(data.msg, {
                                        end: function () {
                                            window.location.reload();
                                        }
                                    });
                                } else if (data.code == 2) {
                                    that._alert({
                                        title: '温馨提示',
                                        content: '<div style="padding:5px 10px; text-align: center;">' + data.msg + '</div>',
                                        btn: ['立即充值', '我知道了'],
                                        btnAlign: "c",
                                        yes: function (index) {
                                            chenmObj.recharge();
                                        }
                                    });
                                } else {
                                    that._alert(data.msg);
                                }
                            },
                            error: function (ret) {
                                layer.close(ii);
                                if (item.type == 'template') {
                                    that._alert("模板下载超时，请更换升级好一点的服务器或前往授权站手动下载模板！");
                                } else {
                                    that._alert("请求超时，请前往授权站购买服务！");
                                }
                                return false;
                            }
                        });
                    }
                })
            } else {
                if (item.alert && item.alert != 'null') {
                    layer.confirm('<div style="padding: 8px;">' + item.alert + '</div>', {
                        title: '使用须知和使用协议',
                        btn: ['已阅读同意并下一步', '取消'],
                    }, function (index) {
                        var tips = '';
                        var arr1 = item.template.split(',');
                        if (item.template != '*' && $.inArray(chenmObj.siteConfig['template'], arr1) < 0) {
                            tips = '<br/><span style="color:red">兼容异常：当前模板可能需要适配后才能用</span>';
                        }
                        if (item.isbuy) {
                            var content = '<div style="padding: 2px;">您将要安装【' + item.name + '】, 是否确定？' + tips + '</div>';
                        } else {
                            var content = '<div style="padding: 2px;">该插件/模板需花费' + item.money + '元<br/>本次购买后将不会再付费，是否确定？' + tips + '</div>';
                        }
                        layer.confirm(content, {
                            btnAlign: "c",
                            icon: 3,
                            title: '安装提示'
                        }, function (index) {
                            layer.close(index);
                            var ii = layer.load(2, {
                                shade: [0.1, '#fff']
                            });
                            $.ajax({
                                type: "POST",
                                url: "./ajax.php?act=plugin_install",
                                dataType: "json",
                                data: {
                                    id: item.id,
                                    dirname: item.dirname
                                },
                                success: function (data) {
                                    layer.close(ii);
                                    if (data.code == 0) {
                                        var html = item.type == 'template' ? '<br/>注意：模板安装后无需开启，可直接在首页模板设置使用！' : '';
                                        layer.msg(data.msg + html, {
                                            time: 3 * 1000,
                                            end: function () {
                                                window.location.href = './plugin.php?mod=install';
                                            }
                                        });
                                    } else if (data.code == 3) {
                                        that.download(data);
                                    } else if (data.code == 2) {
                                        that._alert({
                                            title: '温馨提示',
                                            content: '<div style="padding:5px 10px; text-align: center;">' + data.msg + '</div>',
                                            btn: ['立即充值', '我知道了'],
                                            btnAlign: "c",
                                            yes: function (index) {
                                                chenmObj.recharge();
                                                //window.open(data.url + 'User/index.php#chongzhi');
                                            }
                                        });
                                    } else {
                                        if ('undefined' != typeof data.data.downloadUrl && data.data.downloadUrl) {
                                            if (item.type == 'template') {
                                                layer.alert("模板下载失败，请更换升级好一点的服务器，或手动下载好再上传到网站！<br/>温馨提示：下载完成后将压缩包上传到FTP网站安装目录解压<br/>温馨提示：网站安装目录就是你上传安装程序的那个站点文件夹目录", {
                                                    btn: ['好的', '手动下载'],
                                                    btn2: function () {
                                                        window.location.href = data.data.downloadUrl;
                                                    }
                                                })
                                            } else {
                                                layer.alert("插件下载失败，请更换升级好一点的服务器，或手动下载好再上传插件目录[includes/plugin/" + item.dirname + "]！<br/>温馨提示：下载完成后将压缩包上传到FTP网站安装目录解压<br/>温馨提示：网站安装目录就是你上传安装程序的那个站点文件夹目录", {
                                                    btn: ['好的', '手动下载'],
                                                    btn2: function () {
                                                        window.location.href = data.data.downloadUrl;;
                                                    }
                                                })
                                            }
                                        } else {
                                            that._alert(data.msg);
                                        }
                                    }
                                },
                                error: function (ret) {
                                    console.log(ret);
                                    layer.close(ii);
                                    that._alert({
                                        content: (item.type == 'template' ? '模板' : '插件') + "下载超时，请前往授权站购买下载~！",
                                        btn: ['好的', '取消'],
                                        yes: function () {
                                            var config = chenmObj.siteConfig
                                            var url = 'string' == typeof config.authServer2 ? config.authServer2 : config.authServer;
                                            window.open(url + '/Console/');
                                        }
                                    });
                                    return false;
                                }
                            });
                        });
                    })
                } else {
                    var tips = '';
                    var arr1 = item.template.split(',');
                    if (item.template != '*' && $.inArray(chenmObj.siteConfig['template'], arr1) < 0) {
                        tips = '<br/><span style="color:red">兼容异常：当前模板可能需要适配后才能用</span>';
                    }
                    if (item.isbuy) {
                        var content = '<div style="padding: 2px;">您将要安装【' + item.name + '】, 是否确定？' + tips + '</div>';
                    } else {
                        var content = '<div style="padding: 2px;">该插件/模板需花费' + item.money + '元<br/>本次购买后将不会再付费，是否确定？' + tips + '</div>';
                    }
                    layer.confirm(content, {
                        btnAlign: "c",
                        icon: 3,
                        title: '安装提示'
                    }, function (index) {
                        layer.close(index);
                        var ii = layer.load(2, {
                            shade: [0.1, '#fff']
                        });
                        $.ajax({
                            type: "POST",
                            url: "./ajax.php?act=plugin_install",
                            dataType: "json",
                            data: {
                                id: item.id,
                                dirname: item.dirname
                            },
                            success: function (data) {
                                layer.close(ii);
                                if (data.code == 0) {
                                    var html = item.type == 'template' ? '<br/>注意：模板安装后无需开启，可直接在首页模板设置使用！' : '';
                                    layer.msg(data.msg + html, {
                                        time: 5 * 1000,
                                        end: function () {
                                            window.location.href = './plugin.php?mod=install';
                                        }
                                    });
                                } else if (data.code == 3) {
                                    that.download(data);
                                } else if (data.code == 2) {
                                    that._alert({
                                        title: '温馨提示',
                                        content: '<div style="padding:5px 10px; text-align: center;">' + data.msg + '</div>',
                                        btn: ['立即充值', '我知道了'],
                                        btnAlign: "c",
                                        yes: function (index) {
                                            chenmObj.recharge();
                                            //window.open(data.url + 'User/index.php#chongzhi');
                                        }
                                    });
                                } else {
                                    if ('undefined' != typeof data.data.downloadUrl && data.data.downloadUrl) {
                                        if (item.type == 'template') {
                                            layer.alert("模板下载失败，请更换升级好一点的服务器，或手动下载好再上传到网站！<br/>温馨提示：下载完成后将压缩包上传到FTP网站安装目录解压<br/>温馨提示：网站安装目录就是你上传安装程序的那个站点文件夹目录", {
                                                btn: ['好的', '手动下载'],
                                                btn2: function () {
                                                    window.location.href = data.data.downloadUrl;
                                                }
                                            })
                                        } else {
                                            layer.alert("插件下载失败，请更换升级好一点的服务器，或手动下载好再上传到网站！<br/>温馨提示：下载完成后将压缩包上传到FTP网站安装目录解压<br/>温馨提示：网站安装目录就是你上传安装程序的那个站点文件夹目录", {
                                                btn: ['好的', '手动下载'],
                                                btn2: function () {
                                                    window.location.href = data.data.downloadUrl;;
                                                }
                                            })
                                        }
                                    } else {
                                        that._alert(data.msg);
                                    }
                                }
                            },
                            error: function (ret) {
                                console.log(ret);
                                layer.close(ii);
                                that._alert({
                                    content: (item.type == 'template' ? '模板' : '插件') + "下载超时，请前往授权站购买下载~！",
                                    btn: ['好的', '取消'],
                                    yes: function () {
                                        var config = chenmObj.siteConfig
                                        var url = 'string' == typeof config.authServer2 ? config.authServer2 : config.authServer;
                                        window.open(url + '/Console/');
                                    }
                                });
                                return false;
                            }
                        });
                    });
                }
            }
        }
    },
    download(options) {
        options = options ? options : [];
        var that = this;
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "./ajax.php?act=plugin_download",
            dataType: "json",
            data: {
                options: options
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg, {
                        end: function () {
                            window.location.href = './plugin.php?mod=install';
                        }
                    });
                } else if (data.code == 3) {
                    console.log('download.code', 3);
                    layer.msg("正在下载文件可能耗时较长, 请不要关闭或刷新页面~");
                    setTimeout(function () {
                        that.download(data);
                    }, 1000)
                } else {
                    if ('undefined' != typeof data.data.downloadUrl && data.data.downloadUrl) {
                        layer.alert("插件/模板下载失败，请更换升级好一点的服务器，或手动下载好再上传到网站！<br/>温馨提示：下载完成后将压缩包上传到FTP文件管理的网站安装运行目录解压", {
                            btn: ['好的', '手动下载'],
                            btn2: function () {
                                window.location.href = data.data.downloadUrl;;
                            }
                        })
                    } else {
                        that._alert(data.msg);
                    }
                }
            },
            error: function (error) {
                layer.close(ii);
                that._alert({
                    content: "模板/插件下载超时，请前往授权站购买下载~！",
                    btn: ['好的', '取消'],
                    yes: function () {
                        var config = chenmObj.siteConfig
                        var url = 'string' == typeof config.authServer2 ? config.authServer2 : config.authServer;
                        window.open(url + '/Console/');
                    }
                });
                return false;
            }
        });
    },
    //更新
    update: function (id) {
        var that = this;
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "./ajax.php?act=plugin_update",
            dataType: "json",
            data: {
                id: id
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg, {
                        end: function () {
                            window.location.reload();
                        }
                    });
                } else if (data.code == 3) {
                    console.log('update.code', 3);
                    layer.msg("正在下载文件中，请不要关闭或刷新页面");
                    setTimeout(function () {
                        that.download(data);
                    }, 2000)
                } else {
                    if ('undefined' != typeof data.data.downloadUrl && data.data.downloadUrl) {
                        if (item.type == 'template') {
                            layer.alert("模板下载失败，请更换升级好一点的服务器，或手动下载好再上传到网站！<br/>温馨提示：下载完成后将压缩包上传到FTP网站安装目录解压<br/>温馨提示：网站安装目录就是你上传安装程序的那个站点文件夹目录", {
                                btn: ['好的', '手动下载'],
                                btn2: function () {
                                    window.location.href = data.data.downloadUrl;
                                }
                            })
                        } else {
                            layer.alert("插件下载失败，请更换升级好一点的服务器，或手动下载好再上传到网站！<br/>温馨提示：下载完成后将压缩包上传到FTP网站安装目录解压<br/>温馨提示：网站安装目录就是你上传安装程序的那个站点文件夹目录", {
                                btn: ['好的', '手动下载'],
                                btn2: function () {
                                    window.location.href = data.data.downloadUrl;;
                                }
                            })
                        }
                    } else {
                        that._alert(data.msg);
                    }
                }
            },
            error: function (data) {
                layer.close(ii);
                that._alert({
                    content: "模板/插件下载超时，请前往授权站购买下载~！",
                    btn: ['好的', '取消'],
                    yes: function () {
                        var config = chenmObj.siteConfig
                        var url = 'string' == typeof config.authServer2 ? config.authServer2 : config.authServer;
                        window.open(url + '/Console/');
                    }
                });
                return false;
            }
        });
    },
    //切换状态
    setActive: function (id, status) {
        var that = this;
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "./ajax.php?act=plugin_setActive",
            dataType: "json",
            data: {
                id: id,
                status: status
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg);
                    window.location.reload();
                } else {
                    that._alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg("操作失败，请稍后重试！");
                return false;
            }
        });
    },
    //卸载
    unInstall: function (id) {
        var that = this;
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "./ajax.php?act=plugin_unInstall",
            dataType: "json",
            data: {
                id: id
            },
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg('卸载完成~如需使用可在插件市场重新安装', {
                        time: 1000,
                        end: function () {
                            window.location.reload();
                        }
                    });
                } else {
                    that._alert(data.msg);
                }
            },
            error: function (data) {
                layer.close(ii);
                layer.msg("卸载插件失败，请稍后重试！");
                return false;
            }
        });
    },
    //验证是否已绑定平台账号
    checkBind() {
        $.ajax({
            type: "POST",
            url: "./ajax.php?act=plugin_checkBind",
            dataType: "json",
            success: function (data) {
                if (data.code == 0) {
                    chenmObj.pluginLogin = true;
                }
            },
            error: function (data) {
                layer.msg("页面出错，请稍后重试！");
                return false;
            }
        });
    },
    //获取系统配置
    getConfig() {
        $.ajax({
            type: "POST",
            url: "./ajax.php?act=plugin_getConfig",
            dataType: "json",
            success: function (data) {
                if (data.code == 0) {
                    chenmObj.siteConfig = data.config;
                }
            },
            error: function (data) {
                layer.msg("页面出错，请稍后重试！");
                return false;
            }
        });
    },
    updateBind: function (id, authtype, alias, name, token) {
        var authtypename = '',
            authvalue = '',
            authtips = '';
        if (!token) {
            layer.msg('token无效，请刷新后再试或联系系统商更新');
            return false;
        }
        if (authtype == 2) {
            //域名授权
            authtypename = '域名授权';
            authtips = '填写新的域名，只能这个域名可使用';
            authvalue = window.location.url;
        } else if (authtype == 1) {
            //账号授权
            authtypename = '账号授权';
            authtips = '填写新的插件账号，同一个账号下的授权均可使用';
            authvalue = chenmObj.siteConfig.plugin_user;
        } else {
            //程序授权(授权码)
            authtypename = '程序授权(授权码)';
            authtips = '填写新的授权码，同一个授权码的授权均可使用';
            authvalue = $("#authcode").val();
        }
        var area = chenmObj.isWap() ? ['95%', 'auto'] : ['445px', 'auto'];
        this._alert({
            title: '在线注册账号',
            area: area,
            content: '<div class="panel-body" id="layerForm">' + $("#template_updatebind").html() + '</div>',
            btn: ['换绑', '取消'],
            success: function (layro, index) {
                console.log('authtips', authtips);
                $("#layerForm #authvalue").val(authvalue);
                $("#layerForm #authtypename").val(authtypename);
                $("#layerForm #name").val(name);
                $("#layerForm #authtips").html(authtips);
            },
            yes: function (index) {
                var authvalue = $("#layerForm #authvalue").val();
                var email = $("#layerForm #email").val();
                var ii2 = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                var config = chenmObj.siteConfig
                var url = 'string' == typeof config.authServer2 ? config.authServer2 : config.authServer;
                $.ajax({
                    type: "GET",
                    url: url + 'api.php?act=plugin_update_bind',
                    dataType: "json",
                    data: {
                        id: id,
                        token: token,
                        authvalue: authvalue,
                    },
                    success: function (data) {
                        layer.close(ii2);
                        if (data.code == 0) {
                            layer.msg('更换插件绑定成功', {
                                time: 1500,
                                end: function () {
                                    layer.closeAll();
                                }
                            });
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function (data) {
                        layer.close(ii2);
                        layer.msg("操作失败，请重新登录并更换节点后重试！");
                        return false;
                    }
                });
            }
        });
    },
    //注册账号
    reg: function () {
        var area = chenmObj.isWap() ? ['95%', 'auto'] : ['445px', 'auto'];
        this._alert({
            title: '在线注册账号',
            area: area,
            content: '<div class="panel-body" id="layerForm">' + $("#template_reg").html() + '</div>',
            btn: ['注册', '取消'],
            yes: function (index) {
                var user = $("#layerForm #user").val();
                var pass = $("#layerForm #pass").val();
                var qq = $("#layerForm #qq").val();
                var email = $("#layerForm #email").val();
                var regEmail = new RegExp('^[\\w\-\.]+@[\\w\-\.]+$', 'i');
                if (!user) {
                    layer.msg('登录用户名不能为空!');
                    return false;
                } else if (!pass) {
                    layer.msg('登录密码不能为空!');
                    return false;
                } else if (!qq) {
                    layer.msg('联系QQ不能为空!');
                    return false;
                } else if (!(new RegExp('^[1-9]{1}[0-9]{4,10}$')).test(qq)) {
                    layer.msg('联系QQ格式不正确!');
                    return false;
                } else if (!email) {
                    layer.msg('联系邮箱不能为空!');
                    return false;
                } else if (email.match(regEmail) === null) {
                    console.log('regEmail', regEmail);
                    layer.msg('联系邮箱格式不正确!');
                    return false;
                }
                var ii2 = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                var config = chenmObj.siteConfig
                var url = 'string' == typeof config.authServer2 ? config.authServer2 : config.authServer;
                $.ajax({
                    type: "GET",
                    url: url + 'api.php?act=reg',
                    dataType: "json",
                    data: {
                        user: user,
                        pass: pass,
                        qq: qq,
                        email: email,
                    },
                    success: function (data) {
                        layer.close(ii2);
                        if (data.code == 0) {
                            layer.msg('注册成功，稍后将自动绑定', {
                                time: 1500,
                                end: function () {
                                    $.ajax({
                                        type: "POST",
                                        url: "./ajax.php?act=plugin_binduser",
                                        dataType: "json",
                                        data: {
                                            plugin_user: user,
                                            plugin_pwd: pass,
                                        },
                                        success: function (data) {
                                            layer.close(ii2);
                                            if (data.code == 0) {
                                                chenmObj.siteConfig.plugin_user = user;
                                                chenmObj.siteConfig.plugin_pwd = pass;
                                                layer.closeAll();
                                                chenmObj.pluginLogin = true;
                                                layer.msg('绑定成功，可以购买插件啦~');
                                            } else {
                                                layer.alert(data.msg);
                                            }
                                        },
                                        error: function (data) {
                                            layer.msg("自动绑定失败，请稍后手动绑定！");
                                            return false;
                                        }
                                    });
                                }
                            });
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function (data) {
                        layer.close(ii2);
                        layer.msg("操作失败，请重新登录并更换节点后重试！");
                        return false;
                    }
                });
            }
        });
    },
    //充值余额
    recharge: function () {
        var area = chenmObj.isWap() ? ['95%', 'auto'] : ['445px', 'auto'];
        this._alert({
            title: '在线充值',
            area: area,
            content: '<div class="panel-body" id="layerForm">' + $("#template_recharge").html() + '</div>',
            btn: ['充值', '取消'],
            yes: function (layid) {
                var config = chenmObj.siteConfig
                var user = config.plugin_user;
                if (undefined === user || !user) {
                    layer.alert('还未绑定账号，请先绑定好再充值');
                    return false;
                }
                var money = $("#layerForm #money").val();
                var ii2 = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                var url = 'string' == typeof config.authServer2 ? config.authServer2 : config.authServer;
                $.ajax({
                    type: "GET",
                    url: url + 'api.php?act=recharge',
                    dataType: "json",
                    data: {
                        user: user,
                        money: money,
                    },
                    success: function (data) {
                        layer.close(ii2);
                        if (data.code == 0) {
                            layer.msg('即将跳转，付款后务必返回该网站', {
                                time: 2500,
                                end: function () {
                                    chenmObj.notify_url = data.data.notify_url;
                                    chenmObj.recharge_call();
                                    window.open(data.data.jump_url);
                                }
                            });
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function (data) {
                        layer.close(ii2);
                        layer.open({
                            title: "提示",
                            content: "获取充值跳转链接失败，点击“确定”可进入授权站手动登录账号并充值~",
                            btn: ['确定', '取消'],
                            yes: function () {
                                window.open(url + 'api.php?act=jump');
                            }
                        });
                        return false;
                    }
                });
            }
        });
    },
    recharge_call: function () {
        this.interval1 = setInterval(function () {
            chenmObj.recharge_notify();
        }, 6000);
    },
    recharge_notify: function () {
        $.ajax({
            type: "GET",
            url: chenmObj.notify_url,
            dataType: "json",
            success: function (data) {
                if (data.code == 0) {
                    layer.msg('充值成功，可以购买插件啦~', {
                        time: 500,
                        end: function () {
                            layer.closeAll();
                            clearInterval(chenmObj.interval1);
                            chenmObj.interval1 = null;
                        }
                    });
                }
            },
            error: function (data) {
                layer.closeAll();
                clearInterval(chenmObj.interval1);
                chenmObj.interval1 = null;
                layer.alert("充值结果查询失败，如已付款请联系系统商补单！");
                return false;
            }
        });
    },
    //绑定插件账号
    bindUser: function () {
        var that = this;
        this._alert({
            title: '绑定授权平台账号',
            content: '<div class="panel-body"><div class="form-group"><div class="input-group"><div class="input-group-addon">授权平台账号</div><input type="text" class="form-control" id="plugin_user"/ placeholder="在星河授权站注册的用户账号"></div></div><div class="form-group"><div class="input-group"><div class="input-group-addon">授权平台密码</div><input type="text" class="form-control" id="plugin_pwd"/ placeholder="授权平台用户密码"></div></div><div class="form-group">还没有账号？<a class="reg">立即注册</a></div></div>',
            btn: ['提交绑定', '取消绑定'],
            yes: function (layid) {
                var user = $("#plugin_user").val();
                var pwd = $("#plugin_pwd").val();
                var ii2 = layer.load(2, {
                    shade: [0.1, '#fff']
                });
                $.ajax({
                    type: "POST",
                    url: "./ajax.php?act=plugin_binduser",
                    dataType: "json",
                    data: {
                        plugin_user: user,
                        plugin_pwd: pwd,
                    },
                    success: function (data) {
                        layer.close(ii2);
                        if (data.code == 0) {
                            chenmObj.pluginLogin = true;
                            layer.close(layid);
                            layer.msg(data.msg);
                        } else {
                            layer.alert(data.msg);
                        }
                    },
                    error: function (data) {
                        layer.close(ii2);
                        layer.msg("绑定失败，请稍后重试！");
                        return false;
                    }
                });
            },
            btn2: function () {
                layer.msg("只有绑定账号后才能安装插件哦~");
            }
        });
    },
    //Layer提示插件
    _alert: function (options) {
        var area = [$(window).width() > 640 ? '360px' : '95%', 'auto'];
        if (typeof options == 'string') {
            layer.open({
                title: ['温馨提示', 'color:red'],
                content: options,
                area: area,
                btn: ['我知道了'],
            });
        } else {
            if (!options.type) {
                options.type = 1;
            }
            if (!options.area) {
                options.area = area;
            }
            layer.open(options);
        }
    }
};
chenmObj.init();