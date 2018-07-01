/**
 * This file contains the necessary javascript functions to run the jacob-ian/instagram-feed feed.
 *
 * @author  Jacob Ian Matthews
 * @license  Apache 2.0
 * @copyright  2018 Jacob Ian Matthews
 */



/**
 * [IgVideoPlay This function pauses and plays the Instagram video when clicked]
 * @param {HTML DOM Object} e The clicked object
 */
function IgVideo(e){

	// Find the post object
	var post = e.target.parentNode;

	// Get the video
	var video = post.getElementsByClassName('instagram_video')[0];

	// Find the play icon
	var icon = post.getElementsByClassName('instagram_playvideo')[0];
	
	// If the video is paused
	if(video.paused){

		// Play the video and let it loop
		video.play();
		video.loop = true;

		// Hide the icon
		icon.style.opacity = '0';


	} else {

		// Pause the video if it is not
		video.pause();

		// Show the icon
		icon.style.opacity = '0.8';

	}


}