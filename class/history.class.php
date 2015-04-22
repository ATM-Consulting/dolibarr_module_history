<?php

class THistory extends TObjetStd {
/*
 * Gestion des équipements 
 * */
    
    function __construct() {
        $this->set_table(MAIN_DB_PREFIX.'history');
        $this->add_champs('fk_object','type=entier;index;');
        $this->add_champs('fk_user', 'type=entier;');
        $this->add_champs('type_object,type_action', 'type=chaine;index;');
        $this->add_champs('date_entry','type=date;');
        
        $this->_init_vars('what_changed');
        
        $this->start();
    }

    function compare(&$newO, &$oldO) {
    	$this->what_changed = '';
    
    	foreach($newO as $k=>$v) {
    
    		if(!is_array($v) && !is_object($v)) {
    			if(isset($oldO->{$k}) && !empty($v) && $oldO->{$k} != $v) {
    				$this->what_changed.=$k.' : '.$oldO->{$k}.' => '.$v."\n";
    			}
    
    		}
    
    	}
//exit($this->what_changed.'!');
    }

    function show_whatChanged() {
	
	return nl2br(htmlentities($this->what_changed));
	
    }
    
    function show_action() {
        global $langs;
        $action='';
        
        $action = $langs->trans($this->type_action);
//var_dump($this);        
        return $action;
    }
    
    function show_user() {
        global $db;
        
        $u=new User($db);
        $u->fetch($this->fk_user);
        
        return $u->getLoginUrl(1);
        
    }
    
    function save(&$PDOdb) {
        
        if(empty($this->fk_user) || empty($this->fk_object) || empty($this->type_action) || empty($this->what_changed)) return false;
        
        return parent::save($PDOdb);
    }
    
    static function getHistory(&$PDOdb, $type_object, $fk_object) {
        
        $sql="SELECT rowid FROM ".MAIN_DB_PREFIX."history
         WHERE type_object='".$type_object."' AND fk_object=".(int)$fk_object." 
         ORDER BY date_entry DESC ";
        
        $Tab = $PDOdb->ExecuteAsArray($sql);
        
        $TRes=array();
        foreach($Tab as $row){
            
            $h=new THistory;
            $h->load($PDOdb, $row->rowid);
            
            $TRes[] = $h;
            
        }
        
        return $TRes;
        
    }
    
}
