<?php

$dbconfig['dev'] = array('127.0.0.1', 'qb', 'root', '', 'mysql', true);
$dbconfig['prod'] = array(realpath('.') . '/db/entries.db', '', '', '', 'sqlite', true);

?>