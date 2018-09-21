<div class="wrap">

	<h1>Export Submission</h1>

	<form action="<?php echo admin_url("admin.php?page=biucg-export"); ?>" method="post">
		
		<table class="form-table">
			
			<tr>
				<th scope="row">Filename</th>
				<td>
					<input type="text" name="file_name" class="regular-text" value="<?php echo "submission-".date('Ymd').".csv"; ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">Status </th>
				<td>
					<select name="state" class="select-long">
						<option value="all">All</option>
						<option value="1">Approved</option>
						<option value="2">Rejected</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">Content Type</th>
				<td>
					<select name="type" class="select-long">
						<option value="all">All</option>
						<option value="youtube">Video</option>
						<option value="article">Article</option>
					</select>
				</td>
			</tr>
			
		</table>
		
		<p class="submit">
			<button class="button button-primary" type="submit">Export CSV</button>
		</p>

	</form>

</div>