<?php
/*
 * Copyright 2005-2018 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 *
 */
 
session_start();
foreach ($_POST as $key => $value) {
    $_SESSION[$key] = $value;
}
$mandatoryFields = array('ADMIN_PASSWORD', 'confirm_password', 'firstname', 'lastname', 'email');
$strError = '';

$emailRegexp = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";
if (!preg_match($emailRegexp, $_POST['email'])) {
    $strError .= 'jQuery("input[name=email]").next().html("Invalid E-mail");';
}

foreach ($mandatoryFields as $field) {
    if ($_POST[$field] == '') {
        $strError .= 'jQuery("input[name='.$field.']").next().html("Mandatory field");';
    }
}

if ($_POST['ADMIN_PASSWORD'] != $_POST['confirm_password']) {
    $strError .= 'jQuery("input[name=confirm_password]").next().html("Passwords do not match");';
}

if ($strError) {
    echo $strError;
} else {
    echo 0;
}
