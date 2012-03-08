<?php $debug = false;

require(__DIR__ . "/_config.php");

date_default_timezone_set('UTC'); // prevent warning on my local machine

spl_autoload_register(function($className) {
	if ($className[0] == '\\') {
		$className = substr($className, 1);
	}
	
	// leave if class should not be handled by this autoloader
	if (strpos($className, 'UnitedPrototype\\GoogleAnalytics') !== 0) {
		return;
	}
	
	$path = __DIR__ . "/php-ga/src/" . strtr(substr($className, strlen('UnitedPrototype')), '\\', '/') . '.php';
	require($path);
});

use UnitedPrototype\GoogleAnalytics;

function leechgate_expand_config(&$config) {
	if (!isset($config['gaid'])) {
		throw new Exception("Please specify config variable 'gaid' as Google Analytics account ID, something like UA-XXXXXXX-Y");
	}
	if (!isset($config['target'])) {
		throw new Exception("Please specify config variable 'target' which is target postfix for redirection");
	}
	
	// ask for host unless hardcoded in the config
	if (!isset($config['host'])) {
		$config['host'] = $_SERVER['HTTP_HOST'];
	}

	// build normalized host name
	// remove last dash postfix from first componet of the url: sub-xx-yy.mydomain.com => sub-xx.mydomain.com
	$parts = explode(".", $config['host']);
	$subparts = explode("-", $parts[0]);
	if (count($subparts)>1) {
		array_pop($subparts);
		$parts[0] = implode("-", $subparts);
	}
	$config['normalized_host'] = implode(".", $parts);

	// ask for utma cookie unless hardcoded in the config
	if (!isset($config['utma_cookie'])) {
		$config['utma_cookie'] = $_COOKIE['__utma'];
	}

	// ask for query path unless hardcoded in the config
	if (!isset($config['query'])) {
		$config['query'] = "/".$_GET["q"];
	}
	// build redirect url if not hardcoded
	if (!isset($config['redirect_url'])) {
		$parts = explode(".", $config['normalized_host']);
		$subparts = explode("-", $parts[0]);
		array_push($subparts, $config["target"]);
		$parts[0] = implode("-", $subparts);
		$config['redirect_url'] = implode(".", $parts) . $config["query"];
	}
	
	$actual_url = $_SERVER['HTTP_HOST'] . $config["query"];
	if ($config['redirect_url'] == $actual_url) {
		throw new Exception("Redirection loop! {$config['redirect_url']} => $actual_url");
	}
	
	// parse product and product_version from the query
	// I have a convention to name products like this:
	// TotalFinder-X.Y.Z.dmg
	// Asepsis-X.Y.Z.dmg
	// ..
	if (!isset($config['product'])) {
		$dots = explode(".", $_GET["q"]);
		if (count($dots)>1) {
			array_pop($dots);
		}
		$query_without_extension = implode(".", $dots);
		$parts = explode("-", $query_without_extension);
		if (count($parts)>1) {
			$version = array_pop($parts);
			$config['product'] = implode(".", $parts);
			$config['product_version'] = $version;
		} else {
			$config['product'] = $parts[0];
			$config['product_version'] = "none";
		}
	}
}

function leechgate_track_ga($config) {
	try {
		// prepare GA structures
		$tracker = new GoogleAnalytics\Tracker($config['gaid'], $config['normalized_host']);
		$visitor = new GoogleAnalytics\Visitor();
		$visitor->fromServerVar($_SERVER);
		if ($config['utma_cookie']) {
			$visitor->fromUtma($config['utma_cookie']);
		}
		$session = new GoogleAnalytics\Session();
		$event = new GoogleAnalytics\Event($config['normalized_host'], $config['product'], $config['product_version']);
		$event->setNoninteraction(true);
	
		// track it!
		$tracker->trackEvent($event, $session, $visitor);
	} catch (Exception $e) {
		fwrite(STDERR, "Caught exception in leechgate_track_ga: ".$e->getMessage()."\n");
	}
}

function leechgate_redirect($config) {
	global $debug;
	$redir = "Location: http://".$config['redirect_url'];
	if (!$debug) {
		header($redir, TRUE, 307);
	} else {
		echo "-> " . $redir;
	}
}

if (!isset($leechgate_config) || !is_array($leechgate_config)) {
	throw new Exception("Please specify leechgate_config global variable");
}

leechgate_expand_config($leechgate_config);
leechgate_track_ga($leechgate_config);
leechgate_redirect($leechgate_config);

// debug
if ($debug) {
	echo "<pre>";
	print_r($leechgate_config);
	echo "</pre>";
}

?>