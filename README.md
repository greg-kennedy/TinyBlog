# TinyBlog
TinyBlog is a very small blog engine written in PHP.

## Origin
TinyBlog grew out of frustration wrangling with Wordpress for a personal blog.  Like most popular software today, Wordpress is aimed at "web-scale" enterprise users with a million viewers and dozens of authors and editors.  It is a fantastic bit of engineering that is also extremely overkill for a casual blog with infrequent updates and only occasional readers.

The fundamentals of blog posting haven't changed much since the days of Livejournal.  You make a post, you can add some tags, you can edit or delete previous posts.  There's an archive, and pages of history, and that's it.

It is stupidly complex and inefficient to require a full CMS to recreate that experience.

## Design
TinyBlog does things differently.  All blog pages are static .html files, containing no Javascript, and no dynamic server-side code.  There is an sqlite3 database, but only for the Admin Area: it is used when making a new post or site changes, to rebuild touched pages from scratch.

## Requirements
* PHP 7+
* PHP sqlite3 extension

## Installation
* Download the TinyBlog release .zip to your web server.
* Unzip TinyBlog to your destination area.  You may change the subdirectory name.
* Point your browser at http://www.your-url.com/TinyBlog
* Click the link to the "Admin Page".  Set up a password, and create your first post.
* That's it!

## Backup
* Copy `admin/tinyblog.db` to a safe location.

## License
TinyBlog is licensed under the 3-clause "Revised" BSD license.  Please see `LICENSE` for more information.

TinyBlog uses php-bbcode for BBCode to HTML translation.  This parser is public domain, and an external site for it is maintained here: https://github.com/greg-kennedy/php-bbcode
