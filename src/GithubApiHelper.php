<?php
/**
 * JGerman GitHub Bot Helper based on the Joomla! Framework
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomlagerman\Helper;

use Joomla\Github\Github;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;

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
	 * @param   array  $githubOptions  The options for the Joomla\Github\Github object
	 * @param   array  $options        The options for the GithubApiHelper
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
	 * Returns the latest run date for the item
	 *
	 * @return  DateTime  A DateTime Object with the latest run date
	 *
	 * @since   1.0
	 */
	public function getLatestRunDateTime(): \DateTime
	{
		$dataFileName = $this->getDateFileName('lastrun.data');

		// When there is no file create one with an empty date so it is now.
		if (!is_file($dataFileName))
		{
			$now = new \DateTime('now');
			file_put_contents($dataFileName, $now->format('Y-m-d'));
		}

		return new \DateTime(file_get_contents($dataFileName));
	}

	/**
	 * Sets the latest run date to the given value
	 *
	 * @param   DateTime  $lastRunDateTime  The last run DateTime
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setLatestRunDateTime($lastRunDateTime): void
	{
		$dataFileName = $this->getDateFileName('lastrun.data');

		if (is_file($dataFileName))
		{
			unlink($dataFileName);
		}

		file_put_contents($dataFileName, $lastRunDateTime->format('Y-m-d'));
	}

	/**
	 * Returns the file name to save the latest run DataTime
	 *
	 * @param   string  $fileName  The data filename to use
	 *
	 * @return  string  The dataFile path
	 *
	 * @since   1.0
	 */
	private function getDateFileName($fileName): string
	{
		return $this->dataRootPath . $fileName;
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
	public function getClosedAndMergedTranslationIssuesList($since): array
	{
		// Get all closed issues with the translation label
		$closedIssues = $this->getClosedTranslationIssuesList($since);
		$closedAndMerged = [];

		foreach ($closedIssues as $issue)
		{
			$closedAt = new \DateTime($issue->closed_at);

			// Make sure only the closedAt date is checked and we ignore any additional comments or other updates to the issue
			if ($closedAt->format('Y-m-d') !== $since->format('Y-m-d'))
			{
				continue;
			}

			if ($this->github->pulls->isMerged($this->getOption('source.owner'), $this->getOption('source.repo'), $issue->number)
				&& !empty($this->getChangedTranslationFilesByPR($issue->number)))
			{
				$closedAndMerged[] = $issue;
			}
		}

		return $closedAndMerged;
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

		$labels = NULL;

		return $this->github->issues->getListByRepository(
			$this->getOption('source.owner'), $this->getOption('source.repo'), NULL, $state, NULL, NULL, $labels, NULL, NULL, $since, 0, 500
		);
	}

	/**
	 * Get an pull from the source github owner/repo
	 *
	 * @param   string  $pullrequestId  The pullrequest id
	 *
	 * @return  object
	 *
	 * @since   1.0
	 */
	private function getSourcePull($pullrequestId)
	{
		return $this->github->pulls->get($this->getOption('source.owner'), $this->getOption('source.repo'), $pullrequestId);
	}

	/**
	 * Get the diff for the pullrequest
	 *
	 * @param   string  $pullrequestId  The pullrequest id
	 *
	 * @return  object
	 *
	 * @link  https://developer.github.com/v3/pulls/#get-a-single-pull-request
	 * @link  https://developer.github.com/v3/media/#commits-commit-comparison-and-pull-requests
	 *
	 * @since   1.0
	 */
	private function getSourcePullDiff($pullrequestId)
	{
		$uri = new Uri(
			'https://api.github.com/repos/'
			. $this->getOption('source.owner')
			. '/'
			. $this->getOption('source.repo')
			. '/pulls/'
			. (int) $pullrequestId
		);

		return HttpFactory::getHttp()->get($uri->toString(), ['Accept' => 'application/vnd.github.v3.diff', 'User-Agent' => $this->getOption('userAgent'), 'Authorization' => 'token ' . GITHUB_AUTHTOKEN])->body;
	}

	/**
	 * Get changed files by PR
	 *
	 * @param   string  $pullrequestId  The pullrequest id
	 *
	 * @return  object
	 *
	 * @link  https://docs.github.com/en/rest/pulls/pulls?apiVersion=2022-11-28#list-pull-requests-files
	 *
	 * @since   1.0
	 */
	private function getChangedTranslationFilesByPR($pullrequestId)
	{
		$changedFilesByPR = $this->github->pulls->getFiles($this->getOption('source.owner'), $this->getOption('source.repo'), $pullrequestId);

		$translationFiles = [];

		foreach ($changedFilesByPR as $key => $value)
		{
			if (strpos($value->filename, 'language/') !== false)
			{
				$translationFiles[] = $value->filename;
			}
		}

		return $translationFiles;
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

		$sourcePull = $this->getSourcePull($sourceTranslationIssue->number);
		$labels[]   = $this->getTranslationTargetBranchLabel($sourcePull->base->ref);
		$assigments = $this->getOption('translation.assigments') ?: [];

		// Add label within which client(s) the files are changed
		$changedTranslationFiles = $this->getChangedTranslationFilesByPR($sourceTranslationIssue->number);

		foreach ($changedTranslationFiles as $key => $filename)
		{
			$path = explode('/', $filename);

			if (\in_array($path[0], ['administrator', 'api', 'installation']))
			{
				$labels[] = (string) $path[0];
			}

			if (\in_array($path[0], ['language']))
			{
				$labels[] = 'site';
			}
		}

		// Make sure the lables are unique
		$labels = array_values(array_unique($labels));

		$sourcePullDiff = $this->getSourcePullDiff($sourceTranslationIssue->number);
		$sourcePullDiffText = PHP_EOL . '<details>' . PHP_EOL . '<summary>Click to expand the diff!</summary>' . PHP_EOL . PHP_EOL .
		'```diff' . PHP_EOL . $sourcePullDiff . PHP_EOL . '```' . PHP_EOL . '</details>' . PHP_EOL;

		$body = $this->getOption('translation.templagebody');
		$body = str_replace('[sourcePullRequestUrl]', $sourcePull->_links->html->href, $body);
		$body = str_replace('[sourcePullDiff]', $sourcePullDiffText, $body);

		// Create the issue in the translation owner/repo but skip PRs from the joomla-translation-bot
		if ($sourcePull->user->login !== 'joomla-translation-bot')
		{
			try
			{
				return $this->github->issues->create(
					$this->getOption('translation.owner'),
					$this->getOption('translation.repo'),
					$sourceTranslationIssue->title,
					$body,
					NULL,
					NULL,
					$labels,
					$assigments
				);
			}
			catch (\Exception $e)
			{
				// Try to catch the error by sending the request without the source diff that could couse issues
				$body = $this->getOption('translation.templagebody');
				$body = str_replace('[sourcePullRequestUrl]', $sourcePull->_links->html->href, $body);
				$body = str_replace('[sourcePullDiff]', '', $body);

				return $this->github->issues->create(
					$this->getOption('translation.owner'),
					$this->getOption('translation.repo'),
					$sourceTranslationIssue->title,
					$body,
					NULL,
					NULL,
					$labels,
					$assigments
				);
			}
		}

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
		if ($targetBranch === '3.10-dev')
		{
			return 'Joomla! 3.10';
		}
		return 'Joomla! ' . substr($targetBranch, 0, 3);
	}

	/**
	 * Returns the latest release information
	 *
	 * @param   string  $targetBranch  The branch target of the source repo
	 *
	 * @return  string  The label
	 *
	 * @since   1.0
	 */
	public function getLatestGithubRelease()
	{
		return $this->github->repositories->releases->getLatest($this->getOption('translation.owner'), $this->getOption('translation.repo'));
	}

	/**
	 * Returns the latest release information by branch
	 *
	 * @param   string  $targetBranch  The branch target of the source repo
	 *
	 * @return  string  The label
	 *
	 * @since   1.0
	 */
	public function getLatestGithubReleaseByBranch($branch)
	{
		// Get the last 5 releases
		$last5releases = $this->github->repositories->releases->getList($this->getOption('translation.owner'), $this->getOption('translation.repo'), $page = 0, $limit = 5);

		foreach ($last5releases as $tagName => $release)
		{
			// Check the branch from the tag name
			$branchName = explode('.', $tagName);
			$coreReleaseBranchName = $branchName[0] . '.' . $branchName[1];

			// The tag name does not match, continue here
			if ($coreReleaseBranchName !== $branch)
			{
				continue;
			}

			// Inital value
			if (!isset($latestTag))
			{
				$latestTag = $tagName;
			}

			// When we have found a newer version use it
			if (!version_compare($tagName, $latestTag, '>'))
			{
				$latestTag = $tagName;
			}
		}

		// Return the latest Tage we found
		return $this->github->repositories->releases->getByTag($this->getOption('translation.owner'), $this->getOption('translation.repo'), $latestTag);
	}

	/**
	 * Returns the latest run date for the item
	 *
	 * @param   string  $branch  The core release branch
	 *
	 * @return  string  The last processed release
	 *
	 * @since   1.0
	 */
	public function getLatestPublishedRelease($branch): string
	{
		$dataFileName = $this->getDateFileName('lastrelease' . $branch . '.data');

		// When there is no file create one with an empty date so it is now.
		if (!is_file($dataFileName))
		{
			file_put_contents($dataFileName, $branch . '.0v0');
		}

		return trim(file_get_contents($dataFileName));
	}

	/**
	 * Sets the latest run date to the given value
	 *
	 * @param   string  $branch                The core release branch
	 * @param   string  $lastPublishedRelease  The last processed release
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setLatestPublishedRelease($branch, $lastPublishedRelease): void
	{
		$dataFileName = $this->getDateFileName('lastrelease' . $branch . '.data');

		if (is_file($dataFileName))
		{
			unlink($dataFileName);
		}

		file_put_contents($dataFileName, $lastPublishedRelease);
	}
}
