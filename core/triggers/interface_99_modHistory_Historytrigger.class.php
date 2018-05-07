<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		core/triggers/interface_99_modMyodule_Historytrigger.class.php
 * 	\ingroup	history
 * 	\brief		Sample trigger
 * 	\remarks	You can create other triggers by copying this one
 * 				- File name should be either:
 * 					interface_99_modMymodule_Mytrigger.class.php
 * 					interface_99_all_Mytrigger.class.php
 * 				- The file must stay in core/triggers
 * 				- The class name must be InterfaceMytrigger
 * 				- The constructor method must be named InterfaceMytrigger
 * 				- The name property name must be Mytrigger
 */

/**
 * Trigger class
 */
class InterfaceHistorytrigger
{

    private $db;

    /**
     * Constructor
     *
     * 	@param		DoliDB		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "Triggers of this module are empty functions."
            . "They have no effect."
            . "They are provided for tutorial purpose only.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'development';
        $this->picto = 'history@history';
    }

    /**
     * Trigger name
     *
     * 	@return		string	Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * 	@return		string	Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Trigger version
     *
     * 	@return		string	Version of trigger file
     */
    public function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') {
            return $langs->trans("Development");
        } elseif ($this->version == 'experimental')

                return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else {
            return $langs->trans("Unknown");
        }
    }

    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file
     * is inside directory core/triggers
     *
     * 	@param		string		$action		Event action code
     * 	@param		Object		$object		Object
     * 	@param		User		$user		Object user
     * 	@param		Translate	$langs		Object langs
     * 	@param		conf		$conf		Object conf
     * 	@return		int						<0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function run_trigger($action, $object, $user, $langs, $conf)
    {
        // Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action
        // Users
		
       $db = &$object->db;
       if(is_null($db)) {
           $db = &$this->db;
       }
       if(!empty($object->element)) {
           
            if(!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR',true);
            if(!dol_include_once('/history/config.php')) return 0;
            
            $h=new DeepHistory($db);
            
            $type_object = $object->element;
            if(substr($type_object,-3) == 'det'){
                $type_object = substr($type_object,0,-3);
                if(!empty($object->{'fk_'.$type_object})) $h->fk_object = $object->{'fk_'.$type_object}; // TODO ça marche pas, pas rempli quand update line :/
            }
			
			if(!empty($conf->global->HISTORY_STOCK_FULL_OBJECT_ON_DELETE) && strpos($action,'DELETE')!==false) {
				$h->object = clone $object;
				$h->table_element = $object->table_element;
				$h->fk_object_deleted = $object->id;
			}

	        if(empty($h->fk_object)) $h->fk_object = $object->id;

			global $history_old_object;

    	    if(!empty($object->oldline)) $h->compare($object, $object->oldline);
            else if(!empty($object->oldcopy)) $h->compare($object, $object->oldcopy);
			else if(!empty($history_old_object) && get_class( $history_old_object ) == get_class( $object) ) $h->compare($object, $history_old_object);
            else {
                
                $h->what_changed = 'cf. action';
           
            }
			if($action == 'CATEGORY_LINK' || $action == 'CATEGORY_UNLINK'){
				$langs->load('history@history');
				$objsrc = $object;
				
				if($action == 'CATEGORY_LINK')$object = $object->linkto;
				if($action == 'CATEGORY_UNLINK')$object = $object->unlinkoff;
				
				$h->fk_object = $object->id;
				
				$objsrc->fetch($objsrc->id);
				$type_object= $object->element;
				
				if($action == 'CATEGORY_LINK')$h->what_changed = $langs->transnoentitiesnoconv('CategLinked')." ==> $objsrc->label";
				if($action == 'CATEGORY_UNLINK')$h->what_changed = $langs->transnoentitiesnoconv('CategUnlinked')." ==> $objsrc->label";
				
			}
			if($action == 'COMPANY_LINK_SALE_REPRESENTATIVE' || $action == 'COMPANY_UNLINK_SALE_REPRESENTATIVE'){
				$langs->load('history@history');
			
				$h->fk_object = $object->id;
				$type_object= $object->element;
				$usrtarget = new User($db);
				$usrtarget->fetch($object->context['commercial_modified']);
				$label = $usrtarget->lastname.' '.$usrtarget->firstname;
				if($action == 'COMPANY_LINK_SALE_REPRESENTATIVE')$h->what_changed = $langs->transnoentitiesnoconv('COMPANY_LINK_SALE_REPRESENTATIVE')." ==> $label";
				if($action == 'COMPANY_UNLINK_SALE_REPRESENTATIVE')$h->what_changed = $langs->transnoentitiesnoconv('COMPANY_UNLINK_SALE_REPRESENTATIVE')." ==> $label";
				
			}
			$h->setRef($object);
			
            $h->type_action = $action;
            $h->fk_user = $user->id;
            $h->type_object = $type_object;
			
			if(!empty($h->what_changed))$res = $h->create($user);
			
			if($res<=0) {
				//var_dump($h);exit;
			}
			
               
       }else{
       	switch ($action){
			case 'STOCK_MOVEMENT':
				
	            if(!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR',true);
	            dol_include_once('/history/config.php');
	            
	            $h=new DeepHistory($db);
	            $produit = new Product($db);
				$produit->fetch($object->product_id);
			
				$h->setRef($produit);
				
	            $h->type_action = $action;
	            $h->fk_user = $user->id;
	            $h->type_object = 'product';
            	$h->fk_object = $produit->id;
				$h->what_changed = 'pmp => '.$produit->pmp."\n".'qty_movement => '.$object->qty;
				$h->key_value1 = $produit->pmp;
				
				$h->create($user);
				
				break;
       	}
       }
       
        return 0;
    }
}
