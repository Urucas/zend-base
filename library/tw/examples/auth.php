<?php

/**
 * Demonstration of the various OAuth flows. You would typically do this
 * when an unknown user is first using your application. Instead of storing
 * the token and secret in the session you would probably store them in a
 * secure database with their logon details for your website.
 *
 * When the user next visits the site, or you wish to act on their behalf,
 * you would use those tokens and skip this entire process.
 *
 * The Sign in with Twitter flow directs users to the oauth/authenticate
 * endpoint which does not support the direct message permission. To obtain
 * direct message permissions you must use the "Authorize Application" flows.
 *
 * Instructions:
 * 1) If you don't have one already, create a Twitter application on
 *      https://dev.twitter.com/apps
 * 2) From the application details page copy the consumer key and consumer
 *      secret into the place in this code marked with (YOUR_CONSUMER_KEY
 *      and YOUR_CONSUMER_SECRET)
 * 3) Visit this page using your web browser.
 *
 * @author themattharris
 */



require '../tmhOAuth.php';
require '../tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => '39fWjp29UuJkoNxzK1cAg',
  'consumer_secret' => '7hNmlFPXGbEycerqo8wVcD9V5Q6Ebof7OkKCoBPw',
));

$here = tmhUtilities::php_self();
session_start();

$tmhOAuth->config['user_token']  = "334266070-LnDtpDLIiRK88aiWBE2RxaxVDNa5nDEphY1dTTKh";
$tmhOAuth->config['user_secret'] = "iLZtzlTR6BmB40lWryCZyDQ89kOPrYSDNCjIZ8n2Y0Q";

$code = $tmhOAuth->request('GET', $tmhOAuth->url('1/account/verify_credentials'));
var_dump($code);
 if ($code == 200) {
	//print_r($tmhOAuth->response);
    $resp = json_decode($tmhOAuth->response['response']);

    echo $resp->screen_name;
	$url = $tmhOAuth->url('1/statuses/update');

	$code = $tmhOAuth->request('POST', $url, array(
		'status' => "vrunoa se ha hecho fan de Grecia en las #colectividades #rosario http://kuesty.com/restaurants/local/id/631/name/Grecia #kuesty",
		'lat' =>'-32.946598',
		'long' =>'-60.62956',
		'display_coordinates' =>true,
	));

	tmhUtilities::pr($tmhOAuth->response['response']);

 }

var_dump($code); die();



if(!isset($_SESSION['noticia_node'])) {
	$_SESSION['noticia_node'] = $_GET['node'];
	$_SESSION['noticia_titulo'] = $_GET['titulo'];
}


function outputError($tmhOAuth) {
  echo 'Error: ' . $tmhOAuth->response['response'] . PHP_EOL;
  tmhUtilities::pr($tmhOAuth);
}

// reset request?
if ( isset($_REQUEST['wipe'])) {
  session_destroy();
  header("Location: {$here}");

// already got some credentials stored?
} elseif ( isset($_SESSION['access_token']) ) {
  $tmhOAuth->config['user_token']  = $_SESSION['access_token']['oauth_token'];
  $tmhOAuth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];

  $code = $tmhOAuth->request('GET', $tmhOAuth->url('1/account/verify_credentials'));
  if ($code == 200) {
	//print_r($tmhOAuth->response);
    $resp = json_decode($tmhOAuth->response['response']);

    echo $resp->screen_name;
	//$url = $tmhOAuth->url('1/statuses/update_with_media');
	$url = $tmhOAuth->url('1/statuses/update');
	//$url = str_replace('https://api.twitter.com', 'https://upload.twitter.com', $url);

	//Estoy leyendo: ' + noticia.detalle.titulo + ' ' + 'http://www.tn.com.ar/node/' + noticia.idnoticia + '&url=' + encodeURIComponent('http://www.tn.com.ar/node/' + noticia.idnoticia)

	$node   = $_SESSION['noticia_node'];
	$titulo = $_SESSION['noticia_titulo'];
	
	$message = "Estoy leyendo: " . $titulo . ' http://www.tn.com.ar/node/' .  $node;
	echo $message;
	$_SESSION['noticia_node']  = $_SESSION['noticia_titulo'] = null;

	$code = $tmhOAuth->request('POST', $url, array(
		'status' => $message,
	   'lat' =>'-32.945903301598',
		'long' =>'-60.648012272537',
		'display_coordinates' =>true,
	));


	if ($code == 200) {
		
  		tmhUtilities::pr(json_decode($tmhOAuth->response['response']));
			die('<script type="text/javascript">window.close()</script>');

	} else {
	  tmhUtilities::pr($tmhOAuth->response['response']);
	}

  } else {
    outputError($tmhOAuth);
  }
// we're being called back by Twitter
} elseif (isset($_REQUEST['oauth_verifier'])) {
  $tmhOAuth->config['user_token']  = $_SESSION['oauth']['oauth_token'];
  $tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

  $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
    'oauth_verifier' => $_REQUEST['oauth_verifier']
  ));

  if ($code == 200) {
    $_SESSION['access_token'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    unset($_SESSION['oauth']);
    header("Location: {$here}");
  } else {
    outputError($tmhOAuth);
  }
// start the OAuth dance
} elseif ( isset($_REQUEST['authenticate']) || isset($_REQUEST['authorize']) ) {
  $callback = isset($_REQUEST['oob']) ? 'oob' : $here;

  $params = array(
    'oauth_callback'     => $callback
  );

  if (isset($_REQUEST['force_write'])) :
    $params['x_auth_access_type'] = 'write';
  elseif (isset($_REQUEST['force_read'])) :
    $params['x_auth_access_type'] = 'read';
  endif;

  $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/request_token', ''), $params);

  if ($code == 200) {
    $_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    $method = isset($_REQUEST['authenticate']) ? 'authenticate' : 'authorize';
    $force  = isset($_REQUEST['force']) ? '&force_login=1' : '';
    $authurl = $tmhOAuth->url("oauth/{$method}", '') .  "?oauth_token={$_SESSION['oauth']['oauth_token']}{$force}";
    //echo '<p>To complete the OAuth flow follow this URL: <a href="'. $authurl . '">' . $authurl . '</a></p>';
	header("Location: ".$authurl);

  } else {
    outputError($tmhOAuth);
  }
}

?>
<ul>
  <li><a href="?authenticate=1">Sign in with Twitter</a></li>
  <li><a href="?authenticate=1&amp;force=1">Sign in with Twitter (force login)</a></li>
  <li><a href="?authorize=1">Authorize Application (with callback)</a></li>
  <li><a href="?authorize=1&amp;oob=1">Authorize Application (oob - pincode flow)</a></li>
  <li><a href="?authorize=1&amp;force_read=1">Authorize Application (with callback) (force read-only permissions)</a></li>
  <li><a href="?authorize=1&amp;force_write=1">Authorize Application (with callback) (force read-write permissions)</a></li>
  <li><a href="?authorize=1&amp;force=1">Authorize Application (with callback) (force login)</a></li>
  <li><a href="?wipe=1">Start Over and delete stored tokens</a></li>
</ul>
