<?php

	/**
	 * This class will format raw Instagram post data into HTML that can be styled using CSS. This class is called from the instagramFeed class and should not need any user input.
	 *
	 * @author : Jacob Ian Matthews <jacob@jaobian.com.au>
	 * @license : Apache 2.0
	 * @copyright : 2018 Jacob Ian Matthews
	 */
	
	// Namespace
	namespace JacobIan\InstagramFeed;
	
	class instagramPost {

		/**
		 * [$post This array contains the data for an Instagram post]
		 * @var Array
		 */
		public $post;

		/**
		 * [$name The CSS Style tag for the Instagram feed post]
		 * @var String
		 */
		public $style;

		/**
		 * [$formatted_post This is the HTML of a formatted post]
		 * @var String
		 */
		public $formatted_post;

		/**
		 * [$cachepath The path to the assets folder]
		 * @var String
		 */
		private $assetpath;

		/**
		 * [$id The Instagram Post ID]
		 * @var String
		 */
		public $id;

		/**
		 * [$time The Unix Timestamp of when the post was created]
		 * @var String
		 */
		public $time;

		/**
		 * [$caption The post's caption]
		 * @var String
		 */
		public $caption;

		/**
		 * [$likes The post's likes count]
		 * @var Integer
		 */
		public $likes;

		/**
		 * [$comments The post's comments count]
		 * @var Integer
		 */
		public $comments;

		/**
		 * [$url The post's URL]
		 * @var String
		 */
		public $url;

		/**
		 * [$location The name of the post's tagged location]
		 * @var String
		 */
		public $location;

		/**
		 * [$type The type of post (image or video)]
		 * @var String
		 */
		public $type;


		public function __construct($post, $style, $assetpath){

			// Define inputs as properties
			$this->post = $post;
			$this->style = $style;
			$this->assetpath = $assetpath;

			// Get the properties from the post
			$this->getProperties();

		}

		public function getProperties(){

			// Get the CSS Classes from the input tag
			$this->post_style = 'post_' . $this->style;
			$this->media_style = 'media_' . $this->style;
			

			// Get all information out of post and into variables
			$this->id = $this->post['id'];
			$this->time = $this->post['created_time'];
			$this->caption = $this->post['caption']['text'];
			$this->likes = $this->post['likes']['count'];
			$this->comments = $this->post['comments']['count'];
			$this->url = $this->post['link'];
			$this->location = $this->post['location']['name'];
			$this->type = $this->post['type'];


		}

		public function TimeDiff(){

			// Calculate the time since the post, in hours and days
			$time_p = new \DateTime(date('Y-m-d H:i', $this->time));
			$time_n = new \DateTime(date('Y-m-d H:i', time()));

			$time_diff = $time_n->diff($time_p);

			// Find the difference in years, months, days, hours
			$time_y = $time_diff->format('%y');
			$time_m = $time_diff->format('%m');
			$time_d = $time_diff->format('%d');
			$time_h = $time_diff->format('%h');

			// Hours
			if($time_y == 0 && $time_m == 0 && $time_d == 0) {

				if($time_h == 0) {

					$time_since = "Less than an hour ago";

				} elseif($time_h == 1) {

					$time_since = $time_h . " hour ago";

				} elseif($time_h > 1) {

					$time_since = $time_h . "hours ago";

				}

			}

			// Days
			if($time_y == 0 && $time_m == 0 && $time_d > 0){

				if($time_d == 1) {

					$time_since = $time_d . " day ago";

				} elseif($time_d > 1) {

					$time_since = $time_d . " days ago";

				}

			}

			// Months
			if($time_y == 0 && $time_m > 0) {

				if($time_m == 1) {

					$time_since = $time_m . " month ago";

				} elseif($time_m > 1) {

					$time_since = $time_m . " months ago";

				}

			}

			// Years
			if($time_y > 0) {

				if($time_y == 1) {

					$time_since = $time_y . " year ago";

				} elseif($time_y > 1){

					$time_since = $time_y . " years ago";

				}

			}

			return $time_since;

		}


		public function format(){
		
			// Create an array to store the post's HTML in
			$this->formatted_post = array();

			// Create data attributes
			$data_attribs = "data-id='" . $this->id . "' data-url='" . $this->url . "' data-likes='" . $this->likes . "' data-comments='" . $this->comments . "'";

			// Create a post container 
			array_push($this->formatted_post, "<div class='instagram_post " . $this->post_style . "' " . $data_attribs . ">");

			if($this->type == "video") {

				// Get the video URL
				$video = $this->post['videos']['standard_resolution']['url'];

				// Get the thumbnail
				$thumbnail = $this->post['images']['standard_resolution']['url'];

				// Process the video
				array_push($this->formatted_post, "<video poster='" . $thumbnail . "' class='instagram_video " . $this->media_style . "' alt=''><source src='" . $video . "' type='video/mp4'></video>");

				// Add the play icon
				array_push($this->formatted_post, "<img class='instagram_playvideo' src='" . $this->assetpath . "play.svg'/>");

				// Create the likes and comments holder
				array_push($this->formatted_post, "<div class='instagram_details' onclick='IgVideo(event)'>");


			} else {

				// Get the Image
				$image = $this->post['images']['standard_resolution']['url'];

				// Process the image
				array_push($this->formatted_post, "<img class='instagram_image " . $this->media_style . "' src='" . $image . "' alt='' />");

				// Create the likes and comments holder
				array_push($this->formatted_post, "<div class='instagram_details'>");


			}


			// The rest of the details:
			// 
			// Path to the Icons
			$instagram_icon = $this->assetpath . "instagram.svg";
			$likes_icon = $this->assetpath . "likes.svg";
			$comments_icon = $this->assetpath . "comments.svg";

			// The post time
			array_push($this->formatted_post, "<div class='instagram_time'>" . $this->TimeDiff() . "</div>");

			// The instagram link icon
			array_push($this->formatted_post, "<div class='instagram_link'>");
			array_push($this->formatted_post, "<a href='" . $this->url . "' target='_blank'><img class='instagram_icons' src='" . $instagram_icon . "' alt=''/></a>");
			array_push($this->formatted_post, "</div>");

			// Create holder for the Likes and Comments
			array_push($this->formatted_post, "<div class='instagram_stats'>");

			// Likes and icon
			array_push($this->formatted_post, "<div class='instagram_detail'>");
			array_push($this->formatted_post, "<img class='instagram_icons' src='" . $likes_icon . "' alt=''><span>" . $this->likes . "</span>");
			array_push($this->formatted_post, "</div>");

			// Comments and icon
			array_push($this->formatted_post, "<div class='instagram_detail'>");
			array_push($this->formatted_post, "<img class='instagram_icons' src='" . $comments_icon . "' alt=''><span>" . $this->comments . "</span>");
			array_push($this->formatted_post, "</div>");

			// Close the stats holder
			array_push($this->formatted_post, "</div>");

			// Close the details container
			array_push($this->formatted_post, "</div>");

			// Close post container
			array_push($this->formatted_post, "</div>");

			$this->formatted_post = implode("\n", $this->formatted_post);

			return $this->formatted_post;

		}




	}




?>