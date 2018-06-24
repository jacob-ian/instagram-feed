<?php 
	
	// Load the classes
	spl_autoload_register(function($class) {
		require '../src/' . $class . '.class.php';
	});

	$database = array(
			"username"=>"root",
			"password"=>"",
			"host"=>"instagram-feed.local",
			"database"=>"instagram"
	);

	$feed = new instagramFeed($database, 10, 'home');
	echo implode("\n", $feed->feed());

?>