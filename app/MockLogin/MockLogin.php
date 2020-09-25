<?php
namespace App\MockLogin;

class MockLogin
{
    /**
     * 登录账号
     * @var string
     */
    protected $account = 'cqkd6381@163.com';
    /**
     * 登录密码
     * @var string
     */
    protected $password = 'cqkd6381@163.com';

    /**
     * 首页网址
     * @var string
     */
	protected $page = 'https://www.chengmi.cn/';

    /**
     * 账户详情页网址
     * @var string
     */
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
     * 共享cookie的curl句柄
     * @var resource
     */
    private $shareSh;

    /**
     * 百度OCR遍历解析后得到的4位验证码
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

        $this->getLoginToken($this->page);
	}

    /**
     * 步骤一：解析页面，获取登录的Token
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

        // 下一步
        $this->parseImage();
    }

    /**
     * 步骤二：解析并获取4位验证码
     * @return string
     */
    public function parseImage()
    {
        $token = '24.800ba3882c1230b64e8e0ebd2ea3ba14.2592000.1603594236.282335-22755253';
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
            }elseif(isset($result->error_code)){
                if($result->error_code == 17){
                    echo '提示：今日的百度OCR免费识别次数已达上限，请明日再试！';
                }else{
                    echo $result->error_msg;
                }
                return false;
            }
        };
        $this->code = $code;
        echo '<pre>';
        echo '本次模拟登录百度OCR解析的验证码为：' . $this->code;
        echo '</pre>';

        // 下一步
        $this->initParam();
    }
	
	/**
	* 步骤三：初始化请求参数$param
	*/
    protected function initParam()
	{
        $this->param = [
            'username' => $this->account,
            'userpwd' => md5($this->password),
            'code' => $this->code,
            'b_type' => 1,
            'token' => $this->loginToken,
        ];

        // 下一步
        $this->login();
	}
	
	/**
	* 步骤四：模拟登录
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
            echo '<pre>';
            echo '登录情况：登录成功（账号：' . $this->account . ', 密码：' . $this->password . '）';
            echo '</pre>';
            $this->parseBalance();
        }else{
            echo '<pre>';
            echo '登录情况：本次因账号密码错误或百度OCR识别验证码错误，登录失败，请刷新重试';
            echo '</pre>';
        }
	}

    /**
     * 步骤五：模拟登录成功后，解析页面获取账户余额
     */
	protected function parseBalance()
    {
        // 初始化curl
        $ch1 = curl_init('https://www.chengmi.cn/userpanel');
        curl_setopt($ch1, CURLOPT_SHARE, $this->shareSh);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true); // 执行之后不直接打印出来
        $html = curl_exec($ch1);
        curl_close($ch1);

        // 解析页面
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);

        $xpath = new \DOMXPath($doc);
        $tbody = $xpath->query('//table/tr/td[@class="hsac"]');

        echo '<pre>';
        echo '账户余额：' . trim($tbody->item(0)->nodeValue);
        echo '</pre>';
    }
}