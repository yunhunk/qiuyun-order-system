<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>VAPTCHA php sample</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://bootswatch.com/4/lux/bootstrap.min.css">
    <style>
        .vaptcha-container {
            width: 100%;
            height: 36px;
            line-height: 36px;
            text-align: center;
        }
        .vaptcha-init-main {
            display: table;
            width: 100%;
            height: 100%;
            background-color: #EEEEEE;
        }
        ​
        .vaptcha-init-loading {
            display: table-cell;
            vertical-align: middle;
            text-align: center
        }
        ​
        .vaptcha-init-loading>a {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: none;
        }
        ​
        .vaptcha-init-loading>a img {
            vertical-align: middle
        }
        ​
        .vaptcha-init-loading .vaptcha-text {
            font-family: sans-serif;
            font-size: 12px;
            color: #CCCCCC;
            vertical-align: middle
        }
    </style>
</head>
<body>
    
    <form class="row justify-content-md-center" action="" method="post">
        <div class="col-lg-4 card mt-5">
            <div class="card-body">
                <div class="alert alert-dismissible alert-success">请点击验证</div>
                <!-- <div class="form-group">
                    <input type="text" class="form-control" name="phone" placeholder="手机号">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="label" placeholder="签名(eg: 网站名称)">
                </div> -->
                <div class="form-group">
                    <div class="vaptcha-container" id="vaptchaContainer">
                        <div class="vaptcha-init-main">
                            <div class="vaptcha-init-loading">
                                <a href="/" target="_blank">
                                    <img src="https://cdn.vaptcha.com/vaptcha-loading.gif" />
                                </a>
                                <span class="vaptcha-text">Vaptcha启动中...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="form-group">
                    <button class="btn btn-sm btn-success">提交</button>
                </div> -->
                <div class="form-group">
                    <button class="btn btn-sm btn-success validate">验证</button>
                </div>
            </div>
        </div>
    </form>
    <script src="https://r.vaptcha.com/public/js/jquery.min.js"></script>
    <script src="https://v.vaptcha.com/v3.js"></script>
    <script>
      vaptcha({
        vid: '', //必填
        type: 'click', //必填
        https: false,
        // lang: 'zh-TW',
        container: '#vaptchaContainer', // 嵌入式、点击式必填
        scene: 1, //不填默认为0
        offline_server: '/vaptcha.php', //必填
        //mode: 'offline' // 测试离线模式时使用
      }).then(function (obj) {
        // obj.listen('pass', function () {
        // 验证通过时触发
        // })
        $('.validate').click(function (e) {
          e.preventDefault();
          var token = obj.getToken();
          $.post('./vaptcha.php', { token, scene: 1 }, function (r) {
            if (r.success) {
                $('.alert').html('验证成功')
            } else {
                $('.alert').html(r.msg)
              obj.reset()
            }
          }, 'json')
        })
        obj.render()
      })
    </script>
</body>
</html>
