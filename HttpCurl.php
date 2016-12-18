<?php
/**
 * 基于Http协议的Curl扩展 工具类
 * @author sohotdai <sohotdai@gmail.com>
 * @license 基于curl实现 可模拟多线程任务
 * @version V1.0
 */
Class HttpCurl
{
	/**
	 * @name 成员变量初始化
	 */
	protected $url  		   = array();  // url参数
    protected $data 		   = array();  // data参数
    protected $request_url     = '';       // 请求地址
    protected $request_data    = array();  // 请求参数
    protected $request_timeout = 30;       // 请求超时时间(单位秒)  0为无限等待
    protected $cookie_list 	   = '';  	   // cookie数据

    public function __construct()
    {
    	if (!extension_loaded('curl')) {
			exit('别闹！curl扩展呢？让你吃啦');
		}
    }

    /**
	 * @name 设置cookie
	 */
    public function do_setcookie($value='')
    {
    	$this->cookie_list = $value;
    }

    /**
	 * @name 设置超时时间
	 */
    public function set_timeout($value=30)
    {
    	$this->request_timeout = $value;
    }

    /**
	 * @name 设置请求地址
	 */
    public function set_request_url($value='')
    {
    	$this->request_url = $value;
    }

    /**
	 * @name 设置请求数据
	 */
    public function set_request_data($value='')
    {
    	$this->request_data = $value;
    }

    /**
	 * @name 对于可能的json数据的处理
	 */
	public function ob2ar($obj) {
		if(is_object($obj)) {
			$obj = (array)$obj;
			$obj = $this->ob2ar($obj);
		} elseif(is_array($obj)) {
			foreach($obj as $key => $value) {
				$obj[$key] = $this->ob2ar($value);
			}
		}
		return $obj;
	}

    /** 
     * @name 请求地址 
     * @param $url 
     */  
    public function url($url)  
    {  
        $this->url = $url;  
        $parseUrl  = parse_url($url);    
        $this->request_url  = '';  
        $this->request_url .= $parseUrl['scheme']=='https' ? 'https://' : 'http://';  
        $this->request_url .= $parseUrl['host'];  
        $this->request_url .= $parseUrl['port'] ? ':'.$parseUrl['port'] : ':80';  
        $this->request_url .= $parseUrl['path'];  
        parse_str($parseUrl['query'], $parseStr);  
        $this->request_data = array_merge($this->request_data, $parseStr);  
    }

    /**
	 * @name 发包方法
	 */
    public function send_pack($http_method = 'post', $https_req = false, $ssl_check = false, $send_cookie = false)
    {
        $returnData = array();
        // 1. 初始化  
        $ch = curl_init();  
        // 2. 设置选项，包括URL  
        switch ($http_method) {
        	case 'get':
        		curl_setopt($ch, CURLOPT_HTTPGET, 1); // 请求类型 get 
        		break;
        	case 'post':
        		curl_setopt($ch, CURLOPT_POST, 1); // 请求类型 post
        		break;
        	case 'put':
        		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'put'); 
        		break;
        	default:
        		exit();
        		break;
        }
        if ($send_cookie) {
	        // unset($this->cookielist['PHPSESSID']);  //如果PHPSESSID会影响通信
			// $cookielist = '';
			// foreach($this->cookielist as $key => $val ){
			// 	if($cookielist)
			// 	$cookielist .= ';';
			// 	$cookielist .= $key.'='.$val;
			// }
			curl_setopt($ch, CURLOPT_COOKIE, $this->cookie_list);
			/* cookie位置，一定要使用绝对路径 */
			// $cookie_path = dirname(__file__).'/cookie.txt';
			/* CURL收到的 HTTP Response 中的 Set-Cookie 存放的位置 */
			// curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path);
			/* CURL发送的 HTTP Request 中的 Cookie 存放的位置 */
			// curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);
        }
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: zh-cn','Cache-Control: no-cache','User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.101 Safari/537.36','Connection:keep-alive','Referer:Referer:http://***/wp-login.php?redirect_to=http%3A%2F%2F127.0.0.1%2Fwp-admin%2F&reauth=1'));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_URL, $this->request_url);   // 请求地址  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request_data);   // 请求数据 
        // 将curl_exec()获取的信息以文件流的形式返回,不直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->request_timeout);    // 连接等待时间  
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->request_timeout);           // curl允许执行时间
        // 若目标上线https
        if ($https_req) {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_check); //证书验证
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_check);
        }
        // 3. 执行并获取返回内容
        $output = curl_exec($ch);  
        if ($output === false)  
        {  
            $returnData['status']   = 0;  
            $returnData['data']     = curl_error($ch);  
        }  
        else  
        {  
            $returnData['status']   = 1;  
            $returnData['data']     = $output;            
        }  
        // 4. 释放curl句柄  
        curl_close($ch);  
        return $returnData;  
    }
}
?>