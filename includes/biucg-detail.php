<?php
require_once( ABSPATH . 'wp-admin/includes/image.php' );

$save = NULL; 
$msg = "";

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

if($_POST and isset($_POST['content_id'])) {
	$id = $_POST['content_id'];
	$meta_title = $_POST['meta_title'];
	$meta_description = $_POST['meta_description'];
	$meta_image = (isset($_FILES['meta_image'])) ? $_FILES['meta_image'] : NULL;
	$url_image = "";

	if(isset($meta_image)) {
		$i = 1;
		$wordpress_upload_dir = wp_upload_dir();
		$new_file_path = $wordpress_upload_dir['path'] . '/' . $meta_image['name'];
		$new_file_mime = forge_mime_type($meta_image['naame']);//mime_content_type( $meta_image['tmp_name'] );
		
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
		}
		 
	}

	if($save == NULL) {
		$datas = array(
					'meta_title' => $meta_title,
					'meta_description' => $meta_description
				 );

		if(!empty($url_image)) {
			$datas['meta_image'] = $url_image;
		}

		$set = $query->update_content($id,$datas);

		if($set) {
			$content = $query->get_row($_GET['id']);
			$msg = "Your changes have been saved, Thank you";
			$save = TRUE;
		} else {
			$msg = "Failed to save your changes, try again later !";
			$save = FALSE;
		}
	}
}

?>

<div class="wrap">
	<a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=biucg-submission"><button class="common">&larr; Back to Submission List</button></a>
	<br>
	<h1 class="wp-heading-inline">Content Detail</h1>
	<br>

	<?php if(isset($save)) { ?>
	<div class="updated notice notice-warning is-dismissible">
		<p><?php echo $msg; ?></p>
	</div>
	<?php } ?>

	<form action="" method="post" enctype="multipart/form-data">
		<input type="hidden" name="content_id" value="<?php echo $content[0]->id; ?>">
		<table class="form-table">
			<tr>
				<th scope="row">URL</th>
				<td>
					<input type="text" name="youtubekey" class="regular-text" value="<?php echo ($content[0]->content_type == 'youtube') ? 'https://youtube.com/watch?v='.$content[0]->url : $content[0]->url; ?>" style="width: 60%" readonly>
				</td>
			</tr>
			<tr>
				<th scope="row">Title</th>
				<td>
					<input type="text" name="meta_title" class="regular-text" value="<?php echo $content[0]->meta_title; ?>" style="width: 60%">
				</td>
			</tr>
			<tr>
				<th scope="row">Image Thumbnail</th>
				<td>
					<img src="<?php echo $content[0]->meta_image; ?>" width="300">
					<br>
					<input type="file" name="meta_image">
				</td>
			</tr>
			<tr>
				<th scope="row">Snippet</th>
				<td>
					<textarea rows="7" style="width: 60%" name="meta_description"><?php echo $content[0]->meta_description; ?></textarea>
				</td>
			</tr>
			
			
		</table>
		
		<p class="submit">
			<input type="hidden" name="submit_type" value="general">
			<button class="button button-primary" type="submit">Save Changes</button>
			<?php if($content[0]->status == 2) { ?>
			<button class="button button-primary show-approve" data-id="<?php echo $content[0]->id; ?>" type="button">Approve</button>
			<?php } ?>
			<button class="button button-danger remove-item" type="button" data-id="<?php echo $content[0]->id; ?>">Remove</button>
		</p>

	</form>
</div>