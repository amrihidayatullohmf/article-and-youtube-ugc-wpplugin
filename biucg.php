<?php
/*
  Plugin Name: BI - User Generated Content
  Plugin URI: 
  Description: Enable User Generated Content for Videos and Articles, made for special purpose content management, vote and moderator
  Version: 1.0
  Author: Amri Hidayatulloh
  Author URI: https://www.linkedin.com/in/amrimultimedia/
  License: GPLv2+
  Text Domain: biucg
*/

define( 'BIUCG_VERSION', '1.0.0' );
define( 'BIUCG_MINIMUM_WP_VERSION', '4.0' );
define( 'BIUCG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( BIUCG_PLUGIN_DIR . 'class.biucg-pagetemplater.php' );
require_once( BIUCG_PLUGIN_DIR . 'class.biucg-queryhandler.php' );
require_once( BIUCG_PLUGIN_DIR . 'class.biucg-submissionlist.php' );
require_once( BIUCG_PLUGIN_DIR . 'class.biucg-votelist.php' );
require_once( BIUCG_PLUGIN_DIR . 'class.biucg-leaderboard.php' );
require_once( BIUCG_PLUGIN_DIR . 'class.biucg-mailer.php' );

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Biucg{

	public $mailer;
	public $query;
	public $db;

  // Constructor
    function __construct() {
    	global $wpdb;

    	if ( !function_exists( 'add_action' ) ) {
			echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
			exit;
		}

		$this->query = new Biucg_Queryhandler($wpdb);
		$this->mailer = new Biucg_Mailer();

		if(isset($_POST['state'])) {
			$this->export_submission();
		}

        register_activation_hook( __FILE__, array( $this, 'wpa_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'wpa_uninstall' ) );

        add_action( 'admin_menu', array( $this, 'wpa_add_menu' ));
        add_action('wp_head',array($this,'biucg_head_init'));
        add_action( 'plugins_loaded', array( 'Biucg_Pagetemplater', 'get_instance' ) );

        /* AJAX */
        add_action( 'wp_ajax_biucg_handleAjaxRemove', array( $this, 'biucg_handleAjaxRemove' ) );
        add_action( 'wp_ajax_biucg_handleAjaxApprove', array( $this, 'biucg_handleAjaxApprove' ) );
        add_action( 'wp_ajax_biucg_handleAjaxRevokeVoter', array( $this, 'biucg_handleAjaxRevokeVoter' ) );
        add_action( 'wp_ajax_biucg_fetchYoutubeData', array( $this, 'biucg_fetchYoutubeData' ) );
        add_action( 'wp_ajax_biucg_fetchUrlMetaData', array( $this, 'biucg_fetchUrlMetaData' ) );
        add_action( 'wp_ajax_biucg_uploadImageHandler', array( $this, 'biucg_uploadImageHandler' ) );
        add_action( 'wp_ajax_biucg_submissionSaveHandler', array( $this, 'biucg_submissionSaveHandler' ) );
        add_action( 'wp_ajax_biucg_likeHandler', array( $this, 'biucg_likeHandler' ) );

        add_shortcode( 'BIUGC-UPLOAD-BUTTON', array($this,'biucg_uploader_shortcode' ));
        add_shortcode( 'BIUGC-TOP-VIDEO', array($this,'biucg_uploader_shortcode' ));
        add_shortcode( 'BIUGC-TOP-ARTICLE', array($this,'biucg_uploader_shortcode' ));
        add_shortcode( 'BIUGC-TOP-ALL', array($this,'biucg_uploader_shortcode' ));
    }


    /*
      * Actions perform at loading of admin menu
      */
    function wpa_add_menu() {

        add_menu_page( 'BI User Generated Content', 'BI UGC', 'manage_options', 'biucg-dashboard',  array(
                          $this,
                         'wpa_page_file_path'
                        ), plugins_url('images/biucg-logo.png', __FILE__),'2.2.9');

        add_submenu_page( 'biucg-dashboard', 'Submission', ' Submission', 'manage_options', 'biucg-submission', array(
                              $this,
                             'wpa_page_file_path'
                            ));

        add_submenu_page( 'biucg-dashboard', 'Export', 'Export', 'manage_options', 'biucg-export', array(
                              $this,
                             'wpa_page_file_path'
                            ));
        add_submenu_page( 'biucg-dashboard','Settings', '<b style="color:#f9845b">Settings</b>', 'manage_options', 'biucg-settings', array(
                              $this,
                             'wpa_page_file_path'
                            ));

        
    }

    /*
     * Actions perform on loading of menu pages
     */
    function wpa_page_file_path() {
    	global $wpdb;

    	if( !is_admin() ) {
			echo 'Hi there!  you are seems have no right to call me';
			exit;
		}

    	$screen = get_current_screen();

    	echo '<link rel="stylesheet" type="text/css" href="'.plugins_url( 'vendor/sweetalert/sweetalert.css', __FILE__ ).'">';
    	echo '<link rel="stylesheet" type="text/css" href="'.plugins_url( 'css/dashboard-custom.css', __FILE__ ).'">';
    	echo '<script type="text/javascript" src="'.plugins_url( 'js/jquery-1.11.0.min.js', __FILE__ ).'"></script>';
    	echo '<script type="text/javascript" src="'.plugins_url( 'vendor/sweetalert/sweetalert.js', __FILE__ ).'"></script>';
    	echo '<script type="text/javascript" src="'.plugins_url( 'js/admin.js', __FILE__ ).'"></script>';
    	echo '<input type="hidden" id="ajaxurl" value="'.admin_url('admin-ajax.php').'">';

	    if ( strpos( $screen->base, 'biucg-dashboard' ) !== false ) {
	        $query = $this->query;
	        $table = new Biucg_leaderboard($wpdb,$this->query);
	        include( dirname(__FILE__) . '/includes/biucg-dashboard.php' );
	    } else if ( strpos( $screen->base, 'biucg-submission' ) !== false ) {
	    	$tab = (isset($_GET['tab'])) ? $_GET['tab'] : '';
	    	if($tab == 'like' and isset($_GET['id'])) {
	    		$table = new Biucg_votelist($wpdb,$this->query);
	    		$content = $this->query->get_row($_GET['id']);
	    		include( dirname(__FILE__) . '/includes/biucg-vote.php' );
	    	} else if($tab == 'detail' and isset($_GET['id'])) {
	    		$content = $this->query->get_row($_GET['id']);
	    		$query = $this->query;
	    		if(!isset($content[0]->id)) {
	    			echo "<script>location.href='".site_url()."/wp-admin/admin.php?page=biucg-submission';</script>";
	    		}
	    		include( dirname(__FILE__) . '/includes/biucg-detail.php' );
	    	} else {
	    		$table = new Biucg_submissionlist($wpdb,$this->query);
	    		include( dirname(__FILE__) . '/includes/biucg-submission.php' );
	   		}
	    } else if ( strpos( $screen->base, 'biucg-vote' ) !== false ) {
	    	include( dirname(__FILE__) . '/includes/biucg-vote.php' );
	    } else if ( strpos( $screen->base, 'biucg-export' ) !== false ) {
		    include( dirname(__FILE__) . '/includes/biucg-export.php' );
	    } else if ( strpos( $screen->base, 'biucg-settings' ) !== false ) {
	    	include( dirname(__FILE__) . '/includes/biucg-settings.php' );
	    } 
	    

    }

    /*
     * Actions perform on activation of plugin
     */
    function wpa_install() {
    	global $wpdb;

    	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    	$charset_collate = $wpdb->get_charset_collate();

    	$sql = "CREATE TABLE IF NOT EXISTS  ".$wpdb->prefix . 'biucg_user_contents'." (
					id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					user_id int(11) NOT NULL,
					content_type varchar(32) NOT NULL,
					url varchar(255) NOT NULL,
					meta_title varchar(255) NOT NULL,
					meta_description TEXT,
					meta_image varchar(125) NOT NULL,
					vote_count int(10) NOT NULL,
					view_count int(10) NOT NULL,
					status int(1) DEFAULT 2,
					created_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					modified_date timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
				) $charset_collate;";

		
		dbDelta( $sql );

		$sql = "CREATE TABLE IF NOT EXISTS  ".$wpdb->prefix . 'biucg_user_vote'." (
					id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					user_id int(11) NOT NULL,
					obj_id int(11) NOT NULL,
					status int(1) DEFAULT 2,
					created_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					modified_date timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
				) $charset_collate;";

		
		dbDelta( $sql );


		add_option('biucg-db-version','1.0');
		add_option('biugc-landing-page-id',0);
        add_option('biugc-detail-page-id',0);
        add_option('biugc-own-page-id',0);
		add_option('biugc-youtube-key','');
        add_option('biugc-limit-per-page',10);
        add_option('biugc-top-display-limit',5);
        add_option('biugc-voting-visibility',1);
        add_option('biugc-upload-visibility-video',1);
        add_option('biugc-upload-visibitity-article',1);


    }

    /*
     * Actions perform on de-activation of plugin
     */
    function wpa_uninstall() {

    	delete_option('biucg-db-version');
		delete_option('biugc-landing-page-id');
        delete_option('biugc-detail-page-id');
        delete_option('biugc-own-page-id');
		delete_option('biugc-youtube-key');
        delete_option('biugc-limit-per-page');
        delete_option('biugc-top-display-limit');
        delete_option('biugc-voting-visibility');
        delete_option('biugc-upload-visibility-video');
        delete_option('biugc-upload-visibitity-article');

    }

    function biucg_head_init() {
    	wp_enqueue_style( 'dashicons' );

    	echo '<link rel="stylesheet" type="text/css" href="'.plugins_url( 'css/boardz.min.css', __FILE__ ).'">';
    	echo '<link rel="stylesheet" type="text/css" href="'.plugins_url( 'css/biugc-stylesheet.css', __FILE__ ).'">';
		echo '<link rel="stylesheet" type="text/css" href="'.plugins_url( 'css/biugc-stylesheet-custom.css', __FILE__ ).'">';
   		echo '<link rel="stylesheet" type="text/css" href="'.plugins_url( 'vendor/sweetalert/sweetalert.css', __FILE__ ).'">';
    	
    	echo '<script type="text/javascript" src="'.plugins_url( 'vendor/sweetalert/sweetalert.js', __FILE__ ).'"></script>';
		echo '<script type="text/javascript" src="'.plugins_url( 'js/jquery-1.11.0.min.js', __FILE__ ).'"></script>';
		echo '<script type="text/javascript" src="'.plugins_url( 'vendor/jquery.form.js', __FILE__ ).'"></script>';
		echo '<script type="text/javascript">var biucg_ajaxurl = "'.admin_url('admin-ajax.php').'";</script>';
		echo '<script type="text/javascript" src="'.plugins_url( 'js/biucg-themes.js', __FILE__ ).'?v='.date('U').'"></script>';
	
		include( dirname(__FILE__) . '/views/theme-part-addpopup.php' );
	}


    function template_replacement($filters) {
    	return str_replace("Lorem ipsum", "The quick brown fox jumps over the lazy dog", $filters);
    }

    function export_submission() {
    	$state = $_POST['state'];
		$type = $_POST['type'];
		$file_name = $_POST['file_name'];

		$this->query->export_data(array(
									'type' => $type,
									'state' => $state
								  ));
    	exit;
    }

    function linkifyYouTubeURLs($url) {
	    $url = preg_replace('~(?#!js YouTubeId Rev:20160125_1800)
	        # Match non-linked youtube URL in the wild. (Rev:20130823)
	        https?://          # Required scheme. Either http or https.
	        (?:[0-9A-Z-]+\.)?  # Optional subdomain.
	        (?:                # Group host alternatives.
	          youtu\.be/       # Either youtu.be,
	        | youtube          # or youtube.com or
	          (?:-nocookie)?   # youtube-nocookie.com
	          \.com            # followed by
	          \S*?             # Allow anything up to VIDEO_ID,
	          [^\w\s-]         # but char before ID is non-ID char.
	        )                  # End host alternatives.
	        ([\w-]{11})        # $1: VIDEO_ID is exactly 11 chars.
	        (?=[^\w-]|$)       # Assert next char is non-ID or EOS.
	        (?!                # Assert URL is not pre-linked.
	          [?=&+%\w.-]*     # Allow URL (query) remainder.
	          (?:              # Group pre-linked alternatives.
	            [\'"][^<>]*>   # Either inside a start tag,
	          | </a>           # or inside <a> element text contents.
	          )                # End recognized pre-linked alts.
	        )                  # End negative lookahead assertion.
	        [?=&+%\w.-]*       # Consume any URL (query) remainder.
	        ~ix', '$1',
	        $url);
	    return $url;
	}

	function url_get_contents($Url) {
	    if (!function_exists('curl_init')){ 
	        die('CURL is not installed!');
	    }
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $Url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $output = curl_exec($ch);
	    curl_close($ch);
	    return $output;
	}

	function forge_mime_type($filename) {
		if(empty($filename)) {
			return '';
		}
		$ext = explode(".", $filename);
		$ext = end($ext);
		$ext = strtolower($ext);

		if($ext == 'jpg' or $ext == "jpeg") {
			return 'image/jpeg';
		} else if($ext == "png") {
			return 'image/png';
		} else if($ext == "gif") {
			return 'image/gif';
		}

		return false;
	}

	function get_meta_property_og($url) {
	    $doc = new DomDocument();
		$doc->loadHTML($this->url_get_contents($url));
		$xpath = new DOMXPath($doc);
		$query = '//*/meta[starts-with(@property, \'og:\')]';
		$metas = $xpath->query($query);
		$returns = array();
		foreach ($metas as $meta) {
		    $property = $meta->getAttribute('property');
		    $content = $meta->getAttribute('content');
		    $returns[$property] = $content;
		}
		return $returns;
	}

    /* AJAX HANDLER BELLOW */

    function biucg_handleAjaxRemove(){
    	if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

        $ids = $_POST['ids'];
        $return = array('code'=>500,'msg'=>'Nothing is selected !');
        if(!empty($ids)) {
       		$ids = explode(",", $ids);
       		foreach ($ids as $key => $value) {
       			$this->query->remove_submission($value);
       		}
       		$return = array('code'=>200,'msg'=>'Selected content has been removed, page will be reloaded !');
        }
	    echo json_encode($return);
	    wp_die();
	}

	function biucg_handleAjaxApprove() {
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$return = array('code'=>500,'msg'=>'Nothing is selected !');

		$ids = $_POST['ids'];
		$type = $_POST['type'];
		$email = $_POST['email'];
		$action = "approve";

		if($type == 'verify') {
			$ids = explode(",", $ids);
			$rows = array();
       		foreach ($ids as $key => $value) {
       			$get = $this->query->get_row($value);
       			$rows[] = array($value,$get[0]->user_email);
       		}
       		$return = array('code'=>200,'type'=>'verify','datas'=>$rows);
		} else if($type == 'sending') {
			$get = $this->query->get_row($ids);

			$datas = array(
						'*|NAME|*' => $get[0]->display_name,
						'*|IMAGE|*' => $get[0]->meta_image,
						'*|TITLE|*' => $get[0]->meta_title,
						'*|DESC|*' => $get[0]->meta_description
					 );


			$email = "amri.hidayatulloh@gmail.com";

			$action = ($action == "approve") ? 1 : 3;
			$subject = ($action == 1) ? 'Content Approval' : 'Content Rejection';
			$template = ($action == 1) ? 'email-approval' : 'email-rejection';
			$template =  BIUCG_PLUGIN_DIR.'views/'.$template.'.php';

			$this->query->update_state($ids,$action);

			$this->mailer->set_subject($subject);
			$this->mailer->set_to($email);
			$this->mailer->set_template($template);
			$this->mailer->set_content_pair($datas);
			$this->mailer->send();

			$return = array('code'=>200);
		}

	    echo json_encode($return);
	    wp_die();
	}

	function biucg_handleAjaxRevokeVoter(){
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

        $ids = $_POST['ids'];
        $parent_id = $_POST['parent_id'];
        $return = array('code'=>500,'msg'=>'Nothing is selected !');
        if(!empty($ids)) {
       		$ids = explode(",", $ids);
       		foreach ($ids as $key => $value) {
       			$this->query->revoke_voter($value);
       		}
       		$total_left = $this->query->get_count_vote($parent_id);
       		$this->query->update_total_vote($parent_id,$total_left);
       		$return = array('code'=>200,'msg'=>'Selected voter has been revoked, page will be reloaded !');
        }
	    echo json_encode($return);
	    wp_die();
	}

	function biucg_fetchYoutubeData() {
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$api_key = get_option('biugc-youtube-key');
		$video_url = $_POST['youtube_url'];
		$video_id = $this->linkifyYouTubeURLs($video_url);
		$api_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails%2Cstatistics&id=' . $video_id . '&key=' . $api_key;
		$data = $this->url_get_contents($api_url);
		echo $data;
		wp_die();
	}

	function biucg_fetchUrlMetaData() {
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$url = $_POST['web_url'];
		$return = array('code'=>500);

		if(!empty($url)) {
			$tags = get_meta_tags($url);
			$property_og = $this->get_meta_property_og($url);

			$meta = array();
			$meta['title'] = "";
			$meta['description'] = "";
			$meta['image'] = "";

			/* TITLE */
			if(!empty($tags['title'])) {
				$meta['title'] = $tags['title'];
			}
			if(!empty($tags['og:title']) and strlen($meta['title']) < strlen($tags['og:title'])) {
				$meta['title'] = $tags['og:title'];
			}
			if(!empty($tags['twitter:title']) and strlen($meta['title']) < strlen($tags['twitter:title'])) {
				$meta['title'] = $tags['twitter:title'];
			}
			if(!empty($property_og['og:title']) and strlen($meta['title']) < strlen($property_og['og:title'])) {
				$meta['title'] = $property_og['og:title'];	
			}

			/* DESCRIPTION */
			if(!empty($tags['description'])) {
				$meta['description'] = $tags['description'];
			}
			if(!empty($tags['og:title']) and strlen($meta['description']) < strlen($tags['og:description'])) {
				$meta['description'] = $tags['og:description'];
			}
			if(!empty($tags['twitter:description']) and strlen($meta['description']) < strlen($tags['twitter:description'])) {
				$meta['description'] = $tags['twitter:description'];
			}
			if(!empty($property_og['og:description']) and strlen($meta['description']) < strlen($property_og['og:description'])) {
				$meta['description'] = $property_og['og:description'];	
			}

			/* IMAGE */
			if(!empty($tags['image'])) {
				$meta['image'] = $tags['image'];
			}
			if(!empty($tags['og:image']) and strlen($meta['image']) < strlen($tags['og:image'])) {
				$meta['image'] = $tags['og:image'];
			}
			if(!empty($tags['twitter:image']) and strlen($meta['image']) < strlen($tags['twitter:image'])) {
				$meta['image'] = $tags['twitter:image'];
			}
			if(!empty($property_og['og:title']) and strlen($meta['title']) < strlen($property_og['og:title'])) {
				$meta['title'] = $property_og['og:title'];	
			}
			if(!empty($property_og['og:image']) and strlen($meta['image']) < strlen($property_og['og:image'])) {
				$meta['image'] = $property_og['og:image'];	
			}


			if(!empty($meta['title']) or !empty($meta['description']) or !empty($meta['image'])) {
				$return = array('code'=>200,'tags'=>$meta);
			} 
		}

		echo json_encode($return);
		wp_die();
	}

	function biucg_uploadImageHandler() {
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$return = array('code'=>500,'msg'=>'Please select an Image !');

		$meta_image = (isset($_FILES['meta_image'])) ? $_FILES['meta_image'] : NULL;
		$url_image = "";

		if(isset($meta_image)) {
			$save = TRUE;
			$i = 1;
			$wordpress_upload_dir = wp_upload_dir();
			$new_file_path = $wordpress_upload_dir['path'] . '/' . $meta_image['name'];
			$new_file_mime = $this->forge_mime_type($meta_image['name']);//mime_content_type( $meta_image['tmp_name'] );
			
			if($meta_image['error']) {
				$msg = "Image you tried to upload is corrupted !";
				$save = FALSE;
			}
			if($meta_image['size'] > wp_max_upload_size()) {
				$msg = "Image is too large than expected.";
				$save = FALSE;
			} 
			if(!in_array($new_file_mime, get_allowed_mime_types())) {
				$msg = "WordPress doesn\'t allow this type of uploads.";
				$save = FALSE;
			}

			if($save) {
				while( file_exists( $new_file_path ) ) {
					$i++;
					$new_file_path = $wordpress_upload_dir['path'] . '/' . $meta_image['name'] . "_" . $i;
				}

				if( move_uploaded_file( $meta_image['tmp_name'], $new_file_path ) ) {
					$upload_id = wp_insert_attachment( array(
															'guid'           => $new_file_path, 
															'post_mime_type' => $new_file_mime,
															'post_title'     => preg_replace( '/\.[^.]+$/', '', $meta_image['name'] ),
															'post_content'   => '',
															'post_status'    => 'inherit'
														), $new_file_path );
								 
					wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $new_file_path ) );
				
					$url_image = $wordpress_upload_dir['url'] . '/' . basename( $new_file_path );
					$return = array('code'=>200,'url'=>$url_image);
				} else {
					$return = array('code'=>500,'msg'=>'Failed to upload your image !');
				}
			} else {
				$return = array('code'=>500,'msg'=>$msg);
			}			 
		}

		echo json_encode($return);
		wp_die();
	}

	function biucg_submissionSaveHandler() {
		$return = array('code'=>500,'msg'=>'Complete correctly form bellow !');
	
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$id = (isset($_POST['id'])) ? strip_tags($_POST['id']) : '';
		$type = (isset($_POST['type'])) ? strip_tags($_POST['type']) : '';
		$url = (isset($_POST['url'])) ? strip_tags($_POST['url']) : '';
		$meta_image = (isset($_POST['meta_image'])) ? strip_tags($_POST['meta_image']) : '';
		$meta_title = (isset($_POST['meta_title'])) ? strip_tags($_POST['meta_title']) : '';
		$meta_description = (isset($_POST['meta_description'])) ? strip_tags($_POST['meta_description']) : '';
		$youtube_id = (isset($_POST['youtube_id'])) ? strip_tags($_POST['youtube_id']) : '';
		

		if(!empty($type) and !empty($url) 
						 and !empty($meta_image) 
						 and !empty($meta_title) 
						 and !empty($meta_description)) {


			if(empty($id)) {
				$datas = array(
							'user_id' => get_current_user_id(),
							'content_type' => $type,
							'url' => ($type == 'youtube') ? $youtube_id : $url,
							'meta_title' => $meta_title,
							'meta_image' => $meta_image,
							'meta_description' => $meta_description,
							'vote_count' => 0,
							'view_count' => 0,
							'status' => 2,
							'created_date' => date('Y-m-d H:i:s')
						 );

				//var_dump($datas);

				$set = $this->query->create_content($datas);

				if($set != FALSE) {
					$return = array('code'=>200,'msg'=>'Your submission has been uploaded!<br>It will be published as soon as it is approved by Admin. Thank you !');
				} else {
					$return = array('code'=>500,'msg'=>'Failed to save your Submission, try again later !');
				}
			} else {
				$datas = array(
							'url' => ($type == 'youtube') ? $youtube_id : $url,
							'meta_title' => $meta_title,
							'meta_image' => $meta_image,
							'meta_description' => $meta_description
						);

				$set = $this->query->update_content($id,$datas);

				if($set != FALSE) {
					$return = array('code'=>200,'msg'=>'Your changes have been saved, Thank you !');
				} else {
					$return = array('code'=>500,'msg'=>'Failed to save your Submission, try again later !');
				}
			}
		}

		echo json_encode($return);
		wp_die();
	}

	function biucg_likeHandler() {
		$return = array('code'=>500);
	
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$obj_id = (isset($_POST['obj_id'])) ? strip_tags($_POST['obj_id']) : 0;

		if(!empty($obj_id)) {

			$uid = get_current_user_id();
			$voter = $this->query->check_voter($uid,$obj_id);

			if(!$voter) {
			
				$datas = array(
							'user_id' => $uid,
							'obj_id' => $obj_id,
							'status' => 1,
							'created_date' => date('Y-m-d H:i:s')
						 );

				$set = $this->query->insert_voter($datas);

				if($set != FALSE) {
					$total = $this->query->get_count_vote($obj_id);
					$this->query->update_total_vote($obj_id,$total);

					$return = array('code'=>200,'total'=>$total,'type'=>'like');
				} else {
					$return = array('code'=>500);
				}
			} else {
				$revoke = $this->query->revoke_voter($voter);
				if($revoke != FALSE) {
					$total = $this->query->get_count_vote($obj_id);
					$this->query->update_total_vote($obj_id,$total);

					$return = array('code'=>200,'total'=>$total,'type'=>'unlike');
				} else {
					$return = array('code'=>500);
				}
			}
		}

		echo json_encode($return);
		wp_die();
	}

	/* SHORT CODES */

	function biucg_uploader_shortcode() {
		return "Yes It works !";
	}
	
}

new biucg();

?>