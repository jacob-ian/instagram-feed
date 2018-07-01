# Description:
An Instagram Feed using the old API. It will cache the post data in your webserver and use a MYSQL database to store the metadata to maximise loading speeds.


# Requirements:

- An Instagram Access Token connected to the desired account to display. Note: The account must be public.
- PHP 7.2+



# Usage:

1. Include this repository in your project via Composer (jacob-ian/instagram-feed).

2. Create a MYSQL Database to hold the data:

	```mysql
	CREATE DATABASE instagram
	```
	The ```\JacobIan\InstagramFeed\instagramFetch``` class will automatically create the table and populate it.


2. Setup a cron-job that will point to a PHP file containing:

	```php
	require_once('instagram-feed/src/instagramFetch.class.php');
	$instagramData = new \JacobIan\InstagramFeed\instagramFetch($database, $accesstoken, $cachepath);
	$instagramData->fetch();
	```
	The `require_once()` can be replaced with an autoloader.

	Where: 
	- `$database` should be an array with the structure:

		```php
		$database = array(
			"username"=>"[username]",
			"password"=>"[password]",
			"host"=>"[host]",
			"database"=>"instagram"
		);
		```
	- `$accesstoken` is a string containing the Instagram Access Token relating to the account you wish to display.
	- `$cachepath` should be a string with the path to a location in the public_html/webroot directory where you wish the Instagram Cache and Assets to be stored.

	This cron job should run every 15 minutes, but frequency can be increased depending on how often you wish the feed to refresh.


3. To create the feed itself, use the following code on your PHP webpage:

	```php
	require_once('instagram-feed/src/instagramFeed.class.php');
	$instagram_feed = new \JacobIan\InstagramFeed\instagramFeed($database, $count, $style);
	echo $instagram_feed->feed();
	```
	
	Again, the ```require_once()``` can be replaced by an autoloader.

	Where:
	- `$count` is an integer describing the number of latest posts to display in the feed. Instagram API is limited to a maximum of 20 posts.
	- `$style` is a string containing the desired CSS Style tags on each Instagram Post. See CSS Styles below.

4. Include the following two lines of code to the `<head>` of the webpage containing the Instagram Feed:
	```html
	<link rel="stylesheet" type="text/css" href="https://min.gitcdn.xyz/repo/jacob-ian/instagram-feed/master/css/feed.css"/>
	<script src="https://min.gitcdn.xyz/repo/jacob-ian/instagram-feed/master/js/feed.js"></script>
	```

5. Enjoy!


## CSS Styles:

The available CSS tags are:
	
Size:
- `'small'` : Grid size of 150x150px with a frame around the media
- `'medium'` : Grid size of 300x300px with a frame around the media
- `'large'` : Grid size of 640x640px with a frame around the media

- `'small_noframe'` : Grid size of 150x150px without a frame
- `'medium_noframe'` : Grid size of 300x300px without a frame
- `'large_noframe'` : Grid size of 640x640px without a frame

Color:
- `'dark'` : Sets post background-color to `#333` (dark-grey)
- `'transparent'` : Sets post background-color to transparent
- `'noborder'` : Removes the border on the posts
- Note: the default background-color is `#fff` (white).

Spacing:
- `'nomargin'` : Removes the gap between the posts in the Instagram Feed grid.


Example Styles: 

- `'small dark'` : A small grid size with a frame of colour #333 around the media
- `'large_noframe nomargin noborder'` : A large grid size without a frame and no gaps between the posts. This setup makes it easy to use the Masonry JS Script.

Usage:
	
- Only one Size tag can be used, and it is also the minimum requirement for the feed to work.
- After the size tag, the other tags can be added on with a space in between.


Custom Styles:

- Custom Styles can be used by adding them to the `$style` string with a space.
- To use a custom size style, the size tag can be replaced by a custom string. This will then be converted to the CSS tag `post_[custom]` and `media_[custom]`.




## Notes:

- The `\JacobIan\InstagramFeed\instagramFetch` class automatically transfers the contents of ```'..\assets'``` to the cache folder that is publicly accessable.
- `\JacobIan\InstagramFeed\instagramFetch` creates a database table named 'details' which contains the following information:
	- UserID: Instagram User ID
	- ProfilePictureURL: The URL to your profile picture
	- ProfilePicturePath: The path to the locally stored copy of your profile picture
	- Followers: Your follower count
	- Posts: Your post count
	- LastUpdate: The time of the last successful instagramFetch cron job.
	- CachePath: The path where your Instagram Cache and Assets are stored.
