<?php
!defined('DS') && define('DS', DIRECTORY_SEPARATOR);
if (ini_get('display_errors')) {
    ini_set('display_errors', 'off');
}
include '../includes/common.php';

$title = '检测更新';
checkLogin();

include 'head.php';


echo '
<style>
    .update-container {
        max-width: 760px;
        margin: 4rem auto;
        padding: 0 2rem;
    }
    .update-title {
        margin: 0 0 1.2rem;
        font-size: 1.6rem;
        font-weight: 600;
        color: #006400; 
        letter-spacing: 0.02em;
    }
    .update-core {
        margin: 0 0 2rem;
        font-size: 1rem;
        line-height: 1.7;
        color: #333333;
    }
    .update-tip {
        padding: 1.2rem;
        margin-bottom: 2rem;
        background-color: #f0f8f0; 
        border-left: 3px solid #006400; 
        border-radius: 0 4px 4px 0;
        color: #333333;
        font-size: 0.95rem;
    }
    .steps-title {
        margin: 0 0 1rem;
        font-size: 1.1rem;
        font-weight: 500;
        color: #006400; 
    }
    .update-steps {
        margin: 0;
        padding-left: 1.5rem;
        color: #333333;
    }
    .update-steps li {
        margin-bottom: 0.8rem;
        line-height: 1.6;
    }
</style>

<body>
    <div class="update-container">
        <h2 class="update-title">系统更新方式说明</h2>
        
        <p class="update-core">基于安全性以及稳定性的考虑下，采用手动覆盖更新包的更新方式，取消在线更新</p>
        
        <div class="update-tip">
            重要提示：更新前请务必备份系统核心数据（如数据库、配置文件），避免文件覆盖导致数据丢失；更新包仅支持从官方指定渠道下载。
        </div>
         <div class="update-tip">
            技术支持：Leiong  QQ：3429007740（备注来意）
        </div>
        
        
        <h3 class="steps-title">手动更新步骤</h3>
        <ol class="update-steps">
            <li>从官方渠道下载对应版本的更新包，解压至本地文件夹；</li>
            <li>将解压后的更新文件，覆盖至系统对应目录（请勿删除原有非更新文件）；</li>
            <li>覆盖完成后，刷新系统页面，验证更新是否生效。</li>
            <li>（更新渠道请见后台首页公告）</li>

        </ol>
    </div>
</body>
';
