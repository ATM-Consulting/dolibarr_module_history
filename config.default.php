<?php
	
	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else $dir = '../../';


	if(!defined('INC_FROM_DOLIBARR') && defined('INC_FROM_CRON_SCRIPT')) {
		include_once($dir."master.inc.php");
	}
	elseif(!defined('INC_FROM_DOLIBARR')) {
		include_once($dir."main.inc.php");
	} else {
		global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass, $dolibarr_main_db_type;
	}
	if(!defined('DB_HOST') && !empty($dolibarr_main_db_host)) {
		if(! defined('DB_HOST')) define('DB_HOST',$dolibarr_main_db_host);
		if(! defined('DB_NAME')) define('DB_NAME',$dolibarr_main_db_name);
		if(! defined('DB_USER')) define('DB_USER',$dolibarr_main_db_user);
		if(! defined('DB_PASS')) define('DB_PASS',$dolibarr_main_db_pass);
		if(! defined('DB_DRIVER')) define('DB_DRIVER',$dolibarr_main_db_type);
	}

if(!dol_include_once('abricot/inc.core.php')) {
		print $langs->trans('AbricotNotFound'). ' : <a href="http://wiki.atm-consulting.fr/index.php/Accueil#Abricot" target="_blank">Abricot</a>';
		exit;
	}

	dol_include_once('history/class/history.class.php');

