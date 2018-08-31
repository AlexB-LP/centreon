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
$stateType = 'host';
require_once realpath(dirname(__FILE__) . "/initXmlFeed.php");

if (isset($_GET["id"]) && isset($_GET["color"])) {
    /* Validate the type of request arguments for security */
    if (!is_numeric($_GET['id'])) {
        $buffer->writeElement('error', 'Bad id format');
        $buffer->endElement();
        header('Content-Type: text/xml');
        $buffer->output();
        exit;
    }

    $color = array();
    foreach ($_GET["color"] as $key => $value) {
        $color[$key] = htmlentities($value, ENT_QUOTES, "UTF-8");
    }

    $hosts_id = $centreon->user->access->getHostHostGroupAclConf($_GET["id"], "broker");
    if (count($hosts_id) > 0) {
        $rq = 'SELECT `date_start`, `date_end`, sum(`UPnbEvent`) as UPnbEvent, sum(`DOWNnbEvent`) as DOWNnbEvent, '
            . 'sum(`UNREACHABLEnbEvent`) as UNREACHABLEnbEvent, '
            . 'avg( `UPTimeScheduled` ) as "UPTimeScheduled", '
            . 'avg( `DOWNTimeScheduled` ) as "DOWNTimeScheduled", '
            . 'avg( `UNREACHABLETimeScheduled` ) as "UNREACHABLETimeScheduled", '
            . 'avg( `UNDETERMINEDTimeScheduled` ) as "UNDETERMINEDTimeScheduled" '
            . 'FROM `log_archive_host` WHERE `host_id` IN ('
            . implode(',', array_keys($hosts_id)) . ') GROUP BY date_end, date_start ORDER BY date_start desc';
        $DBRESULT = $pearDBO->query($rq);
        while ($row = $DBRESULT->fetchRow()) {
            fillBuffer($statesTab, $row, $color);
        }
        $DBRESULT->free();
    }
} else {
    $buffer->writeElement("error", "error");
}
$buffer->endElement();

header('Content-Type: text/xml');

$buffer->output();
