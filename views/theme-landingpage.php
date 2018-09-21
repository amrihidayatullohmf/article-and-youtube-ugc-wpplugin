<?php 
global $wpdb;

$type = (isset($_GET['type'])) ? strip_tags($_GET['type']) : 'all';
$page = (isset($_GET['nav'])) ? strip_tags($_GET['nav']) : 1;
$keyword = (isset($_POST['query'])) ? strip_tags($_POST['query']) : '';

$offset = 0;
$limit = get_option('biugc-limit-per-page');
$margin_display = 3;

if($page > 1) {
	$offset = ($page - 1) * $limit;
}

$query = "SELECT u.display_name, u.user_email, c.* FROM wp_biucg_user_contents c LEFT JOIN wp_users u ON c.user_id = u.ID WHERE c.status = 1";

if($type != 'all') {
	if($type == 'video') {
		$type = 'youtube';
	}
	$query .= " AND c.content_type = '".$type."' ";
}

if(!empty($keyword)) {
	$query .= " AND (c.content_type LIKE '%".$keyword."%' OR c.url LIKE '%".$keyword."%' OR c.meta_title LIKE '%".$keyword."%' OR c.meta_description LIKE '%".$keyword."%' OR u.display_name LIKE '%".$keyword."%') ";
}

$query .= " ORDER BY c.created_date DESC";

$total = $wpdb->get_results($query);
$total_row = $wpdb->num_rows;
$total_page = ceil($total_row/$limit);

$query .= " LIMIT ".$offset.",".$limit;
$contents = $wpdb->get_results($query);

$first = 1;
$last = $total_page;
$prev = ($page > 1) ? $page - 1 : NULL;
$next = ($page < $total_page) ? $page + 1 : NULL;
	  	
$start_endpoint = $page - $margin_display;
$end_endpoint = $page + $margin_display;

if($start_endpoint <= 0 and $end_endpoint <= $total_page) {
	$start_endpoint = 1;
	$end_endpoint = ($page + ($margin_display + ($margin_display - $page)));
} else if($end_endpoint > $total_page and $start_endpoint >= 1) {
	$start_endpoint = ($page - ($margin_display + ($margin_display - ($total_page - ($page-1)))));
	$end_endpoint = $total_page;
} else if($start_endpoint >= 1 and $end_endpoint <= $total_page) {
	$start_endpoint = $page - $margin_display;
	$end_endpoint = $page + $margin_display;
} else {
	$start_endpoint = 1;
	$end_endpoint = $total_page;
}


get_header(); 

?>


<div class="biucg-main-content-wrapper"> 
	<form action="" method="post">
	<div class="biucg-heading-toggle">
		<div class="biucg-category">
			<a href="?type=all"><button type="button" class="<?php if($type == "all") echo 'active'; ?> biucg-button">All</button></a>
			<a href="?type=article"><button type="button" class="<?php if($type == "article") echo 'active'; ?>biucg-button">Article</button></a>
			<a href="?type=video"><button type="button" class="<?php if($type == "video") echo 'active'; ?>biucg-button">Video</button></a>
		</div>
		<div class="biucg-search">
			
			<div class="biucg-search-box">
				
					<input type="text" name="query" placeholder="Search..." value="<?php echo $keyword; ?>">
					<button type="submit"><span class="dashicons dashicons-search"></span></button>
				
			</div>
			
			<?php if(is_user_logged_in() and (get_option('biugc-upload-visibility-article') == 1 or get_option('biugc-upload-visibility-video') == 1)) { ?>
			<button type="button" class="biucg-add-content-toggle trigger-upload biucg-button">+ Upload New</button>
			<?php } ?>
		</div>
	</div>
	</form>



	<div class="biucg-panels">
		<?php 
		$detail_post_id = get_option('biugc-detail-page-id');
		$detail_url = get_permalink($detail_post_id);
		foreach ($contents as $key => $value) { 
			$avatar = get_avatar_url($value->user_id);
			$avatar = str_replace('//www','http://www',$avatar);
			$post_url = $detail_url."?id=".$value->id;

		?>
		<a href="<?php echo $post_url; ?>">
			<div class="biucg-item">
				<button class="biucg-label"><?php echo ucwords($value->content_type); ?></button>
				<div class="biucg-image-area">
					<div class="overlay"></div>
					<?php if($value->content_type == 'youtube') { ?>
						<button class="biucg-play"><span class="dashicons dashicons-controls-play"></span></button>
					<?php } ?>
					<img src="" alt="" data-src="<?php echo $value->meta_image; ?>" class="biucg-lazy-load">
				</div>
				<div class="biucg-content-area">
					<div class="biucg-title"><?php echo $value->meta_title; ?></div>
					<div class="biucg-snippet"><?php echo substr(strip_tags($value->meta_description),0,100); ?>...</div>
				</div>
				<div class="biucg-author-area">
					<div class="biucg-author">
						<div class="author-image">
							<img src="<?php echo $avatar; ?>">
						</div>
						<div class="author-name">
							<?php echo $value->display_name; ?><br>
							<span><?php echo date('F, d Y',strtotime($value->created_date)); ?></span>
						</div>
					</div>
					<div class="biucg-likes">
						<?php echo $value->vote_count; ?><br>
						<span>Likes</span>
					</div>
				</div>
			</div>
		</a>
		<?php } ?>
	</div>

	<div class="biucg-pagination"> 
		<?php if($start_endpoint < $end_endpoint) { ?>
		<ul>
			<a href="?type=<?php echo $type; ?>&nav=<?php echo $first; ?>"><li>First</li></a>
			<?php if(isset($prev)) { ?>
			<a href="?type=<?php echo $type; ?>&nav=<?php echo $prev; ?>"><li>Prev</li></a>
			<?php } ?>

			<?php for($i = $start_endpoint; $i <= $end_endpoint; $i++) { ?>
			<a href="?type=<?php echo $type; ?>&nav=<?php echo $i; ?>"><li <?php if($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></li></a>
			<?php } ?>

			<?php if(isset($next)) { ?>
			<a href="?type=<?php echo $type; ?>&nav=<?php echo $next; ?>"><li>Next</li></a>
			<?php } ?>
			<a href="?type=<?php echo $type; ?>&nav=<?php echo $last; ?>"><li>Last</li></a>
		</ul>
		<?php } ?>
	</div>
</div>
	


<?php get_footer(); ?>