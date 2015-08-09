<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

$config = parse_ini_file("../config.ini");

require_once("php/general.php");

require_once("php/pages/article.php");
require_once("php/pages/author.php");
require_once("php/pages/index.php");

// we try to construct the page object
try {
  // initialize the global database object
  try {
    $database = new PDO("sqlite:" . "../" . $config["path"]);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch(PDOException $e) {
    print "Something went wrong with the database. If the problem persists, please contact us at <a href='mailto:pieterbelmans@gmail.com'>pieterbelmans@gmail.com</a>.";
    // if there is actually a persistent error: add output code here to check it
    exit();
  }

  // determine the type of page
  if (empty($_GET["page"]))
    $type = "index";
  else
    $type = $_GET["page"];

  // build the correct page
  switch($type) {
    case "index":
      $page = new IndexPage($database);
      break;

    case "articles":
      if (empty($_GET["id"])) {
        $page = new ErrorPage("No article id supplied"); // TODO improve: maybe just display all articles sorted by id?
        break;
      }

      // TODO check existence

      $page = new ArticlePage($database, $_GET["id"]);
      break;

    case "author":
      if (empty($_GET["id"])) {
        $page = new ErrorPage("No author id supplied"); // TODO improve: maybe just display all articles sorted by author?
        break;
      }

      // TODO check existence

      $page = new AuthorPage($database, $_GET["id"]);
      break;

    // TODO etc. etc.
  }

  // get the parts here so that exceptions are thrown
  $title = $page->getTitle();
  $main = $page->getMain();
}
catch(PDOException $e) {
  print_r($e);
  $page = new ErrorPage($e);

  // get the parts here so that exceptions are thrown
  $title = $page->getTitle();
  $main = $page->getMain();
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">

<title>the exceptional collection<?php print $title; ?></title>

<script type='text/x-mathjax-config'>
	MathJax.Hub.Config({
		extensions: ['tex2jax.js'],
		jax: ['input/TeX','output/HTML-CSS'],
		TeX: {
			extensions: ['AMSmath.js', 'AMSsymbols.js', 'color.js'],
			TagSide: 'left'
		},
		tex2jax: {
			inlineMath: [ ['$','$'], ["\\(","\\)"] ],
			displayMath: [ ['$$','$$'], ["\\[","\\]"] ],
			processEscapes: true
		},
		'HTML-CSS': { scale: 85 }
	});
</script>
<script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js"></script>

<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>

<script type="text/javascript">
$(document).ready(function() {
  $("table#articles").DataTable( {
    "columnDefs" : [ { "orderable": false, "targets" : 2 } ]
  });
});
</script>

<link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<link href="https://cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css" type="text/css" rel="stylesheet">
<link href="<?php print href("css/main.css"); ?>" type="text/css" rel="stylesheet">
<link href="<?php print href("css/article.css"); ?>" type="text/css" rel="stylesheet">
<link href="<?php print href("css/table.css"); ?>" type="text/css" rel="stylesheet">

</head>


<body>

<div id="header">
  <ul id="menu">
    <li><a href="<?php print href(""); ?>">home</a>
    <li><a href="<?php print href("authors"); ?>">authors</a>
    <li><a href="<?php print href("keywords"); ?>">keywords</a>
    <li><a href="<?php print href("about"); ?>">about</a>
  </ul>

  <h1><a href="<?php print href(""); ?>">the exceptional collection</a></h1>
  <p>An overview of exceptional collections and semi-orthogonal decompositions in the literature
</div>

<div id="text">
<?php print $main; ?>
</div>

</body>

</html>
