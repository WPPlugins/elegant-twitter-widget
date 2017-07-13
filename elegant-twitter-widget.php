<?php
/*
Plugin Name: Elegant Twitter Widget
Plugin URI: http://www.zenofshen.com/elegant-twitter-widget/
Description: A WordPress widget that displays twitter updates in yummy valid semantic XHTML code by parsing XML.
Version: 1.0
Author: Paul Shen
Author URI: http://www.zenofshen.com
License: GPL (http://www.gnu.org/copyleft/gpl.html)
Notes: Requires at least PHP >= 5.0. Options for the widget are username and number of status updates. Template functions are provided for customizability.
*/

/* Version 1.0 - initial release
 * January 7, 2008
 */


/* The HTML code that is displayed before the list of twitter statuses. */
function topwrapper_template() {
?>
	<li id="twitter-sidebar" class="widget widget_twitter">
		<h3 class="widgettitle">twitter</h3>
		<ul id="twitter-list">
<?php
}

/* The HTML code that is displayed for each status.
 * Comes with the following variables
 * $username
 * $text - the text of the status update
 * $id - the id of the update (used for permalinking)
 * $timetext - the time of update in twitter-esque text (i.e. about an hour ago, two days ago,...)
 * $timestamp - the UNIX timestamp of the update
 */
function status_template($username, $text, $id, $timetext, $timestamp) {
?>
			<li class="twitter-status">
				<?php echo $text; ?><br />
				<span class="twitter-meta">tweeted <a href="http://twitter.com/<?php echo $username  ?>/statuses/<?php echo $id; ?>" title="Permalink to tweet #<?php echo $id; ?>"><?php echo $timetext; ?></a></span>
			</li>
<?php
}

/* The HTML code that is displayed after the list of twitter statuses. */
function bottomwrapper_template() {
?>
		</ul>
	</li>
<?php
}


function widget_twitter() {
	if (!$options = get_option('Twitter Widget'))
		$options = array('username' => '', 'numStatuses' => 3);
	
	// Get the XML feed from twitter
	$url = 'http://twitter.com/statuses/user_timeline/' . $options['username'] . '.xml?count=' . $options['numStatuses'];
	$response = file_get_contents($url);
	
	// Create the SimpleXML object
	$statuses = simplexml_load_string($response);

	topwrapper_template();

	foreach ($statuses->status as $status) {
		// First calculate the $timetext. The integer values are in seconds. Change text as wanted.
		$timediff = time() - strtotime($status->created_at);
		if ($timediff < 59)
			$timetext = 'less than a minute ago';
		else if ($timediff < 119)
			$timetext = 'about a minute ago';
		else if ($timediff < 3000)
			$timetext = (int)($timediff / 60) . ' minutes ago';
		else if ($timediff < 5340)
			$timetext = 'about an hour ago';
		else if ($timediff < 9000)
			$timetext = 'a couple of hours ago';
		else if ($timediff < 82800)
			$timetext = 'about ' . (int)($timediff / 3600) . ' hours ago';
		else if ($timediff < 129600)
			$timetext = 'a day ago';
		else if ($timediff < 172800)
			$timetext = 'almost 2 days ago';
		else $timetext = (int)($timediff / 86400) . ' days ago';
		
		status_template($options['username'], $status->text, $status->id, $timetext, strtotime($status->created_at));
	}

	bottomwrapper_template();
}

/* The code for the widget options */
function widget_twitter_options() {
	if(!$options = get_option('Twitter Widget'))
		$options = array('username' => '', 'numStatuses' => 3);
	
	if($_POST['twitter-submit']) {
		$options = array('username' => $_POST['twitter-username'], 'numStatuses' => $_POST['twitter-numStatuses']);
		update_option('Twitter Widget', $options);
	}
?>
	<p>Name: <input type="text" id="twitter-username" name="twitter-username" value="<?php echo $options['username']; ?>" /></p>
	<p>Number Statuses: <input type="text" id="twitter-numStatuses" name="twitter-numStatuses" value="<?php echo $options['numStatuses']; ?>" /></p>
	<input type="hidden" id="twitter-submit" name="twitter-submit" value="1" />
<?php
}

function twitter_init() {
	register_sidebar_widget(__('Twitter Widget'), 'widget_twitter');
	register_widget_control(__('Twitter Widget'), 'widget_twitter_options', 200, 200);
}

add_action('plugins_loaded', 'twitter_init');
?>
