<?php
/**
 * JGerman GitHub Bot Configuration
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set error reporting for development
error_reporting(-1);

// Load the system contstants
require 'constants.php';

// Ensure we've initialized Composer
if (!file_exists(ROOT_PATH . '/vendor/autoload.php'))
{
	exit(1);
}

require ROOT_PATH . '/vendor/autoload.php';

use Joomla\Registry\Registry;
use joomlagerman\Helper\GithubApiHelper;
use joomlagerman\Helper\LogHelper;

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
