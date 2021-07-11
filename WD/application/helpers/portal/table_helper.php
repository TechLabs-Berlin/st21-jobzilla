<?php
/*
 * @author boo2mark
 *
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class BTable extends BObject
{
    
    // load the user data into member fields
    public function __construct($primary_key)
    {
        parent::__construct();
        
        $CI = get_instance();
        
        $table_name = $CI->config->item('prefix') . $this->_table;
        
        $user = $CI->db->get_where("{$table_name}", array(
            "{$this->_primary_key}" => "{$primary_key}"
        ))->row_array();
        
        if ($user) {
            
            $fields = array_keys($user);
            
            foreach ($fields as $field) {
                
                if (! in_array($field, $this->_ignore_fields))
                    $this->{$field} = $user[$field];
            }
        }
    }
}