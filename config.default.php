<?php
	
	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else $dir = '../../';


	if(!defined('INC_FROM_DOLIBARR') && defined('INC_FROM_CRON_SCRIPT')) {
		include($dir."master.inc.php");
	}
	elseif(!defined('INC_FROM_DOLIBARR')) {
		include($dir."main.inc.php");
	} else {
		global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
	}
	
	if(!dol_include_once('/abricot/inc.core.php')) {
		print $langs->trans('AbricotNotFound'). ' : <a href="http://wiki.atm-consulting.fr/index.php/Accueil#Abricot" target="_blank">Abricot</a>';
		exit;
	}

	dol_include_once('/history/class/history.class.php');

