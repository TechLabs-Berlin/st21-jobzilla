<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Menu_model extends CI_Model
{

    var $_table_folder = 'tables';

    var $_extension = '.xml';

    var $_exclude = array(
        'chart',
        'profile_settings',
        'message',
        'lock_tables',
        'country'
    );

    var $_defaultMenuName = 'application';

    var $_menu_table = 'menu';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('directory');
    }

    /*
     * create menu for each xml
     */
    public function createMenuFromXML()
    {
        $folder_path = FCPATH . 'application/' . $this->_table_folder;
        $files = directory_map($folder_path);
        $parent_id = 0;
        
        if (count($files)) {
            
            // get default parent id
            $applicationMenu = $this->checkMenuExists($this->_defaultMenuName);
            if (is_object($applicationMenu)) {
                $parent_id = $applicationMenu->id;
            }
            
            foreach ($files as $file) {
                $file = str_replace($this->_extension, '', $file);
                if (! in_array($file, $this->_exclude) && ! $this->checkMenuExists($file)) {
                    $this->createMenu($file, $parent_id);
                    echo 'menu created ' . $file . ' </br>';
                }
            }
        }
    }

    /*
     * check if menu exists
     * returns true if menu exists
     */
    public function checkMenuExists($menuName)
    {
        $this->db->select('id');
        $this->db->where('is_deleted', 0);
        $this->db->where('name', $menuName);
        
        return $this->db->get($this->_menu_table)->row();
    }

    /*
     * create a menu item from
     * a xml file
     */
    public function createMenu($menuName, $parent = 0)
    {
        $data = array(
            'name' => $menuName,
            'icon' => '',
            'have_view' => 1,
            'sort' => 0,
            'parent' => $parent
        );
        $this->db->insert($this->_menu_table, $data);
    }
}