<?php

class DeepHistory extends SeedObject {
/*
 * Gestion des équipements
 * */

	public $table_element = 'history';
	
	function __construct($db) {
    	
    	$this->db = $db;
    	
    	$this->fields=array(
        		'fk_object'=>array('type'=>'integer','index'=>true)
        		,'fk_object_deleted'=>array('type'=>'integer','index'=>true)
        		,'key_value1'=>array('type'=>'float','index'=>true)
        		,'fk_user'=>array('type'=>'integer')
        		,'type_object'=>array('type'=>'string','length'=>50,'index'=>true)
        		,'type_action'=>array('type'=>'string','length'=>50,'index'=>true)
        		,'ref'=>array('type'=>'string','length'=>50,'index'=>true)
        		,'table_object'=>array('type'=>'string','length'=>50,'index'=>true)
        		,'object'=>array('type'=>'array')
        		,'date_entry'=>array('type'=>'date')
        		,'what_changed'=>array('type'=>'text')
        );
        		
        $this->init();
        
        $this->key_value1 = 0;

	}

	function show_ref() {
		global $db,$user,$conf,$langs;

		dol_include_once('/'.$this->type_object.'/class/'.$this->type_object.'.class.php');

		$class = ucfirst($this->type_object);

		if($class=='Project_task') $class='Task';
		else if($class=='Order_supplier') $class='CommandeFournisseur';
		else if($class=='Invoice_supplier') $class='FactureFournisseur';

		if(!class_exists($class )) return $langs->trans('CantInstanciate').' : '.	$class;
        $object=new $class($db);

        $res = $object->fetch($this->fk_object);

		if($res<=0 || $object->id == 0) {
			return $langs->trans('WholeObjectDeleted');
		}

        if(method_exists($object, 'getNomUrl')) {
            return $object->getNomUrl(1);
        }

	}

	function setRef(&$object) {

		if(!empty($object->code_client)) $this->ref = $object->code_client;
		else if(!empty($object->facnumber)) $this->ref = $object->facnumber;
		else if(!empty($object->ref)) $this->ref = $object->ref;

	}
    function compare(&$newO, &$oldO)
    {
    	$this->what_changed = '';
        $this->what_changed .= $this->cmp($newO, $oldO);
    	$this->what_changed .= $this->cmp($newO->array_options, $oldO->array_options, true);
    }

    private function cmp(&$newO, &$oldO, $checkArrayOptions = false)
    {
        if(empty($newO) || empty($oldO)) return '';

        $diff = '';

        foreach($newO as $k=>$v)
        {
            if(!is_array($v) && !is_object($v))
            {

				if ($checkArrayOptions)
				{
					if($oldO[$k] !== $v && (!empty($v) || (!empty($oldO[$k]) &&  $oldO[$k] !== '0.000') ) )
	            	{
	            		// substr remove options_
	                    $diff.=substr($k, 8).' : '.$oldO[$k].' => '.$v."\n";
	                }
				}
				else
				{
					//isset($oldO->{$k}) => renvoi false sur $oldO->zip car défini à null
	                if(property_exists($oldO, $k) // vérifie que l'attribut exist
	                	&& !is_object($oldO->{$k})
	                	&& $oldO->{$k} !== $v
	                	&& (!empty($v) || (!empty($oldO->{$k}) &&  $oldO->{$k} !== '0.000' )   )
						)
	            	{
	                    $diff.=$k.' : '.$oldO->{$k}.' => '.$v."\n";
	                }
				}

            }

        }

        return $diff;
    }

    function show_whatChanged($show_details = true, $show_restore = true) {
	global $conf,$user;

		$r = nl2br(htmlentities($this->what_changed));

		if(!empty($conf->global->HISTORY_STOCK_FULL_OBJECT_ON_DELETE)) {
			if($show_details && !empty($this->object)) $r.=' <a href="?type_object='.$this->type_object.'&id='.$this->fk_object.'&showObject='.$this->getId().'">'.img_view().'</a>';

			if($show_restore && !empty($user->rights->history->restore)) {
				$res = $this->db->query("SELECT * FROM ".MAIN_DB_PREFIX.$this->table_element.'_deletedhistory');
				if($obj=$this->db->fetch_object($res)) {
					$r.=' <a href="?type_object='.$this->type_object.'&id='.$this->fk_object.'&restoreObject='.$this->getId().'">'.img_picto('Restore', 'refresh').'</a>';
				}

			}

		}


		return $r;

    }

    function show_action() {
        global $langs;
        $action='';

        $action = $langs->trans($this->type_action);

        return $action;
    }

    function show_user() {
        
        $u=new User($this->db);
        $u->fetch($this->fk_user);

        return $u->getLoginUrl(1);

    }

    function save(&$user) {
    	
        if(empty($this->fk_user) || empty($this->fk_object) || empty($this->type_action) || empty($this->what_changed)) return false;

        return $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
    }

    static function getHistory($type_object, $fk_object) {

    	global $db;
    	
        if($type_object == 'task') $type_object = 'project_task';

		if($type_object=='deletedElement') {
			$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."history
	         WHERE type_action LIKE '%DELETE%'
	         ORDER BY date_entry DESC";

		}
		else{
			$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."history
	         WHERE type_object='".$type_object."' AND fk_object=".(int)$fk_object."
	         ORDER BY date_entry DESC ";
		}

		$res = $db->query($sql);

		$TRes=array();
		while($obj = $db->fetch_object($res)) {
			
			$h=new DeepHistory($db);
			if($h->fetch($obj->rowid)>0) {
			
				$TRes[] = $h;
			
			}
			else{
				var_dump($h);exit;
			}
		}
		
        return $TRes;

    }
    static function addHistory(&$user, $type_object, $fk_object, $action, $what_changed = 'cf. action') {
		global $db;
    	
            $h=new DeepHistory($db);
            $h->fk_object = $fk_object;
            $h->what_changed = $what_changed;
            $h->type_action = $action;
            $h->fk_user = $user->id;
            $h->type_object = $type_object;
            $h->create($user);
    }

	static function restoreCopy($id_to_restore) {
		global $user,$db,$langs;
		
		$h=new DeepHistory($db);
		if($h->fetch($id_to_restore )){
			
			$table = $h->table_object;
			$backup_table = $table.'_deletedhistory';

			$obj = new SeedObject($db);
			$obj->table_object= $backup_table;
			$obj->init_vars_by_db();
			$obj->fetch( $h->fk_object_deleted );

			$obj2 = clone $obj;

			$db->query("set foreign_key_checks = 0");

			$obj2->table_object= $table;
			$obj2->init_db_by_vars();
			$obj2->date_creation = $obj2->tms = time();
			
			$obj2->replaceCommon($user);

			setEventMessage($langs->trans("DeletedObjectRestored"));
		}

	}
	
	public function replaceCommon(User $user, $notrigger = false)
	{
		global $langs;
		
		$error = 0;
		
		$now=dol_now();
		
		$fieldvalues = $this->set_save_query();
		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation']=$this->db->idate($now);
		if (array_key_exists('fk_user_creat', $fieldvalues) && ! ($fieldvalues['fk_user_creat'] > 0)) $fieldvalues['fk_user_creat']=$user->id;
		unset($fieldvalues['rowid']);	// The field 'rowid' is reserved field name for autoincrement field so we don't need it into insert.
		
		$keys=array();
		$values = array();
		foreach ($fieldvalues as $k => $v) {
			$keys[$k] = $k;
			$value = $this->fields[$k];
			$values[$k] = $this->quote($v, $value);
		}
		
		// Clean and check mandatory
		foreach($keys as $key)
		{
			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && $values[$key] == '-1') $values[$key]='';
			if (! empty($this->fields[$key]['foreignkey']) && $values[$key] == '-1') $values[$key]='';
			
			//var_dump($key.'-'.$values[$key].'-'.($this->fields[$key]['notnull'] == 1));
			if ($this->fields[$key]['notnull'] == 1 && empty($values[$key]))
			{
				$error++;
				$this->errors[]=$langs->trans("ErrorFieldRequired", $this->fields[$key]['label']);
			}
			
			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && empty($values[$key])) $values[$key]='null';
			if (! empty($this->fields[$key]['foreignkey']) && empty($values[$key])) $values[$key]='null';
		}
		
		if ($error) return -1;
		
		$this->db->begin();
		
		if (! $error)
		{
			$sql = 'REPLACE INTO '.MAIN_DB_PREFIX.$this->table_element;
			$sql.= ' ('.implode( ", ", $keys ).')';
			$sql.= ' VALUES ('.implode( ", ", $values ).')';
			
			$res = $this->db->query($sql);
			if ($res===false) {
				$error++;
				$this->errors[] = $this->db->lasterror();
			}
		}
		
		if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);
		}
		
		if (! $error)
		{
			$result=$this->insertExtraFields();
			if ($result < 0) $error++;
		}
		
		if (! $error && ! $notrigger)
		{
			// Call triggers
			$result=$this->call_trigger(strtoupper(get_class($this)).'_CREATE',$user);
			if ($result < 0) { $error++; }
			// End call triggers
		}
		
		// Commit or rollback
		if ($error) {
			$this->db->rollback();
			return -1;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}
	

	static function makeCopy(&$object) {

		if(is_object($object) && !empty($object->table_element)){

			$table = $h->table_object;
			$backup_table = $table.'_deletedhistory';
			
			$obj = new SeedObject($db);
			$obj->table_object= $table;
			$obj->init_vars_by_db();
			$obj->fetch( $object->id );
			
			$obj2 = clone $obj;
			
			$db->query("set foreign_key_checks = 0");
			
			$obj2->table_object= $backup_table;
			$obj2->init_db_by_vars();
			$obj2->date_creation = $obj2->tms = time();
			
			$obj2->replaceCommon($user);

		}

		foreach($object as $k=>$v) {

			if(is_object($v) || is_array($v)) {
				self::makeCopy($v);
			}

		}


	}

}
