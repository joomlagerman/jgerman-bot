<?php
/**
 * JGerman Twitter Configuration based on the Joomla! Framework
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;
use joomlagerman\Helper\GithubApiHelper;
use joomlagerman\Helper\LogHelper;
use joomlagerman\Helper\NotifyerHelper;
use joomlagerman\Helper\TwitterApiHelper;

// GitHub API Setup
$githubOptions = new Registry;
$githubOptions->set('api.username', GITHUB_USERNAME);
$githubOptions->set('headers', ['Authorization' => 'token ' . GITHUB_AUTHTOKEN]);

$options = new Registry;
$options->set('translation.owner', GITHUB_TRANSLATION_OWNER);
$options->set('translation.repo', GITHUB_TRANSLATION_REPO);

$githubApiHelper = new GithubApiHelper($githubOptions, $options);

// LogHelper Setup
$logHelper = new LogHelper(['logName' => 'jgerman']);

// Notifyer Setup
$notifyerOptions = new Registry;
$notifyerOptions->set('slack.enabled', NOTIFYER_SLACK_ENABED);
$notifyerOptions->set('slack.webhookurl', NOTIFYER_SLACK_WEBHOOKURL);
$notifyerOptions->set('slack.username', NOTIFYER_SLACK_USERNAME);
$notifyerOptions->set('mattermost.enabled', NOTIFYER_MATTERMOST_ENABED);
$notifyerOptions->set('mattermost.webhookurl', NOTIFYER_MATTERMOST_WEBHOOKURL);
$notifyerOptions->set('telegram.enabled', NOTIFYER_TELEGRAM_ENABED);
$notifyerOptions->set('telegram.botToken', NOTIFYER_TELEGRAM_BOTTOKEN);
$notifyerOptions->set('telegram.chatId', NOTIFYER_TELEGRAM_CHATID);
$notifyerOptions->set('notifyer.messageTemplate', NOTIFYER_TWITTER_TWEET_MESSAGE_TEMPLATE);

$notifierHelper = new NotifyerHelper($notifyerOptions);

// Twitter API Setup
$twitterOptions = new Registry;
$twitterOptions->set('consumerKey', TWITTER_CONSUMER_KEY);
$twitterOptions->set('consumerSecret', TWITTER_CONSUMER_SECRET);
$twitterOptions->set('accessToken', TWITTER_ACCESS_TOKEN);
$twitterOptions->set('accessTokenSecret', TWITTER_ACCESS_TOKEN_SECRET);
$twitterOptions->set('tweetTemplate', TWITTER_TWEET_TEMPLATE);

$twitterApiHelper = new TwitterApiHelper($twitterOptions);
