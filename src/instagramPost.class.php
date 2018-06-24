<?php

	/**
	 * This class will format raw Instagram post data into HTML that can be styled using CSS.
	 */
	
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

		public function __construct($post, $name){

			// Define inputs as properties
			$this->post = $post;
			$this->name = $name;

		}

		public function format(){

			// Create data attributes
			$data_attribs = "data-id='" . $this->post['id'] . "' data-url='" . $this->post['url'] . "' data-likes='" . $this->post['likes'] . "' data-comments='" . $this->post['comments'] . "'";

			// Create a post container 
			echo "<div class='" . $this->name . "_instagram_post' " . $data_attribs . ">";

			// Different process for a video
			$isvideo = $this->post['isvideo'];

			if($isvideo) {

				$file = '/img/instagram/hires/' . $this->post['id'] . '.mp4';

				// Process the video
				echo "<video class='" . $this->name . "_instagram_video'><source src='" . $file . "'></video>";

				// Create the likes and comments holder
				echo "<div class='" . $this->name . "_instagram_video_details'>";


			} else {

				$file = '/img/instagram/hires/' . $this->post['id'] . '.jpg';

				// Process the image
				echo "<img class='" . $this->name . "_instagram_image' src='" . $file . "'/>";

				// Create the likes and comments holder
				echo "<div class='" . $this->name . "_instagram_image_details'>";


			}


			// The rest of the details
			// The instagram link icon
			echo "<div class='" . $this->name . "_instagram_link'>";
			echo "<a href='" . $this->post['url'] . "' target='_blank'><img class='" . $this->name . "_instagram_icons' src='/img/instagram/icons/instagram.svg'/></a>";
			echo "</div>";

			// Likes and icon
			echo "<div class='" . $this->name . "_instagram_likes'>";
			echo "<img class='" . $this->name . "_instagram_icons' src='/img/instagram/icons/likes.svg'/><div class='" . $this->name . "_instagram_likes_text'>" . $this->post['likes'] . "</div>";
				echo "</div>";

			// Comments and icon
			echo "<div class='" . $this->name . "_instagram_comments'>";
			echo "<img class='" . $this->name . "_instagram_icons' src='/img/instagram/icons/comments.svg'/><div class='" . $this->name . "_instagram_comments_text'>" . $this->post['comments'] . "</div>";
			echo "</div>";

			// Close the details container
			echo "</div>";

			// Close post container
			echo "</div>";

		}




	}




?>