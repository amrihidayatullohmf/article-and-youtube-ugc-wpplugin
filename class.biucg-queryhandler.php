<?php
class Biucg_Queryhandler {

	private $wpdb;

	function __construct($wpdb) {
		$this->wpdb = $wpdb;
	}

	private function gen_query($filter = array(), $order = array(), $endpoint = array(), $keyword = "") {
		$query = "SELECT u.display_name, u.user_email, c.* FROM wp_biucg_user_contents c LEFT JOIN wp_users u ON c.user_id = u.ID";

		$where = array();

		if(isset($filter['type']) and $filter['type'] != 'all') {
			$where[] = " c.content_type = '".$filter['type']."' ";
		}

		if(isset($filter['state']) and $filter['state'] != 'all') {
			$where[] = " c.status = '".$filter['state']."' ";
		} else {
			$where[] = " c.status != 0 ";
		}

		if(!empty($keyword)) {
			$where[] = " (c.content_type LIKE '%".$keyword."%' OR c.url LIKE '%".$keyword."%' OR c.meta_title LIKE '%".$keyword."%' OR c.meta_description LIKE '%".$keyword."%' OR u.display_name LIKE '%".$keyword."%') ";
		}

		if(count($where) > 0) {
			$where_str = implode("AND", $where);
			$query .= " WHERE ".$where_str;
		}

		if(isset($order[0]) and isset($order[1])) {
			$query .= " ORDER BY c.".$order[0]." ".$order[1];
		}

		if(isset($endpoint[0]) and isset($endpoint[1])) {
			$query .= " LIMIT ".$endpoint[0].",".$endpoint[1];
		}

		return $query;
	}

	public function get_row($id) {
		$query = "SELECT u.display_name, u.user_email, c.* FROM wp_biucg_user_contents c LEFT JOIN wp_users u ON c.user_id = u.ID WHERE c.status != 0 AND c.id = ".$id;
		return $this->wpdb->get_results( $query );
	}

	public function get_submission($filter = array(),$order = array('created_date','ASC'), $endpoint = array(),$keyword) {
		$query = $this->gen_query($filter,$order,$endpoint,$keyword);
		return $this->wpdb->get_results( $query );
	}

	public function get_count($filter = array(),$keyword = "") {
		$query = $this->gen_query($filter,array(),array(),$keyword);
		$this->wpdb->get_results( $query );
		return $this->wpdb->num_rows;
	}

	public function remove_submission($id) {
		return $this->wpdb->update('wp_biucg_user_contents',array('status'=>0),array('id'=>$id));
	}

	public function update_state($id,$state = 1) {
		return $this->wpdb->update('wp_biucg_user_contents',array('status'=>$state),array('id'=>$id));
	}

	public function export_data($filter = array(),$file_name="") {
		$file_name = (!empty($file_name)) ? $file_name : "submission-".date('Ymd').".csv";
		$result = $this->get_submission($filter);

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$file_name);

		$output = fopen('php://output', 'w');

		$header = array(
							'ID', 
							'User ID', 
							'User Name',
							'User E-Mail',
							'Content Type',
							'URL',
							'Meta Title',
							'Meta Image URL',
							'Meta Description',
							'Total Like',
							'Total View',
							'Status',
							'Submission Date'
						);

		fputcsv($output,$header);

		foreach ($result as $key => $value) {
			$desc = strip_tags($value->meta_description);
			$status = ($value->status == 2) ? 'Pending' : 'Approved';
			$row = array(
							$value->id, 
							$value->user_id, 
							$value->display_name,
							$value->user_email,
							$value->content_type,
							$value->url,
							$value->meta_title,
							$value->meta_image,
							$desc,
							$value->vote_count,
							$value->view_count,
							$status,
							"=\"".$value->created_date."\""
						);

			fputcsv($output,$row);	
		}
	}

	public function gen_query_voter($id, $order = array('created_date','DESC'), $endpoint = array(), $keyword = "") {
		$query = "SELECT u.display_name, u.user_email, c.* FROM wp_biucg_user_vote c LEFT JOIN wp_users u ON c.user_id = u.ID WHERE c.status = 1 AND c.obj_id = ".$id;

		if(!empty($keyword)) {
			$query .= " AND (u.display_name LIKE '%".$keyword."%' OR c.created_date LIKE '%".$keyword."%') ";
		}

		if(isset($order[0]) and isset($order[1])) {
			$query .= " ORDER BY ".$order[0]." ".$order[1];
		}

		if(isset($endpoint[0]) and isset($endpoint[1])) {
			$query .= " LIMIT ".$endpoint[0].",".$endpoint[1];
		}

		return $query;
	}

	public function get_voter($id, $order = array(), $endpoint = array(), $keyword = "") {
		$query = $this->gen_query_voter($id,$order,$endpoint,$keyword);
		return $this->wpdb->get_results( $query );
	}

	public function get_count_vote($id,$order = array(), $endpoint = array(), $keyword = "") {
		$query = $this->gen_query_voter($id,$order,$endpoint,$keyword);
		$this->wpdb->get_results( $query );
		return $this->wpdb->num_rows;
	}

	public function check_voter($uid,$objid) {
		$check = $this->wpdb->get_results("SELECT * FROM wp_biucg_user_vote WHERE user_id = '".$uid."' AND obj_id = '".$objid."' AND status = 1");
		return (isset($check[0]->id)) ? $check[0]->id : FALSE;
	}

	public function get_total_vote() {
		$this->wpdb->get_results( "SELECT * FROM wp_biucg_user_vote WHERE status = 1" );
		return $this->wpdb->num_rows;
	}

	public function revoke_voter($id) {
		return $this->wpdb->update('wp_biucg_user_vote',array('status'=>0),array('id'=>$id));
	}

	public function update_total_vote($parent_id, $total) {
		return $this->wpdb->update('wp_biucg_user_contents',array('vote_count'=>$total),array('id'=>$parent_id));
	}

	public function update_content($id,$datas = array()) {
		return $this->wpdb->update('wp_biucg_user_contents',$datas,array('id'=>$id));
	}

	public function create_content($datas = array()) {
		return $this->wpdb->insert('wp_biucg_user_contents',$datas);	
	}

	public function insert_voter($datas = array()) {
		return $this->wpdb->insert('wp_biucg_user_vote',$datas);
	}

}