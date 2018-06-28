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
		 * [$name The name of the Instagram feed to prevent CSS conflicts]
		 * @var String
		 */
		public $name;

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

		public function __construct($post, $name, $assetpath){

			// Define inputs as properties
			$this->post = $post;
			$this->name = $name;
			$this->assetpath = $assetpath;

		}

		public function format(){

			// Get all information out of post and into variables
			$id = $this->post['id'];
			$time = $this->post['created_time'];
			$caption = $this->post['caption']['text'];
			$likes = $this->post['likes']['count'];
			$comments = $this->post['comments']['count'];
			$url = $this->post['link'];
			$location = $this->post['location']['name'];
			$type = $this->post['type'];

			// Create an array to store the post's HTML in
			$this->formatted_post = array();

			// Create data attributes
			$data_attribs = "data-id='" . $id . "' data-url='" . $url . "' data-likes='" . $likes . "' data-comments='" . $comments . "'";

			// Create a post container 
			array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_post' " . $data_attribs . ">");

			if($type == "video") {

				// Get the video URL
				$video = $this->post['videos']['standard_resolution']['url'];

				// Get the thumbnail
				$thumbnail = $this->post['images']['standard_resolution']['url'];

				// Process the video
				array_push($this->formatted_post, "<video controls poster='" . $thumbnail . "' class='" . $this->name . "_instagram_video'><source src='" . $video . "' type='video/mp4'></video>");

				// Create the likes and comments holder
				array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_video_details'>");


			} else {

				// Get the Image
				$image = $this->post['images']['standard_resolution']['url'];

				// Process the image
				array_push($this->formatted_post, "<img class='" . $this->name . "_instagram_image' src='" . $image . "'/>");

				// Create the likes and comments holder
				array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_image_details'>");


			}


			// The rest of the details:
			// 
			// Path to the Icons
			$instagram_icon = $this->assetpath . "instagram.svg";
			$likes_icon = $this->assetpath . "likes.svg";
			$comments_icon = $this->assetpath . "comments.svg";

			// The instagram link icon
			array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_link'>");
			array_push($this->formatted_post, "<a href='" . $url . "' target='_blank'><img class='" . $this->name . "_instagram_icons' src='" . $instagram_icon . "'/></a>");
			array_push($this->formatted_post, "</div>");

			// Likes and icon
			array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_likes'>");
			array_push($this->formatted_post, "<img class='" . $this->name . "_instagram_icons' src='" . $likes_icon . "'/><div class='" . $this->name . "_instagram_likes_text'>" . $likes . "</div>");
			array_push($this->formatted_post, "</div>");

			// Comments and icon
			array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_comments'>");
			array_push($this->formatted_post, "<img class='" . $this->name . "_instagram_icons' src='" . $comments_icon . "'/><div class='" . $this->name . "_instagram_comments_text'>" . $comments . "</div>");
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