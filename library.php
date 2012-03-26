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

// load language depending onfiguration
if (!file_exists(WB_PATH.'/modules/manufaktur_i18n/languages/' . LANGUAGE . '.cfg.php')) {
  require_once(WB_PATH .'/modules/manufaktur_i18n/languages/DE.cfg.php');
} else {
  require_once(WB_PATH .'/modules/manufaktur_i18n/languages/' .LANGUAGE .'.cfg.php');
}

require_once WB_PATH.'/modules/manufaktur_i18n/class.i18n.php';

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

  public function loadLanguage($module_directory, $language, $type=dbManufakturI18nTranslations::TYPE_REGULAR) {
  	global $dbI18n;
    global $dbI18nTrans;
    global $dbI18nSrc;
    
    $i18n = $dbI18n->getTableName();
    $i18nT = $dbI18nTrans->getTableName();
    $i18nS = $dbI18nSrc->getTableName();
    
    $SQL = "SELECT $i18n.i18n_key, $i18nT.trans_translation FROM $i18n,$i18nT,$i18nS WHERE ".
      "$i18n.i18n_id=$i18nT.i18n_id AND $i18n.i18n_id=$i18nS.i18n_id AND $i18nS.src_module='$module_directory' AND ".
      "$i18nT.trans_language='$language' AND $i18nT.trans_status='ACTIVE' AND $i18nT.trans_type='REGULAR'";
    $result = array();
    if (!$dbI18n->sqlExec($SQL, $result)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbI18n->getError()));
      return false;
    }
    foreach ($result as $item) {
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
   * This is a dummy function, needed to register strings which should not translated
   * yet but added to the database. The function returns the string without any change.
   * 
   * @param string $translate
   * @return string $translate
   */
  public function I18n_Register($translate) {
  	return $translate;
  } // I18n_Register()

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

} // class manufaktur_I18n