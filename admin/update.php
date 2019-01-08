<?php

/* TinyBlog Update Script
    Re-renders all HTML documents in the parent folder. */

/* big ol' list of replacements */
const FROM_BBCODE = array(
  '/\[code\](.+?)\[\/code\]/is',  // CODE
  '/\n{2,}/', '/\n/',             // NEWLINE
  '/\[b\]/i', '/\[\/b\]/i',       // BOLD
  '/\[i\]/i', '/\[\/i\]/i',       // ITALIC
  '/\[u\]/i', '/\[\/u\]/i',       // UNDERLINE
  '/\[s\]/i', '/\[\/s\]/i',       // STRIKETHROUGH
  '/\[url\](.+?)\[\/url\]/i',     // URL
  '/\[url=([^\]]+)\](.+?)\[\/url\]/i',     // URL
  '/\[img\](.+?)\[\/img\]/i',     // IMG
  '/\[quote\](.+?)\[\/quote\]/i',     // QUOTE
// '/\[quote=([^\]]+)\](.+?)\[\/quote\]/i',     // QUOTE
  '/\[size=([^\]]+)\](.+?)\[\/size\]/i',     // SIZE
  '/\[color=([^\]]+)\](.+?)\[\/color\]/i',     // COLOR
  '/\[list\]/i', '/\[\/list\]/i', // LIST
  '/\[\*\](.+)$/',                // LIST ITEM
  '/\[table\]/i', '/\[\/table\]/i', // TABLE
  '/\[tr\]/i', '/\[\/tr\]/i', // TABLE ROW
  '/\[td\]/i', '/\[\/td\]/i', // TABLE CELL
);
const TO_HTML     = array(
  '<pre>$1</pre>',                // CODE
  '<p>',      '<br>',             // NEWLINE
  '<b>',      '</b>',             // BOLD
  '<i>',      '</i>',             // ITALIC
  '<ins>',    '</ins>',           // UNDERLINE
  '<del>',    '</del>',           // UNDERLINE
  '<a href="$1">$1</a>',          // URL
  '<a href="$1">$2</a>',          // URL
  '<img src="$1">',               // IMG
  '<blockquote>$1</blockquote>',  // QUOTE
// '<blockquote>$2</blockquote>',  // QUOTE
  '<span style="font-size:$1">$2</span>',     // SIZE
  '<span style="font-color:$1">$2</span>',     // COLOR
  '<ul>',     '</ul>',            // LIST
  '<li>$1</li>',                  // LIST ITEM
  '<table>',  '</table>',         // TABLE
  '<tr>',     '</tr>',            // TABLE ROW
  '<td>',     '</td>',            // TABLE CELL
);

/* Renders a post to HTML, for inclusion into a document. */
function render_post($post)
{
  /* BBCode replacements */
  $post = preg_replace(FROM_BBCODE, TO_HTML, $post);

  return $post;
}

/* Re-creates ALL posts */
function create_all_posts($db)
{
  /* Delete all existing posts */
  $files = glob('../post/*.html'); // get all file names
  foreach ($files as $file) { // iterate files
    if (is_file($file)) {
      unlink($file); // delete file
    }
  }

  /* Retrieve list of posts from db */
  $result = $db->query('SELECT id FROM posts');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    // Call create_post for every post in the db
    create_post($db, $row[0]);
  }
  $result->finalize();
}

/* Bakes a new post at a specified URL. */
function create_post($db, $id)
{
  /* Retrieve settings */
  $result = $db->query('SELECT key, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $settings[$row[0]] = $row[1];
  }
  $result->finalize();

  $blog_name = $settings['name'];

  /* get the blog post */
  $stmt = $db->prepare('SELECT date, title, post FROM posts WHERE id=:id');
  $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

  $result = $stmt->execute();

  $num_rows = 0;
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $date = date(DATE_RFC2822, $row[0]);
    $title = $row[1];
    $post = $row[2];
    $num_rows ++;
  }
  $result->finalize();
  $stmt->close();

  if (! $num_rows) {
    throw new Exception("id '$id' did not match any posts in database");
  }

  // bake the post
  $html = <<<HTML
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>$title - $blog_name</title>
  <link rel="stylesheet" type="text/css" href="../style.css">
 </head>
 <body>
  <header id="banner">
   <h1>$blog_name</h1>
  </header>
  <div id="wrap">
   <aside>
    <nav>
     <header>
      <h2>Other</h2>
     </header>
     <ul>
      <li><a href="../archive.html">Blog Archive</a></li>
      <li><a href="../admin">Admin Area</a></li>
     </ul>
    </nav>
   </aside>
   <main>
    <article>
     <header class="title">
      <h2>$title</h2>
      <p>$date</p>
     </header>
     <section>
HTML;
  $html .= render_post($post);
  $html .= <<<HTML
     </section>
    </article>
   </main>
  </div>
  <footer><a href="https://github.com/greg-kennedy/TinyBlog">Powered by TinyBlog</a></footer>
 </body>
</html>
HTML;

  // write to disk
  $file = fopen('../post/' . $id . '.html', 'w');
  fwrite($file, $html);
  fclose($file);
}

/* Bakes a new Index page. */
function create_index($db)
{
  /* Retrieve settings */
  $result = $db->query('SELECT key, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $settings[$row[0]] = $row[1];
  }
  $result->finalize();

  $blog_name = $settings['name'];
  //$index_size = $settings['index_size'];

  /* get the five most recent blog posts */
  $stmt = $db->prepare('SELECT id, date, title, post FROM posts ORDER BY date desc LIMIT 5');

  $result = $stmt->execute();

  $num_rows = 0;
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $id[] = $row[0];
    $date[] = date(DATE_RFC2822, $row[1]);
    $title[] = $row[2];
    $post[] = $row[3];
    $num_rows ++;
  }
  $result->finalize();
  $stmt->close();

  if (! $num_rows) {
    // No posts in DB.  Create the initial "index" page again.
    $html = <<<HTML
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>TinyBlog Default Index</title>
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>
  <header id="banner">
   <h1>TinyBlog</h1>
  </header>
  <div id="wrap">
   <main>
    <article>
     <header class="title">
      <h2>TinyBlog has been successfully installed.</h2>
     </header>
     <section>
      <p>You should proceed to the <b><a href="admin">admin area</a></b> to set up your blog.</p>
     </section>
    </article>
   </main>
  </div>
  <footer><a href="https://github.com/greg-kennedy/TinyBlog">Powered by TinyBlog</a></footer>
 </body>
</html>
HTML;
  } else {
    // bake the post
    $html = <<<HTML
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>$blog_name</title>
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>
  <header id="banner">
   <h1>$blog_name</h1>
  </header>
  <div id="wrap">
   <aside>
    <nav>
     <header>
      <h2>Other</h2>
     </header>
     <ul>
      <li><a href="archive.html">Blog Archive</a></li>
      <li><a href="admin">Admin Area</a></li>
     </ul>
    </nav>
   </aside>
   <main>
HTML;

    for ($i = 0; $i < $num_rows; $i ++) {
      $html .= <<<HTML
    <article>
     <header class="title">
      <h2><a href="post/$id[$i].html">$title[$i]</a></h2>
      <p>$date[$i]</p>
     </header>
     <section>
HTML;
  $html .= render_post($post[$i]);
  $html .= <<<HTML
     </section>
    </article>
HTML;
    }

    $html .= <<<HTML
   </main>
  </div>
  <footer><a href="https://github.com/greg-kennedy/TinyBlog">Powered by TinyBlog</a></footer>
 </body>
</html>
HTML;
  }

  // write to disk
  $file = fopen('../index.html', 'w');
  fwrite($file, $html);
  fclose($file);
}

function create_archive($db)
{
  /* Retrieve settings */
  $result = $db->query('SELECT key, value FROM settings');
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $settings[$row[0]] = $row[1];
  }
  $result->finalize();

  $blog_name = $settings['name'];

  /* get all the blog posts */
  $stmt = $db->prepare('SELECT id, date, title FROM posts ORDER BY date desc');

  $result = $stmt->execute();

  $num_rows = 0;
  while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $id[] = $row[0];
    $date[] = date(DATE_RFC2822, $row[1]);
    $title[] = $row[2];
    $num_rows ++;
  }
  $result->finalize();
  $stmt->close();

/*
  if (! $num_rows) {
    // TODO: empty blog, replace with boilerplate
    throw new Exception("id '$id' did not match any posts in database");
  }
*/

  // bake the post
  $html = <<<HTML
<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8">
  <title>Archive - $blog_name</title>
  <link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>
  <header id="banner">
   <h1>$blog_name</h1>
  </header>
  <div id="wrap">
   <aside>
    <nav>
     <header>
      <h2>Other</h2>
     </header>
     <ul>
      <li><a href="archive.html">Blog Archive</a></li>
      <li><a href="admin">Admin Area</a></li>
     </ul>
    </nav>
   </aside>
   <main>
    <article>
     <header class="title">
      <h2>Blog Archive</h2>
      <p>Complete list of posts:</p>
     </header>
     <section>
      <table>
       <tr>
        <th>Date Posted</th>
        <th>Title</th>
       </tr>
HTML;
  for ($i = 0; $i < $num_rows; $i ++) {
    $html .= <<<HTML
       <tr>
        <td>$date[$i]</td>
        <td><a href="post/$id[$i].html">$title[$i]</a></td>
       </tr>
HTML;
  }
  $html .= <<<HTML
      </table>
     </section>
    </article>
   </main>
  </div>
  <footer><a href="https://github.com/greg-kennedy/TinyBlog">Powered by TinyBlog</a></footer>
 </body>
</html>
HTML;

  // write to disk
  $file = fopen('../archive.html', 'w');
  fwrite($file, $html);
  fclose($file);
}
