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

// wb2lepton compatibility
if (!defined('LEPTON_PATH'))
  require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/wb2lepton.php';

if (!class_exists('manufaktur_I18n'))
  require_once LEPTON_PATH . '/modules/manufaktur_i18n/library.php';
global $lang;
if (!is_object($lang))
  $lang = new manufaktur_I18n(LANGUAGE);

if (!class_exists('manufakturConfigDialog')) {
  if (file_exists(LEPTON_PATH.'/modules/manufaktur_config/class.dialog.php')) {
    require_once LEPTON_PATH.'/modules/manufaktur_config/class.dialog.php';
  }
  else {
    trigger_error('manufaktur_I18n needs the configuration utility '.
        '<a href="http://phpmanufaktur.de/cms/downloads.php" traget="_blank">'.
        'manufakturConfig</a>. Please install and try again!', E_USER_ERROR);
  }
}

class manufakturI18nDialog {

  const REQUEST_ACTION = 'i18n_act';
  const REQUEST_SUB_ACTION = 'i18n_sub';
  const REQUEST_SUB_SUB_ACTION = 'i18n_ssa';
  const REQUEST_ITEMS = 'i18n_its';
  const REQUEST_LANGUAGE = 'i18n_lang';
  const REQUEST_XML_FILE = 'xmlf';


  const ACTION_DEFAULT = 'def';
  const ACTION_ABOUT = 'abt';
  const ACTION_TOOLS = 'tls';
  const ACTION_EDIT = 'edt';
  const ACTION_EDIT_CHECK = 'edtc';
  const ACTION_SETTINGS = 'set';
  const ACTION_SETTING_GENERAL = 'stg';
  const ACTION_SETTING_USERS = 'stu';
  const ACTION_SETTING_LANGUAGES = 'stl';
  const ACTION_SETTING_LANGUAGES_CHECK = 'stlc';
  const ACTION_IMPORT_LANGUAGE = 'ilg';

  private static $message = '';
  private static $error = '';
  private static $tab_navigation_array = array();
  private static $tab_settings_array = array();
  private static $module_directory = null;
  private static $module_name = null;
  private static $dialog_link = null;
  private static $img_url = null;
  protected $lang = null;
  protected $config = null;

  public function __construct($module_directory, $module_name, $dialog_link) {
    global $lang;
    self::$module_directory = $module_directory;
    self::$module_name = $module_name;
    self::$dialog_link = $dialog_link;
    self::$img_url = WB_URL.'/modules/'.basename(dirname(__FILE__)).'/images/';
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->lang = $lang;
    // don't translate the Tab Strings here - this will be done in the template!
    self::$tab_navigation_array = array(
        self::ACTION_EDIT => 'Edit',
        self::ACTION_TOOLS => 'Tools',
        self::ACTION_SETTINGS => 'Settings',
        self::ACTION_ABOUT => 'About'
    );
    self::$tab_settings_array = array(
        self::ACTION_SETTING_GENERAL => 'General',
        //self::ACTION_SETTING_USERS => 'User',
        self::ACTION_SETTING_LANGUAGES => 'Languages'
        );
    $this->config = new manufakturConfig('manufaktur_i18n');
  } // __construct()

  /**
   * Set $this->error to $error
   *
   * @param $error STR
   */
  protected function setError($error) {
    self::$error = $error;
  } // setError()

  /**
   * Get Error from $this->error;
   *
   * @return STR $this->error
   */
  public function getError() {
    return self::$error;
  } // getError()

  /**
   * Check if $this->error is empty
   *
   * @return BOOL
   */
  public function isError() {
    return (bool) !empty(self::$error);
  } // isError

  /**
   * Reset Error to empty String
   */
  protected function clearError() {
    self::$error = '';
  }

  /**
   * Set $this->message to $message
   *
   * @param $message STR
   */
  protected function setMessage($message) {
    self::$message = $message;
  } // setMessage()

  /**
   * Get Message from $this->message;
   *
   * @return STR $this->message
   */
  public function getMessage() {
    return self::$message;
  } // getMessage()

  /**
   * Check if $this->message is empty
   *
   * @return BOOL
   */
  public function isMessage() {
    return (bool) !empty(self::$message);
  } // isMessage

  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.php');
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

    $template_path = LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/backend/';

    // check if a custom template exists ...
    $load_template = (file_exists($template_path . 'custom.' . $template)) ? $template_path . 'custom.' . $template : $template_path . $template;
    try {
      $result = $parser->get($load_template, $template_data);
    } catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->I18n('Error executing the template <b>{{ template }}</b>: {{ error }}', array(
              'template' => basename($load_template),
              'error' => $e->getMessage()))));
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Action handler for I18n_Dialog
   * This function expects that the xssPrevent method will be executed by the
   * calling backend module!
   *
   * @return string - I18n Dialog
   */
  public function action() {

    if (null == $this->config->getValue('cfgSourcesShow', 'manufaktur_i18n')) {
      // the configuration settings does not exists
      $config_xml = LEPTON_PATH.'/modules/manufaktur_i18n/data/config.xml';
      if (file_exists($config_xml)) {
        if (!$this->config->readXMLfile($config_xml)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->config->getError()));
          return $this->show(self::ACTION_SETTINGS, '');
        }
        else {
          $this->setMessage($this->config->getMessage());
        }
      }
      else {
        // fatal error: cant load the config.xml
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->I18n("Cant't load the configuration file {{ file }}",
            array('file' => substr($config_xml, strlen(LEPTON_PATH))))));
        return $this->show(self::ACTION_SETTINGS, '');
      }
    }

    $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_ABOUT;

    switch ($action) {
      case self::ACTION_SETTINGS:
        $result = $this->show(self::ACTION_SETTINGS, $this->actionSettings());
        break;
      case self::ACTION_EDIT:
        $result = $this->show(self::ACTION_EDIT, $this->dlgEdit());
        break;
      case self::ACTION_TOOLS:
        $result = $this->show(self::ACTION_TOOLS, $this->dlgTools());
        break;
      case self::ACTION_ABOUT:
      default:
        $result = $this->show(self::ACTION_ABOUT, $this->dlgAbout());
        break;
    }
    return $result;
  } // action()

  protected function actionSettings() {
    if (isset($_REQUEST[self::REQUEST_SUB_SUB_ACTION])) {
      $action = $_REQUEST[self::REQUEST_SUB_SUB_ACTION];
    }
    else {
      $action = isset($_REQUEST[self::REQUEST_SUB_ACTION]) ? $_REQUEST[self::REQUEST_SUB_ACTION] : self::ACTION_SETTING_GENERAL;
    }
    switch ($action) {
      case self::ACTION_IMPORT_LANGUAGE:
        $result = $this->showSetting(self::ACTION_SETTING_LANGUAGES, $this->execImportXMLlanguage());
        break;
      case self::ACTION_SETTING_LANGUAGES:
        $result = $this->showSetting(self::ACTION_SETTING_LANGUAGES, $this->dlgSettingLanguages());
        break;
      case self::ACTION_SETTING_LANGUAGES_CHECK:
        $result = $this->showSetting(self::ACTION_SETTING_LANGUAGES, $this->checkSettingLanguages());
        break;
      case self::ACTION_SETTING_USERS:
        $result = $this->showSetting(self::ACTION_SETTING_USERS, $this->dlgSettingUsers());
        break;
      case self::ACTION_SETTING_GENERAL:
      default:
        $result = $this->showSetting(self::ACTION_SETTING_GENERAL, $this->dlgSettingGeneral());
        break;
    }
    return $result;
  } // actionSettings()

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
    foreach (self::$tab_navigation_array as $key => $value) {
      $navigation[] = array(
        'active' => ($key == $action) ? 1 : 0,
        'url' => sprintf('%s&%s', self::$dialog_link, http_build_query(array(
          self::REQUEST_ACTION => $key
        ))),
        'text' => $value
      );
    }
    $data = array(
      'LEPTON_URL' => LEPTON_URL,
      'IMG_URL' => self::$img_url,
      'navigation' => $navigation,
      'error' => (int) $this->isError(),
      'content' => ($this->isError()) ? $this->getError() : $content
    );
    return $this->getTemplate('body.lte', $data);
  } // show()

  protected function showSetting($action, $content) {
    $navigation = array();
    foreach (self::$tab_settings_array as $key => $value) {
      $navigation[] = array(
          'active' => ($key == $action) ? 1 : 0,
          'url' => sprintf('%s&amp;%s',
              self::$dialog_link,
              http_build_query(array(
                  self::REQUEST_ACTION => self::ACTION_SETTINGS,
                  self::REQUEST_SUB_ACTION => $key
                  ))),
          'text' => $value
          );
    }
    $data = array(
        'content' => $content,
        'navigation' => $navigation,
        'IMG_URL' => self::$img_url
        );
    return $this->getTemplate('settings.lte', $data);
  } // showSetting()

  /**
   * About Dialog for the I18n_Dialog
   *
   * @return string dialog
   */
  protected function dlgAbout() {
    $data = array(
      'version' => sprintf('%01.2f', $this->getVersion()),
      'img_url' => self::$img_url,
      'release_notes' => file_get_contents(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.txt')
    );
    return $this->getTemplate('about.lte', $data);
  } // dlgAbout();

  protected function dlgTools() {
  	$result = array();
  	//$this->parseSourceFile(LEPTON_PATH.'/modules/kit_cronjob/class.cronjob.php', $result);
  	//$this->scanDirectory(LEPTON_PATH.'/modules/kit_cronjob');
  	if (!$this->lang->scanDirectory(LEPTON_PATH.'/modules/manufaktur_config')) {
  	  $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->getError()));
  	  return false;
  	}
    return __METHOD__;
  } // dlgTools()

  protected function dlgEdit() {
    global $database;

    $module_directory = 'manufaktur_config';
    $language = 'EN';

    // first step: get the needed ID's
    $SQL = "SELECT DISTINCT `i18n_id` FROM `".dbManufakturI18nSources::getTableName()."` ".
        "WHERE `src_module`='$module_directory' AND `src_status`='ACTIVE'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $needed_ids = array();
    while (false !== ($data = $query->fetchRow(MYSQL_ASSOC))) $needed_ids[] = $data['i18n_id'];
    $nids = implode(',', $needed_ids);

    // second step: get KEY data for the ID's
    $SQL = "SELECT * FROM `".dbManufakturI18n::getTableName()."` WHERE FIND_IN_SET(`i18n_id`, '$nids')";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    // container for the KEY records
    $key_data = array();
    // container for alphabetic sort
    $key_sort = array();
    while (false !== ($data = $query->fetchRow(MYSQL_ASSOC))) {
      $key_data[$data['i18n_id']] = $data;
      $text = manufaktur_I18n::unsanitize($data['i18n_key']);
      $text = strip_tags($text);
      // get plain lowercase key strings for alphabetic sort
      $key_sort[$data['i18n_id']] = strtolower($text);
    }
    // sort the KEYs alphabetic
    asort($key_sort, SORT_STRING);

    // third step: get the source informations
    $SQL = "SELECT * FROM `".dbManufakturI18nSources::getTableName().
      "` WHERE FIND_IN_SET(`i18n_id`, '$nids') AND `src_status`='ACTIVE'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $sources_data = array();
    while (false !== ($data = $query->fetchRow(MYSQL_ASSOC)))
      $sources_data[$data['src_id']] = $data;

    // fourth step: get the translated strings for the needed language
    $SQL = "SELECT * FROM `".dbManufakturI18nTranslations::getTableName().
      "` WHERE FIND_IN_SET(`i18n_id`, '$nids') AND `trans_language`='$language' AND ".
      "`trans_type`='REGULAR' AND `trans_status`='ACTIVE'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $translated_data = array();
    while (false !== ($data = $query->fetchRow(MYSQL_ASSOC)))
      $translated_data[$data['i18n_id']] = $data;

    // fifth step: get the custom strings for the needed language
    $SQL = "SELECT * FROM `".dbManufakturI18nTranslations::getTableName().
    "` WHERE FIND_IN_SET(`i18n_id`, '$nids') AND `trans_language`='$language' AND ".
    "`trans_type`='CUSTOM' AND `trans_status`='ACTIVE'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $custom_data = array();
    while (false !== ($data = $query->fetchRow(MYSQL_ASSOC)))
      $custom_data[$data['i18n_id']] = $data;

    // sixth step: get the language string
    $SQL = "SELECT * FROM `".dbManufakturI18nLanguages::getTableName().
      "` WHERE `lang_iso`='".strtolower($language)."'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $lang = $query->fetchRow(MYSQL_ASSOC);

    // now step through all data and build the items array
    $items = array();
    foreach ($key_sort as $id => $sort_string) {
      $sources = array();
      $i = 0;
      foreach ($sources_data as $source) {
        if ($source['i18n_id'] == $id) {
          $sources[$source['src_id']] = array(
              'id' => $source['src_id'],
              'i18n_id' => $id,
              'file' => $source['src_file'],
              'path' => $source['src_path'],
              'directory' => $source['src_module'],
              'line' => $source['src_line'],
              'timestamp' => $source['src_timestamp']
              );
          $i++;
        }
      }
      $items[$id] = array(
          'key' => array(
              'id' => $id,
              'value' => $key_data[$id]['i18n_key'],
              'last_sync' => date(CFG_DATETIME_STR, strtotime($key_data[$id]['i18n_last_sync'])),
              'status' => $key_data[$id]['i18n_status']
              ),
          'description' => array(
              'value' => manufaktur_I18n::unsanitize($key_data[$id]['i18n_description']),
              'name' => sprintf('i18n_description_%d', $id)
              ),
          'translation' => array(
              'value' => (isset($translated_data[$id])) ? manufaktur_I18n::unsanitize($translated_data[$id]['trans_translation']) : '',
              'name' => sprintf('i18n_translation_%d', $id),
              'author' => (isset($translated_data[$id])) ? manufaktur_I18n::unsanitize($translated_data[$id]['trans_author']) : '',
              'timestamp' => (isset($translated_data[$id])) ? date(CFG_DATETIME_STR, strtotime($translated_data[$id]['trans_timestamp'])) : '',
              'quality' => array(
                  'value' => (isset($translated_data[$id])) ? $translated_data[$id]['trans_quality'] : '0',
                  'name' => sprintf('i18n_quality_%d', $id)
                  ),
              'is_empty' => array(
                  'value' => (isset($translated_data[$id])) ? $translated_data[$id]['trans_is_empty'] : '0',
                  'name' => sprintf('i18n_is_empty_%d', $id)
                  ),
              ),
          'sources' => $sources,
          'custom' => array(
              'value' => (isset($custom_data[$id])) ? manufaktur_I18n::unsanitize($custom_data[$id]['trans_translation']) : '',
              'name' => sprintf('custom_translation_%d', $id),
              'is_empty' => array(
                  'value' => (isset($custom_data[$id])) ? $custom_data[$id]['trans_is_empty'] : '0',
                  'name' => sprintf('custom_is_empty_%d', $id)
                  ),
              ),
          );
    }
    $its = array();
    $data = array(
        'form' => array(
            'name' => 'i18n_edit',
            'action' => self::$dialog_link
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_EDIT_CHECK
            ),
        'request_items' => array(
            'name' => self::REQUEST_ITEMS,
            'value' => implode(',', $its)
            ),
        'message' => array(
            'is_message' => ($this->isMessage()) ? 1 : 0,
            'text' => $this->getMessage()
            ),
        'items' => $items,
        'language' => array(
            'id' => $lang['lang_id'],
            'iso' => $lang['lang_iso'],
            'local' => $lang['lang_local'],
            'english' => $lang['lang_english']
        ),
        'settings' => array(
            'show_sources' => (int) $this->config->getValue('cfgSourcesShow', 'manufaktur_i18n'),
            'edit_descriptions' => (int) $this->config->getValue('cfgDescriptionsEdit', 'manufaktur_i18n')
            )
        );
    return $this->getTemplate('edit.lte', $data);
  } // dlgEdit()

  protected function dlgSettingGeneral() {
    $link = sprintf('%s&amp;%s',
        self::$dialog_link,
        http_build_query(array(
            self::REQUEST_ACTION => self::ACTION_SETTINGS,
            self::REQUEST_SUB_ACTION => self::ACTION_SETTING_GENERAL)));
    $dialog = new manufakturConfigDialog('manufaktur_i18n', 'manufaktur_I18n', $link);
    return $dialog->action();
  } // dlgSettingGeneral()

  protected function dlgSettingUsers() {
    return __METHOD__;
  } // dlgSettingUsers()

  protected function dlgSettingLanguages() {
    global $database;

    $SQL = "SELECT * FROM `".dbManufakturI18nLanguages::getTableName()."` ORDER BY `lang_iso` ASC";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $request_items = array();
    $items = array();
    while (false !== ($lang = $query->fetchRow(MYSQL_ASSOC))) {
      $request_items[] = $lang['lang_id'];
      $items[$lang['lang_id']] = array(
          'id' => $lang['lang_id'],
          'iso' => $lang['lang_iso'],
          'local' => $lang['lang_local'],
          'english' => $lang['lang_english'],
          'name' => sprintf('lang_id_%d', $lang['lang_id'])
          );
    }
    $data = array(
        'form' => array(
            'name' => 'i18n_languages',
            'action' => self::$dialog_link
        ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_SETTINGS
        ),
        'sub_action' => array(
            'name' => self::REQUEST_SUB_ACTION,
            'value' => self::ACTION_SETTING_LANGUAGES_CHECK
            ),
        'request_items' => array(
            'name' => self::REQUEST_ITEMS,
            'value' => implode(',', $request_items)
        ),
        'message' => array(
            'is_message' => ($this->isMessage()) ? 1 : 0,
            'text' => ($this->isMessage()) ? $this->getMessage() : ''
            ),
        'items' => $items,
        'add' => array(
            'iso' => array(
                'value' => isset($_REQUEST['lang_iso']) ? $_REQUEST['lang_iso'] : '',
                'name' => 'lang_iso'
                ),
            'local' => array(
                'value' => isset($_REQUEST['lang_local']) ? $_REQUEST['lang_local'] : '',
                'name' => 'lang_local'
                ),
            'english' => array(
                'value' => isset($_REQUEST['lang_english']) ? $_REQUEST['lang_english'] : '',
                'name' => 'lang_english'
                )
            ),
        'xml' => array(
            'name' => 'lang_xml',
            'options' => array(
                array(
                    'value' => '0',
                    'text' => $this->lang->I18n('- no XML action -')
                    ),
                array(
                    'value' => 'export',
                    'text' => $this->lang->I18n('Export languages as XML file')
                    ),
                array(
                    'value' => 'import',
                    'text' => $this->lang->I18n('Import languages from XML file')
                    )
                )
            )
        );
    return $this->getTemplate('settings.languages.lte', $data);
  } // dlgSettingLanguages()

  protected function checkSettingLanguages() {
    global $database;
    if (!isset($_REQUEST[self::REQUEST_ITEMS])) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->I18n('Missing $_REQUEST <b>i18n_its</b>!')));
      return false;
    }
    if (isset($_REQUEST['lang_xml']) && ($_REQUEST['lang_xml'] == 'export')) {
      return $this->exportXMLlanguage();
    }
    elseif (isset($_REQUEST['lang_xml']) && ($_REQUEST['lang_xml'] == 'import')) {
      return $this->importXMLlanguage();
    }
    $message = '';
    $this->setMessage('');
    $items = explode(',', $_REQUEST[self::REQUEST_ITEMS]);
    // first step: delete entries
    foreach ($items as $id) {
      if (isset($_REQUEST[sprintf('lang_id_%d', $id)])) {
        $SQL = "DELETE FROM `".dbManufakturI18nLanguages::getTableName()."` WHERE `lang_id`='$id'";
        if (null == $database->query($SQL)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
        $message .= $this->lang->I18n('<p>The language item with the <b>ID {{ id }}</b> was successfull deleted.</p>',
            array('id' => $id));
      }
    }
    // second step: add entry
    if (isset($_REQUEST['lang_iso']) && !empty($_REQUEST['lang_iso']) &&
        isset($_REQUEST['lang_local']) && !empty($_REQUEST['lang_local']) &&
        isset($_REQUEST['lang_english']) && !empty($_REQUEST['lang_english'])) {
      // add the new entry
      $iso = strtolower($_REQUEST['lang_iso']);
      $SQL = "INSERT INTO `".dbManufakturI18nLanguages::getTableName()."` (".
          "`lang_iso`,`lang_local`,`lang_english`) VALUES (".
          "'$iso','{$_REQUEST['lang_local']}','{$_REQUEST['lang_english']}')";
      if (null == $database->query($SQL)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }
      unset($_REQUEST['lang_iso']);
      unset($_REQUEST['lang_local']);
      unset($_REQUEST['lang_english']);
      $message .= $this->lang->I18n('<p>The language with the <b>ISO Code {{ iso }}</b> was successfull added.</p>',
          array('iso' => $iso));
    }
    $this->setMessage($message);
    return $this->dlgSettingLanguages();
  } // checkSettingLanguages()


  protected function exportXMLlanguage() {
    $path = LEPTON_PATH.MEDIA_DIRECTORY.DIRECTORY_SEPARATOR.date('ymd')."-i18n-languages.xml";

    if (!$this->lang->writeXMLlanguageFile($path)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->getError()));
      return false;
    }
    $this->setMessage($this->lang->getMessage());
    return $this->dlgSettingLanguages();
  } // exportXMLexport()

  protected function importXMLlanguage() {
    $data = array(
        'form' => array(
            'name' => 'i18n_lang_import',
            'action' => self::$dialog_link
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_SETTINGS
            ),
        'sub_action' => array(
            'name' => self::REQUEST_SUB_ACTION,
            'value' => self::ACTION_SETTING_LANGUAGES
             ),
        'sub_sub_action' => array(
            'name' => self::REQUEST_SUB_SUB_ACTION,
            'value' => self::ACTION_IMPORT_LANGUAGE
            ),
        'message' => array(
            'text' => $this->isMessage() ? $this->getMessage() : ''),
        'xml' => array(
            'name' => self::REQUEST_XML_FILE
            )
        );
    return $this->getTemplate('import.language.lte', $data);
  } // importXMLlanguage()

  protected function execImportXMLlanguage() {
    $xml_path = null;
    // first: check upload
    if (isset($_FILES[self::REQUEST_XML_FILE]) && (is_uploaded_file($_FILES[self::REQUEST_XML_FILE]['tmp_name']))) {
      if ($_FILES[self::REQUEST_XML_FILE]['error'] == UPLOAD_ERR_OK) {
        if ($_FILES[self::REQUEST_XML_FILE]['type'] != 'text/xml') {
          // this is not a XML file!
          $this->setMessage($this->lang->I18n('The uploaded file <b>{{ file }}</b> is not a valid XML file!',
              array('file' => $_FILES[self::REQUEST_XML_FILE]['name'])));
          @unlink($_FILES[self::REQUEST_XML_FILE]['tmp_name']);
          return $this->importXMLlanguage();
        }
        $xml_path = LEPTON_PATH.'/temp/'.$_FILES[self::REQUEST_XML_FILE]['name'];
        if (!move_uploaded_file($_FILES[self::REQUEST_XML_FILE]['tmp_name'], $xml_path)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
              $this->lang->I18n('The file {{ file }} could not moved to the temporary directory.',
                  array('file' => $_FILES[self::REQUEST_XML_FILE]['name']))));
          return false;
        }
      }
      else {
        switch ($_FILES[self::REQUEST_XML_FILE]['error']) :
        case UPLOAD_ERR_INI_SIZE:
          $error = $this->lang->I18n('The uploaded file <b>{{ file }}</b> is greater than the parameter <b>upload_max_filesize</b> of <b>{{ max_size }}</b> within the <b>php.ini</b>',
              array('max_size' => ini_get('upload_max_filesize'), 'file' => $_FILES[self::REQUEST_XML_FILE]['name']));
          break;
        case UPLOAD_ERR_FORM_SIZE:
          $error = $this->lang->I18n('The uploaded file <b>{{ file }}</b> is greater than MAX_FILE_SIZE within the form directive.',
              array('file' => $_FILES[self::REQUEST_XML_FILE]['name']));
          break;
        case UPLOAD_ERR_PARTIAL:
          $error = $this->lang->I18n('The file <b>{{ file }}</b> was uploaded partial, please try again!',
              array('file' => $_FILES[self::request_file]['name']));
          break;
        default:
          $error = $this->lang->I18n('A not described error occured during file upload, please try again!');
          break;
        endswitch;
        @unlink($_FILES[self::REQUEST_XML_FILE]['tmp_name']);
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $error));
        return false;
      }
    }
    else {
      // nothing to do ...
      $this->setMessage($this->lang->I18n('There was no file specified for upload!'));
      return $this->importXMLlanguage();
    }

    if (!$this->lang->readXMLlanguageFile($xml_path)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->getError()));
      return false;
    }
    $this->setMessage($this->lang->getMessage());
    return $this->dlgSettingLanguages();
  } // execImportXMLlanguage()

} // class manufakturI18nDialog