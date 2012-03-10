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
if (!file_exists(WB_PATH.'/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.cfg.php')) {
  require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.cfg.php');
} else {
  require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.cfg.php');
}

if (!class_exists('manufaktur_I18n'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE)).'/library.php';
global $lang;
if (!is_object($lang)) $lang = new manufaktur_I18n();

class I18n_Dialog {

  const REQUEST_ACTION = 'i18n_act';

  const ACTION_DEFAULT = 'def';
  const ACTION_ABOUT = 'abt';

  private $message = '';
  private $error = '';
  private $tab_navigation_array = NULL;
  private $module_directory = NULL;
  private $page_link = NULL;
  protected $lang = NULL;

  public function __construct($module_directory) {
    global $lang;
    $this->module_directory = $module_directory;
    $this->page_link = ADMIN_URL.'/admintools/tool.php?tool='.$module_directory;
    $this->img_url = WB_URL.'/modules/'.basename(dirname(__FILE__)).'/images/';
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->lang = $lang;
    $this->tab_navigation_array = array(
        self::ACTION_ABOUT => $this->lang->I18n('About')
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

    $template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/backend/';

    // check if a custom template exists ...
    $load_template = (file_exists($template_path.'custom.'.$template)) ? $template_path.'custom.'.$template : $template_path.$template;
    try {
      $result = $parser->get($load_template, $template_data);
    }
    catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}', array(
          'template' => basename($load_template),
          'error' => $e->getMessage()))));
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Verhindert XSS Cross Site Scripting
   *
   * @param $_REQUEST REFERENCE
   *          Array
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
      if (strpos($key, 'cfg_') == 0)
        continue; // ignore config values!
      if (!in_array($key, $html_allowed)) {
        $_REQUEST[$key] = $this->xssPrevent($value);
      }
    }
    $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_DEFAULT;

  } // action()

  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   *
   * @param STR $action - aktives Navigationselement
   * @param STR $content - Inhalt
   *
   * @return ECHO RESULT
   */
  protected function show($action, $content) {
    $navigation = array();
    foreach ($this->tab_navigation_array as $key => $value) {
      $navigation[] = array(
          'active' 	=> ($key == $action) ? 1 : 0,
          'url'			=> sprintf('%s&%s', $this->page_link, http_build_query(array(self::REQUEST_ACTION => $key))),
          'text'		=> $value
      );
    }
    $data = array(
        'WB_URL'			=> WB_URL,
        'navigation'	=> $navigation,
        'error'				=> (int) $this->isError(),
        'content'			=> ($this->isError()) ? $this->getError() : $content
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
        'release_notes' => file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.txt'),
    );
    return $this->getTemplate('about.lte', $data);
  } // dialogAbout();

} // I18n_Dialog