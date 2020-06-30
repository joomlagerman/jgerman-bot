<?php
/**
 * JGerman Twitter Helper based on the Joomla! Framework
 *
 * @copyright  Copyright (C) 2020 J!German (www.jgerman.de) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomlagerman\Helper;

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class for Twitter
 *
 * @since  1.0
 */
class TwitterApiHelper
{
	/**
	 * The twitter object
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $twitter;

	/**
	 * The twitter api options
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
	public function __construct($options)
	{
		$this->options = $options;
		$this->twitter = new TwitterOAuth(
			$this->getOption('consumerKey'),
			$this->getOption('consumerSecret'),
			$this->getOption('accessToken'),
			$this->getOption('accessTokenSecret')
		);
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
	 * @return  TwitterApiHelper  This object for method chaining.
	 *
	 * @since   1.0
	 */
	public function setOption($key, $value): TwitterApiHelper
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * Send the tweet out
	 *
	 * @param   string  $tweetText The Text of the tweet
	 *
	 * @return  \stdClass
	 *
	 * @since   1.0
	 */
	public function sendTweet($tweetText): \stdClass
	{
		return $this->twitter->post('statuses/update', ['status' => $tweetText]);
	}

	/**
	 * Verify the supplied Credentials
	 *
	 * @param   string  $tweetText The Text of the tweet
	 *
	 * @return  \stdClass
	 *
	 * @since   1.0
	 */
	public function verifyCredentials(): \stdClass
	{
		return $this->twitter->get('account/verify_credentials');
	}
}
