<?php
class vk_com implements SocialLoginPlugin {
	public static function login( $code ) {
		global $wgVkSecret, $wgVkAppId;
		$host = $_SERVER["SERVER_NAME"];
		$r = SLgetContents("https://oauth.vk.com/access_token?redirect_uri=http://$host/Special:SocialLogin?service=vk.com&client_id=$wgVkAppId&client_secret=$wgVkSecret&code=$code");
		$response = json_decode($r);
		if (!isset($response->access_token)) return false;
		$access_token = $response->access_token;
		$id = $response->user_id;
		$r = SLgetContents("https://api.vk.com/method/users.get?uid=$id&fields=first_name,last_name,nickname,sex,bday,screen_name&access_token=$access_token");
		$response = json_decode($r);
		$response = $response->response[0];
		$name = SocialLogin::generateName(array($response->screen_name, $response->nickname, $response->last_name . " " . $response->first_name));

		return array(
			"id" => $id,
			"service" => "vk.com",
			"profile" => "$id@vk.com",
			"name" => $name,
			"email" => "",
			"realname" => $response->last_name . " " . $response->first_name,
			"access_token" => $access_token
		);
	}

	public static function check( $id, $access_token ) {
		$r = SLgetContents("https://api.vk.com/method/getUserInfo?access_token=$access_token");
		$response = json_decode($r);
		//$response = $response->response;
		if (!($response = $response->response) || !isset($response->user_id) || $response->user_id != $id) return false;
		$r = SLgetContents("https://api.vk.com/method/users.get?uid=" . $response->user_id . "&fields=first_name,last_name&access_token=$access_token");
		$response = json_decode($r);
		$response = $response->response[0];
		return array(
			"id" => $id,
			"service" => "vk.com",
			"profile" => "$id@vk.com",
			"realname" => $response->last_name . " " . $response->first_name,
			"access_token" => $access_token
		);
	}
	
	public static function loginUrl( ) {
		global $wgVkAppId;
		$host = $_SERVER["SERVER_NAME"];
		return "https://oauth.vk.com/authorize?client_id=$wgVkAppId&display=popup&redirect_uri=http://$host/Special:SocialLogin?service=vk.com&response_type=code";
	}
}