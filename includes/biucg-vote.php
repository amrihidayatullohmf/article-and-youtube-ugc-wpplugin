<div class="wrap">

	<?php
	$id = $_GET['id'];
	$table->set_parent_id($id);

	$k = "";
	if($_POST) {
		$k = $_POST['s'];
		$table->set_keyword($k); 
	}

	if(isset($_GET['orderby']) and isset($_GET['order'])) {
		$table->set_sortparam($_GET['orderby'],$_GET['order']);	
	}

	$table->get_current_page();
	$table->prepare_items();
	
	?>

	<a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=biucg-submission"><button class="common">&larr; Back to Submission List</button></a>
	<br>
	<h1 class="wp-heading-inline"><?php echo $content[0]->vote_count; ?> User Likes</h1>
	<br>
	
	
	<div class="header-area-table">
		<div class="category-area">
			<div class="title">
				<span class="dashicons dashicons-text"></span>&nbsp;<?php echo $content[0]->meta_title; ?><br>
				<small><span class="dashicons dashicons-admin-users"></span>&nbsp; Posted by <b><?php echo $content[0]->display_name; ?></b> on <b><?php echo date('F, d Y H:i',strtotime($content[0]->created_date)); ?></b></small>
			</div>
		</div>
		<div class="search-area">
			<form method="post">
			  	<input type="hidden" name="page" value="my_list_test" />
				<p class="search-box">
					<label class="screen-reader-text" for="search_id-search-input">
					search:</label> 
					<input id="search_id-search-input" type="text" name="s" value="<?php echo $k; ?>" /> 
					<input id="search-submit" class="button" type="submit" name="" value="search" />
				</p>
			</form>
		</div>
	</div>

	<input type="hidden" id="tabletype" value="voter">
	<input type="hidden" id="parent_id" value="<?php echo $id; ?>">

	<?php 
	$table->display(); 
	?>

</div>