<?php 
global $wpdb;

$uid = (is_user_logged_in()) ? get_current_user_id() : 0;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$contents = $wpdb->get_results("SELECT u.display_name, u.user_email, c.* FROM wp_biucg_user_contents c LEFT JOIN wp_users u ON c.user_id = u.ID WHERE c.status != 0 AND c.id = ".$id);

if(count($contents) == 0) {
	header('location:'.site_url());
} 

if($contents[0]->status == 2 and $contents[0]->user_id != $uid) {
	header('location:'.site_url());
}

$check_like = $wpdb->get_results("SELECT * FROM wp_biucg_user_vote WHERE user_id = '".$uid."' AND obj_id = '".$contents[0]->id."' AND status = 1");
$has_liked = (count($check_like) > 0) ? TRUE : FALSE;

$avatar = get_avatar_url($contents[0]->user_id);
$avatar = str_replace('//www','http://www',$avatar);
$back_url = get_permalink(get_option('biugc-landing-page-id'));


get_header(); 
?>

<?php if($contents[0]->content_type == 'youtube') { ?>

<div class="biucg-main-content-wrapper"> 
	<a href="<?php echo $back_url; ?>"><button>&larr; Back</button></a>
	<br><br>
	<iframe class="biucg-youtube-embed-frame" src="https://www.youtube.com/embed/<?php echo $contents[0]->url; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
	<div class="biucg-snippet-area">
		<div class="text-area">
			<div class="title"><?php echo $contents[0]->meta_title; ?></div>
			<div class="desc"><?php echo $contents[0]->meta_description; ?></div>
		
			<div class="author-area">
				<div class="avatar-area">
					<img src="<?php echo $avatar; ?>">
				</div>
				<div class="name-area">
					<?php echo $contents[0]->display_name; ?><br>
					<span><?php echo date('F, d Y',strtotime($contents[0]->created_date)); ?></span>
				</div>
			</div>
		</div>
		<div class="like-area">
			<?php if(is_user_logged_in() and get_option('biugc-voting-visibility') == 1 and $contents[0]->status == 1) { ?>
			<div class="number"><?php echo $contents[0]->vote_count; echo ($contents[0]->vote_count == 1) ? ' Like' : ' Likes'; ?> </div>
			<button class="<?php if($has_liked) echo 'on'; ?> biucg-trigger-like" data-id="<?php echo $contents[0]->id; ?>"><span class="dashicons dashicons-thumbs-up"></span></button>
			<?php } ?>
		</div>
	</div>
</div>

<?php } else { ?>

<style type="text/css">
.site-content {
	padding-top : 0;
	padding-left : 0;
	padding-right : 0;
	padding-bottom : 0;

}
</style>
<div class="biucg-floating-bar">
	<div class="biucg-snippet-area">
		<div class="text-area">
			<div class="author-area">
				<a href="<?php echo $back_url; ?>"><button>&larr; Back</button></a>
				<div class="avatar-area">
					<img src="<?php echo $avatar; ?>">
				</div>
				<div class="name-area">
					<?php echo $contents[0]->display_name; ?><br>
					<span><?php echo date('F, d Y',strtotime($contents[0]->created_date)); ?></span>
				</div>
			</div>
		</div>
		<div class="like-area">
			<?php if(is_user_logged_in() and get_option('biugc-voting-visibility') == 1 and $contents[0]->status == 1) { ?>
			<div class="number"><?php echo $contents[0]->vote_count; echo ($contents[0]->vote_count == 1) ? ' Like' : ' Likes'; ?> </div>
			<button class="<?php if($has_liked) echo 'on'; ?> biucg-trigger-like" data-id="<?php echo $contents[0]->id; ?>"><span class="dashicons dashicons-thumbs-up"></span></button>
			<?php } ?>
		</div>
	</div>
</div>
<iframe src="<?php echo $contents[0]->url; ?>" class="biucg-iframe"></iframe>

<?php } ?>

<?php get_footer(); ?>

