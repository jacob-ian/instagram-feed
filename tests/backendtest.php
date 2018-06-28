<?php
	
	/**
	 * This PHP test script will complete one instagramFetch.
	 *
	 * This is done by creating an instance of the class instagramFetch, and then calling its method fetch(). This will cache all Instagram posts locally, create a MySQL table if necessary, and then populate it with the metadata of each post.
	 *
	 * It is absolutely necessary to have an Instagram Access Token to use this repository.
	 *
	 * @author : Jacob Ian Matthews <jacob@jacobian.com.au>
	 * @license : Apache 2.0
	 * @copyright : 2018 Jacob Ian Matthews
	 */
	
	// Load the required class
	require_once('../src/instagramFetch.class.php');

	// Create an array containing datbase information. 
	$database = array(
			"username"=>"",
			"password"=>"",
			"host"=>"",
			"database"=>""
	);

	// Create a string containing your Instagram Access Token
	$accesstoken = "";

	// Create the cache path string
	$cachepath = "";

	// Create an instance of the fetch class
	$instagram_fetch = new \JacobIan\InstagramFeed\instagramFetch($database, $accesstoken, $cachepath);

	// Call the fetch method
	$instagram_fetch->fetch();


?>