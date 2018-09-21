$(document).ready(function(){
	$(".biucg-tab-action").click(function(){
		var rel = $(this).data('rel');

		$(".biucg-tab-action").removeClass('active');
		$(".biucg-form-segment").removeClass('active');

		$(this).addClass('active');
		$(rel).addClass('active');
		$(rel).find('.special').focus();
	});

	function showErrorPopup(msg,duration) {
		$("#retrieve-loader").hide();
		$("#error-notif").html(msg);
		$("#error-notif").slideDown(100).delay(duration).slideUp(100);
	}

	$("#youtube_url").blur(function(){
		if($(this).val() == "") {
			return false;
		}
		$("#retrieve-loader").show();
		$.ajax({
			type : 'POST',
			url : biucg_ajaxurl,
			data : {
				action : 'biucg_fetchYoutubeData',
				youtube_url : $(this).val()
			},
			dataType : 'json',
			success : function(d) {
				console.log(d);
				if(typeof d.pageInfo != undefined && d.pageInfo.totalResults > 0) {
					$("#youtube_id").val(d.items[0].id);
					$("#youtube_title").val(d.items[0].snippet.title);
					$("#youtube_description").val(d.items[0].snippet.description);
					$("#youtube_image").find('input').val(d.items[0].snippet.thumbnails.high.url);
					$("#youtube_image").find('img').prop('src',d.items[0].snippet.thumbnails.high.url);
					$("#retrieve-loader").hide();
				} else {
					showErrorPopup('Ops! URL video is not valid !',2000);
				}
			},
			error : function(e) {
				console.log(e);
				showErrorPopup('Ops! Unknown error occured !',2000);
			}
		});
	});

	$("#web_url").blur(function(){
		if($(this).val() == "") {
			return false;
		}
		$("#retrieve-loader").show();
		$.ajax({
			type : 'POST',
			url : biucg_ajaxurl,
			data : {
				action : 'biucg_fetchUrlMetaData',
				web_url : $(this).val()
			},
			dataType : 'json',
			success : function(d) {
				if(d.code == 200) {
					$("#meta_title").val(d.tags.title);
					$("#meta_description").val(d.tags.description);
					$("#meta_image").find('input').val(d.tags.image);
					$("#meta_image").find('img').prop('src',d.tags.image);
					$("#retrieve-loader").hide();
				} else {
					showErrorPopup('Ops! URL you\'ve provided doesnt seem having a Meta Information, however you still can to manualy set content',5000);
				}
				console.log(d);
			},
			error : function(e) {
				console.log(e);
				showErrorPopup('Ops! Unknown error occured !',2000);
			}
		});
	});

	var _self_container;

	$(".biucg-image-uploader").click(function(){
		_self_container = $(this); 
		$("#biucgmetaimage").trigger('click');
		$("#biucgmetaimage").change(function(){
			var val = $(this).val();
			if(val == '') {
				return false;
			}

			$("#imageUploader").ajaxForm({
				beforeSubmit: function(formData,jqForm,options) {
					$("#retrieve-loader").show();
		        },
		        success: function(d,s) {
		            console.log(d);
		            var d = JSON.parse(d);

		            if(d.code == 200) {
		            	_self_container.find('img').prop('src',d.url);
		            	_self_container.find('input').val(d.url);
		            	$("#retrieve-loader").hide();
		            } else {
		            	showErrorPopup(d.msg,2000);
		            }
		            
		        },
		        error: function(e) {
		            console.log(e);
		            showErrorPopup('Ops! Unknown error occured !',2000);
		        }		
	            
	        }).submit();

	        return false;			
		});

		return false;
	});

	$(".biucg-form-trigger").submit(function(){
		$("#retrieve-loader").show();

		$.ajax({
			type : 'POST',
			data : $(this).serialize(),
			url : biucg_ajaxurl,
			dataType : 'json',
			success : function(d) {
				console.log(d);
				if(d.code == 200) {
					$("#biucg-submission-success-message").html(d.msg);
					$(".biucg-form-segment").removeClass('active');
					$("#popheaderarea").hide();
					$("#biucg-success").addClass('active');
					$("#retrieve-loader").hide();
				} else {
					showErrorPopup(d.msg,2000);
				}
			}, 
			error : function(e) {
				console.log(e);
		        showErrorPopup('Ops! Unknown error occured !',2000);
			}
		});
		return false;
	});

	$(".trigger-upload").click(function(){
		$(".biucg-tab-action").removeClass('active');
		$(".biucg-form-segment").removeClass('active');
		$(".biucg-tab-action:first-child").addClass('active');
		$(".biucg-form-segment:first-child").addClass('active');
	
		$("#biucg-black-bg").fadeIn(300);
		setTimeout(function(){
			$("#biucg-popup-add").fadeIn(200);
		},200);
	});

	$(".biucg-trigger-like").click(function(){
		var p = $(this).parent();
		var objid = $(this).data('id');
		var temp = p.find('.number').html();

		if($(this).hasClass('on')) {
			p.find('.number').html('Unliking...');
		} else {
			p.find('.number').html('Liking...');
		}

		$.ajax({
			type : 'POST',
			url : biucg_ajaxurl,
			data : {
				action : 'biucg_likeHandler',
				obj_id : objid
			},
			dataType : 'json',
			success : function(d) {
				console.log(d);
				if(d.code == 200) {
					var likes = (d.total > 1) ? 'Likes' : 'Like';
					p.find('.number').html(d.total+" "+likes);

					if(d.type == 'like') {
						p.find('button').addClass('on');
					} else {
						p.find('button').removeClass('on');
					}
				} else {
					p.find('.number').html(temp);
				}
			},
			error : function(e) {
				console.log(e);
				p.find('.number').html(temp);
			}
		});
		return false;
	});

	$(".biucg-close-popup").click(function(){
		$(".biucg-tab-action").removeClass('active');
		$(".biucg-form-segment").removeClass('active');
		$(".biucg-tab-action:first-child").addClass('active');
		$(".biucg-form-segment:first-child").addClass('active');

		$(".biucg-form-segment").find('img').each(function(){
			$(this).prop('src',$(this).prop('alt'));
		});
		$("#biucg-popup-add").find('input').each(function() {
			if(!$(this).hasClass('skip-clear')) {
				$(this).val('');
			}
		});
		$("#biucg-popup-add").find('textarea').each(function() {
			$(this).val('');

		});

		$("#biucg-popup-add").fadeOut(300);
		setTimeout(function(){
			$("#biucg-black-bg").fadeOut(300);
		},200);
	});

	$(".biucg-remove-item").click(function(){
		var ids = $(this).data('id');
		swal({
          title: "Are you sure?",
          text: 'You are going to remove this content',
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, I Confirm!",
          cancelButtonText: "No, Cancel!",
          closeOnConfirm: true,
          closeOnCancel: true
        },
        function(isConfirm){
          if (isConfirm) {
          	$.ajax({
          		type : 'POST',
          		url : biucg_ajaxurl,
          		data : {
          			action  : "biucg_handleAjaxRemove",
          			ids : ids
          		},
          		dataType : 'json',
          		success : function(d) {
          			console.log(d);
          			if(d.code == 200) {
          				swal('Yeay!',d.msg,'success');
          				$("#biucg-item-"+ids).remove();
          			} else {
          				swal('Ops',d.msg,'error');
          			}
          		},
          		error : function(e) {
          			console.log(e);
          			swal('Ops','Unknown error occured !','error');
          		}
          	});
          }
      	});

      	return false;
	});

	$(".biucg-edit-item").click(function(){
		var id = $(this).data('id');
		var type = $(this).data('type');
		var url = $(this).data('url');
		var title = $(this).data('title');
		var description = $(this).data('description');
		var image = $(this).data('image');

		if(type == 'youtube') {
			type = 'video';
		}

		$(".biucg-tab-action").removeClass('active');
		$(".biucg-form-segment").removeClass('active');

		$(".biucg-tab-action").each(function(){
			if($(this).data('rel') == "#biucg-"+type) {
				$(this).addClass('active');
			}
		});

		$("#biucg-"+type).addClass('active');

		if(type == 'video') {
			$("#youtube_id").val(url);
			$("#youtube_title").val(title);
			$("#youtube_description").val(description);
			$("#youtube_image").find('input').val(image);
			$("#youtube_image").find('img').prop('src',image);
			$("#youtube_url").val('https://youtube.com/watch?v='+url);
			$("#meta_id_youtube").val(id);
		} else {
			$("#meta_id").val(id);
			$("#web_url").val(url);
			$("#meta_title").val(title);
			$("#meta_description").val(description);
			$("#meta_image").find('input').val(image);
			$("#meta_image").find('img').prop('src',image);
		}

		$("#biucg-black-bg").fadeIn(300);
		setTimeout(function(){
			$("#biucg-popup-add").fadeIn(200);
		},200);

		return false;
	});

	$(".biucg-lazy-load").each(function(){
		$(this).on('load', function(){
			$(this).addClass('loaded');
		}).each(function() {
		  	if($(this).complete) { 
		  		$($(this)).load();
		  		
		  	}
		}).attr('src',$(this).data('src'));
	});

});
