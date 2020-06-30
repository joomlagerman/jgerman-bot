<?php
/**
 * JGerman GitHub Bot based on the Joomla! Framework
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (PHP_SAPI != 'cli')
{
	echo 'This script needs to be called via CLI!' . PHP_EOL;
	exit;
}

// Set error reporting for development
error_reporting(-1);

// Load the contstants
require dirname(__DIR__) . '/includes/constants.php';

// Ensure we've initialized Composer
if (!file_exists(ROOT_PATH . '/vendor/autoload.php'))
{
	exit(1);
}

require ROOT_PATH . '/vendor/autoload.php';

// Load the github base configuration
require dirname(__DIR__) . '/includes/twitter-base.php';

$logHelper->writeLogMessage('Start JGerman Twitter Bot');


$latestGithubRelease = (string) $githubApiHelper->getLatestGithubRelease();
$logHelper->writeLogMessage('Latest JGerman GitHub Release: ' . $latestGithubRelease);
$latestPublishedRelease = (string) $githubApiHelper->getLatestPublishedRelease();
$logHelper->writeLogMessage('Latest processed Release: ' . $latestPublishedRelease);


$logHelper->writeLogMessage('End JGerman Twitter Bot');

if (!version_compare($latestGithubRelease, $latestPublishedRelease, '>'))
{
	$logHelper->writeLogMessage('End JGerman Twitter Bot');
}


//setLatestPublishedRelease

// https://github.com/joomlagerman/joomla/releases/tag/3.9.19v2
$latestGithubRelease->html_url;

// 3.9.19v2
$latestGithubRelease->tag_name;

// 3.9.19v2 for Joomla! 3.9.19
$latestGithubRelease->name;

// bool(false) / true
$latestGithubRelease->prerelease;


//var_dump($twitterApiHelper->sendTweet('Mein erster neuer Tweet.'));

exit;



// check latest release

// check that is is a new release

$notifierHelper->sendLogNotification('Start JGerman Twitter Bot');

// post the tweet

// send notification
$notifierHelper->sendTweetCreationNotification([
	'tweetText' => $createdTweet->text,
	'tweetUrl'  => 'https://twitter.com/joomlagerman/status/' . $createdTweet->id,
]);

$logHelper->writeLogMessage('End JGerman Twitter Bot');
$notifierHelper->sendLogNotification('End JGerman Twitter Bot');
