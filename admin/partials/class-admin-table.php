<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       apurba.me
 * @since      1.0.0
 *
 * @package    Fb_Notify
 * @subpackage Fb_Notify/admin/partials
 */
require plugin_dir_path( __FILE__ ) . '../../includes/class-fb-list-table.php';

class My_List_Table extends TXP_FB_List_Table {



    public $found_data;



    function get_columns(){
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'first_name'    => 'First Name',
            'last_name'     => 'Author',
            'email'         => 'Email',
            'link'          => 'Profile Link',
            'gender'        => 'Gender',
            'picture'       => 'Profile Picture',
            'send_data'     => 'Send Notification'
        );
        return $columns;
    }

    function prepare_items($items=null) {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $items;

        $per_page = apply_filters('txp_fb_data_table',20);
        $current_page = $this->get_pagenum();

        $total_items = count($items);
        // only ncessary because we have sample data
        $this->found_data = array_slice($items,(($current_page-1)*$per_page),$per_page);


        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page                     //WE have to determine how many items to show on a page
        ) );
        $this->items = $this->found_data;

    }

    function column_default($item, $column_name) {
        return $item[$column_name];
    }

    function column_link($item){
        return "<a class='txp-profile-link' target='_blank' href=".$item['link'].">Visit Profile</a>";
    }



    function column_picture($item){

        $pic_url = esc_url($item['picture']->data->url);
        return '<img src='."$pic_url".' alt="profile-pic">';
    }

    function column_send_data(){
      return  '<button class="button button-primary txp-send-notification-btn">'.esc_html__('Send Notification','txp').'</button>';
    }


    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" class="selected_id" name="selected_id[]" value="%s" />', $item['id']
        );
    }

    function get_views()
    {
        $views = array(
            'txp-send-notification' => '<button class="button button-primary txp-send-notification-btn">'.esc_html__('Send Notification','txp').'</button>',
              'txp-send-notification-delete' => '<button class="button button-primary txp-send-notification-delete-btn">'.esc_html__('Delete','txp').'</button>',
              'txp-send-notification-csv-download' => '<a href='.get_site_url().'/?download_report class="button button-primary">'.esc_html__('Download All Data as CSV','txp').'</a>',

        );


        return $views;
    }

}



