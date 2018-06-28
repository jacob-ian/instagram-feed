<?php

	/**
	 * This class is the backend for jacob-ian/instagram-feed.
	 *
	 * It is intended to be called via a cron job with a frequency greater than once per every 15 minutes (the refresh
	 * rate). The refresh rate depends on the user's needs.
	 *
	 * The class will take in an array of database information and an Instagram Access Token, and then will use the old
	 * Instagram API to cache the 20 latest posts from a public profile. It is only possible to load the latest 20 
	 * posts using this API. The posts are cached on the user's webserver/hard drive, and metadata about the posts is
	 * stored in an SQL table named 'feed'. It will also store other information in a table named 'details'. See
	 * README.md -> Extras, for more details.
	 *
	 * It is necessary to have a MySQL database and an Instagram Access Token to run this class.
	 *
	 *
	 * @author  : Jacob Ian Matthews <jacob@jacobian.com.au>
	 * @license  : Apache 2.0
	 * @copyright  : 2018 Jacob Ian Matthews
	 */

	// Namespace
	namespace JacobIan\InstagramFeed;

	class instagramFetch {

		/**
		 * [$host Name of the MySQL host server]
		 * @var String
		 */
		private $host;

		/**
		 * [$user Username for user of MySQL host]
		 * @var String
		 */
		private $user;

		/**
		 * [$password Password for MySQL host user]
		 * @var String
		 */
		private $password;

		/**
		 * [$database Name of the database to store Instagram Feed Data]
		 * @var String
		 */
		private $db;

		/**
		 * [$mysqli The MySQLI Object with the database]
		 * @var Object
		 */
		private $mysqli;

		/**
		 * [$accesstoken The Instagram Access Token associated to the account whose posts will be displayed]
		 * @var String
		 */
		private $accesstoken;

		/**
		 * [$cachepath The filepath to the Instagram post cache. This should be in the public/webroot directory.]
		 * @var String
		 */
		public $cachepath;

		/**
		 * [$highpath The path to the cache of Instagram images/videos]
		 * @var String
		 */
		public $jsonpath;

		/**
		 * [$assetpath The path to the assets folder]
		 * @var String
		 */
		public $assetpath;

		/**
		 * [$user_id The Instagram user's ID]
		 * @var Integer
		 */
		public $user_id;

		/**
		 * [$post_count The Instagram user's post count]
		 * @var Integer
		 */
		public $post_count;

		/**
		 * [$followers The Instagram user's follower count]
		 * @var Integer
		 */
		public $followers;

		/**
		 * [$profile_picture The URL to the Instagram User's profile picture]
		 * @var String
		 */
		public $profile_picture;

		/**
		 * [__construct Constructor of class]
		 * @param Array $database    MySQL database login informatiom
		 * @param String $accesstoken The Instagram Access token
		 * @param String $cachepath   The path to the desired Instagram Cache location -> should be in webroot.
		 */
		public function __construct($database, $accesstoken, $cachepath) {

			// Store the input parameters as properties
			$this->host = $database['host'];
			$this->username = $database['username'];
			$this->password = $database['password'];
			$this->db = $database['database'];

			$this->accesstoken = $accesstoken;
			$this->cachepath = $cachepath;

			// Connect to the database
			$this->connectDB();

			// Check if the tables exist
			$this->checkTables();

			// Check to see if the path exists
			$this->checkPath();

			// Transfer the asset files to the public_html/cachepath
			$this->assetTransfer();


		}

		private function connectDB(){

			// Connect to the database and check connection
			$this->mysqli = new \mysqli($this->host, $this->username, $this->password, $this->db);

			if($this->mysqli->connect_error){
				die("Failed to connect to database: " . $this->mysqli->connect_error);
			} 

		}

		private function checkTables(){

			// Create a query to select the details table
			$details_query = "show tables like 'details'";
			$details = $this->mysqli->query($details_query);

			// Check to see if it exists, if not create it.
			if($details->num_rows == 0) {

				$create_details = "CREATE TABLE details (Detail varchar(255), Value varchar(255))";
				$create_dq = $this->mysqli->query($create_details);

				if(!$create_dq) {

					echo "There was an error creating the database table 'details': " . $this->mysqli->error;
				}

				// Create the placeholders for the table
				$insert = "INSERT INTO details ( Detail ) VALUES ('UserID'), ('Posts'), ('Followers'), ('ProfilePictureURL'), ('ProfilePicturePath'), ('LastUpdate'), ('CachePath');";

				// Do the query
				if(!$this->mysqli->query($insert)) {

					echo "There was error inserting placeholder in 'details': " . $this->mysqli->error;

				}

			}

		}

		private function checkPath(){

			// Check if the path exists, if not create it
			if(!is_dir($this->cachepath)) {

				// Set recursive = true to create all parent directories
				mkdir($this->cachepath, true);

			}

			// Check if the media storage path exists
			$this->jsonpath = $this->cachepath . '/json/';

			// If the media path doesn't exist
			if(!is_dir($this->jsonpath)){

				// Make the directory
				mkdir($this->jsonpath);
			}

			// Check if the assets path exists
			$this->assetpath = $this->cachepath . '/assets/';

			if(!is_dir($this->assetpath)){

				// Make directory
				mkdir($this->assetpath);

			}

		}

		public function assetTransfer(){

			// Define from path
			$from = dirname(__DIR__) . '/assets/';

			// Define to path
			$to = $this->assetpath;

			// Scan both directories for differences
			$scanfrom = scandir($from);
			$scanto = scandir($to);

			if(sizeof($scanto) == 0){

				// There are no files in the transfer destination, therefore transfer all
				foreach($scanfrom as $file){

					$filedest = $to . $file;
					$fileloc = $from . $file;
					file_put_contents($filedest, file_get_contents($fileloc));

				}


			} else {

				// There are files, so compare each folder
				foreach($scanfrom as $file) {

					// Flag to see if file is in the other directory
					$in = false;

					foreach($scanto as $file2) {

						// If there is a match
						if(strcmp($file, $file2)){

							// Set the flag to true
							$in = true;

						}

					}

					// If the flag is still false, transfer the file
					if(!$in) {

						$filedest = $to . $file;
						$fileloc = $from . $file;
						file_put_contents($filedest, file_get_contents($fileloc));

					}


				}
			}

		}

		public function getInfo(){

			// First, find post count and follower data
			$info_get = file_get_contents("https://api.instagram.com/v1/users/self/?access_token=" . $this->accesstoken);
			$info = json_decode($info_get, true);
			
			// Set decoded data as properties
			$this->user_id = $info['data']['id'];
			$this->post_count = $info['data']['counts']['media'];
			$this->followers = $info['data']['counts']['followed_by'];
			$this->profile_picture = $info['data']['profile_picture'];
			

		}

		public function cache(){

			// Get the Instagram feed 
			$this->feed = file_get_contents('https://api.instagram.com/v1/users/' . $this->user_id . '/media/recent/?access_token=' . $this->accesstoken . '&count=' . $this->post_count);

			// Save it to jsonpath
			file_put_contents($this->jsonpath . "feed.json", $this->feed);

		
		}

		public function updateDetails(){

			// Update follower count, ID, and other details in details database
			$update_d = "UPDATE details SET Value='";
			$detail1 = $update_d . $this->user_id . "' WHERE Detail = 'UserID'";
			$detail2 = $update_d . $this->post_count . "' WHERE Detail = 'Posts'";
			$detail3 = $update_d . $this->followers . "' WHERE Detail = 'Followers'";
			$detail4 = $update_d . $this->profile_picture . "' WHERE Detail = 'ProfilePictureURL'";
			$detail5 = $update_d . date('r', time()) . "' WHERE Detail = 'LastUpdate'";
			$detail6 = $update_d . $this->cachepath . "' WHERE Detail = 'CachePath'";

			// Put all updates in an array
			$update_array = array($detail1, $detail2, $detail3, $detail4, $detail5, $detail6);

			// For loop to handle detail updates
			foreach($update_array as $update){

				// Define the query
				$query = $this->mysqli->query($update);

				if(!$query) {

					// Display MySQL error
					echo "Error updating details: " . $this->mysqli->error;

				}
			}


		}

		public function updateProfilePicture(){

			// Define the profile picture path
			$pp_path = $this->cachepath . '/profile_picture.jpg';

			// If the file exists
			if(is_file($pp_path)){

				// Get the data of the current and new profile picture
				$current = file_get_contents($pp_path);
				$new = file_get_contents($this->profile_picture);

				if(strcmp($current, $new) !== 0){

					// If they are different, replace the old with the enw
					unlink($pp_path);

					// Download the new picture
					file_put_contents($pp_path, file_get_contents($this->profile_picture));

				}
				
			} else {

				// First download, define the path of the profile picture
				$q = "UPDATE details SET Value='" . $pp_path . "' WHERE Detail = 'ProfilePicturePath'";
				$query = $this->mysqli->query($q);

				if($query){

					// Download the picture
					file_put_contents($pp_path, file_get_contents($this->profile_picture));
					
				} else {

					// Couldn't add to database, display error
					echo "The profile picture path could not be updated: " . $this->mysqli->error;

				}

			}

		}

		public function fetch(){

			// Download the new details information
			$this->getInfo();

			// Download and cache the JSON feed
			$this->cache();

			// Update details database
			$this->updateDetails();

			// Update the profile picture if it has changed
			$this->updateProfilePicture();

		}

		public function __destruct() {

			// Close the MySQL connection
			$this->mysqli->close();

		}



	}



?>