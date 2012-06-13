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


$module_directory = 'manufaktur_i18n';
$module_name = 'manufaktur_I18n';
$module_function = (defined('LEPTON_VERSION')) ? 'library' : 'snippet';
$module_version = '0.10';
$module_status = 'BETA';
$module_platform = '2.8';
$module_author = 'Ralf Hertsch - Berlin (Germany)';
$module_license = 'GNU Public License (GPL)';
$module_description = 'Language administration for phpManufaktur addons';
$module_home = 'http://phpmanufaktur.de/manufaktur_i18n';
$module_guid = '224EC934-A3EC-454C-9D0A-984993F4DF3D';

?>