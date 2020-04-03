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

// Load the github base configuration
require '../includes/github-base.php';

$currentRunDateTime = new DateTime('now');
$since = $githubApiHelper->getLatestRunDateTime();

// Make sure we only run once a day
if ($currentRunDateTime->format('Y-m-d') === $since->format('Y-m-d'))
{
	exit;
}

$closedTranslationIssues = $githubApiHelper->getClosedAndMergedTranslationIssuesList($since);

if (!empty($closedTranslationIssues) || !is_array($closedTranslationIssues))
{
	// We have issues to check
	foreach ($closedTranslationIssues as $translationIssue)
	{
		$githubApiHelper->createNewTranslationRequestIssueFromMergedTranslationIssue($translationIssue);
	}
}

$githubApiHelper->setLatestRunDateTime($currentRunDateTime);
