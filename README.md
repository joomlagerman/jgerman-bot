J!German Bot
======================================

### Description

This repo holds the code powering automation for the JGerman Team. As of today it has the following features:

- [x] autocreate issues to the [joomlagerman/joomla](https://github.com/joomlagerman/joomla) repo when an language changing PR at the [joomla/joomla-cms](https://github.com/joomla/joomla-cms) repo got merged (using [@jgerman-bot](https://github.com/jgerman-bot))
- [x] tweet all new [joomlagerman/joomla](https://github.com/joomlagerman/joomla) releases via our twitter account [@joomlagerman](https://twitter.com/joomlagerman)

## Initial Setup

- `cd /repo/path/jgerman-bot`
- `git clone git@github.com:joomlagerman/jgerman-bot.git .`
- `cp includes/constants.dist.php includes/constants.php`
- `nano includes/constants.php` (Add the access data)
- Setup an daily cronjob on this script: `php cli/jgerman-github-bot.php`
- Setup an cronjob on this script: `php cli/jgerman-twitter-bot.php`

## Update Setup

- `cd /repo/path/jgerman-bot`
- `git reset --hard HEAD && git pull origin master`
