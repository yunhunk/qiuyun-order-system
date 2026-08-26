<?php
// 大福**网对接 模拟V1.0
namespace core;
class DafuApi {
    private $url;
    private $user;//账号名
    private $pwd;//账号密码
    private $code_user = 'superkinger';//超人打码平台账号
    private $code_pwd = '5252088yun';//超人打码平台密码
    private $cookie;//验证码cookie
    private $code_cookie;//验证码cookie
    private $loginErrNum = 0;
    public 	$tid;//商品ID
    public  $price;//商品价格
    public  $errorinfo;
    public  $source;//商品页面源代码

	public function __construct($url, $user, $pwd) {
		$this->url  = $url;
        $this->user = $user;
        $this->pwd  = $pwd;

        $this->setError('[305]还未设置具体商品');

        if (empty($url)) {
        	$this->setError('[201]对接站地址不能为空！');
        }
        elseif (empty($user)) {
        	$this->setError('[202]对接站账号不能为空！');
        }
        elseif (empty($pwd)) {
        	$this->setError('[203]对接站密码不能为空！');
        }
        else{
        	$login = $this->checkLogin();
	        if ($login!="登录成功") {
	        	$this->setError('[204]'.$login);
	        }
        }
        return true;
    }

    private function setCookie($cookie){
    	$filename = ROOT . 'other/dafu_' . md5($this->url). '.txt';
        file_put_contents($filename, $cookie, FILE_APPEND | LOCK_EX);
    }

    private function getCookie(){
    	$filename = ROOT . 'other/dafu_' . md5($this->url). '.txt';
        return file_get_contents($filename);
    }

    private function writeGdLog($msg){
    	$filename = ROOT."other/epay/dafu/gdlog.txt";
    	//file_put_contents($filename, $msg, FILE_APPEND | LOCK_EX);
        $fp = fopen($filename, "a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"-------------------------------\n".date("Y-m-d H:i:s")."\n".$msg."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
    }

    private function checkLogin() {

    	if ($this->loginErrNum>=2){
    		$this->loginErrNum = 0;
    		return '登录失败，验证码已连续错误5次！';
    	}

    	$url = 'http://' .$this->url. '/login.html';

        $data1 = $this->getCurl($url, 0, 0, 0, 1);
        preg_match_all("/Set-Cookie: (.*);/iU", $data1, $matchs);
        $this->cookie = '';
		foreach ($matchs[1] as $val) {
			$this->cookie .= $val . "; ";
		}

		$codeData = $this->getCode($this->cookie);
		if ($codeData['code']==0) {
			$post = 'email='.$this->user.'&password='.$this->pwd.'&captcha='.$codeData['result'].'&remember=1';
			$result = $this->getCurl($url, $post, $url, $this->cookie, 1);
			$this->writeGdLog("登录平台提交cookie[".$this->loginErrNum."]：".$this->cookie);
			$this->writeGdLog("登录平台提交[".$this->loginErrNum."]：".$post);
			$this->writeGdLog("登录平台返回[".$this->loginErrNum."]：".$result);
			if (stripos($result, '验证码错误')!==false) {
				$this->loginErrNum = $this->loginErrNum+1;
				return $this->checkLogin();
			}
			elseif (stripos($result, '登录成功')!==false) {
				preg_match_all("/Set-Cookie: (.*);/iU", $result, $matchs);
				foreach ($matchs[1] as $val) {
					$this->cookie .= $val . "; ";
				}
				$this->setCookie($this->cookie);
				return '登录成功';
			}
			elseif (stripos($result, '不匹配')!==false||stripos($result, '禁用')!==false) {
				return '密码错误';
			}
			else{
				return $this->getSubstr($result, '<html', '</html>');
			}
		}
        else{
        	$this->loginErrNum = $this->loginErrNum+1;
			return $this->checkLogin();
		}
	}

	public function getCode($cookie) {
		$data1 = $this->getCurl('http://' .$this->url. '/captcha.html', 0, 0, $cookie);
		$post = array(
			'username' => $this->code_user,
			'password' => $this->code_pwd,
			'softId'   => '',
			'imgdata'  => bin2hex($data1)
		);
		$data2 = $this->getCurl('http://api2.sz789.net:88/RecvByte.ashx', http_build_query($post));
        $json = json_decode($data2, true);
        $result = array();
        $result['code'] = -1;
		if (is_array($json)) {
			if ($json['info']=='1') {
				$result['code'] = 0;
				$result['result'] = $json['result'];
			}
			else{
                $result['msg'] = '识别失败，状态码['.$json['info'].']';
			}
		}
		else{
			$result['msg'] = '解析失败，返回：'.$data2;
		}
		$this->writeGdLog("验证码识别返回[".$this->loginErrNum."]：".$data2);
		return $result;
	}

	private function strToHex($string)   
	{   
	      $hex="";   
	      for ($i=0;$i<strlen($string);$i++) {
	      	$hex.=dechex(ord($string[$i]));   
	      	$hex=strtoupper($hex);   
	      } 
	      return   $hex;   
    }   

	public function getSubstr($str, $leftStr, $rightStr)
	{
		$left = strpos($str, $leftStr);
		$right = strpos($str, $rightStr,$left);
		if($left < 0 or $right < $left) return '';
		return substr($str, $left + strlen($leftStr), $right-$left-strlen($leftStr));
	}

    private function setError($msg) {
		$this->errorinfo.= $msg."\n";
	}

    public function setTid($shopurl) {
		$this->tid = $this->getTid($shopurl);
		if ($this->tid<1) {
			$this->errorinfo = str_ireplace("[305]还未设置具体商品", '[306]商品链接异常或不正确！', $this->errorinfo);
		}
		return true;
	}

	private function getTid($shopurl) {
		if (preg_match('/trade\?id=([0-9]+)/', $shopurl, $match)) {
			return $match[1];
		}
		return 0;
	}

	public function getPirce() {
		if (empty($this->source))$this->setError('[406]商品页面源代码为空！');
		if (preg_match('/<span class="trade-price">¥([0-9\.]+)<\/span>/', $this->source, $match)) {
			return $match[1];
		}
		return 0;
	}

	public function getBuyStatus() {
		if (empty($this->source))$this->setError('[406]商品页面源代码为空！');
		if (preg_match('/onclick="var a=\'0\'/', $this->source, $match)) {
			return false;
		}
		return true;
	}

	public function getBuykucun() {//获取卡密库存
		if (empty($this->source))$this->setError('[406]商品页面源代码为空！');
		if (preg_match('/onclick="var a=\'([0-9]+)\'/', $this->source, $match)) {
			return $match[1];
		}
		return 0;
	}

	public function buyOrder($num=1, $data = array()) {

        if (is_array($data)) {
        	$i=1;
        	foreach ($data as $value) {
                $post .= 'ext'.$i.'='.$value.'&';
        		$i++;
        	}
        }
        $post .= 'gid='.$this->tid.'&count='.$num;

        $result = [];
		$result['code'] = -1;
		$referer = 'http://' .$this->url. '/';
        $url = 'http://' .$this->url. '/buy';
        $cookie = $this->getCookie();
        if ($cookie=='') {
        	$logindata = $this->checkLogin();
        	if ($logindata=='登录成功') {
        		$cookie = $this->getCookie();
        	}
        	else{
        		$result['msg'] = '登录失败，详情请获取错误日志！';
        		return $result;
        	}
        }
		$data1 = $this->getCurl($url, $post, $referer, $cookie);
		if (stripos($data1, '订单生成成功')) {
			$post2 = $this->getPayData($data1);
			if ($post2) {
				$url2 = 'http://' .$this->url. '/pay';
				$referer2 = $url;
				$data2 = $this->getCurl($url2, $post2, $referer2, $cookie);
				if (stripos($data2, '支付成功')) {
					$result['code'] = 0;
					$result['msg'] = '购买失败';
				}
				else{
					$this->writeGdLog("订单支付返回：".$data2);
					$result['msg'] = '支付表单数据生成失败';
				}
			}
			else{
				$result['msg'] = '支付表单数据生成失败';
			}
		}
		else{
			$this->writeGdLog("订单生成返回：".$data1);
			$result['msg'] = '购买页面数据返回异常，'.$data1;
		}
		return $result;
	}

    private function getPayData($source) {
        $html = $this->getSubstr($source, 'method="post">', '<script src="/static/index/js/fingerprint2.min.js"></script>');
        $html = str_replace(["\r\n","\n"], '', $html);
        $html = str_replace('"', "'", $html);
        $post = '';
        if (preg_match_all("/name='([a-zA-Z0-9\_\-]+)' value=\"([a-zA-Z0-9\_\-]+)\"/", $html, $matches)) {
            foreach ($matches[1] as $key => $value) {
            	$post.=$value.'='.$matches[2][$key].'&';
            }
        	//$post = trim($post, '&');
        	$post .= 'murmur='.$this->getFp($_SERVER['HTTP_HOST']);
        	$this->writeGdLog("匹配支付表单：提交数据=>".$post);
        }
        else{
        	$this->writeGdLog("匹配支付表单：原始文本=>".$html);
        }

		return $post;
	}

	public function getFp($url){

		  function Fingerprint($str) {   
		   $kFingerPrintSeed = 19820125;
		   return MurmurHash64A($str, $kFingerPrintSeed);
		  }
		  
		  function getBytes($str) {
		     $len = strlen($str);
		     $bytes = array();
		     for($i=0;$i<$len;$i++) {
		         $bytes[] =  ord($str[$i]);
		     }
		     return $bytes;
		  }
		  
		  function multi64($x, $y) {
		     $result = 0;
		     for($i = 0; $i < 64; $i++) {
		         $bit = ($x >> $i) & 1;
		         if($bit) {
		             $result = add64($result, $y << $i);
		         }
		     }
		     return $result;
		 }
		  
		 function r_shift($num, $bit) {
		     if($bit <= 0) return $num;
		     if($num > 0) {
		         return $num>>$bit;
		     } else {
		         $num = $num>>1;
		         $num = $num & 0x7FFFFFFFFFFFFFFF;
		         return r_shift($num, $bit - 1);
		     }
		 }
		  
		 function add64($x,$y){
		     $jw = $x & $y;
		     $jg = $x ^ $y;
		     while($jw)
		     {
		         $t_a = $jg;
		         $t_b = $jw << 1;
		         $jw = $t_a & $t_b;
		         $jg = $t_a ^ $t_b;
		      }
		      return $jg;
		 }
		  
		 function MurmurHash64A($key, $seed) {
			$m = -4132994306676758123;
			$r = 47;
			$len = strlen($key);

			$h = $seed ^ (multi64($len, $m));
			$bytes = getBytes($key);

			for ($i = 0; $i <= ($len / 8) - 1; $i++) {
				$k = 0;
				for ($j = 0; $j < 8; $j++) {
					$k = ($k << 8) | $bytes[$i * 8 + 7 - $j];
				}
				$k = multi64($k, $m);
				$k ^= r_shift($k, $r);
				$k = multi64($k, $m);
				$h ^= $k;
				$h = multi64($h, $m);
			}

			$data2_index = $len - $len % 8;
			switch ($len & 7) {
				case 7: $h ^= ($bytes[$data2_index + 6]) << 48;
				case 6: $h ^= ($bytes[$data2_index + 5]) << 40;
				case 5: $h ^= ($bytes[$data2_index + 4]) << 32;
				case 4: $h ^= ($bytes[$data2_index + 3]) << 24;
				case 3: $h ^= ($bytes[$data2_index + 2]) << 16;
				case 2: $h ^= ($bytes[$data2_index + 1]) << 8;
				case 1: $h ^= ($bytes[$data2_index + 0]);
				$h = multi64($h, $m);
			};

			$h ^= r_shift($h, $r);
			$h = multi64($h, $m);
			$h ^= r_shift($h, $r);

			return $h;
		}

		$string = Fingerprint($url);
		if ($string<=0) {
			$string = bcadd('18446744073709551616', $string);
		}
		return $string;
	}

	private function getCurl($url,$post=0,$referer=0,$cookie=0,$header=0) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$httpheader[] = "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
		$httpheader[] = "Accept-Encoding: gzip, deflate";
		$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
		$httpheader[] = "Connection: close";
		$httpheader[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

		if($header){
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
		}

		if ($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}

		if($referer){
			if($referer==1){
				curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
			}else{
				curl_setopt($ch, CURLOPT_REFERER, $referer);
			}
		}

		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 UBrowser/6.2.4098.3 Safari/537.36');

		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$ret = curl_exec($ch);
		if(curl_errno($ch)){
			$ret = curl_error('ErrorResult:'.$ch);
		}
		curl_close($ch);
		return $ret;
	}

}
