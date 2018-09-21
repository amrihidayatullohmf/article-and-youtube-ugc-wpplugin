<?php
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

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'general';
$subtab = (isset($_GET['subtab'])) ? $_GET['subtab'] : 'approval';

$url_stylesheet = plugins_url( '../css/biugc-stylesheet-custom.css', __FILE__ );
$stylesheet = url_get_contents($url_stylesheet);

$email_template = "";
if($subtab == 'approval' or $subtab == 'rejection') {
	$template_url = plugins_url( '../views/email-'.$subtab.'.php', __FILE__ );
	$email_template = url_get_contents($template_url);
}

$wp_pages = get_pages();
$post_result = array();

if($_POST) {
	if($_POST['submit_type'] == 'stylesheet') {
		$new_stylesheet = $_POST['stylesheet'];
		$save = file_put_contents(BIUCG_PLUGIN_DIR.'css/biugc-stylesheet-custom.css',$new_stylesheet);
		if(isset($save) and $save != FALSE) {
			$stylesheet = $new_stylesheet;
			$post_result = array(
								'status' => 'success',
								'message' => 'Changes has been successfully saved, thank you !'
						   );
		} else {
			$post_result = array(
								'status' => 'error',
								'message' => 'Fail to save changes, file that you tried to modify doesnt seems writable'
						   );
		}
	} else if($_POST['submit_type'] == 'email-template') {
		$new_template = $_POST['template'];
		$type_template = $_POST['type_template'];
		if($type_template == 'approval' or $type_template == 'rejection') {
			$template_url =  BIUCG_PLUGIN_DIR.'views/email-'.$type_template.'.php';
			$save = file_put_contents($template_url,$new_template);
		
			if(isset($save) and $save != FALSE) {
				$email_template = $new_template;
				$post_result = array(
									'status' => 'success',
									'message' => 'Changes has been successfully saved, thank you !'
							   );
			} else {
				$post_result = array(
									'status' => 'error',
									'message' => 'Fail to save changes, file that you tried to modify doesnt seems writable'
							   );
			}
		} else {
			$post_result = array(
									'status' => 'error',
									'message' => 'Fail to save changes, missing parameter !'
							   );
		}
	
	} else if($_POST['submit_type'] == 'general') {

		$youtubekey = $_POST['youtubekey'];
		$limitperpage = $_POST['limitperpage'];
		$limittoplist = $_POST['limittoplist'];
		$votevisible = isset($_POST['votevisible']) ? 1 : 0;
		$videovisible = isset($_POST['contentvisibilityvideo']) ? 1 : 0;
		$articlevisible = isset($_POST['contentvisibilityarticle']) ? 1 : 0;

		update_option('biugc-youtube-key',$youtubekey);
        update_option('biugc-limit-per-page',$limitperpage);
        update_option('biugc-top-display-limit',$limittoplist);
        update_option('biugc-voting-visibility',$votevisible);
        update_option('biugc-upload-visibility-video',$videovisible);
        update_option('biugc-upload-visibitity-article',$articlevisible);

		$post_result = array(
									'status' => 'success',
									'message' => 'Changes has been successfully saved, thank you !'
							   );

	} else if($_POST['submit_type'] == 'routing') {

		$landingpage = $_POST['landingpage'];
		$detailpage = $_POST['detailpage'];
		$ownpage = $_POST['ownpage'];

		update_option('biugc-landing-page-id',$landingpage);
        update_option('biugc-detail-page-id',$detailpage);
        update_option('biugc-own-page-id',$ownpage);

		$post_result = array(
									'status' => 'success',
									'message' => 'Changes has been successfully saved, thank you !'
							   );

	}
}

?>


<div class="wrap">



	<h1>Settings</h1>

	<h2 class="nav-tab-wrapper">
		
		<a href="<?php echo admin_url('admin.php?page=biucg-settings&tab=general'); ?>" class="nav-tab <?php if($tab == 'general') echo 'nav-tab-active'; ?>">General</a>
		<a href="<?php echo admin_url('admin.php?page=biucg-settings&tab=routing'); ?>" class="nav-tab <?php if($tab == 'routing') echo 'nav-tab-active'; ?>">Pages</a>
		<a href="<?php echo admin_url('admin.php?page=biucg-settings&tab=email-template'); ?>" class="nav-tab <?php if($tab == 'email-template') echo 'nav-tab-active'; ?>">E-Mail Template</a>
		<a href="<?php echo admin_url('admin.php?page=biucg-settings&tab=stylesheet'); ?>" class="nav-tab <?php if($tab == 'stylesheet') echo 'nav-tab-active'; ?>">Stylesheet</a>
		<a href="<?php echo admin_url('admin.php?page=biucg-settings&tab=short-code'); ?>" class="nav-tab <?php if($tab == 'short-code') echo 'nav-tab-active'; ?>">Short Codes</a>
	</h2>


	<?php if(count($post_result) > 0 and isset($post_result['message'])) { ?>
	<div class="updated notice notice-warning is-dismissible">
		<p><?php echo $post_result['message']; ?></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text">Dismiss</span>
		</button>
	</div>
	<?php } ?>

	

	<?php if($tab == 'short-code') { ?>

	<table class="form-table">
		<tr>
			<td>
				<p>Use these short codes on your Pages. It will be automatically replaced into BI User Generated Content's display</p>
			</td>
		</tr>
		
		<tr>
			<td>
				<input type="text" name="test" class="regular-text customize-code" value="[BIUGC-TOP-VIDEO|ARTICLE|ALL]" readonly="readonly">
				<p class="description">Short Code will be replaced into top list container, use <span class="code">BIUGC-TOP-ALL</span> to display Video and Article,<br><span class="code">BIUGC-TOP-VIDEO</span> for only Video and <span class="code">BIUGC-TOP-ARTICLE</span> for only article</p>
			</td>
		</tr>
		<tr>
			<td>
				<input type="text" name="test" class="regular-text customize-code" value="[BIUGC-UPLOAD-BUTTON]" readonly="readonly">
				<p class="description">Short Code will be replaced into BI User Generated Content upload button</p>
			</td>
		</tr>
	</table>

	<?php } else if($tab == 'general') { ?>

	<form action="" method="post">
		
		<table class="form-table">
			
			<tr>
				<th scope="row">Youtube API Key</th>
				<td>
					<input type="text" name="youtubekey" class="regular-text" value="<?php echo get_option('biugc-youtube-key'); ?>">
					<p class="description">For fetching Youtube Data (more info <a href="https://developers.google.com/youtube/v3/docs">https://developers.google.com/youtube/v3/docs</a>)</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Pagination Limit per Page</th>
				<td>
					<input type="number" min='1' name="limitperpage" class="regular-text" value="<?php echo get_option('biugc-limit-per-page'); ?>">
					<p class="description">Number of content that will be displayed per page</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Top List Display Limit</th>
				<td>
					<input type="number" min='1' name="limittoplist" class="regular-text" value="<?php echo get_option('biugc-top-display-limit'); ?>">
					<p class="description">Number of content that will be displayed on top list</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Like Visibility</th>
				<td>
					<input type="checkbox" name="votevisible" class="regular-text" value="1" <?php if(get_option('biugc-voting-visibility') == 1) echo 'checked'; ?>>
					Enable Like
				</td>
			</tr>
			<tr>
				<th scope="row">Upload Visibility</th>
				<td>
					<input type="checkbox" name="contentvisibilityvideo" class="regular-text" value="1"  <?php if(get_option('biugc-upload-visibility-video') == 1) echo 'checked'; ?>>
					Enable Video
					&nbsp;&nbsp;
					<input type="checkbox" name="contentvisibilityarticle" class="regular-text" value="1"  <?php if(get_option('biugc-upload-visibitity-article') == 1) echo 'checked'; ?>>
					Enable Article
				</td>
			</tr>
			
		</table>
		
		<p class="submit">
			<input type="hidden" name="submit_type" value="general">
			<button class="button button-primary" type="submit">Save Changes</button>
		</p>

	</form>

	<?php } else if($tab == 'routing') { ?>

		<?php //var_dump($wp_pages); ?>
	<form action="" method="post">
		
		<table class="form-table">
			
			<tr>
				<th scope="row">Landing Page</th>
				<td>
					<select name="landingpage" class="select-long">
						<option>- Select Page -</option>
						<?php foreach ($wp_pages as $key => $value) { ?>
						<option value="<?php echo $value->ID; ?>" <?php if($value->ID == get_option('biugc-landing-page-id')) echo 'selected'; ?>><?php echo $value->post_title; ?></option>
						<?php } ?>
					</select>
					
				</td>
			</tr>
			<tr>
				<th scope="row">Detail Page</th>
				<td>
					<select name="detailpage" class="select-long">
						<option>- Select Page -</option>
						<?php foreach ($wp_pages as $key => $value) { ?>
						<option value="<?php echo $value->ID; ?>" <?php if($value->ID == get_option('biugc-detail-page-id')) echo 'selected'; ?>><?php echo $value->post_title; ?></option>
						<?php } ?>
					</select>
					
				</td>
			</tr>

			<tr>
				<th scope="row">User Own Page</th>
				<td>
					<select name="ownpage" class="select-long">
						<option>- Select Page -</option>
						<?php foreach ($wp_pages as $key => $value) { ?>
						<option value="<?php echo $value->ID; ?>" <?php if($value->ID == get_option('biugc-own-page-id')) echo 'selected'; ?>><?php echo $value->post_title; ?></option>
						<?php } ?>
					</select>
					
				</td>
			</tr>
			
		</table>
		
		<p class="submit">
			<input type="hidden" name="submit_type" value="routing">
			<button class="button button-primary" type="submit">Save Changes</button>
		</p>

	</form>

	<?php } else if($tab == 'email-template') { ?>

		

	<form action="" method="post">
		<table class="form-table">
			
			<tr>
				<td>
					<h3 class="nav-tab-wrapper">
						<a href="<?php echo admin_url('admin.php?page=biucg-settings&tab=email-template&subtab=approval'); ?>" class="nav-tab <?php if($subtab == 'approval') echo 'nav-tab-active'; ?>">Approval Template</a>
						<a href="<?php echo admin_url('admin.php?page=biucg-settings&tab=email-template&subtab=rejection'); ?>" class="nav-tab <?php if($subtab == 'rejection') echo 'nav-tab-active'; ?>">Rejection Template</a>
					</h3>
					<textarea class="full-text email-template-text" id="email-approval" name="template"><?php echo $email_template; ?></textarea>
					<input type="hidden" name="type_template" value="<?php echo $subtab; ?>">
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="submit_type" value="email-template">
					<button class="button button-primary" type="submit">Save Changes</button>
					
				</td>
			</tr>
		</table>
		<br>
	</form>


	<?php } else if($tab == 'stylesheet') { ?>

	<form action="" method="post">
		<table class="form-table">
			<tr>
				<td>
					<textarea class="full-text" name="stylesheet"><?php echo $stylesheet; ?></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="submit_type" value="stylesheet">
					<button class="button button-primary" type="submit">Save Changes</button>
					
				</td>
			</tr>
		</table>
		<br>
	</form>

	<?php } ?>




</div>