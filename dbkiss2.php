<?php
/*
	DBKiss 2.00 Beta (2012-01-07)
	Author: Czarek Tomczak [czarek.tomczak#gmail.com]
	Web site: http://www.gosu.pl/dbkiss/
	License: GPL v3
*/

define("DBKISS_VERSION", "2.00 Beta");

// ----------------------------------------------------------------
// SQLite configuration.
// ----------------------------------------------------------------

/* Option 1: require authentication to access the database. */

//define("SQLITE_FILE", "./mydatabase.sqlite");
//define("SQLITE_USER", "myuser"); // put here any username you like.
//define("SQLITE_PASSWORD", md5("mypassword")); // md5 hash here, not plain text.

/* Option 2: do not require any authentication, use it with caution. */

//define("SQLITE_FILE", "./mydatabase.sqlite");
//define("SQLITE_INSECURE", 1);

/* Option 3: define above constants in a separate file */

// Create a php file in which you define these constants and then
// include "./dbkiss.php" file. In future, when you update to a new
// version of dbkiss script, you won't have to edit dbkiss file again.

// ----------------------------------------------------------------
// SQLite optional.
// ----------------------------------------------------------------

/* This option says that for big files on tables list an estimation of records
   will be displayed, instead of doing COUNT(*), cause it could slow down application
   significantly.  Estimation means that MAX(rowid) will be used instead, which
   may be inaccurate when some records in the table has been deleted. When you enter
   the table view, then precise counting is always done. */

//define("SQLITE_ESTIMATE_COUNT", 50*1024*1024); // This is the default (50 MB).

// ----------------------------------------------------------------
// DBKISS_SQL directory.
// ----------------------------------------------------------------

// Some of the features in the SQL Editor require creating 'dbkiss_sql' directory,
// where history of queries are kept and other data. If the script has permission
// it will create that directory automatically, otherwise you need to create that
// directory manually and make it writable. You can also set it to empty '' string,
// but some of the features in the sql editor will not work (templates, pagination)

if (!defined('DBKISS_SQL_DIR')) {
	define('DBKISS_SQL_DIR', 'zzz_sql');
}

// ----------------------------------------------------------------
// Auto connection script for MySQL and PostgreSQL
// ----------------------------------------------------------------

// An example configuration script that will automatically connect to localhost database.
// This is useful on localhost if you don't want to see the "Connect" screen.

// "mysql_local.php" source below:

/*
	define('COOKIE_PREFIX', str_replace('.php', '', basename(__FILE__)).'_');
	define('DBKISS_SQL_DIR', 'dbkiss_mysql');
	$cookie = array(
		'db_driver' => 'mysql',
		'db_server' => 'localhost',
		'db_name' => 'test',
		'db_user' => 'root',
		'db_pass' => 'toor',
		'db_charset' => 'latin2',
		'page_charset' => 'iso-8859-2',
		'remember' => 1
	);
	foreach ($cookie as $k => $v) {
		if ('db_pass' == $k) { $v = base64_encode($v); }
		$k = COOKIE_PREFIX.$k;
		if (!isset($_COOKIE[$k])) {
			$_COOKIE[$k] = $v;
		}
	}
	require './dbkiss.php';
*/

// ----------------------------------------------------------------
// Popups - width and height.
// ----------------------------------------------------------------

define("SQL_POPUP_WIDTH", 800);
define("SQL_POPUP_HEIGHT", 600);

define("EDITROW_POPUP_WIDTH", 620);
define("EDITROW_POPUP_HEIGHT", 600);

// ----------------------------------------------------------------
// CHANGELOG
// ----------------------------------------------------------------

/*

2.00
* Support for SQLite file databases was added. To use it edit dbkiss.php file and add 3 constants
	at the top of the file: SQLITE_FILE, SQLITE_USER, SQLITE_PASSWORD. Sqlite requires "pdo_sqlite" extension.
* User interface has a new modern look now.
* CSRF protection using Origin header, currently only Chrome browser supports this featue - other browsers are not protected.
* Postgresql bug fixed: case sensitivity in table names and columns is now supported, identifiers are now enquoted with double quotes.
* Mysql bug fixed: column names are now enquoted with backticks, table names have already been backticked.
* Mysql enhancement: SHOW syntax is treated the same as SELECT in SQL editor
* SQL editor minor enhancements, better wrapping in data view, full_content is calling nl2br by default
* Export All: includes Views declarations now.
* Editing row: you can use natural time strings when editing fields of type INT and TIMESTAMP, TIME.
	Natural time strings are: "Yesterday 11:00", "+5 days" and many more, click "Help" to see all
	supported syntax. In fields of type INT an unix timestamp will be generated.
* Fixed a bug that broke listing in table view when colored search phrase inside an anchor tag.
* Bug fixed: when changed sorting the old offset of the paging was kept.
* Mysql and postgresql connection timeouts are set to 3 seconds so that a meaningfull error message is displayed now if timeout.
* Fixed bug: column sorting in table view was case sensitive, it now uses LOWER(column_name) in ORDER BY.
* Table names and column names are now allowed to start with numeric values ex. `62-511` or `62`

1.11
* Links in data output are now clickable. Clicking them does not reveal the location of your dbkiss script to external sites.

1.10
* Support for views in Postgresql (mysql had it already).
* Views are now displayed in a seperate listing, to the right of the tables on main page.
* Secure redirection - no referer header sent - when clicking external links (ex. powered by), so that the location of the dbkiss script on your site is not revealed.

1.09
* CSV export in sql editor and table view (feature sponsored by Patrick McGovern)

1.08
* date.timezone E_STRICT error fixed

1.07
* mysql tables with dash in the name generated errors, now all tables in mysql driver are
	enquoted with backtick.

1.06
* postgresql fix

1.05
* export of all structure and data does take into account the table name filter on the main page,
	so you can filter the tables that you want to export.

1.04
* exporting all structure/data didn't work (ob_gzhandler flush bug)
* cookies are now set using httponly option
* text editor complained about bad cr/lf in exported sql files
	(mysql create table uses \n, so insert queries need to be seperated by \n and not \r\n)

1.03
* re-created array_walk_recursive for php4 compatibility
* removed stripping slashes from displayed content
* added favicon (using base64_encode to store the icon in php code, so it is still one-file database browser)

1.02
* works with short_open_tag disabled
* code optimizations/fixes
* postgresql error fix for large tables

1.01
* fix for mysql 3.23, which doesnt understand "LIMIT x OFFSET z"

1.00
* bug fixes
* minor feature enhancements
* this release is stable and can be used in production environment

0.61
* upper casing keywords in submitted sql is disabled (it also modified quoted values)
* sql error when displaying table with 0 rows
* could not connect to database that had upper case characters

*/

// ----------------------------------------------------------------
// DBKiss internal code from now on - DO NOT EDIT.
// ----------------------------------------------------------------

ob_start('ob_gzhandler');

// ----------------------------
// @errorhandler
// ----------------------------

error_reporting(-1);
ini_set('display_errors', 1);
ini_set("html_errors", 0);
if (!ini_get('date.timezone')) {
	ini_set('date.timezone', 'Europe/Warsaw');
}
if ("127.0.0.1" == $_SERVER["SERVER_ADDR"] && "127.0.0.1" == $_SERVER["REMOTE_ADDR"]) {
	ini_set("log_errors", 1);
	ini_set("error_log", "./!phperror.log");
}

if (ini_get('register_globals')) {
	header('HTTP/1.0 503 Service Unavailable');
	exit("ERROR: register_globals is On.");
}

if (version_compare(PHP_VERSION, '4.3.0', '<')) {
	header('HTTP/1.0 503 Service Unavailable');
	exit("ERROR: old php version detected: ".PHP_VERSION.". You need at least 4.3.0.");
}

set_error_handler('errorHandler');
register_shutdown_function('errorHandler_last');
ini_set('display_errors', 1);
global $Global_LastError;

function errorHandler_last()
{
	if (function_exists("error_get_last")) {
		$error = error_get_last();
		if ($error) {
			errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}
}
function errorHandler($errno, $errstr, $errfile, $errline)
{
	global $Global_LastError;
	$Global_LastError = $errstr;

	// Check with error_reporting, if statement is preceded with @ we have to ignore it.
	if (!($errno & error_reporting())) {
		return;
	}

	// Mysql shows error using locale, but it doesn't use UTF-8 charset, some fix for that.
	$errstr = ConvertPolishToUTF8($errstr);

	// Headers.
	if (!headers_sent()) {
		header('HTTP/1.0 503 Service Unavailable');
		while (ob_get_level()) { ob_end_clean(); } // This will cancel ob_gzhandler, so later we set Content-encoding to none.
		header('Content-Encoding: none'); // Fix gzip encoding header.
		header("Content-Type: text/html; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	// Error short message.
	$errfile = basename($errfile);

	$msg = sprintf('%s<br>In %s on line %d.', nl2br($errstr), $errfile, $errline);

	// Display error.

	printf("<!doctype html><html><head><meta charset=utf-8><title>PHP Error</title>");
	printf("<meta name=\"robots\" content=\"noindex,nofollow\">");
	printf("<link rel=\"shortcut icon\" href=\"{$_SERVER['PHP_SELF']}?dbkiss_favicon=1\">");
	printf("<style type=text/css>");
	printf("body { font: 11px Tahoma; line-height: 1.4em; padding: 0; margin: 1em 1.5em; }");
	printf("h1 { font: bold 15px Tahoma; border-bottom: rgb(175, 50, 0) 1px solid; margin-bottom: 0.85em; padding-bottom: 0.25em; color: rgb(200, 50, 0); text-shadow: 1px 1px 1px #fff; }");
	print("h2 { font: bold 13px Tahoma; margin-top: 1em; color: #000; text-shadow: 1px 1px 1px #fff; }");
	printf("</style></head><body>");

	printf("<h1>PHP Error</h1>");
	printf($msg);

	if ("127.0.0.1" == $_SERVER["SERVER_ADDR"] && "127.0.0.1" == $_SERVER["REMOTE_ADDR"])
	{
		// Showing backtrace only on localhost, cause it shows full arguments passed to functions,
		// that would be a security hole to display such data, cause it could contain some sensitive
		// data fetched from tables or could even contain a database connection user and password.

		printf("<h2>Backtrace</h2>");
		ob_start();
		debug_print_backtrace();
		$trace = ob_get_clean();
		$trace = preg_replace("/^#0[\s\S]+?\n#1/", "#1", $trace); // Remove call to errorHandler() from trace.
		$trace = trim($trace);
		print nl2br($trace);
	}

	printf("</body></html>");

	// Log error to file.
	if ("127.0.0.1" == $_SERVER["SERVER_ADDR"] && "127.0.0.1" == $_SERVER["REMOTE_ADDR"]) {
		error_log($msg);
	}

	// Email error.

	exit();
}

// @erroronconnect

function ConnectError($msg)
{
	// Display an error message, use this instead of exit("text...").

	// Mysql shows error using locale, but it doesn't use UTF-8 charset, some fix for that.
	$msg = ConvertPolishToUTF8($msg);

	printf("<!doctype html><html><head><meta charset=utf-8><title>Connect Error</title>");
	printf("<meta name=\"robots\" content=\"noindex,nofollow\">");
	printf("<link rel=\"shortcut icon\" href=\"{$_SERVER['PHP_SELF']}?dbkiss_favicon=1\">");
	printf("<style type=text/css>");
	printf("body { font: 11px Tahoma; line-height: 1.4em; padding: 0em; margin: 1em 1.5em; }");
	printf("h1 { font: bold 15px Tahoma; border-bottom: rgb(175, 50, 0) 1px solid; margin-bottom: 0.85em; padding-bottom: 0.25em; color: rgb(200, 50, 0); text-shadow: 1px 1px 1px #fff; }");
	print("h2 { font: bold 13px Tahoma; margin-top: 1em; color: #000; text-shadow: 1px 1px 1px #fff; }");
	printf("</style></head><body>");

	printf("<h1>Connect Error</h1>");
	print $msg;

	exit();
}

// -----------------------------
// @debug
// -----------------------------

// You can access this function only on localhost.

if ("127.0.0.1" == $_SERVER["SERVER_ADDR"] && "127.0.0.1" == $_SERVER["REMOTE_ADDR"])
{
	function dump($data)
	{
		// @dump

		if (!headers_sent()) {
			header('HTTP/1.0 503 Service Unavailable');
			while (ob_get_level()) { ob_end_clean(); } // This will cancel ob_gzhandler, so later we set Content-encoding to none.
			header('Content-encoding: none'); // Fix gzip encoding header.
			header("Content-type: text/html");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
		}

		if (func_num_args() > 1) { $data = func_get_args(); }

		if ($data && count($data) == 2 && isset($data[1]) && "windows-1250" == strtolower($data[1])) {
			$charset = "windows-1250";
			$data = $data[0];
		} else if ($data && count($data) == 2 && isset($data[1]) && "iso-8859-2" == strtolower($data[1])) {
			$charset = "iso-8859-2";
			$data = $data[0];
		} else {
			$charset = "utf-8";
		}

		printf('<!doctype html><head><meta charset='.$charset.'><title>dump()</title></head><body>');
		printf('<h1 style="color: rgb(150,15,225);">dump()</h1>');
		ob_start();
		print_r($data);
		$html = ob_get_clean();
		$html = htmlspecialchars($html);
		printf('<pre>%s</pre>', $html);
		printf('</body></html>');
		exit();
	}
}

// --------------------
// @CSRF protection.
// --------------------

// Currenly only Chrome supports Origin header.

if ("POST" == $_SERVER["REQUEST_METHOD"]) {
	if (isset($_SERVER["HTTP_ORIGIN"])) {
		$origin = $_SERVER["HTTP_ORIGIN"];
		$origin = str_replace("https://", "http://", $origin);
		$address = "http://".$_SERVER["SERVER_NAME"];
		if ($_SERVER["SERVER_PORT"] != 80) {
			$address .= ":".$_SERVER["SERVER_PORT"];
		}
		if (strpos($origin, $address) !== 0) {
			trigger_error("CSRF protection in POST request: detected invalid Origin header: ".$_SERVER["HTTP_ORIGIN"],
				E_USER_ERROR);
			exit();
		}
	}
}

// ----------------------------
// @gpc
// ----------------------------

function GET($key, $type)
{
	// GET("export", "string")
	// is an equivalent of:
	// $_GET["export"] = isset($_GET["export"]) ? (string) $_GET["export"] : "";

	if ("string" == $type) {
		$_GET[$key] = isset($_GET[$key]) ? (string) $_GET[$key] : "";
	} else if ("int" == $type) {
		$_GET[$key] = isset($_GET[$key]) ? (int) $_GET[$key] : 0;
	} else if ("bool" == $type) {
		$_GET[$key] = isset($_GET[$key]) ? (bool) $_GET[$key] : false;
	} else if ("array" == $type) {
		$_GET[$key] = isset($_GET[$key]) ? (array) $_GET[$key] : array();
	} else {
		trigger_error("GET() failed: key=$key, type=$type", E_USER_ERROR);
	}
	return $_GET[$key];
}
function POST($key, $type)
{
	// POST("export", "string")
	// is an equivalent of:
	// $_POST["export"] = isset($_POST["export"]) ? (string) $_POST["export"] : "";

	if ("string" == $type) {
		$_POST[$key] = isset($_POST[$key]) ? (string) $_POST[$key] : "";
	} else if ("int" == $type) {
		$_POST[$key] = isset($_POST[$key]) ? (int) $_POST[$key] : 0;
	} else if ("bool" == $type) {
		$_POST[$key] = isset($_POST[$key]) ? (bool) $_POST[$key] : false;
	} else if ("array" == $type) {
		$_POST[$key] = isset($_POST[$key]) ? (array) $_POST[$key] : array();
	} else {
		trigger_error("POST() failed: key=$key, type=$type", E_USER_ERROR);
	}
	return $_POST[$key];
}

// ----------------------------
// @favicon
// ----------------------------

GET("dbkiss_favicon", "bool");

if ($_GET['dbkiss_favicon'])
{
	if (defined("SQLITE_FILE")) {
		$favicon = 'AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAzAMztMwDM7TMAzO0zAMztMwDM7TIAye82BsT3NgjC+joKv/47C7//Owu//zoKv/42CML6jJkAj4yZACAAAAAAMwDM7TMAzO0zAMztMwDM7TYGx/U7C7//PxDC/6CH3v//////QxHC/0QSxP9CEsf/QRLE/9nQMP+bpgX/jJkAcDMAzO3//////////5+H6PY7C7//oIjj////////////QxG//////////////////0ISx///7Ur/+usb/4yZAP8zAMztMwDM7TMAzO3/////Owu///////9DEcL//////0MRv///////RBLE/0ISx/9CEsf//+1K///wHP+MmQD/MwDM7TMAzO3/////MwDM7TsLv///////QxHC//////9FEb///////0URv/9FEb//RRG////YKf//7Bf/jJkA/zMAzO3/////MwDM7TMAzO07C7///////0ANv///////Owu///////87C7//Owu//z0Nv//HswD/8r4C/6OmAP8zAMztn4fo9v//////////Owu//5uF3v//////oIfe/0MRv///////RBLE/0ISx/9AEMT/yccw/5ahBf+rqAD/MwDM7TMAzO0zAMztMwDM7TsLv/9CEsf/QxHC/0MRv/9DEb//QxHC/0QSxP9CEsf/QhLH/+bYSf/Rzhv/jJkA/wAAAAAAAAAAAAAAAAAAAACMmQD//+eF///tTP//7xT//+8U///rO///6mP//+iM//XgeP/m2En/1tMe/4yZAP8AAAAAAAAAAAAAAAAAAAAAjJkA///nhf//7Uz//+8U///vFP//6zv//+pj///ojP/14Hj/5thJ/9bTHv+MmQD/AAAAAAAAAAAAAAAAAAAAAIyZAP/65HX/29Uf/7/CBP+lrQD/mKMA/4yZAP+MmQD/maMP/7K1Gf/PzBn/jJkA/wAAAAAAAAAAAAAAAAAAAACMmQD/1tYp/+vpmP/38L7///DC///pqP//5pn//+aZ//Lchf/ZzV//oagU/4yZAP8AAAAAAAAAAAAAAAAAAAAAjJkA//z88P///PL///XZ///wwv//6aj//+aZ///mmf//5pn//+aZ//rjjv+MmQD/AAAAAAAAAAAAAAAAAAAAAIyZAO/4+vD///zy///12f//8ML//+mo///mmf//5pn//+aZ///mmf/6447/jJkA/wAAAAAAAAAAAAAAAAAAAACMmQBgoqsn/9fZmP/68cD///DC///pqP//5pn//+aZ//fgjf/ZzV//o6sW/4yZAGAAAAAAAAAAAAAAAAAAAAAAAAAAAIyZACCMmQCPjJkAv4yZAP+MmQD/jJkA/4yZAP+MmQC/jJkAj4yZACAAAAAAAAGsQQAArEEAAKxBAACsQQAArEEAAKxBAACsQQAArEHwAKxB8ACsQfAArEHwAKxB8ACsQfAArEHwAKxB+AGsQQ==';
	} else {
		$favicon = 'AAABAAIAEBAAAAEACABoBQAAJgAAABAQAAABACAAaAQAAI4FAAAoAAAAEAAAACAAAAABAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wDQcRIAAGaZAL5mCwCZ//8Av24SAMVwEgCa//8AvmcLAKn//wAV0/8Awf//AErL5QDGcBIAvnESAHCpxgDf7PIA37aIAMNpDQDHcRIAZO7/AErl/wAdrNYAYMbZAI/1+QDouYkAO+D/AIT4/wDHcBIAjPr/AMJvEgDa//8AQIyzAMNvEgCfxdkA8v//AEzl/wB46fQAMLbZACms1gAAeaYAGou1AJfX6gAYo84AHrLbAN+zhgCXxtkAv/P5AI30+ADv9fkAFH2pABja/wDGaw4AwXASAAVwoQDjuIkAzXARADCmyQAAe64Ade35AMBxEgC+aQ0AAKnGACnw/wAngqwAxW8RABBwnwAAg6wAxW4QAL7w9wCG7PIAHKnSAMFsDwC/ZwwADnWkAASQwgAd1v8Aj7zSAMZvEQDv+fwABXSmABZ+qgAC6fIAAG+iAMhsDwAcz/kAvmsOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAICAgICOTUTCQQECRMQEQACAgICVUpJEgEfBxRCJ1FOAgEBGgQ4AQEGAQEBDhZWAwICAgEEASIBBgEHFA4WTQMCAgECBAE2AQ8BDw89QDQDAgECAgQBVwEJAQQJPj9TKQIaAQEELgESBgEHHUU6N0QCAgICBA4iBgYfBx1PDUgDAAAAAAMcJQsLGxUeJg0XAwAAAAADHCULCxsVHiYNFwMAAAAAAzwtTDtUAwNLKiwDAAAAAAMoK0YMCggFRxgzAwAAAAADUCQgDAoIBQUFGQMAAAAAQzIkIAwKCAUFBRkDAAAAACNBLzAMCggFMRhSIwAAAAAAERAhAwMDAyEQEQAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPAAAADwAAAA8AAAAPAAAADwAAAA8AAAAPAAAAD4AQAAKAAAABAAAAAgAAAAAQAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMxmAO3MZgDtzGYA7cxmAO3MZgDtymYB78RmBvfCZgj6vmYK/r5mC/++Zgv/vmYK/sJmCPoAZpmPAGaZIAAAAADMZgDtzGYA7cxmAO3MZgDtxmYF9b9nDP/BbA//37aI///////CbxL/xXAS/8dxEv/FbxH/MLbZ/wV0pv8AZplwzGYA7f//////////57aF9r5mC//juIn///////////+/bhL/////////////////xnAS/0rl//8cz/n/AGaZ/8xmAO3MZgDtzGYA7f////++Zgv//////8NvEv//////v24S///////FcBL/x3ES/8ZwEv9K5f//Hdb//wBmmf/MZgDtzGYA7f/////MZgDtvmYL///////BcBL//////75xEv//////vnES/75xEv/AcRL/KfD//xja//8AZpn/zGYA7f/////MZgDtzGYA7b5mC///////vmsO//////++Zwv//////75mC/++Zwv/vmkN/wCpxv8C6fL/AHmm/8xmAO3ntoX2//////////++Zgv/37OG///////ftoj/v24S///////FcBL/x3AS/8VuEP8wpsn/BXCh/wCDrP/MZgDtzGYA7cxmAO3MZgDtvmYL/8ZwEv/DbxL/v24S/79uEv/CbxL/xXAS/8dwEv/GbxH/Ssvl/xyp0v8AZpn/AAAAAAAAAAAAAAAAAAAAAABmmf+E+P//TOX//xXT//8V0///O+D//2Tu//+M+v//eOn0/0rL5f8drNb/AGaZ/wAAAAAAAAAAAAAAAAAAAAAAZpn/hPj//0zl//8V0///FdP//zvg//9k7v//jPr//3jp9P9Ky+X/HazW/wBmmf8AAAAAAAAAAAAAAAAAAAAAAGaZ/3Xt+f8estv/BJDC/wB7rv8Ab6L/AGaZ/wBmmf8OdaT/Gou1/xijzv8AZpn/AAAAAAAAAAAAAAAAAAAAAABmmf8prNb/l9fq/77w9//B////qf///5r///+Z////huzy/2DG2f8Ufan/AGaZ/wAAAAAAAAAAAAAAAAAAAAAAZpn/7/n8//L////a////wf///6n///+a////mf///5n///+Z////j/X5/wBmmf8AAAAAAAAAAAAAAAAAAAAAAGaZ7+/1+f/y////2v///8H///+p////mv///5n///+Z////mf///4/1+f8AZpn/AAAAAAAAAAAAAAAAAAAAAABmmWAngqz/l8bZ/7/z+f/B////qf///5r///+Z////jfT4/2DG2f8Wfqr/AGaZYAAAAAAAAAAAAAAAAAAAAAAAAAAAAGaZIABmmY8AZpm/AGaZ/wBmmf8AZpn/AGaZ/wBmmb8AZpmPAGaZIAAAAAAAAQICAAA1EwAABAkAABEAAAACAgAASRIAAAcUAABRTvAAARrwAAEB8AABAfAAVgPwAAIB8AAiAfAABxT4AU0D';
	}
	header('Content-type: image/vnd.microsoft.icon');
	echo base64_decode($favicon);
	exit();
}

// ----------------------------
// @sqlite CHECKS
// ----------------------------

// Do all the initial checks for the sqlite database.

SQLite_DoChecks();

function SQLite_DoChecks()
{
	if (defined("SQLITE_FILE") || defined("SQLITE_USER") || defined("SQLITE_PASSWORD") || defined("SQLITE_INSECURE"))
	{
		// Verify all required constants are defined.

		if (!defined("SQLITE_FILE")) {
			trigger_error("SQLITE_FILE is not defined.", E_USER_ERROR);
		}

		if (defined("SQLITE_INSECURE"))
		{
			if (defined("SQLITE_USER") || defined("SQLITE_PASSWORD")) {
				trigger_error("SQLITE_INSECURE is defined, but SQLITE_USER and SQLITE_PASSWORD have also been defined - that is not allowed.", E_USER_ERROR);
			}
			if (!SQLITE_INSECURE) {
				trigger_error("SQLITE_INSECURE has been set to 0 - that is an invalid use of this constant, you can only set it to 1 or do not define it at all.", E_USER_ERROR);
			}
		}
		else
		{
			if (!defined("SQLITE_USER")) {
				trigger_error("SQLITE_USER is not defined.", E_USER_ERROR);
			}
			if (!defined("SQLITE_PASSWORD")) {
				trigger_error("SQLITE_PASSWORD is not defined.", E_USER_ERROR);
			}
		}

		// Check whether the PDO and PDO_SQLITE extensions are loaded.

		if (!extension_loaded("pdo")) {
			trigger_error("PDO extension is not loaded", E_USER_ERROR);
		}
		if (!extension_loaded("pdo_sqlite")) {
			trigger_error("PDO_SQLITE extension is not loaded.", E_USER_ERROR);
		}

		// User and password cannot use the default values provided in the example.

		if (defined("SQLITE_INSECURE"))
		{
			// No authentication required.
			// No check required here.
		}
		else
		{
			if (!trim(SQLITE_USER)) {
				trigger_error("SQLITE_USER cannot be empty.", E_USER_ERROR);
			}

			if (SQLITE_USER == "myuser") {
				trigger_error("SQLITE: you cannot use the default username `myuser`, change it to some other name.", E_USER_ERROR);
			}
			if (SQLITE_PASSWORD == "34819d7beeabb9260a5c854bc85b3e44") {
				trigger_error("SQLITE: you cannot use the default md5 hash of a password that was provided with the example.", E_USER_ERROR);
			}
			if (strlen(SQLITE_PASSWORD) != 32) {
				trigger_error("SQLITE: the length of md5 hash of a password defined in SQLITE_PASSWORD is not 32 chars, this is not a valid m5 hash.", E_USER_ERROR);
			}
		}

		// Whether database file exists and is readable/writable.
		// The file does not have to exist - if it does not exist sqlite will create a new database.

		if (is_dir(SQLITE_FILE)) {
			trigger_error("SQLITE_FILE is a directory.", E_USER_ERROR);
		}

		if (file_exists(SQLITE_FILE))
		{
			if (!is_readable(SQLITE_FILE)) {
				trigger_error("SQLITE_FILE is not readable.", E_USER_ERROR);
			}
			if (!is_writable(SQLITE_FILE)) {
				trigger_error("SQLITE_FILE is not writable.", E_USER_ERROR);
			}
		}

		// The directory containing the file must be writable.
		if (!is_writable(dirname(SQLITE_FILE))) {
			trigger_error("SQLITE_FILE directory must be writable.", E_USER_ERROR);
		}

		// Optional constants, default values.

		if (!defined("SQLITE_ESTIMATE_COUNT")) {
			define("SQLITE_ESTIMATE_COUNT", 50*1024*1024);
		}

		// A constant for sqlite-enabled detection used later in the script.

		define("SQLITE_USED", 1);
	}
	else
	{
		define("SQLITE_USED", 0);
	}
}

// --------------------------
// @pdo
// --------------------------

// Remember about slow INSERTs in SQLite when not in transaction.

function PDO_Connect($dsn, $user="", $password="")
{
	global $PDO;
	$PDO = new PDO($dsn, $user, $password);
	$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
}
function PDO_FetchOne($query, $params=null)
{
	global $PDO;
	if (isset($params)) {
		$stmt = $PDO->prepare($query);
		$stmt->execute($params);
	} else {
		$stmt = $PDO->query($query);
	}
	$row = $stmt->fetch(PDO::FETCH_NUM);
	if ($row) {
		return $row[0];
	} else {
		return false;
	}
}
function PDO_FetchRow($query, $params=null)
{
	global $PDO;
	if (isset($params)) {
		$stmt = $PDO->prepare($query);
		$stmt->execute($params);
	} else {
		$stmt = $PDO->query($query);
	}
	return $stmt->fetch(PDO::FETCH_ASSOC);
}
function PDO_FetchAll($query, $params=null)
{
	global $PDO;
	if (isset($params)) {
		$stmt = $PDO->prepare($query);
		$stmt->execute($params);
	} else {
		$stmt = $PDO->query($query);
	}
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function PDO_FetchAssoc($query, $params=null)
{
	global $PDO;
	if (isset($params)) {
		$stmt = $PDO->prepare($query);
		$stmt->execute($params);
	} else {
		$stmt = $PDO->query($query);
	}
	$rows = $stmt->fetchAll(PDO::FETCH_NUM);
	$assoc = array();
	$columns = null;
	foreach ($rows as $row) {
		if (!isset($columns)) {
			$columns = count($row);
		}
		if (1 == $columns) { // When 1 column that is not really an assoc, the array keys are numeric.
			$assoc[] = $row[0];
		} else if (2 == $columns) {
			$assoc[$row[0]] = $row[1];
		} else {
			$assoc[$row[0]] = $row;
		}
	}
	return $assoc;
}
function PDO_Execute($query, $params=null)
{
	global $PDO;
	if (isset($params)) {
		$stmt = $PDO->prepare($query);
		$stmt->execute($params);
	} else {
		$PDO->query($query);
	}
}
function PDO_InsertId()
{
	global $PDO;
	return $PDO->lastInsertId();
}

// --------------------------
// @authenticate SQLITE
// --------------------------

if (SQLITE_USED)
{
	if (defined("SQLITE_INSECURE") && SQLITE_INSECURE)
	{
		// Do not require any authentication.
		assert(!defined("SQLITE_USER") && !defined("SQLITE_PASSWORD"));
	}
	else
	{
		assert(!defined("SQLITE_INSECURE"));

		define("HT_USER", SQLITE_USER);
		define("HT_PASSWORD", SQLITE_PASSWORD);

		define("HT_PATH", "");
		define("HT_DOMAIN", "");

		define("HT_PREFIX", "");
		define("HT_SECURE", false);

		ht_controller();
	}
}

function ht_controller()
{
	if (isset($_GET['ht_logout']) && $_GET['ht_logout']) {
		ht_logout();
	}
	if (ht_authorize()) {
		return 1;
	}
	ht_authenticate();
	exit();
}
function ht_user()
{
	// To display username on site.

	if (isset($_COOKIE['ht_user']) && HT_USER == $_COOKIE['ht_user']) {
		return $_COOKIE['ht_user'];
	}
}
function ht_authorize()
{
	$c_user = isset($_COOKIE['ht_user']) ? $_COOKIE['ht_user'] : null;
	$c_password = isset($_COOKIE['ht_password']) ? $_COOKIE['ht_password'] : null;

	if (HT_USER == $c_user && HT_PASSWORD == $c_password) {
		return 1;
	} else {
		return 0;
	}
}
function ht_logout()
{
	$time = time() - 3600*48;
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
		setcookie(HT_PREFIX.'ht_user', '', $time, HT_PATH, HT_DOMAIN, HT_SECURE, true);
		setcookie(HT_PREFIX.'ht_password', '', $time, HT_PATH, HT_DOMAIN, HT_SECURE, true);
	} else {
		setcookie(HT_PREFIX.'ht_user', '', $time, HT_PATH, HT_DOMAIN, HT_SECURE);
		setcookie(HT_PREFIX.'ht_password', '', $time, HT_PATH, HT_DOMAIN, HT_SECURE);
	}
	unset($_COOKIE['ht_user']);
	unset($_COOKIE['ht_password']);

	$loc = $_SERVER['REQUEST_URI'];
	$loc = preg_replace('#[\?\&]ht\_logout=1#', '', $loc);
	if (isset($_SERVER['HTTP_REFERER'])) {
		$loc = $_SERVER['HTTP_REFERER'];
	}
	header('Location: '.$loc);

	exit();
}
function ht_authenticate()
{
	$p_user = isset($_POST['ht_user']) ? $_POST['ht_user'] : null;
	$p_password = isset($_POST['ht_password']) ? $_POST['ht_password'] : null;
	$p_remember = isset($_POST['ht_remember']) ? (bool) $_POST['ht_remember'] : null;
	$p_referer = isset($_POST['ht_referer']) ? $_POST['ht_referer'] : null;

	if ('POST' == $_SERVER['REQUEST_METHOD']) {
		if (strtolower($p_user) == strtolower(HT_USER) && md5($p_password) == HT_PASSWORD) {
			$time = 0;
			if ($p_remember) {
				$time = time() + 3600*24*14;
			}
			if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
				setcookie(HT_PREFIX.'ht_user', HT_USER, $time, HT_PATH, HT_DOMAIN, HT_SECURE, true);
				setcookie(HT_PREFIX.'ht_password', md5($p_password), $time, HT_PATH, HT_DOMAIN, HT_SECURE, true);
			} else {
				setcookie(HT_PREFIX.'ht_user', HT_USER, $time, HT_PATH, HT_DOMAIN, HT_SECURE);
				setcookie(HT_PREFIX.'ht_password', md5($p_password), $time, HT_PATH, HT_DOMAIN, HT_SECURE);
			}
			header('Location: '.$p_referer);
			exit();
		}
	} else {
		$p_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		if (!$p_referer) {
			$p_referer = $_SERVER['REQUEST_URI'];
		}

	}

	ht_loginform(array(
		"p_referer" => $p_referer,
		"p_user" => $p_user,
		"p_remember" => $p_remember
	));

	exit();
}
function ht_loginform($vars)
{
	extract($vars, EXTR_SKIP);
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="robots" content="noindex, nofollow">
		<title>SQLite authentication</title>
		<link rel="shortcut icon" href="<?php echo $_SERVER['PHP_SELF']; ?>?dbkiss_favicon=1">
		<meta name="robots" content="noindex,nofollow">
		<style type="text/css">
		body {
			font: 11px Tahoma;
			line-height: 1.4em;
		}
		input {
			font: 11px Tahoma;
		}
		body {
			margin: 1em 1.5em;
			padding: 0em;
		}
		h1 {
			font: bold 15px Tahoma;
			text-shadow: 1px 1px 1px #fff;
			color: #000;
			margin-bottom: 0.85em;
			border-bottom: #999 1px solid;
			padding-bottom: 0.25em;
		}
		div.submit {
			margin-top: 1em;
		}
		div.error {
			margin: 1em 0em;
			color: rgb(225,0,0);
		}
		div.remember {
			margin-top: 0.5em;
		}
		</style>
	</head>
	<body>

	<h1>SQLite authentication</h1>

	<?php if ('POST' == $_SERVER['REQUEST_METHOD']): ?>
	<div class="error">
		Invalid credentials provided. Try again.
	</div>
	<?php endif; ?>

	<form action="<?php echo strip_tags($_SERVER['REQUEST_URI']); ?>" method="post">
	<input type="hidden" name="ht_referer" value="<?php echo htmlspecialchars($p_referer); ?>">
		<label>User:</label>
		<div><input type="text" name="ht_user" id="ht_user" value="<?php echo htmlspecialchars($p_user); ?>"></div>
		<label>Password:</label>
		<div><input type="password" name="ht_password" id="ht_password" value=""></div>
		<div class="remember">
			<input type="checkbox" name="ht_remember" id="ht_remember"
				value="1" <?php if ($p_remember): ?>checked="checked"<?php endif; ?>>
			<label for="ht_remember">remember me (2 weeks)</label>
		</div>
		<div class="submit">
			<input class="button" type="submit" value="Log in">
		</div>
	</form>

	<script>
	window.onload = function()
	{
		var user = document.getElementById('ht_user');
		var password = document.getElementById('ht_password');
		if (user.value.length) {
			password.focus();
		} else {
			user.focus();
		}
	}
	</script>

	</body>
	</html>

<?php
}

// ------------------------------
// @html
// ------------------------------

function AttributeValue($value)
{
	// Html attribute's value cannot contain double quotes and sometimes
	// even single quotes, so we remove them both.
	return str_replace(array("\"", "'"), array("", ""), $value);
}
if (!function_exists('array_walk_recursive'))
{
	function array_walk_recursive(&$array, $func)
	{
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				array_walk_recursive($array[$k], $func);
			} else {
				$func($array[$k], $k);
			}
		}
	}
}
function create_links($text)
{
	// Protocols: http, https, ftp, irc, svn
	// Parse emails also?

	$text = preg_replace('#([a-z]+://[a-zA-Z0-9\.\,\;\:\[\]\{\}\-\_\+\=\!\@\#\%\&\(\)\/\?\`\~]+)#e', 'create_links_eval("\\1")', $text);

	// Excaptions:

	// 1) cut last char if link ends with ":" or ";" or "." or "," - cause in 99% cases that char doesnt belong to the link
	// (check if previous char was "=" then let it stay cause that could be some variable in a query, some kind of separator)
	// (should we add also "-" ? But it is a valid char in links and very common, many links might end with it when creating from some title of an article?)

	// 2) brackets, the link could be inside one of 3 types of brackets:
	// [http://...] , {http://...}
	// and most common: (http://some.com/) OR http://some.com(some description of the link)
	// In these cases regular expression will catch: "http://some.com/)" AND "http://some.com(some"
	// So when we catch some kind of bracket in the link we will cut it unless there is also a closing bracket in the link:
	// We will not cut brackets in this link: http://en.wikipedia.org/wiki/Common_(entertainer) - wikipedia often uses brackets.

	return $text;
}
function create_links_eval($link)
{
	$orig_link = $link;
	$cutted = "";

	if (in_array($link[strlen($link)-1], array(":", ";", ".", ","))) {
		$link = substr($link, 0, -1);
		$cutted = $orig_link[strlen($orig_link)-1];
	}

	if (($pos = strpos($link, "(")) !== false) {
		if (strpos($link, ")") === false) {
			$link = substr($link, 0, $pos);
			$cutted = substr($orig_link, $pos);
		}
	} else if (($pos = strpos($link, ")")) !== false) {
		if (strpos($link, "(") === false) {
			$link = substr($link, 0, $pos);
			$cutted = substr($orig_link, $pos);
		}
	} else if (($pos = strpos($link, "[")) !== false) {
		if (strpos($link, "]") === false) {
			$link = substr($link, 0, $pos);
			$cutted = substr($orig_link, $pos);
		}
	} else if (($pos = strpos($link, "]")) !== false) {
		if (strpos($link, "[") === false) {
			$link = substr($link, 0, $pos);
			$cutted = substr($orig_link, $pos);
		}
	} else if (($pos = strpos($link, "{")) !== false) {
		if (strpos($link, "}") === false) {
			$link = substr($link, 0, $pos);
			$cutted = substr($orig_link, $pos);
		}
	} else if (($pos = strpos($link, "}")) !== false) {
		if (strpos($link, "{") === false) {
			$link = substr($link, 0, $pos);
			$cutted = substr($orig_link, $pos);
		}
	}
	return "<a title=\"$link\" style=\"color: #000; text-decoration: none; border-bottom: #000 1px dotted;\" href=\"javascript:;\" onclick=\"link_noreferer('$link')\">$link</a>$cutted";
}
function truncate_html($string, $length, $break_words = false, $end_str = '..')
{
	// Does not break html tags whilte truncating, does not take into account chars inside tags: <b>a</b> = 1 char length.
	// Break words is always TRUE - no breaking is not implemented.

	// Limits: no handling of <script> tags.

	$inside_tag = false;
	$inside_amp = 0;
	$finished = false; // finished but the loop is still running cause inside tag or amp.
	$opened = 0;

	$string_len = strlen($string);

	$count = 0;
	$ret = "";

	for ($i = 0; $i < $string_len; $i++)
	{
		$char = $string[$i];
		$nextchar = isset($string[$i+1]) ? $string[$i+1] : null;

		if ('<' == $char && ('/' == $nextchar || ctype_alpha($nextchar))) {
			if ('/' == $nextchar) {
				$opened--;
			} else {
				$opened++;
			}
			$inside_tag = true;
		}
		if ('>' == $char) {
			$inside_tag = false;
			$ret .= $char;
			continue;
		}
		if ($inside_tag) {
			$ret .= $char;
			continue;
		}

		if (!$finished)
		{
			if ('&' == $char) {
				$inside_amp = 1;
				$ret .= $char;
				continue;
			}
			if (';' == $char && $inside_amp) {
				$inside_amp = 0;
				$count++;
				$ret .= $char;
				continue;
			}
			if ($inside_amp) {
				$inside_amp++;
				$ret .= $char;
				if ('#' == $char || ctype_alnum($char)) {
					if ($inside_amp > 7) {
						$count += $inside_amp;
						$inside_amp = 0;
					}
				} else {
					$count += $inside_amp;
					$inside_amp = 0;
				}
				continue;
			}
		}

		$count++;

		if (!$finished) {
			$ret .= $char;
		}

		if ($count >= $length) {
			if (!$inside_tag && !$inside_amp) {
				if (!$finished) {
					$ret .= $end_str;
					$finished = true;
					if (0 == $opened) {
						break;
					}
				}
				if (0 == $opened) {
					break;
				}
			}
		}
	}
	return $ret;
}
function html_spaces($string)
{
	$inside_tag = false;
	for ($i = 0; $i < strlen($string); $i++)
	{
		$c = $string{$i};
		if ('<' == $c) {
			$inside_tag = true;
		}
		if ('>' == $c) {
			$inside_tag = false;
		}
		if (' ' == $c && !$inside_tag) {
			$string = substr($string, 0, $i).'&nbsp;'.substr($string, $i+1);
			$i += strlen('&nbsp;')-1;
		}
	}
	return $string;
}
function html_once($s)
{
	$s = str_replace(array('&lt;','&gt;','&amp;lt;','&amp;gt;'),array('<','>','&lt;','&gt;'),$s);
	return str_replace(array('&lt;','&gt;','<','>'),array('&amp;lt;','&amp;gt;','&lt;','&gt;'),$s);
}
function str_truncate($string, $length, $etc = ' ..', $break_words = true)
{
	if ($length == 0) {
		return '';
	}
	if (strlen($string) > $length + strlen($etc)) {
		if (!$break_words) {
			$string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
		}
		return substr($string, 0, $length) . $etc;
	}
	return $string;
}
function options($options, $selected = null, $ignore_type = false)
{
	$ret = '';
	foreach ($options as $k => $v) {
		//str_replace('"', '\"', $k)
		$ret .= '<option value="'.$k.'"';
		if ((is_array($selected) && in_array($k, $selected)) || (!is_array($selected) && $k == $selected && $selected !== '' && $selected !== null)) {
			if ($ignore_type) {
				$ret .= ' selected="selected"';
			} else {
				if (!(is_numeric($k) xor is_numeric($selected))) {
					$ret .= ' selected="selected"';
				}
			}
		}
		$ret .= '>'.$v.' </option>';
	}
	return $ret;
}
function checked($bool)
{
	if ($bool) return 'checked="checked"';
}
function radio_assoc($checked, $assoc, $input_name, $link = false)
{
	$ret = '<table cellspacing="0" cellpadding="0"><tr>';
	foreach ($assoc as $id => $name)
	{
		$params = array(
			'id' => $id,
			'name' => $name,
			'checked' => checked($checked == $id),
			'input_name' => $input_name
		);
		if ($link) {
			if (is_array($link)) {
				$params['link'] = $link[$id];
			} else {
				$params['link'] = sprintf($link, $id, $name);
			}
			$ret .= sprintf('<td><input class="checkbox" type="radio" name="%s" id="%s_%s" value="%s" %s></td><td>%s&nbsp;</td>', $params["input_name"], $params["input_name"], $params["id"], $params["id"], $params["checked"], $params["link"]);
		} else {
			$ret .= sprintf('<td><input class="checkbox" type="radio" name="%s" id="%s_%s" value="%s" %s></td><td><label for="%s_%s">%s</label>&nbsp;</td>', $params["input_name"], $params["input_name"], $params["id"], $params["id"], $params["checked"], $params["input_name"], $params["id"], $params["name"]);
		}
	}
	$ret .= '</tr></table>';
	return $ret;
}
function str_wrap($s, $width, $break = ' ', $omit_tags = false)
{
	//$restart = array(' ', "\t", "\r", "\n");

	if (!isset($_GET["full_content"]) || !isset($_POST["content"])) {
		GET("full_content", "bool");
		POST("full_content", "bool");
	}

	$full_content = ($_GET["full_content"] || $_POST["full_content"]);

	if ($full_content) {
		// If full_content then after wrapping, nl2br() will be called.
		$restart = array("\r", "\n");
	} else {
		$restart = array();
	}
	$cnt = 0;
	$ret = '';
	$open_tag = false;
	$inside_link = false;

	$A = ord("A");
	$Z = ord("Z");

	for ($i=0; $i<strlen($s); $i++)
	{
		$char = $s[$i];
		$nextchar = isset($s[$i+1]) ? $s[$i+1] : null;
		$nextchar2 = isset($s[$i+2]) ? $s[$i+2] : null;

		if ($omit_tags)
		{
			if ($char == '<') {
				$open_tag = true;
				if ('a' == $nextchar) {
					$inside_link = true;
				} else if ('/' == $nextchar && 'a' == $nextchar2) {
					$inside_link = false;
				}
			}
			if ($char == '>') {
				$open_tag = false;
			}
			if ($open_tag) {
				$ret .= $char;
				continue;
			}
		}

		if (in_array($char, $restart)) {
			$cnt = 0;
		} else {
			$char_ord = ord($char);
			if ($char_ord >= $A && $char_ord <= $Z) {
				// uppercase chars are counted as 1.5
				$cnt += 1.5;
			} else {
				// lowercase chars are counted as 1
				$cnt++;
			}
		}
		$ret .= $char;
		if ($cnt > $width) {
			if (1 || !$inside_link) {
				// Inside link, do not break it.
				$ret .= $break;
				$cnt = 0;
			}
		}
	}
	return $ret;
}
function ColorSearchPhrase($html, $search)
{
	// Do not replace text inside tags. For example searrching for "html":
	// <a href="test.html">Html file</a>
	// "test.html" should not be changed in that example.

	// We search the string for tags, then replace each of the tag
	// with a special unique string, then we replace

	if (strstr($html, "<")) {

		preg_match_all("#<[^<>]+>#", $html, $matches);
		$id = 0;
		$uniqueArray = array();

		// First we hide all tags by replacing with some unique string.
		foreach ($matches[0] as $tag) {
			$id++;
			$uniqueString = "@@@@@$id@@@@@";
			$uniqueArray[$uniqueString] = $tag;
			$html = str_replace($tag, $uniqueString, $html);
		}

		// Than we color the search phrase.
		$search = preg_quote($search);
		$html = preg_replace('#('.$search.')#i', '<span style="background: #ffff96;">$1</span>', $html);

		// We get back all the tags by replacing unique strings with their corresponding tag.
		foreach ($uniqueArray as $uniqueString => $tag) {
			$html = str_replace($uniqueString, $tag, $html);
		}

		return $html;

	} else {
		$search = preg_quote($search);
		$html = preg_replace('#('.$search.')#i', '<span style="background: #ffff96;">$1</span>', $html);
		return $html;
	}
}

// ~~ @htmlend

// --------------------------------
// @filter
// --------------------------------

function table_filter($tables, $filter)
{
	$filter = trim($filter);
	if ($filter) {
		foreach ($tables as $k => $table) {
			if (!stristr($table, $filter)) {
				unset($tables[$k]);
			}
		}
	}
	return $tables;
}

// -------------------------------
// @dbquotes
// -------------------------------

if (ini_get('magic_quotes_gpc')) {
	ini_set('magic_quotes_runtime', 0);
	array_walk_recursive($_GET, 'db_magic_quotes_gpc');
	array_walk_recursive($_POST, 'db_magic_quotes_gpc');
	array_walk_recursive($_COOKIE, 'db_magic_quotes_gpc');
}
function db_magic_quotes_gpc(&$val)
{
	$val = stripslashes($val);
}

// -------------------------------
// @sqleditor SIZE
// -------------------------------

$sql_font = 'font: 12px Courier New;';
$sql_area = $sql_font.' width: 708px; height: 179px; border: #ccc 1px solid; background: #f9f9f9; padding: 4px 6px; ';
$sql_area .= 'border-radius: 4px; box-shadow: 1px 1px 2px #ddd; margin-bottom: 6px;';

// -------------------------------
// @db_name STYLE
// -------------------------------

if (!isset($db_name_style)) {
	$db_name_style = '';
}
if (!isset($db_name_h1)) {
	$db_name_h1 = '';
}

// ------------------------------
// @cookies
// ------------------------------

if (!defined('COOKIE_PREFIX')) {
	define('COOKIE_PREFIX', 'dbkiss_');
}

define('COOKIE_WEEK', 604800); // 3600*24*7
define('COOKIE_SESS', 0);

function cookie_get($key)
{
	$key = COOKIE_PREFIX.$key;
	if (isset($_COOKIE[$key])) return $_COOKIE[$key];
	return null;
}
function cookie_set($key, $val, $time = COOKIE_SESS)
{
	$key = COOKIE_PREFIX.$key;
	$expire = $time ? time() + $time : 0;
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
		setcookie($key, $val, $expire, '', '', false, true);
	} else {
		setcookie($key, $val, $expire);
	}
	$_COOKIE[$key] = $val;
}
function cookie_del($key)
{
	$key = COOKIE_PREFIX.$key;
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
		setcookie($key, '', time()-3600*24, '', '', false, true);
	} else {
		setcookie($key, '', time()-3600*24);
	}
	unset($_COOKIE[$key]);
}
if (!SQLITE_USED)
{
	conn_modify('db_name');
	conn_modify('db_charset');
	conn_modify('page_charset');
}
function conn_modify($key)
{
	if (!isset($_GET["from"])) {
		GET("from", "string");
	}

	if (array_key_exists($key, $_GET)) {
		cookie_set($key, $_GET[$key], cookie_get('remember') ? COOKIE_WEEK : COOKIE_SESS);
		if ($_GET['from']) {
			header('Location: '.$_GET['from']);
		} else {
			header('Location: '.$_SERVER['PHP_SELF']);
		}
		exit;
	}
}

// --------------------------------
// @connection PARAMETERS
// --------------------------------

if (SQLITE_USED)
{
	$db_driver = "sqlite";
	$db_server = SQLITE_FILE;
	$db_name = basename(SQLITE_FILE);
	$db_name = preg_replace("#\.\w+$#", "", $db_name);
	if (!$db_name) { // Could be ".mydatabase" file.
		$db_name = basename(SQLITE_FILE);
	}
	if (defined("SQLITE_INSECURE")) {
		$db_user = "No authentication required.";
	} else {
		$db_user = SQLITE_USER;
	}
	$db_pass = "void";
	$db_charset = "utf8";
	$page_charset = "utf-8";
}
else
{
	$db_driver = cookie_get('db_driver');
	$db_server = cookie_get('db_server');
	$db_name = cookie_get('db_name');
	$db_user = cookie_get('db_user');
	$db_pass = base64_decode(cookie_get('db_pass'));
	$db_charset = cookie_get('db_charset');
	$page_charset = cookie_get('page_charset');
}

$charset1 = array('latin1', 'latin2', 'utf8', 'cp1250');
$charset2 = array('iso-8859-1', 'iso-8859-2', 'utf-8', 'windows-1250');
$charset1[] = $db_charset;
$charset2[] = $page_charset;
$charset1 = charset_assoc($charset1);
$charset2 = charset_assoc($charset2);

$driver_arr = array('mysql', 'pgsql');
$driver_arr = array_assoc($driver_arr);

function charset_assoc($arr)
{
	sort($arr);
	$ret = array();
	foreach ($arr as $v) {
		if (!$v) { continue; }
		$v = strtolower($v);
		$ret[$v] = $v;
	}
	return $ret;
}

// --------------------------------
// @disconnect
// --------------------------------

GET("disconnect", "bool");

if ($_GET['disconnect'])
{
	if (SQLITE_USED) {
		ht_logout();
	}
	cookie_del('db_pass');
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
}

// --------------------------------------------
// @authenticate MYSQL, PGSQL
// --------------------------------------------

if (!$db_pass || (!$db_driver || !$db_server || !$db_name || !$db_user))
{
	assert(!SQLITE_USED);

	POST("db_driver", "string");
	POST("db_server", "string");
	POST("db_name", "string");
	POST("db_user", "string");
	POST("db_pass", "string");
	POST("db_charset", "string");
	POST("page_charset", "string");
	POST("remember", "bool");

	if ('POST' == $_SERVER['REQUEST_METHOD'])
	{
		$db_driver = $_POST['db_driver'];
		$db_server = $_POST['db_server'];
		$db_name = $_POST['db_name'];
		$db_user = $_POST['db_user'];
		$db_pass = $_POST['db_pass'];
		$db_charset = $_POST['db_charset'];
		$page_charset = $_POST['page_charset'];

		if ($db_driver && $db_server && $db_name && $db_user)
		{
			$db_test = true;
			db_connect($db_server, $db_name, $db_user, $db_pass);
			if (is_resource($db_link))
			{
				$time = $_POST['remember'] ? COOKIE_WEEK : COOKIE_SESS;
				cookie_set('db_driver', $db_driver, $time);
				cookie_set('db_server', $db_server, $time);
				cookie_set('db_name', $db_name, $time);
				cookie_set('db_user', $db_user, $time);
				cookie_set('db_pass', base64_encode($db_pass), $time);
				cookie_set('db_charset', $db_charset, $time);
				cookie_set('page_charset', $page_charset, $time);
				cookie_set('remember', $_POST['remember'], $time);
				header('Location: '.$_SERVER['PHP_SELF']);
				exit;
			}
		}
	}
	else
	{
		$_POST['db_driver'] = $db_driver;
		$_POST['db_server'] = $db_server ? $db_server : 'localhost';
		$_POST['db_name'] = $db_name;
		$_POST['db_user'] = $db_user;
		$_POST['db_charset'] = $db_charset;
		$_POST['page_charset'] = $page_charset;
		$_POST['db_driver'] = $db_driver;
	}
	?>

		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta name="robots" content="noindex, nofollow">
			<title>Connect</title>
			<link rel="shortcut icon" href="<?php echo $_SERVER['PHP_SELF']; ?>?dbkiss_favicon=1">
		</head>
		<body>

		<?php layout(); ?>

		<h1>Connect</h1>

		<?php if (isset($db_test) && is_string($db_test)): ?>
			<div style="background: #ffffd7; padding: 0.5em; border: #ccc 1px solid; margin-bottom: 1em;">
				<span style="color: red; font-weight: bold;">Error:</span>&nbsp;
				<?php echo $db_test;?>
			</div>
		<?php endif; ?>

		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
		<table class="ls2" cellspacing="1">
		<tr>
			<th>Driver:</th>
			<td><select name="db_driver"><?php echo options($driver_arr, $_POST['db_driver']);?></select></td>
		</tr>
		<tr>
			<th>Server:</th>
			<td><input type="text" name="db_server" value="<?php echo $_POST['db_server'];?>"></td>
		</tr>
		<tr>
			<th>Database:</th>
			<td><input type="text" name="db_name" value="<?php echo $_POST['db_name'];?>"></td>
		</tr>
		<tr>
			<th>User:</th>
			<td><input type="text" name="db_user" value="<?php echo $_POST['db_user'];?>"></td>
		</tr>
		<tr>
			<th>Password:</th>
			<td><input type="password" name="db_pass" value=""></td>
		</tr>
		<tr>
			<th>Db charset:</th>
			<td><input type="text" name="db_charset" value="<?php echo $_POST['db_charset'];?>" size="10"> (optional)</td>
		</tr>
		<tr>
			<th>Page charset:</th>
			<td><input type="text" name="page_charset" value="<?php echo $_POST['page_charset'];?>" size="10"> (optional)</td>
		</tr>
		<tr>
			<td colspan="2" class="none" style="padding: 0; background: none; padding-top: 0.3em;">
				<table cellspacing="0" cellpadding="0"><tr><td>
				<input type="checkbox" name="remember" id="remember" value="1" <?php echo checked($_POST['remember']);?>></td><td>
				<label for="remember">remember me on this computer</label></td></tr></table>
			</td>
		</tr>
		<tr>
			<td class="none" colspan="2" style="padding-top: 0.4em;"><input type="submit" value="Connect"></td>
		</tr>
		</table>
		</form>

		</body>
		</html>

	<?php

	exit;
}

// -------------------------------
// @connect
// -------------------------------

if (SQLITE_USED) {
	PDO_Connect("sqlite:".SQLITE_FILE);
} else {
	db_connect($db_server, $db_name, $db_user, $db_pass);
}

if ($db_charset && "mysql" == $db_driver) {
	db_exe("SET NAMES $db_charset");
}

// -------------------------------
// @dump
// -------------------------------

GET("dump_all", "int");

if (1 == $_GET['dump_all']) {
	// @structure
	dump_all($data = false);
}
else if (2 == $_GET['dump_all']) {
	// @data
	dump_all($data = true);
}

// ------------------------------
// @export
// ------------------------------

GET("dump_table", "string");
GET("type", "string");

if ($_GET['dump_table']) {
	$type = $_GET["type"] ? $_GET["type"] : "table";
	dump_table($_GET['dump_table'], $type);
}

// ------------------------------
// @csv
// ------------------------------

GET("export", "string");
GET("query", "string");
GET("separator", "string");

if ('csv' == $_GET['export']) {
	export_csv(base64_decode($_GET['query']),  $_GET['separator']);
}

// ------------------------------
// @import PHP
// ------------------------------

POST("sqlfile", "string");
POST("ignore_errors", "bool");
POST("transaction", "bool");
POST("force_myisam", "bool");
POST("query_start", "int");

if ($_POST['sqlfile'])
{
	$files = sql_files_assoc();
	if (!isset($files[$_POST['sqlfile']])) {
		exit('File not found. md5 = '.$_POST['sqlfile']);
	}
	$sqlfile = $files[$_POST['sqlfile']];
	layout();
	echo '<div>Importing: <b>'.$sqlfile.'</b> ('.size(filesize($sqlfile)).')</div>';
	echo '<div>Database: <b>'.$db_name.'</b></div>';
	flush();
	import($sqlfile, $_POST['ignore_errors'], $_POST['transaction'], $_POST['force_myisam'], $_POST['query_start']);
	exit;
}

// -----------------------------
// @drop TABLE
// -----------------------------

POST("drop_table", "string");

if ($_POST['drop_table'])
{
	$drop_table_enq = quote_table($_POST['drop_table']);
	db_exe('DROP TABLE '.$drop_table_enq);
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
}

// -----------------------------
// @drop VIEW
// -----------------------------

POST("drop_view", "string");

if ($_POST['drop_view'])
{
	$drop_view_enq = quote_table($_POST['drop_view']);
	db_exe('DROP VIEW '.$drop_view_enq);
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
}

// ------------------------------
// @connect MYSQL, PGSQL
// ------------------------------

function db_connect($db_server, $db_name, $db_user, $db_pass)
{
	// This function is for mysql and postgresql only.
	// It is not called when connecting to sqlite.

	global $db_driver, $db_link, $db_test;

	if (!extension_loaded($db_driver)) {
		trigger_error($db_driver.' extension not loaded', E_USER_ERROR);
	}

	if ('mysql' == $db_driver)
	{
		// If we do not set the mysql timeout then it timeouts together with php after 30 secs,
		// but a php timeout will generate a fatal error, and by using @ we will get no error at all
		// and a white page will be displayed.

		ini_set("mysql.connect_timeout", 3);

		// We do not have to use "@" for mysql_connect, we can just ignore all errors by setting error_reporting to 0.
		// This way we do not see a blank page on fatal error.

		error_reporting(E_ERROR); // Only Fatal run-time errors.
		$db_link = mysql_connect($db_server, $db_user, $db_pass);
		error_reporting(-1);

		if (!is_resource($db_link)) {
			if ($db_test) {
				$db_test = 'mysql_connect() failed: '.ConvertPolishToUTF8(db_error());
				return;
			} else {
				cookie_del('db_pass');
				cookie_del('db_name');
				ConnectError('mysql_connect() failed: '.db_error());
			}
		}
		if (!@mysql_select_db($db_name, $db_link)) {
			$error = db_error();
			db_close();
			if ($db_test) {
				$db_test = 'mysql_select_db() failed: '.ConvertPolishToUTF8($error);
				return;
			} else {
				cookie_del('db_pass');
				cookie_del('db_name');
				ConnectError('mysql_select_db() failed: '.$error);
			}
		}
	}
	else if ('pgsql' == $db_driver)
	{
		$conn = sprintf("host='%s' connect_timeout=3 dbname='%s' user='%s' password='%s'", $db_server, $db_name, $db_user, $db_pass);

		error_reporting(E_ERROR); // Only Fatal run-time errors.
		$db_link = pg_connect($conn);
		error_reporting(-1);

		if (!is_resource($db_link)) {
			if ($db_test) {
				$db_test = db_error();
				return;
			} else {
				cookie_del('db_pass');
				cookie_del('db_name');
				ConnectError(db_error());
			}
		}
	}
	register_shutdown_function('db_cleanup');
}
function db_cleanup()
{
	// This func is called only for mysql and postgresql databases,
	// sqlite uses a different model.

	db_close();
}
function db_close()
{
	// This func is called only for mysql and postgresql databases,
	// sqlite uses a different model.

	global $db_driver, $db_link;

	if (is_resource($db_link)) {
		if ('mysql' == $db_driver) {
			mysql_close($db_link);
		}
		if ('pgsql' == $db_driver) {
			pg_close($db_link);
		}
	}
}
function db_query($query, $dat = false)
{
	global $db_driver, $db_link;

	$query = db_bind($query, $dat);

	if (!db_is_safe($query)) {
		return false;
	}

	if ("mysql" == $db_driver) {
		$rs = mysql_query($query, $db_link);
		if (!$rs) {
			trigger_error("mysql_query() failed: $query.<br>Error: ".db_error(), E_USER_ERROR);
		}
		return $rs;
	}
	else if ("pgsql" == $db_driver) {
		$rs = pg_query($db_link, $query);
		if (!$rs) {
			trigger_error("pg_query() failed: $query.<br>Error: ".db_error(), E_USER_ERROR);
		}
		return $rs;
	}
	else if ("sqlite" == $db_driver) {
		global $PDO;
		$stmt = $PDO->query($query);
		return $stmt;
	}
}
function db_is_safe($q, $ret = false)
{
	// currently only checks UPDATE's/DELETE's if WHERE condition is not missing
	$upd = 'update';
	$del = 'delete';

	$q = ltrim($q);
	if (strtolower(substr($q, 0, strlen($upd))) == $upd
		|| strtolower(substr($q, 0, strlen($del))) == $del) {
		if (!preg_match('#\swhere\s#i', $q)) {
			if ($ret) {
				return false;
			} else {
				trigger_error(sprintf('db_is_safe() failed. Detected UPDATE/DELETE without WHERE condition. Query: %s.', $q), E_USER_ERROR);
				return false;
			}
		}
	}

	return true;
}
function db_exe($query, $dat = false)
{
	$rs = db_query($query, $dat);
	db_free($rs);
}
function db_one($query, $dat = false)
{
	$row = db_row_num($query, $dat);
	if ($row) {
		return $row[0];
	} else {
		return false;
	}
}
function db_row($query, $dat = false)
{
	global $db_driver, $db_link;
	if ('mysql' == $db_driver)
	{
		if (is_resource($query)) {
			$rs = $query;
			return mysql_fetch_assoc($rs);
		} else {
			$query = db_limit($query, 0, 1);
			$rs = db_query($query, $dat);
			$row = mysql_fetch_assoc($rs);
			db_free($rs);
			if ($row) {
				return $row;
			}
		}
		return false;
	}
	else if ('pgsql' == $db_driver)
	{
		if (is_resource($query) || is_object($query)) {
			$rs = $query;
			return pg_fetch_assoc($rs);
		} else {
			$query = db_limit($query, 0, 1);
			$rs = db_query($query, $dat);
			$row = pg_fetch_assoc($rs);
			db_free($rs);
			if ($row) {
				return $row;
			}
		}
		return false;
	}
	else if ("sqlite" == $db_driver)
	{
		global $PDO;
		if (is_object($query)) { // PDOStatement object.
			$stmt = $query;
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} else {
			$query = db_limit($query, 0, 1);
			$stmt = db_query($query, $dat);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt = null; // db_free
			if ($row) {
				return $row;
			}
		}
		return false;
	}
}
function db_row_num($query, $dat = false)
{
	global $db_driver, $db_link;
	if ('mysql' == $db_driver)
	{
		if (is_resource($query)) {
			$rs = $query;
			return mysql_fetch_row($rs);
		} else {
			$rs = db_query($query, $dat);
			$row = mysql_fetch_row($rs);
			db_free($rs);
			if ($row) {
				return $row;
			}
			return false;
		}
	}
	else if ('pgsql' == $db_driver)
	{
		if (is_resource($query) || is_object($query)) {
			$rs = $query;
			return pg_fetch_row($rs);
		} else {
			$rs = db_query($query, $dat);
			$row = pg_fetch_row($rs);
			db_free($rs);
			if ($row) {
				return $row;
			}
			return false;
		}
	}
	else if ("sqlite" == $db_driver)
	{
		if (is_object($query)) {
			$stmt = $query;
			return $stmt->fetch(PDO::FETCH_NUM);
		} else {
			$stmt = db_query($query, $dat);
			$row = $stmt->fetch(PDO::FETCH_NUM);
			$stmt = null; // db_free
			if ($row) {
				return $row;
			}
			return false;
		}
	}
}
function db_list($query)
{
	global $db_driver, $db_link;

	$rs = db_query($query);
	$ret = array();

	if ('mysql' == $db_driver) {
		while ($row = mysql_fetch_assoc($rs)) {
			$ret[] = $row;
		}
	}
	else if ('pgsql' == $db_driver) {
		while ($row = pg_fetch_assoc($rs)) {
			$ret[] = $row;
		}
	}
	else if ("sqlite" == $db_driver) {
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}

	db_free($rs);

	return $ret;
}
function db_assoc($query)
{
	global $db_driver, $db_link;

	if ("sqlite" == $db_driver)
	{
		global $PDO;
		$stmt = db_query($query);
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		$assoc = array();
		$columns = null;
		foreach ($rows as $row) {
			if (!isset($columns)) {
				$columns = count($row);
			}
			// Not supporting 1 column, cause that would be incompatible with mysql and pgsql.
			// Use instead PDO_FetchAssoc() which supports 1 column as assoc.
			if (2 == $columns) {
				$assoc[$row[0]] = $row[1];
			} else {
				$assoc[$row[0]] = $row;
			}
		}
		return $assoc;
	}

	$rs = db_query($query);
	$rows = array();
	$num = db_row_num($rs);

	if (!is_array($num)) {
		return array();
	}
	if (!array_key_exists(0, $num)) {
		return array();
	}
	if (1 == count($num)) {
		$rows[] = $num[0];
		while ($num = db_row_num($rs)) {
			$rows[] = $num[0];
		}
		return $rows;
	}
	if ('mysql' == $db_driver) {
		mysql_data_seek($rs, 0);
	}
	else if ('pgsql' == $db_driver) {
		pg_result_seek($rs, 0);
	}
	$row = db_row($rs);
	if (!is_array($row)) {
		return array();
	}
	if (count($num) < 2) {
		trigger_error(sprintf('db_assoc() failed. Two fields required. Query: %s.', $query), E_USER_ERROR);
	}
	if (count($num) > 2 && count($row) <= 2) {
		trigger_error(sprintf('db_assoc() failed. If specified more than two fields, then each of them must have a unique name. Query: %s.', $query), E_USER_ERROR);
	}
	foreach ($row as $k => $v) {
		$first_key = $k;
		break;
	}
	if (count($row) > 2) {
		$rows[$row[$first_key]] = $row;
		while ($row = db_row($rs)) {
			$rows[$row[$first_key]] = $row;
		}
	} else {
		$rows[$num[0]] = $num[1];
		while ($num = db_row_num($rs)) {
			$rows[$num[0]] = $num[1];
		}
	}
	db_free($rs);
	return $rows;
}
function db_limit($query, $offset, $limit)
{
	global $db_driver;

	$offset = (int) $offset;
	$limit = (int) $limit;

	$query = trim($query);
	if (";" == substr($query, -1)) {
		$query = substr($query, 0, -1);
	}

	$query = preg_replace('#^([\s\S]+)LIMIT\s+\d+\s+OFFSET\s+\d+\s*$#i', '$1', $query);
	$query = preg_replace('#^([\s\S]+)LIMIT\s+\d+\s*,\s*\d+\s*$#i', '$1', $query);

	if ('mysql' == $db_driver) {
		// mysql 3.23 doesn't understand "LIMIT x OFFSET z"
		return $query." LIMIT $offset, $limit";
	} else {
		// pgsql, sqlite
		return $query." LIMIT $limit OFFSET $offset";
	}
}
function db_escape($value)
{
	global $db_driver, $db_link;

	if ('mysql' == $db_driver) {
		return mysql_real_escape_string($value, $db_link);
	}
	else if ('pgsql' == $db_driver) {
		return pg_escape_string($value);
	}
	else if ("sqlite" == $db_driver) {
		// Sqlite allows only quoting, we find a way around by removing
		// the quotes around the string.
		global $PDO;
		$value = $PDO->quote($value);
		$length = strlen($value);
		if ('\'' == $value[0] && '\'' == $value[$length-1]) {
			return substr($value, 1, $length-2);
		}
		return $value;
	}
}
function db_quote($s)
{
	global $db_driver;

	switch (true) {
		case is_null($s): return 'NULL';
		case is_int($s): return $s;
		case is_float($s): return $s;
		case is_bool($s): return (int) $s;
		case is_string($s):
			if ("sqlite" == $db_driver) {
				global $PDO;
				return $PDO->quote($s);
			} else {
				return "'" . db_escape($s) . "'";
			}
		case is_object($s): return $s->getValue();
		default:
			trigger_error(sprintf("db_quote() failed. Invalid data type: '%s'.", gettype($s)), E_USER_ERROR);
			return false;
	}
}
function db_strlen_cmp($a, $b)
{
	if (strlen($a) == strlen($b)) {
		return 0;
	}
	return strlen($a) > strlen($b) ? -1 : 1;
}
function db_bind($q, $dat)
{
	if (false === $dat) {
		return $q;
	}
	if (!is_array($dat)) {
		//return trigger_error('db_bind() failed. Second argument expects to be an array.', E_USER_ERROR);
		$dat = array($dat);
	}

	$qBase = $q;

	// special case: LIKE '%asd%', need to ignore that
	$q_search = array("'%", "%'");
	$q_replace = array("'\$", "\$'");
	$q = str_replace($q_search, $q_replace, $q);

	preg_match_all('#%\w+#', $q, $match);
	if ($match) {
		$match = $match[0];
	}
	if (!$match || !count($match)) {
		return trigger_error('db_bind() failed. No binding keys found in the query.', E_USER_ERROR);
	}
	$keys = $match;
	usort($keys, 'db_strlen_cmp');
	$num = array();

	foreach ($keys as $key)
	{
		$key2 = str_replace('%', '', $key);
		if (is_numeric($key2)) $num[$key] = true;
		if (!array_key_exists($key2, $dat)) {
			return trigger_error(sprintf('db_bind() failed. No data found for key: %s. Query: %s.', $key, $qBase), E_USER_ERROR);
		}
		$q = str_replace($key, db_quote($dat[$key2]), $q);
	}
	if (count($num)) {
		if (count($dat) != count($num)) {
			return trigger_error('db_bind() failed. When using numeric data binding you need to use all data passed to the query. You also cannot mix numeric and name binding.', E_USER_ERROR);
		}
	}

	$q = str_replace($q_replace, $q_search, $q);

	return $q;
}
function db_free($rs)
{
	global $db_driver;
	if (db_is_result($rs)) {
		if ('mysql' == $db_driver) return mysql_free_result($rs);
		else if ('pgsql' == $db_driver) return pg_free_result($rs);
		else if ("sqlite" == $db_driver) return 1; // There is no such function in PDO, it is probably freed when you destroy the "$stmt" variable.
	}
}
function db_is_result($rs)
{
	global $db_driver;
	if ('mysql' == $db_driver) return is_resource($rs);
	else if ('pgsql' == $db_driver) return is_object($rs) || is_resource($rs);
	else if ("sqlite" == $db_driver) return is_object($rs);

}
function db_error()
{
	// @db_error
	global $db_driver, $db_link;

	if ('mysql' == $db_driver) {
		if (is_resource($db_link)) {
			if (mysql_error($db_link)) {
				$error = mysql_error($db_link);
				return $error . ' ('. mysql_errno($db_link).')';
			} else {
				return false;
			}
		} else {
			if (mysql_error()) {
				return mysql_error(). ' ('. mysql_errno().')';
			} else {
				return false;
			}
		}
	}
	else if ('pgsql' == $db_driver) {
		if (is_resource($db_link)) {
			return pg_last_error($db_link);
		} else {
			global $Global_LastError;
			return $Global_LastError;
		}
	}
	else if ("sqlite" == $db_driver) {
		global $PDO;
		if ($PDO) {
			$error = $PDO->errorInfo();
			if ("00000" != $error[0] || $error[1]) {
				return "($error[0]) ($error[1]) $error[2]";
			} else {
				return false;
			}
		}
	}
}
function db_begin()
{
	global $db_driver;
	if ('mysql' == $db_driver) {
		db_exe('SET AUTOCOMMIT=0');
		db_exe('BEGIN');
	}
	else if ('pgsql' == $db_driver) {
		db_exe('BEGIN');
	}
	else if ("sqlite" == $db_driver) {
		db_exe("BEGIN TRANSACTION");
	}
}
function db_end()
{
	global $db_driver;
	if ('mysql' == $db_driver) {
		db_exe('COMMIT');
		db_exe('SET AUTOCOMMIT=1');
	}
	else if ('pgsql' == $db_driver) {
		db_exe('COMMIT');
	}
	else if ("sqlite" == $db_driver) {
		db_exe("COMMIT TRANSACTION");
	}
}
function db_rollback()
{
	global $db_driver;
	if ('mysql' == $db_driver) {
		db_exe('ROLLBACK');
		db_exe('SET AUTOCOMMIT=1');
	}
	else if ('pgsql' == $db_driver) {
		db_exe('ROLLBACK');
	}
	else if ("sqlite" == $db_driver) {
		db_exe("ROLLBACK TRANSACTION");
	}
}
function db_in_array($arr)
{
	$in = '';
	foreach ($arr as $v) {
		if ($in) $in .= ',';
		$in .= db_quote($v);
	}
	return $in;
}
function db_where($where_array, $field_prefix = null, $omit_where = false)
{
	global $db_driver;

	$field_prefix = str_replace('.', '', $field_prefix);
	$where = '';
	if (count($where_array)) {
		foreach ($where_array as $wh_k => $wh)
		{
			if (is_numeric($wh_k)) {
				if ($wh) {
					if ($field_prefix && !preg_match('#^\s*\w+\.#i', $wh) && !preg_match('#^\s*\w+\s*\(#i', $wh)) {
						if ("mysql" == $db_driver)
							$wh = "`$field_prefix`".'.'.trim($wh);
						else
							$wh = "\"$field_prefix\"".'.'.trim($wh);
					}
					if ($where) $where .= ' AND ';
					$where .= $wh;
				}
			} else {
				if ($wh_k) {
					if ($field_prefix && !preg_match('#^\s*\w+\.#i', $wh_k) && !preg_match('#^\s*\w+\s*\(#i', $wh)) {
						if ("mysql" == $db_driver)
							$wh_k = "`$field_prefix`".'.'.$wh_k;
						else
							$wh_k = "\"$field_prefix\"".'.'.$wh_k;
					}
					$wh = db_cond($wh_k, $wh);
					if ($where) $where .= ' AND ';
					$where .= $wh;
				}
			}
		}
		if ($where) {
			if (!$omit_where) {
				$where = ' WHERE '.$where;
			}
		}
	}
	return $where;
}
function db_insert($tbl, $dat)
{
	global $db_driver;
	if (!count($dat)) {
		trigger_error('db_insert() failed. Data is empty.', E_USER_ERROR);
		return false;
	}
	$cols = '';
	$vals = '';
	$first = true;
	foreach ($dat as $k => $v) {
		if ($first) {
			$cols .= quote_column($k);
			$vals .= db_quote($v);
			$first = false;
		} else {
			$cols .= ',' . quote_column($k);
			$vals .= ',' . db_quote($v);
		}
	}
	if ('mysql' == $db_driver) {
		$tbl = "`$tbl`";
	} else {
		// pgsql, sqlite
		$tbl = "\"$tbl\"";
	}
	$q = "INSERT INTO $tbl ($cols) VALUES ($vals)";
	db_exe($q);
}
// $wh = WHERE condition, might be (string) or (array)
function db_update($tbl, $dat, $wh)
{
	global $db_driver;
	if (!count($dat)) {
		trigger_error('db_update() failed. Data is empty.', E_USER_ERROR);
		return false;
	}
	$set = '';
	$first = true;
	foreach ($dat as $k => $v) {
		if ($first) {
			$set   .= quote_column($k) . '=' . db_quote($v);
			$first = false;
		} else {
			$set .= ',' . quote_column($k) . '=' . db_quote($v);
		}
	}
	if (is_array($wh)) {
		$wh = db_where($wh, null, $omit_where = true);
	}
	if ('mysql' == $db_driver) {
		$tbl = "`$tbl`";
	} else {
		$tbl = "\"$tbl\"";
	}
	$q = "UPDATE $tbl SET $set WHERE $wh";
	return db_exe($q);
}
function db_insert_id($table = null, $pk = null)
{
	global $db_driver, $db_link;
	if ('mysql' == $db_driver) {
		return mysql_insert_id($_db['conn_id']);
	}
	else if ('pgsql' == $db_driver) {
		if (!$table || !$pk) {
			trigger_error('db_insert_id(): table & pk required', E_USER_ERROR);
		}
		$seq_id = $table.'_'.$pk.'_seq';
		return db_seq_id($seq_id);
	}
	else if ("sqlite" == $db_driver) {
		global $PDO;
		return $PDO->lastInsertId();
	}
}
function db_seq_id($seqName)
{
	return db_one('SELECT currval(%seqName)', array('seqName'=>$seqName));
}
function db_cond($k, $v)
{
	$k_enq = quote_column($k);
	if (is_null($v)) {
		return "$k_enq IS NULL";
	} else {
		$v = db_quote($v);
		return "$k_enq = $v";
	}
}
function list_dbs()
{
	// @databases

	global $db_driver, $db_link;
	if ('mysql' == $db_driver)
	{
		$result = mysql_query('SHOW DATABASES', $db_link);
		$ret = array();
		while ($row = mysql_fetch_row($result)) {
			$ret[$row[0]] = $row[0];
		}
		return $ret;
	}
	else if ('pgsql' == $db_driver)
	{
		return db_assoc('SELECT datname, datname FROM pg_database');
	}
	else if ("sqlite" == $db_driver)
	{
		// There is only 1 database in sqlite database file.
		return array("void" => "void");
	}
}
function views_supported()
{
	static $ret;
	if (isset($ret)) {
		return $ret;
	}

	global $db_driver, $db_link;

	if ('mysql' == $db_driver) {
		$version = mysql_get_server_info($db_link);
		if (strpos($version, "-") !== false) {
			$version = substr($version, 0, strpos($version, "-"));
		}
		if (version_compare($version, "5.0.2", ">=")) {
			// Views are available in 5.0.0 but we need SHOW FULL TABLES
			// and the FULL syntax was added in 5.0.2, FULL allows us to
			// to distinct between tables & views in the returned list by
			// by providing an additional column.
			$ret = true;
			return true;
		} else {
			$ret = false;
			return false;
		}
	}
	else if ('pgsql' == $db_driver) {
		return true;
	}
	else if ("sqlite" == $db_driver) {
		return true;
	}

}
function list_tables($views_mode=false)
{
	// @tables
	// @views

	global $db_driver, $db_link, $db_name;

	if ($views_mode && !views_supported()) {
		return array();
	}

	static $cache_tables;
	static $cache_views;

	if ($views_mode) {
		if (isset($cache_views)) {
			return $cache_views;
		}
	} else {
		if (isset($cache_tables)) {
			return $cache_tables;
		}
	}

	static $all_tables; // tables and views

	if ('mysql' == $db_driver)
	{
		if (!isset($all_tables)) {
			$all_tables = db_assoc("SHOW FULL TABLES");
			// assoc: table name => table type (BASE TABLE or VIEW)
		}

		// This chunk of code is the same as in pgsql driver.
		if ($views_mode) {
			$views = array();
			foreach ($all_tables as $view => $type) {
				if ($type != 'VIEW') { continue; }
				$views[] = $view;
			}
			$cache_views = $views;
			return $views;
		} else {
			$tables = array();
			foreach ($all_tables as $table => $type) {
				if ($type != 'BASE TABLE') { continue; }
				$tables[] = $table;
			}
			$cache_tables = $tables;
			return $tables;
		}
	}
	else if ('pgsql' == $db_driver)
	{
		if (!isset($all_tables)) {
			$query = "SELECT table_name, table_type ";
			$query .= "FROM information_schema.tables ";
			$query .= "WHERE table_schema = 'public' ";
			$query .= "AND (table_type = 'BASE TABLE' OR table_type = 'VIEW') ";
			$query .= "ORDER BY table_name ";
			$all_tables = db_assoc($query);
		}

		// This chunk of code is the same as in mysql driver.
		if ($views_mode) {
			$views = array();
			foreach ($all_tables as $view => $type) {
				if ($type != 'VIEW') { continue; }
				$views[] = $view;
			}
			$cache_views = $views;
			return $views;
		} else {
			$tables = array();
			foreach ($all_tables as $table => $type) {
				if ($type != 'BASE TABLE') { continue; }
				$tables[] = $table;
			}
			$cache_tables = $tables;
			return $tables;
		}
	}
	else if ("sqlite" == $db_driver)
	{
		if ($views_mode) {
			$views = PDO_FetchAssoc("SELECT name FROM sqlite_master WHERE type = 'view' AND name NOT LIKE 'sqlite_%' ORDER BY name");
			$cache_views = $views;
			return $views;
		} else {
			$tables = PDO_FetchAssoc("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
			$cache_tables = $tables;
			return $tables;
		}
	}
}
function IsTableAView($table)
{
	// There is no cache here, so call it only once!

	global $db_driver, $db_name;

	if ("mysql" == $db_driver) {
		// Views and information_schema is supported since 5.0
		if (views_supported()) {
			$query = "SELECT table_name FROM information_schema.tables WHERE table_schema=%0 AND table_name=%1 AND table_type='VIEW' ";
			$row = db_row($query, array($db_name, $table));
			return (bool) $row;
		}
		return false;
	}
	else if ("pgsql" == $db_driver) {
		$query = "SELECT table_name, table_type ";
		$query .= "FROM information_schema.tables ";
		$query .= "WHERE table_schema = 'public' ";
		$query .= "AND table_type = 'VIEW' AND table_name = %0 ";
		$row = db_row($query, $table);
		return (bool) $row;
	}
}
function quote_table($table)
{
	global $db_driver;

	if ('mysql' == $db_driver) {
		return "`$table`";
	} else {
		return "\"$table\"";;
	}
}
function quote_column($column)
{
	global $db_driver;

	if ('mysql' == $db_driver) {
		return "`$column`";
	} else {
		return "\"$column\"";;
	}
}
function table_structure($table, $type="table")
{
	// @dump
	// @export
	// @structure

	global $db_driver;

	if ('mysql' == $db_driver)
	{
		if ("table" == $type) {
			$query = "SHOW CREATE TABLE `$table`";
			$row = db_row_num($query);
			echo $row[1].';';
			echo "\n\n";
		} else if ("view" == $type) {
			$query = "SHOW CREATE VIEW `$table`";
			$row = db_row_num($query);
			echo $row[1].';';
			echo "\n\n";
		} else {
			assert(0);
		}
	}
	else if ('pgsql' == $db_driver)
	{
		return;
	}
	else if ("sqlite" == $db_driver)
	{
		if ("table" == $type) {
			$sql = PDO_FetchOne("SELECT sql FROM sqlite_master WHERE name = :name AND type='table' ",
				array(":name"=>$table));
			$sql = str_replace("\r\n", "\n", $sql);  // editplus invalid line endings, strange
			echo "$sql;\n";
		} else if ("view" == $type) {
			$sql = PDO_FetchOne("SELECT sql FROM sqlite_master WHERE name = :name AND type='view' ",
				array(":name"=>$table));
			$sql = str_replace("\r\n", "\n", $sql); // editplus invalid line endings, strange
			echo "$sql;\n";
		} else {
			assert(0);
		}
		unset($sql);

		if ("table" == $type) {
			$indexes = PDO_FetchAll("SELECT * FROM sqlite_master WHERE tbl_name = :tbl_name AND type='index' ",
				array(":tbl_name"=>$table));
			foreach ($indexes as $index) {
				if ($index["sql"]) { // autoindexes have column "sql" empty
					echo "\n-- INDEX: \"{$index['name']}\"\n\n";
					echo "{$index['sql']};\n";
				}
			}
		}

		echo "\n";
	}
}
function table_data($table)
{
	// @dump
	// @export
	// @data

	global $db_driver;
	set_time_limit(0);
	if ('mysql' == $db_driver) {
		$query = "SELECT * FROM `$table` ";
	} else {
		// pgsql, sqlite
		$query = "SELECT * FROM \"$table\" ";
	}
	$result = db_query($query);
	$count = 0;
	while ($row = db_row($result))
	{
		if ('mysql' == $db_driver) {
			echo 'INSERT INTO `'.$table.'` VALUES (';
		} else {
			// pgsql, sqlite
			echo 'INSERT INTO "'.$table.'" VALUES (';
		}
		$x = 0;
		foreach($row as $key => $value)
		{
			if ($x == 1) { echo ', '; }
			else  { $x = 1; }
			if (is_numeric($value)) { echo "'".$value."'"; }
			elseif (is_null($value))  { echo 'NULL'; }
			else { echo '\''. escape($value) .'\''; }
		}
		echo ");\n";
		$count++;
		if ($count % 100 == 0) { flush(); }
	}
	db_free($result);
	if ($count) {
		echo "\n";
	}
}
function table_status()
{
	// @table
	// @status

	// Size is not supported for Views, only for Tables.

	global $db_driver, $db_link, $db_name;
	if ('mysql' == $db_driver)
	{
		$status = array();
		$status['total_size'] = 0;
		$result = mysql_query("SHOW TABLE STATUS FROM `$db_name`", $db_link);
		while ($row = mysql_fetch_assoc($result)) {
			if (!is_numeric($row['Data_length'])) {
				// Data_length for Views is NULL.
				continue;
			}
			$status['total_size'] += $row['Data_length']; // + Index_length
			$status[$row['Name']]['size'] = $row['Data_length'];
			$status[$row['Name']]['count'] = $row['Rows'];
		}
		return $status;
	}
	else if ('pgsql' == $db_driver)
	{
		$status = array();
		$status['total_size'] = 0;
		$tables = list_tables(); // only tables, not views
		if (!count($tables)) {
			return $status;
		}
		$tables_in = db_in_array($tables);
		$rels = db_list("SELECT relname, reltuples, (relpages::decimal + 1) * 8 * 2 * 1024 AS relsize FROM pg_class WHERE relname IN ($tables_in)");
		foreach ($rels as $rel) {
			$status['total_size'] += $rel['relsize'];
			$status[$rel['relname']]['size'] = $rel['relsize'];
			$status[$rel['relname']]['count'] = $rel['reltuples'];
		}
		return $status;
	}
	else if ("sqlite" == $db_driver)
	{
		global $db_server;
		$status = array();
		$status["total_size"] = filesize($db_server);
		return $status;
	}
}

//dump(db_list("PRAGMA table_info(\"test22\")"));
//dump(db_list("SHOW COLUMNS FROM nwuser2"));

//db_exe("CREATE TABLE some33 (id integer PRIMARY KEY, name varchar(30)) ");
//dump(db_list("SELECT * FROM information_schema.columns WHERE table_name = 'some33' ORDER BY ordinal_position"));
//dump(db_list("SELECT * FROM information_schema.table_constraints WHERE table_name = 'some33'"));
//dump(db_list("SELECT * FROM information_schema.key_column_usage WHERE table_name = 'some33'"));

$global_columns = array();

function ColumnType($type)
{
	// @column

	if ('varchar' == $type) { $type = 'char'; }
	else if ('integer' == $type) { $type = 'int'; }
	else if ('tinyint' == $type) { $type = 'int'; }
	else if ('smallint' == $type) { $type = 'int'; }
	else if ('mediumint' == $type) { $type = 'int'; }
	else if ('bigint' == $type) { $type = 'int'; }

	return $type;
}

function table_columns($table)
{
	// @columns

	global $db_driver, $global_columns;

	static $cache_columns = array();
	if (isset($cache_columns[$table])) {
		return $cache_columns[$table];
	}

	if ('mysql' == $db_driver)
	{
		$columns = array();
		$rows = @db_list("SHOW COLUMNS FROM `$table`");
		/*
			[Field] => id
			[Type] => int(11)
			[Null] => NO
			[Key] => PRI
			[Default] =>
			[Extra] =>
		*/
		if (!($rows && count($rows))) {
			return false;
		}
		if (!isset($global_columns[$table])) {
			$global_columns[$table] = array();
		}
		foreach ($rows as $row)
		{
			$type = $row['Type']; // for example "VARCHAR(50)"
			preg_match('#^[a-z]+#i', $type, $match);
			$type = strtolower($match[0]);
			$type = ColumnType($type);

			$columns[$row['Field']] = $type;

			$global_columns[$table][] = array(
				"name" => $row["Field"],
				"type" => $type,
				"pk" => $row["Key"] == "PRI" || $row["Key"] == "UNI",
				"notnull" => $row["Null"] == "NO",
				"default" => $row["Default"]
			);
		}
	}
	else if ('pgsql' == $db_driver)
	{
		$columns = db_list("SELECT column_name, udt_name, column_default, is_nullable FROM information_schema.columns WHERE table_name = '$table' ORDER BY ordinal_position");
		dump($columns);

		// column_name
		// udt_name
		// column_default - default value
		// is_nullable

		/*
			[table_catalog] => test
			[table_schema] => public
			[table_name] => some22
			[column_name] => id
			[ordinal_position] => 1
			[column_default] =>
			[is_nullable] => NO
			[data_type] => integer
			[character_maximum_length] =>
			[udt_catalog] => test
			[udt_schema] => pg_catalog
			[udt_name] => int4
			[dtd_identifier] => 1
			[is_self_referencing] => NO
			[is_identity] => NO
			[is_generated] => NEVER
			[is_updatable] => YES
		*/

		// To get primary key for a table in postgresql:
		//db_exe("CREATE TABLE some33 (id integer PRIMARY KEY, name varchar(30)) ");
		//dump(db_list("SELECT * FROM information_schema.table_constraints WHERE table_name = 'some33'"));
		//dump(db_list("SELECT * FROM information_schema.key_column_usage WHERE table_name = 'some33'"));

		if (!count($columns)) {
			return false;
		}
		foreach ($columns as $col => $type) {
			// "_" also in regexp - error when retrieving column info from "pg_class" - through a Custom query in SQL Editor POPUP.
			// udt_name might be "_aclitem" / "_text".

			// $type == for example "VARCHAR(50)"
			preg_match('#^[a-z_]+#i', $type, $match);
			$type = strtolower($match[0]);
			$type = ColumnType($type);

			$columns[$col] = $type;
		}
	}
	else if ("sqlite" == $db_driver)
	{
		$rawColumns = db_list("PRAGMA table_info(\"$table\")");
		/*
			[cid] => 0
			[name] => id
			[type] => INTEGER
			[notnull] => 0
			[dflt_value] =>
			[pk] => 1
		*/
		if (!count($rawColumns)) {
			return false;
		}
		foreach ($rawColumns as $row) {
			if ($row["type"]) {
				preg_match("#^[a-z]+#i", $row["type"], $match);
				$type = strtolower($match[0]);
				$type = ColumnType($type);
			} else {
				// type might be empty: sqlite_sequence table
				// Solved by not querying sqlite_ special: AND name NOT LIKE 'sqlite_%'
				$type = "";
			}
			$col = $row["name"];
			$columns[$col] = $type;

			$global_columns[$table][] = array(
				"name" => $row["name"],
				"type" => $type,
				"pk" => (int) $row["pk"],
				"notnull" => (int) $row["notnull"],
				"default" => $row["dflt_value"]
			);
		}
	}

	$cache_columns[$table] = $columns;

	return $columns;
}
function IsTimestampColumn($column, $value)
{
	// @column
	// @istime

	if (ctype_digit($value) && preg_match('#(time|date|unix|data|czas)#i', $column) && $value > 100000000)
	{
		// 100 000 000 == 1973-03-03 10:46:40
		// Only big integers change to dates, so a low one like "1054"
		// does not get changed into a date, cause that would probably be wrong.
		return true;
	}
	return false;
}
function table_columns_group($types)
{
	// @columns

	foreach ($types as $k => $type) {
		if ($type) {
			preg_match('#^\w+#', $type, $match);
			$type = $match[0];
		} else {
			$type = "";
		}
		$types[$k] = $type;
	}
	$types = array_unique($types);
	$types = array_values($types);
	$types2 = array();
	foreach ($types as $type) {
		$types2[$type] = $type;
	}
	return $types2;
}
function table_pk($table)
{
	// @pk

	$table_columns = table_columns($table);
	if (!$table_columns) return null;

	$pk = "";

	global $global_columns;

	foreach ($global_columns[$table] as $column) {
		if ($column["pk"]) {
			if ($pk) {
				$pk .= ":".$column["name"];
			} else {
				$pk = $column["name"];
			}
		}
	}

	if ($pk) {
		// Possible returns: "col1", "col1:col2", "col1:col2:col3"
		return $pk;
	}

	global $db_driver;

	if ("sqlite" == $db_driver)
	{
		// Detect PK for:
		// UNIQUE (nagroda, id_gracza)

		// When there are multiple pks there is no info about it in PRAGMA table_info.pk,
		// we need to get it from the SQL string that was used to create the table.

		$sql = PDO_FetchOne("SELECT sql FROM sqlite_master WHERE type = 'view' OR type='table' AND name = :name ",
			array(":name" => $table));

		if (preg_match("#UNIQUE\s*\(\s*(\"?\w+\"?(\s*,\s*\"?\w+\"?)*)\s*\)#i", $sql, $match)) {
			$cols = $match[1];
			$cols = str_replace("\"", "", $cols);
			$cols = preg_replace("#\s+#", "", $cols);
			$cols = str_replace(",", ":", $cols);
			return $cols; // "nagroda:id_gracza"
		}
	}

	foreach ($table_columns as $col => $type) {
		return $col;
	}
}
function guess_pk($rows)
{
	// @pk

	if (!count($rows)) {
		return false;
	}
	$patterns = array('#^\d+$#', '#^[^\s]+$#');
	$row = array_first($rows);
	foreach ($patterns as $pattern)
	{
		foreach ($row as $col => $v) {
			if ($v && preg_match($pattern, $v)) {
				if (array_col_match_unique($rows, $col, $pattern)) {
					return $col;
				}
			}
		}
	}
	return false;
}
function QuotePkeys($pkeys)
{
	// Quotes multiple primary keys.
	// nagroda, id_gracza => "nagroda", "id_gracza"

	$pkeys_enq = $pkeys;
	unset($pkeys);

	if (is_array($pkeys_enq)) {
		$pkeys_enq = implode(",", $pkeys_enq);
	} else {
		$pkeys_enq = str_replace(":", ",", $pkeys_enq);
	}

	$pkeys_enq = preg_replace("#(\w+)#i", "\"\$1\"", $pkeys_enq);
	return $pkeys_enq;
}
function FirstFromPkeys($pkeys)
{
	if (is_array($pkeys)) {
		return $pkeys[0];
	} else {
		$arr = explode(":", $pkeys);
		return $arr[0];
	}
}
function EncodeRowId($row, $pk, $pkeys)
{
	// We need to escape ":" colons that are used to represent many primary keys for the table.
	// It also creates and Id using the arguments passed depending whether pkeys is not empty.

	if ($pkeys) {
		$cols = explode(":", $pkeys);
		$rowid = array();
		foreach ($cols as $col) {
			$rowid[] = str_replace(":", "::", $row[$col]);
		}
		$rowid = implode(":", $rowid);
		return $rowid;
	} else {
		return str_replace(":", "::", $row[$pk]);
	}
}
function DecodeRowId($id)
{
	// We unescape colons.
	// Returns: "12" or array("12", "15");

	$id = str_replace("::", ":", $id);

	if (strstr($id, ":")) {
		$values = explode(":", $id);
		return $values;
	} else {
		return $id;
	}
}
function escape($text)
{
	$text = addslashes($text);
	$search = array("\r", "\n", "\t");
	$replace = array('\r', '\n', '\t');
	return str_replace($search, $replace, $text);
}
function ob_cleanup()
{
	while (ob_get_level()) {
		ob_end_clean();
	}
	if (headers_sent()) {
		return;
	}
	if (function_exists('headers_list')) {
		foreach (headers_list() as $header) {
			if (preg_match('/Content-Encoding:/i', $header)) {
				header('Content-encoding: none');
				break;
			}
		}
	} else {
		header('Content-encoding: none');
	}
}
function query_color($query)
{
	// @color

	$color = 'red';
	$words = array('SELECT', 'UPDATE', 'DELETE', 'FROM', 'LIMIT', 'OFFSET', 'AND', 'LEFT JOIN', 'WHERE', 'SET',
		'ORDER BY', 'GROUP BY', 'GROUP', 'DISTINCT', 'COUNT', 'COUNT\(\*\)', 'IS', 'NULL', 'IS NULL', 'AS', 'ON', 'INSERT INTO', 'VALUES', 'BEGIN', 'COMMIT', 'CASE', 'WHEN', 'THEN', 'END', 'ELSE', 'IN', 'NOT', 'LIKE', 'ILIKE', 'ASC', 'DESC', 'LOWER', 'UPPER');
	$words = implode('|', $words);

	$query = preg_replace("#^({$words})(\s)#i", '<font color="'.$color.'">$1</font>$2', $query);
	$query = preg_replace("#(\s)({$words})$#i", '$1<font color="'.$color.'">$2</font>', $query);
	// replace twice, some words when preceding other are not replaced
	$query = preg_replace("#([\s\(\),])({$words})([\s\(\),])#i", '$1<font color="'.$color.'">$2</font>$3', $query);
	$query = preg_replace("#([\s\(\),])({$words})([\s\(\),])#i", '$1<font color="'.$color.'">$2</font>$3', $query);
	$query = preg_replace("#^($words)$#i", '<font color="'.$color.'">$1</font>', $query);

	preg_match_all('#<font[^>]+>('.$words.')</font>#i', $query, $matches);
	foreach ($matches[0] as $k => $font) {
		$font2 = str_replace($matches[1][$k], strtoupper($matches[1][$k]), $font);
		$query = str_replace($font, $font2, $query);
	}

	return $query;
}
function query_upper($sql)
{
	// @color

	return $sql;
	// todo: don't upper quoted ' and ' values
	$queries = preg_split("#;(\s*--[ \t\S]*)?(\r\n|\n|\r)#U", $sql);
	foreach ($queries as $k => $query) {
		$strip = query_strip($query);
		$color = query_color($strip);
		$sql = str_replace($strip, $color, $sql);
	}
	$sql = preg_replace('#<font color="\w+">([^>]+)</font>#iU', '$1', $sql);
	return $sql;
}
function query_cut($query)
{
	// @color

	// removes sub-queries and string values from query
	$brace_start = '(';
	$brace_end = ')';
	$quote = "'";
	$inside_brace = false;
	$inside_quote = false;
	$depth = 0;
	$ret = '';
	$query = str_replace('\\\\', '', $query);

	for ($i = 0; $i < strlen($query); $i++)
	{
		$prev_char = isset($query{$i-1}) ? $query{$i-1} : null;
		$char = $query{$i};
		if ($char == $brace_start) {
			if (!$inside_quote) {
				$depth++;
			}
		}
		if ($char == $brace_end) {
			if (!$inside_quote) {
				$depth--;
				if ($depth == 0) {
					$ret .= '(...)';
				}
				continue;
			}
		}
		if ($char == $quote) {
			if ($inside_quote) {
				if ($prev_char != '\\') {
					$inside_quote = false;
					if (!$depth) {
						$ret .= "'...'";
					}
					continue;
				}
			} else {
				$inside_quote = true;
			}
		}
		if (!$depth && !$inside_quote) {
			$ret .= $char;
		}
	}
	return $ret;
}
function table_from_query($query)
{
	// @query

	if (preg_match('#\sFROM\s+["`]?(\w+)["`]?#i', $query, $match)) {
		$cut = query_cut($query);
		if (preg_match('#\sFROM\s+["`]?(\w+)["`]?#i', $cut, $match2)) {
			$table = $match2[1];
		} else {
			$table = $match[1];
		}
	} else if (preg_match('#UPDATE\s+"?(\w+)"?#i', $query, $match)) {
		$table = $match[1];
	} else if (preg_match('#INSERT\s+INTO\s+"?(\w+)"?#', $query, $match)) {
		$table = $match[1];
	} else {
		$table = false;
	}
	return $table;
}
function is_select($query)
{
	// @query
	return preg_match('#^\s*(SELECT)\s+#i', $query);
}
function is_show($query)
{
	// @query

	// mysql only: SHOW CREATE TABLE, SHOW CREATE VIEW.
	// It is the same as SELECT, but cannot add LIMIT to such query.

	// So that in SQL Editor popup we have SHOW statements available in "SELECT queries: <select>".
	// Also it allows us to click "Refetch" link.

	return preg_match('#^\s*(SHOW)\s+#i', $query);
}
function query_strip($query)
{
	// @query

	// strip comments and ';' from the end of query
	$query = trim($query);
	if (";" == substr($query, -1)) {
		$query = substr($query, 0, -1);
	}
	$lines = preg_split("#(\r\n|\n|\r)#", $query);
	foreach ($lines as $k => $line) {
		$line = trim($line);
		if (!$line || 0 === strpos($line, '--')) {
			unset($lines[$k]);
		}
	}
	$query = implode("\r\n", $lines);
	return $query;
}
function dump_table($table, $type)
{
	// @dump
	// @export

	ob_cleanup();
	define('DEBUG_CONSOLE_HIDE', 1);
	set_time_limit(0);
	global $db_name;
	header("Cache-control: private");
	header("Content-type: application/octet-stream");
	header('Content-Disposition: attachment; filename='.$table.'.sql');
	// dump_table is called for both: tables and views.
	if ("table" == $type) {
		table_structure($table, $type);
	} else {
		// View > Export
		// We export only data for views.
	}
	table_data($table, $type);
	exit;
}
function dump_all($data = false)
{
	// @dump
	// @structure
	// @data

	GET("table_filter", "string");

	global $db_driver, $db_server, $db_name;

	ob_cleanup();
	define('DEBUG_CONSOLE_HIDE', 1);
	set_time_limit(0);

	$tables = list_tables();
	$table_filter = $_GET["table_filter"];
	$tables = table_filter($tables, $table_filter);

	header("Cache-control: private");
	header("Content-type: application/octet-stream");
	header('Content-Disposition: attachment; filename='.$db_name.'.sql');

	echo "--\n";
	if ($data) {
		echo "-- Dump type: DATA & STRUCTURE\n";
	} else {
		echo "-- Dump type: STRUCTURE ONLY\n";
	}

	if ("sqlite" == $db_driver) {
		echo "-- Database file: $db_server\n";
	} else {
		echo "-- Database: $db_name\n";
	}

	$date = date("Y-m-d");
	echo "-- Exported on: $date\n";
	$version = DBKISS_VERSION;
	echo "-- Powered by: DBKiss (http://www.gosu.pl/dbkiss/)\n";
	echo "--\n\n";

	foreach ($tables as $key => $table)
	{
		echo "-- TABLE: \"$table\"\n\n";
		table_structure($table);
		if ($data) {
			echo "-- INSERTS for: \"$table\"\n\n";
			table_data($table);
		}
		flush();
	}
	unset($table);

	$views = list_tables(true);
	foreach ($views as $key => $view)
	{
		echo "-- VIEW: \"$view\"\n\n";
		table_structure($view, "view");
		flush();
	}

	echo "--\n";
	echo "-- END OF DUMP\n";
	echo "--\n";

	exit;
}
function export_csv($query, $separator)
{
	// @csv

	ob_cleanup();
	set_time_limit(0);

	if (!is_select($query) && !is_show($query)) {
		trigger_error('export_csv() failed: not a SELECT or SHOW query: '.$query, E_USER_ERROR);
	}

	$table = table_from_query($query);
	if (!$table) {
		$table = 'unknown';
	}

	header("Cache-control: private");
	header("Content-type: application/octet-stream");
	header('Content-Disposition: attachment; filename='.$table.'_'.date('Ymd').'.csv');

	$rs = db_query($query);
	$first = true;

	while ($row = db_row($rs)) {
		if ($first) {
			echo csv_row(array_keys($row), $separator);
			$first = false;
		}
		echo csv_row($row, $separator);
		flush();
	}

	exit();
}
function csv_row($row, $separator)
{
	// @csv

	foreach ($row as $key => $val) {
		$enquote = false;
		if (false !== strpos($val, $separator)) {
			$enquote = true;
		}
		if (false !== strpos($val, "\"")) {
			$enquote = true;
			$val = str_replace("\"", "\"\"", $val);
		}
		if (false !== strpos($val, "\r") || false !== strpos($val, "\n")) {
			$enquote = true;
			$val = preg_replace('#(\r\n|\r|\n)#', "\n", $val); // excel needs \n instead of \r\n
		}
		if ($enquote) {
			$row[$key] = "\"".$val."\"";
		}
	}
	$out = implode($separator, $row);
	$out .= "\r\n";
	return $out;
}
function import($file, $ignore_errors = false, $transaction = false, $force_myisam = false, $query_start = false)
{
	// @import PHP

	global $db_driver, $db_link, $db_charset;
	if ($ignore_errors && $transaction) {
		echo '<div>You cannot select both: ignoring errors and transaction</div>';
		exit;
	}

	$count_errors = 0;
	set_time_limit(0);
	$fp = fopen($file, 'r');
	if (!$fp) { exit('fopen('.$file.') failed'); }
	flock($fp, 1);
	$text = trim(fread($fp, filesize($file)));
	flock($fp, 3);
	fclose($fp);

	if ($force_myisam) {
		$text = preg_replace('#TYPE\s*=\s*InnoDB#i', 'TYPE=MyISAM', $text);
	}
	$text = preg_split("#;(\r\n|\n|\r)#", $text);
	$x = 0;
	echo '<div>Ignoring errors: <b>'.($ignore_errors?'Yes':'No').'</b></div>';
	echo '<div>Transaction: <b>'.($transaction?'Yes':'No').'</b></div>';
	echo '<div>Force MyIsam: <b>'.($force_myisam?'Yes':'No').'</b></div>';
	echo '<div>Query start: <b>#'.$query_start.'</b></div>';
	echo '<div>Queries found: <b>'.count($text).'</b></div>';
	echo '<div>Executing ...</div>';
	flush();

	if ($transaction) {
		echo '<div>BEGIN;</div>';
		db_begin();
	}

	$time = time_start();
	$query_start = (int) $query_start;
	if (!$query_start) {
		$query_start = 1;
	}
	$query_no = 0;

	foreach($text as $key => $value)
	{
		$x++;
		$query_no++;
		if ($query_start > $query_no) {
			continue;
		}

		if ('mysql' == $db_driver)
		{
			$result = @mysql_query($value.';', $db_link);
		}
		if ('pgsql' == $db_driver)
		{
			$result = @pg_query($db_link, $value.';');
		}
		if(!$result) {
			$x--;
			if (!$count_errors) {
				echo '<table class="ls" cellspacing="1"><tr><th width="25%">Error</th><th>Query</th></tr>';
			}
			$count_errors++;
			echo '<tr><td>#'.$query_no.' '.db_error() .')'.'</td><td>'.nl2br(html_once($value)).'</td></tr>';
			flush();
			if (!$ignore_errors) {
				echo '</table>';
				echo '<div><span style="color: red;"><b>Import failed.</b></span></div>';
				echo '<div>Queries executed: <b>'.($x-$query_start+1).'</b>.</div>';
				if ($transaction) {
					echo '<div>ROLLBACK;</div>';
					db_rollback();
				}
				echo '<br><div><a href="'.$_SERVER['PHP_SELF'].'?import=1">&lt;&lt; go back</a></div>';
				exit;
			}
		}
	}
	if ($count_errors) {
		echo '</table>';
	}
	if ($transaction) {
		echo '<div>COMMIT;</div>';
		db_end();
	}
	echo '<div><span style="color: green;"><b>Import finished.</b></span></div>';
	echo '<div>Queries executed: <b>'.($x-$query_start+1).'</b>.</div>';
	echo '<div>Time: <b>'.time_end($time).'</b> sec</div>';
	echo '<br><div><a href="'.$_SERVER['PHP_SELF'].'?import=1">&lt;&lt; go back</a></div>';
}
function layout()
{
	// @layout

	global $sql_area;
	?>
		<style type=text/css>

		/* @styles */
		/* @css */

		html, body { cursor: default; }

		::selection {
			background: #C1EBFA;
		}

		body,table {
			font: 11px Tahoma;
		}
		body {
			line-height: 1.4em;
		}
		input,select,textarea {
			font: 11px Tahoma;
		}

		input[type=text], input[type=search] {
			border: #b5b4bb 1px solid;
			border-radius: 3px;
			padding: 2px 3px;
			background: #f9f9f9;
		}
		input[type=text]:focus, input[type=search]:focus select:focus {
			background: #F2FBFE;
			border-color: #7FB2DA;
			/* #F0FAFE #F2FBFE #F6FCFE */
		}
		form .ls2 input[type=text] {
			background: #fff;
		}
		select {
			border: #b5b4bb 1px solid;
			border-radius: 3px;
			padding: 1px 3px;
			background: #f9f9f9;
		}
		input[type=button], input[type=submit] {
			border-width: 1px;
			border-radius: 3px;
			background: -webkit-linear-gradient(#fff, #ddd);
			background: -moz-linear-gradient(#fff, #ddd);
			background: -o-linear-gradient(#fff, #ddd);
			padding: 2px 12px;
			cursor: pointer;
			box-sizing: border-box;
			border-style: solid;
			border-color: #ddd #989699 #989699 #ddd;
		}
		input[type=button]:active, input[type=submit]:active {
			border-color:  #989699 #ddd #ddd #989699 ;
		}
		input[type=button]:hover, input[type=submit]:hover, input[type=button]:focus, input[type=submit]:focus {
			background: -webkit-linear-gradient(#fff, #CDEFFB);
			background: -moz-linear-gradient(#fff, #CDEFFB);
			background: -o-linear-gradient(#fff, #CDEFFB);
		}

		input:focus, select:focus, textarea:focus {
			outline: none;
		}
		body { padding: 0; margin: 1em 1.5em; }

		h1, h2 { margin: 11px 0; }
		h1 { font: bold 15px Tahoma; }
		h2 { font: bold 13px Tahoma; }

		a, a:visited { text-decoration: none; }
		a:hover { text-decoration: underline; }
		a, a:visited, a.blue, a.blue:visited { color: #0064ff;  }

		.special {
			font: 11px Tahoma;
			color: #000;
			padding: 2px 8px;
			border: #ccc 1px solid; border-radius: 5px;
			background: -webkit-linear-gradient(#fff, #eee);
			background: -moz-linear-gradient(#fff, #eee);
			background: -o-linear-gradient(#fff, #eee);
			filter: progid:dximagetransform.microsoft.gradient(startcolorstr=#ffffff, endcolorstr=#eeeeee);
		}
		.special:hover {
			border-color: #aaa;
			color: #000;
			text-decoration: none;
			background: -webkit-linear-gradient(#fff, #ddd);
			background: -moz-linear-gradient(#fff, #ddd);
			background: -o-linear-gradient(#fff, #ddd);
			filter: progid:dximagetransform.microsoft.gradient(startcolorstr=#ffffff, endcolorstr=#dddddd);
		}

		p { margin: 0.75em 0; }

		/* form */

		form { margin: 0; padding: 0; }
		form th { text-align: left; }

		form .none td, form .none th { background: none; padding: 0em 0.25em; }
		label { padding-left: 2px; padding-right: 4px; }

		.checkbox { padding-left: 0; margin-left: 0; margin-top: 1px; }

		/* messages */

		.error { background: #ffffd7; padding: 0.5em; border: #ccc 1px solid; margin-bottom: 1em; margin-top: 1em; }
		.msg { background: #eee; padding: 0.5em; border: #ccc 1px solid; margin-bottom: 1em; margin-top: 1em; }
		.sql_area { <?php echo $sql_area;?> }
		.query { background: #eee; padding: 0.35em; border: #ccc 1px solid; margin-bottom: 1em; margin-top: 1em; }

		/* @ls */

		.ls {
			box-shadow: 1px 1px 8px #ddd;
		}

		.ls > tbody > tr > th,
		.ls > tbody > tr > td { padding: 2px 10px; font: 11px Tahoma; }

		.ls > tbody > tr > th {
			background: -webkit-linear-gradient(#fff, #ddd);
			background: -moz-linear-gradient(#fff, #ddd);
			background: -o-linear-gradient(#fff, #ddd);
			filter: progid:dximagetransform.microsoft.gradient(startcolorstr=#ffffff, endcolorstr=#dddddd);
			font-weight: bold;
			font-size: 11px;
			text-transform: none;
			border-top: #ccc 1px solid;
			border-bottom: #bbb 1px solid;
			padding: 3px 5px;
		}
		.ls > tbody > tr > th.camelcase {
			text-transform: none;
		}

		.ls > tbody > tr > th.sortable {
			padding: 0px;
		}
		.ls > tbody > tr > th.sortable > a {
			display: block;
			color: #111;
			padding: 3px 14px;
			position: relative;
		}
		.ls > tbody > tr > th.sortable:hover {
			border-bottom: #999 1px solid;
		}
		.ls > tbody > tr > th.sortable > a:hover {
			text-decoration: none;
			background: -webkit-linear-gradient(#fff, #CDEFFB);
			background: -moz-linear-gradient(#fff, #bfbfbf);
			background: -o-linear-gradient(#fff, #bfbfbf);
			filter: progid:dximagetransform.microsoft.gradient(startcolorstr=#ffffff, endcolorstr=#bfbfbf);
			color: #000;
		}
		.ls > tbody > tr > th.sortable > a:active {
			text-decoration: none;
			background: -webkit-linear-gradient(#CDEFFB, #fff);
			background: -moz-linear-gradient(#eee, #fff);
			background: -o-linear-gradient(#eee, #fff);
			filter: progid:dximagetransform.microsoft.gradient(startcolorstr=#eeeeee, endcolorstr=#ffffff);
			color: #000;
		}


		.ls > tbody > tr > th.sortable > a > span.uparrow1 {
			position: absolute;
			width: 16px; height: 16px;
			top: 2px; left: -1px;
			background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPNJREFUeNpi/P//PwMlgImBQjDwBrCgCxj1XGfg4OZmYGNnj2FgZCxg+P9/wq+fP5f8+PqV4VyJJnEuAAZsDFBTQZS7mDGIBvGJ9gJI8c9v3wri/OWMX/1kYIjxkzMG8XEZgmEA0KkFceGaxvuP32d49p2B4eCJ+wwhIZrGIHGiwuD71y9n+yfsZXj79h2Dk4Ki8b7NZ86eOHaPgZGJ6SxRBvz/9y/9989fDJysrGfeAr0ApBmAfBNWdjbiYuHbp89AV3wFs3/9ZwCzQZgTGDNEGfDx7VtYaJ69uPesFMP372eB0cnw68cPrAYwjuYFBoAAAwCwH3kFP+QZjgAAAABJRU5ErkJggg==") no-repeat;
		}
		.ls > tbody > tr > th.sortable > a > span.downarrow1 {
			position: absolute;
			width: 16px; height: 16px;
			top: 2px; right: -2px;
			background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA40lEQVR42mNkoBAwDnMDWANmz2QwNPRlOH9+8+8NqekkG8AdueSMZna08fWpS89+XR5jQrIBIinrz6glBhjfmr/h7Js5gcQbIF+yb+b/f/+M3759x+CUG2q8b/Lqs8LCQgyMTExnH/Y4pRM0QKP+1JnYBFPji2fvMwhrKjK8vX6fwdJMkWHm3NNnbzSamRA0wKDragwjI2NBSqSm8btfQK+wMzDMWnb97P///ydcKNNeQlQYWEx9ADYkwV/OeMHGR2DNJ7IVlqCrwxuI9nNfxDAADWEAaj6YLLEEm5pBnhLpYgAAn+ZVERqSnwgAAAAASUVORK5CYII=") no-repeat;
		}
		.ls > tbody > tr > th.sortable > a > span.uparrow2 {
			position: absolute;
			width: 16px; height: 16px;
			top: 2px; left: -1px;
			background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEdSURBVHjaYvz//z8DJQAggJgYKAQAAUSxAQABRLEBAAHEgi7Q0tLCwM3NzcDGxhbDyMhY8O/fvwm/fv1a8u3bN4aamhoMAwACCKsLgAEb8/PnzwInJydjoOYCEB+XCwACCJsBMUDbCry9vY2BhjB4eXkZ//jxowAkjs0AgADCMACkOTAw0PjkyZMgNgOI9vHxMQaJYzMAIIBYsBhwdubMmQzv379nkJaWNt6zZ89ZIGBgYmI6i80AgADCMAAYaOkgp7Oysp6B0gzAcDABBirWMAAIIAwDvnz5AnY6NDDB7O/fvzNwcnJiNQAggDAM+PDhA8wlZ48ePSoFDMCzQOczgFyDDQAEECOleQEggChOiQABRLEBAAFEsQEAAQYAUQR6EOOFIlQAAAAASUVORK5CYII=") no-repeat;
		}
		.ls > tbody > tr > th.sortable > a > span.downarrow2 {
			position: absolute;
			width: 16px; height: 16px;
			top: 2px; right: -2px;
			background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAETSURBVHjaYvz//z8DJQAggJgYKAQAAUSxAQABRLEBAAHEgk2QkZERTPv7+8/U0tLyvX79+uYNGzakg8TQwwwggPC6gIODw9ja2lqSnZ3dGJcagADCawAXFxfYNSAaFwAIIKxeyM/Pn/nv3z/jDx8+MLCxsTH8/v2bITc39wzQsLNA6XRktQABxILDZuOIiAjjCxcugG13dXU1BgKGpUuXYqgFCCCsXuDk5JywefPms6ampmADzMzMGNavX38WyJ6ArhYggBixpUSQv3t7e2OAzAJvb2/jrVu3gpw+obi4eAm6eoAAwmkACEyZMiUGyC4AqpmQk5OzBFs0AgQQI6V5ASCAKE6JAAFEsQEAAUSxAQABBgBLiE3/WjHs8wAAAABJRU5ErkJggg==") no-repeat;
		}


		.ls > tbody > tr > td {
			border-bottom: #e7e7e7 1px solid;
			background: #fff; /* has to be cause box-shadow might make it gray sometimes - bug */
		}
		.ls > tbody > tr:nth-of-type(odd) > td {
			background: -webkit-linear-gradient(#f9f9f9, #f0f0f0);
			background: -moz-linear-gradient(#f9f9f9, #f0f0f0);
			background: -o-linear-gradient(#f9f9f9, #f0f0f0);
			filter: progid:dximagetransform.microsoft.gradient(startcolorstr=#f9f9f9, endcolorstr=#f0f0f0);
		}

		.ls > tbody > tr > th,
		.ls > tbody > tr > td { border-right: #fff 1px solid; }
		.ls > tbody > tr > th:last-child,
		.ls > tbody > tr > td:last-child { border-right: none; }

		.ls > tbody > tr > th > a {
			display: block;
		}

		.ls > tbody > tr > th {
			border-right: #ccc 1px solid;
		}
		.ls > tbody > tr > th:first-child {
			border-top-left-radius: 5px;
			border-left: #ccc 1px solid;
		}
		.ls > tbody > tr > th:last-child {
			border-top-right-radius: 5px;
			border-right: #ccc 1px solid;
		}


		.ls > tbody > tr > td:first-child {
			border-left: #ddd 1px solid;
		}
		.ls > tbody > tr > td:last-child {
			border-right: #ddd 1px solid;
		}
		.ls > tbody > tr:last-child > td {
			border-bottom: #ddd 1px solid;
			padding-bottom: 3px;
		}


		.ls > tbody > tr:last-child > td:first-child {
			border-bottom-left-radius: 5px;
		}
		.ls > tbody > tr:last-child > td:last-child {
			border-bottom-right-radius: 5px;
		}

		.ls > tbody > tr > td.next_marked {
			border-bottom: #aaa 1px solid;
		}
		.ls > tbody > tr > td.marked {
			background: #ddd;
			border-bottom: #aaa 1px solid;
			background: -webkit-linear-gradient(#eee, #ddd);
			background: -moz-linear-gradient(#eee, #ddd);
			background: -o-linear-gradient(#eee, #ddd);
			filter: progid:dximagetransform.microsoft.gradient(startcolorstr=#eeeeee, endcolorstr=#dddddd);
		}

		.tables > tbody > tr > td:first-child {

		}


		/* @ls2 */

		.ls2 th { background: #ccc; }
		.ls2 th th { background-color: none; }
		.ls2 td { background: #f5f5f5; }
		.ls2 td td { background-color: none; }
		.ls2 th, .ls2 td { padding: 0.1em 0.5em; }
		.ls2 th th, .ls2 td td { padding: 0; }
		.ls2 th { text-align: left; vertical-align: top; line-height: 1.7em; background: #e0e0e0; font-weight: normal; }
		.ls2 th th { line-height: normal; background-color: none; }
		.ls2 .none { background: none; padding-top: 0.4em; }

		div.poweredby {	}

		/* @tooltip */

		div.tooltip {
			background: #fff;
			padding: 0.75em 1em;
			font: 11px Tahoma; line-height: 1.4em;
			border: 1px solid #bbb;
			border-radius: 4px;
			box-shadow: 1px 1px 8px #ccc;
			opacity: 0;
			-webkit-transition: opacity 200ms ease-in;
			-moz-transition: opacity 200ms ease-in;
			-o-transition: opacity 200ms ease-in;
			-ms-transition: opacity 200ms ease-in;
			transition: opacity 200ms ease-in;
		}

		/* @help */

		.help {
			width: 15px; height: 15px;
			background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAAAAXNSR0IArs4c6QAAAAlwSFlzAAALEwAACxMBAJqcGAAAAbJJREFUKFNtk79rFEEUxz9zu1GjnAcWFv4GUxwWEkuVNJFoShs5ECKIKdJbBDlLBcFUFoY0KRSEgP+ACAch3VVphJNESUQNhCRmPXPr7uzMs1h2nL3Lt5mZN3y+82beG0WfRET6Y4WUUspfh/5CROTOoya3r11wsc1fGoDPG1uIiPgGDhYRmZqd49XLZ0RJvv9tXzMcVwCoA1ennpcMQh98OvuYxfYevhJtAEh1xuR0s2QQiojcaszwen6exfYeiTZcP3+UxmjNGSytRrTWUqLt70xONzk7dh8REZf2l50EAGMsjdEaS6sRy18PMMaycO8cHzr7DAUBAPUzJwGo4KlI0Qd9aWP4k/4vRum1jbEYa0snArxp76DTjCDMz+r8/D0Ipzpz85sXjznwYyfiMDm4ZwK0MQwF+dha75YgbcwA7O68HVfoxQnaGKy1jI9UefvgMhP1mgPj5G8JVpDXeezuQ27MzLG5tUuWaQ7T6UtXaC08ofP+BUopFULes75BNzhF76BbAo+fqDrQ2rwKpUYvDKLqCABpnBscGa6yu/GJHyvvsNYSBIEagAuD/lih/l/1D+7b7TstfO0wAAAAAElFTkSuQmCC") no-repeat;
			display: inline-block;
			margin-bottom: -3px;
		}
		.help:hover {
			background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAAAAXNSR0IArs4c6QAAAAlwSFlzAAALEwAACxMBAJqcGAAAAa5JREFUKFNtk71rFEEYxn+ztxya00AwQUGJhVhoGq0Ei1jYKFhGQUhxVUCIIBjBwr9AtFSEKwSTLqCFRSrRWCmICCLx64oLNibEYC57H3s781hsbp255Glm3nfm98zXO4YBSdJgri9jjPHj2A8k6fD9Ra5NDBe5j1ne//ntF5LkGxSwJJ1+9oa3s1P8zfLxRmI51duZe/Yc++8uBgaxD76YusCTlW18dTObtz3LzemroYEkjd57rK+J060PW7rxblPzjUS+5huJqstrqi6v6c5nyVy5LUkqtv29mQFgnZgeH2JhtcXr1TbOOZ5OjrFUb1IuRQAcOnMCgGivLfqgr9Q6kuz/YwS3bZ3IrAtWBKjVm7S7olzO5218qu+G015W9M8f3VeASz8S9lIBd1xEah3lUt6+aiQB1HIRpOExijOv9wwbbUNqHVbi4vEKzy8d4fLJSg4Cf7LgijCQv/PI3EOq12dY+d2mEy5QaOLYGLVajfTRDMYYE0Nes75BWjrAVtIKwOHKUAHanfIPCr1v0InHAbDNTQBKB0fofnmPe/kAKxFHkdkF9w0Gc30N/qp/BbAB6d6RyA0AAAAASUVORK5CYII=") no-repeat;
		}


		</style>

		<script>

		// @scripts
		// @js

		function Element(id)
		{
			if (typeof id == "string") {
				return document.getElementById(id);
			} else {
				return id;
			}
		}
		function sprintf(text){
		    var i=1, args=arguments;
		    return text.replace(/%s/g, function(pattern){
			return (i < args.length) ? args[i++] : "";
		    });
		}

		// @popup

		function popup(url, width, height, more)
		{
			if (!width) width = <?php echo SQL_POPUP_WIDTH; ?>;
			if (!height) height = <?php echo SQL_POPUP_HEIGHT; ?>;
			var x = (screen.width/2-width/2);
			var y = (screen.height/2-height/2);
			window.open(url, "", "scrollbars=yes,resizable=yes,width="+width+",height="+height+",screenX="+(x)+",screenY="+y+",left="+x+",top="+y+(more ? ","+more : ""));
		}

		// @noreferer

		function link_noreferer(link)
		{
			// Tested: Chrome, Firefox, Inetrnet Explorer, Opera.
			var w = window.open("about:blank", "_blank");
			w.document.open();
			w.document.write("<"+"!doctype html>");
			w.document.write("<"+"html><"+"head>");
			w.document.write("<"+"title>Secure redirect</title>");
			w.document.write("<"+"style>");
			w.document.write("body { font: 11px Tahoma; line-height: 1.4em; margin: 1em 1.5em; }");
			w.document.write("h1 { font: bold 15px Tahoma; color: #000; text-shadow: 1px 1px 1px #fff; }");
			w.document.write("h1 a { font: normal 13px Tahoma; } h1 small { font: normal 11px Tahoma; }");
			w.document.write("a, a:visited { color: rgb(0,110,255); display: inline-block; margin-top: 0.25em; }");
			w.document.write("<"+"/style>");
			w.document.write("<"+"meta http-equiv=refresh content='10;url="+link+"'>");
			// Meta.setAttribute() doesn't work on firefox.
			// Firefox: needs document.write('<meta>')
			// IE: the firefox workaround doesn't work on ie, but we can use a normal redirection
			//      as IE is already not sending the referer because it does not do it when using
			//      open.window, besides the blank url in address bar works fine (about:blank).
			// Opera: firefox fix works.
			w.document.write("<"+"script>var xcount=10; var xtimer=null; function counter(){ --xcount; if (xcount < 0) return; document.getElementById('xseconds').innerHTML = xcount; } xtimer = setInterval(counter, 1000);<"+"/script>");
			w.document.write("<"+"script>function redirect() { clearInterval(xtimer); document.getElementById('xseconds').innerHTML = '0'; if (navigator.userAgent.indexOf('MSIE') != -1) { location.replace('"+link+"'); } else { document.open(); document.write('<"+"meta http-equiv=refresh content=\"0;"+link+"\">'); document.close(); } }<"+"/script>");
			w.document.write("<"+"/head><"+"body>");
			w.document.write("<"+"h1>Secure redirect to:<br><a href=\"javascript:;\" onclick=\"redirect()\">"+link+"</a> <br><small>(safe to click)</small><"+"/h1>");
			w.document.write("<"+"p>This is a secure redirect that hides the HTTP REFERER header.");
			w.document.write("<br>The site you are being redirected won't know the location of the dbkiss script.");
			w.document.write("<br>In <b id=xseconds>10</b> seconds you will be redirected to that address. ");
			w.document.write("<"+"/body><"+"/html>");
			w.document.close();
		}

		/* @tooltip */

		var Tooltip_Div = null;
		var Tooltip_OverElem = null;
		if (document.addEventListener) {
			document.addEventListener('click', Tooltip_Hide, false);
		} else {
			document.attachEvent("onclick", Tooltip_Hide);
		}


		function Tooltip(overElem, textOrId, isPlaintext)
		{
			Tooltip_OverElem = overElem;
			Tooltip_Hide();

			var text = textOrId;

			if (text.length < 30 && document.getElementById(text)) {
				// Instead of text we can pass and id of the element that contains the text.
				text = document.getElementById(text).innerHTML;
			}

			var div = document.createElement('DIV');
			div.className = 'tooltip';

			if (isPlaintext) {
				div.innerHTML = text.replace(/\n/g, "<br>");
			} else {
				div.innerHTML = text;
			}

			var top = 0;
			var left = 0;

			var rects = overElem.getClientRects();
			if (rects.length) {
				top = rects[0].top + window.pageYOffset;
				left = rects[0].right + 10 + window.pageYOffset;
			}

			div.style.position = 'absolute';
			div.style.top = top+'px';
			div.style.left = left+'px';

			Tooltip_Div = div;
			document.body.appendChild(div);

			window.setTimeout(function(){
				div.style.opacity = 1;
			}, 13);
		}
		function Tooltip_Hide(event)
		{
			if (event && event.target == Tooltip_OverElem) {
				return;
			}
			if (Tooltip_Div && Tooltip_Div.parentNode) {
				var div = Tooltip_Div;
				div.style.opacity = 0;
				window.setTimeout(function(){
					document.body.removeChild(div);
				}, 500);
			}
			Tooltip_Div = null;
		}

		// @mark_row

		function mark_row(tr, event)
		{
			// User might be selecting text, but onclick="" event will be also fired.
			// We detect whether user is selecting or unselecting, in that case we do not mark the row.
			var selection = window.getSelection();
			if (selection.rangeCount > 0) {
				// Range - making selection in row.
				// None - unselecting text.
				// Caret - normal click on a row.
				if (selection.type == "Range" || selection.type == "None") {
					return;
				}
			}

			var event = event ? event : window.event;
			if (event && event.target && "TD" != event.target.tagName) {
				// So that clicking "Edit" or a parsed link does not mark the row.
				return 0;
			}

			var els = tr.getElementsByTagName('td');
			if (tr.marked) {
				for (var i = 0; i < els.length; i++) {
					els[i].className = els[i].className.replace(/\bmarked\b/, "");
				}
				els = null;
				tr.marked = false;

				var prev_td = tr.previousElementSibling.firstElementChild;
				if (prev_td.innerText != "#") {
					if (!/\bmarked\b/.test(prev_td)) {
						prev_els = tr.previousElementSibling.getElementsByTagName("td");
						for (var i = 0; i < prev_els.length; i++) {
							prev_els[i].className = prev_els[i].className.replace(/\bnext_marked\b/, "");
						}
					}
				}
			} else {
				for (var i = 0; i < els.length; i++) {
					els[i].className += " marked";
				}
				tr.marked = true;
				els = null;

				var prev_td = tr.previousElementSibling.firstElementChild;
				if (prev_td.innerText != "#") {
					if (!/\bmarked\b/.test(prev_td)) {
						prev_els = tr.previousElementSibling.getElementsByTagName("td");
						for (var i = 0; i < prev_els.length; i++) {
							prev_els[i].className += " next_marked";
						}
					}
				}
			}
		}

		var IS_CTRL = 0;

		if (document.addEventListener) {
			document.addEventListener("keydown", function(event){
				if (17 == event.keyCode) {
					IS_CTRL = 1;
				}
			}, false);
		} else {
			document.attachEvent("onkeydown", function(event){
				event = event ? event : window.event;
				if (17 == event.keyCode) {
					IS_CTRL = 1;
				}
			});
		}

		if (document.addEventListener) {
			document.addEventListener("keyup", function(event){
				if (17 == event.keyCode) {
					IS_CTRL = 0;
				}
			}, false);
		} else {
			document.attachEvent("onkeyup", function(event){
				event = event ? event : window.event;
				if (17 == event.keyCode) {
					IS_CTRL = 0;
				}
			});
		}

		function ElementPosition(elem, event)
		{
			// You can pass any "event", it does not have to be related
			// with given element, it is only required because mouse position
			// can be fetched only from event object and firefox does not
			// support "window.event".

			var rect = elem.getBoundingClientRect();

			var pageX = rect.left + window.pageXOffset;
			var pageY = rect.top + window.pageYOffset;

			var mouseX = -1;
			var mouseY = -1;
			if (event) {
				if (event.target === elem && "offsetX" in event) {
					// Firefox does not support "offsetX".
					mouseX = event.offsetX;
					mouseY = event.offsetY;
				} else {
					mouseX = event.pageX - pageX;
					mouseY = event.pageY - pageY;
				}
			}

			var hasMouse = false;
			if (mouseX >= 0 && mouseX <= rect.width	&& mouseY >= 0 && mouseY <= rect.height) {
				hasMouse = true;
			}

			var ret = {
				"screenX": rect.left, // x-coordinate relative to the top-left corner of the screen.
				"screenY": rect.top,
				"pageX": pageX, // x-coordinate relative to the top-left corner of the browser window's client area.
				"pageY": pageY,
				"width": rect.width,
				"height": rect.height,
				"mouseX": mouseX, // mouse X coordinate relative to current element, -1 when hasMouse=false and no event passed.
				"mouseY": mouseY,
				"hasMouse": hasMouse
			};

			return ret;
		}
		function ElementSide(elem, event)
		{
			// Which side of element mouse points at? Left, Right, Up or Down?
			pos = ElementPosition(elem, event);
			halfWidth = Math.floor(pos.width / 2);
			halfHeight = Math.floor(pos.height / 2);
			var left = false;
			var right = false;
			if (pos.mouseX > halfWidth) { right = true; }
			else { left = true; }
			return {
				"left": left,
				"right": right
			};
		}
		function Sort_Mouseover(target, event)
		{
			var side = ElementSide(target, event);
			var match, arrow1;
			if (match = target.innerHTML.match(/class="?((up|down)arrow1)"?/i)) {
				arrow1 = match[1];
			}
			var className;
			if (arrow1) {
				className = (arrow1 == "uparrow1") ? "downarrow2" : "uparrow2";
			} else {
				className = side.left ? "uparrow2" : "downarrow2";
			}
			var className_existing = "";
			var match;
			if (match = target.innerHTML.match(/class="?((up|down)arrow2)"?/i)) {
				className_existing = match[1];
			}
			if (!className_existing || (className_existing && className_existing != className)) {
				if (className_existing && className_existing != className) {
					// Special case: always remove arrow2 (see 3rd param "forceRemove")
					Sort_Mouseout(target, event, true);
				}
				var span = document.createElement("span");
				span.className = className;
				var parent = target;
				span.onmouseover = function(event){ Sort_Mouseover(parent, event); };
				span.onmouseout = function(event){ Sort_Mouseout(parent, event); };
				span.onmousedown = function(event){ Sort_Click(parent, event); };
				target.appendChild(span);
			}
		}
		function Sort_Mouseout(target, event, forceRemove)
		{
			target.innerHTML = target.innerHTML.replace(/\s*<span[^<>]+class="?(up|down)arrow2"?[^<>]*><\/span>/i, "");
		}
		function Sort_Mousemove(target, event)
		{
			Sort_Mouseover(target, event);
		}
		function Sort_Click(target, event)
		{
			console.log("Sort_Click", target, event);
			var side = ElementSide(target, event);
			var link = target.getAttribute("mylink");
			if (!link) {
				console.log(link, target, event);
			}
			if (target.innerHTML.match(/class="?((up|down)arrow1)"?/i)) {
				link = link;
			} else {
				if (side.left) {
					link = link.replace(/(order_desc=)\d+/, function(m0,m1){ return m1+"0"; });
				} else {
					link = link.replace(/(order_desc=)\d+/, function(m0,m1){ return m1+"1"; });
				}
			}
			window.location.href = link;
		}

		</script>

		<!-- @keys. Keyboard shortcuts - not doing anything, just so that Chrome does not make that annoying Ding sound. -->

		<a accesskey=q href="javascript:;"></a>
		<a accesskey=z href="javascript:;"></a>
		<a accesskey=s href="javascript:;"></a>

	<?php
}

// @rawlayout
function rawlayout_start($title='')
{
	// @layout
	// Used in: Edit row, SQL Editor, SQL Popup, Search.

	global $page_charset;
	$flash = flash();
	?>

	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $page_charset;?>">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo $title;?></title>
		<link rel="shortcut icon" href="<?php echo $_SERVER['PHP_SELF']; ?>?dbkiss_favicon=1">
	</head>
	<body>

	<?php layout(); ?>

	<?php if ($flash) { echo $flash; } ?>

	<?php
}
function rawlayout_end()
{
	// @layout
	// Used in: Edit row, SQL Editor, SQL Popup, Search.

	?>
	</body>
	</html>
	<?php
}
function conn_info()
{
	// @conn_info
	// @connection
	// @info

	global $db_driver, $db_server, $db_name, $db_user, $db_charset, $page_charset, $charset1, $charset2;
	$dbs = list_dbs();
	$db_name = $db_name;
	?>
	<p>
		<?php if (SQLITE_USED): ?>

			<?php
				$db_file = realpath($db_server);
				$base = basename($db_file);
				$db_file = substr($db_file, 0, strlen($db_file) - strlen($base));
				$db_file = "<b style=\"cursor: help;\" title=\"Located in: $db_file\">$base</b>";
			?>

			Database: <?php echo $db_file; ?>
			&nbsp;-&nbsp;

			<!--
			<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?execute_sql=1">SQL Editor</a>
			&nbsp;-&nbsp;
			-->

			<a class=blue href="javascript:void(0)" onclick="popup('<?php echo $_SERVER['PHP_SELF'];?>?execute_sql=1&popup=1')">SQL Editor</a>
			&nbsp;-&nbsp;

			<!--
			Charset: <b>UTF8</b>
			&nbsp;-&nbsp;
			-->

			<?php if (defined("SQLITE_INSECURE")): ?>
				User: <b>No authentication</b>
			<?php else: ?>
				User: <b><?php echo $db_user; ?></b>
			<?php endif; ?>



			<?php if (!defined("SQLITE_INSECURE")): ?>
				&nbsp;-&nbsp;
				<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?disconnect=1">Disconnect</a>
			<?php endif; ?>

		<?php else: ?>

			Driver: <b><?php echo $db_driver;?></b>
			&nbsp;-&nbsp;

			Server: <b><?php echo $db_server;?></b>
			&nbsp;-&nbsp;

			User: <b><?php echo $db_user;?></b>
			&nbsp;-&nbsp;

			<!--
			<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?execute_sql=1">SQL Editor</a>
			&nbsp;-&nbsp;
			-->

			<a class=blue href="javascript:void(0)" onclick="popup('<?php echo $_SERVER['PHP_SELF'];?>?execute_sql=1&popup=1')">SQL Editor</a>
			&nbsp;-&nbsp;

			Data<u>b</u>ase: <select accesskey=b id="db_name" name="db_name" onchange="location='<?php echo $_SERVER['PHP_SELF'];?>?db_name='+this.value"><?php echo options($dbs, $db_name);?></select>
			&nbsp;-&nbsp;

			Db charset: <select name="db_charset" onchange="location='<?php echo $_SERVER['PHP_SELF'];?>?db_charset='+this.value+'&from=<?php echo urlencode($_SERVER['REQUEST_URI']);?>'">
			<option value=""></option><?php echo options($charset1, $db_charset);?></select>
			&nbsp;-&nbsp;

			Page charset: <select name="page_charset" onchange="location='<?php echo $_SERVER['PHP_SELF'];?>?page_charset='+this.value+'&from=<?php echo urlencode($_SERVER['REQUEST_URI']);?>'">
			<option value=""></option><?php echo options($charset2, $page_charset);?></select>
			&nbsp;-&nbsp;

			<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?disconnect=1">Disconnect</a>

		<?php endif; ?>

		<!--
		<div style="position: absolute; top: 1em; right: 1.5em;">
		DBKiss version: <a title="Check for updates" href="javascript:void(0)" onclick="link_noreferer('http://www.gosu.pl/dbkiss/')"><?php echo DBKISS_VERSION; ?></a>
		</div>
		-->

		<div style="position: absolute; top: 1em; right: 1.5em;">
			DBKiss version: <b><?php echo DBKISS_VERSION; ?></b>
			&nbsp;
			<a class=special href="javascript:void(0)" onclick="link_noreferer('http://www.gosu.pl/dbkiss/')">Check for updates</a>
		</div>

	</p>
	<?php
}

// @files

function size($bytes)
{
	return number_format(ceil($bytes / 1024),0,'',' ').' k';
}
function file_ext($name)
{
	$ext = null;
	if (($pos = strrpos($name, '.')) !== false) {
		$len = strlen($name) - ($pos+1);
		$ext = substr($name, -$len);
		if (!preg_match('#^[a-z0-9]+$#i', $ext)) {
			return null;
		}
	}
	return $ext;
}
function file_put($file, $s)
{
	// file_put_contents is not supported in 4.3
	$fp = fopen($file, 'wb') or trigger_error('fopen() failed: '.$file, E_USER_ERROR);
	if ($fp) {
		fwrite($fp, $s);
		fclose($fp);
	}
}
function file_date($file)
{
	return date('Y-m-d H:i:s', filemtime($file));
}
function dir_exists($dir)
{
	return file_exists($dir) && !is_file($dir);
}
function dir_read($dir, $ignore_ext = array(), $allow_ext = array(), $sort = null)
{
	if (is_null($ignore_ext)) $ignore_ext = array();
	if (is_null($allow_ext)) $allow_ext = array();
	foreach ($allow_ext as $k => $ext) {
		$allow_ext[$k] = str_replace('.', '', $ext);
	}

	$ret = array();
	if ($handle = opendir($dir)) {
		while (($file = readdir($handle)) !== false) {
			if ($file != '.' && $file != '..') {
				$ignore = false;
				foreach ($ignore_ext as $ext) {
					if (file_ext_has($file, $ext)) {
						$ignore = true;
					}
				}
				if (is_array($allow_ext) && count($allow_ext) && !in_array(file_ext($file), $allow_ext)) {
					$ignore = true;
				}
				if (!$ignore) {
					$ret[] = array(
						'file' => $dir.'/'.$file,
						'time' => filemtime($dir.'/'.$file)
					);
				}
			}
		}
		closedir($handle);
	}
	if ('date_desc' == $sort) {
		$ret = array_sort_desc($ret, 'time');
	}
	return array_col($ret, 'file');
}
function sql_files()
{
	$files = dir_read('.', null, array('.sql'));
	$files2 = array();
	foreach ($files as $file) {
		$files2[md5($file)] = $file.sprintf(' (%s)', size(filesize($file)));
	}
	return $files2;
}
function sql_files_assoc()
{
	$files = dir_read('.', null, array('.sql'));
	$files2 = array();
	foreach ($files as $file) {
		$files2[md5($file)] = $file;
	}
	return $files2;
}

// @array

function array_assoc($a)
{
	$ret = array();
	foreach ($a as $v) {
		$ret[$v] = $v;
	}
	return $ret;
}
function array_col($arr, $col)
{
	$ret = array();
	foreach ($arr as $k => $row) {
		$ret[] = $row[$col];
	}
	return $ret;
}
function array_sort_desc($arr, $col_key)
{
	if (is_array($col_key)) {
		foreach ($arr as $k => $v) {
			$arr[$k]['__array_sort'] = '';
			foreach ($col_key as $col) {
				$arr[$k]['__array_sort'] .= $arr[$k][$col].'_';
			}
		}
		$col_key = '__array_sort';
	}
	uasort($arr, create_function('$a,$b', 'return strnatcasecmp($b["'.$col_key.'"], $a["'.$col_key.'"]);'));
	if ('__array_sort' == $col_key) {
		foreach ($arr as $k => $v) {
			unset($arr[$k]['__array_sort']);
		}
	}
	return $arr;
}
function array_first_key($arr)
{
	$arr2 = $arr;
	reset($arr);
	list($key, $val) = each($arr);
	return $key;
}
function array_first($arr)
{
	$arr2 = $arr;
	return array_shift($arr2);
}
function array_col_values($arr, $col)
{
	$ret = array();
	foreach ($arr as $k => $row) {
		$ret[] = $row[$col];
	}
	return $ret;
}
function array_col_values_unique($arr, $col)
{
	return array_unique(array_col_values($arr, $col));
}
function array_col_match($rows, $col, $pattern)
{
	if (!count($rows)) {
		trigger_error('array_col_match(): array is empty', E_USER_ERROR);
	}
	$ret = true;
	foreach ($rows as $row) {
		if (!preg_match($pattern, $row[$col])) {
			return false;
		}
	}
	return true;
}
function array_col_match_unique($rows, $col, $pattern)
{
	if (!array_col_match($rows, $col, $pattern)) {
		return false;
	}
	return count($rows) == count(array_col_values_unique($rows, $col));
}

// @url

function self($cut_query = false)
{
	$uri = $_SERVER['REQUEST_URI'];
	if ($cut_query) {
		$before = str_before($uri, '?');
		if ($before) {
			return $before;
		}
	}
	return $uri;
}
function url($script, $params = array())
{
	$query = '';

	/* remove from script url, actual params if exist */
	foreach ($params as $k => $v) {
		$exp = sprintf('#(\?|&)%s=[^&]*#i', $k);
		if (preg_match($exp, $script)) {
			$script = preg_replace($exp, '', $script);
		}
	}

	/* repair url like 'script.php&id=12&asd=133' */
	$exp = '#\?\w+=[^&]*#i';
	$exp2 = '#&(\w+=[^&]*)#i';
	if (!preg_match($exp, $script) && preg_match($exp2, $script)) {
		$script = preg_replace($exp2, '?$1', $script, 1);
	}

	foreach ($params as $k => $v) {
		if (!strlen($v)) continue;
		if ($query) { $query .= '&'; }
		else {
			if (strpos($script, '?') === false) {
				$query .= '?';
			} else {
				$query .= '&';
			}
		}
		if ('%s' != $v) {
			$v = urlencode($v);
		}
		$v = preg_replace('#%25(\w+)%25#i', '%$1%', $v); // %id_news% etc. used in listing
		$query .= sprintf('%s=%s', $k, $v);
	}
	return $script.$query;
}
function url_offset($offset, $params = array())
{
	$url = $_SERVER['REQUEST_URI'];
	if (preg_match('#&offset=\d+#', $url)) {
		$url = preg_replace('#&offset=\d+#', '&offset='.$offset, $url);
	} else {
		$url .= '&offset='.$offset;
	}
	return $url;
}

// @time

function time_micro()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
function time_start()
{
	return time_micro();
}
function time_end($start)
{
	$end = time_micro();
	$end = round($end - $start, 3);
	$end = pad_zeros($end, 3);
	return $end;
}
function pad_zeros($number, $zeros)
{
	if (strstr($number, '.')) {
		preg_match('#\.(\d+)$#', $number, $match);
		$number .= str_repeat('0', $zeros-strlen($match[1]));
		return $number;
	} else {
		return $number.'.'.str_repeat('0', $zeros);
	}
}

// @string

function str_has_any($str, $arr_needle, $ignore_case = false)
{
	if (is_string($arr_needle)) {
		$arr_needle = preg_replace('#\s+#', ' ', $arr_needle);
		$arr_needle = explode(' ', $arr_needle);
	}
	foreach ($arr_needle as $needle) {
		if (str_has($str, $needle, $ignore_case)) {
			return true;
		}
	}
	return false;
}
function str_before($str, $needle)
{
	$pos = strpos($str, $needle);
	if ($pos !== false) {
		$before = substr($str, 0, $pos);
		return strlen($before) ? $before : false;
	} else {
		return false;
	}
}
function ConvertPolishToUTF8($String)
{
	// @polish

	// Converts "windows-1250" and "iso-8859-2" chars to UTF-8 equivalent.
	$ReplacePairs = array(
		"\xb9" => "\xc4\x85", "\xa5" => "\xc4\x84", "\xe6" => "\xc4\x87", "\xc6" => "\xc4\x86", "\xea" => "\xc4\x99", "\xca" => "\xc4\x98",
		"\xb3" => "\xc5\x82", "\xa3" => "\xc5\x81", "\xf1" => "\xc5\x84", "\xd1" => "\xc5\x83", "\xf3" => "\xc3\xb3", "\xd3" => "\xc3\x93",
		"\x9c" => "\xc5\x9b", "\x8c" => "\xc5\x9a", "\x9f" => "\xc5\xba", "\x8f" => "\xc5\xb9", "\xbf" => "\xc5\xbc", "\xaf" => "\xc5\xbb",
		"\xb1" => "\xc4\x85", "\xa1" => "\xc4\x84", "\xe6" => "\xc4\x87", "\xc6" => "\xc4\x86", "\xea" => "\xc4\x99", "\xca" => "\xc4\x98",
		"\xb3" => "\xc5\x82", "\xa3" => "\xc5\x81", "\xf1" => "\xc5\x84", "\xd1" => "\xc5\x83", "\xf3" => "\xc3\xb3", "\xd3" => "\xc3\x93",
		"\xb6" => "\xc5\x9b", "\xa6" => "\xc5\x9a", "\xbc" => "\xc5\xba", "\xac" => "\xc5\xb9", "\xbf" => "\xc5\xbc", "\xaf" => "\xc5\xbb"
	);
	// Cannot use str_replace() cause it replaces the replaced matches if the new string is longer, and UTF-8
	// consists of 2 chars so we must use strtr that does not replace stuff that it already has worked on.
	return strtr($String, $ReplacePairs);
}

// @error

global $_error, $_error_style;
$_error = array();
$_error_style = '';

function error($msg = null)
{
	if (isset($msg) && func_num_args() > 1) {
		$args = func_get_args();
		$msg = call_user_func_array('sprintf', $args);
	}
	global $_error, $_error_style;
	if (isset($msg)) {
		$_error[] = $msg;
	}
	if (!count($_error)) {
		return null;
	}
	if (count($_error) == 1) {
		return sprintf('<div class="error" style="%s">%s</div>', $_error_style, $_error[0]);
	}
	$ret = '<div class="error" style="'.$_error_style.'">Following errors appeared:<ul>';
	foreach ($_error as $msg) {
		$ret .= sprintf('<li>%s</li>', $msg);
	}
	$ret .= '</ul></div>';
	return $ret;
}

// @date

function timestamp($time, $span = true)
{
	$time_base = $time;
	$time = substr($time, 0, 16);
	$time2 = substr($time, 0, 10);
	$today = date('Y-m-d');
	$yesterday = date('Y-m-d', time()-3600*24);
	if ($time2 == $today) {
		if (substr($time_base, -8) == '00:00:00') {
			$time = 'Today';
		} else {
			$time = 'Today'.substr($time, -6);
		}
	} else if ($time2 == $yesterday) {
		$time = 'Yesterday'.substr($time, -6);
	}
	return '<span style="white-space: nowrap;">'.$time.'</span>';
}

// @redirect

function redirect($url)
{
	$url = url($url);
	header("Location: $url");
	exit;
}
function redirect_notify($url, $msg)
{
	if (strpos($msg, '<') === false) {
		$msg = sprintf('<b>%s</b>', $msg);
	}
	cookie_set('flash_notify', $msg);
	redirect($url);
}
function redirect_ok($url, $msg)
{
	if (strpos($msg, '<') === false) {
		$msg = sprintf('<b>%s</b>', $msg);
	}
	cookie_set('flash_ok', $msg);
	redirect($url);
}
function redirect_error($url, $msg)
{
	if (strpos($msg, '<') === false) {
		$msg = sprintf('<b>%s</b>', $msg);
	}
	cookie_set('flash_error', $msg);
	redirect($url);
}

// @flash

function flash()
{
	static $is_style = false;

	$flash_error = cookie_get('flash_error');
	$flash_ok = cookie_get('flash_ok');
	$flash_notify = cookie_get('flash_notify');

	if (!($flash_error || $flash_ok || $flash_notify)) {
		return false;
	}

	ob_start();
	?>

	<?php if (!$is_style): ?>
		<style type="text/css">
		#flash { background: #ffffd7; padding: 0.3em; padding-bottom: 0.15em; border: #ddd 1px solid; margin-bottom: 1em; }
		#flash div { padding: 0em 0em; }
		#flash table { font-weight: normal; }
		#flash td { text-align: left; }
		</style>
	<?php endif; ?>

	<div id="flash" ondblclick="document.getElementById('flash').style.display='none';">
		<table width="100%" ondblclick="document.getElementById('flash').style.display='none';"><tr>
		<td style="line-height: 14px;"><?php echo  $flash_error ? $flash_error : ($flash_ok ? $flash_ok : $flash_notify); ?></td></tr></table>
	</div>

	<?php
	$cont = ob_get_contents();
	ob_end_clean();

	if ($flash_error) cookie_del('flash_error');
	else if ($flash_ok) cookie_del('flash_ok');
	else if ($flash_notify) cookie_del('flash_notify');

	$is_style = true;

	return $cont;
}

// @parsetime @naturaltime
function ParseTime($string, $now_unix=null)
{
	// ParseTime(): converts time in natural form to unix timestamp.
	// English and Polish words supported.
	// Author: Czarek Tomczak [czarek.tomczak@gosu.pl] (07-11-2011)

	// + now, NOW, now(), NOW(), time, TIME, time(), TIME(), today, teraz, dzisiaj, dzis
	// + today 12:00 + current seconds
	// + today/now/dzisiaj 12:13:13
	// + yesterday, tomorrow (PL: wczoraj, jutro)
	// + tomorrow 12:30, dzis 12:13:13

	static $static_now;
	if (!isset($static_now)) { $static_now = time(); }

	if (isset($now_unix)) {
		$now = $now_unix;
	} else {
		$now = $static_now;
	}

	static $static_hour;
	if (!isset($static_hour)) { $static_hour = (int) date("H", $static_now); }
	if (isset($now_unix)) { $now_hour = (int) date("H", $now_unix); }
	else { $now_hour = $static_hour; }

	static $static_minute;
	if (!isset($static_minute)) { $static_minute = (int) date("i", $static_now);}
	if (isset($now_unix)) { $now_minute = (int) date("i", $now_unix); }
	else { $now_minute = $static_minute; }

	static $static_second;
	if (!isset($static_second)) { $static_second = (int) date("s", $static_now); }
	if (isset($now_unix)) { $now_second = (int) date("s", $now_unix); }
	else { $now_second = $static_second; }

	static $static_month;
	if (!isset($static_month)) { $static_month = (int) date("n", $static_now); }
	if (isset($now_unix)) { $now_month = (int) date("n", $now_unix); }
	else { $now_month = $static_month; }

	static $static_day;
	if (!isset($static_day)) { $static_day = (int) date("j", $static_now); }
	if (isset($now_unix)) { $now_day = (int) date("j", $now_unix); }
	else { $now_day = $static_day; }

	static $static_year;
	if (!isset($static_year)) { $static_year = (int) date("Y", $static_now); }
	if (isset($now_unix)) { $now_year = (int) date("Y", $now_unix); }
	else { $now_year = $static_year; }

	static $static_dayofweek;
	if (!isset($static_dayofweek)) {
		$static_dayofweek = (int) date("w", $static_now);
		// 0 = sunday, make it 7
		if (0 == $static_dayofweek) {
			$static_dayofweek = 7;
		}
	}
	if (isset($now_unix)) {
		$now_dayofweek = (int) date("w", $now_unix);
		if (0 == $now_dayofweek) {
			$now_dayofweek = 7;
		}
	}
	else { $now_dayofweek = $static_dayofweek; }

	// Removes polish chars "ą">"a", "ś">"s", so we can later detect "dzień" or "środa" by comparing
	// chars without tails "dzien", "sroda". Removing tails is supported for 3 charsets.

	static $win_chars = "\xb9\xe6\xea\xb3\xf1\xf3\x9c\x9f\xbf\xa5\xc6\xca\xa3\xd1\xd3\x8c\x8f\xaf"; // windows-1250
	static $iso_chars = "\xb1\xe6\xea\xb3\xf1\xf3\xb6\xbc\xbf\xa1\xc6\xca\xa3\xd1\xd3\xa6\xac\xaf"; // iso-8859-2
	static $notail_chars = "acelnoszzACELNOSZZ";
	static $utf_map = array("\xc4\x85"=>"a", "\xc4\x84"=>"A", "\xc4\x87"=>"c", "\xc4\x86"=>"C", "\xc4\x99"=>"e", "\xc4\x98"=>"E", "\xc5\x82"=>"l", "\xc5\x81"=>"L", "\xc3\xb3"=>"o", "\xc3\x93"=>"O", "\xc5\x9b"=>"s", "\xc5\x9a"=>"S", "\xc5\xba"=>"z", "\xc5\xb9"=>"Z", "\xc5\xbc"=>"z", "\xc5\xbb"=>"Z", "\xc5\x84"=>"n", "\xc5\x83"=>"N");

	$string = strtr($string, $win_chars, $notail_chars);
	$string = strtr($string, $iso_chars, $notail_chars);
	$string = strtr($string, $utf_map);

	// To lower. "Dzień" > "Dzien" > "dzien"

	$string = strtolower($string);

	// en: "next Monday" = +1 Monday, "last"Monday" = -1 Monday, "prev Monday", "previous monday"
	// pl: "nastepny Poniedzialek", "poprzedni Poniedzialek", "ostatni poniedziałek"

	// note: "prev" needs to be replaced after "previous", cause "ious" might come out of the replacement.
	static $replace_keys = array("next", "last", "previous", "prev", "nastepny", "poprzedni", "ostatni");
	static $replace_vals = array("+1", "-1", "-1", "-1", "+1", "-1", "-1");
	$string = str_replace($replace_keys, $replace_vals, $string);

	// "in 2 days at 5:00" => remove "in", "at"
	// "in 2 days and 5 hours" => remove "and"
	// "za 2 dni o 5:00", "za 2 dni i 5 godzin oraz 5 minut i 6 sekund"
	$string = preg_replace("#(\b)(in|at|and|za|oraz|o|i)(\b)#", "\$1\$3", $string);

	// Cut day endings.

	// en:
	// "1st february" > "1 february"
	// "2nd Feb" > "2 Feb"
	// "3rd Feb" > "3 Feb",
	// "4th Feb 2013" > "4 Feb 2013"

	$string = preg_replace("#(\d+)\-?(st|nd|rd|th)(\s)#i", "\$1\$3", $string);

	// pl:
	// "1szy Lutego", "1-szy lutego" > "1 lutego"
	// "2gi Lutego", "2-gi lutego" > "2 lutego"
	// "3ci Lutego", "3-ci lutego"
	// "4ty Lutego", "4-ty lutego", "5ty", "6ty", "9ty", "10ty", "11ty
	// "7my lutego", "7-my lutego", "8my"

	$string = preg_replace("#(\d+)\-?(szy|gi|ci|ty|my)(\s)#i", "\$1\$3", $string);

	// Start mktime values.
	$start_year = $now_year;
	$start_month = $now_month;
	$start_day = $now_day;
	$start_hour = $now_hour;
	$start_minute = $now_minute;
	$start_second = $now_second;

	// +11.05 = 2011-05-11 (current year) + current hour, minutes, seconds
	// +11.05.2011 (day.month.year)
	// +11.05.2011 15:30 + current seconds
	// +11.05.2011 15:30:13
	// +11.05 12:30
	// +11.05 12:30:13
	// + "11.05", "11.05 15:30", "11.05 15:30:13", "11.05.2011", "11.05.2011 15:30", "11.05.2011 15:30:13",

	if (preg_match("#(\d\d)\.(\d\d)(\.(\d\d\d\d))?#", $string, $match))
	{
		$string = preg_replace("#".preg_quote($match[0], "#")."#", "", $string, 1);

		$start_day = (int) $match[1];
		$start_month = (int) $match[2];
		if (isset($match[3])) {
			$start_year = (int) $match[4];
		} else {
			$start_year = $now_year;
		}
	}

	// +"2011-05-11"
	// +"2011-05" = "2011-05-currentday"'
	// +"2011" = "2011-currmonth-currday"
	// +"2011 15:00"

	// Daty typu "11.05.2011" muszą być wykonane przed tym regexpem bo
	// inaczej wyłapie rok z tej daty "2011".

	if (preg_match("#(\d\d\d\d)(-(\d\d)(-(\d\d)))?#", $string, $match))
	{
		$string = preg_replace("#".preg_quote($match[0], "#")."#", "", $string, 1);

		$start_year = (int) $match[1];
		if (isset($match[2])) {
			$start_month = (int) $match[3];
			if (isset($match[4])) {
				$start_day = (int) $match[5];
			} else {
				$start_day = 1;
			}
		} else {
			$start_month = 1;
			$start_day = 1;
		}
	}

	$HoursFound = false;

	// Hours: "15:30", "15:30:13",
	if (preg_match("#(\d\d):(\d\d)(:(\d\d))?#i", $string, $match))
	{
		$HoursFound = true;
		$string = preg_replace("#".preg_quote($match[0], "#")."#", "", $string, 1);

		$start_hour = (int) $match[1];
		$start_minute = (int) $match[2];
		if (isset($match[3])) {
			// "15:30:13"
			$start_second = (int) $match[4];
		} else {
			$start_second = 0;
		}
	}

	// + 12d, +12d, + 12d, +12 day, +12 days = time() + 12 days
	// + -5h = time() - 5 hours
	// + 1y - 5d + 5h = time() + 1year - 5 days + 5 hours
	// + -5d 15:55 = time() - 5 days
	// + -1week 12:00, -1 Week 12:00, - 1 week 12:00:53
	// + -1w

	// + "now", " now", "now()", "time", "NOW()", "TIME()", "Today", "teraz", "dzisiaj", "dzis",
	// + "now 12:00", "now  12:50", "Today 12:50:50", "dzisiaj 12:51:51", "teraz 12:31", "dzis 12:31",
	// + "Tomorrow", "jutro",
	// + "Tomorrow 12:13", "tomorrow 12:13:13", "jutro 12:13",
	// + "pojutrze"
	// + "yesterday", "Wczoraj",
	// + "yesterday 12:13", "Yesterday 12:13:13", "Wczoraj 12:13",
	// + "przedwczoraj"

	// + "tomorrow +2h", "tomorrow 15:00 +2h"

	// "15 styczeń o 17:00", "21 styczen 2010", "15 stycznia", "15 sty"

	// Day of week, the nearest one but not including the current one.
	// +"saturday 15:00" == on friday it means "+1day 15:00" or "tomorrow 15:00"
	// +"saturday 15:00" == on saturday it means next saturday
	// +"+1 saturday 15:00" == next saturday, on friday it means "+1 day"
	// +"+2 saturday" == saturday after next saturday, on friday it means ""
	// +"in 2days at 15:00", "za 2 dni o 15:00"
	// +"za 3 dni i 5 godzin" == "+3dni+5godz"
	// +"dzien i 2 godziny", "0dni 0godz 1 sekunda"

	$start_time = mktime($start_hour, $start_minute, $start_second, $start_month, $start_day, $start_year);

	preg_match_all("#([+-])?\s*(\d+)?\s*([a-z_]+(\(\))?)#i", $string, $matches);

	foreach ($matches[0] as $k => $match0)
	{
		$match1 = $matches[1][$k];
		$match2 = $matches[2][$k];
		$match3 = $matches[3][$k];

		$sign = "+";
		if ($match1) {
			$sign = $match1;
		}

		if (strlen($match2)) { // could be "+0h"
			$number = (int) $match2;
		} else {
			$number = 1; // "dzien" == "1 dzien"
		}

		$word = $match3;

		// en: "now", "time", "now()", "time()",
		// pl: "teraz", "czas"

		if ("unix_timestamp" == $word || "current_timestamp" == $word
			|| "now" == $word || "time" == $word || "now()" == $word || "time()" == $word
			|| "teraz" == $word || "czas" == $word)
		{
			$start_time = $now;
			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "today"
		// pl: "dzisiaj", "dzis"
		if ("today" == $word || "dzisiaj" == $word || "dzis" == $word)
		{
			if ($HoursFound) {
				$start_time = mktime($start_hour, $start_minute, $start_second, $now_month, $now_day, $now_year);
			} else {
				$start_time = mktime(0, 0, 0, $now_month, $now_day, $now_year);
			}
			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "tomorrow"
		// pl: "jutro"

		if ("tomorrow" == $word || "jutro" == $word) {
			if ($HoursFound) {
				$start_time = mktime($start_hour, $start_minute, $start_second, $now_month, $now_day, $now_year);
			} else {
				$start_time = mktime(0, 0, 0, $now_month, $now_day, $now_year);
			}
			$start_time = $start_time + 3600*24;
			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// pl: "pojutrze"

		if ("pojutrze" == $word) {
			if ($HoursFound) {
				$start_time = mktime($start_hour, $start_minute, $start_second, $now_month, $now_day, $now_year);
			} else {
				$start_time = mktime(0, 0, 0, $now_month, $now_day, $now_year);
			}
			$start_time = $start_time + 3600*24*2;
			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "yesterday"
		// pl: "wczoraj"

		if ("yesterday" == $word || "wczoraj" == $word) {
			if ($HoursFound) {
				$start_time = mktime($start_hour, $start_minute, $start_second, $now_month, $now_day, $now_year);
			} else {
				$start_time = mktime(0, 0, 0, $now_month, $now_day, $now_year);
			}
			$start_time = $start_time - 3600*24;
			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// pl: "przedwczoraj"

		if ("przedwczoraj" == $word) {
			$start_time = $start_time - 3600*24*2;
			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "y", "year", "years"
		// pl: "r", "rok", "lata", "lat"

		if ("y" == $word || "year" == $word || "years" == $word
			|| "r" == $word || "rok" == $word || "lata" == $word || "lat" == $word)
		{
			// feb 2012 = 29 days
			// feb 2011 = 28 days

			// 29 feb 2012 - 1 year == 28 feb 2011 and not 1 mar 2011
			// Adding years always keeps the month intact.
			// strototime() returns 1 mar 2011 in this case.

			$temp_hour = (int) date("H", $start_time);
			$temp_minute = (int) date("i", $start_time);
			$temp_second = (int) date("s", $start_time);
			$temp_month = (int) date("n", $start_time);
			$temp_day = (int) date("j", $start_time);
			$temp_year = (int) date("Y", $start_time);

			if ("+" == $sign) {
				$temp_year = $temp_year + $number;
			} else {
				$temp_year = $temp_year - $number;
			}

			// Max 4 tries (should be probably 1 enought, but let's keep the code consistent with adding/subtracting months).

			for ($i = 1; $i <= 5; ++$i)
			{
				if (5 == $i) {
					return 0; // Errpr.
				}

				$test_time = mktime($temp_hour, $temp_minute, $temp_second, $temp_month, $temp_day, $temp_year);
				$new_month = (int) date("n", $test_time);

				if ($new_month == $temp_month) {
					$start_time = $test_time;
					break;
				} else {
					--$temp_day;
				}
			}

			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "m", "month", "months"
		// pl: "mies", "miesiac", "miesiace", "miesiecy"

		if ("m" == $word || "month" == $word || "months" == $word
			|| "mies" == $word || "miesiac" == $word || "miesiace" == $word || "miesiecy" == $word)
		{
			// jan 2012 = 31 days
			// luty 2012 = 29 days
			// luty 2011 = 28 days

			// 31 jan 2012 + 1 month == 29 feb 2011 and not 2 mar 2011 as in strtotime
			// 31 jan 2011 + 1 month == 28 feb 2011 and not 3 mar 2011 as in strtotime
			// Adding 1 month always returns the next month, and not +2 months as strtotime() does.

			$temp_hour = (int) date("H", $start_time);
			$temp_minute = (int) date("i", $start_time);
			$temp_second = (int) date("s", $start_time);
			$temp_month = (int) date("n", $start_time);
			$temp_day = (int) date("j", $start_time);
			$temp_year = (int) date("Y", $start_time);

			// We cannot simply add +number to month, cause months must be between 1..12.
			$temp_number = abs($number);
			while ($temp_number > 0)
			{
				if ("+" == $sign) {
					$temp_month = $temp_month + 1;
				} else {
					$temp_month = $temp_month - 1;
				}

				if (13 == $temp_month) {
					$temp_month = 1;
					$temp_year = $temp_year + 1;
				} else if (0 == $temp_month) {
					$temp_month = 12;
					$temp_year = $temp_year - 1;
				}

				$temp_number--;
			}

			// Max 4 tries.
			// 31th day >> 28th day, 3 tries should be enough, allowing 4 just to be sure.

			for ($i = 1; $i <= 5; ++$i)
			{
				if (5 == $i) {
					return 0; // Error.
				}

				$test_time = mktime($temp_hour, $temp_minute, $temp_second, $temp_month, $temp_day, $temp_year);
				$new_day = (int) date("j", $test_time);

				if ($temp_day >= 28 && in_array($new_day, array(1,2,3,4))) {
					// 31 Jan +1 month could be 03 Mar / 02 Mar / 01 Mar - bad.
					// Must be the last day of Feb.
					--$temp_day;
				} else {
					$start_time = $test_time;
					break;
				}
			}

			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "d", "day", "days"
		// pl: "dz", "dzien", "dni"

		if ("d" == $word || "day" == $word || "days" == $word
			|| "dz" == $word || "dzien" == $word || "dni" == $word)
		{
			$secs = 3600*24;
			if ("+" == $sign) {
				$start_time = $start_time + ($secs * $number);
			} else {
				$start_time = $start_time - ($secs * $number);
			}

			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "h", "hour", "hours"
		// pl: "g", "godz", "godzina", "godziny", "godzin"

		if ("h" == $word || "hour" == $word || "hours" == $word
			|| "g" == $word || "godz" == $word || "godzin" == $word || "godzina" == $word || "godziny" == $word)
		{
			if ("+" == $sign) {
				$start_time = $start_time + (3600 * $number);
			} else {
				$start_time = $start_time - (3600 * $number);
			}


			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "i", "min", "minute", "minutes"
		// pl: "minuta", "minuty", "minut"

		if ("i" == $word || "min" == $word || "minute" == $word || "minutes" == $word
			|| "minuta" == $word || "minuty" == $word || "minut" == $word)
		{
			if ("+" == $sign) {
				$start_time = $start_time + (60 * $number);
			} else {
				$start_time = $start_time - (60 * $number);
			}

			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "s", "sec", "second", "seconds"
		// pl: "sek", "sekunda", "sekundy", "sekund"

		if ("s" == $word || "sec" == $word || "second" == $word || "seconds" == $word
			|| "sek" == $word || "sekunda" == $word || "sekundy" == $word || "sekund" == $word)
		{
			if ("+" == $sign) {
				$start_time = $start_time + (1 * $number);
			} else {
				$start_time = $start_time - (1 * $number);
			}

			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "w", "week", "weeks"
		// pl: "t", "tydz", "tydzien", "tyg", "tygodnie", "tygodni"

		if ("w" == $word || "week" == $word || "weeks" == $word
			|| "t" == $word || "tydz" == $word || "tydzien" == $word || "tyg" == $word || "tygodnie" == $word || "tygodni" == $word)
		{
			if ("+" == $sign) {
				$start_time = $start_time + ((3600*24*7)*$number);
			} else {
				$start_time = $start_time - ((3600*24*7)*$number);
			}

			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: January, Jan, February, Feb, March, Mar, April, Apr, May, June, Jun, July, Jul, August, Aug, September, Sep, October, Oct, December, Dec.
		// examples: 1st February, 1 Feb, 4th Feb 2013 15:00
		// pl: Styczeń, Sty, Luty, Lut, Marzec, Mar, Kwiecień, Kwi, Maj, Czerwiec, Cze, Lipiec, Lip, Sierpień, Sie, Wrzesień, Wrz, Październik, Paź, Listopad, Lis, Grudzień, Gru.
		// examples: 12 lutego, 15 stycznia o 17:00, 15 styczeń, 15 sty.

		static $months = array(
			"january" => 1, "jan" => 1,
			"february" => 2, "feb" => 2,
			"march" => 3, "mar" => 3,
			"april" => 4, "apr" => 4,
			"may" => 5,
			"june" => 6, "jun" => 6,
			"july" => 7, "jul" => 7,
			"august" => 8, "aug" => 8,
			"september" => 9, "sep" => 9,
			"october" => 10, "oct" => 10,
			"november" => 11, "nov" => 11,
			"december" => 12, "dev" => 12,
			"styczen" => 1, "stycznia" => 1, "sty" => 1,
			"luty" => 2, "lutego" => 2, "lut" => 2,
			"marzec" => 3, "marca" => 3, "mar" => 3,
			"kwiecien" => 4, "kwietnia" => 4, "kwi" => 4,
			"maja" => 5, "maj" => 5, // "maja" needs to be replaced before "maj", or "a" will stay in string.
			"czerwiec" => 6, "czerwca" => 6, "cze" => 6,
			"lipiec" => 7, "lipca" => 7, "lip" => 7,
			"sierpien" => 8, "sierpnia" => 8, "sie" => 8,
			"wrzesien" => 9, "wrzesnia" => 9, "wrz" => 9,
			"pazdziernika" => 10, "pazdziernik" => 10, "paz", // "pazdziernika" needs to be replaced before "pazdziernik".
			"listopada" => 11, "listopad" => 11, "lis" => 11, // "listopada" needs to be replaced before "listopad".
			"grudzień" => 12, "grudnia" => 12, "gru" => 12
		);

		if (array_key_exists($word, $months))
		{
			if (!$number) {
				// when number is missing in string, it will be set to "1", so this line should never execute.
				return 0;
			}

			$year = (int) date("Y", $start_time);
			$hour = (int) date("H", $start_time);
			$minute = (int) date("i", $start_time);
			$second = (int) date("s", $start_time);

			$day = $number;
			$month = $months[$word];

			$start_time = mktime($hour, $minute, $second, $month, $day, $year);

			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// en: "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"
		// pl: "poniedzialek", "wtorek", "sroda", "czwartek", "piatek", "sobota", "niedziela"
		// +1 saturday - next saturday

		if (in_array($word, array("monday", "mon", "tuesday", "tue", "wednesday", "wed", "thursday", "thu",
			"friday", "fri", "saturday", "sat", "sunday", "sun"))
			|| in_array($word, array("poniedzialek", "pon", "wtorek", "wto", "sroda", "sro", "czwartek", "czw",
			"piatek", "pia", "sobota", "sob", "niedziela", "nie")))
		{
			$daynum = str_replace(
				array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday",
					"mon", "tue", "wed", "thu", "fri", "sat", "sun",
					"poniedzialek", "wtorek", "sroda", "czwartek", "piatek", "sobota", "niedziela",
					"pon", "wto", "sro", "czw", "pia", "sob", "nie"),
				array(1, 2, 3, 4, 5, 6, 7,
					1, 2, 3, 4, 5, 6, 7,
					1, 2, 3, 4, 5, 6, 7,
					1, 2, 3, 4, 5, 6, 7),
				$word
			);

			if ("+" == $sign) {
				if ($daynum == $now_dayofweek) {
					$days = 7;
				} else if ($daynum > $now_dayofweek) {
					// string: 4 > today: 3
					$days = $daynum - $now_dayofweek;
				} else {
					// string: 3 < today: 4
					$days = (7 - $now_dayofweek) + ($daynum);
				}
				assert($days >= 1 && $days <= 7);
				// $days = difference in days only for 1st week
				if ($number >= 2) {
					$start_time = $start_time + (3600*24*$days) + (7 * ($number-1));
				} else {
					$start_time = $start_time + (3600*24*$days);
				}
			} else {
				if ($daynum == $now_dayofweek) {
					$days = 7;
				} else if ($daynum > $now_dayofweek) {
					// string: 4 > today: 3
					$days = 7 - ($daynum - $now_dayofweek);
				} else {
					// string: 3 < today: 4
					$days = $now_dayofweek - $daynum;
				}
				assert($days >= 1 && $days <= 7);
				// $days = difference in days only for 1st week
				if ($number >= 2) {
					$start_time = $start_time - (3600*24*$days) - (7 * ($number-1));
				} else {
					$start_time = $start_time - (3600*24*$days);
				}
			}

			$string = preg_replace("#".preg_quote($match0, "#")."#", "", $string, 1);
			continue;
		}

		// Unknown word - error.
		return 0;
	}

	if (trim($string)) {
		// Some unknown identifier is still left in the string - error.
		return 0;
	} else {
		return $start_time;
	}
}
function ParseTime_Tests($now=null)
{
	while (ob_get_level()) { ob_end_clean(); }
	header("Content-Encoding: none");
	ob_start();

	// + 12d, +12d, + 12d, +12 day, +12 days = time() + 12 days
	// + -5h = time() - 5 hours
	// + 1y - 5d + 5h = time() + 1year - 5 days + 5 hours
	// + -5d 15:55 = time() - 5 days
	// + -1week 12:00, -1 Week 12:00, - 1 week 12:00:53
	// + -1w

	$tests = array(
		"now", " now", "now()", "time", "NOW()", "TIME()", "unix_timestamp", "current_timestamp", "Today", "teraz", "dzisiaj", "dzis",
		"now 13:00", "now  13:13", "Today 13:13:13", "dzisiaj 13:13:13", "teraz 13:13", "dzis 13:13",
		"yesterday", "Wczoraj",
		"Tomorrow", "jutro",
		"yesterday 13:13", "Yesterday 13:13:13", "Wczoraj 13:13",
		"Tomorrow 13:13", "tomorrow 13:13:13", "jutro 13:13",
		"pojutrze", "pojutrze 13:13", "przedwczoraj", "przedwczoraj 13:13",
		"2013-12-13", "2013-12-13 13:13", "2013-12-13 13:13:13",
		"2011-13-13", "2011-12-33",
		"13.12", "13.12 13:13", "13.12 13:13:13", "13.12.2013", "13.12.2013 13:13", "13.12.2013 13:13:13",
		"13:13",
		"12d", "12 dni", "+ 12 dni",
		"-1year", "teraz -1year", "dzisiaj 12:00 -1year", "-1month", "+1 month", "+2month", "+3month", "+4m", "+5m", "1d -5h", "1y - 5d + 5h", "1y -5 Dni", "-1 tydzien 13:13", "-1w",
		"wczoraj +1h", "wczoraj -1h", "jutro +1dzien",
		"+12m", "+13m", "+24m", "+25m", "+36m", "+37m", "+48m", "+49m",
		"1st february", "1 february", "2nd Feb", "3rd Feb", "4th Feb 2013",
		"15 stycznia", "stycznia", "styczeń 15:00", "15 styczeń", "15 sty", "12 lutego", "12 luty", "12 Lut", "15 styczeń o 17:00", "21 styczen 2010",
		"21szy stycznia", "21-szy stycznia", "21st January", "21 January",
		"monday 15:00", "tuesday 15:00", "poniedziałek 15:00", "wtorek 15:00",
		"+1 saturday 15:00", "+2 saturday", "in 2 days at 15:00", "za 2 dni o 15:00",
		"za 3 dni i 5 godzin", "in 2 days and 5 hours", "+2days +5hours",
		"+0h", "5 godzin", "5 minut", "5 sekund",
		"za tydzień", "za 2 tygodnie",
		"next monday", "prev monday", "previous monday", "last monday",
		"następny poniedziałek", "poprzedni poniedziałek", "ostatni poniedziałek",
		"+1mon", "+1 tue", "next wed", "next Wed 15:00",
		"+1pon", "+1 wto", "następny Pon"
	);

	// "15 styczeń o 17:00", "21 styczen 2010", "15 stycznia"
	// Day of week, the nearest one but not including the current one.
	// "saturday 15:00" == on friday it means "+1day 15:00" or "tomorrow 15:00"
	// "saturday 15:00" == on saturday it means next saturday
	// "+1 saturday 15:00" == next saturday, on friday it means "+1 day"
	// "+2 saturday" == saturday after next saturday, on friday it means ""
	// "in 2days at 15:00", "za 2 dni o 15:00"
	// "za 3 dni i 5 godzin" == "+3dni+5godz"
	// "dzien i 2 godziny", "0dni 0godz 1 sekunda"

	printf("<title>Natural time strings</title>");
	printf("<meta charset=utf-8>");
	printf("<style type=text/css>html, body { margin: 0; padding: 0; } body { margin: 1em 1.5em; padding: 0em; } body, table { font: 11px Tahoma; } body { line-height: 1.4em; } h1 { font: bold 15px Tahoma; }</style>");
	printf("<h1>Natural time strings</h1>");
	printf("<div>In fields of type <b>INT</b> you can use natural string times to generate <b>Unix Timestamps</b>.</div>");
	printf("<div>You can also use this strings in fields of type <b>DATETIME</b> and <b>TIMESTAMP</b>.</div>");
	printf("<div>Supported languages are: <b>English</b> and <b>Polish</b>.<div><br>");

	printf("<style type=text/css>th { background: #ddd; padding: 2px 4px;  } td { background: #f5f5f5; padding: 2px 4px; }</style>");

	printf("<table cellspacing=1 cellpadding=0>");
	printf("<tr><th>String</th><th>Date</th></tr>");

	$no = 0;
	foreach ($tests as $string) {
		++$no;
		$result_time = ParseTime($string, $now);
		$result = date("Y-m-d H:i:s", $result_time);
		if ($result_time == 0) $result = 0;
		printf("<tr><td>%s</td><td>%s</td></tr>", $string, $result);
	}

	printf("</table><br>");

	exit();
}

if (GET("action", "string") == "parsetime")
{
	// $now = strtotime("2012-02-29 01:01:01"); // Test -1year: 29 feb 2012 - 1 year == 28 feb 2011 and not 1 mar 2011
	// $now = strtotime("2012-01-31 01:01:01"); // Test +1month: 31 jan 2012 + 1 month == 29 feb 2011 and not 2 mar 2011
	$now = strtotime("2011-01-31 01:01:01"); // Test +1month: 31 jan 2011 + 1 month == 28 feb 2011 and not 3 mar 2011
	$now = time();
	ParseTime_Tests($now);
	exit();
}

// ~~~~~~~~~ @funcsend

?>
<?php if (GET("import", "bool")): ?>

	<?php

	// ----------------------------------------------------------------
	// @import HTML
	// ----------------------------------------------------------------

	?>

	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $page_charset;?>">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo $db_name_h1?$db_name_h1:$db_name;?> &gt; Import</title>
		<link rel="shortcut icon" href="<?php echo $_SERVER['PHP_SELF']; ?>?dbkiss_favicon=1">
	</head>
	<body>

	<?php layout(); ?>
	<h1><a class=blue style="<?php echo $db_name_style;?>" href="<?php echo $_SERVER['PHP_SELF'];?>"><?php echo $db_name_h1?$db_name_h1:$db_name;?></a> &gt; Import</h1>
	<?php conn_info(); ?>

	<?php $files = sql_files(); ?>

	<?php if (count($files)): ?>
		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
		<table class="none" cellspacing="0" cellpadding="0">
		<tr>
			<td>SQL file:</th>
			<td><select name="sqlfile"><option value="" selected="selected"></option><?php echo options($files);?></select></td>
			<td><input type="checkbox" name="ignore_errors" id="ignore_errors" value="1"></td>
			<td><label for="ignore_errors">ignore errors</label></td>
			<td><input type="checkbox" name="transaction" id="transaction" value="1"></td>
			<td><label for="transaction">transaction</label></td>
			<td><input type="checkbox" name="force_myisam" id="force_myisam" value="1"></td>
			<td><label for="force_myisam">force myisam</label></td>
			<td><input type="text" size="5" name="query_start" value=""></td>
			<td>query start</td>
			<td><input type="submit" value="Import"></td>
		</tr>
		</table>
		</form>
		<br>
	<?php else: ?>
		No sql files found in current directory.
	<?php endif; ?>

	</body></html>

	<?php exit(); ?>

<?php endif; ?>


<?php if ('editrow' == GET("action", "string")): ?>
<?php

	// ----------------------------------------------------------------
	// @editrow PHP
	// ----------------------------------------------------------------

	GET("id", "string");
	GET("pk", "string");
	GET("table", "string");
	POST("dbkiss_action", "string");

	function dbkiss_filter_id($id)
	{
		# mysql allows table names of: `62-511`
		# also, columns might be numeric ex. `62`
		if (preg_match('#^[_a-z0-9][a-z0-9_\-]*$#i', $id)) {
			return $id;
		}
		return false;
	}

	if (ctype_digit($_GET["id"])) {
		// sqlite:
		// 1 row: SELECT * FROM "sqlite_sequence" WHERE "seq" = 5
		// No rows found: SELECT * FROM "sqlite_sequence" WHERE "seq" = '5'
		// Solved by not querying sqlite_ special: AND name NOT LIKE 'sqlite_%'
		$_GET["id"] = (int) $_GET["id"];
	}

	$_pk = $_GET["pk"];
	$_id = DecodeRowId($_GET["id"]);

	if (strstr($_pk, ":")) {
		$_pkeys = explode(":", $_pk);
		$pvals_temp = $_id;
		$_where = array();
		foreach ($_pkeys as $arrKey => $key) {
			$_where[$key] = $pvals_temp[$arrKey];
		}
	} else {
		$_pkeys = false;
		$_where = array($_pk => $_id);
	}

	$_GET['table'] = htmlspecialchars($_GET['table']);
	$_GET['pk'] = htmlspecialchars($_GET['pk']);

	$title_edit = sprintf('Edit (%s=%s)', $_GET['pk'], $_GET['id']);
	$title = ' &gt; '.$_GET['table'].' &gt; '.$title_edit;

	if (!dbkiss_filter_id($_GET['table'])) {
		error('Invalid table name');
	}

	if ($_pkeys) {
		foreach ($_pkeys as $key) {
			if (!dbkiss_filter_id($key)) {
				error('Invalid pk');
			}
		}
	} else {
		if (!dbkiss_filter_id($_pk)) {
			error('Invalid pk');
		}
	}

	$row = false;

	if (!error())
	{
		$table_enq = quote_table($_GET['table']);
		$test = db_row("SELECT * FROM $table_enq");

		if ($test) {
			if ($_pkeys) {
				foreach ($_pkeys as $key) {
					if (!array_key_exists($key, $test)) {
						error('Invalid pk');
					}
				}
			} else {
				if (!array_key_exists($_pk, $test)) {
					error('Invalid pk');
				}
			}
		}

		if (!error())
		{
			$table_enq = quote_table($_GET['table']);

			$where = db_where($_where);
			$query = "SELECT * FROM $table_enq $where";
			$query = db_limit($query, 0, 2);
			$rows = db_list($query);

			if (count($rows) > 1) {
				error('Invalid pk: found more than one row with given id');
			} else if (count($rows) == 0) {
				error('Row not found');
			} else {
				$row = $rows[0];
			}
		}
	}

	if ($row) {
		$types = table_columns($_GET['table']);
	}

	$edit_actions_assoc = array(
		'update' => 'Update',
		'update_pk' => 'Overwrite pk',
		'insert' => 'Copy row (insert)',
		'delete' => 'Delete'
	);

	// -----------------------------------------

	$edit_action = $_POST['dbkiss_action'];

	if ("GET" == $_SERVER["REQUEST_METHOD"])
	{
		$edit_action = array_first_key($edit_actions_assoc);
		$post = $row;
	}

	if ("POST" == $_SERVER["REQUEST_METHOD"])
	{
		if (!array_key_exists($edit_action, $edit_actions_assoc)) {
			$edit_action = '';
			error('Invalid action');
		}

		$post = array();
		foreach ($row as $k => $v) {
			if (array_key_exists($k, $_POST)) {
				$val = (string) $_POST[$k];
				if ('null' == $val || "NULL" == $val) {
					$val = null;
				}
				if ('int' == $types[$k]) {
					if (!strlen($val)) {
						$val = null;
					}
					if (!(preg_match('#^-?\d+$#', $val) || is_null($val))) {
						error('%s: invalid value', $k);
					}
					if (!ctype_digit($val)) {
						$val = ParseTime($val);
					}
				}
				if ('float' == $types[$k]) {
					if (!strlen($val)) {
						$val = null;
					}
					$val = str_replace(',', '.', $val);
					if (!(is_numeric($val) || is_null($val))) {
						error('%s: invalid value', $k);
					}
				}
				if ('datetime' == $types[$k] || 'timestamp' == $types[$k]) {
					if (!strlen($val)) {
						$val = null;
					}
					if (!ctype_digit($val)) {
						$parsetime = ParseTime($val);
						if ($parsetime) {
							$val = date("Y-m-d H:i:s", $parsetime);
						} else {
							$val = null;
						}
					}
				}
				$post[$k] = $val;
			} else {
				error('Missing key: %s in POST', $k);
			}
		}

		if ('update' == $edit_action)
		{
			if ($post[$_GET['pk']] != $row[$_GET['pk']]) {
				if (count($row) != 1) { // Case: more than 1 column
					error('%s: cannot change pk on UPDATE', $_GET['pk']);
				}
			}
		}
		if ('update_pk' == $edit_action)
		{
			if ($post[$_GET['pk']] == $row[$_GET['pk']]) {
				error('%s: selected action Overwrite pk, but pk value has not changed', $_GET['pk']);
			}
		}
		if ('insert' == $edit_action)
		{
			if (strlen($post[$_GET['pk']])) {
				$table_enq = quote_table($_GET['table']);
				$pk_enq = quote_column($_GET["pk"]);
				$test = db_row("SELECT * FROM $table_enq WHERE $pk_enq = %0", array($post[$_GET['pk']]));
				if ($test) {
					error('%s: there is already a record with that id', $_GET['pk']);
				}
			}
		}

		if (!error())
		{
			$post2 = $post;
			if ('update' == $edit_action)
			{
				if (count($row) != 1) { // Case: more than 1 column
					unset($post2[$_GET['pk']]);
				}
				db_update($_GET['table'], $post2, $_where);
				if (db_error()) {
					error('<font color="red"><b>DB error</b></font>: '.db_error());
				} else {
					if (count($row) == 1) { // Case: only 1 column
						redirect_ok(url(self(), array('id'=>$post[$_GET['pk']])), 'Row updated');
					} else {
						redirect_ok(self(), 'Row updated');
					}
				}
			}
			if ('update_pk' == $edit_action)
			{
				@db_update($_GET['table'], $post2, $_where);
				if (db_error()) {
					error('<font color="red"><b>DB error</b></font>: '.db_error());
				} else {
					$url = url(self(), array('id' => $post[$_GET['pk']]));
					redirect_ok($url, 'Row updated (pk overwritten)');
				}
			}
			if ('insert' == $edit_action)
			{
				$new_id = false;
				if (!strlen($post2[$_GET['pk']])) {
					unset($post2[$_GET['pk']]);
				} else {
					$new_id = $post2[$_GET['pk']];
				}
				@db_insert($_GET['table'], $post2);
				if (db_error()) {
					error('<font color="red"><b>DB error</b></font>: '.db_error());
				} else {
					if (!$new_id) {
						$new_id = db_insert_id($_GET['table'], $_GET['pk']);
					}
					$url = url(self(), array('id'=>$new_id));
					$msg = sprintf('Row inserted (%s=%s)', $_GET['pk'], $new_id);
					redirect_ok($url, $msg);
				}
			}
			if ('delete' == $edit_action)
			{
				$table_enq = quote_table($_GET['table']);
				$pk_enq = quote_column($_GET["pk"]);
				@db_exe("DELETE FROM $table_enq WHERE $pk_enq = %0", $_GET['id']);
				if (db_error()) {
					error('<font color="red"><b>DB error</b></font>: '.db_error());
				} else {
					redirect_ok(self(), 'Row deleted');
				}
			}
		}
	}

	?>
<?php rawlayout_start($_GET["table"]." &gt; ".$title_edit); ?>

	<?php

	// ----------------------------------------------------------------
	// @editrow HTML
	// ----------------------------------------------------------------

	?>

	<h1><span style="<?php echo $db_name_style;?>"><?php echo $db_name_h1?$db_name_h1:$db_name;?></span><?php echo $title;?></h1>

	<?php echo error();?>

	<?php if ($row): ?>

		<form action="<?php echo self();?>" method="post">

		<div id="Help_naturaltime" style="display: none;">
			You can use natural time strings in fields of type: int, datetime, timestamp.
			See <a href="javascript:popup('<?php echo $_SERVER['PHP_SELF']; ?>?action=parsetime', 550, 600)">examples</a>.
		</div>

		<div style="float: left;">
		<?php echo radio_assoc($edit_action, $edit_actions_assoc, 'dbkiss_action');?>
		</div>
		<div style="float: left; margin-left: 0.5em;">
			<a class=help title="Help: natural time strings" href="javascript:void(0)" onclick="Tooltip(this, 'Help_naturaltime')"></a>
		</div>

		<br style="clear: both;">
		<br>

		<table cellspacing="1" class="ls2">
		<?php foreach ($post as $k => $v): if (is_null($v)) { $v = 'null'; } $v = htmlspecialchars($v); ?>
			<tr>
				<th><?php echo $k;?>:</th>
				<td>
					<?php if ('int' == $types[$k]): ?>
						<input type="text" name="<?php echo $k;?>" value="<?php echo html_once($v);?>" size="11">
					<?php elseif ('char' == $types[$k]): ?>
						<input type="text" name="<?php echo $k;?>" value="<?php echo html_once($v);?>" size="50">
					<?php elseif (in_array($types[$k], array('text', 'mediumtext', 'longtext')) || strstr($types[$k], 'blob')): ?>
						<textarea name="<?php echo $k;?>" cols="80" rows="<?php echo $k=='notes'?10:10;?>"><?php echo html_once($v);?></textarea>
					<?php else: ?>
						<input type="text" name="<?php echo $k;?>" value="<?php echo html_once($v);?>" size="30">
					<?php endif; ?>
				</td>
				<td valign="top"><?php echo $types[$k];?></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<td colspan="3" class="none">
				<input type="submit" wait="1" block="1" class="button" value="Edit">
			</td>
		</tr>
		</table>

		</form>

	<?php endif; ?>

	<?php rawlayout_end(); ?>

<?php exit(); endif; ?>


<?php if (GET("execute_sql", "bool")): ?>

<?php

	// ----------------------------------------------------------------
	// @sqleditor LISTING PHP
	// ----------------------------------------------------------------

	function listing($base_query, $md5_get = false)
	{
		// @listing

		GET("full_content", "bool");
		GET("only_select", "bool");
		GET("offset", "int");

		POST("full_content", "bool");
		POST("only_select", "bool");

		global $db_driver, $db_link;

		$full_content = ($_GET["full_content"] || $_POST["full_content"]);

		$md5_i = false;
		if ($md5_get) {
			preg_match('#_(\d+)$#', $md5_get, $match);
			$md5_i = $match[1];
		}

		$base_query = trim($base_query);

		if (";" == substr($base_query, -1)) {
			$base_query = substr($base_query, 0, -1);
		}

		$query = $base_query;
		$ret = array('msg'=>'', 'error'=>'', 'data_html'=>false);
		$limit = 25;
		$offset = $_GET["offset"];
		$page = floor($offset / $limit + 1);

		if ($query) {
			if (is_select($query) && !preg_match('#\s+LIMIT\s+\d+#i', $query) && !preg_match('#into\s+outfile\s+#', $query)) {
				$query = db_limit($query, $offset, $limit);
			} else {
				$limit = false;
			}
			$time = time_start();
			if (!db_is_safe($query, true)) {
				$ret['error'] = 'Detected UPDATE/DELETE without WHERE condition (put WHERE 1=1 if you want to execute this query)';
				return $ret;
			}
			$rs = @db_query($query);
			if ($rs) {
				if ($rs === true) {
					if ('mysql' == $db_driver)
					{
						$affected = mysql_affected_rows($db_link);
						$time = time_end($time);
						$ret['data_html'] = '<b>'.$affected.'</b> rows affected.<br>Time: <b>'.$time.'</b> sec';
						return $ret;
					}
				} else {
					if ('pgsql' == $db_driver)
					{
						$affected = @pg_affected_rows($rs);
						if ($affected || preg_match('#^\s*(DELETE|UPDATE)\s+#i', $query)) {
							$time = time_end($time);
							$ret['data_html'] = '<p><b>'.$affected.'</b> rows affected. Time: <b>'.$time.'</b> sec</p>';
							return $ret;
						}
					}
				}

				$rows = array();
				while ($row = db_row($rs)) {
					$rows[] = $row;
					if ($limit) {
						if (count($rows) == $limit) { break; }
					}
				}
				db_free($rs);

				if (is_select($base_query)) {
					$found = @db_one("SELECT COUNT(*) FROM ($base_query) AS sub");
					if (!is_numeric($found) || (count($rows) && !$found)) {
						global $COUNT_ERROR;
						$COUNT_ERROR = ' (COUNT ERROR) ';
						$found = count($rows);
					}
				} else {
					if (count($rows)) {
						$found = count($rows);
					} else {
						$found = false;
					}
				}
				if ($limit) {
					$pages = ceil($found / $limit);
				} else {
					$pages = 1;
				}
				$time = time_end($time);

			} else {
				$ret['error'] = db_error();
				return $ret;
			}
		} else {
			$ret['error'] = 'No query found.';
			return $ret;
		}

		ob_start();

		// ----------------------------------------------------------------
		// @sqleditor LISTING HTML
		// ----------------------------------------------------------------

	?>
		<?php if (is_numeric($found)): ?>
			<p>
				Found: <b><?php echo $found;?></b><?php echo isset($GLOBALS['COUNT_ERROR'])?$GLOBALS['COUNT_ERROR']:'';?>.
				Time: <b><?php echo $time;?></b> sec.
				<?php
					$params = array('md5'=>$md5_get, 'offset'=>$_GET["offset"]);
					if ($_GET['only_select'] || $_POST['only_select']) { $params['only_select'] = 1; }
					if ($_GET['full_content'] || $_POST['full_content']) { $params['full_content'] = 1; }
				?>
				/ <a href="<?php echo url(self(), $params);?>">Refetch</a>
				/ Export to CSV:&nbsp;

				<a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=csv&separator=<?php echo urlencode('|');?>&query=<?php echo base64_encode($base_query); ?>">pipe</a>
				-
				<a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=csv&separator=<?php echo urlencode("\t");?>&query=<?php echo base64_encode($base_query); ?>">tab</a>
				-
				<a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=csv&separator=<?php echo urlencode(',');?>&query=<?php echo base64_encode($base_query); ?>">comma</a>
				-
				<a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=csv&separator=<?php echo urlencode(';');?>&query=<?php echo base64_encode($base_query); ?>">semicolon</a>
			</p>
		<?php else: ?>
			<p>Result: <b>OK</b>. Time: <b><?php echo $time;?></b> sec</p>
		<?php endif; ?>

		<?php if (is_numeric($found)): ?>

			<?php if ($pages > 1): ?>
			<p>
				<?php if ($page > 1): ?>
					<?php $ofs = ($page-1)*$limit-$limit; ?>
					<?php
						$params = array('md5'=>$md5_get, 'offset'=>$ofs);
						if ($_GET['only_select'] || $_POST['only_select']) { $params['only_select'] = 1; }
					?>
					<a href="<?php echo url(self(), $params);?>">&lt;&lt; Prev</a> &nbsp;
				<?php endif; ?>
				Page <b><?php echo $page;?></b> of <b><?php echo $pages;?></b> &nbsp;
				<?php if ($pages > $page): ?>
					<?php $ofs = $page*$limit; ?>
					<?php
						$params = array('md5'=>$md5_get, 'offset'=>$ofs);
						if ($_GET['only_select'] || $_POST['only_select']) { $params['only_select'] = 1; }
					?>
					<a href="<?php echo url(self(), $params);?>">Next &gt;&gt;</a>
				<?php endif; ?>
			</p>
			<?php endif; ?>

			<?php if ($found): ?>

				<?php
					$edit_table = table_from_query($base_query);
					if ($edit_table) {
						$edit_pk = array_first_key($rows[0]);
						if (is_numeric($edit_pk)) { $edit_table = false; }
					}
					if ($edit_table) {
						$types = table_columns($edit_table);
						if ($types && count($types)) {
							if (in_array($edit_pk, array_keys($types))) {
								if (!array_col_match_unique($rows, $edit_pk, '#^\d+$#')) {
									$edit_pk = guess_pk($rows);
									if (!$edit_pk) {
										$edit_table = false;
									}
								}
							} else {
								$edit_table = false;
							}
						} else {
							$edit_table = false;
						}
					}
					$edit_url = '';
					if ($edit_table) {
						$edit_url = url(self(true), array('action'=>'editrow', 'table'=>$edit_table, 'pk'=>$edit_pk, 'id'=>'%s'));
					}
				?>

				<table class="ls" cellspacing="1">
				<tr>
					<?php if ($edit_url): ?><th>#</th><?php endif; ?>
					<?php foreach ($rows[0] as $col => $v): ?>
						<th><?php echo $col;?></th>
					<?php endforeach; ?>
				</tr>
				<?php foreach ($rows as $row): ?>
				<tr onclick="mark_row(this, event)">
					<?php if ($edit_url): ?>
						<td valign=top><a href="javascript:void(0)" onclick="popup('<?php echo sprintf($edit_url, $row[$edit_pk]);?>', <?php echo EDITROW_POPUP_WIDTH; ?>, <?php echo EDITROW_POPUP_HEIGHT; ?>)">Edit</a>&nbsp;</td>
					<?php endif; ?>
					<?php
						$count_cols = 0;
						foreach ($row as $v) { $count_cols++; }
					?>
					<?php foreach ($row as $k => $v): ?>
						<?php
							if (preg_match('#^\s*<a[^>]+>[^<]+</a>\s*$#iU', $v) && strlen(strip_tags($v)) < 50) {
								$v = strip_tags($v, '<a>');
								$v = create_links($v);
							} else {
								$v = strip_tags($v);
								$v = str_replace('&nbsp;', ' ', $v);
								$v = preg_replace('#[ ]+#', ' ', $v);
								$v = create_links($v);
								if (!$full_content && strlen($v) > 50) {
									if (1 == $count_cols) {
										$v = truncate_html($v, 255);
									} else {
										$v = truncate_html($v, 50);
									}
								}
								// $v = html_once($v); - create_links() disabling
							}
							if ($full_content) {
								$v = str_wrap($v, 80, '<br>', true);
							}
							if ($full_content) {
								$v = nl2br($v);
							}
							//$v = stripslashes(stripslashes($v));
							if (isset($types[$k]) && $types && $types[$k] == 'int' && IsTimestampColumn($k, $v))
							{
								// 100 000 000 == 1973-03-03 10:46:40
								// Only big integers change to dates, so a low one like "1054"
								// does not get changed into a date, cause that would probably be wrong.

								$tmp = date('Y-m-d H:i', $v);
								if ($tmp) {
									$v = $tmp;
								}
							}
						?>
						<td <?php echo $full_content ? 'valign="top"':'';?> nowrap><?php echo is_null($row[$k])?'-':$v;?></td>
					<?php endforeach; ?>
				</tr>
				<?php endforeach; ?>
				</table>

			<?php endif; ?>

			<?php if ($pages > 1): ?>
			<p>
				<?php if ($page > 1): ?>
					<?php $ofs = ($page-1)*$limit-$limit; ?>
					<?php
						$params = array('md5'=>$md5_get, 'offset'=>$ofs);
						if ($_GET['only_select'] || $_POST['only_select']) { $params['only_select'] = 1; }
					?>
					<a href="<?php echo url(self(), $params);?>">&lt;&lt; Prev</a> &nbsp;
				<?php endif; ?>
				Page <b><?php echo $page;?></b> of <b><?php echo $pages;?></b> &nbsp;
				<?php if ($pages > $page): ?>
					<?php $ofs = $page*$limit; ?>
					<?php
						$params = array('md5'=>$md5_get, 'offset'=>$ofs);
						if ($_GET['only_select'] || $_POST['only_select']) { $params['only_select'] = 1; }
					?>
					<a href="<?php echo url(self(), $params);?>">Next &gt;&gt;</a>
				<?php endif; ?>
			</p>
			<?php endif; ?>

		<?php endif; ?>

	<?php
		$cont = ob_get_contents();
		ob_end_clean();
		$ret['data_html'] = $cont;
		return $ret;
	}

?>
<?php

	// ----------------------------------------------------------------
	// @sqleditor PHP
	// ----------------------------------------------------------------

	set_time_limit(0);

	$msg = '';
	$error = '';
	$top_html = '';
	$data_html = '';

	$template = GET("template", "string");
	GET("popup", "bool");
	GET("md5", "string");
	GET("full_content", "bool");
	GET("only_select", "bool");

	POST("sql", "string");
	POST("perform", "string");
	POST("only_select", "bool");
	POST("full_content", "bool");
	POST("save_as", "string");
	POST("load_from", "string");

	if ($_GET['md5']) {
		$_GET['only_select'] = true;
		$_GET['only_select'] = true;
	}

	if ($_GET['only_select']) { $_POST['only_select'] = 1; }

	$sql_dir = false;
	if (defined('DBKISS_SQL_DIR')) {
		$sql_dir = DBKISS_SQL_DIR;
	}

	if ($sql_dir) {
		if (!(dir_exists($sql_dir) && is_writable($sql_dir))) {
			if (!dir_exists($sql_dir) && is_writable('.')) {
				mkdir($sql_dir);
			} else {
				exit('You must create "'.$sql_dir.'" directory with write permission.');
			}
		}
		if (!file_exists($sql_dir.'/.htaccess')) {
			file_put($sql_dir.'/.htaccess', 'deny from all');
		}
		if (!file_exists($sql_dir.'/index.html')) {
			file_put($sql_dir.'/index.html', '');
		}
	}

	if ('GET' == $_SERVER['REQUEST_METHOD']) {
		if ($sql_dir)
		{
			if ($_GET['md5'] && preg_match('#^(\w{32,32})_(\d+)$#', $_GET['md5'], $match)) {
				$md5_i = $match[2];
				$md5_tmp = sprintf($sql_dir.'/zzz_%s.dat', $match[1]);
				$_POST['sql'] = file_get_contents($md5_tmp);
				$_SERVER['REQUEST_METHOD'] = 'POST';
				$_POST['perform'] = 'execute';
			} else if ($_GET['md5'] && preg_match('#^(\w{32,32})$#', $_GET['md5'], $match)) {
				$md5_tmp = sprintf($sql_dir.'/zzz_%s.dat', $match[1]);
				$_POST['sql'] = file_get_contents($md5_tmp);
				$_GET['md5'] = '';
			} else {
				if ($_GET['md5']) {
					trigger_error('invalid md5', E_USER_ERROR);
				}
			}
		}
	} else {
		$_GET['md5'] = '';
	}

	$_POST['sql'] = trim($_POST['sql']);
	$md5 = md5($_POST['sql']);
	$md5_file = sprintf($sql_dir.'/zzz_%s.dat', $md5);
	if ($sql_dir && $_POST['sql']) {
		file_put($md5_file, $_POST['sql']);
	}

	if ($sql_dir && 'save' == $_POST['perform'] && $_POST['save_as'] && $_POST['sql'])
	{
		$_POSAT['save_as'] = str_replace('.sql', '', $_POST['save_as']);
		if (preg_match('#^[\w ]+$#', $_POST['save_as'])) {
			$file = $sql_dir.'/'.$_POST['save_as'].'.sql';
			$overwrite = '';
			if (file_exists($file)) {
				$overwrite = ' - <b>overwritten</b>';
				$bak = $sql_dir.'/zzz_'.$_POST['save_as'].'_'.md5(file_get_contents($file)).'.dat';
				copy($file, $bak);
			}
			$msg .= sprintf('<div>Sql saved: %s %s</div>', basename($file), $overwrite);
			file_put($file, $_POST['sql']);
		} else {
			error('Saving sql failed: only alphanumeric chars are allowed');
		}
	}

	if ($sql_dir) {
		$load_files = dir_read($sql_dir, null, array('.sql'), 'date_desc');
	}
	$load_assoc = array();
	if ($sql_dir) {
		foreach ($load_files as $file) {
			$file_path = $file;
			$file = basename($file);
			$load_assoc[$file] = '('.substr(file_date($file_path), 0, 10).')'.' ' .$file;
		}
	}

	if ($sql_dir && 'load' == $_POST['perform'])
	{
		$file = $sql_dir.'/'.$_POST['load_from'];
		if (array_key_exists($_POST['load_from'], $load_assoc) && file_exists($file)) {
			$msg .= sprintf('<div>Sql loaded: %s (%s)</div>', basename($file), timestamp(file_date($file)));
			$_POST['sql'] = file_get_contents($file);
			$_POST['save_as'] = basename($file);
			$_POST['save_as'] = str_replace('.sql', '', $_POST['save_as']);
		} else {
			error('<div>File not found: %s</div>', $file);
		}
	}

	// after load - md5 may change
	$md5 = md5($_POST['sql']);

	if ($sql_dir && 'load' == $_POST['perform'] && !error()) {
		$md5_tmp = sprintf($sql_dir.'/zzz_%s.dat', $md5);
		file_put($md5_tmp, $_POST['sql']);
	}

	$is_sel = false;

	$queries = preg_split("#;(\s*--[ \t\S]*)?(\r\n|\n|\r)#U", $_POST['sql']);
	foreach ($queries as $k => $query) {
		$query = query_strip($query);
		if (0 === strpos($query, '@')) {
			$is_sel = true;
		}
		$queries[$k] = $query;
		if (!trim($query)) { unset($queries[$k]); }
	}

	$sql_assoc = array();
	$sql_selected = false;
	$i = 0;

	$params = array(
		'md5' => $md5,
		'only_select' => $_GET["only_select"] || $_POST['only_select'],
		'full_content' => $_GET["full_content"] || $_POST['full_content'],
		'offset' => ''
	);
	$sql_main_url = url(self(), $params);

	foreach ($queries as $query) {
		$i++;
		$query = preg_replace("#^@#", "", $query);
		if (!is_select($query) && !is_show($query)) {
			continue;
		}
		$query = preg_replace('#\s+#', ' ', $query);
		$params = array(
			'md5' => $md5.'_'.$i,
			'only_select' => $_GET["only_select"] || $_POST['only_select'],
			'full_content' => $_GET["full_content"] || $_POST['full_content'],
			'offset' => ''
		);
		$url = url(self(), $params);
		if ($_GET['md5'] && $_GET['md5'] == $params['md5']) {
			$sql_selected = $url;
		}
		$sql_assoc[$url] = str_truncate(strip_tags($query), 80);
	}

	if ('POST' == $_SERVER['REQUEST_METHOD'])
	{
		if (!$_POST['perform']) {
			$error = 'No action selected.';
		}
		if (!$error)
		{
			$time = time_start();
			switch ($_POST['perform']) {
				case 'execute':
					$i = 0;
					db_begin();
					$commit = true;
					foreach ($queries as $query)
					{
						$i++;
						if ($is_sel) {
							if (0 === strpos($query, '@')) {
								$query = substr($query, 1);
							} else {
								if (!$_GET['md5']) { continue; }
							}
						}
						if ($_POST['only_select'] && !is_select($query) && !is_show($query)) {
							continue;
						}
						if ($_GET['md5'] && $i != $md5_i) {
							continue;
						}
						if ($_GET['md5'] && $i == $md5_i) {
							if (!is_select($query) && !is_show($query)) {
								trigger_error('not select query', E_USER_ERROR);
							}
						}

						$exec = listing($query, $md5.'_'.$i);
						$query_trunc = str_truncate(html_once($query), 1000);
						$query_trunc = query_color($query_trunc);
						$query_trunc = nl2br($query_trunc);
						$query_trunc = html_spaces($query_trunc);
						if ($exec['error']) {
							$exec['error'] = preg_replace('#error:#i', '', $exec['error']);
							$top_html .= sprintf('<div style="background: #ffffd7; padding: 0.5em; border: #ccc 1px solid; margin-bottom: 1em; margin-top: 1em;"><b style="color:red">Error</b>: %s<div style="margin-top: 0.25em;"><b>Query %s</b>: %s</div></div>', $exec['error'], $i, $query_trunc);
							$commit = false;
							break;
						} else {
							$query_html = sprintf('<div class="query"><b style="font-size: 10px;">Query %s</b>:<div style="'.$sql_font.' margin-top: 0.35em;">%s</div></div>', $i, $query_trunc);
							$data_html .= $query_html;
							$data_html .= $exec['data_html'];
						}
					}
					if ($commit) {
						db_end();
					} else {
						db_rollback();
					}
					break;
			}
			$time = time_end($time);
		}
	}

?>
<?php rawlayout_start('SQL Editor ' . ("[".($db_name_h1?$db_name_h1:$db_name))."]"); ?>

	<?php

	// ----------------------------------------------------------------
	// @sqleditor HTML
	// ----------------------------------------------------------------

	?>

	<?php if ($_GET['popup']): ?>
		<h1>SQL Editor [<span style="<?php echo $db_name_style;?>"><?php echo $db_name_h1?$db_name_h1:$db_name;?></span>]</h1>
	<?php else: ?>
		<h1><a class=blue style="<?php echo $db_name_style;?>" href="<?php echo $_SERVER['PHP_SELF'];?>">
			<?php echo $db_name_h1?$db_name_h1:$db_name;?></a> &gt; SQL Editor</h1>
	<?php endif; ?>

	<?php echo error();?>

	<script>
	function sql_submit(form)
	{
		if (form.perform.value.length) {
			return true;
		}
		return false;
	}
	function sql_execute(form)
	{
		form.perform.value='execute';
		form.submit();
	}
	function sql_preview(form)
	{
		form.perform.value='preview';
		form.submit();
	}
	function sql_save(form)
	{
		form.perform.value='save';
		form.submit();
	}
	function sql_load(form)
	{
		if (form.load_from.selectedIndex)
		{
			form.perform.value='load';
			form.submit();
			return true;
		}
		button_clear(form);
		return false;
	}
	</script>

	<?php if ($msg): ?>
		<div class="msg"><?php echo $msg;?></div>
	<?php endif; ?>

	<?php echo $top_html;?>

	<?php if (count($sql_assoc)): ?>
		<p>
			SELECT queries:
			<select name="sql_assoc" onchange="if (this.value.length) location=this.value">
				<option value="<?php echo html_once($sql_main_url);?>"></option>
				<?php echo options($sql_assoc, $sql_selected);?>
			</select>
		</p>
	<?php endif; ?>

	<?php if ($_GET['md5']): ?>
		<?php echo $data_html;?>
	<?php endif; ?>

	<form action="<?php echo $_SERVER['PHP_SELF'];?>?execute_sql=1&popup=<?php echo $_GET['popup'];?>" method="post" onsubmit="return sql_submit(this);" style="margin-top: 1em;">
	<input type="hidden" name="perform" value="">
	<div style="margin-bottom: 0.25em;">
		<textarea accesskey=s id="sql_area" name="sql" class="sql_area"><?php echo htmlspecialchars(query_upper($_POST['sql']));?></textarea>
	</div>
	<table cellspacing="0" cellpadding="0"><tr>
	<td nowrap>
		<input type="button" wait="1" class="button" value="Execute" onclick="sql_execute(this.form); ">
	</td>
	<td nowrap>
		&nbsp;
		<input type="button" wait="1" class="button" value="Preview" onclick="sql_preview(this.form); ">
	</td>
	<td nowrap>
		&nbsp;
		<input type="checkbox" name="only_select" id="only_select" value="1" <?php echo checked($_POST['only_select'] || $_GET['only_select']);?>>
	</td>
	<td nowrap>
		<label for="only_select">Only SELECT queries</label>
	</td>
	<td nowrap>
		&nbsp;
		<input type="checkbox" name="full_content" id="full_content" value="1" <?php echo checked($_POST['full_content'] || $_GET['full_content']);?>>
	</td>
	<td nowrap>
		<label for="full_content">Full content</label>
		&nbsp;
	</td>



	<td nowrap>
		<input type="text" name="save_as" value="<?php echo html_once($_POST['save_as']);?>">
		&nbsp;
	</td>
	<td nowrap>
		<input type="button" wait="1" class="button" value="Save" onclick="sql_save(this.form); ">
		&nbsp;&nbsp;&nbsp;
	</td>
	<td nowrap>
		<select name="load_from" style="width: 140px;"><option value=""></option><?php echo options($load_assoc);?></select>
		&nbsp;
	</td>
	<td nowrap>
		<input type="button" wait="1" class="button" value="Load" onclick="return sql_load(this.form);">
	</td>
	</tr></table>
	</form>

	<?php

		if ('preview' == $_POST['perform'])
		{
			echo '<h2>Preview</h2>';
			$i = 0;
			foreach ($queries as $query)
			{
				$i++;
				$query = preg_replace("#^@#", "", $query);
				$query = html_once($query);
				$query = query_color($query);
				$query = nl2br($query);
				$query = html_spaces($query);
				printf('<div class="query"><b style="font-size: 10px;">Query %s</b>:<div style="'.$sql_font.' margin-top: 0.35em;">%s</div></div>', $i, $query);
			}
		}

	?>

	<?php if (!$_GET['md5']): ?>
		<script>Element('sql_area').focus();</script>
		<?php echo $data_html;?>
	<?php endif; ?>

	<?php rawlayout_end(); ?>

<?php exit(); endif; ?>

<?php if (GET("viewtable", "string")): ?>
<?php

	// ----------------------------------------------------------------
	// @viewtable
	// ----------------------------------------------------------------

	GET("viewtable", "string");
	GET("full_content", "bool");
	GET("search", "string");
	GET("column", "string");
	GET("column_type", "string");
	GET("offset", "int");
	GET("order_by", "string");
	GET("order_desc", "bool");
	POST("full_content", "bool");

	set_time_limit(0);

	$full_content = ($_GET["full_content"] || $_POST["full_content"]);

	$table = $_GET['viewtable'];
	$table_enq = quote_table($table);
	$count = db_one("SELECT COUNT(*) FROM $table_enq");

	$types = table_columns($table);
	$columns = array_assoc(array_keys($types));
	$columns2 = $columns;

	foreach ($columns2 as $k => $v) {
		$columns2[$k] = $v.' ('.$types[$k].')';
	}
	$types_group = table_columns_group($types);

	$where = '';
	$found = $count;
	if ($_GET['search']) {
		$search = $_GET['search'];
		$cols2 = array();

		if ($_GET['column']) {
			$cols2[] = $_GET['column'];
		} else {
			$cols2 = $columns;
		}
		$where = '';
		$search = db_escape($search);
		$search = str_replace(array("%", "_"), array("\%", "\_"), $search);

		$column_type = '';
		if (!$_GET['column']) {
			$column_type = $_GET['column_type'];
		} else {
			$_GET['column_type'] = '';
		}

		$ignore_int = false;
		$ignore_time = false;

		foreach ($columns as $col)
		{
			if (!$_GET['column'] && $column_type) {
				if ($types[$col] != $column_type) {
					continue;
				}
			}
			if (!$column_type && !is_numeric($search) && strstr($types[$col], 'int')) {
				$ignore_int = true;
				continue;
			}
			if (!$column_type && is_numeric($search) && ( strstr($types[$col], 'time') || strstr($types[$col], 'date') )) {
				$ignore_time = true;
				continue;
			}
			if ($_GET['column'] && $col != $_GET['column']) {
				continue;
			}
			if ($where) { $where .= ' OR '; }
			if (is_numeric($search)) {
				if ("mysql" == $db_driver) {
					$where .= "`$col` = '$search' ";
				} else {
					// pgsql, sqlite
					$where .= "\"$col\" = '$search' ";
				}
			} else {
				if ('mysql' == $db_driver) {
					$where .= "`$col` LIKE '%$search%' ";
				} else if ('pgsql' == $db_driver) {
					$where .= "\"$col\" ILIKE '%$search%' ";
				} else if ("sqlite" == $db_driver) {
					$where .= "\"$col\" LIKE '%$search%' ";
				} else {
					trigger_error('db_driver not implemented');
				}
			}
		}
		if (($ignore_int || $ignore_time) && !$where) {
			$where .= ' 1=2 ';
		}
		$where = 'WHERE '.$where;
	}

	if ($where) {
		$table_enq = quote_table($table);
		$found = db_one("SELECT COUNT(*) FROM $table_enq $where");
	}

	$limit = 50;
	$offset = $_GET["offset"];
	$page = floor($offset / $limit + 1);
	$pages = ceil($found / $limit);


	$pk = table_pk($table);

	// Pkeys needed for "Edit" row link parameter.

	if (strstr($pk, ":")) {
		$pkeys = $pk;
		$pkeys_enq = QuotePkeys($pkeys);
	} else {
		$pk_enq = quote_column($pk);
		$pkeys = false;
	}

	$order = "ORDER BY";
	if ($_GET['order_by']) {
		$order_by_enq = quote_column($_GET['order_by']);
		if ("char" == $types[$_GET["order_by"]] || "text" == $types[$_GET["order_by"]]) {
			$order .= " LOWER($order_by_enq)";
		} else {
			$order .= " $order_by_enq";
		}
	} else {
		if ($pk) {
			if ($pkeys) {
				$order .= " $pkeys_enq";
				$_GET["order_by"] = FirstFromPkeys($pk); // So that when clicking on first column to sort it will be sorted Descendant.
			} else {
				$order .= " $pk_enq";
				$_GET["order_by"] = $pk; // So that when clicking on first column to sort it will be sorted Descendant.
			}
		} else {
			$order = '';
		}
	}

	if ($_GET['order_desc']) { $order .= ' DESC'; }

	$table_enq = quote_table($table);
	$base_query = "SELECT * FROM $table_enq $where $order";
	$rs = db_query(db_limit($base_query, $offset, $limit));

	if ($count && $rs) {
		$rows = array();
		while ($row = db_row($rs)) {
			$rows[] = $row;
		}
		db_free($rs);
		// If there are multiple pkeys then that is 100% sure and we do not have to check it later.
		if (!$pkeys) {
			if (count($rows) && !array_col_match_unique($rows, $pk, '#^\d+$#')) {
				$pk = guess_pk($rows);
			}
		}
	}

	function indenthead($str)
	{
		if (is_array($str)) {
			$str2 = '';
			foreach ($str as $k => $v) {
				$str2 .= sprintf('%s: %s'."\r\n", $k, $v);
			}
			$str = $str2;
		}
		$lines = explode("\n", $str);
		$max_len = 0;
		foreach ($lines as $k => $line) {
			$lines[$k] = trim($line);
			if (preg_match('#^[^:]+:#', $line, $match)) {
				if ($max_len < strlen($match[0])) {
					$max_len = strlen($match[0]);
				}
			}
		}
		foreach ($lines as $k => $line) {
			if (preg_match('#^[^:]+:#', $line, $match)) {
				$lines[$k] = str_replace($match[0], $match[0].str_repeat('&nbsp;', $max_len - strlen($match[0])), $line);
			}
		}
		return implode("\r\n", $lines);
	}

	?>

	<?php

	// ----------------------------------------------------------------
	// @viewtable HTML
	// ----------------------------------------------------------------

	?>

	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $page_charset;?>">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo $db_name_h1?$db_name_h1:$db_name;?> &gt; <?php echo $table;?></title>
		<link rel="shortcut icon" href="<?php echo $_SERVER['PHP_SELF']; ?>?dbkiss_favicon=1">
	</head>
	<body>

	<?php layout(); ?>

	<h1><a class=blue style="<?php echo $db_name_style;?>" href="<?php echo $_SERVER['PHP_SELF'];?>"><?php echo $db_name_h1?$db_name_h1:$db_name;?></a> &gt; <?php echo $table;?></h1>

	<?php conn_info(); ?>

	<p>
		<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>">All tables</a>
		&nbsp;&gt;&nbsp;
		<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?viewtable=<?php echo $table;?>"><?php echo $table;?></a> (<?php echo $count;?>)
		&nbsp;&nbsp;/&nbsp;&nbsp;

		Export to CSV:&nbsp;

		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=csv&separator=<?php echo urlencode('|');?>&query=<?php echo base64_encode($base_query); ?>">pipe</a>
		-
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=csv&separator=<?php echo urlencode("\t");?>&query=<?php echo base64_encode($base_query); ?>">tab</a>
		-
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=csv&separator=<?php echo urlencode(',');?>&query=<?php echo base64_encode($base_query); ?>">comma</a>
		-
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?export=csv&separator=<?php echo urlencode(';');?>&query=<?php echo base64_encode($base_query); ?>">semicolon</a>
		&nbsp;
		<?php AutoFocus_HelpLink(); ?>
		&nbsp;

		<!--
		<a title="Click to see some help for this page." class="help" style="margin-bottom: -4px;" href="javascript:;" onclick="Tooltip(this, 'Help_MarkRows')"></a>

		<div id="Help_MarkRows" style="display: none;">
			You can <b>mark</b> rows on the listing by clicking on the row while <b>holding CTRL</b>.<br>
			Row's background will change to <b>darker gray</b> when you do this.<br>
			This can be helpful in a few cases, one of them is to be able to <b>compare visually</b> some of the records on current page.<br>
			Unfortunately, that marking won't be <b>preserved</b> when you go to the Next or Previous page.<br>
			<br>
			But such a feature could be implemented in the future, some kind of <b>virtual table</b> to which the marked records<br>
			will be put, so you would be able to come back later and list them in an easy manner. If you would like such a feature <br>
			then <b>Vote for it</b> by contacting the author of this project.
		</div>
		-->

		<!--
		&nbsp;&nbsp;/&nbsp;&nbsp;
		Functions:
		<a href="<?php echo $_SERVER['PHP_SELF'];?>?viewtable=<?php echo $table;?>&indenthead=1">indenthead()</a>
		-->
	</p>

	<?php AutoFocus_Script(); ?>

	<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get" style="margin-bottom: 1em;" name=AutoFocus_Form onsubmit="document.getElementById('AutoFocus_Submit').focus()">
	<input type="hidden" name="viewtable" value="<?php echo $table;?>">

	<div>
		<u>S</u>earch: <input size=30 type="text" name="search" value="<?php echo html_once($_GET['search']);?>" id=AutoFocus_Input onfocus="this.setAttribute('isfocused', '1');" onblur="this.setAttribute('isfocused', '');" autocomplete=off></td>
		<input type="submit" value="Search" id=AutoFocus_Submit>

		<!--
		<input type="checkbox" name="full_content" id="full_content" <?php echo checked($full_content);?>>
		<label for="full_content">Full content</label>
		-->

		<select><option>Truncate content</option><option>Full content</option></select>

		&nbsp;<a class=help href="javascript:;" title="Help: searching" onclick="Tooltip(this, 'Help_TableSearch')"></a>
	</div>

	<div>
		Column: <select name="column"><option value=""></option><?php echo options($columns2, $_GET['column']);?></select>

		Type: <select name="column_type"><option value=""></option><?php echo options($types_group, $_GET['column_type']);?></select>

		S<u>o</u>rt:
		<select accesskey=o id="order_by" name="order_by"><option value=""></option>
			<?php echo options($columns, $_GET['order_by']);?></select>

		<!--
		<input type="checkbox" name="order_desc" id="order_desc" value="1" <?php echo checked($_GET['order_desc']);?>>
		<label for="order_desc">Descending order</label>
		-->

		Sort type:
		<select><option>Sort up</option><option>Sort down</option></select>

		Export query:
		<a href="">View in SQL Editor</a>



	</div>

	</form>

	<div id=Help_TableSearch style="display: none;">
		Wyszukiwanie: id=nick, gdy INT to dokladnie sprawdza, gdy char lub text to uzywa LIKE.<br>
		Wystarczy wpisac czesc nazwy kolumny, nie trzeba calej, wtedy znajdzie 1 kolumne ktora zawiera to slowo i w niej bedzie wyszukiwał.<br>
		Sort: w liscie dodac sortowanie desc, czyli: id, id DESC, name, name DESC - w &lgt;options&gt;<br>
		Type: dac jako checkboxy? Zrobic to jakos wygodniej zeby nie bylo rozdzielenia na Column i Type jak jest teraz.
	</div>

	<?php if ($count): ?>

		<?php if ($count && $count != $found): ?>
			<p>Found: <b><?php echo $found;?></b></p>
		<?php endif; ?>

		<?php if ($found): ?>

			<?php if ($pages > 1): ?>
			<p>

				<?php if ($page > 1): ?>
					<a href="<?php echo url_offset(($page-1)*$limit-$limit);?>">&lt;&lt; Prev</a> &nbsp;
				<?php endif; ?>
				Page <b><?php echo $page;?></b> of <b><?php echo $pages;?></b> &nbsp;
				<?php if ($pages > $page): ?>
					<a href="<?php echo url_offset($page*$limit);?>">Next &gt;&gt;</a>
				<?php endif; ?>

			</p>
			<?php endif; ?>

			<table class="ls" cellspacing="0">
			<tr>
				<?php if ($pk || $pkeys): ?><th>#</th><?php endif; ?>
				<?php foreach ($columns as $col): ?>
					<?php
						$params = array('order_by'=>$col);
						$params['order_desc'] = 0;
						$params["offset"] = 0;
						$col2 = $col;
						if ($_GET['order_by'] == $col) {
							if ($_GET["order_desc"]) {
								$col2 = "$col2 <span class=downarrow1 onmousedown=\"Sort_Click(document.getElementById('current_order_arrow'), event);\"></span>";
							} else {
								$col2 = "$col2 <span class=uparrow1 onmousedown=\"Sort_Click(document.getElementById('current_order_arrow'), event);\"></span>";
							}
							$params['order_desc'] = $_GET['order_desc'] ? 0 : 1;
						}
						$camelcase = (strtolower($col) != $col);
					?>
					<th class="sortable <?php echo $camelcase; ?>">
						<a href="javascript:void(0)"
							<?php if ($_GET["order_by"] == $col): ?>id="current_order_arrow"<?php endif; ?>
							onmouseover="Sort_Mouseover(this, event)"
							onmouseout="Sort_Mouseout(this, event)"
							onmousemove="Sort_Mousemove(this, event)"
							onmousedown="Sort_Click(this, event)"
							mylink="<?php echo url(self(), $params);?>">
							<?php echo $col2;?></a></th>
				<?php endforeach; ?>
			</tr>
			<?php
				$get_search = $_GET['search'];
			?>
			<?php
				$edit_url_tpl = url(self(true), array('action'=>'editrow', 'table'=>$table, 'pk'=>$pkeys ? $pkeys : $pk, 'id'=>'EDIT_URL_TPL_ID'));
			?>
			<?php foreach ($rows as $row): ?>
			<tr onclick="mark_row(this, event)">
				<?php if ($pk || $pkeys): ?>
					<?php $edit_url = str_replace("EDIT_URL_TPL_ID", EncodeRowId($row, $pk, $pkeys), $edit_url_tpl); ?>
					<td valign=top><a href="javascript:void(0)" onclick="popup('<?php echo $edit_url;?>', <?php echo EDITROW_POPUP_WIDTH; ?>, <?php echo EDITROW_POPUP_HEIGHT; ?>)">Edit</a>&nbsp;</td>
				<?php endif; ?>
				<?php foreach ($row as $k => $v): ?>
					<?php
						$v = strip_tags($v);
						$v = create_links($v);
						if (!$full_content) {
							$v = truncate_html($v, 50);
						}
						//$v = html_once($v);
						//$v = htmlspecialchars($v); -- create_links() disabling
						if ($full_content) {
							$v = str_wrap($v, 80, '<br>', true);
						}
						if ($full_content) {
							$v = nl2br($v);
						}
						//$v = stripslashes(stripslashes($v));
						if ($get_search) {
							$search = $_GET['search'];
							if (isset($_GET["column"]) && $_GET["column"]) {
								// When search specific columns highlight the search phrase only for that column.
								if ($k == $_GET["column"]) {
									$v = ColorSearchPhrase($v, $search);
								}
							} else {
								$v = ColorSearchPhrase($v, $search);
							}
						}
						if ($types[$k] == 'int' && IsTimestampColumn($k, $v))
						{
							$tmp = date('Y-m-d H:i', $v);
							if ($tmp) {
								$v = $tmp;
							}
						}
					?>
					<td  <?php echo $full_content ? 'valign="top"':'';?> nowrap><?php echo is_null($row[$k])?'-':$v;?></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
			</table>

			<?php if ($pages > 1): ?>
			<p>
				<?php if ($page > 1): ?>
					<a href="<?php echo url_offset(($page-1)*$limit-$limit);?>">&lt;&lt; Prev</a> &nbsp;
				<?php endif; ?>
				Page <b><?php echo $page;?></b> of <b><?php echo $pages;?></b> &nbsp;
				<?php if ($pages > $page): ?>
					<a href="<?php echo url_offset($page*$limit);?>">Next &gt;&gt;</a>
				<?php endif; ?>
			</p>
			<?php endif; ?>

		<?php endif; ?>

	<?php endif; ?>

</body>
</html>
<?php exit(); endif; ?>


<?php if (GET("searchdb", "bool")): ?>
<?php

	// ----------------------------------------------------------------
	// @searchdb PHP
	// ----------------------------------------------------------------

	GET("types", "array");
	GET("search", "string");
	GET("md5", "bool");
	GET("table_filter", "string");

	$_GET['search'] = trim($_GET['search']);

	$tables = list_tables();

	if ($_GET['table_filter']) {
		foreach ($tables as $k => $table) {
			if (!str_has_any($table, $_GET['table_filter'], $ignore_case = true)) {
				unset($tables[$k]);
			}
		}
	}

	$all_types = array();
	$columns  = array();
	foreach ($tables as $table) {
		$types = table_columns($table);
		$columns[$table] = $types;
		$types = array_values($types);
		$all_types = array_merge($all_types, $types);
	}
	$all_types = array_unique($all_types);

	if ($_GET['search'] && $_GET['md5']) {
		$_GET['search'] = md5($_GET['search']);
	}

?>
<?php rawlayout_start(sprintf('%s &gt; Search', $db_name)); ?>

	<?php
	// ----------------------------------------------------------------
	// @searchdb HTML
	// ----------------------------------------------------------------
	?>

	<h1><a class=blue style="<?php echo $db_name_style;?>" href="<?php echo $_SERVER['PHP_SELF'];?>"><?php echo $db_name_h1?$db_name_h1:$db_name;?></a> &gt; Search</h1>
	<?php conn_info(); ?>

	<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
	<input type="hidden" name="searchdb" value="1">
	<table class="ls2" cellspacing="1">
	<tr>
		<th>Search:</th>
		<td>
			<input type="text" name="search" value="<?php echo html_once($_GET['search']);?>" size="40">
			<?php if ($_GET['search'] && $_GET['md5']): ?>
				md5(<?php echo html_once($_GET['search']);?>)
			<?php endif; ?>
			<input type="checkbox" name="md5" id="md5_label" value="1">
			<label for="md5_label">md5</label>
		</td>
	</tr>
	<tr>
		<th>Table filter:</th>
		<td><input type="text" name="table_filter" value="<?php echo html_once($_GET['table_filter']);?>">
	</tr>
	<tr>
		<th>Columns:</th>
		<td>
			<?php foreach ($all_types as $type): ?>
				<input type="checkbox" id="type_<?php echo $type;?>" name="types[<?php echo $type;?>]" value="1" <?php echo checked(isset($_GET['types'][$type]));?>>
				<label for="type_<?php echo $type;?>"><?php echo $type;?></label>
			<?php endforeach; ?>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="none">
			<input type="submit" value="Search">
		</td>
	</tr>
	</table>
	</form>

	<?php if ($_GET['search'] && !count($_GET['types'])): ?>
		<p>No columns selected.</p>
	<?php endif; ?>

	<?php if ($_GET['search'] && count($_GET['types'])): ?>

		<p>Searching <b><?php echo count($tables);?></b> tables for: <b><?php echo htmlspecialchars($_GET['search']);?></b></p>

		<?php $found_any = false; ?>

		<?php set_time_limit(0); ?>

		<?php foreach ($tables as $table): ?>
			<?php

				$where = '';
				$cols2 = array();

				$where = '';
				$search = db_escape($_GET['search']);
				$search = str_replace(array("%", "_"), array("\%", "\_"), $search);

				foreach ($columns[$table] as $col => $type)
				{
					if (!in_array($type, array_keys($_GET['types']))) {
						continue;
					}
					if ($where) {
						$where .= ' OR ';
					}
					if (is_numeric($search)) {
						if ("mysql" == $db_driver) {
							$where .= "`$col` = '$search' ";
						} else {
							// pgsql, sqlite
							$where .= "\"$col\" = '$search' ";
						}
					} else {
						if ('mysql' == $db_driver) {
							$where .= "`$col` LIKE '%$search%' ";
						} else if ('pgsql' == $db_driver) {
							$where .= "\"$col\" ILIKE '%$search%' ";
						} else if ("sqlite" == $db_driver) {
							$where .= "\"$col\" LIKE '%$search%' ";
						} else {
							trigger_error('db_driver not implemented');
						}
					}
				}

				$found = false;

				if ($where) {
					$where = 'WHERE '.$where;
					$table_enq = quote_table($table);
					$found = db_one("SELECT COUNT(*) FROM $table_enq $where");
				}

				if ($found) {
					$found_any = true;
				}

			?>

			<?php
				if ($where && $found) {
					$limit = 10;
					$offset = 0;

					$pk = table_pk($table);

					if (strstr($pk, ":")) {
						$pkeys = $pk;
						$pkeys_enq = QuotePkeys($pkeys);
					} else {
						$pkeys = false;
						$pk_enq = quote_column($pk);
					}

					if ($pkeys) {
						$order = "ORDER BY $pkeys_enq";
					} else {
						$order = "ORDER BY $pk_enq";
					}

					$table_enq = quote_table($table);
					$rs = db_query(db_limit("SELECT * FROM $table_enq $where $order", $offset, $limit));

					$rows = array();
					while ($row = db_row($rs)) {
						$rows[] = $row;
					}
					db_free($rs);

					if (!$pkeys) {
						// If there are multiple primary keys then this pkeys are 100% sure, we do not have to guess.
						if (count($rows) && !array_col_match_unique($rows, $pk, '#^\d+$#')) {
							$pk = guess_pk($rows);
						}
					}
				}
			?>

			<?php if ($where && $found): ?>

				<p>
					Table: <a href="<?php echo $_SERVER['PHP_SELF'];?>?viewtable=<?php echo $table;?>&search=<?php echo urlencode($_GET['search']);?>"><b><?php echo $table;?></b></a><br>
					Found: <b><?php echo $found;?></b>
					<?php if ($found > $limit): ?>
						&nbsp;<a href="<?php echo $_SERVER['PHP_SELF'];?>?viewtable=<?php echo $table;?>&search=<?php echo urlencode($_GET['search']);?>">show all &gt;&gt;</a>
					<?php endif; ?>
				</p>

				<table class="ls" cellspacing="1">
				<tr>
					<?php if ($pk || $pkeys): ?><th>#</th><?php endif; ?>
					<?php foreach ($columns[$table] as $col => $type): ?>
						<th><?php echo $col;?></th>
					<?php endforeach; ?>
				</tr>
				<?php foreach ($rows as $row): ?>
				<tr>
					<?php if ($pk || $pkeys): ?>
						<?php $edit_url = url(self(true), array('action'=>'editrow', 'table'=>$table, 'pk'=>$pkeys ? $pkeys : $pk, 'id'=>EncodeRowId($row, $pk, $pkeys))); ?>
						<td valign=top><a href="javascript:void(0)" onclick="popup('<?php echo $edit_url;?>', <?php echo EDITROW_POPUP_WIDTH; ?>, <?php echo EDITROW_POPUP_HEIGHT; ?>)">Edit</a>&nbsp;</td>
					<?php endif; ?>
					<?php foreach ($row as $k => $v): ?>
						<?php
							$v = str_truncate($v, 50);
							$v = html_once($v);
							//$v = stripslashes(stripslashes($v));
							$search = $_GET['search'];
							if ($columns[$table][$k] == 'int' && IsTimestampColumn($k, $v)) {
								$tmp = date('Y-m-d H:i', $v);
								if ($tmp) {
									$v = $tmp;
								}
							}
							$v = ColorSearchPhrase($v, $search);
						?>
						<td nowrap><?php echo $v;?></td>
					<?php endforeach; ?>
				</tr>
				<?php endforeach; ?>
				</table>

			<?php endif; ?>

		<?php endforeach; ?>

		<?php if (!$found_any): ?>
			<p>No rows found.</p>
		<?php endif; ?>

	<?php endif; ?>

	<?php rawlayout_end(); ?>
<?php exit; endif; ?>

<?php

	// ----------------------------------------------------------------
	// @mainscreen PHP
	// ----------------------------------------------------------------

	GET("table_filter", "string");
	GET("views_count", "bool");
	GET("precise_count", "bool");

	$tables = list_tables();
	$status = table_status();
	$views = list_tables(true);

?>

<?php

	// ----------------------------------------------------------------
	// @mainscreen HTML
	// ----------------------------------------------------------------

?>

	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $page_charset;?>">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo $db_name_h1?$db_name_h1:$db_name;?></title>
		<link rel="shortcut icon" href="<?php echo $_SERVER['PHP_SELF']; ?>?dbkiss_favicon=1">
	</head>
	<body>

	<?php layout(); ?>

	<?php if (stristr($_SERVER["HTTP_USER_AGENT"], "MSIE") && stristr($_SERVER["HTTP_USER_AGENT"], "Trident")): ?>
		<div style="background: rgb(255, 255, 225); padding: 0.5em 1em; border: #ddd 1px solid; display: inline-block;">
			Internet Explorer is not supported. You may see interface glitches and javascript features broken.<br>
			Try using some decent browser like: Chrome, Firefox, Opera, Safari.
		</div>
	<?php endif; ?>

	<h1  style="<?php echo $db_name_style;?>"><?php echo $db_name_h1?$db_name_h1:$db_name;?></h1>

	<?php conn_info(); ?>

	<p>
		Tables: <b><?php echo count($tables);?></b>
		&nbsp;-&nbsp;
		Total size: <b><?php echo number_format(ceil($status['total_size']/1024),0,'',' ').' k';?></b>
		&nbsp;-&nbsp;
		Views: <b><?php echo count($views);?></b>
		&nbsp;-&nbsp;

		<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?searchdb=1&table_filter=<?php echo html_once($_GET['table_filter']);?>">Search</a>
		&nbsp;-&nbsp;
		<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?import=1">Import SQL</a>
		&nbsp;-&nbsp;
		D<u>u</u>mp database:

		<?php
		$export_structure =  $_SERVER['PHP_SELF'] . "?dump_all=1&table_filter=" . urlencode(html_once($_GET['table_filter'])); // Structure only.
		$export_data = $_SERVER['PHP_SELF'] . "?dump_all=2&table_filter=" . urlencode(html_once($_GET['table_filter'])); // Data and structure.
		?>

		<?php if ('pgsql' == $db_driver): ?>
			<select accesskey=u id=dump_database onchange="if (this.value) { window.location.href=this.value; }">
				<option value=""></option>
				<option value="<?php echo $export_data; ?>">Data only</option>
			</select>
		<?php else: ?>
			<select accesskey=u id=dump_database onchange="if (this.value) { window.location.href=this.value; }">
				<option value=""></option>
				<option value="<?php echo $export_structure; ?>">Structure only</option>
				<option value="<?php echo $export_data; ?>">Data and structure</option>
			</select>
		<?php endif; ?>

		&nbsp;<a title="Help: dumping database" class=help href="javascript:;" onclick="Tooltip(this, 'Help_DumpDatabase')"></a>

		<div id=Help_DumpDatabase style="display: none;">
			Dumping database takes into account the <b>table search</b> on the main page.<br>It allows
			you to dump only the <b>found tables</b>.<br>
			<div style="margin-top: 0.5em;">Do not make mistake by thinking that you are making a dump of the <b>whole database</b>,<br>
			when in fact you are dumping only <b>some of the tables</b>.</div>
		</div>

	</p>

	<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get" name=AutoFocus_Form style="margin-bottom: 0.5em;" onsubmit="document.getElementById('AutoFocus_Submit').focus()">
	<table cellspacing="0" cellpadding="0"><tr>
	<td style="padding-right: 3px;">Table:</td>
	<td style="padding-right: 3px;">
		<input type="text" name="table_filter" value="<?php echo html_once($_GET['table_filter']);?>" id=AutoFocus_Input onfocus="this.setAttribute('isfocused', '1');" onblur="this.setAttribute('isfocused', '');" autocomplete=off>
	</td>
	<td style="padding-right: 3px;"><input id=AutoFocus_Submit type="submit" class="button" wait="1" value="Filter"> <?php AutoFocus_HelpLink(); ?></td>
	</tr></table>
	</form>

	<?php AutoFocus_Script(); ?>

	<div style="float: left;">

		<!------------------>
		<!-- @listtables -->
		<!------------------>

		<?php
			$tables = table_filter($tables, $_GET['table_filter']);
		?>

		<?php if ($_GET['table_filter']): ?>
			<p>Tables found: <b><?php echo count($tables);?></b></p>
		<?php endif; ?>

		<table class="ls tables" cellspacing="0" style="margin-top: 1em;">
		<tr>
			<th style="min-width: 70px;">Table</th>

			<?php if ("sqlite" == $db_driver && $status["total_size"] > SQLITE_ESTIMATE_COUNT): ?>
				<?php $title = $_GET["precise_count"] ? "Click to Disable precise counting" : "Click to Enable precise counting"; ?>
				<th><a class=blue href="<?php echo $_SERVER['PHP_SELF']; ?>?table_filter=<?php echo urlencode($_GET['table_filter']);?>&precise_count=<?php echo $_GET['precise_count'] ? 0 : 1; ?>" style="color: #000; text-decoration: underline;" title="<?php echo $title; ?>"><?php echo $_GET["precise_count"] ? "*" : "~"; ?>Count</a></th>
			<?php else: ?>
				<th>Count</th>
			<?php endif; ?>

			<?php if (!SQLITE_USED): ?>
			<th>Size</th>
			<?php endif; ?>

			<th style="min-width: 65px;">Options</th>
		</tr>

		<?php if (!count($tables)): ?>
		<tr>
			<td align="" colspan=4 style="padding: 0.25em 0.5em;">No tables found.</td>
		</tr>
		<?php endif; ?>

		<?php foreach ($tables as $table): ?>
		<tr>
			<?php
				if ('mysql' == $db_driver) {

					// COUNT(*) would be slower!
					// We already have this info from table's status.

					$count = $status[$table]['count'];

					// $table_enq = quote_table($table);
					// $count = db_one("SELECT COUNT(*) FROM $table_enq");

				}
				else if ('pgsql' == $db_driver) {

					$count = $status[$table]['count'];

					if (!$count) {

						// Some tables might have missing "reltuples"? This has not been
						// documented and now I have no idea what is this chunk of code
						// doing here, really.

						$table_enq = quote_table($table);
						$count = db_one("SELECT COUNT(*) FROM $table_enq");
					}
				}
				else if ("sqlite" == $db_driver) {

					$table_enq = quote_table($table);

					// COUNT(*) might be very slow in SQLite!
					// Do some tests and maybe use MAX(rowid) as count.
					// A faser count but not too precise when some records has been deleted
					// from the table: SELECT MAX(rowid) FROM sometable;

					// COUNT(*) in sqlite requires reading all the data, so it might be slow
					// for a large database like 400 MB file. The solution is to count precisely
					// only if the database is small for example < 25 MB. When it's larger then
					// use MAX(rowid) instead of counting.

					// SQLITE_ESTIMATE_COUNT

					$precise_count = true;
					if ($status["total_size"] > SQLITE_ESTIMATE_COUNT) {
						$precise_count = false;
						if ($_GET["precise_count"]) {
							$precise_count = true;
						}
					}

					if ($precise_count) {
						$count = db_one("SELECT COUNT(*) FROM $table_enq");
					} else {
						$count = db_one("SELECT MAX(rowid) FROM $table_enq");
					}
				}
			?>
			<td>
				<a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?viewtable=<?php echo $table;?>"><?php echo $table;?></a>
			</td>
			<td align=right style="color: #333;"><?php echo number_format($count,0,'',' ');?></td>

			<?php if (!SQLITE_USED): ?>
			<td align=right style="color: #666;"><?php echo isset($status[$table]) ? number_format(ceil($status[$table]['size']/1024),0,'',',').' k' : "-";?></td>
			<?php endif; ?>

			<td>
				<a href="<?php echo $_SERVER['PHP_SELF'];?>?dump_table=<?php echo $table;?>">Export</a>
				&nbsp;-&nbsp;
				<?php $table_enq = quote_table($table); ?>
				<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="drop_<?php echo $table;?>" method="post" style="display: inline;"><input type="hidden" name="drop_table" value="<?php echo $table;?>"></form>
				<a href="javascript:void(0)" onclick="if (confirm('DROP TABLE <?php echo AttributeValue($table_enq);?> ?')) document.forms['drop_<?php echo $table;?>'].submit();">Drop</a>
			</td>



		</tr>
		<?php endforeach; ?>
		</table>
		<?php unset($table); ?>

	</div>

	<?php if (views_supported()): ?>
	<div style="float: left; margin-left: 3em;">

		<!------------------>
		<!-- @listviews -->
		<!------------------>

		<?php
			$views = table_filter($views, $_GET['table_filter']);
		?>

		<?php if ($_GET['table_filter']): ?>
			<p>Views found: <b><?php echo count($views);?></b></p>
		<?php endif; ?>

		<table class="ls tables" cellspacing="0" style="margin-top: 1em;">
		<tr>
			<th style="min-width: 70px;">View</th>
			<th><a class=blue href="<?php echo $_SERVER['PHP_SELF']; ?>?table_filter=<?php echo urlencode($_GET['table_filter']);?>&views_count=<?php echo $_GET['views_count'] ? 0 : 1; ?>" style="color: #000; text-decoration: underline;" title="Click to enable/disable counting in Views">Count</a></th>
			<th style="min-width: 65px;">Options</th>
		</tr>
		<?php if (!count($views)): ?>
		<tr>
			<td align="" colspan=3 style="padding: 0.25em 0.5em;">No views found.</td>
		</tr>
		<?php endif; ?>

		<?php foreach ($views as $view): ?>
		<?php $view_enq = quote_table($view); ?>
		<tr>
			<td><a class=blue href="<?php echo $_SERVER['PHP_SELF'];?>?viewtable=<?php echo $view;?>"><?php echo $view;?></a></td>
			<?php
				if ($_GET['views_count']) {
					$count = db_one("SELECT COUNT(*) FROM $view_enq");
				} else {
					$count = null;
				}
			?>
			<td align=right><?php echo isset($count) ? number_format($count,0,'',' ') : "-"; ?></td>
			<td>
				<a href="<?php echo $_SERVER['PHP_SELF'];?>?dump_table=<?php echo $view;?>&type=view">Export</a>
				&nbsp;-&nbsp;
				<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="drop_<?php echo $view;?>" method="post" style="display: inline;">
				<input type="hidden" name="drop_view" value="<?php echo $view;?>"></form>
				<a href="javascript:void(0)" onclick="if (confirm('DROP VIEW <?php echo AttributeValue($view_enq);?> ?')) document.forms['drop_<?php echo $view;?>'].submit();">Drop</a>
			</td>
		</tr>
		<?php endforeach; ?>
		</table>

	</div>
	<?php endif; ?>

	<div style="clear: both;"></div>

	</body>
	</html>

<?php

// ----------------------------------------------------------------
// @autofocus JAVASCRIPT
// ----------------------------------------------------------------

function AutoFocus_Script()
{
?>

	<script>

	// @autofocus

	if (document.addEventListener) {
		document.addEventListener("keydown", AutoFocus_OnKeyDown, false);
	} else {
		document.attachEvent("onkeydown", AutoFocus_OnKeyDown);
	}

	function AutoFocus_Help(elem)
	{
		if (!elem) {
			elem = document.getElementById("AutoFocus_HelpLink");
		}
		var msg = "";
		msg += "You can just <b>start typing</b> on this page and the search input will be <b>focused automatically</b>.<br>";
		msg += "Click <b>Alt + S</b> to focus the search input and not delete the text.<br>";
		msg += "Press <b>Alt + Z</b> to reset the input and resubmit the form.<br>";
		msg += "Press <b>Alt + R</b> to reset the search filters.<br>";
		msg += "<br>";
		msg += "Most <b>form controls</b> have keyboard shortcuts associated, press ALT + underlined letter.<br>";
		msg += "After you choose an option from <b>&lt;select&gt;</b> element, press Enter to submit the form.<br>";
		msg += "<br>";
		msg += "You can navigate through a listing by using <b>arrows</b> on your keyboard.<br>";
		msg += "Use <b>Up</b> or <b>Down</b> to move to the next or previous record.<br>";
		msg += "Use <b>Left</b> or <b>Right</b> to move to the next page or previous page on table view screen<br>or switch between tables and views on the main page.<br>";
		msg += "Use <b>Page Up</b> or <b>Page Down</b> to jump up or down by one page screen.<br>";
		msg += "Use <b>Home</b> or <b>End</b> to jump to the first or to the last record.<br>";
		msg += "<br>";
		msg += "Use <b>Enter</b> to view the table or edit the row.<br>";
		msg += "Use <b>Delete</b> to drop the table or delete the row.<br>";
		msg += "Use <b>Shift</b> to select multiple rows.";
		Tooltip(elem, msg);
	}

	// @expandselect
	function ExpandSelect(select, maxOptionsVisible)
	{
		// Downloaded from:
		// http://www.gosu.pl/expand-html-select-element-in-javascript.html

		if (typeof maxOptionsVisible == "undefined") {
			maxOptionsVisible = 20;
		}
		if (typeof select == "string") {
			select = document.getElementById(select);
		}
		if (typeof window["ExpandSelect_tempID"] == "undefined") {
			window["ExpandSelect_tempID"] = 0;
		}
		window["ExpandSelect_tempID"]++;

		var rects = select.getClientRects();

		// ie: cannot populate options using innerHTML.
		function PopulateOptions(select, select2)
		{
			select2.options.length = 0; // clear out existing items
			for (var i = 0; i < select.options.length; i++) {
				var d = select.options[i];
				select2.options.add(new Option(d.text, i))
			}
		}

		var select2 = document.createElement("SELECT");
		//select2.innerHTML = select.innerHTML;
		PopulateOptions(select, select2);
		select2.style.cssText = "visibility: hidden;";
		if (select.style.width) {
			select2.style.width = select.style.width;
		}
		if (select.style.height) {
			select2.style.height = select.style.height;
		}
		select2.id = "ExpandSelect_" + window.ExpandSelect_tempID;

		select.parentNode.insertBefore(select2, select.nextSibling);
		select = select.parentNode.removeChild(select);

		if (select.length > maxOptionsVisible) {
			select.size = maxOptionsVisible;
		} else {
			select.size = select.length;
		}

		if ("pageXOffset" in window) {
			var scrollLeft = window.pageXOffset;
			var scrollTop = window.pageYOffset;
		} else {
			// ie <= 8
			// Function taken from here: http://help.dottoro.com/ljafodvj.php
			function GetZoomFactor()
			{
				var factor = 1;
				if (document.body.getBoundingClientRect) {
					var rect = document.body.getBoundingClientRect ();
					var physicalW = rect.right - rect.left;
					var logicalW = document.body.offsetWidth;
					factor = Math.round ((physicalW / logicalW) * 100) / 100;
				}
				return factor;
			}
			var zoomFactor = GetZoomFactor();
			var scrollLeft = Math.round(document.documentElement.scrollLeft / zoomFactor);
			var scrollTop = Math.round(document.documentElement.scrollTop / zoomFactor);
		}

		select.style.position = "absolute";
		select.style.left = (rects[0].left + scrollLeft) + "px";
		select.style.top = (rects[0].top + scrollTop) + "px";
		select.style.zIndex = "1000000";
		select.setAttribute("_ExpandSelect", "1"); // So that Enter in AutoFocus_OnKeyDown does not submit form when expanded.

		var old_onchange = select.onchange;
		select.onchange = null;

		var tempID = window["ExpandSelect_tempID"];

		var collapseFunc;

		var blurFunc = function(){
			collapseFunc();
		};

		var clickFunc = function(e){
			e = e ? e : window.event;
			if (e.target) {
				if (e.target.tagName == "OPTION") {
					e.preventDefault();
					collapseFunc();
					return 0;
				}
			} else {
				// IE case.
				if (e.srcElement.tagName == "SELECT" || e.srcElement.tagName == "OPTION") {
					e.preventDefault();
					collapseFunc();
					return 0;
				}
			}
			return 1;
		};

		var keydownFunc = function(e){
			e = e ? e : window.event;
			// Need to implement hiding select on "Escape" and "Enter".
			if (e.altKey || e.ctrlKey || e.shiftKey || e.metaKey) {
				return 1;
			}
			// Escape, Enter.
			if (27 == e.keyCode || 13 == e.keyCode) {
				e.preventDefault();
				collapseFunc();
				return 0;
			}

			return 1;
		};

		collapseFunc = function(){
			if (select.removeEventListener) {
				select.removeEventListener("blur", blurFunc, false);
				select.removeEventListener("click", clickFunc, false);
				select.removeEventListener("keydown", keydownFunc, false);
			} else {
				select.detachEvent("onblur", blurFunc);
				select.detachEvent("onclick", clickFunc);
				select.detachEvent("onkeydown", keydownFunc);
			}
			select.size = 1;
			select.style.position = "static";
			select = select.parentNode.removeChild(select);
			var select2 = document.getElementById("ExpandSelect_"+tempID);
			select2.parentNode.insertBefore(select, select2);
			select2.parentNode.removeChild(select2);
			select.focus();
			window.setTimeout(function(){
				// Enter on select should submit the form only when collapsed, in AutoFocus_OnKeyDown.
				select.setAttribute("_ExpandSelect", "");
				select.onchange = old_onchange;
			}, 20);
		};

		if (select.addEventListener)
			select.addEventListener("keydown", keydownFunc, false);
		else select.attachEvent("onkeydown", keydownFunc);

		if (select.addEventListener)
			select.addEventListener("click", clickFunc, false);
		else select.attachEvent("onclick", clickFunc);

		if (select.addEventListener)
			select.addEventListener("blur", blurFunc, false);
		else select.attachEvent("onblur", blurFunc);

		document.body.appendChild(select);
		select.focus();
	}

	// @autofocus @keyboard @keys

	function AutoFocus_OnKeyDown(event)
	{
		// Add this code to form:
		// name=AutoFocus_Form onsubmit="document.getElementById('AutoFocus_Submit').focus()"

		// Add this code to input:
		// id=AutoFocus_Input onfocus="this.setAttribute('isfocused', '1');" onblur="this.setAttribute('isfocused', '');" autocomplete=off

		// ALSO EDIT <a accesskey=> in Layout().

		if (!event)
			event = window.event;

		var form = document.forms["AutoFocus_Form"];
		var input = document.getElementById("AutoFocus_Input");
		var submit = document.getElementById("AutoFocus_Submit");
		//var search_focused = input.getAttribute("isfocused"); // Need to set that attribute on input's events: "onfocus" and "onblur".

		// If other element in the form of type: input[text], select - is focused, then do not focus the search text input.

		// F1 help.
		if (112 == event.keyCode) {
			event.preventDefault();
			if (Tooltip_Div && Tooltip_Div.parentNode) {
				document.body.removeChild(Tooltip_Div);
			} else {
				AutoFocus_Help();
			}
			return 0;
		}

		// Alt + S (search - focus)
		if (event.altKey && !event.ctrlKey && !event.metaKey && !event.shiftKey && 83 == event.keyCode) {
			event.preventDefault();
			if (document.activeElement.id == "AutoFocus_Input") {
				input.blur();
			} else {
				input.focus();
				input.select();
			}
			return 0;
		}

		// Alt + R (Reset - go to main page)
		// Must be called before 0-9 or A-Z detections.
		if (event.altKey && 82 == event.keyCode) {
			event.preventDefault();
			if (window.location.href.match(/viewtable=([^&]+)/) != -1) {
				window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?viewtable=".RegExp.$1;
			} else {
				window.location.href = "<?php echo $_SERVER['PHP_SELF'];?>";
			}
			return 0;
		}

		// Alt + Z (reset)
		// Must be called before 0-9 or A-Z detections.
		if (event.altKey && 90 == event.keyCode) {
			event.preventDefault();
			input.value = "";
			submit ? submit.focus() : void(0);
			form.submit();
			return 0;
		}

		// 0-9, shiftKey allowed
		// A-Z, a-z, shiftKey allowed
		if ( (!event.altKey && !event.ctrlKey && !event.metaKey && event.keyCode >= 48 && event.keyCode <= 57)
			|| (!event.altKey && !event.ctrlKey && !event.metaKey && event.keyCode >= 65 && event.keyCode <= 90)
		) {
			if (document.activeElement.id != "AutoFocus_Input") {
				var focusedElem = document.activeElement;
				if (focusedElem.tagName == "SELECT"
					|| (focusedElem.tagName == "INPUT" && focusedElem.type == "text")
				) {
					event.preventDefault();
					return 0;
				}
				input.value = "";
				input.focus();
			}
			return 1;
		}

		// @accesskeys

		// Keyboard shortcuts for <select> elements.
		var select_shortcuts = [ // can have multiple shortcuts for the same letter, but on different pages, so must use array.
			[79, "order_by"], // "o" - Sort.
			[66, "db_name"], // "D" - Database.
			[85, "dump_database"] // "u" - Dump database.
		];
		for (var i = 0; i < select_shortcuts.length; ++i) {
			var keyCode = select_shortcuts[i][0];
			var elemID = select_shortcuts[i][1];
			if (event.keyCode == keyCode && Element(elemID)) {
				event.preventDefault();
				ExpandSelect(elemID);
				return 0;
			}
		}

		// No alt, no ctrl, no shift, no meta - from now on...
		if (event.altKey || event.ctrlKey || event.shiftKey || event.metaKey) {
			return 1;
		}

		// 27 Escape - hide tooltip
		if (27 == event.keyCode && typeof Tooltip_Div != "undefined" && Tooltip_Div) {
			// Escape also hides tooltip if shown.
			Tooltip_Hide();
			return 0;
		}

		// 27 Escape - undo focus of search input
		if (27 == event.keyCode && (document.activeElement.tagName == "INPUT" || document.activeElement.tagName == "SELECT")) {
			// Escape also hides tooltip if shown.
			document.activeElement.blur();
			return 0;
		}

		// 13 Enter - after choosing option from <select> submit the form when typing "enter".
		if (13 == event.keyCode && document.activeElement.tagName == "SELECT") {
			if (!document.activeElement.getAttribute("_ExpandSelect")) {
				if (document.activeElement.form) {
					event.preventDefault();
					var form = document.activeElement.form;
					document.activeElement.blur();
					form.submit();
					return 0;
				}
				else if (document.activeElement.onchange) {
					event.preventDefault();
					document.activeElement.onchange();
					document.activeElement.blur();
					return 0;
				}
			}
		}

		// return value:
		// 1 - do propagate further events.
		// 0 - do not propagate any events for this key, we have taken of all that should be donevent.

		return 1;
	}
	</script>
<?php
}
function AutoFocus_HelpLink()
{
?>
	<a class="help" id="AutoFocus_HelpLink" href="javascript:void(0)" onclick="AutoFocus_Help(this)" title="Help: keyboard shortcuts F1"></a>
<?php
}
?>