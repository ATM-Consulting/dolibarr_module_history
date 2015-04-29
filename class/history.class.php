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
        $this->what_changed .= $this->cmp($newO, $oldO);
    	$this->what_changed .= $this->cmp($newO->optionsarray_options, $oldO->array_options);
    }
    
    private function cmp(&$newO, &$oldO) {
    	
        if(empty($newO) || empty($oldO)) return '';
        
        $diff = '';
     
        foreach($newO as $k=>$v) {
    		
            if(!is_array($v) && !is_object($v)) {
			
				//isset($oldO->{$k}) => renvoi false sur $oldO->zip car défini à null              
                if(property_exists($oldO, $k) // vérifie que l'attribut exist    
                	&& $v !== '' // TODO Si new val n'est pas vide mais c'est faux, la nouvelle valeur peut être vide 
                	&& $oldO->{$k} !== $v 
                	&& empty($oldO->{$k}) !== empty($v)) //ce test permet d'éviter d'enregistrer un changement si $oldO->effectif_id == null et que $v == 0
            	{
                    $diff.=$k.' : '.$oldO->{$k}.' => '.$v.PHP_EOL;
                }
    
            }
    
        }
     
        return $diff;   
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
    static function addHistory(&$PDOdb, &$user, $type_object, $fk_object, $action, $what_changed = 'cf. action') {
        
            $h=new THistory;
            $h->fk_object = $fk_object;
            $h->what_changed = $what_changed;
            $h->type_action = $action;
            $h->fk_user = $user->id;
            $h->type_object = $type_object;
            $h->save($PDOdb);
    }
}
