<?php

    require('config.php');

    dol_include_once('/core/lib/functions2.lib.php');
    dol_include_once('/comm/propal/class/propal.class.php');
    dol_include_once('/core/lib/propal.lib.php');
    dol_include_once('/core/lib/contact.lib.php');
    dol_include_once('/core/lib/agenda.lib.php');
    dol_include_once('/comm/action/class/actioncomm.class.php');
    dol_include_once('/core/lib/product.lib.php');
    dol_include_once('/core/lib/company.lib.php');
    dol_include_once('/core/lib/project.lib.php');
    dol_include_once('/projet/class/project.class.php');
    dol_include_once('/projet/class/task.class.php');
    dol_include_once('/projet/class/task.class.php');
   	dol_include_once('/fourn/class/fournisseur.commande.class.php');
    dol_include_once('/fourn/class/fournisseur.facture.class.php');
	dol_include_once('/fourn/class/fournisseur.product.class.php');

	if(DOL_VERSION>5) {
		dol_include_once('/commande/class/commande.class.php');
		dol_include_once('/core/lib/order.lib.php');
	}
	
    llxHeader('',$langs->trans('HideletedElementstory'));

    $type_object = GETPOST('type_object');
    $fk_object = GETPOST('id');

	if($type_object == 'deletedElement') {
		dol_include_once('/history/lib/history.lib.php');
		$head = historyAdminPrepareHead($object);
        dol_fiche_head($head, 'delted',$langs->trans("ModuleName"),  0,  "history@history");

	}
	else if($type_object == 'propal') {
        $object = new Propal($db);
        $object->fetch($fk_object);
        $head = propal_prepare_head($object);
        dol_fiche_head($head, 'history', $langs->trans('Proposal'), 0, 'propal');
    }
    else if($type_object=='societe') {
        $object = new Societe($db);
        $object->fetch($fk_object);
        $head = societe_prepare_head($object);
        dol_fiche_head($head, 'history', $langs->trans('Company'), 0, 'company');

    }

    else if($type_object=='action') {
        $object = new ActionComm($db);
        $object->fetch($fk_object);
        $head = actions_prepare_head($object);
        dol_fiche_head($head, 'history', $langs->trans('Company'), 0, 'action');

    }

    else if($type_object=='project') {
        $object = new Project($db);
        $object->fetch($fk_object);
        $head = project_prepare_head($object);
        dol_fiche_head($head, 'history', $langs->trans('Project'), 0, 'action');

    }

    /*else if($type_object=='order') {
     //TODO : for dolibarr 5.0 order class will manage correctly change so can be uncomment
    	$object = new Commande($db);
    	$object->fetch($fk_object);
    	$head = commande_prepare_head($object);
    	dol_fiche_head($head, 'history', $langs->trans('CustomerOrder'), 0, 'action');

    }*/

    else if( class_exists(ucfirst($type_object)) ) {
        $class = ucfirst($type_object);

        $object=new $class($db);
        $object->fetch($fk_object);

        if(function_exists($type_object.'_prepare_head')) {
            $head = call_user_func($type_object.'_prepare_head', $object, $user);
            dol_fiche_head($head, 'history', $langs->trans($class), 0, $type_object);
        }

    }
    else{
        exit('Erreur, ce type d\'objet '.ucfirst($type_object).' n\'est pas traité par le module');

    }

    $THistory = DeepHistory::getHistory($type_object, $fk_object)  ;

    if(GETPOST('restoreObject')>0) {

		DeepHistory::restoreCopy(GETPOST('restoreObject'));

    }

	?>
    <table class="border" width="100%">
        <tr class="liste_titre">
            <td class="liste_titre"><?php echo $langs->trans('Date') ?></td><?php
            if($type_object == 'deletedElement') {
            	echo '<td class="liste_titre">'.$langs->trans('Ref').'</td>';
			}
            ?><td class="liste_titre"><?php echo $langs->trans('Action') ?></td>
            <td class="liste_titre"><?php echo $langs->trans('WhatChanged') ?></td>
            <td class="liste_titre"><?php echo $langs->trans('User') ?></td>
        </tr>

    <?php

    $class = 'pair';
    foreach($THistory as &$history) {

		if($type_object == 'deletedElement') {
			        ?>
			        <tr class="<?php $class=($class=='impair')?'pair':'impair'; echo $class; ?>">
			            <td><?php echo $history->get_date('date_entry','dayhoursec'); ?></td>
			            <td><?php echo $history->show_ref() ?></td>
			            <td><?php echo $history->show_action() ?></td>
			            <td><?php echo html_entity_decode($history->show_whatChanged($PDOdb, false, true)) ?></td>
			            <td><?php echo $history->show_user() ?></td>
			        </tr>
					<?php

		}
		else {
	        ?>
	        <tr class="<?php $class=($class=='impair')?'pair':'impair'; echo $class; ?>">
	            <td><?php echo $history->get_date('date_entry','dayhoursec'); ?></td>
	            <td><?php echo $history->show_action() ?></td>
	            <td><?php echo html_entity_decode($history->show_whatChanged($PDOdb)) ?></td>
	            <td><?php echo $history->show_user() ?></td>
	        </tr>
	        <?php

	        if(!empty($history->object) && GETPOST('showObject') == $history->getId()) {
	        	unset($history->object->db);
				echo '<tr><td colspan="4"><pre>'.print_r($history->object,true).'</pre></td></tr>';

	        }

		}

    }

    ?>

    </table>
    </div>
    <?php

    llxFooter();
