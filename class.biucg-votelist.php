<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Biucg_votelist extends WP_List_Table {
	private $wpdb;
	private $filter;
	private $queryHandler;
	private $keyword = "";
	private $order = array('created_date','DESC');
	private $current_page = 1;
	private $parent_id = 0;

	function __construct($wpdb,$queryHandler) {
        parent::__construct(
            array(
                'singular' => 'singular_form',
                'plural'   => 'plural_form',
                'ajax'     => false
            )
        );

        $this->wpdb = $wpdb;
        $this->queryHandler = $queryHandler;
    }

    function set_parent_id($id) {
    	$this->parent_id = $id;
    }

    function get_current_page() {
		$current_page = $this->get_pagenum();

		if(!isset($current_page) or empty($current_page)) {
			$current_page = 1;
		}

		$this->current_page = $current_page;
	}

	function get_number() {
		return $this->queryHandler->get_count_vote($this->parent_id);
	}

	function set_keyword($keyword) {
		$this->keyword = $keyword;
	}

	function set_sortparam($orderby = 'created_date',$order = 'DESC') {
		$this->order = array($orderby,$order);
	}

	function prepare_data($offset = 0,$limit = 25) {
		$lists = $this->queryHandler->get_voter($this->parent_id,$this->order,array($offset,$limit),$this->keyword);
		$rows = array();

		foreach ($lists as $key => $value) {
			$avatar = get_avatar_url($value->user_id);
			$avatar = str_replace('//www','http://www',$avatar);
			$url = site_url()."/wp-admin/user-edit.php?user_id=".$value->id."&wp_http_referer=/wp-admin/admin.php?page=biucg-submission";
			
			$rows[] = array(
							'check'		=> '<input type="checkbox" class="checkitem" value="'.$value->id.'" name="ids[]" style="margin-left:8px">',
							'votername' => '<img src="'.$avatar.'" width="40" class="avatarimage"><div class="username"><a href="'.$url.'" target="_blank">'.$value->display_name."</a></div>",
							'datetime' 	=> $value->created_date,
							'action'	=> '<div class="action-area-btn" style="text-align:left">
												<button class="remove remove-voter" data-id="'.$value->id.'">Remove</button>
											</div>'
					);
		}

		return $rows;
	}

	

	function get_columns(){
	  $columns = array(
	  	'check'		=> '<input type="checkbox" class="checkall">',
	    'votername' => 'Name',
	    'datetime'  => 'Timestamp',
	    'action'  	=> 'Action'
	  );
	  return $columns;
	}

	function prepare_items() {
	  $offset = 0;
	  $limit = 25;
  	  $current_page = $this->current_page;
  	  $total_items = $this->queryHandler->get_count($this->filter);

  	  if($current_page > 1) {
  	  	$offset = ($current_page - 1) * $limit;
  	  }

  	  $found_data = $this->prepare_data($offset,$limit);

	  $columns = $this->get_columns();
	  $hidden = array();
	  $sortable = array(
	  				'datetime'  => array( 'created_date', false )
	  			  );
	  $this->_column_headers = array($columns, $hidden, $sortable);
	  $this->set_pagination_args( array(
    	'total_items' => $total_items,              
    	'per_page'    => $limit                     
  	  ) );
 	  $this->items = $found_data;


 	  $this->process_bulk_action();
	}


	function column_cb($item) {
          
    }

    public function get_bulk_actions() {
	    return array(
	            'revoke'  => __( 'Remove', 'your-textdomain' ),
	    );
    }

    public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();

        switch ( $action ) {

            case 'delete':
                wp_die( 'Delete something' );
                break;

            case 'save':
                wp_die( 'Save something' );
                break;

            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }


	function column_default( $item, $column_name ) {
	  switch( $column_name ) { 
	  	case 'check':
	    case 'votername':
	    case 'action':
	    case 'datetime':
	      return $item[ $column_name ];
	    default:
	      return print_r( $item, true ) ; 
	  }
	}	
}