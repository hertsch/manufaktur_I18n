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

require_once WB_PATH.'/modules/manufaktur_i18n/library.php';

global $admin;

$tables = array(
    'dbManufakturI18n',
    'dbManufakturI18nSources',
    'dbManufakturI18nTranslations',
    'dbManufakturI18nLanguages'
    );
$error = '';

foreach ($tables as $table) {
  $create = null;
  $create = new $table();
  if (!$create->createTable()) {
    $error .= sprintf('[INSTALLATION %s] %s', $table, $create->getError());
  }
  if ($table == 'dbManufakturI18nLanguages') {
    if (!$create->readLanguageCSV()) {
      $error .= sprintf('[INSTALLATION %s] %s', $table, $create->getError());
    }
  }
}

// Prompt Errors
if (!empty($error)) {
  $admin->print_error($error);
}
