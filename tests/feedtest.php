<?php 
	/**
	 * This PHP script is designed to test the implementation of the instagramFetch backend.
	 *
	 * This is done by creating an instance of the instagramFeed class - creating a new instagramFeed.
	 *
	 * @author : Jacob Ian Matthews <jacob@jacobian.com.au>
	 * @license : Apache 2.0
	 * @copyright : 2018 Jacob Ian Matthews
	*/

	// Load the necessary classes. Please point this to the instagram-feed repository folder.
	spl_autoload_register(function($class) {
		require '../src/' . $class . '.class.php';
	});

	// Create an array containing datbase information. 
	$database = array(
			"username"=>"",
			"password"=>"",
			"host"=>"",
			"database"=>""
	);

	// Create a new Instagram feed, showing the last 10 posts and including the keyword 'home' in all its <div> elements.
	$feed = new instagramFeed($database, 10, 'home');

	// Echo the Instagram feed with breaks between each line for better clarity.
	echo $feed->feed();

?>