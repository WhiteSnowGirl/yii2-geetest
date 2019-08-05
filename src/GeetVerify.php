<?php
namespace geetest\verify;

use Yii;
use yii\base\Component;
use yii\base\Exception;
// use \geetest\verify\lib\GeetestLib;

class GeetVerify extends Component
{

	const CAPTCHA_ID = '48a6ebac4ebc6642d68c217fca33eb4d';
	const PRIVATE_KEY = '4f1c085290bec5afdc54df73535fc361';

	public $ip_address = "127.0.0.1"; # 网站用户id
	public $client_type = "web"; #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
	public $user_id = "test"; # 请在此处传输用户请求验证时所携带的IP
	private $GtSdk = "";

	public function __construct()
	{
		require_once dirname(__FILE__) . '/lib/class.geetestlib.php';
		$this->GtSdk = new \geetest\verify\lib\GeetestLib(self::CAPTCHA_ID, self::PRIVATE_KEY);
	}

	public function getCode()
	{
		$session = Yii::$app->getSession();
		
		$data = [
			"user_id" => $this->user_id, 
			"client_type" => $this->client_type, 
			"ip_address" => $this->ip_address
		];

		$status = $this->GtSdk->pre_process($data, 1);
		$session['gtserver'] = $status;
		$session['user_id'] = $data['user_id'];
		return $this->GtSdk->get_response_str();
	}

	public function virifyCode()
	{
		$session = Yii::$app->getSession();
		$data = [
			"user_id" => $this->user_id, 
			"client_type" => $this->client_type, 
			"ip_address" => $this->ip_address
		];
		$this->GtSdk = new \geetest\verify\lib\GeetestLib(self::CAPTCHA_ID, self::PRIVATE_KEY);
		if ($session['gtserver'] == 1) {   //服务器正常
	    $result = $this->GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
	    if ($result) {
	    	return true;
	    } else{
	    	return false;
	    }
		}else{  //服务器宕机,走failback模式
	    if ($this->GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
	    	return true;
	    }else{
	    	return false;
	    }
		}
	}
}