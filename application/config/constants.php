<?php
defined('BASEPATH') OR exit('No direct script access allowed');

defined('SMS_SENDER')  OR define('SMS_SENDER', '9105448323');

/*
|-------------------------------------------------------------------------
| Random Constants
|-------------------------------------------------------------------------
*/

defined('BIO_NAME_MAX_LENGTH') OR define ('BIO_NAME_MAX_LENGTH', 100);
defined('INVENTORY_NAME_MAX_LENGTH') OR define ('INVENTORY_NAME_MAX_LENGTH', 100);

defined('COMPANY_NAME_MAX_LENGTH') OR define ('COMPANY_NAME_MAX_LENGTH', 100);
defined('FORM_NAME_MAX_LENGTH') OR define('FORM_NAME_MAX_LENGTH', 91); // 100 with unique id added
defined('COMPANY_DB_MAX_LENGTH') OR define ('COMPANY_DB_MAX_LENGTH', 100);
defined('DEPARTMENT_STRING_MAX_LENGTH') OR define ('DEPARTMENT_STRING_MAX_LENGTH', 100);
defined('DROPDOWN_OPTION_MAX_LENGTH') OR define ('DROPDOWN_OPTION_MAX_LENGTH', 100);
defined('DEFAULT_FREQUENCY') OR define ('DEFAULT_FREQUENCY', 1);
defined('DEFAULT_QUOTA') OR define ('DEFAULT_QUOTA', 1);
defined('ALERT_REPEAT_LIMIT') OR define ('ALERT_REPEAT_LIMIT', 3);

/*
|-------------------------------------------------------------------------
| Database Constants
|-------------------------------------------------------------------------
*/
defined('DB_HOSTNAME')	OR define('DB_HOSTNAME', 'localhost');
defined('DB_USERNAME')	OR define('DB_USERNAME', 'root');
defined('DB_PASSWORD')	OR define('DB_PASSWORD', '2boyz4me');

/*
|-------------------------------------------------------------------------
| User Level Constants
|-------------------------------------------------------------------------
*/
defined('SUPERUSER')		OR define('SUPERUSER',			1);
defined('ACCOUNT_MANAGER')	OR define('ACCOUNT_MANAGER',	2);
defined('COMPANY_MANAGER')	OR define('COMPANY_MANAGER',	3);
defined('COMPANY_EMPLOYEE')	OR define('COMPANY_EMPLOYEE',	4);

/*
|-------------------------------------------------------------------------
| Default Company Constants
|-------------------------------------------------------------------------
*/
defined('BLUWAVE') OR define('BLUWAVE', 1);

/*
|-------------------------------------------------------------------------
| Default Department Constants
|-------------------------------------------------------------------------
*/
defined('ADMINISTRATION') OR define('ADMINISTRATION', 1);
defined('UNASSIGNED') OR define('UNASSIGNED', 2);

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
