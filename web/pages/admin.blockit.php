<?php
/*************************************************************************
This file is part of SourceBans++

SourceBans++ (c) 2014-2019 by SourceBans++ Dev Team

The SourceBans++ Web panel is licensed under a
Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-nc-sa/3.0/>.

This program is based off work covered by the following copyright(s):
SourceBans 1.4.11
Copyright © 2007-2014 SourceBans Team - Part of GameConnect
Licensed under CC-BY-NC-SA 3.0
Page: <http://www.sourcebans.net/> - <http://www.gameconnect.net/>
*************************************************************************/

include_once '../init.php';

if (!$userbank->HasAccess(ADMIN_OWNER | ADMIN_ADD_BAN)) {
    echo "No Access";
    die();
}
require_once(INCLUDES_PATH . '/xajax.inc.php');
$xajax = new xajax();
//$xajax->debugOn();
$xajax->setRequestURI("./admin.blockit.php");
$xajax->registerFunction("BlockPlayer");
$xajax->registerFunction("LoadServers2");
$xajax->processRequests();
$username = $userbank->GetProperty("user");

function LoadServers2($check, $type, $length)
{
    $objResponse = new xajaxResponse();
    global $userbank, $username;
    if (!$userbank->HasAccess(ADMIN_OWNER | ADMIN_ADD_BAN)) {
        $objResponse->redirect("index.php?p=login&m=no_access", 0);
        Log::add("w", "Hacking Attempt", "$username tried to use blockit, but doesn't have access.");
        return $objResponse;
    }

    $GLOBALS['PDO']->query("SELECT sid, rcon FROM `:prefix_servers` WHERE enabled = 1 ORDER BY modid, sid");
    $servers = $GLOBALS['PDO']->resultset();
    foreach($servers as $id => $server) {
        if (!empty($servers["rcon"])) {
            $text = '<font size="1">Searching...</font>';
            $objResponse->addScript("xajax_BlockPlayer('" . $check . "', '" . $servers["sid"] . "', '" . $id . "', '" . $type . "', '" . $length . "');");
        } else { //no rcon = servercount + 1 ;)
            $text = '<font size="1">No rcon password.</font>';
            $objResponse->addScript('set_counter(1);');
        }
        $objResponse->addAssign("srv_" . $id, "innerHTML", $text);
    }

    return $objResponse;
}

function BlockPlayer($check, int $sid, $num, $type, int $length)
{
    require_once("../includes/system-functions.php");

    $objResponse = new xajaxResponse();
    global $userbank, $username;

    if (!$userbank->HasAccess(ADMIN_OWNER | ADMIN_ADD_BAN)) {
        $objResponse->redirect("index.php?p=login&m=no_access", 0);
        Log::add("w", "Hacking Attempt", "$username tried to process a playerblock, but doesnt have access.");
        return $objResponse;
    }

    $ret = rcon('status', $sid);

    if (!$ret) {
        $objResponse->addAssign("srv_$num", "innerHTML", "<font color='red' size='1'><i>Can't connect to server.</i></font>");
        $objResponse->addScript('set_counter(1);');
        return $objResponse;
    }

    // show hostname instead of the ip, but leave the ip in the title
    $hostsearch = preg_match_all('/hostname:[ ]*(.+)/', $ret, $hostname, PREG_PATTERN_ORDER);
    $hostname   = trunc(htmlspecialchars($hostname[1][0]), 25);
    if (!empty($hostname))
        $objResponse->addAssign("srvip_$num", "innerHTML", "<font size='1'><span title='" . $sdata['ip'] . ":" . $sdata['port'] . "'>" . $hostname . "</span></font>");

    foreach (parseRconStatus($ret) as $player) {
        if (\SteamID\SteamID::compare($player['steamid'], $check)) {
            $GLOBALS['PDO']->query("UPDATE `:prefix_comms` SET sid = :sid WHERE authid = :authid AND RemovedBy IS NULL");
            $GLOBALS['PDO']->bind(':sid', $sid);
            $GLOBALS['PDO']->bind(':authid', $check);
            $GLOBALS['PDO']->execute();

            rcon("sc_fw_block $type $length $player[steamid]", $sid);

            $objResponse->addAssign("srv_$num", "innerHTML", "<font color='green' size='1'><b><u>Player Found & blocked!</u></b></font>");
            $objResponse->addScript("set_counter('-1');");
            return $objResponse;
        }
    }

    $objResponse->addAssign("srv_$num", "innerHTML", "<font size='1'>Player not found.</font>");
    $objResponse->addScript('set_counter(1);');
    return $objResponse;
}

$GLOBALS['PDO']->query("SELECT ip, port FROM `:prefix_servers` WHERE enabled = 1 ORDER BY modid, sid");

$servers = $GLOBALS['PDO']->resultset();
$theme->assign('total', count($servers));

foreach(array_keys($servers) as $key) {
    $servers[$key]['num'] = $key;
}

$theme->assign('servers', $servers);
$theme->assign('xajax_functions', $xajax->printJavascript("../scripts", "xajax.js"));
$theme->assign('check', $_GET["check"]); // steamid or ip address
$theme->assign('type', $_GET['type']);
$theme->assign('length', $_GET['length']);

$theme->left_delimiter  = "-{";
$theme->right_delimiter = "}-";
$theme->display('page_blockit.tpl');
$theme->left_delimiter  = "{";
$theme->right_delimiter = "}";
