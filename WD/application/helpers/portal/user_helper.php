<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

    /*
 * UserHelper class
 */
class BUserHelper extends BObject
{

    public static $_instance;

    public $user;

    public function __construct($user_id = 0)
    {
        parent::__construct();
        $CI = get_instance();
        if ($user_id == 0) {
            $user_id_config = $CI->config->item('user_id');
            $user_id = $CI->session->userdata($user_id_config);
        }
        
        $this->user = new BUser($user_id);
        $this->user_role = new BRole(@$this->user->role);
        $this->user_image = @$this->user->get_user_profile_picture();
        if (! empty($this->user->account_id)) {
            $this->tenant = new BAccount($this->user->account_id);
        }
    }

    public static function get_instance()
    {
        if (! isset(self::$_instance)) {
            
            self::$_instance = new BUserHelper();
        }
        
        return self::$_instance;
    }

    public static function get_profile_image_by_id($user_id = 0)
    {
        self::$_instance = new BUserHelper($user_id);
        return self::$_instance;
    }

    public static function render_profile_picture($image_name)
    {
        $CI = get_instance();
        if ((stripos($image_name, "http://") !== false) || (stripos($image_name, "https://") !== false)) {
            $url_profile_picture = $image_name;
        } else{
            $CI->config->load('dashboard_override');
            $profile_img_upload_path = $CI->config->item("profile_img_upload_path");
            $url_profile_picture =  CDN_URL.$profile_img_upload_path;
            
            if(isset($image_name) AND !empty($image_name)){
                $url_profile_picture .= $image_name;
            }else{
                
                $url_profile_picture = $CI->config->item("default_avater_url");
            }
        }
        
        return $url_profile_picture;
    }

    public static function getCurrentUserACL()
    {
        $userData = BUserHelper::get_instance();
        
        return $userData->user_role->access_level;
    }

    public static function getAccessibleRoles($array = FALSE)
    {
        $CI = get_instance();
        $vieweableRoles = array();
        $currentUserACL = self::getCurrentUserACL();
        $CI->db->select('slug');
        $CI->db->where('access_level >=', $currentUserACL);
        $CI->db->where('is_deleted', 0);
        $CI->db->where('account_id', get_default_account_id());
        
        $user_roles_table = $CI->_table_prefix . "user_roles";
        
        $data = $CI->db->get($user_roles_table)->result_array();
        
        foreach ($data as $row) {
            $vieweableRoles[] = $row['slug'];
        }
        
        if ($array) {
            return $vieweableRoles;
        } else {
            $vieweableRolesStr = "'" . implode("','", $vieweableRoles) . "'";
            return $vieweableRolesStr;
        }
    }

    public static function getUserLanguageCode()
    {
        $CI = get_instance();
        $site_languages = $CI->config->item('site_languages');
        $user_language = get_current_user_lang();
        foreach ($site_languages as $key => $value) {
            if ($user_language === $value) {
                $site_lang = $key;
            }
        }
        if (isset($site_lang)) {
            return $site_lang;
        } else {
            return "en";
        }
    }
}

/*
 * User class
 */
class BUser extends BTable
{
    
    // protected members
    protected $_table = 'dashboard_login';

    protected $_primary_key = 'id';

    protected $_ignore_fields = array(
        'password'
    );
    
    // load the user data into member 
    public function __construct($primary_key)
    {
        parent::__construct($primary_key);
    }

    public function get_user_profile_picture()
    {
        $CI = get_instance();
        
        if ((stripos ( $this->image, "http://" ) !== false) || (stripos ( $this->image, "https://" ) !== false)) {
            return  $this->image;
        }else{
            $CI->config->load('dashboard_override');        
            $profile_img_upload_path = $CI->config->item("profile_img_upload_path");
            $url_profile_picture = CDN_URL . $profile_img_upload_path;        
            $path_profile_picture = FCPATH . $profile_img_upload_path . $this->image;            
            if (isset($this->image) and ! empty($this->image) and file_exists($path_profile_picture)) {
                $url_profile_picture .= $this->image;
            } else {
                $url_profile_picture = $CI->config->item("default_avater_url");
            }
            return $url_profile_picture;
        }
        
    }
}

/*
 * Role table
 */
class BRole extends BTable
{
    
    // protected 
    protected $_table = 'user_roles';

    protected $_primary_key = 'slug';

    protected $_ignore_fields = array();
    
    // load the user data into member 
    public function __construct($primary_key = 0)
    {
        parent::__construct($primary_key);
    }
}

/*
 * Account Table
 */
class BAccount extends BTable
{
    
    // protected 
    protected $_table = 'accounts';

    protected $_primary_key = 'id';

    protected $_ignore_fields = array();
    
    // load the user data into member 
    public function __construct($primary_key = 0)
    {
        parent::__construct($primary_key);
    }
}