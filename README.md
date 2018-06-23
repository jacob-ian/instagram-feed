# instagram-feed
An Instagram Feed using the old API. It will cache the post data in the webserver and use a MYSQL database to store the metadata.

Requirements:

- An Instagram Access Token connected to the desired account to display. Note: The account must be public.
- PHP 7.2+


Usage:

1. Include this repository in your project via Composer.

2. Create a MYSQL Database to hold the data:

```mysql

CREATE DATABASE instagram

```
- the instagramFetch class will automatically create the table and populate it.


2. Setup a cron-job that will point to a PHP file containing:

```php

require_once('/src/instagramFetch.class.php');

$instagramData = new instagramFetch($AccessToken, $database);
$instagramData->fetch();

```
- Note that the parameter $database should be an array with the structure:

```php

$database = array(
	"username"=>"[username]",
	"password"=>"[password]",
	"host"=>"[host]",
	"database"=>"instagram"
);

```

- This cron job should run every 15 minutes, but frequency can be increased depending on how often you wish the feed to refresh.


3. To create the feed itself, use the following code on your PHP webpage:

```php

require_once('/src/instagramFeed.class.php');

$instagram_feed = new instagramFeed($database, $count);
print_r($instagram_feed->feed());


```

- Where $count is an integer describing the number of latest posts to display in the feed. Instagram API is limited to a maximum of 20 posts.
