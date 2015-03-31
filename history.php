<?php 

    require('config.php');
    
    dol_include_once('/core/lib/functions2.lib.php');
    dol_include_once('/comm/propal/class/propal.class.php');
    dol_include_once('/core/lib/propal.lib.php');
    
    llxHeader('',$langs->trans('History'));
    
    $type_object = GETPOST('type_object');
    $fk_object = GETPOST('id');
    
    if($type_object == 'propal') {
        $object = new Propal($db);
        $object->fetch($fk_object);
        $head = propal_prepare_head($object);
        dol_fiche_head($head, 'history', $langs->trans('Proposal'), 0, 'propal');
     }
    
    $PDOdb=new TPDOdb;
    
    $THistory = THistory::getHistory($PDOdb, $type_object, $fk_object)  ;
    
    ?>
    <table class="border" width="100%">
        <tr class="liste_titre">
            <th><?php echo $langs->trans('Date') ?></th>
            <th><?php echo $langs->trans('Action') ?></th>
            <th><?php echo $langs->trans('user') ?></th>
        </tr>
        
    <?
    
    foreach($THistory as $h) {
        
        ?>
        <tr class="<?php $class=($class=='impair')?'pair':'impair'; echo $class; ?>">
            <td><?php echo $h->get_date('date_entry'); ?></td>
            <td><?php echo $h->show_action() ?></td>
            <td><?php echo $h->show_user() ?></td>
        </tr>
        <?
        
    }
    
    ?>
    
    </table>
    </div>
    <?php
    
    llxFooter();