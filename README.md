J!German Bot
======================================

### Description

This repo holds the code powering the @jgerman-bot account. As of today it has the following features:
- autocreate issues to the joomlagerman/joomla repo when an language changing PR at the joomla/joomla-cms repo got merged

## Initial Setup

- `cd /repo/path/jgerman-bot`
- `git clone git@github.com:joomlagerman/jgerman-bot.git .`
- `mv includes/constants.php.dist includes/constants.php`
- `nano includes/constants.php` (Add the access data)

## Update Setup

- `cd /repo/path/jgerman-bot`
- `git reset --hard HEAD && git pull origin master`
