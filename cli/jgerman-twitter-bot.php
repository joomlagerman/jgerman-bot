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

$latestGithubRelease = $githubApiHelper->getLatestGithubRelease();
$logHelper->writeLogMessage('Latest JGerman GitHub Release: ' . $latestGithubRelease->tag_name);
$latestPublishedRelease = (string) $githubApiHelper->getLatestPublishedRelease();
$logHelper->writeLogMessage('Latest processed Release: ' . $latestPublishedRelease);

/**
 * GitHub Varibales
 * https://github.com/joomlagerman/joomla/releases/tag/3.9.19v2
 * $latestGithubRelease->html_url;
 * 3.9.19v2
 * $latestGithubRelease->tag_name;
 * // 3.9.19v2 for Joomla! 3.9.19
 * $latestGithubRelease->name;
 * // bool(true/false)
 * $latestGithubRelease->prerelease
 */

if (!version_compare($latestGithubRelease->tag_name, $latestPublishedRelease, '>'))
{
	$logHelper->writeLogMessage('End JGerman Twitter Bot');
	exit;
}

if ($latestGithubRelease->prerelease === true)
{
	$logHelper->writeLogMessage('End JGerman Twitter Bot');
	exit;
}

$notifierHelper->sendLogNotification('Start JGerman Twitter Bot');

$releaseName = str_replace('for', 'für', $latestGithubRelease->name);

$tweetText = $twitterApiHelper->getOption('tweetTemplate');
$tweetText = str_replace('[URL]', $latestGithubRelease->html_url, $tweetText);
$tweetText = str_replace('[releaseNameVersion]', $releaseName, $tweetText);

$createdTweet = $twitterApiHelper->sendTweet($tweetText);

/**
 * object(stdClass)#26 (24) {
 * ["created_at"]=>
 * string(30) "Sun Aug 23 06:53:12 +0000 2020"
 * ["id"]=>
 * int(1297426645998084101)
 * ["id_str"]=>
 * string(19) "1297426645998084101"
 * ["text"]=>
 * string(146) "Wir haben gerade die deutsche Übersetzung in Version 3.9.20v1 für Joomla! 3.9.20 veröffentlicht. Die Übersetzung st… https://t.co/0aFKpzeyee"
 * ["truncated"]=>
 * bool(true)
 * ["entities"]=>
 * object(stdClass)#51 (4) {
 *   ["hashtags"]=>
 *   array(0) {
 *   }
 *   ["symbols"]=>
 *   array(0) {
 *   }
 *   ["user_mentions"]=>
 *   array(0) {
 *   }
 *   ["urls"]=>
 *   array(1) {
 *     [0]=>
 *     object(stdClass)#52 (4) {
 *     ["url"]=>
 *     string(23) "https://t.co/0aFKpzeyee"
 *     ["expanded_url"]=>
 *     string(52) "https://twitter.com/i/web/status/1297426645998084101"
 *     ["display_url"]=>
 *     string(29) "twitter.com/i/web/status/1…"
 *     ["indices"]=>
 *     array(2) {
 *       [0]=>
 *       int(117)
 *       [1]=>
 *       int(140)
 *     }
 *   }
 * }
 */

$githubApiHelper->setLatestPublishedRelease($latestGithubRelease->tag_name);

// send notification
$notifierHelper->sendMessageTemplateNotification([
	'tweetText' => $createdTweet->text,
	'tweetUrl'  => 'https://twitter.com/joomlagerman/status/' . $createdTweet->id,
]);

$logHelper->writeLogMessage('End JGerman Twitter Bot');
$notifierHelper->sendLogNotification('End JGerman Twitter Bot');
