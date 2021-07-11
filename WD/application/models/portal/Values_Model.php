<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Values_Model extends Base_Model
{

    public $table;

    public function __construct()
    {
        parent::__construct();
        
        $this->table = explode("_", strtolower(__CLASS__))[0];
    }
}