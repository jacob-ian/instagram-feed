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

			// Get the posts as an array of data
			$this->getPosts();

		}

		private function connectDB() {

			// Connect to the database and check connection
			$this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->db);

			if($this->mysqli->connect_error){
				die("Failed to connect to database: " . $this->mysqli->connect_error);
			} 

		}

		public function getPosts(){

			// Get the posts from the database. Get the specified number of posts in descending date order. 
			$order_query = "SELECT * FROM feed ORDER BY PostDate DESC LIMIT " . $this->count;
			$ordered_arr = $this->mysqli->query($order_query);

			// Create an array to store the posts
			$this->post_array = array();

			// Get the posts out of the query
			if($ordered_arr->num_rows > 0){

				while($ordered_row = $ordered_arr->fetch_assoc()) {

					// Create sub-array
					$sub_array = array(
						'id' => $ordered_row['ID'],
						'date' => $ordered_row['PostDate'],
						'caption' => $ordered_row['Caption'],
						'location' => $ordered_row['Location'],
						'likes' => $ordered_row['Likes'],
						'comments' => $ordered_row['Comments'],
						'isvideo' => $ordered_row['Video'],
						'url' => $ordered_row['URL'],
						'lowres' => $ordered_row['LoRes'],
						'hires' => $ordered_row['HiRes']
					);
					// Push into the post array
					array_push($this->post_array, $sub_array);

				}

			} else {

				echo 'Error: Could not fetch latest Instagram posts. ' . $this->mysqli->error;
				echo "\nPlease contact the website administrator.";

			}

			return $this->post_array;

		}

		public function feed(){

			// Create an array for the formatted posts to output
			$this->posts = array();

			// Create HTML container for the feed
			$containerOpen = "<div class='" . $this->name . "_instagram_feed'>";
			$containerClose = "</div>";

			// Put the open container string into the output array
			array_push($this->posts, $containerOpen);

			// Format each of the posts in the unformatted array
			foreach($this->post_array as $post) {

				$formatted = new instagramPost($post, $this->name);


				// Add the formatted post to the formatted posts array
				array_push($this->posts, $formatted->format());

			}

			// Put the close container string in the output array
			array_push($this->posts, $containerClose);
			
			// Return the array of posts
			return $this->posts;

		}

		public function __destruct(){

			// Close connection to database
			$this->mysqli->close();

		}

	}



?>