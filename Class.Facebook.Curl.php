<?php
set_time_limit(800);
session_start();
/** 
require basic permissions
https://developers.facebook.com/docs/facebook-login/permissions?locale=pt_BR

to page and user best experience look the permissions
	manage_pages
	publish_pages
	publish_actions
 */
$_SESSION['ptoken'] ="";
Class FBCurl {
	public $script_url;
	public $scopes = [];
	public $infos = [];

	/**
	  constructor
	 */
	function FBCurl() {
		$this->script_url = APP_URL . $_SERVER['SCRIPT_NAME'];
	}

	/** *
	 curl call to facebook, return post id
	 */
	public function curl() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->infos["url"]);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->infos["args"]);
		$data = curl_exec($ch);
		$decode = json_decode($data);
		if(isset($decode->id))  
			return $decode->id;
	}

	/** *
	 return user facebook access token
	 */
	public function user_access_token() {
		if( isset($_SESSION['token']) && empty($_SESSION['token']))
			return $_SESSION['token'];

	  $code = (isset($_REQUEST["code"]) && !empty($_REQUEST["code"]) ? $_REQUEST["code"] : "" );
		if (empty($code)) {
			$dialog_url = "http://www.facebook.com/dialog/oauth?client_id=" . APP_ID . "&redirect_uri=" . urlencode($this->script_url) . "&scope=" . implode(",", $this->scopes );
			header("Location: $dialog_url");
		}
		$token_url = "https://graph.facebook.com/oauth/access_token?client_id=" . APP_ID . "&redirect_uri=" . urlencode($this->script_url) . "&client_secret=" . APP_SECRET . "&code=" . $code;
		$at = @file_get_contents($token_url);
		$p1 = explode("&", $at);
		$p2 = explode("=", $p1[0]);
		return @$p2[1];
	}

	/** *
	 return page facebook access token, set in the session
	get facebook user token and request facebook page token
	 */
	public function page_access_token() {
		/** if page token exist in the session dont call additional params */
		if( isset($_SESSION['ptoken']) && empty($_SESSION['ptoken'])) 
			return $_SESSION['ptoken'];
		/**  */
		$fut = $this->user_access_token();
		$token_url = "https://graph.facebook.com/" . PAGE_ID . "/?fields=access_token&access_token=" . $fut;
		$fpt = @file_get_contents($token_url);
		$fpt = json_decode($fpt, true);
		if( isset($fpt["access_token"]) && !empty($fpt["access_token"])) {
			$_SESSION['ptoken'] = $fpt["access_token"];
			return @$fpt["access_token"];
		}
	}

	/** send post to page feed facebook
	 return post id
	 */
	public function send_page_feed() {
		$FB_ACCESS_TOKEN = (isset($_SESSION['ptoken']) && !empty($_SESSION['ptoken'])) ? $_SESSION['ptoken'] : $this->page_access_token();
		$this->infos['url'] = "https://graph.facebook.com/" . PAGE_ID . "/feed?access_token=" . $FB_ACCESS_TOKEN;
		return $this->curl();
	}

	/** send post to user feed facebook
	 return post id
	 */
	public function send_feed() {
		$FB_ACCESS_TOKEN = (!empty($_SESSION['token'])) ? $_SESSION['token'] : $this->user_access_token() ;
		$this->infos['url'] = "https://graph.facebook.com/me/feed?access_token=" . $FB_ACCESS_TOKEN;
		return $this->curl();
	}
}

