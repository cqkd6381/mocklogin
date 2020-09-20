<?php
namespace App\MockLogin;

class MockLogin
{
	use ParseHtml;
	
	/**
	 * 提交登录的URL
	 */
	protected $url;
	
	/**
	 * 提交登录的参数
	 */
	protected $param;
	
	/**
	 * 登录的Token
	 */
	protected $loginToken;
	
	/**
	 * 初始化
	 */
	public function __construct()
	{
		$this->getLoginToken();
		
		$this->initParam();
	}
	
	/**
	* 初始化请求参数$param
	*/
	public function initParam()
	{
		echo 345;
	}
	
	/**
	* 登录
	*/
	public function login()
	{
		if (empty($this->url) || empty($this->param)) {
			return false;
		}

		$postUrl = $this->url;
		$curlPost = $this->param;
		// 初始化curl
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $postUrl);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		// 要求结果为字符串且输出到屏幕上
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		// post提交方式
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		// 运行curl
		$data = curl_exec($curl);
		curl_close($curl);

		return $data;
	}
}