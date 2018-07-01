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

	// Load the necessary classes
	require_once("../src/instagramFeed.class.php");
	
	// Create an array containing datbase information. 
	$database = array(
			"username"=>"",
			"password"=>"",
			"host"=>"",
			"database"=>""
	);

	// Create a new Instagram feed, showing the last 10 posts and including the style 'small'
	$feed = new \JacobIan\InstagramFeed\instagramFeed($database, 10, 'small');

	// Echo the Instagram feed with breaks between each line for better clarity.
	echo $feed->feed();

?>
