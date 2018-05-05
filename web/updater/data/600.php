<?php
$this->dbs->query('SELECT VERSION() AS version');
$version = $this->dbs->single();

$charset = 'utf8';
if (version_compare($version['version'], "5.5.3") >= 0) {
    $charset .= 'mb4';

    $this->dbs->query("SHOW tables");
    $data = $this->dbs->resultset();

    foreach ($data as $table) {
        $table = $table['Tables_in_'.DB_NAME];

        $this->dbs->query("ALTER TABLE `".$table."` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->dbs->execute();

        $this->dbs->query("REPAIR TABLE ".$table);
        $this->dbs->execute();

        $this->dbs->query("OPTIMIZE TABLE ".$table);
        $this->dbs->execute();
    }
}

$web_cfg = "<?php
/**
 * config.php
 *
 * This file contains all of the configuration for the db
 * that will
 * @author SteamFriends Development Team
 * @version 1.0.0
 * @copyright SteamFriends (www.SteamFriends.com)
 * @package SourceBans
 */
if(!defined('IN_SB')){echo 'You should not be here. Only follow links!';die();}

define('DB_HOST', '{server}');                       // The host/ip to your SQL server
define('DB_USER', '{user}');                        // The username to connect with
define('DB_PASS', '{pass}');                        // The password
define('DB_NAME', '{db}');                          // Database name
define('DB_PREFIX', '{prefix}');                    // The table prefix for SourceBans
define('DB_PORT', '{port}');                            // The SQL port (Default: 3306)
define('DB_CHARSET', '{charset}');                    // The Database charset (Default: utf8)
define('STEAMAPIKEY', '{steamapikey}');                // Steam API Key for Shizz
define('SB_WP_URL', '{sbwpurl}');                       //URL of SourceBans Site
define('SB_EMAIL', '{sbwpemail}');

//define('DEVELOPER_MODE', true);            // Use if you want to show debugmessages
//define('SB_MEM', '128M');                 // Override php memory limit, if isn't enough (Banlist is just a blank page)
?>";

$web_cfg = str_replace("{server}", DB_HOST, $web_cfg);
$web_cfg = str_replace("{user}", DB_USER, $web_cfg);
$web_cfg = str_replace("{pass}", DB_PASS, $web_cfg);
$web_cfg = str_replace("{db}", DB_NAME, $web_cfg);
$web_cfg = str_replace("{prefix}", DB_PREFIX, $web_cfg);
$web_cfg = str_replace("{port}", DB_PORT, $web_cfg);
$web_cfg = str_replace("{charset}", $charset, $web_cfg);
$web_cfg = str_replace("{steamapikey}", STEAMAPIKEY, $web_cfg);
$web_cfg = str_replace("{sbwpurl}", SB_WP_URL, $web_cfg);
$web_cfg = str_replace("{sbwpemail}", SB_EMAIL, $web_cfg);

$config = fopen("../config.php", "w");
fwrite($config, $web_cfg);
fclose($config);

return true;
