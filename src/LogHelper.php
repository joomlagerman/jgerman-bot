<?php
/**
 * JGerman GitHub Bot Helper based on the Joomla! Framework
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomlagerman\Helper;

/**
 * Class for github
 *
 * @since  1.0
 */
class LogHelper
{
	/**
	 * Constructor.
	 *
	 * @param   array  $options  The options for the LogHelper
	 *
	 * @since   1.0
	 */
	public function __construct($options)
	{
		$this->logfile = ROOT_PATH . '/logs/' . date('Ymd') . '_' . $options['logName'] . '.log';
	}

	/**
	 * Get the log message with date and time.
	 *
	 * @param   string  $message      The log messages
	 * @param   string  $messageType  The log messagetype
	 *
	 * @return  string  The log message including metadata like dates
	 *
	 * @since   1.0
	 */
	private function getLogMessage($message, $messageType = false): string
	{
		if (is_string($messageType))
		{
			return '[' . date('d/m/Y H:i:s') . '] - [' . $messageType . '] - ' . $message . PHP_EOL;
		}

		return '[' . date('d/m/Y H:i:s') . '] - ' . $message . PHP_EOL;
	}

	/**
	 * Write the log message to the log file
	 *
	 * @param   string  $message      The log messages
	 * @param   string  $messageType  The log messagetype
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function writeLogMessage($message, $messageType = false): void
	{
		file_put_contents($this->logfile, $this->getLogMessage($message, $messageType), FILE_APPEND | LOCK_EX);
	}
}
