<?php 

$this->dbs->query("ALTER TABLE `:prefix_admins` ADD COLUMN `attempts` INT DEFAULT 0");
$this->dbs->execute();

$this->dbs->query("ALTER TABLE `:prefix_admins` ADD COLUMN `lockout_until` DATETIME DEFAULT NULL");
$this->dbs->execute();

return true;