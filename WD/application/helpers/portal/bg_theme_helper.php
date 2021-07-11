<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bg_theme 
{
    private static $_instance;
    private $bgImageTable, $userLoginTable;
    private $ciInstance;
    
    public function __construct() 
    {
        $this->ciInstance = &get_instance();
        $this->bgImageTable = 'bg_images';
        $this->userLoginTable = 'dashboard_login';
    }
    
    public static function getSelectedBgTheme () {
        
        $self = self::$_instance = new Bg_theme();
        
        $dbLoginTbl = $self->userLoginTable;
        $bgImagesTbl = $self->bgImageTable;
        $userId = $self->ciInstance->session->userdata('user_id');
        $tenantId = $self->ciInstance->session->userdata('account_id');
        
        // get background image from user login table
        $self->ciInstance->db->select('background_image');
        $self->ciInstance->db->where(array('id' => $userId, 'is_deleted' => 0));
        $imageNameArr = $self->ciInstance->db->get($dbLoginTbl)->row_array();
        
        // get all background images from bg image table
        $self->ciInstance->db->select('background_image,bg_theme');
        $self->ciInstance->db->where(array('is_deleted' => 0, 'account_id' => $tenantId));
        $bgImageArr = $self->ciInstance->db->get($bgImagesTbl)->result_array();
        
        if ( count( $bgImageArr ) > 0 ) {
            foreach ( $bgImageArr as $bgImage ) {
                
                if ( stripos( $bgImage['background_image'], 'https://') !== false || stripos( $bgImage['background_image'], 'http://') !== false ) 
                    $imgName = $bgImage['background_image'];
                else {
                    
                    $imgArr = explode('/', $bgImage['background_image']);
                    $imgArrLen = count( $imgArr );
                    $imgName = $imgArr[ ($imgArrLen-1) ];
                }
                
                
                if ( $imgName === $imageNameArr['background_image'] ) {
                    return $bgImage['bg_theme'];
                }
            }
            
            return $self->ciInstance->config->item('#DASHBOARD_DEFAULT_BG_THEME_COLOR');
        } else {
            return $self->ciInstance->config->item('#DASHBOARD_DEFAULT_BG_THEME_COLOR');
        }
    
    }    
}