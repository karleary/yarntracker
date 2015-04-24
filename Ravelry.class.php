<?php
/**
 * @file Ravelry.class.php
 * @author Will Steinmetz
 * This file contains methods for working with the Ravelry API
 */

/**
 * @class Ravelry
 * Abstract class for working with the Ravelry API
 */
abstract class Ravelry {
	private static $apiUrl = "https://api.ravelry.com";
	private static $requestTokenEndpoint = "https://www.ravelry.com/oauth/request_token";
	private static $authorizeEndpoint = "https://www.ravelry.com/oauth/authorize";
	private static $accessTokenEndpoint = "https://www.ravelry.com/oauth/access_token";
	
	/**
	 * Do an HTTP request
	 * @param string $urlReq
	 */
	private static function doHttpRequest($urlReq) {
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, "$urlReq");
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		
		// grab URL and pass it to the browser
		$request_result = curl_exec($ch);
		
		// close cURL resource, and free up system resources
		curl_close($ch);
		
		return $request_result;
	}
	
	/**
	 * Authenticate with Ravelry
	 */
	public static function authenticate() {
		$test_consumer = new OAuthConsumer(RAVELRY_ACCESS_KEY, RAVELRY_SECRET_KEY, NULL);
		
		//prepare to get request token
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
		$parsed = parse_url(self::$requestTokenEndpoint);
		$params = array(callback => RAVELRY_BASE_URL);
		parse_str($parsed['query'], $params);
		$params['oauth_callback'] = RAVELRY_BASE_URL . "/" . RAVELRY_CALLBACK_PATH;
		
		$req_req = OAuthRequest::from_consumer_and_token($test_consumer, NULL, "GET", self::$requestTokenEndpoint, $params);
		$req_req->sign_request($sig_method, $test_consumer, NULL);
		
		$req_token = self::doHttpRequest($req_req->to_url());
		
		//assuming the req token fetch was a success, we should have
		//oauth_token and oauth_token_secret
		parse_str ($req_token, $tokens);
		
		$oauth_token = $tokens['oauth_token'];
		$oauth_token_secret = $tokens['oauth_token_secret'];
		
		$auth_url = self::$authorizeEndpoint . "?oauth_token={$oauth_token}&oauth_callback=" . RAVELRY_BASE_URL . "/" . RAVELRY_CALLBACK_PATH;
		
		//Forward us to Ravelry for auth
		header("Location: $auth_url");
		exit();
	}

	public static function authenticate2($user, $pwd) {
		//$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		//$iv_size = mcrypt_enc_get_iv_size($cipher);
		$iv =  '1234567890123456';
		/*mcrypt_generic_init($cipher, substr(RAVELRY_SECRET_KEY, 0, 32), $iv);
		$cipherText = mcrypt_generic($cipher, "{$user}:{$pwd}");
		mcrypt_generic_deinit($cipher);*/
		$cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, RAVELRY_SECRET_KEY, "{$user}:{$pwd}", 'cbc', $iv);
		
		$data = array(
			'access_key' => RAVELRY_ACCESS_KEY,
			//'credentials' => "{$user}:{$pwd}",
			//'credentials' => base64_encode(hash_hmac('sha256', "{$user}:{$pwd}", RAVELRY_SECRET_KEY)),
			'credentials' => base64_encode($cipherText),
			//'query' => 'authenticate',
			'timestamp' => date('c')
		);
		
		$url = self::$apiUrl . '/authenticate?' . http_build_query($data);
		
		$signature = base64_encode(hash_hmac('sha256', $url, RAVELRY_SECRET_KEY, true));
		
		$data['signature'] = $signature;
		
		$final = http_build_query($data);
		$final = self::$apiUrl . '/authenticate?' . $final;
		
		print_r($final);
		exit();
		
		print_r(self::doHttpRequest($url));
	}

	/**
	 * Capture and store the access token in a session
	 */
	public static function storeAccessToken() {
		//We were passed these through the callback.
		$_SESSION[RAVELRY_SESSION_NAMESPACE]['oauth_token'] = $_REQUEST['oauth_token'];
		$_SESSION[RAVELRY_SESSION_NAMESPACE]['oauth_verifier'] = $_REQUEST['oauth_verifier'];
		$_SESSION[RAVELRY_SESSION_NAMESPACE]['username'] = $_REQUEST['username'];
	}
	
	/**
	 * Search for a yarn in Ravelry
	 * @param string $yarn - yarn to search for
	 * @return mixed
	 */
	public static function searchYarn($yarn) {
    	$consumer = new OAuthConsumer(RAVELRY_ACCESS_KEY, RAVELRY_SECRET_KEY, NULL);
		$auth_token = new OAuthConsumer($_SESSION[RAVELRY_SESSION_NAMESPACE]['oauth_token'], $_SESSION[RAVELRY_SESSION_NAMESPACE]['oauth_verifier']);
		$access_token_req = new OAuthRequest("GET", self::$accessTokenEndpoint);
		$access_token_req = $access_token_req->from_consumer_and_token($consumer, $auth_token, "GET", self::$accessTokenEndpoint);
		
		$access_token_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $auth_token);
		
		$after_access_request = self::doHttpRequest($access_token_req->to_url());
		parse_str($after_access_request, $access_tokens);
		print_r($access_token_req->to_url());
		
		$access_token = new OAuthConsumer($access_tokens['oauth_token'], $access_tokens['oauth_token_secret']);
		
    	$storeSearchReq = $access_token_req->from_consumer_and_token($consumer,
                $access_token, "GET", self::$apiUrl . "/yarns/search.json?query=" . urlencode($yarn) . '&page_size=50');
		
		$storeSearchReq->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $access_token);
		
		$after_request = self::doHttpRequest($storeSearchReq->to_url());
		
		// return the list of yarns
		return $after_request;
	}

	/** Karen Leary
	 * Search for a yarn in Ravelry
	 * @param string $yarn - yarn to search for
	 * @return mixed
	 */
	public static function searchMyStash() {
    	$consumer = new OAuthConsumer(RAVELRY_ACCESS_KEY, RAVELRY_SECRET_KEY, NULL);
		$auth_token = new OAuthConsumer($_SESSION[RAVELRY_SESSION_NAMESPACE]['oauth_token'], $_SESSION[RAVELRY_SESSION_NAMESPACE]['oauth_verifier']);
		$access_token_req = new OAuthRequest("GET", self::$accessTokenEndpoint);
		$access_token_req = $access_token_req->from_consumer_and_token($consumer, $auth_token, "GET", self::$accessTokenEndpoint);
		
		$access_token_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $auth_token);
		
		$after_access_request = self::doHttpRequest($access_token_req->to_url());
		parse_str($after_access_request, $access_tokens);
		print_r($access_token_req->to_url());
		
		$access_token = new OAuthConsumer($access_tokens['oauth_token'], $access_tokens['oauth_token_secret']);
		
    	$storeSearchReq = $access_token_req->from_consumer_and_token($consumer,
                $access_token, "GET", self::$apiUrl . "/people/". $username. "/stash/unified/list.json");
		
		$storeSearchReq->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $access_token);
		
		$after_request = self::doHttpRequest($storeSearchReq->to_url());
		
		// return the list of yarns
		return $after_request;
	}
}
