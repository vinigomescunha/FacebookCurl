<?php

define('APP_ID', 'your_app_id');
define('APP_SECRET', 'your_app_secret');//see https://developers.facebook.com/apps/APP_ID/settings/
define('APP_URL', 'your_site_url'); //see https://developers.facebook.com/apps/APP_ID/settings/ site url
define("PAGE_ID", 'page_id_to_send_page_feed'); //case use feed of page

require_once("Class.Facebook.Curl.php");

/**
**Example User Post
*/
$curl = new FBCurl();
$curl->scopes[] = "publish_pages";//additional permissions see https://developers.facebook.com/docs/facebook-login/permissions?locale=pt_BR
$curl->infos['args'] = [ 
		'published'=>true,
		'message' => "Hello World",//to mention your pages @[pageid]
		'link' => 'http://www.facebook.com/FacebookBrasil'//to attach link
		];
$pid = $curl->send_feed();
echo "<a href='http://www.facebook.com/$pid' target='_blank'>Success</a>";

/*
** schedule post to page feed
** requires permissions to publish in pages

$date = date('Y-m-d h:i:s', strtotime("+30 minutes"));
$curl->infos["args"] = [
		'published'=>false,
		'scheduled_publish_time' => strtotime($date), //date time 13 min after and 6 months before
		'message' => "This is a post",//to mention your pages @[pageid]
		'link' => "http://www.facebook.com/mypageurl_or_id"
		];
print_r($curl);
print_r($curl->send_page_feed());
*/ 

//to post photo in the page or profile feed
/**
**
  $file = '/path/to/image.jpg';
  $curl->infos['args'][basename($file)] = new CurlFile($file);
  $curl->infos['url'] = "https://graph.facebook.com/" . PAGE_ID . '/photos';
*/
