<?php

	/*
	The front-end for the jacob-ian/instagram-feed.

	This class takes an SQL database's information as an array, i.e. $db = array("username"=>"[username]", "password"=>"[password]", "server"=."[server]", "database"=>"[database]"). This database must contain the data produced from class.fetchData.php. The class also takes a post count (number of posts to display) as the second input parameter.
	
	To display an Instagram feed of 10 posts, usage is as such:
	
	$feed = new instagramFeed($db, 10);
	echo $feed->posts();

	@author: Jacob Ian Matthews (jacob@jacobian.com.au)
	@copyright: 2018 Jacob Ian Matthews 
	@license: Apache 2.0

	*/

	// Give it the namespace
	namespace JacobIan\InstagramFeed;

	// Require the instagramPost Class
	require_once('instagramPost.class.php');
	
	class instagramFeed {

		/**
		 * [$count The requested count of posts to display. Max is 20.]
		 * @var integer
		 */
		public $count;

		/**
		 * [$name The name of the Instagram Feed to prevent CSS conflicts over multiple uses of the package]
		 * @var String
		 */
		public $name;

		/**
		 * [$post_array An array of the unstyled/raw posts as json data]
		 * @var Array
		 */
		public $post_array;

		/**
		 * [$posts A ready to echo array of Instagram posts]
		 * @var Array
		 */
		public $posts;

		/**
		 * [$db The name of the database containing data from class.fetchData.php]
		 * @var String
		 */
		private $db;

		/**
		 * [$host The name of the server containing the SQL database]
		 * @var String
		 */
		private $host;

		/**
		 * [$username The username needed to access the database]
		 * @var String
		 */
		private $username;

		/**
		 * [$password The password for the database user]
		 * @var String
		 */
		private $password;

		/**
		 * [$mysqli A MySQLi instance]
		 * @var Object
		 */
		private $mysqli;

		/**
		 * [$info The information from the details database]
		 * @var Array
		 */
		private $info;

		/**
		 * [$cachepath The path of the local cache]
		 * @var String
		 */
		private $cachepath;

		/**
		 * [$assetpath The path of the assets folder]
		 * @var String
		 */
		private $assetpath;



		/**
		 * [_construct description]
		 * @param  Array $db    An array containing the host, database, username and password for an SQL database.
		 * @param  integer $count An integer with the requested number of Instagram posts to display.
		 */
		public function __construct($db, $count, $name){

			// Save the input parameters as properties
			$this->host = $db['host'];
			$this->username = $db['username'];
			$this->password = $db['password'];
			$this->db = $db['database'];
			$this->count = $count;
			$this->name = $name;

			// Connect to the database
			$this->connectDB();

			// Get the details information
			$this->getInfo();

			// Get the posts out of the JSON cache
			$this->getPosts();

		}

		private function connectDB() {

			// Connect to the database and check connection
			$this->mysqli = new \mysqli($this->host, $this->username, $this->password, $this->db);

			if($this->mysqli->connect_error){
				die("Failed to connect to database: " . $this->mysqli->connect_error);
			} 

		}

		private function getInfo() {

			// Query the database for the cachepath and assets path information
			$q = "SELECT * FROM details";
			$query = $this->mysqli->query($q);

			// Create an array to store the databse info
			$this->info = array();

			if($query){

				// Check if the result has any rows
				if($query->num_rows > 0){

					// Set each row equal to the associate array
					while($row = $query->fetch_assoc()) {

						// Add the detail and value to the array
						$this->info[$row['Detail']] = $row['Value'];

					}

				} else {

					// Display SQL error
					echo "There was an error with getting details: " . $this->mysqli->error;

				}


			} else {

				// Display SQL error
				echo "Error fetching details from database: " . $this->mysqli->error;
			}

			// Define the cachepath variable
			$this->cachepath = $this->info['CachePath'];
			$this->assetpath = $this->cachepath . "/assets/";

		}


		public function getPosts(){

			// Get the JSON file
			$json = file_get_contents('../tests/cache/json/feed.json');

			// Get the initial array
			$json_arr = json_decode($json, true);

			// Cut out the excess information
			$this->post_array = $json_arr['data'];

		}

		public function feed(){

			// Create an output array for the feed
			$this->posts = array();

			// Create HTML container for the feed
			$containerOpen = "<div class='" . $this->name . "_instagram_feed'>";
			$containerClose = "</div>";

			// Push the feed container opening div statement into array
			array_push($this->posts, $containerOpen);

			// Format each of the posts in the unformatted array
			foreach($this->post_array as $post) {

				$formatted = new instagramPost($post, $this->name, $this->assetpath);

				// Add the formatted post to the formatted posts array
				array_push($this->posts, $formatted->format());

			}

			// Push the feed container closing statement into array
			array_push($this->posts, $containerOpen);

			// Convert the array to a String for easier echoing
			$this->posts = implode("\n", $this->posts);

			// Return the string of posts
			return $this->posts;

		}

		public function __destruct(){

			// Close connection to database
			$this->mysqli->close();

		}

	}



?>