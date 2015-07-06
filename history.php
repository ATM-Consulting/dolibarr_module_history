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
    
    llxHeader('',$langs->trans('History'));
    
    $type_object = GETPOST('type_object');
    $fk_object = GETPOST('id');
    
    if($type_object == 'propal') {
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
    
    
    $PDOdb=new TPDOdb;
    
    $THistory = THistory::getHistory($PDOdb, $type_object, $fk_object)  ;
    
    ?>
    <table class="border" width="100%">
        <tr class="liste_titre">
            <th><?php echo $langs->trans('Date') ?></th>
            <th><?php echo $langs->trans('Action') ?></th>
            <th><?php echo $langs->trans('WhatChanged') ?></th>
            <th><?php echo $langs->trans('User') ?></th>
        </tr>
        
    <?
    
    foreach($THistory as $h) {
        
        ?>
        <tr class="<?php $class=($class=='impair')?'pair':'impair'; echo $class; ?>">
            <td><?php echo $h->get_date('date_entry','d/m/Y H:i:s'); ?></td>
            <td><?php echo $h->show_action() ?></td>
            <td><?php echo $h->show_whatChanged() ?></td>
            <td><?php echo $h->show_user() ?></td>
        </tr>
        <?
        
    }
    
    ?>
    
    </table>
    </div>
    <?php
    
    llxFooter();
