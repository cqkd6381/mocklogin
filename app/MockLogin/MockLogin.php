<?php
namespace App\MockLogin;

class MockLogin
{
	protected $page = 'https://www.chengmi.cn/';

	protected $imagePage = 'https://www.chengmi.cn/member/code.aspx';
    /**
     * 提交登录的URL
     */
	protected $url = 'https://www.chengmi.cn/member/ajax/User.ashx';
	
	/**
	 * 提交登录的参数
	 */
	protected $param;
	
	/**
	 * 登录的Token
	 */
	protected $loginToken;

    /**
     * @var resource
     */
    private $shareSh;

    /**
     * @var string
     */
    private $code;

    /**
	 * 初始化对象
	 */
	public function __construct()
	{
        // 创建 cURL 共享句柄，并设置共享 cookie 数据
        $this->shareSh = curl_share_init();
        curl_share_setopt($this->shareSh, CURLSHOPT_SHARE, CURL_LOCK_DATA_COOKIE);

        echo "<pre>";
//        $this->parseBalance();
        $this->getLoginToken($this->page);
	}

    /**
     * 解析页面，获取登录的Token
     * @param $page
     * @return mixed
     */
    public function getLoginToken($page)
    {
        // 初始化curl
        $ch1 = curl_init($this->page);
        curl_setopt($ch1, CURLOPT_SHARE, $this->shareSh);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true); // 执行之后不直接打印出来
        $html = curl_exec($ch1);
        curl_close($ch1);

        // 解析页面
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);

        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('//input[@id="login_token"]');
        $node = $nodes->item(0);

        $this->loginToken = $node->getAttribute('value');
        var_dump($this->loginToken);

        // 下一步1
        $this->parseImage();
    }

    /**
     * 解析并获取4位验证码
     * @return string
     */
    public function parseImage()
    {
        $token = '24.aaf4da724502c25d448cb9211f686419.2592000.1603201298.282335-22712335';
        $postUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/webimage_loc?access_token=' . $token;

        $code = '';
        while (1){
            // 初始化curl
            $ch2 = curl_init($this->imagePage);
            curl_setopt($ch2, CURLOPT_SHARE, $this->shareSh);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); // 执行之后不直接打印出来
            $img = curl_exec($ch2);
            curl_close($ch2);

            $param = ['image' => base64_encode($img)];
            // 初始化curl
            $ch3 = curl_init($postUrl);
            curl_setopt($ch3, CURLOPT_SHARE, $this->shareSh);
            curl_setopt($ch3, CURLOPT_HEADER, 0);
            // 要求结果为字符串且输出到屏幕上
            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
            // post提交方式
            curl_setopt($ch3, CURLOPT_POST, 1);
            curl_setopt($ch3, CURLOPT_POSTFIELDS, $param);
            // 运行curl
            $data = curl_exec($ch3);
            curl_close($ch3);

            var_dump($data);
            $result = json_decode($data);
            if($result && isset($result->words_result) && count($result->words_result)){
                $code = $result->words_result[0]->words;
                if($code && strlen($code) === 4){
                    $match = '/^[a-zA-Z0-9]+$/u';
                    preg_match($match, $code, $matches);
                    if(count($matches)){
                        break;
                    }
                }
            }
        };
        $this->code = $code;
        var_dump($this->code);

        // 下一步2
        $this->initParam();
    }
	
	/**
	* 初始化请求参数$param
	*/
    protected function initParam()
	{
        $this->param = [
            'username' => 'cqkd6381@163.com',
            'userpwd' => md5('cqkd6381@163.com'),
            'code' => $this->code,
            'b_type' => 1,
            'token' => $this->loginToken,
        ];

        // 下一步3
        $this->login();
	}
	
	/**
	* 登录
	*/
    protected function login()
	{
		if (empty($this->url) || empty($this->param)) {
			return false;
		}

		// 初始化curl
		$curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_SHARE, $this->shareSh);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		// 要求结果为字符串且输出到屏幕上
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		// post提交方式
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->param);
		// 运行curl
		$data = curl_exec($curl);
		curl_close($curl);

        if('10000' == substr($data , 0 , 5)){
            var_dump('登录成功');
            $this->parseBalance();
        }else{
            var_dump('登录失败');
        }
	}

	protected function parseBalance()
    {
        // 初始化curl
        $ch1 = curl_init('https://www.chengmi.cn/userpanel');
//        $ch1 = curl_init('https://www.chengmi.cn/');
        curl_setopt($ch1, CURLOPT_SHARE, $this->shareSh);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true); // 执行之后不直接打印出来
        $html = curl_exec($ch1);
        curl_close($ch1);

        // 解析页面
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);

        $xpath = new \DOMXPath($doc);
        $tbody = $xpath->query('//table/tr/td[@class="hsac"]');

        var_dump(trim($tbody->item(0)->nodeValue));
    }
}