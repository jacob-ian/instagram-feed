<?php

	/**
	 * This class will format raw Instagram post data into HTML that can be styled using CSS. This class is called from the instagramFeed class and should not need any user input.
	 *
	 * @author : Jacob Ian Matthews <jacob@jaobian.com.au>
	 * @license : Apache 2.0
	 * @copyright : 2018 Jacob Ian Matthews
	 */
	
	// Namespace
	namespace JacobIan;
	
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

			// Create an array to store the post's HTML in
			$this->formatted_post = array();

			// Create data attributes
			$data_attribs = "data-id='" . $this->post['id'] . "' data-url='" . $this->post['url'] . "' data-likes='" . $this->post['likes'] . "' data-comments='" . $this->post['comments'] . "'";

			// Create a post container 
			array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_post' " . $data_attribs . ">");

			// Different process for a video
			$isvideo = $this->post['isvideo'];

			// Get the filepath
			$file = addslashes($this->post['media']);

			if($isvideo) {

				// Process the video
				array_push($this->formatted_post, "<video class='" . $this->name . "_instagram_video'><source src='" . $file . "'></video>");

				// Create the likes and comments holder
				array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_video_details'>");


			} else {

				// Process the image
				array_push($this->formatted_post, "<img class='" . $this->name . "_instagram_image' src='" . $file . "'/>");

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
			array_push($this->formatted_post, "<a href='" . $this->post['url'] . "' target='_blank'><img class='" . $this->name . "_instagram_icons' src='" . $instagram_icon . "'/></a>");
			array_push($this->formatted_post, "</div>");

			// Likes and icon
			array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_likes'>");
			array_push($this->formatted_post, "<img class='" . $this->name . "_instagram_icons' src='" . $likes_icon . "'/><div class='" . $this->name . "_instagram_likes_text'>" . $this->post['likes'] . "</div>");
			array_push($this->formatted_post, "</div>");

			// Comments and icon
			array_push($this->formatted_post, "<div class='" . $this->name . "_instagram_comments'>");
			array_push($this->formatted_post, "<img class='" . $this->name . "_instagram_icons' src='" . $comments_icon . "'/><div class='" . $this->name . "_instagram_comments_text'>" . $this->post['comments'] . "</div>");
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