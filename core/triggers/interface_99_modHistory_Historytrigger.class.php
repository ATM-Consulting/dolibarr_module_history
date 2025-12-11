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

require_once __DIR__ .'/../../class/history.class.php';
/**
 * Trigger class
 */
class InterfaceHistorytrigger extends DolibarrTriggers
{

	protected $db;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "Triggers of this module are empty functions. They have no effect.They are provided for tutorial purpose only.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'history@history.png';
	}

	/**
	 * Return name of trigger file
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Return description of trigger file
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}

	/**
	 * Return version of trigger file
	 *
	 * @return string Version of trigger file
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
	 * @param   string      $action     Event action code
	 * @param   Object      $object     Object
	 * @param   User        $user       Object user
	 * @param   Translate   $langs      Object langs
	 * @param   Conf        $conf       Object conf
	 * @return  int                     <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, $user, $langs, $conf)
	{
		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action
		// Users

		$langs->load("history@history");

		$db = &$object->db;

		if (is_null($db)) {
			$db = &$this->db;
		}
		if (!empty($object->element)) {
			if (!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR', true);
			if (!dol_include_once('history/config.php')) return 0;

			$type_object = $object->element;

			$noObjects = explode(',', getDolGlobalString('HISTORY_NO_OBJECT_LIST'));
			if (in_array($type_object, $noObjects)) return 0;

			$deepHistory = new DeepHistory($db);

			if (substr($type_object, -3) == 'det') {
				$type_object = substr($type_object, 0, -3);
				if ( !empty($object->{ 'fk_'.$type_object })) $deepHistory->fk_object = $object->{ 'fk_'.$type_object }; // TODO ça marche pas, pas rempli quand update line :/
			}


			if ( empty($deepHistory->fk_object) ) $deepHistory->fk_object = $object->id;

			global $history_old_object;

			if ( !empty($object->oldline) ) $deepHistory->compare($object, $object->oldline);
			elseif ( !empty($object->oldcopy) ) $deepHistory->compare($object, $object->oldcopy);
			elseif ( !empty($history_old_object) && get_class($history_old_object) == get_class($object) ) $deepHistory->compare($object, $history_old_object);
			else {
				$deepHistory->what_changed = $langs->trans("NOTHING_FOUND");
			}

			if (getDolGlobalString('HISTORY_STOCK_FULL_OBJECT_ON_DELETE') && strpos($action, 'DELETE')!==false) {
				//TODO Faire en sorte que ça marche, cette feature n'a jamais été dev complement il y a pas mal de choses à faire pour que ça fonctionne
				$deepHistory->table_object = $object->table_element;
				$deepHistory->fk_object_deleted = $object->id;
				if (empty($deepHistory->what_changed)) $deepHistory->what_changed = $langs->trans("NOTHING_FOUNDS");
			}

			if ($action == 'CATEGORY_LINK' || $action == 'CATEGORY_UNLINK' || $action == 'CATEGORY_MODIFY') {
				$langs->load('history@history');

				if ($action == 'CATEGORY_LINK') {
					$objectToLink = $object->linkto;
				} elseif ($action == 'CATEGORY_MODIFY' && is_object($object->context['linkto'])) {
					$objectToLink = $object->context['linkto'];
				}
				if ($action == 'CATEGORY_UNLINK') {
					$objectToLink = $object->unlinkoff;
				} elseif ($action == 'CATEGORY_MODIFY' && isset($object->context['unlinkoff']) && is_object($object->context['unlinkoff'])) {
					$objectToLink = $object->context['unlinkoff'];
				}

				$deepHistory->fk_object = $objectToLink->id;

				$object->fetch($object->id);
				$type_object = $objectToLink->element;

				if ($action == 'CATEGORY_LINK' || $action == 'CATEGORY_MODIFY' && is_object($object->context['linkto'])) {
					$deepHistory->what_changed = $langs->transnoentitiesnoconv('CategLinked')." ==> $object->label";
				}
				if ($action == 'CATEGORY_UNLINK' || $action == 'CATEGORY_MODIFY' && isset($object->context['unlinkoff']) && is_object($object->context['unlinkoff'])) {
					$deepHistory->what_changed = $langs->transnoentitiesnoconv('CategUnlinked')." ==> $object->label";
				}
			}
			if ($action == 'COMPANY_LINK_SALE_REPRESENTATIVE' || $action == 'COMPANY_UNLINK_SALE_REPRESENTATIVE') {
				$langs->load('history@history');

				$deepHistory->fk_object = $object->id;
				$type_object= $object->element;
				$usrtarget = new User($db);
				$usrtarget->fetch($object->context['commercial_modified']);
				$label = $usrtarget->lastname.' '.$usrtarget->firstname;
				if ($action == 'COMPANY_LINK_SALE_REPRESENTATIVE')$deepHistory->what_changed = $langs->transnoentitiesnoconv('COMPANY_LINK_SALE_REPRESENTATIVE')." ==> $label";
				if ($action == 'COMPANY_UNLINK_SALE_REPRESENTATIVE')$deepHistory->what_changed = $langs->transnoentitiesnoconv('COMPANY_UNLINK_SALE_REPRESENTATIVE')." ==> $label";
			}
			$deepHistory->setRef($object);

			$deepHistory->type_action = $action;
			$deepHistory->fk_user = $user->id;
			$deepHistory->type_object = $type_object;

			if (!empty($deepHistory->what_changed)) {
				$res = $deepHistory->create($user);
			}
		} else {
			switch ($action) {
				case 'STOCK_MOVEMENT':

					if (!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR', true);
					dol_include_once('/history/config.php');

					$deepHistory=new DeepHistory($db);
					$produit = new Product($db);
					$produit->fetch($object->product_id);

					$deepHistory->setRef($produit);

					$deepHistory->type_action = $action;
					$deepHistory->fk_user = $user->id;
					$deepHistory->type_object = 'product';
					$deepHistory->fk_object = $produit->id;
					$deepHistory->what_changed = 'pmp => '.$produit->pmp."\n".'qty_movement => '.$object->qty;
					$deepHistory->key_value1 = $produit->pmp;

					$deepHistory->create($user);

					  break;
			}
		}

		return 0;
	}
}
