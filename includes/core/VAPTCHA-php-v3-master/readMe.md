## v3 php example

1. 请在index.php中填入vid，同时在lib文件夹下vaptcha.class.php文件中填入vid和key
2. 测试离线模式需在前端设置`mode: offline`(即index.php文件中),  同时将lib文件夹下vaptcha.class.php文件中的`getChannelData()`方法中的url替换为： `$url = self::$offline_url. 'offline';`
