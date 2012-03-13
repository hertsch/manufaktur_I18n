<?php

/**
 * kitCronjob
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012 - phpManufaktur by Ralf Hertsch
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License (GPL)
 * @version $Id$
 *
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include (WB_PATH . '/framework/class.secure.php');
} else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include ($root . '/framework/class.secure.php');
  } else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// load language depending onfiguration
if (!file_exists(WB_PATH.'/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.cfg.php')) {
  require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.cfg.php');
} else {
  require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.cfg.php');
}

if (!class_exists('dbconnectle')) require_once WB_PATH.'/modules/dbconnect_le/include.php';

class dbManufakturI18n extends dbConnectLE {

  const FIELD_ID = 'i18n_id';
  const FIELD_KEY = 'i18n_key';
  const FIELD_DESCRIPTION = 'i18n_description';
  const FIELD_STATUS = 'i18n_status';
  const FIELD_LAST_SYNC = 'i18n_last_sync';
  const FIELD_TIMESTAMP = 'i18n_timestamp';

  const STATUS_ACTIVE = 'ACTIVE';
  const STATUS_BACKUP = 'BACKUP';
  const STATUS_IGNORE = 'IGNORE';

  private $create_table = false;

  public function __construct($create_table=false) {
    $this->create_table = $create_table;
    // set timezone
    date_default_timezone_set(CFG_TIME_ZONE);
    parent::__construct();
    $this->setTableName('mod_manufaktur_i18n');
    $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
    $this->addFieldDefinition(self::FIELD_KEY, "TEXT", false, false, true);
    $this->addFieldDefinition(self::FIELD_DESCRIPTION, "TEXT");
    $this->addFieldDefinition(self::FIELD_STATUS, "ENUM('ACTIVE', 'BACKUP', 'IGNORE') NOT NULL DEFAULT 'ACTIVE'");
    $this->addFieldDefinition(self::FIELD_LAST_SYNC, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
    $this->setAllowedHTMLtags('<a><abbr><acronym><code><b><div><em><i><label><li><p><pre><span><strong><ul>');
    $this->checkFieldDefinitions();
    // Tabelle erstellen
    if ($this->create_table) {
      if (!$this->sqlTableExists()) {
        if (!$this->sqlCreateTable()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        }
      }
    }
  } // __construct()

} // class dbManufakturI18n

class dbManufakturI18nTranslations extends dbConnectLE {

  const FIELD_ID = 'trans_id';
  const FIELD_I18N_ID = 'i18n_id';
  const FIELD_LANGUAGE = 'trans_language';
  const FIELD_TRANSLATION = 'trans_translation';
  const FIELD_USAGE = 'trans_usage';
  const FIELD_TYPE = 'trans_type';
  const FIELD_STATUS = 'trans_status';
  const FIELD_AUTHOR = 'trans_author';
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

  private $create_table = false;

  public function __construct($create_table=false) {
    $this->create_table = $create_table;
    // set timezone
    date_default_timezone_set(CFG_TIME_ZONE);
    parent::__construct();
    $this->setTableName('mod_manufaktur_i18n_trans');
    $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
    $this->addFieldDefinition(self::FIELD_I18N_ID, "INT(11) NOT NULL DEFAULT '-1'");
    $this->addFieldDefinition(self::FIELD_LANGUAGE, "VARCHAR(2) NOT NULL DEFAULT 'EN'");
    $this->addFieldDefinition(self::FIELD_TRANSLATION, "TEXT", false, false, true);
    $this->addFieldDefinition(self::FIELD_USAGE, "ENUM('TEXT','MESSAGE','ERROR','HINT','LABEL','BUTTON') NOT NULL DEFAULT 'TEXT'");
    $this->addFieldDefinition(self::FIELD_TYPE, "ENUM('REGULAR','CUSTOM') NOT NULL DEFAULT 'REGULAR'");
    $this->addFieldDefinition(self::FIELD_STATUS, "ENUM('ACTIVE','BACKUP') NOT NULL DEFAULT 'ACTIVE'");
    $this->addFieldDefinition(self::FIELD_AUTHOR, "VARCHAR(64) NOT NULL DEFAULT '- unknown -'");
    $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
    $this->setIndexFields(array(self::FIELD_LANGUAGE));
    $this->setAllowedHTMLtags('<a><abbr><acronym><code><b><div><em><i><label><li><p><pre><span><strong><ul>');
    $this->checkFieldDefinitions();
    // Tabelle erstellen
    if ($this->create_table) {
      if (!$this->sqlTableExists()) {
        if (!$this->sqlCreateTable()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        }
      }
    }
  } // __construct()

} // class dbManufakturI18nTranslations

class dbManufakturI18nSources extends dbConnectLE {

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

  private $create_table = false;

  public function __construct($create_table=false) {
    $this->create_table = $create_table;
    // set timezone
    date_default_timezone_set(CFG_TIME_ZONE);
    parent::__construct();
    $this->setTableName('mod_manufaktur_i18n_src');
    $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
    $this->addFieldDefinition(self::FIELD_I18N_ID, "INT(11) NOT NULL DEFAULT '-1'");
    $this->addFieldDefinition(self::FIELD_FILE, "VARCHAR(64) NOT NULL DEFAULT ''");
    $this->addFieldDefinition(self::FIELD_PATH, "TEXT");
    $this->addFieldDefinition(self::FIELD_MODULE, "VARCHAR(64) NOT NULL DEFAULT ''");
    $this->addFieldDefinition(self::FIELD_LINE, "INT(11) NOT NULL DEFAULT '-1'");
    $this->addFieldDefinition(self::FIELD_STATUS, "ENUM('ACTIVE','BACKUP') NOT NULL DEFAULT 'ACTIVE'");
    $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
    $this->setIndexFields(array(self::FIELD_I18N_ID, self::FIELD_MODULE));
    $this->checkFieldDefinitions();
    // Tabelle erstellen
    if ($this->create_table) {
      if (!$this->sqlTableExists()) {
        if (!$this->sqlCreateTable()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        }
      }
    }
  } // __construct()

} // class dbManufakturI18nSources

global $dbI18n;
if (!is_object($dbI18n)) $dbI18n = new dbManufakturI18n();

global $dbI18nSrc;
if (!is_object($dbI18nSrc)) $dbI18nSrc = new dbManufakturI18nSources();

global $dbI18nTrans;
if (!is_object($dbI18nTrans)) $dbI18nTrans = new dbManufakturI18nTranslations();