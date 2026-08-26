<?php
namespace core;

/**
 * 阿里云OSS对象储存 封装简易类
 * By 星河
 *
 * Object Storage Service(OSS)'s client class, which wraps all OSS APIs user could call to talk to OSS.
 * Users could do operations on bucket, object, including MultipartUpload or setting ACL via an OSSClient instance.
 * For more details, please check out the OSS API document:https://www.alibabacloud.com/help/doc-detail/31947.htm
 */

use OSS\Core\OssException;
use OSS\OssClient;

class Alioss
{
    public $ossClient        = null;
    private $accessKeyId     = "<yourAccessKeyId>";
    private $accessKeySecret = "<yourAccessKeySecret>";

    // Endpoint以杭州为例，其它Region请按实际服务器IP归属填写
    private $endpoint = "http://oss-cn-hongkong.aliyuncs.com";
    // 存储空间名称
    private $bucketName = '';

    private $ErrorMsg = '';

    public function __construct($accessKeyId, $accessKeySecret, $bucketName, $endpoint = '')
    {
        global $conf;
        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录RAM控制台创建RAM账号。
        $this->accessKeyId     = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;

        // 设置请求节点。
        if (!empty($endpoint)) {
            $this->endpoint = $endpoint;
        }

        // 设置存储空间名称。
        if (empty($bucketName)) {
            $this->setErrInfo(__FUNCTION__ . ": bucketName 不能为空！");
        }
        $this->bucketName = $bucketName;

        try {
            $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            return true;

        } catch (OssException $e) {
            $this->setErrInfo(__FUNCTION__ . ": FAILED\n" . $e->getMessage() . "\n");
            return false;
        }
    }

    /**
     * 简单上传文件 带自动回调
     */
    public function uploadFile($filePath, $filename, $callbackUrl = '')
    {
        if ($callbackUrl == "") {
            $callbackUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/other/alioss_return.php';
        }
        $url =
            '{
                "callbackUrl":"' . $callbackUrl . '",
                "callbackHost":"' . $_SERVER['HTTP_HOST'] . '",
                "callbackBody":"bucket=${bucket}&object=${object}&etag=${etag}&size=${size}&mimeType=${mimeType}&imageInfo.height=${imageInfo.height}&imageInfo.width=${imageInfo.width}&imageInfo.format=${imageInfo.format}&my_var1=${x:var1}&my_var2=${x:var2}",
                "callbackBodyType":"application/x-www-form-urlencoded"
                }';
        $options = array(OssClient::OSS_CALLBACK => $url);
        try {
            $data = $this->ossClient->uploadFile($this->bucketName, $filename, $filePath, $options);
            if (array_key_exists('info', $data)) {
                $result = ['code' => 0, 'msg' => 'succ', 'info' => $data['info']];
            } else {
                $result = ['code' => -1, 'msg' => '上传失败，' . json_encode([$data])];
            }
            return $result;
        } catch (OssException $e) {
            return ['code' => -1, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 设置错误信息
     */

    private function setErrInfo($msg)
    {
        $this->ErrorMsg .= $msg . "\n";

    }

    /**
     * 获取错误信息
     */

    public function getErrInfo()
    {
        return $this->ErrorMsg;
    }
}
