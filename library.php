<?php

/**
 * manufakturI18n
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License (GPL)
 * @version $Id$
 *
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include($root.'/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

// wb2lepton compatibility
if (!defined('LEPTON_PATH')) require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

// load language depending onfiguration
if (!file_exists(LEPTON_PATH.'/modules/manufaktur_i18n/languages/'.LANGUAGE.'.cfg.php')) {
  require_once(LEPTON_PATH.'/modules/manufaktur_i18n/languages/EN.cfg.php');
}
else {
  require_once(LEPTON_PATH.'/modules/manufaktur_i18n/languages/'.LANGUAGE.'.cfg.php');
}

abstract class dbMiniContainer {

  private static $error = '';

  public function __construct() {
    date_default_timezone_set(CFG_TIME_ZONE);
  } // __construct()

  /**
   * Create the database table
   * Set error message at any SQL problem.
   *
   * @return boolean
   */
  abstract public function createTable();

  /**
   * Delete the database table.
   * Set error message at any SQL problem.
   *
   * @return boolean
   */
  public function deleteTable() {
    global $database;
    $database->query('DROP TABLE IF EXISTS `'.$this->getTableName().'`');
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // deleteTable()

  /**
   * Return the table name
   *
   * @return string table name
   */
  abstract public static function getTableName();

  /**
   * Set an error
   *
   * @param string $error
   */
  protected function setError($error) {
    $this->error = $error;
  } // setError()

  /**
   * Return the actual error
   *
   * @return string
   */
  public function getError() {
    return $this->error;
  } // getError()

} // abstract class dbMiniContainer

class dbManufakturI18n extends dbMiniContainer {

  const FIELD_ID = 'i18n_id';
  const FIELD_KEY = 'i18n_key';
  const FIELD_DESCRIPTION = 'i18n_description';
  const FIELD_STATUS = 'i18n_status';
  const FIELD_LAST_SYNC = 'i18n_last_sync';
  const FIELD_TIMESTAMP = 'i18n_timestamp';

  const STATUS_ACTIVE = 'ACTIVE';
  const STATUS_BACKUP = 'BACKUP';
  const STATUS_IGNORE = 'IGNORE';

  /**
   * Create the database table
   * Set error message at any SQL problem.
   *
   * @return boolean
   */
  public function createTable() {
    global $database;
    $SQL = "CREATE TABLE IF NOT EXISTS `".self::getTableName()."` ( ".
        "`i18n_id` INT(11) NOT NULL AUTO_INCREMENT, ".
        "`i18n_key` TEXT, ".
        "`i18n_description` TEXT, ".
        "`i18n_status` ENUM('ACTIVE','BACKUP','IGNORE') NOT NULL DEFAULT 'ACTIVE', ".
        "`i18n_last_sync` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', ".
        "`i18n_timestamp` TIMESTAMP, "."PRIMARY KEY (`i18n_id`)".
        " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
    if (null == $database->query($SQL)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // createTable()

  public static function getTableName() {
    return TABLE_PREFIX.'mod_manufaktur_i18n';
  } // getTableName()

} // class dbManufakturI18n

class dbManufakturI18nTranslations extends dbMiniContainer {

  const FIELD_ID = 'trans_id';
  const FIELD_I18N_ID = 'i18n_id';
  const FIELD_LANGUAGE = 'trans_language';
  const FIELD_TRANSLATION = 'trans_translation';
  const FIELD_USAGE = 'trans_usage';
  const FIELD_TYPE = 'trans_type';
  const FIELD_STATUS = 'trans_status';
  const FIELD_AUTHOR = 'trans_author';
  const FIELD_QUALITY = 'trans_quality';
  const FIELD_IS_EMPTY = 'trans_is_empty';
  const FIELD_TIMESTAMP = 'trans_timestamp';

  const USAGE_TEXT = 'TEXT';
  const USAGE_MESSAGE = 'MESSAGE';
  const USAGE_ERROR = 'ERROR';
  const USAGE_HINT = 'HINT';
  const USAGE_LABEL = 'LABEL';
  const USAGE_BUTTON = 'BUTTON';

  const STATUS_ACTIVE = 'ACTIVE';
  const STATUS_BACKUP = 'BACKUP';

  const TYPE_REGULAR = 'REGULAR';
  const TYPE_CUSTOM = 'CUSTOM';

  const AUTHOR_UNKNOWN = '- unknown -';

  /**
   * Create the table for the database class.
   *
   * @return boolean
   */
  public function createTable() {
    global $database;
    $SQL = "CREATE TABLE IF NOT EXISTS `".self::getTableName()."` ( ".
        "`trans_id` INT(11) NOT NULL AUTO_INCREMENT, ".
        "`i18n_id` INT(11) NOT NULL DEFAULT '-1', ".
        "`trans_language` VARCHAR(2) NOT NULL DEFAULT 'EN', ".
        "`trans_translation` TEXT, ".
        "`trans_usage` ENUM('TEXT','MESSAGE','ERROR','HINT','LABEL','BUTTON') NOT NULL DEFAULT 'TEXT', ".
        "`trans_type` ENUM('REGULAR','CUSTOM') NOT NULL DEFAULT 'REGULAR', ".
        "`trans_status` ENUM('ACTIVE','BACKUP') NOT NULL DEFAULT 'ACTIVE', ".
        "`trans_author` VARCHAR(64) NOT NULL DEFAULT '- unknown -', ".
        "`trans_quality` FLOAT NOT NULL DEFAULT '0', ".
        "`trans_is_empty` TINYINT NOT NULL DEFAULT '0', ".
        "`trans_timestamp` TIMESTAMP, "."PRIMARY KEY (`trans_id`), KEY (`i18n_id`, `trans_language`)".
        " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
    if (null == $database->query($SQL)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // __construct()

  public static function getTableName() {
    return TABLE_PREFIX.'mod_manufaktur_i18n_trans';
  } // getTableName()

} // class dbManufakturI18nTranslations

class dbManufakturI18nSources extends dbMiniContainer {

  const FIELD_ID = 'src_id';
  const FIELD_I18N_ID = 'i18n_id';
  const FIELD_FILE = 'src_file';
  const FIELD_PATH = 'src_path';
  const FIELD_MODULE = 'src_module';
  const FIELD_LINE = 'src_line';
  const FIELD_STATUS = 'src_status';
  const FIELD_TIMESTAMP = 'src_timestamp';

  const STATUS_ACTIVE = 'ACTIVE';
  const STATUS_BACKUP = 'BACKUP';

  /**
   * Create the table for the database class
   *
   * @return boolean
   */
  public function createTable() {
    global $database;
    $SQL = "CREATE TABLE IF NOT EXISTS `".self::getTableName()."` ( ".
        "`src_id` INT(11) NOT NULL AUTO_INCREMENT, ".
        "`i18n_id` INT(11) NOT NULL DEFAULT '-1', ".
        "`src_file` VARCHAR(64) NOT NULL DEFAULT '', ".
        "`src_path` TEXT, ".
        "`src_module` VARCHAR(64) NOT NULL DEFAULT '', ".
        "`src_line` INT(11) NOT NULL DEFAULT '-1', ".
        "`src_status` ENUM('ACTIVE','BACKUP') NOT NULL DEFAULT 'ACTIVE', ".
        "`src_timestamp` TIMESTAMP, ".
        "PRIMARY KEY (`src_id`), KEY (`i18n_id`, `src_module`)".
        " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
    if (null == $database->query($SQL)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // __construct()

  public static function getTableName() {
    return TABLE_PREFIX.'mod_manufaktur_i18n_src';
  } // getTableName()

} // class dbManufakturI18nSources

class dbManufakturI18nLanguages extends dbMiniContainer {

  const FIELD_ID = 'lang_id';
  const FIELD_ISO = 'lang_iso';
  const FIELD_LOCAL = 'lang_local';
  const FIELD_ENGLISH = 'lang_english';

  /**
   * Create the table for the database class
   *
   * @return boolean
   */
  public function createTable() {
    global $database;
    $SQL = "CREATE TABLE IF NOT EXISTS `".self::getTableName()."` ( ".
        "`lang_id` INT(11) NOT NULL AUTO_INCREMENT, ".
        "`lang_iso` VARCHAR(2) NOT NULL DEFAULT 'nn', ".
        "`lang_local` VARCHAR(64) NOT NULL DEFAULT '-undefined-', ".
        "`lang_english` VARCHAR(64) NOT NULL DEFAULT '-undefined-', ".
        "PRIMARY KEY (`lang_id`), KEY (`lang_iso`, `lang_english`)".
        " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
    if (null == $database->query($SQL)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // __construct()

  public static function getTableName() {
    return TABLE_PREFIX.'mod_manufaktur_i18n_lang';
  } // getTableName()

  public function readLanguageCSV() {
    global $database;

    $start = true;
    $insert = array();
    if (false !== ($handle = fopen(LEPTON_PATH.'/modules/manufaktur_i18n/data/languages.csv', 'r'))) {
      while (false !== ($data = fgetcsv($handle, 1000, ";"))) {
        if ($start) {
          // ignore the first line
          $start = false; continue;
        }
        if (count($data) < 3) continue;
        $insert[] = "('{$data[0]}','{$data[1]}','{$data[2]}')";
      }
      fclose($handle);
      $insert_str = implode(',', $insert);
      $SQL = "INSERT INTO `".self::getTableName()."`".
          "(`lang_iso`, `lang_local`,`lang_english`) VALUES $insert_str";
      if (null == $database->query($SQL)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }
      return true;
    }
    // don't use I18n here - it's not ready for use!
    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, 'Got no handle for languages.csv'));
    return false;
  } // readLanguageCSV()

} // class dbManufakturI18nLanguages

class manufaktur_I18n {

  private $error = '';
  private $message = '';
  private static $default_language = 'EN';
  private static $wanted_language = 'EN';
  private $loaded_modules = array();
  public static $language_array = array();

  public function __construct($module_directory, $language='') {
    $this->wanted_language = (!empty($language)) ? $language : LANGUAGE;
    $this->loadLanguage($module_directory, self::$default_language);
    $this->loadLanguage($module_directory, self::$default_language, dbManufakturI18nTranslations::TYPE_CUSTOM);
    if ($language != self::$default_language) {
      $this->loadLanguage($module_directory, $language);
      $this->loadLanguage($module_directory, $language, dbManufakturI18nTranslations::TYPE_CUSTOM);
    }
  } // __construct()

  /**
   * Set $this->error to $error
   *
   * @param $error STR
   */
  protected function setError($error) {
    $this->error = $error;
  } // setError()

  /**
  * Get Error from $this->error;
  *
  * @return STR $this->error
  */
  public function getError() {
    return $this->error;
  } // getError()

  /**
  * Check if $this->error is empty
  *
  * @return BOOL
  */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /**
   * @return the $message
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @param string $message
   */
  public function setMessage($message) {
    $this->message = $message;
  }

  public function isMessage() {
    return (bool) !empty($this->message);
  }

  /**
   * Return the version of manufakturConfig
   *
   * @return float module version
   */
  public function getVersion() {
    global $database;
    $version = $database->get_one("SELECT `version` FROM ".TABLE_PREFIX."addons WHERE `directory`='manufaktur_i18n'", MYSQL_ASSOC);
    return floatval($version);
  } // getVersion()

  public function loadLanguage($module_directory, $language, $type=dbManufakturI18nTranslations::TYPE_REGULAR) {
  	global $database;

    $i18n = dbManufakturI18n::getTableName();
    $i18nT = dbManufakturI18nTranslations::getTableName();
    $i18nS = dbManufakturI18nSources::getTableName();

    $SQL = "SELECT $i18n.i18n_key, $i18nT.trans_translation FROM $i18n,$i18nT,$i18nS WHERE ".
      "$i18n.i18n_id=$i18nT.i18n_id AND $i18n.i18n_id=$i18nS.i18n_id AND $i18nS.src_module='$module_directory' AND ".
      "$i18nT.trans_language='$language' AND $i18nT.trans_status='ACTIVE' AND $i18nT.trans_type='REGULAR'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    while (false !== ($item = $query->fetchRow(MYSQL_ASSOC))) {
      if (isset(self::$language_array[$item[dbManufakturI18n::FIELD_KEY]]))
        unset(self::$language_array[$item[dbManufakturI18n::FIELD_KEY]]);
      self::$language_array[$item[dbManufakturI18n::FIELD_KEY]] = $item[dbManufakturI18nTranslations::FIELD_TRANSLATION];
    }
  } // loadLanguage()

  /**
   * The central function for the translation
   *
   * @param string $translate - the string to translate
   * @param array $arguments - additional arguments for the translated string
   * @return string - the translated string
   */
  public function I18n($translate, $arguments=array()) {
    if (empty($translate) || !is_string($translate)) return $translate;
    if (array_key_exists($translate, self::$language_array)) {
      $translate = self::$language_array[$translate];
    }
    if (is_array($arguments)) {
      foreach ($arguments as $key => $value) {
        $translate = preg_replace( "~{{\s*$key\s*}}~i", $value, $translate);
      }
    }
    return $translate;
  } // I18n()

 /**
   * This function is used to indicate language strings, which should not translated
   * yet but registered for the database - this is usefull i.e. to transmit original
   * language strings from the program to the template, process them and translate
   * at output.
   * I18n_Register() simply return the unchanged $language string
   *
   * @param string $translate
   * @return string $translate
   */
  public function I18n_Register($translate) {
    return $translate;
  } // I18n_Register()

  /**
   * Iterate directory tree very efficient
   * Function postet from donovan.pp@gmail.com at
   * http://www.php.net/manual/de/function.scandir.php
   *
   * @param string $directory
   * @return array - directoryTree
   */
  public static function getDirectoryTree($directory, $extensions_only = NULL) {
    if (substr($directory, -1) == "/") $directory = substr($directory, 0, -1);
    $path = array();
    $stack = array();
    $stack[] = $directory;
    while ($stack) {
      $thisdir = array_pop($stack);
      if (false !== ($dircont = scandir($thisdir))) {
        $i = 0;
        while (isset($dircont[$i])) {
          if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
            $current_file = "{$thisdir}/{$dircont[$i]}";
            if (is_file($current_file)) {
              if ($extensions_only == NULL) {
                $path[] = "{$thisdir}/{$dircont[$i]}";
              }
              else {
                $path_info = pathinfo("{$thisdir}/{$dircont[$i]}");
                if (isset($path_info['extension']) && in_array($path_info['extension'], $extensions_only)) $path[] = "{$thisdir}/{$dircont[$i]}";
              }
            }
            elseif (is_dir($current_file)) {
              $stack[] = $current_file;
            }
          }
          $i++;
        }
      }
    }
    return $path;
  } // getDirectoryTree()

  /**
   * Parses a PHP file for I18n() functions and gather the text parameter, line
   * number, module name and filename in the $result array.
   *
   * @param string $file_path
   * @param reference array $result
   * @return boolean
   */
  protected function parseSourceFile($file_path, &$result = array()) {
    if (false === ($source = file_get_contents($file_path))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->I18n("Can't get file content: {{ file }}", array(
          'file' => $file_path
      ))));
      return false;
    }
    $path = substr($file_path, strlen(WB_PATH));
    $module = dirname(substr($file_path, strlen(WB_PATH . '/modules/')));
    if (strpos($module, DIRECTORY_SEPARATOR) > 0) $module = substr($module, 0, strpos($module, DIRECTORY_SEPARATOR));
    $file = basename($file_path);
    $tokens = token_get_all($source);
    $matches = array();
    // first run: get only matches for "I18n"
    for($i = 0; $i < count($tokens); $i++) {
      if (is_array($tokens[$i]) && (token_name($tokens[$i][0]) == 'T_STRING') &&
        		(($tokens[$i][1] == 'I18n') || ($tokens[$i][1] == 'I18n_Register'))) $matches[] = $i;
    }
    foreach ($matches as $match) {
      $parensis_open = 0;
      $concat = false;
      $text = '';
      $has_content = false;
      for($i = $match; $i < count($tokens); $i++) {
        if ($parensis_open < 1) {
          // first detect the opening parensis!
          if (is_string($tokens[$i]) && ($tokens[$i] == '(')) {
            $parensis_open++;
          }
        }
        else {
          if (is_string($tokens[$i])) {
            // handle strings
            if ($tokens[$i] == ')') {
              $parensis_open--;
              $concat = false;
              if ($parensis_open == 0) break;
            }
            if ($tokens[$i] == '(') {
              $concat = false;
              $parensis_open++;
            }
            if ($has_content && ($tokens[$i] == '.')) {
              $concat = true;
            }
          }
          else {
            // handle tokens
            if (token_name($tokens[$i][0]) == 'T_CONSTANT_ENCAPSED_STRING') {
              if (empty($text)) {
                $item = trim($tokens[$i][1]);
                $text = substr($item, 1, strlen($item) - 2);
                $has_content = true;
              }
              elseif ($concat) {
                $item = trim($tokens[$i][1]);
                $text .= substr($item, 1, strlen($item) - 2);
              }
            }
          }
        }
      }
      if (!empty($text)) {
        $result[] = array(
            'module' => $module,
            'path' => $path,
            'file' => $file,
            'key' => $text,
            'line' => $tokens[$match][2]
        );
      }
    }
    return true;
  } // parseSourceFile()

  /**
   * Parses a Template file for I18n() functions and gather the text parameter,
   * line number, module name and filename in the $result array.
   *
   * @param string $file_path
   * @param reference array $result
   * @return boolean
   */
  protected function parseTemplateFile($file_path, &$result = array()) {
    if (false === ($source = file($file_path))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->I18n("Can't get file content: {{ file }}", array(
          'file' => $file_path
      ))));
      return false;
    }
    $path = substr($file_path, strlen(WB_PATH));
    $module = dirname(substr($file_path, strlen(WB_PATH . '/modules/')));
    if (strpos($module, DIRECTORY_SEPARATOR) > 0) $module = substr($module, 0, strpos($module, DIRECTORY_SEPARATOR));
    $file = basename($file_path);
    $line_number = 1;
    foreach ($source as $line) {
      $hits = preg_match_all('/{I18n(.?)\((.*?)\)(.?)}/', $line, $matches);
      if ($hits > 0) {
        for($i = 0; $i < $hits; $i++) {
          foreach ($matches[$i] as $match) {
            if (!empty($match)) {
              if (preg_match('/(\'(.*)\')|("(.*)")/', $match, $hit) == 1) {
                $text = trim($hit[0]);
                $result[] = array(
                    'module' => $module,
                    'path' => $path,
                    'file' => $file,
                    'key' => substr($text, 1, strlen($text) - 2),
                    'line' => $line_number
                );
              }
            }
          }
        }
      }
      $line_number++;
    }
    return true;
  } // parseTemplateFile()

  /**
   * Delete all entries from all language tables which status is set to $status
   *
   * @param string $module the module directory
   * @return boolean - result
   */
  public function deleteEntriesByStatus($module_directory, $status) {
    global $database;

    $ti = dbManufakturI18n::getTableName();
    $tis = dbManufakturI18nSources::getTableName();
    $tit = dbManufakturI18nTranslations::getTableName();

    $SQL = "DELETE `$ti`,`$tis`,`$tit` FROM `$ti`,`$tis`,`$tit` WHERE " .
      "`$ti`.`i18n_id`=`$tis`.`i18n_id` AND `$ti`.`i18n_id`=`$tit`.`i18n_id` AND " .
      "`src_module`='$module_directory' AND `i18n_status`='$status' AND " .
      "`src_status`='$status' AND `trans_status`='$status'";
    if (null == ($database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // deleteBackupEntries

  /**
   * Sanitize any given value.
   *
   * @param string $item
   * @return string santized value
   */
  public static function sanitize($item) {
    if (!is_array($item)) {
      // undoing 'magic_quotes_gpc = On' directive
      if (get_magic_quotes_gpc()) $item = stripcslashes($item);
      $item = str_replace("<", "&lt;", $item);
      $item = str_replace(">", "&gt;", $item);
      $item = str_replace("\"", "&quot;", $item);
      $item = str_replace("'", "&#039;", $item);
      $item = mysql_real_escape_string($item);
    }
    return $item;
  } // sanitize()

  /**
   * Unsanitize the given value.
   *
   * @param string $item
   * @return string unsanitized value
   */
  public static function unsanitize($item) {
    $item =  stripcslashes($item);
    $item = str_replace("&#039;", "'", $item);
    $item = str_replace("&gt;", ">", $item);
    $item = str_replace("&quot;", "\"", $item);
    $item = str_replace("&lt;", "<", $item);
    return $item;
  } // unsanitize()

  /**
   * Change all language record for the $module from the $from_status to the
   * $to_status.
   * Important: The status will also changed for all records which
   * status is set to 'IGNORE'!
   *
   * @param string $module module directory
   * @param string $from_status
   * @param string $to_status
   */
  public function changeEntriesFromStatusToStatus($module, $from_status, $to_status) {
    global $database;

    $ti = dbManufakturI18n::getTableName();
    $tis = dbManufakturI18nSources::getTableName();
    $tit = dbManufakturI18nTranslations::getTableName();

    $SQL = "UPDATE `$ti`,`$tis`,`$tit` SET `i18n_status`='$to_status'," .
      "`src_status`='$to_status',`trans_status`='$to_status' WHERE " .
      "`$ti`.`i18n_id`=`$tis`.`i18n_id` AND `$ti`.`i18n_id`=`$tit`.`i18n_id` AND " .
      "`src_module`='$module' AND (`i18n_status`='$from_status' OR " .
      "`i18n_status`='IGNORE')";
    if (null == $database->query($SQL)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // createBackupEntries()

  /**
   * Scan the $directory and register all
   * @param string $directory
   */
  public function scanDirectory($directory) {
    global $database;

    $check = self::getDirectoryTree($directory, array(
        'php',
        'lte'
    ));
    $translation = array();
    foreach ($check as $file_path) {
      $path_info = pathinfo($file_path);
      if ($path_info['extension'] == 'php') {
        if (!$this->parseSourceFile($file_path, $translation)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
          return false;
        }
      }
      elseif ($path_info['extension'] == 'lte') {
        if (!$this->parseTemplateFile($file_path, $translation)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
          return false;
        }
      }
      else {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->I18n('The file type <b>{{ file_type }}</b> is not supported!',
                array('file_type' => $path_info['extension']))));
        return false;
      }
    }

    foreach ($translation as $entry) {
      $key = self::sanitize($entry['key']);
      $SQL = "SELECT `i18n_id`, `i18n_key` FROM `".dbManufakturI18n::getTableName() .
        "` WHERE `i18n_key`='$key' AND (`i18n_status`='ACTIVE' OR `i18n_status`='IGNORE')";
      if (null == ($query = $database->query($SQL))) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }

      $add_only_source = false;
      if ($query->numRows() > 0) {
        $result = $query->fetchRow(MYSQL_ASSOC);
        $add_only_source = ($result['i18n_key'] == $key) ? true : false;
      }

      if ($add_only_source) {
        // entry already exists, keep only the source usage
        $SQL = "INSERT INTO `".dbManufakturI18nSources::getTableName().
          "` (`i18n_id`, `src_path`, `src_file`, `src_line`, `src_module`) VALUES (".
          "'{$result[dbManufakturI18n::FIELD_ID]}','{$entry['path']}',".
          "'{$entry['file']}','{$entry['line']}','{$entry['module']}')";
        if (null == ($database->query($SQL))) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
      }
      else {
        // create a new entry
        $SQL = "INSERT INTO `".dbManufakturI18n::getTableName()."` ".
            "(`i18n_description`, `i18n_key`, `i18n_last_sync`, `i18n_status`) VALUES ( ".
            "'', '$key', '".date("Y-m-d H:i:s", time())."', ".
            "'".dbManufakturI18n::STATUS_ACTIVE."')";
        if (null == ($database->query($SQL))) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
        // get the ID for the new entry
        $id = mysql_insert_id();
        // add the standard EN translation
        $author = (isset($_SESSION['DISPLAY_NAME'])) ? $_SESSION['DISPLAY_NAME'] : dbManufakturI18n::AUTHOR_UNKNOWN;
        $SQL = "INSERT INTO `".dbManufakturI18nTranslations::getTableName()."` (".
            "`trans_author`, `i18n_id`, `trans_language`, `trans_translation`, ".
            "`trans_usage`, `trans_type`, `trans_status`) VALUES (".
            "'$author','$id','EN','$key','TEXT','REGULAR','ACTIVE')";
        if (null == ($database->query($SQL))) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
        // add source usage...
        $SQL = "INSERT INTO `".dbManufakturI18nSources::getTableName()."` (".
            "`i18n_id`, `src_path`, `src_file`, `src_line`, `src_module`) VALUES (".
            "'$id', '{$entry['path']}', '{$entry['file']}', '{$entry['line']}', '{$entry['module']}')";
        if (null == ($database->query($SQL))) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
      }
    }
    return true;
  } // scanDirectory()

  /**
   * Transforms XML errors to an regular error string
   *
   * @param string $method the calling method
   * @param string $line the line number of the source code
   */
  protected function setXMLerror($method, $line) {
    $err_str = '<p>Failed loading XML<br />';
    foreach (libxml_get_errors() as $error) {
      $err_str .= sprintf("[%s at %d:%d] %s<br />", $this->XMLerrorLevel2string($error->level), $error->line, $error->column, $error->message);
    }
    $err_str .= "</p>";
    $this->setError(sprintf('[%s - %s] %s', $method, $line, $err_str));
  } // setXMLerror()

  /** Prettifies an XML string into a human-readable and indented work of art
   *
   *  @param string $xml The XML as a string
   *  @param boolean $html_output True if the output should be escaped (for use in HTML)
   */
  protected static function xmlPrettyPrint($xml, $html_output = false) {
    $xml_obj = new SimpleXMLElement($xml);
    $tab_width = 2; // tabulator width
    $indent = 0; // current indentation level
    $pretty = array();

    // get an array containing each XML element
    $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

    // shift off opening XML tag if present
    if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
      $pretty[] = array_shift($xml);
    }

    foreach ($xml as $el) {
      if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
        // opening tag, increase indent
        $pretty[] = str_repeat(' ', $indent).$el;
        $indent += $tab_width;
      }
      else {
        if (preg_match('/^<\/.+>$/', $el)) {
          $indent -= $tab_width; // closing tag, decrease indent
        }
        if ($indent < 0) {
          $indent += $tab_width;
        }
        $pretty[] = str_repeat(' ', $indent).$el;
      }
    }
    $xml = implode("\n", $pretty);
    return ($html_output) ? htmlentities($xml) : $xml;
  } // xmlPrettyPrint()

  public function writeXMLlanguageFile($path) {
    global $database;

    $SQL = "SELECT * FROM `".dbManufakturI18nLanguages::getTableName()."` ORDER BY `lang_iso` ASC";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }

    if ($query->numRows() > 0) {
      // set the XML file header
      $xml_header = sprintf('<?xml version="1.0" encoding="UTF-8"?><xmilg version="%01.2f"></xmilg>', $this->getVersion());
      // create XML object
      $xml = new SimpleXMLElement($xml_header);
      while (false !== ($lang = $query->fetchRow(MYSQL_ASSOC))) {
        $language = $xml->addChild('language');
        $language->addAttribute('iso', $lang['lang_iso']);
        $language->addChild('local', $lang['lang_local']);
        $language->addChild('english', $lang['lang_english']);
      }
      // save XML object as string
      $result = $xml->asXML();
      // prettyfy the output
      $result = $this->xmlPrettyPrint($result);
      // save the XML file
      if (!file_put_contents($path, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->I18n('Error writing the XML file {{ file }}.',
            array('file' => substr($path, strlen(LEPTON_PATH))))));
        return false;
      }
      $relative_path = substr($path, strlen(LEPTON_PATH));
      $this->setMessage($this->I18n('The language records are saved as <a href="{{ url }}">{{ file }}</a> (click right and choose "save as...").',
          array('file' => $relative_path, 'url' => LEPTON_URL.$relative_path)));
    }
    else {
      $this->setMessage($this->I18n('There exists no language records for a XML export!'));
    }
    return true;
  } // writeXMLlanguageFile()

  public function readXMLlanguageFile($path) {
    global $database;
    if (!file_exists($path)) {
      $this->setError(sprintf('[%s - %s] %s', $this->I18n('The XML file <b>{{ file }}</b> does not exist!',
          array('file' => substr($path, strlen(LEPTON_PATH))))));
      return false;
    }
    // catch the XML errors
    libxml_use_internal_errors(true);
    // create XML iterator object
    if (false === ($xmlIterator = new SimpleXMLIterator($path, 0, true))) {
      $this->setXMLerror(__METHOD__, __LINE__ - 1);
      return false;
    }
    $message = '';
    $this->setMessage('');
    if ($xmlIterator->getName() != 'xmilg') {
      // no valid XMILG file!
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->I18n('The file <b>{{ file }}</b> is no valid manufaktur_I18n language file, missing the XML element <b>xmilg</b>.',
              array('file' => substr($path, strlen(LEPTON_PATH))))));
      return false;
    }
    if (!isset($xmlIterator->attributes()->version)) {
      // missing the version information for the XMILG file!
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->I18n('The file <b>{{ file }}</b> is no valid manufaktur_I18n language file, missing the <b>version attribute</b> in the XML element <b>xmilg</b>.',
              array('file' => substr($path, strlen(LEPTON_PATH))))));
      return false;
    }
    $mcfg_version = (string) $xmlIterator->attributes()->version;

    $data = array();
    $x=0;
    for ($xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
      // we need only childs of type "language" ...
      if ($xmlIterator->key() != 'language')
        continue;
      // ok - get the attributes for the language
      $language = $xmlIterator->current()->attributes();
      if (!isset($language->iso)) {
        // missing language attributes
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->I18n('The file <b>{{ file }}</b> is no valid manufaktur_I18n language file, missing a <b>language attribute</b> in the XML element <b>language</b>, needed is at minimum <i>iso</i>.',
                array('file' => substr($path, strlen(LEPTON_PATH))))));
        return false;
      }
      $data[$x]['iso'] = (string) $language->iso;
      foreach ($xmlIterator->getChildren() as $child) {
        // we need only childs of type 'local' and 'english'
        if ($child->getName() == 'local') {
          $data[$x]['local'] = (string) $child;
        }
        elseif ($child->getName() == 'english') {
          $data[$x]['english'] = (string) $child;
        }
      }
      $x++;
    }
    if (count($data) > 0) {
      // ok - save the language records, first prepare!
      $items = array();
      $count = 0;
      foreach ($data as $lang) {
        if (isset($lang['iso']) && isset($lang['local']) && isset($lang['english'])) {
          $items[] = sprintf("('{$lang['iso']}','{$lang['local']}','{$lang['english']}')");
          $count++;
        }
      }
      if (count($items) > 0) {
        // delete the existing language entries
        $SQL = "DELETE FROM `".dbManufakturI18nLanguages::getTableName()."`";
        if (null == $database->query($SQL)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
        // insert the new language entries
        $SQL = "INSERT INTO `".dbManufakturI18nLanguages::getTableName()."` ".
            "(`lang_iso`,`lang_local`,`lang_english`) VALUES ".implode(',', $items);
        if (null == $database->query($SQL)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
        $message = $this->I18n('Deleted the old entries from the I18n language table and insert <b>{{ count }}</b> language records from the manufaktur_I18n language file <b>{{ file }}</b>.',
            array('count' => $count, 'file' => basename($path)));
      }
    }
    if (empty($message)) {
      $message = $this->I18n('There were no settings to process!');
    }
    $this->setMessage($message);
    return true;
  } // readXMLlanguageFile()

} // class manufaktur_I18n