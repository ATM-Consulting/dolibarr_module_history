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
    
    function show_action() {
        
        $action='';
        
        $action = $this->type_action;
        
        return $action;
    }
    
    function show_user() {
        global $db;
        
        $u=new User($db);
        $u->fetch($this->fk_user);
        
        return $u->getLoginUrl(1);
        
    }
    
    function save(&$PDOdb) {
        
        if(empty($this->fk_user) || empty($this->fk_object) || empty($this->type_action)) return false;
        
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
            $h->load($PDOdb, $h->rowid);
            
            $TRes[] = $h;
            
        }
        
        return $TRes;
        
    }
    
}