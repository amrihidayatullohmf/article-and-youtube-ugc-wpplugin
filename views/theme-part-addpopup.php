<?php if(is_user_logged_in()) { ?>

<div class="biucg-form-hidden">
<form method="post" enctype="multipart/form-data" id="imageUploader" action="<?php echo admin_url('admin-ajax.php'); ?>">
	<input type="hidden" name="action" value="biucg_uploadImageHandler">
	<input type="file" name="meta_image" accept=".jpg,.png,.gif" id="biucgmetaimage">
</form>
</div>

<div class="biucg-black-bg" id="biucg-black-bg"></div>
<div class="biucg-popup-add" id="biucg-popup-add">
	<div class="biucg-popup-wrapper">
		<div class="biucg-popup-box">
			<div class="error-notif" id="error-notif">Lorem Ipsum Dolor sit Amet</div>
			<button type="button" class="retrieve-loader" id="retrieve-loader"><img src="<?php echo plugins_url( 'biucg/images/loading.gif'); ?>" width="30"></button>
			<div class="header-area" id="popheaderarea">
				<div class="tab-area">
					<?php if(get_option('biugc-upload-visibitity-article') == 1) { ?><button type="button" class="biucg-tab-action active" data-rel="#biucg-article">Article</button><?php } ?>
					<?php if(get_option('biugc-upload-visibility-video') == 1) { ?><button type="button" class="biucg-tab-action" data-rel="#biucg-video">Video</button><?php } ?>
				</div>
				<button class="biucg-close-popup" type="button">&times;</button>
			</div>
			<div class="form-area-content">
				<?php if(get_option('biugc-upload-visibitity-article') == 1) { ?>
				<div class="biucg-form-segment active" id="biucg-article">
					<form class="biucg-form-trigger" method="post">
						<input type="hidden" name="id" id="meta_id">
						<input type="hidden" class="skip-clear" name="action" value="biucg_submissionSaveHandler">
						<input type="hidden" class="skip-clear" name="type" value="article">
						<div class="biucg-field">
							<input type="text" name="url" id="web_url" placeholder="http://url-to-your-article.com" class="special">
							<div class="biucg-helper">URL to your article site or blog</div>
						</div>
						<div class="biucg-field">
							<div class="biucg-image-uploader" id="meta_image">
								<button type="button"><span class="dashicons dashicons-upload"></span></button>
								<img src="<?php echo plugins_url( 'biucg/images/dummy-image-square.jpg'); ?>" alt="<?php echo plugins_url( 'biucg/images/dummy-image-square.jpg'); ?>">
								<input type="hidden" name="meta_image">
							</div>
						</div>
						<div class="biucg-field">
							<input type="text" name="meta_title" id="meta_title" placeholder="Title">
						</div>
						<div class="biucg-field">
							<textarea name="meta_description" id="meta_description" placeholder="Short Description"></textarea>
						</div>
						<div class="biucg-field">
							<button type="submit">SUBMIT</button>
						</div>
					</form>
				</div>
				<?php } ?>
				
				<?php if(get_option('biugc-upload-visibility-video') == 1) { ?>
				<div class="biucg-form-segment" id="biucg-video">
					<form class="biucg-form-trigger" method="post">
						<input type="hidden" name="id" id="meta_id_youtube">
						<input type="hidden" class="skip-clear" name="action" value="biucg_submissionSaveHandler">
						<input type="hidden" name="youtube_id" value="" id="youtube_id">
						<input type="hidden" class="skip-clear" name="type" value="youtube">
						<div class="biucg-field">
							<input type="text" name="url"  placeholder="https://youtube.com/watch?v=aZsh83dN" class="special" id="youtube_url">
							<div class="biucg-helper">URL to your youtube video</div>
						</div>
						<div class="biucg-field">
							<div class="biucg-image-uploader" id="youtube_image">
								<button type="button"><span class="dashicons dashicons-upload"></span></button>
								<img src="<?php echo plugins_url( 'biucg/images/dummy-image-square.jpg'); ?>" alt="<?php echo plugins_url( 'biucg/images/dummy-image-square.jpg'); ?>">
								<input type="hidden" name="meta_image">
							</div>
						</div>
						<div class="biucg-field">
							<input type="text" name="meta_title" placeholder="Title" id="youtube_title">
						</div>
						<div class="biucg-field">
							<textarea name="meta_description" placeholder="Short Description" id="youtube_description"></textarea>
						</div>
						<div class="biucg-field">
							<button type="submit">SUBMIT</button>
						</div>
					</form>
				</div>
				<?php } ?>

				<div class="biucg-form-segment" id="biucg-success">
					<div class="success-area">
						<button class="icon"><span class="dashicons dashicons-yes"></span></button>
						<br>
						<div class="title">
							Yeay!
						</div>
						<div class="message" id="biucg-submission-success-message"></div>
						<div class="button-area">
							<a href="<?php echo get_permalink(get_option('biugc-own-page-id')); ?>">
								<button>Go To My Submission</button>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>