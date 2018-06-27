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
	namespace JacobIan;

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
		public $mediapath;

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
		 * [$db_feed This is the Intagram Feed as stored on the database pre-refresh.]
		 * @var Array
		 */
		public $db_feed;

		/**
		 * [$deleted This contains the deleted posts since last refresh]
		 * @var Array
		 */
		private $deleted;

		/**
		 * [$new This contains the new posts since last refresh]
		 * @var Array
		 */
		public $new;

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

			// Get the current Instagram feed from the SQL Database
			$this->getDBFeed();

			// Get the Instagram raw JSON data
			$this->getJSON();

		}

		private function connectDB(){

			// Connect to the database and check connection
			$this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->db);

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
				$insert = "INSERT INTO details ( Detail ) VALUES ('UserID'), ('Posts'), ('Followers'), ('ProfilePictureURL'), ('ProfilePictureLocal'), ('LastUpdate'), ('CachePath');";

				// Do the query
				if(!$this->mysqli->query($insert)) {

					echo "There was error inserting placeholder in 'details': " . $this->mysqli->error;

				}
				
				

			}

			// Create a query to select the feed table
			$feed_query = "show tables like 'feed'";
			$feed = $this->mysqli->query($feed_query);

			if($feed) {

				// If it doesn't exist, create it
				if($feed->num_rows == 0) {

					$create_feed = "CREATE TABLE feed (ID varchar(255), PostDate varchar(25), Video tinyint(1), Caption longtext, Location text, Likes int(10) unsigned, Comments int(10) unsigned, Media varchar(255), URL varchar(255))";
					$create_fq = $this->mysqli->query($create_feed);

					if(!$create_fq){

						// Display MySQL Error
						echo "There was an error creating the database table 'feed': " . $this->mysqli->error;

					}

				}
			} else {

				// Display MySQL error
				echo "There was an error finding the 'feed' table: " . $this->mysqli->error;

			}


		}

		private function checkPath(){

			// Check if the path exists, if not create it
			if(!is_dir($this->cachepath)) {

				// Set recursive = true to create all parent directories
				mkdir($this->cachepath, true);

			}

			// Check if the media storage path exists
			$this->mediapath = $this->cachepath . '/media/';

			// If the media path doesn't exist
			if(!is_dir($this->mediapath)){

				// Make the directory
				mkdir($this->mediapath);
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

		public function getDBFeed(){

			// Create SQL Query
			$feed_query = "SELECT * FROM feed ORDER BY PostDate DESC";
			$feed_temp = $this->mysqli->query($feed_query);

			// Instantiate array to store the database feed
			$this->db_feed = array();

			// Put the SQL data into the array
			if($feed_temp){

				if($feed_temp->num_rows > 0){

					while($row = $feed_temp->fetch_assoc()){

						// Add the row from the database data to the array
						array_push($this->db_feed, $row);

					}

				}

			} else {

				// Display MySQL Error
				echo 'There was an error fetching the Instagram Feed from the database: ' . $this->mysql->error;

			}

		}

		public function getJSON(){

			// First, find post count and follower data
			$info_get = file_get_contents("https://api.instagram.com/v1/users/self/?access_token=" . $this->accesstoken);
			$info = json_decode($info_get, true);
			
			$this->user_id = $info['data']['id'];
			$this->post_count = $info['data']['counts']['media'];
			$this->followers = $info['data']['counts']['followed_by'];
			$this->profile_picture = $info['data']['profile_picture'];
			
			// Get the instagram feed 
			$feed_get = file_get_contents('https://api.instagram.com/v1/users/' . $this->user_id . '/media/recent/?access_token=' . $this->accesstoken . '&count=' . $this->post_count);
			$feed = json_decode($feed_get, true);

			// Remove excess information
			$this->feed = $feed['data'];

		}

		public function newPosts(){

			// Create a new array to hold the new posts to cache
			$this->new = array();

			// If there has already been an initial run
			if(sizeof($this->db_feed) > 0){

				// Loop through the downloaded feed
				foreach($this->feed as $external){

					// Get the ID of the current external post
					$ID = $external['id'];

					// Create a flag variable
					$is_in = false;

					// Loop through all the cached posts to check if the post is there
					foreach($this->db_feed as $cached){

						$cachedID = $cached['ID'];

						// Compare the IDs
						if(strcmp($cachedID, $ID)) {

							$is_in = true;

						}

					}

					// If the flag is still false, add to $this->new array for caching
					if(!$is_in) {

						// Add to array
						array_push($this->new, $external);

					}

				}

			} else {

				// Loop through downloaded feed
				foreach($this->feed as $external){

					// Add to the array
					array_push($this->new, $external);

				}

			}
			

			// Return the new posts
			return $this->new;
			

		}

		public function deletedPosts(){

			// Create the deleted posts array
			$this->deleted = array();

			// Loop through the cached feed
			foreach($this->db_feed as $cached) {

				// Get the ID of the current cached post
				$id = $cached['ID'];

				// Create a flag variable
				$is_in = false;

				// Loop through the refreshed data to see if it still exists
				foreach($this->feed as $external) {

					// Get the ID of the current external post
					$ext_id = $external['id'];

					// Compare it to the ID of the cached post
					if(strcmp($ext_id, $id)){

						// Set the flag to true if it is still there
						$is_in = true;

					}

				}

				// If the post isn't in the external data
				if(!$is_in) {

					// Put the cached post inside the deleted array
					array_push($this->deleted, $cached);

				}


			}

			// Return the array of deleted posts
			return $this->deleted;

		}

		private function cache($posts){

			// Loop through each post to cache
			foreach($posts as $post) {

				// Get the internal data as variables
				$id = $post['id'];
				$type = $post['type'];
				$time = $post['created_time'];
				$caption = $post['caption']['text'];
				$location = $post['location']['name'];
				$likes = $post['likes']['count'];
				$comments = $post['comments']['count'];
				$url = $post['link'];


				// Process for an image
				if($type == 'image') {

					// Create variables
					$mediaURL = $post['images']['standard_resolution']['url'];
					
					// Image downloads to folder
					$path = $this->mediapath . $id . '.jpg';
					file_put_contents($path, file_get_contents($mediaURL));

					// Set the video boolean to false
					$video_bool = false;


				} elseif($type == 'video') {

					// Create variables
					$mediaURL = $post['videos']['standard_resolution']['url'];

					// Hi Res video to folder
					$path = $this->mediapath . $id . '.mp4';
					file_put_contents($path, file_get_contents($mediaURL));

					// Set video boolean to true
					$video_bool = true;

				}	


				// Now store everything in the database
				$q1 = "INSERT INTO feed VALUES ('" . $id ."', '";
				$q1 .= $time . "', '" ;
				$q1 .= $video_bool . "', '";
				$q1 .= addslashes($caption) . "', '"; 
				$q1 .= $location . "', '";
				$q1 .= $likes . "', '";
				$q1 .= $comments . "', '";
				$q1 .= $path . "', '";
				$q1 .= $url. "')";

				if(!$this->mysqli->query($q1)) {

					echo "Error inputting new images into database: " . $this->mysqli->error;

				}
			}

		}

		private function delete($posts) {

			foreach($posts as $post) {

				// Delete post from the database
				$remove_query = "DELETE FROM feed WHERE ID = " . $post['ID'];
				$remove = $this->mysqli->query($remove_query);

				if(!$remove) {

					echo "There was an error deleting the post " . $post['ID'] . ": " . $this->mysqli->error;
				}

				// Delete post from the cache
				unlink($post['LowRes']);
				unlink($post['HighRes']);

			}


		}

		public function updateLikesComments(){

			// Create foreach loop to get the current likes and comments number from all posts
			$stats_array = array();

			foreach($this->feed as $post) {

				$id = $post['id'];
				$likes = $post['likes']['count'];
				$comments = $post['comments']['count'];

				$sub_array = array(
					'id' => $id,
					'likes' => $likes,
					'comments' => $comments
				);

				array_push($stats_array, $sub_array);

			}
			
			// Create foreach loop to handle the likes and comments
			foreach($stats_array as $stat) {

				// SQL to update all likes
				$like_query = "UPDATE feed SET Likes='" . $stat['likes'] . "' WHERE ID='" . $stat['id'] . "'";

				if(!$this->mysqli->query($like_query)) {

					echo 'Error updating likes: ' . $this->mysqli->error;

				}

				// SQL to update all comments counts
				$comments_query = "UPDATE feed SET Comments='" . $stat['comments'] . "' WHERE ID='" . $stat['id'] . "'";
				if(!$this->mysqli->query($comments_query)) {

					echo 'Error updating comments: ' . $this->mysqli->error;

				}

			}

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

			// Get the stored profile picture URL from the database
			$p_picture_q = "SELECT Value FROM details WHERE Detail='ProfilePictureURL'";
			$db_pic_result = $this->mysqli->query($p_picture_q);

			if($db_pic_result){

				// Connection successful
				if($db_pic_result->num_rows > 0){

					while($pic_row = $db_pic_result->fetch_assoc()){

						$db_picture = $pic_row['Value'];
					}

					// Compare the database URL to the URL from the downloaded JSON data
					if(!strcmp($db_picture[0], $this->profile_picture)) {

						// There is a new profile picture, so delete old and download new
						$pp_path = $this->cachepath . '/profile_picture.jpg';

						if(is_file($pp_path)){

							unlink($pp_path);
						}

						file_put_contents($pp_path, file_get_contents($this->profile_picture));

						// Add to database
						$updateq = "UPDATE details SET Value ='" . $pp_path . "' WHERE Detail='ProfilePictureLocal'";
						if(!$this->mysqli->query($updateq)) {

							// Display SQL Error
							echo "There was an error updating the local profile picture path: " . $this->mysqli->error;

						}

					}
				}
			
			} else {

				// Echo SQL Error
				echo "There was an error getting the database profile picture: " . $this->mysqli->error;

			}


		}

		public function fetch(){

			// Download and cache any new posts
			if(sizeof($this->newPosts()) > 0) {

				// Call cache() method with the array of new posts
				$this->cache($this->newPosts());

			}
			
			// Delete any posts that were lost due to 20 post max, or were deleted from the page
			if(sizeof($this->deletedPosts()) > 0) {

				// Call delete() method with array of deleted posts
				$this->delete($this->deletedPosts());

			}

			// Update the likes and comments on the database cached posts
			$this->updateLikesComments();

			// Update the profile picture if it has changed
			$this->updateProfilePicture();

			// Update the profile information if it has changed
			$this->updateDetails();

		}

		public function __destruct() {

			// Close the MySQL connection
			$this->mysqli->close();

		}



	}



?>