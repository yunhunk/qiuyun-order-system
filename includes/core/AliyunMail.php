<?php
namespace core;

class AliyunMail
{
    private $AccessKey_Id;
    private $AccessKey_Secret;

    public function __construct($AccessKey_Id, $AccessKey_Secret)
    {
        $this->AccessKey_Id     = $AccessKey_Id;
        $this->AccessKey_Secret = $AccessKey_Secret;
    }
    private function aliyunSignature($parameters, $AccessKey_Secret, $method)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = $method . '&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature    = base64_encode(hash_hmac("sha1", $stringToSign, $AccessKey_Secret . "&", true));

        return $signature;
    }
    private function percentEncode($str)
    {

        $str = urlencode($str);
        $str = preg_replace('/\s/', '%20', $str);
        $str = preg_replace('/\+/', '%20', $str);
        $str = preg_replace('/\*/', '%2A', $str);
        $str = preg_replace('/%7E/', '~', $str);
        return $str;
    }

    public function send($to, $sub, $msg, $from, $from_name)
    {
        if (empty($this->AccessKey_Id) || empty($this->AccessKey_Secret)) {
            return false;
        }

        $url  = 'https://dm.aliyuncs.com/';
        $data = array(
            'Action'           => 'SingleSendMail',
            'AccountName'      => $from,
            'ReplyToAddress'   => 'false',
            'AddressType'      => 1,
            'ToAddress'        => $to,
            'FromAlias'        => $from_name,
            'Subject'          => $sub,
            'HtmlBody'         => $msg,
            'Format'           => 'JSON',
            'Version'          => '2015-11-23',
            'AccessKeyId'      => $this->AccessKey_Id,
            'SignatureMethod'  => 'HMAC-SHA1',
            'Timestamp'        => gmdate('Y-m-d\TH:i:s\Z'),
            'SignatureVersion' => '1.0',
            'SignatureNonce'   => random(8),
        );
        $data['Signature'] = $this->aliyunSignature($data, $this->AccessKey_Secret, 'POST');
        $ch                = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $json     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode == 200) {
            return true;
        } else {
            $arr = json_decode($json, true);
            return $arr['Message'];
        }
    }
}
