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
  if (defined('LEPTON_VERSION')) include (WB_PATH . '/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include ($root . '/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// load language depending onfiguration
if (!file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.cfg.php')) {
  require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/DE.cfg.php');
}
else {
  require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.cfg.php');
}

if (!class_exists('manufaktur_I18n')) require_once WB_PATH . '/modules/manufaktur_i18n/library.php';
global $lang;
if (!is_object($lang)) $lang = new manufaktur_I18n();

class I18n_Dialog {

  const REQUEST_ACTION = 'i18n_act';

  const ACTION_DEFAULT = 'def';
  const ACTION_ABOUT = 'abt';
  const ACTION_TOOLS = 'tls';
  const ACTION_EDIT = 'edt';

  private $message = '';
  private $error = '';
  private $tab_navigation_array = array();
  private $module_directory = NULL;
  private $page_link = NULL;
  private $img_url = NULL;
  protected $lang = NULL;

  public function __construct($module_directory) {
    global $lang;
    $this->module_directory = $module_directory;
    $this->page_link = ADMIN_URL . '/admintools/tool.php?tool=' . $module_directory;
    $this->img_url = WB_URL . '/modules/' . basename(dirname(__FILE__)) . '/images/';
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->lang = $lang;
    // don't translate the Tab Strings here - this will be done in the template!
    $this->tab_navigation_array = array(
        self::ACTION_EDIT => 'Edit',
        self::ACTION_TOOLS => 'Tools',
        self::ACTION_ABOUT => 'About'
    );
  }

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
   * Reset Error to empty String
   */
  protected function clearError() {
    $this->error = '';
  }

  /**
   * Set $this->message to $message
   *
   * @param $message STR
   */
  protected function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
   * Get Message from $this->message;
   *
   * @return STR $this->message
   */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
   * Check if $this->message is empty
   *
   * @return BOOL
   */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage

  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.php');
    if ($info_text == false) {
      return -1;
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      }
    }
    return -1;
  } // getVersion()

  /**
   * Return the needed template
   *
   * @param $template string
   * @param $template_data array
   */
  protected function getTemplate($template, $template_data) {
    global $parser;

    $template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/backend/';

    // check if a custom template exists ...
    $load_template = (file_exists($template_path . 'custom.' . $template)) ? $template_path . 'custom.' . $template : $template_path . $template;
    try {
      $result = $parser->get($load_template, $template_data);
    } catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}', array(
        'template' => basename($load_template),
        'error' => $e->getMessage()
      ))));
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Verhindert XSS Cross Site Scripting
   *
   * @param $_REQUEST REFERENCE Array
   * @return $request
   */
  protected function xssPrevent(&$request) {
    if (is_string($request)) {
      $request = html_entity_decode($request);
      $request = strip_tags($request);
      $request = trim($request);
      $request = stripslashes($request);
    }
    return $request;
  } // xssPrevent()

  /**
   * Action handler for I18n_Dialog
   *
   * @return string - I18n Dialog
   */
  public function action() {

    $html_allowed = array();
    foreach ($_REQUEST as $key => $value) {
      if (strpos($key, 'cfg_') == 0) continue; // ignore config values!
      if (!in_array($key, $html_allowed)) {
        $_REQUEST[$key] = $this->xssPrevent($value);
      }
    }

    $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_ABOUT;

    switch ($action) {
      case self::ACTION_EDIT:
        $result = $this->show(self::ACTION_EDIT, $this->dlgEdit());
        break;
      case self::ACTION_TOOLS:
        $result = $this->show(self::ACTION_TOOLS, $this->dlgTools());
        break;
      case self::ACTION_ABOUT:
      default:
        $result = $this->show(self::ACTION_ABOUT, $this->dialogAbout());
        break;
    }
    return $result;
  } // action()

  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   *
   * @param STR $action aktives Navigationselement
   * @param STR $content Inhalt
   *
   * @return ECHO RESULT
   */
  protected function show($action, $content) {
    $navigation = array();
    foreach ($this->tab_navigation_array as $key => $value) {
      $navigation[] = array(
        'active' => ($key == $action) ? 1 : 0,
        'url' => sprintf('%s&%s', $this->page_link, http_build_query(array(
          self::REQUEST_ACTION => $key
        ))),
        'text' => $value
      );
    }
    $data = array(
      'WB_URL' => WB_URL,
      'IMG_URL' => $this->img_url,
      'navigation' => $navigation,
      'error' => (int) $this->isError(),
      'content' => ($this->isError()) ? $this->getError() : $content
    );
    return $this->getTemplate('body.lte', $data);
  } // show()

  /**
   * About Dialog for the I18n_Dialog
   *
   * @return string dialog
   */
  protected function dialogAbout() {
    $data = array(
      'version' => sprintf('%01.2f', $this->getVersion()),
      'img_url' => $this->img_url,
      'release_notes' => file_get_contents(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.txt')
    );
    return $this->getTemplate('about.lte', $data);
  } // dialogAbout();

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
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->I18n("Can't get file content: {{ file }}", array(
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
   * line
   * number, module name and filename in the $result array.
   *
   * @param string $file_path
   * @param
   *          reference array $result
   * @return boolean
   */
  protected function parseTemplateFile($file_path, &$result = array()) {
    if (false === ($source = file($file_path))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->I18n("Can't get file content: {{ file }}", array(
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
   * @param string $module
   * @return boolean - result
   */
  public function deleteEntriesByStatus($module, $status) {
    global $dbI18n;
    global $dbI18nSrc;
    global $dbI18nTrans;

    $ti = $dbI18n->getTableName();
    $tis = $dbI18nSrc->getTableName();
    $tit = $dbI18nTrans->getTableName();

    $SQL = "DELETE `$ti`,`$tis`,`$tit` FROM `$ti`,`$tis`,`$tit` WHERE " . "`$ti`.`i18n_id`=`$tis`.`i18n_id` AND `$ti`.`i18n_id`=`$tit`.`i18n_id` AND " . "`src_module`='$module' AND `i18n_status`='$status' AND " . "`src_status`='$status' AND `trans_status`='$status'";
    $result = array();
    if (!$dbI18n->sqlExec($SQL, $result)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18n->getError()));
      return false;
    }
    return true;
  } // deleteBackupEntries

  /**
   * Change all language record for the $module from the $from_status to the
   * $to_status.
   * Important: The status will also changed for all records which
   * status is set to 'IGNORE'!
   *
   * @param unknown_type $module
   * @param unknown_type $from_status
   * @param unknown_type $to_status
   */
  public function changeEntriesFromStatusToStatus($module, $from_status, $to_status) {
    global $dbI18n;
    global $dbI18nSrc;
    global $dbI18nTrans;

    $ti = $dbI18n->getTableName();
    $tis = $dbI18nSrc->getTableName();
    $tit = $dbI18nTrans->getTableName();

    $SQL = "UPDATE `$ti`,`$tis`,`$tit` SET `i18n_status`='$to_status'," . "`src_status`='$to_status',`trans_status`='$to_status' WHERE " . "`$ti`.`i18n_id`=`$tis`.`i18n_id` AND `$ti`.`i18n_id`=`$tit`.`i18n_id` AND " . "`src_module`='$module' AND (`i18n_status`='$from_status' OR " . "`i18n_status`='IGNORE')";
    $result = array();
    if (!$dbI18n->sqlExec($SQL, $result)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18n->getError()));
      return false;
    }
    return true;
  } // createBackupEntries()

  public function scanDirectory($directory) {
    global $dbI18n;
    global $dbI18nSrc;
    global $dbI18nTrans;

    // first thing we have to do: set the existing language entries to "backup" status

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
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->I18n('The file type <b>{{ file_type }}</b> is not supported!', array(
          'file_type' => $path_info['extension']
        ))));
        return false;
      }
    }

    foreach ($translation as $entry) {
      $key = addslashes($entry['key']);
      $SQL = "SELECT `i18n_id`, `i18n_key` FROM " . $dbI18n->getTableName() . " WHERE `i18n_key`='$key' AND (`i18n_status`='ACTIVE' OR `i18n_status`='IGNORE')";
      $result = array();
      if (!$dbI18n->sqlExec($SQL, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18n->getError()));
        return false;
      }
      
      $add = false;
      if (count($result) > 0) {
      	$add = ($result[0]['i18n_key'] == $key) ? false : true;
      }
      
      if ($add) {
        // entry already exists, keep only the source usage
        $data = array(
          dbManufakturI18nSources::FIELD_I18N_ID => $result[0][dbManufakturI18n::FIELD_ID],
          dbManufakturI18nSources::FIELD_PATH => $entry['path'],
          dbManufakturI18nSources::FIELD_FILE => $entry['file'],
          dbManufakturI18nSources::FIELD_LINE => $entry['line'],
          dbManufakturI18nSources::FIELD_MODULE => $entry['module']
        );
        if (!$dbI18nSrc->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18nSrc->getError()));
          return false;
        }
      }
      else {
        // create a new entry
        $data = array(
          dbManufakturI18n::FIELD_DESCRIPTION => '',
          dbManufakturI18n::FIELD_KEY => $entry['key'],
          dbManufakturI18n::FIELD_LAST_SYNC => date("Y-m-d H:i:s", time()),
          dbManufakturI18n::FIELD_STATUS => dbManufakturI18n::STATUS_ACTIVE
        );
        $id = -1;
        if (!$dbI18n->sqlInsertRecord($data, $id)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18n->getError()));
          return false;
        }
        // add the standard EN translation
        $data = array(
          dbManufakturI18nTranslations::FIELD_AUTHOR => (isset($_SESSION['DISPLAY_NAME'])) ? $_SESSION['DISPLAY_NAME'] : dbManufakturI18n::AUTHOR_UNKNOWN,
          dbManufakturI18nTranslations::FIELD_I18N_ID => $id,
          dbManufakturI18nTranslations::FIELD_LANGUAGE => 'EN',
          dbManufakturI18nTranslations::FIELD_TRANSLATION => $entry['key'],
          dbManufakturI18nTranslations::FIELD_USAGE => dbManufakturI18nTranslations::USAGE_TEXT,
          dbManufakturI18nTranslations::FIELD_TYPE => dbManufakturI18nTranslations::TYPE_REGULAR,
          dbManufakturI18nTranslations::FIELD_STATUS => dbManufakturI18nTranslations::STATUS_ACTIVE
        );
        if (!$dbI18nTrans->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18nTrans->getError()));
          return false;
        }
        // add source usage...
        $data = array(
          dbManufakturI18nSources::FIELD_I18N_ID => $id,
          dbManufakturI18nSources::FIELD_PATH => $entry['path'],
          dbManufakturI18nSources::FIELD_FILE => $entry['file'],
          dbManufakturI18nSources::FIELD_LINE => $entry['line'],
          dbManufakturI18nSources::FIELD_MODULE => $entry['module']
        );
        if (!$dbI18nSrc->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18nSrc->getError()));
          return false;
        }
      }
    }
    return true;
  } // scanDirectory()

  protected function dlgTools() {
  	$result = array();
  	//$this->parseSourceFile(LEPTON_PATH.'/modules/kit_cronjob/class.cronjob.php', $result);
  	$this->scanDirectory(LEPTON_PATH.'/modules/kit_cronjob');
    return __METHOD__;
  } // dlgTools()

  protected function dlgEdit() {
    return __METHOD__;
  } // dlgEdit()
} // I18n_Dialog