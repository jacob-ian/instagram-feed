<?php 
	
	// Load the classes
	spl_autoload_register(function($class) {
		require '../src/' . $class . '.class.php';
	});

	$database = array(
			"username"=>"root",
			"password"=>"",
			"host"=>"instagramfeed.local",
			"database"=>"instagram"
	);

	$feed = new instagramFeed($database, 10);
	echo "<pre>";
	print_r($feed->feed());

?>