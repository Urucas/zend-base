<?php

require '../library/tw/tmhOAuth.php';
require '../library/tw/tmhUtilities.php';


class Model_Twitter {
	
	public function tweet($message, $lat, $lng) {
	
		$tmhOAuth = new tmhOAuth(array(
		  'consumer_key'    => '39fWjp29UuJkoNxzK1cAg',
		  'consumer_secret' => '7hNmlFPXGbEycerqo8wVcD9V5Q6Ebof7OkKCoBPw',
		));

		$here = tmhUtilities::php_self();

		$tmhOAuth->config['user_token']  = "334266070-LnDtpDLIiRK88aiWBE2RxaxVDNa5nDEphY1dTTKh";
		$tmhOAuth->config['user_secret'] = "iLZtzlTR6BmB40lWryCZyDQ89kOPrYSDNCjIZ8n2Y0Q";

		$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/account/verify_credentials'));

		if ($code == 200) {
		
			$resp = json_decode($tmhOAuth->response['response']);
    		$resp->screen_name;
		
			$url = $tmhOAuth->url('1.1/statuses/update');

			$code = $tmhOAuth->request('POST', $url, array(
				'status' => $message,
				'lat' => $lat,
				'long' => $lng,
				'display_coordinates' =>true,
				'wrap_links' => true
			));
		}
		return false;
		
 	}	

}
