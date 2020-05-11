<?php
/**
 * JGerman GitHub Bot Configuration
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;
use joomlagerman\Helper\GithubApiHelper;
use joomlagerman\Helper\LogHelper;
use joomlagerman\Helper\NotifyerHelper;

$logHelper = new LogHelper(['logName' => 'jgerman']);

$githubOptions = new Registry;
$githubOptions->set('api.username', GITHUB_USERNAME);
$githubOptions->set('headers', ['Authorization' => 'token ' . GITHUB_AUTHTOKEN]);

$options = new Registry;
$options->set('source.owner', GITHUB_SOURCE_OWNER);
$options->set('source.repo', GITHUB_SOURCE_REPO);
$options->set('source.watchlabel', GITHUB_SOURCE_WATCHLABEL);
$options->set('translation.owner', GITHUB_TRANSLATION_OWNER);
$options->set('translation.repo', GITHUB_TRANSLATION_REPO);
$options->set('translation.label', GITHUB_TRANSLATION_LABEL);
$options->set('translation.assigments', GITHUB_TRANSLATION_ASSIGMENTS);
$options->set('translation.templagebody', GITHUB_TRANSLATION_TEMPLATE_BODY);

$githubApiHelper = new GithubApiHelper($githubOptions, $options);

$notifyerOptions = new Registry;
$options->set('slack.enabled', NOTIFYER_SLACK_ENABED);
$options->set('slack.webhookurl', NOTIFYER_SLACK_WEBHOOKURL);
$options->set('slack.username', NOTIFYER_SLACK_USERNAME);
$options->set('mattermost.enabled', NOTIFYER_MATTERMOST_ENABED);
$options->set('mattermost.webhookurl', NOTIFYER_MATTERMOST_WEBHOOKURL);
$options->set('telegram.enabled', NOTIFYER_TELEGRAM_ENABED);
$options->set('telegram.botToken', NOTIFYER_TELEGRAM_BOTTOKEN);
$options->set('telegram.chatId', NOTIFYER_TELEGRAM_CHATID);
$options->set('notifyer.messageTemplate', NOTIFYER_MESSAGE_TEMPLATE);

$notifierHelper = new NotifyerHelper($notifyerOptions);
