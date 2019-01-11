<?php

/* Settings handler for TinyBlog */

// Global settings structure
//  All settings for the blog must be listed here
//  Format:
//   key => [DescriptiveName, Type, DefaultValue, RequiresRebuild]

// settings types
const TYPE_STRING = 0;
const TYPE_INTEGER = 1;
const TYPE_PASSWORD = 2;

const REBUILD_NONE = 0;
const REBUILD_INDEX = 1;
const REBUILD_FEED = 2;

define('SETTINGS', [
  ['password', 'Admin Password', TYPE_PASSWORD, 'admin', REBUILD_NONE ],
  ['blog_name', 'Blog Name', TYPE_STRING, 'TinyBlog', REBUILD_INDEX | REBUILD_FEED ],
  ['blog_author', 'Blog Author', TYPE_STRING, 'TinyBlog', REBUILD_FEED ],
  ['blog_url', 'Blog URL', TYPE_STRING, (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", REBUILD_FEED ],
  ['index_size', 'Index Posts', TYPE_INTEGER, 5, REBUILD_INDEX ],
]);

// Populate a settings array with all blog settings.
function settings_load($db)
{
  // Default values for settings.
  // saved settings in DB
  $result = $db->query('SELECT key, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $db_settings[$row[0]] = $row[1];
  }
  $result->finalize();

  // Create the settings array we wish to return
  //  fill the table with our settings, using the defaults where missing from db
  foreach (SETTINGS as $setting) {
    $name = $setting[0];
    $default = $setting[3];

    if (array_key_exists($name, $db_settings)) {
      $settings[$name] = $db_settings[$name];
    } else {
      $settings[$name] = $default;
    }
  }

  return $settings;
}

