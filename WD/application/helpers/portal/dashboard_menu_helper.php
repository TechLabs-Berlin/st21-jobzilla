<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * get the menu either FROM folder or xml
 */
function dashboard_get_menu_left_selection()
{
    $CI = & get_instance();
    
    return BMenuHelper::get_menu_data_from_db();
}

class BMenuHelper
{
    public static $_instance;
    
    public static function parent_start($menu, $active = 0)
    {
        $CI =& get_instance();
        
        $template_name = $CI->config->item("template_name");

        $data = array(
            'active' => $active,
            'menu' => $menu
        );

       $html = $CI->load->view($template_name.'/'.$template_name.'_menu/parent_start', $data, true);  

        return $html;
    }

    public static function menuNameSanitizer($name)
    {
        // filling up all whitestapce with underscore
        $name = preg_replace('/\s+/u', '_', $name);

        // check if the text has underscore at front
        if ( substr($name, 0, 1) === "_" ) {
            return strtoupper($name);
        } else {
            return "_" . strtoupper($name);
        }
    }

    public static function parent_start_top($menu, $active = 0, $caret, $menu_opener, $adjustment, $openingSide)
    {

         $CI =& get_instance();
        
        $template_name = $CI->config->item("template_name");

        $data = array(
            'active' => $active,
            'menu' => $menu,
            'caret' => $caret,
            'menu_opener'=>$menu_opener,
            'adjustment'=>$adjustment,
            'openingSide'=>$openingSide
        );

       $html = $CI->load->view($template_name.'/'.$template_name.'_menu/parent_start_top', $data, true);     


        return $html;
    }

    public static function parent_end()
    {
        $CI =& get_instance();
        
        $template_name = $CI->config->item("template_name");

       $html = $CI->load->view($template_name.'/'.$template_name.'_menu/parent_end', '', true);     
        
        return $html;
    }

    public static function render_single_item($menu)
    {

        $CI =& get_instance();
        
        $template_name = $CI->config->item("template_name");

        $data = array(
            'menu' => $menu
        );

       $html = $CI->load->view($template_name.'/'.$template_name.'_menu/render_single_item', $data, true);   
        
        
        return $html;
    }

    public static function render_menu_html($menu, $childs)
    {
        $CI = get_instance();
        $current_item = $CI->uri->segment(2);
        
        $current_user_role = get_user_role();
        $allowed_tables = $CI->session->userdata('allowed_tables');
        $not_allowed_tables = get_user_not_viewable_tables($current_user_role);
        // Save data into a session array
        
        $full_permission = 0;
        
        if (in_array('*', $allowed_tables)) {
            $full_permission = 1;
        }
        
        $active_child_open = 0;
        $html = '';
        
        if (isset($childs[$menu['id']]) and is_array($childs[$menu['id']])) {
            // has child , its a parent
            
            $child_open_flag = 0;
            $child_has_permission = 0;
            
            foreach ($childs[$menu['id']] as $child) {
                if ($current_item == $child['name']) {
                    $child_open_flag = 1;
                }
                
                // check if child has allowed permission
                if (in_array($child['name'], $allowed_tables)) {
                    $child_has_permission = 1;
                }
            }
            
            if ($child_open_flag) {
                
                $menu['active_open'] = 1;
            }
            
            if ((in_array($menu['name'], $allowed_tables) or $full_permission or $child_has_permission) and ! in_array($menu['name'], $not_allowed_tables)) {
                
                foreach ($childs[$menu['id']] as $child) {
                    if ($current_item == $child['name']) {
                        $child['active_open'] = 1;
                        $active_child_open = 1;
                    }
                    
                    if ((in_array($child['name'], $allowed_tables) or $full_permission) and ! in_array($menu['name'], $not_allowed_tables)) {
                        
                        $return_child = self::render_menu_html($child, $childs);
                        if ($return_child['active_child']) {
                            $active_child_open = 1;
                        }
                        $html .= $return_child['html'];
                    }
                }
                
                $html = self::parent_start($menu, $active_child_open) . $html;
                $html .= self::parent_end();
            }
        } else {
            
            if ($current_item == $menu['name']) {
                $menu['active_open'] = 1;
                $active_child_open = 1;
            }
            if ((in_array($menu['name'], $allowed_tables) or $full_permission) and ! in_array($menu['name'], $not_allowed_tables)) {
                
                $html = self::render_single_item($menu);
            }
        }
        return array(
            'html' => $html,
            'active_child' => $active_child_open
        );
    }

    public static function render_top_menu_html($menu, $childs, $top_iteration = null)
    {
        $CI = get_instance();
        $current_item = $CI->uri->segment(2);
        
        $current_user_role = get_user_role();
        $allowed_tables = $CI->session->userdata('allowed_tables');
        $not_allowed_tables = get_user_not_viewable_tables($current_user_role);
        
        $full_permission = 0;
        
        if (in_array('*', $allowed_tables)) {
            $full_permission = 1;
        }
        
        $active_child_open = 0;
        $html = '';
        
        if (isset($childs[$menu['id']]) and is_array($childs[$menu['id']])) {
            // has child , its a parent
            
            $child_open_flag = 0;
            $child_has_permission = 0;
            
            foreach ($childs[$menu['id']] as $child) {
                
                if ($current_item == $child['view']) {
                    $child_open_flag = 1;
                }
                
                // check if child has allowed permission
                if (in_array($child['view'], $allowed_tables)) {
                    $child_has_permission = 1;
                }
            }
            
            if ($child_open_flag) {
                
                $menu['active_open'] = 1;
            }
            $i = 0;
            if ((in_array($menu['view'], $allowed_tables) or $full_permission) or $child_has_permission) {
                $caret = 'right m--pull-right';
                $openingSide = $menu["submenu_position"];
                $menu_opener = 'hover';
                $adjustment = '';
                foreach ($childs[$menu['id']] as $child) {
                    if ($current_item == $child['view']) {
                        $child['active_open'] = 1;
                        $active_child_open = 1;
                    }
                    
                    if ((in_array($child['view'], $allowed_tables) or $full_permission) && (! in_array($menu['view'], $not_allowed_tables))) {
                        if ($i < $top_iteration) {
                            $caret = 'down';
                            $menu_opener = 'hover';
                            $openingSide = "left";
                            $adjustment = 'm-menu__arrow--adjust';
                            $i ++;
                        }
                        $return_child = self::render_top_menu_html($child, $childs);
                        if ($return_child['active_child']) {
                            $active_child_open = 1;
                        }
                        $html .= $return_child['html'];
                    }
                }
                
                if ($current_item == $menu['view']) {
                    $menu['active_open'] = 1;
                    $active_child_open = 1;
                }
                $html = self::parent_start_top($menu, $active_child_open, $caret, $menu_opener, $adjustment, $openingSide) . $html;
                $html .= self::parent_end();
            }
        } else {
            
            if ($current_item == $menu['view']) {
                $menu['active_open'] = 1;
                $active_child_open = 1;
            }
            if ((in_array($menu['view'], $allowed_tables) or $full_permission) && (! in_array($menu['view'], $not_allowed_tables))) {
                
                $html = self::render_single_item($menu);
            }
        }
        return array(
            'html' => $html,
            'active_child' => $active_child_open
        );
    }

    public static function get_menu_data_from_db()
    {
        $CI = get_instance();
        $account_id = get_default_account_id();
        
        $menu_table = $CI->config->item('prefix') . 'menu';
        $views_table = $CI->config->item('prefix') . 'views';
        
        $CI->db->select("{$menu_table}.*,v.name as view");
        $CI->db->from("$menu_table");
        $CI->db->where("{$menu_table}.is_deleted", 0);
        $CI->db->where("{$menu_table}.account_id", $account_id);
        $CI->db->where("{$menu_table}.visibility", 1);
        $CI->db->join($views_table . " v", "v.id = {$menu_table}.views_id AND v.is_deleted = 0 AND v.account_id = '{$account_id}'", "left");
        $CI->db->order_by("{$menu_table}.parent", "ASC");
        $CI->db->order_by("{$menu_table}.sort", "ASC");
        $CI->db->order_by("{$menu_table}.name", "ASC");
        
        $menu_items = $CI->db->get()->result_array();
        $childs = array();
        $base_url = base_url();
        
        foreach ($menu_items as &$menu) {
            $menu['href'] = "javascript:;";
            if ( !empty($menu["have_view"]) && !empty($menu["views_id"]) ) {
                $menu['href'] = $base_url . 'portal/system/defaultview/' . $menu['view'] . '/listing';
            }
            $childs[$menu['parent']][] = $menu;
        }
        return array(
            'menu_items' => $menu_items,
            'childs' => $childs
        );
    }

    public static function get_menu_position_from_user_role()
    {
        $CI = get_instance();
        $menu_position = "";
        get_user_role();
        $user_data = BUserHelper::get_instance();
        $user_role = $user_data->user->role;
        $CI->db->select('menu_position');
        $CI->db->where('slug', $user_role);
        $CI->db->where('is_deleted', 0);
        $query = $CI->db->get('user_roles');
        if ($query->num_rows() > 0) {
            $data = $query->row();
            $menu_position = $data->menu_position;
        }
        
        return $menu_position;
    }

    
    public static function get_not_viewable_menus( $user_role )
    {
        if (! isset(self::$_instance)) {
    
            self::$_instance = self::get_user_not_viewable_tables($user_role);
        }
    
        return self::$_instance;
    }

    public static function get_user_not_viewable_tables($user_role)
    {
        $CI = & get_instance();
        $CI->config->load('dashboard');
        $CI->config->load('dashboard_override');
        $tableName = $CI->config->item('prefix') . 'permissions_row';
    
        $return_tables = array();
        $CI->db->select("distinct(menu) as menus");
        $CI->db->where("role", $user_role);
        $CI->db->where("is_deleted", 0);
        $CI->db->where("show_left_menu", 0);
    
        $results = $CI->db->get($tableName)->result_array();
        if (count($results)) {
            foreach ($results as $result) {
                $return_tables[] = (strtolower($result['menus']));
            }
            return $return_tables;
        } else {
            return array();
        }
    }
}

/*
 * this function returns a list of table named in array which this current user can view
 */
function get_user_viewable_tables($role)
{
    static  $result = array();
    
    if( key_exists($role, $result ) ) return  $result[$role];

    $CI = & get_instance();
    $CI->config->load('dashboard');
    $CI->config->load('dashboard_override');
    $tableName = $CI->config->item('prefix') . 'permissions_row';
    
    $return_tables = array();
    $CI->db->select("distinct(menu) as menus");
    $CI->db->where("role", $role);
    $CI->db->where("is_deleted", 0);
    
    $results = $CI->db->get($tableName)->result_array();
    if (count($results)) {
        foreach ($results as $result) {
            $return_tables[] = (strtolower($result['menus']));
        }
        $result[$role] = $return_tables;
        return $return_tables;
    } else {
        return array();
    }
}

function get_user_not_viewable_tables($role)
{
    return BMenuHelper::get_not_viewable_menus( $role );
}

function get_user_role_menu($role)
{
    $CI = & get_instance();
    $CI->config->load('dashboard');
    $CI->config->load('dashboard_override');
    $tableName = $CI->config->item('prefix') . 'permissions_row';
    
    $return_tables = array();
    
    if ($role == "super_admin") {
        $CI->db->select("distinct(name) as menus");
        $CI->db->where("is_deleted", 0);
        $CI->db->where("have_view", 1);
        $CI->db->where("menu.account_id", get_default_account_id());
        $results = $CI->db->get("menu")->result_array();
    } else {
        $CI->db->select("distinct(menu) as menus");
        $CI->db->join("menu", "menu.name=permissions_row.menu");
        $CI->db->where("role", $role);
        $CI->db->where("permissions_row.is_deleted", 0);
        $CI->db->where("menu.have_view", 1);
        $CI->db->where("permissions_row.account_id", get_default_account_id());
        $results = $CI->db->get($tableName)->result_array();
    }
    // echo $CI->db->last_query();
    if (count($results)) {
        foreach ($results as $result) {
            $return_tables[$result['menus']] = dashboard_lang('_' . strtoupper($result['menus']));
        }
        if ($role == "super_admin") {
            $return_tables["*"] = "*";
        }
        return $return_tables;
    } else {
        return array();
    }
}

function get_tables_by_menu($menu)
{
    $CI = & get_instance();
    $CI->config->load('dashboard');
    $CI->config->load('dashboard_override');
    $tableName = $CI->config->item('prefix') . 'permissions_row';
    
    $return_tables = array();
    $CI->db->select("distinct(menu) as menus");
    /*
     * if ($role != "super_admin") {
     * $CI->db->where("role", $role);
     * }
     */
    $CI->db->where("is_deleted", 0);
    
    $results = $CI->db->get($tableName)->result_array();
    if (count($results)) {
        foreach ($results as $result) {
            $return_tables[$result['menus']] = dashboard_lang('_' . strtoupper($result['menus']));
        }
        return $return_tables;
    } else {
        return array();
    }
}
