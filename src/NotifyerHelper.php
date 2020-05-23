<?php
/**
 * JGerman Notifyer Helper based on the Joomla! Framework
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomlagerman\Helper;

use Joomla\Http\HttpFactory;

/**
 * Class for github
 *
 * @since  1.0
 */
class NotifyerHelper
{
	/**
	 * The http factory
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $http;

	/**
	 * Constructor.
	 *
	 * @param   array  $options  The options for the NotifyerHelper
	 *
	 * @since   1.0
	 */
	public function __construct($options)
	{
		$this->options = $options ?: new Registry;

		$this->http = HttpFactory::getHttp();
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
	 * Get the Notification message with date and time.
	 *
	 * @param   array   $messageData  The messagedata
	 * @param   string  $messageType  The log messagetype
	 *
	 * @return  string  The log message including metadata like dates
	 *
	 * @since   1.0
	 */
	private function getIssueCreationNotificationMessage($messageData, $messageType = false): string
	{
		$message = $this->getOption('notifyer.messageTemplate');

		foreach ($messageData as $key => $value)
		{
			$message = \str_replace('{' . $key . '}', $value, $message);
		}

		if (is_string($messageType))
		{
			return '[jgerman-bot] - [' . $messageType . '] - ' . $message . PHP_EOL;
		}

		return  '[jgerman-bot] - ' . $message . PHP_EOL;
	}

	/**
	 * Send the Issue creation Notificaton
	 *
	 * @param   array   $messageData  The messagedata
	 * @param   string  $messageType  The log messagetype
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendIssueCreationNotification($messageData, $messageType = false): void
	{
		$this->sendNotificationMessage(
			$this->getIssueCreationNotificationMessage(
				$messageData,
				$messageType
			)
		);
	}

	/**
	 * Send the Log Notifications
	 *
	 * @param   array   $messageData  The messagedata
	 * @param   string  $messageType  The log messagetype
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendLogNotification($message): void
	{
		$this->sendNotificationMessage($message);
	}

	/**
	 * Send the Notifications to the configured endpoints
	 *
	 * @param   array  $message  The messagt to be sended out
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function sendNotificationMessage($message): void
	{
		if ($this->getOption('slack.enabled') === true)
		{
			$data = [
				'payload' => json_encode(
					[
						'username' => $this->getOption('slack.username'),
						'text'     => $message,
					]
				)
			];

			$this->http->post($this->getOption('slack.webhookurl'), $data);
		}

		if ($this->getOption('mattermost.enabled') === true)
		{
			$data = [
				'payload' => json_encode(
					[
						'text' => $message,
					]
				)
			];

			$this->http->post($this->getOption('mattermost.webhookurl'), $data);
		}

		if ($this->getOption('telegram.enabled') === true)
		{
			$data = [
				'chat_id'                  => $this->getOption('telegram.chatId'),
				'parse_mode'               => 'HTML',
				'disable_web_page_preview' => 'true',
				'text'                     => $message,
			];

			$this->http->post('https://api.telegram.org/bot' . $this->getOption('telegram.botToken') . '/sendMessage', $data);
		}
	}
}
