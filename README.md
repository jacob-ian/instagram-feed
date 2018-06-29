# Description:
An Instagram Feed using the old API. It will cache the post data in your webserver and use a MYSQL database to store the metadata to maximise loading speeds.


# Requirements:

- An Instagram Access Token connected to the desired account to display. Note: The account must be public.
- PHP 7.2+



# Usage:

1. Include this repository in your project via Composer.

2. Create a MYSQL Database to hold the data:

	```mysql
	CREATE DATABASE instagram
	```
	The instagramFetch class will automatically create the table and populate it.


2. Setup a cron-job that will point to a PHP file containing:

	```php
	require_once('instagram-feed/src/instagramFetch.class.php');
	$instagramData = new \JacobIan\InstagramFeed\instagramFetch($database, $accesstoken, $cachepath);
	$instagramData->fetch();
	```
	The require_once can be replaced with an autoloader.

	Where: 
	- $database should be an array with the structure:

		```php
		$database = array(
			"username"=>"[username]",
			"password"=>"[password]",
			"host"=>"[host]",
			"database"=>"instagram"
		);
		```
	- $accesstoken is a string containing the Instagram Access Token relating to the account you wish to display.
	- $cachepath should be a string with the path to a location in the public_html/webroot directory where you wish the Instagram Cache and Assets to be stored.

	This cron job should run every 15 minutes, but frequency can be increased depending on how often you wish the feed to refresh.


3. To create the feed itself, use the following code on your PHP webpage:

	```php
	require_once('instagram-feed/src/instagramFeed.class.php');
	$instagram_feed = new \JacobIan\InstagramFeed\instagramFeed($database, $count, $style);
	echo $instagram_feed->feed();
	```
	
	Again, the require_once can be replaced by an autoloader.

	Where:
	- $count is an integer describing the number of latest posts to display in the feed. Instagram API is limited to a maximum of 20 posts.
	- $style is a string containing the desired CSS Style tags on each Instagram Post (the grid size). The available tags are:
		- 'post_small' : Grid size of 150x150px
		- 'post_medium' : Grid size of 300x300px
		- 'post_large' : Grid size of 640x640px

4. Enjoy!


# To Do:

- Create the stylesheets
- Create a CDN to hold the stylesheets



# Notes:

- The ```\JacobIan\InstagramFeed\instagramFetch``` class automatically transfers the contents of ```'..\assets'``` to the cache folder that is publicly accessable.
- ```\JacobIan\InstagramFeed\instagramFetch() ``` creates a database table named 'details' which contains the following information:
	- UserID: Instagram User ID
	- ProfilePictureURL: The URL to your profile picture
	- ProfilePicturePath: The path to the locally stored copy of your profile picture
	- Followers: Your follower count
	- Posts: Your post count
	- LastUpdate: The time of the last successful instagramFetch cron job.
	- CachePath: The path where your Instagram Cache and Assets are stored.
- Custom CSS can be used to style the Instagram posts by using a custom tag for $style when creating the \JacobIan\InstagramFeed\instagramFeed object.
