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

$logHelper->writeLogMessage('Start JGerman GitHub Bot');

$currentRunDateTime = new DateTime('now');
$lastRunDate = $githubApiHelper->getLatestRunDateTime();

// Make sure we only run once a day
if ($currentRunDateTime->format('Y-m-d') === $lastRunDate->format('Y-m-d'))
{
	$logHelper->writeLogMessage('We only run once a day so exiting here.');
	$logHelper->writeLogMessage('End JGerman GitHub Bot');
	exit;
}

$closedTranslationIssues = $githubApiHelper->getClosedAndMergedTranslationIssuesList($lastRunDate);

$logHelper->writeLogMessage('We have ' . count($closedTranslationIssues) . ' closed translation issues since the last run.');

if (!empty($closedTranslationIssues) || !is_array($closedTranslationIssues))
{
	$createdTranslationRequestIssues = 0;

	// We have issues to check
	foreach ($closedTranslationIssues as $translationIssue)
	{
		$return = $githubApiHelper->createNewTranslationRequestIssueFromMergedTranslationIssue($translationIssue);

		if ($return)
		{
			$createdTranslationRequestIssues++;
		}
	}

	$logHelper->writeLogMessage('We have ' . $createdTranslationRequestIssues . ' translation request issues created.');
}

$logHelper->writeLogMessage('Set the new latest run date to:' . $currentRunDateTime->format('Y-m-d'));
$githubApiHelper->setLatestRunDateTime($currentRunDateTime);
$logHelper->writeLogMessage('End JGerman GitHub Bot');
