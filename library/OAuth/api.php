<?php

	foreach ($_REQUEST as $key => $value) {
		if (substr($key, 0, 6) == 'oauth_') { // ugly fix is ugly
			continue;
		}
		$_REQUEST[$key] = urldecode($value);
	}

	class APIBadRequestException extends Exception { }
	class APIUnauthorizedException extends Exception { }
	class APINotFoundException extends Exception { }
	class APIServerException extends Exception { }

	class OAuth {

		const NONCE_DURATION = 86400;
		const TIMESTAMP_OFFSET = 3600;

		public function __construct($action = '') {
			$this->action = $action;
                       
                        /*
			$this->checkParams();
			$this->getClient();
			$this->actionHandler();
			$this->checkSignature();
			$this->checkTimestamp();
			$this->checkNonce();
			$this->checkVersion();
			$this->actionHandler(2); // fugly fix is fugly
                         *
                         */
		}

		private function checkParams() {
			$mandatory = array('oauth_consumer_key', 'oauth_signature_method');
			if (isset($_REQUEST['oauth_signature_method']) && $_REQUEST['oauth_signature_method'] != 'PLAINTEXT') {
				$mandatory = array_merge($mandatory, array('oauth_nonce', 'oauth_timestamp'));
			}
			switch ($this->action) {
				case 'request_token':
					break;
				case 'access_token':
					$mandatory = array_merge($mandatory, array('oauth_token', 'oauth_verifier'));
				default:
					$mandatory = array_merge($mandatory, array('oauth_token'));
					break;
			}
			foreach ($mandatory as $param) {
				if (!isset($_REQUEST[$param])) {
					throw new APIBadRequestException('Missing parameter '.$param);
				}
			}
		}

		private function actionHandler($step = 1) {
			switch ($this->action) {
				case 'request_token':
					if ($step == 2) {
						$this->output = array('oauth_token' => OAuthUtils::uniqid(16), 'oauth_token_secret' => OAuthUtils::uniqid(16));
						$sql_query = "INSERT INTO oauth_token (client, oauth_token, oauth_token_secret, token_type) VALUES (".(int) $this->client['id'].", '".addslashes($this->output['oauth_token'])."', '".addslashes($this->output['oauth_token_secret'])."', 'request')";
						$server_db = 2;
						db_insert2($sql_query, $server_db);
					}
					break;
				case 'access_token':
					if ($step == 1) {
						$sql_query = "SELECT id, oauth_token, oauth_token_secret, user FROM oauth_token WHERE client = ".(int) $this->client['id']." AND oauth_token = '".addslashes($_REQUEST['oauth_token'])."' AND oauth_verifier = '".addslashes($_REQUEST['oauth_verifier'])."' AND token_type = 'verified'";
						$server_db = 2;
						if (!($token = db_query($sql_query, $server_db))) {
							throw new APIUnauthorizedException('Can not find token');
						}
						$this->output = array('oauth_token' => OAuthUtils::uniqid(16), 'oauth_token_secret' => OAuthUtils::uniqid(16));
						$this->token = $token[0];
					}
					else if ($step == 2) {
						$server_db = 2;
						$sql_query = "DELETE FROM oauth_token WHERE client = ".$this->client['id']." AND user = ".(int) $this->token['user']." AND token_type = 'access'"; // borro los tokens
						db_update2($sql_query, $server_db);
						$sql_query = "UPDATE oauth_token SET oauth_token = '".addslashes($this->output['oauth_token'])."', oauth_token_secret = '".addslashes($this->output['oauth_token_secret'])."', token_type = 'access', oauth_verifier = '' WHERE id = ".(int) $this->token['id'];
						db_update2($sql_query, $server_db);
					}
					break;
				default:
					$sql_query = "SELECT user, oauth_token, oauth_token_secret FROM oauth_token WHERE client = ".(int) $this->client['id']." AND oauth_token = '".addslashes($_REQUEST['oauth_token'])."' AND token_type = 'access'";
					$server_db = 2;
					if (!($token = db_query($sql_query, $server_db))) {
						throw new APIUnauthorizedException('Can not find token');
					}
					$this->token = $token[0];
					if (!($user = user_get_info_by_id($token[0]['user']))) {
						throw new APIUnauthorizedException('Unknow user');
					}
					$user_data = $user;
			}
		}

		private function checkVersion() {
			if (isset($_REQUEST['oauth_version']) && $_REQUEST['oauth_version'] != '1.0') {
				throw new APIBadRequestException('oauth_version is not supported');
			}		
		}

		public function authorizeHandler() {
			$verifier = OAuthUtils::uniqid(16);
			$sql_query = "UPDATE oauth_token SET user = ".(int) $user_data['id'].", verifier = '".addslashes($verifier)."' WHERE token = '".addslashes($_REQUEST['oauth_token'])."'";
			
		}

		public function loginHandler() {
			$sql_query = "SELECT user FROM oauth_token WHERE client = ".(int) $this->client['id']." AND oauth_token = '".addslashes($_REQUEST['oauth_token'])."' AND status = 'verified'";
		}

		private function getClient($getBy = 'key') {
			$sql_query = "SELECT * FROM oauth_client WHERE 1";
			switch ($getBy) {
				case 'key':
					$sql_query .= " AND client_key = '".addslashes($_REQUEST['oauth_consumer_key'])."'";
			}
			$sql_query .= " AND status = 'active'";
			$server_db = 2;
			$application = db_query($sql_query, $server_db);
			if (empty($application[0])) {
				throw new APIUnauthorizedException('No such app for oauth_consumer_key '.$_REQUEST['oauth_consumer_key']);
			}
			$this->client = $application[0];
		}

		private function getBaseString() {
			$http_scheme = $this->getHttpScheme();
			$base_string = $_SERVER['REQUEST_METHOD'].'&'.OAuthUtils::uencode($http_scheme.'://'.$_SERVER['HTTP_HOST']);
			if (($http_scheme == 'http' && $_SERVER['SERVER_PORT'] != 80) || ($http_scheme == 'https' && $_SERVER['SERVER_PORT'] != 443)) {
				$base_string .= ':'.$_SERVER['SERVER_PORT'];
			}
			$path = $_SERVER['REQUEST_URI'];
			if (($pos = strpos($path, '?')) > 0) {
				$path = substr($path, 0, $pos);
			}
			if (empty($path)) {
				$path = '/';
			}
			$base_string .= OAuthUtils::uencode($path);
			$params = array_merge($_GET, $_POST);
			if (isset($params['object'])) {
				unset($params['object']);
			}
			if (isset($params['method'])) {
				unset($params['method']);
			}
			if (isset($params['oauth_signature'])) unset($params['oauth_signature']);
			ksort($params);
			$base_string .= '&'.OAuthUtils::uencode(http_build_query($params));
			$this->base_string = $base_string; // debug;
			return $base_string;
		}

		private function getHttpScheme() {
			if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)) {
				$http_scheme = 'https';
			}
			else {
				$http_scheme = 'http';
			}
			return $http_scheme;
		}

		private function checkSignature() {
			if (!isset($_REQUEST['oauth_signature'])) $_REQUEST['oauth_signature'] = '';
			if ($_REQUEST['oauth_signature'] !== $this->genSignature()) {
				throw new APIUnauthorizedException('Invalid signature: enviada: '.$_REQUEST['oauth_signature'].' !== original: '.$this->genSignature());
			}
		}

		private function genSignature() {
			switch ($_REQUEST['oauth_signature_method']) {
				case 'HMAC-SHA1':
					$key = OAuthUtils::uencode($this->client['client_secret']).'&'.(isset($this->token) ? OAuthUtils::uencode($this->token['oauth_token_secret']) : '');
					$signature = base64_encode(hash_hmac('sha1', $this->getBaseString(), $key, true));
					break;
				default:
					throw new APIBadRequestException('Only HMAC-SHA1 is supported');
			}
			$GLOBALS['memc']->set('last_api_call', json_encode(array('key' => $key, 'basestring' => $this->base_string, 'signature' => $signature, 'enviada' => $_REQUEST['oauth_signature'])), 0, 3); // debug;
			return $signature;
		}

		private function checkNonce() {
			global $config;
			if ($_REQUEST['oauth_signature_method'] != 'PLAINTEXT') {
				$sql_query = "INSERT INTO oauth_nonce (nonce, client) VALUES ('".addslashes($_REQUEST['oauth_nonce'])."', ".$this->client['id'].")";
				$server_db = 2;
				db($config['mysql_db'], $server_db);
				if (!mysql_query($sql_query, $GLOBALS['mysql_connected'.$server_db])) {
					if (mysql_errno() == 1062) {
						throw new APIUnauthorizedException('Used nonce');
					}
				}
			}
		}

		private function checkTimestamp() {
			if ($_REQUEST['oauth_signature_method'] != 'PLAINTEXT') {
				if ($_REQUEST['oauth_timestamp'] < time() - OAuth::TIMESTAMP_OFFSET && $_REQUEST['oauth_timestamp'] > time() + OAuth::TIMESTAMP_OFFSET && $_REQUEST['oauth_timestamp'] < time() - OAuth::NONCE_DURATION) {
					throw new APIUnauthorizedException('oauth_timestamp is not valid');
				}
			}
		}

	}

	class OAuthUtils {

		static public function uencode($str) {
			return str_replace('%7E', '~', rawurlencode($str));
		}

		static public function uniqid($length = 32) {
			return substr(sha1(microtime(true).rand(0, 9999)), rand(0, 40 - $length), $length);
		}

		static public function output($array, $json = false) {
			if (isset($_REQUEST['callback'])) {
				echo strip_tags($_REQUEST['callback']).'('.json_encode($array).')';
			} else {
				echo http_build_query($array);
			}
		}

		static public function cleanTimestamp() {
			$sql_query = "DELETE ".OAuth::NONCE_DURATION;
			$server_db = 2;
			db_update2($sql_query, $server_db);
		}

		static public function authorizeHandler() {
			global $config;
			if (isset($user_data['id']) && isset($_REQUEST['confirm'])) {
				if (!isset($_REQUEST['oauth_token']) || !isset($_REQUEST['oauth_token_secret']) || !isset($_REQUEST['key'])) {
					throw new APIBadRequestException('Missing parameter');
				}
				$output = array('oauth_token' => $_REQUEST['oauth_token'], 'oauth_verifier' => OAuthUtils::uniqid(16));
				$sql_query = "UPDATE oauth_token SET user = ".(int) $user_data['id'].", oauth_verifier = '".addslashes($output['oauth_verifier'])."', token_type = 'verified' WHERE oauth_token = '".addslashes($_REQUEST['oauth_token'])."' AND oauth_token_secret = '".addslashes($_REQUEST['oauth_token_secret'])."'";
				$server_db = 2;
				if (!db_update2($sql_query, $server_db)) {
					throw new APIUnauthorizedException('Can not find token');
				}
				if (!empty($_REQUEST['callback']) && preg_match('/https?:\/\/.+/', $_REQUEST['callback'], $match)) {
					header('Location: '.$_REQUEST['callback'].'?'.http_build_query($output));
				}
				else {
					OAuthUtils::output($output);
				}
				exit;
			}
			else {
				if (!isset($_REQUEST['oauth_token'])) {
					throw new APIBadRequestException('Missing parameter oauth_token');
				}
				$sql_query = "SELECT client, oauth_token, oauth_token_secret FROM oauth_token WHERE oauth_token = '".addslashes($_REQUEST['oauth_token'])."' AND token_type  = 'request'";
				$server_db = 2;
				if (!($token = db_query($sql_query, $server_db))) {
					throw new APIUnauthorizedException('Can not find token');
				}
				if (!($client = OAuthUtils::getClientById($token[0]['client']))) {
					throw new APIUnauthorizedException('Can not find client');
				}
				$GLOBALS['token'] = $token[0];
				$GLOBALS['client'] = $client;
				if (isset($_REQUEST['oauth_callback'])) {
					$GLOBALS['client']['callback'] = $_REQUEST['oauth_callback'];
				}
			}
		}

		static public function getClientById($client) {
			$sql_query = "SELECT * FROM oauth_client WHERE id = ".(int) $client;
			$server_db = 2;
			if ($client = db_query($sql_query, $server_db)) {
				$getClientById = $client[0];
			}
			return isset($getClientById) ? $getClientById : false;
		}

		static public function checkClient() {
			$sql_query = "SELECT * FROM oauth_client WHERE client_key = '".addslashes($_REQUEST['oauth_consumer_key'])."' AND status = 'active'";
			$server_db = 2;
			$application = db_query($sql_query, $server_db);
			if (empty($application[0])) {
				throw new APIUnauthorizedException('No such app for oauth_consumer_key '.$_REQUEST['oauth_consumer_key']);
			}
			return $application[0];
		}

	}

?>
