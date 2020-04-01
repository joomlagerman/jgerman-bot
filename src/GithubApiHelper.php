<?php
/**
 * JGerman GitHub Bot Helper based on the Joomla! Framework
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomlagerman\Helper;

use Joomla\Github\Github;
use Joomla\Registry\Registry;

/**
 * Class for github
 *
 * @since  1.0
 */
class GithubApiHelper
{
	/**
	 * The github object
	 *
	 * @var    Github
	 * @since  1.0
	 */
	private $github;

	/**
	 * The github object
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Options to init the connection
	 *
	 * @since   1.0
	 */
	public function __construct($githubOptions, $options)
	{
		$this->options = $options ?: new Registry;

		// Setup the default user agent if not already set.
		if (!$this->getOption('userAgent'))
		{
			$this->setOption('userAgent', 'JGerman-Bot/1.0');
		}

		$this->github = new Github($githubOptions);
		$this->dataRootPath = ROOT_PATH . '/data/';
	}

	/**
	 * Returns the latest publication date for the item
	 *
	 * @param   string    $rssValue  The RSS value
	 * @param   string    $rssType   The RSS type
	 *
	 * @return  DateTime  A DateTime Object with the latest publication date
	 *
	 * @since   1.0
	 */
	public function getLatestRunDateTime(): \DateTime
	{
		$dataFileName = $this->getDateFileName();

		// When there is no file create one with an empty date so it is now.
		if (!is_file($dataFileName))
		{
			$now = new \DateTime('now');
			file_put_contents($dataFileName, $now->format(\DateTime::ISO8601));
		}

		return new \DateTime(file_get_contents($dataFileName));
	}

	/**
	 * Sets the latest publication date to the given value
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setLatestRunDateTime($lastRunDateTime): void
	{
		$dataFileName = $this->getDateFileName();

		if (is_file($dataFileName))
		{
			unlink($dataFileName);
		}

		file_put_contents($dataFileName, $lastRunDateTime->format(\DateTime::ISO8601));
	}

	/**
	 * Returns the file name to save the latest run DataTime
	 *
	 *
	 * @return  string  The dataFile path
	 *
	 * @since   1.0
	 */
	public function getDateFileName(): string
	{
		return $this->dataRootPath . 'lastrun.data';
	}

	/**
	 * Get an option from the instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   1.0
	 */
	public function getOption($key)
	{
		return isset($this->options[$key]) ? $this->options[$key] : null;
	}

	/**
	 * Set an option for the instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  GithubApiHelper  This object for method chaining.
	 *
	 * @since   1.0
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * Get all closed and merged PRs with the translation label since a given timestamp
	 *
	 * @param   DateTime  $since  The timestamp since we want new data
	 *
	 * @return  array  An array of github Issue objects
	 *
	 * @since   1.0
	 */
	public function getClosedAndMergedTranslationIssuesList($since)
	{
		// Get all closed issues with the translation label
		$closedIssues = $this->getClosedTranslationIssuesList($since);

		foreach ($closedIssues as $issue)
		{
			if ($this->github->pulls->isMerged($this->getOption('source.owner'), $this->getOption('source.repo'), $issue->number))
			{
				$closedAndMerged[] = $issue;
			}
		}

		return isset($closedAndMerged) ? $closedAndMerged : [];
	}

	/**
	 * Get all closed issues with the translation label since a given timestamp
	 *
	 * @param   DateTime  $since  The timestamp since we want new data
	 *
	 * @return  array  an array of github Issue objects
	 *
	 * @since   1.0
	 */
	private function getClosedTranslationIssuesList($since)
	{
		// List all closed issues with the watchlabel
		$state  = 'closed';
		$labels = urlencode($this->getOption('source.watchlabel'));

		return $this->github->issues->getListByRepository(
			$this->getOption('source.owner'), $this->getOption('source.repo'), NULL, $state, NULL, NULL, $labels, NULL, NULL, $since
		);
	}

	/**
	 * Get an pull from the source github owner/repo
	 *
	 * @param   string  $issueId  The issue id
	 *
	 * @return  object
	 *
	 * @since   1.0
	 */
	public function getSourcePull($issueId)
	{
		return $this->github->pulls->get($this->getOption('source.owner'), $this->getOption('source.repo'), $issueId);
	}

	/**
	 * Creates an translation request issue
	 *
	 * @param   object  $sourceTranslationIssue  The sourceTranslationIssue Object
	 *
	 * @return  boolean  True on success false on failiure
	 *
	 * @since   1.0
	 */
	public function createNewTranslationRequestIssueFromMergedTranslationIssue($sourceTranslationIssue)
	{
		// Labels
		$labels[] = $this->getOption('translation.label');

		$pull = $this->getSourcePull($sourceTranslationIssue->number);
		$labels[] = $this->getTranslationTargetBranchLabel($pull->base->ref);

		$sourcePullRequestUrl = $pull->_links->html->href;
		$body = $this->getOption('translation.templagebody');
		$body = str_replace('[sourcePullRequestUrl]', $sourcePullRequestUrl, $body);

		// Get the changed ini files and add that to the body message with open checkboxes.

		// Create the issue in the translation owner/repo
		return $this->github->issues->create(
			$this->getOption('translation.owner'),
			$this->getOption('translation.repo'),
			$sourceTranslationIssue->title,
			$body,
			NULL,
			NULL,
			$labels,
			$this->getOption('translation.assigments')
		);
	}

	/**
	 * Returns the correct target label for a given target branch
	 *
	 * @param   string  $targetBranch  The branch target of the source repo
	 *
	 * @return  string  The label
	 *
	 * @since   1.0
	 */
	private function getTranslationTargetBranchLabel($targetBranch)
	{
		if ($targetBranch === 'staging')
		{
			return 'Joomla! 3.x';
		}

		return 'Joomla! ' . substr($targetBranch, 0, 1) . '.x';
	}
}
