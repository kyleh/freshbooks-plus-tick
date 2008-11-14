<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the "Database Connection"
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the "default" group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = "devlocal";
$active_record = TRUE;
//production
$db['production']['hostname'] = "localhost";
$db['production']['username'] = "";
$db['production']['password'] = "";
$db['production']['database'] = "";
$db['production']['dbdriver'] = "mysql";
$db['production']['dbprefix'] = "";
$db['production']['pconnect'] = TRUE;
$db['production']['db_debug'] = FALSE;
$db['production']['cache_on'] = FALSE;
$db['production']['cachedir'] = "";
$db['production']['char_set'] = "utf8";
$db['production']['dbcollat'] = "utf8_general_ci";
//localhost dev enviornment
$db['devlocal']['hostname'] = "localhost";
$db['devlocal']['username'] = "fbapps";
$db['devlocal']['password'] = "fresh11235";
$db['devlocal']['database'] = "tickspotapi";
$db['devlocal']['dbdriver'] = "mysql";
$db['devlocal']['dbprefix'] = "";
$db['devlocal']['pconnect'] = TRUE;
$db['devlocal']['db_debug'] = TRUE;
$db['devlocal']['cache_on'] = FALSE;
$db['devlocal']['cachedir'] = "";
$db['devlocal']['char_set'] = "utf8";
$db['devlocal']['dbcollat'] = "utf8_general_ci";
//remote dev enviroment
$db['devremote']['hostname'] = "localhost";
$db['devremote']['username'] = "fbsync_tick";
$db['devremote']['password'] = "demo1234";
$db['devremote']['database'] = "fbsync_tickapi";
$db['devremote']['dbdriver'] = "mysql";
$db['devremote']['dbprefix'] = "";
$db['devremote']['pconnect'] = TRUE;
$db['devremote']['db_debug'] = TRUE;
$db['devremote']['cache_on'] = FALSE;
$db['devremote']['cachedir'] = "";
$db['devremote']['char_set'] = "utf8";
$db['devremote']['dbcollat'] = "utf8_general_ci";



/* End of file database.php */
/* Location: ./system/application/config/database.php */