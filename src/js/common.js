
var filtered_tandems = false;

apply_tandem_filters = function(){
	
	if($('#user_selected').val()!='0'){
		filtered_tandems = true;
		$( "#applied-criteria ul" ).append('<li>' + $('#user_selected').data('title') + ': <em>' + $('#user_selected option:selected').text() + '</em></li>');
	}

	if($('#select_exercise').val()!=-1){
		filtered_tandems = true;
		$( "#applied-criteria ul" ).append('<li>' + $('#select_exercise').data('title') + ': <em>' + $('#select_exercise option:selected').text() + '</em></li>');
	}

	if($('#start_date').val()!=''){
		filtered_tandems = true;
		$( "#applied-criteria ul" ).append('<li>' + $('#start_date').data('title') + ': <em>' + $('#start_date').val() + '</em></li>');
	}

	if($('#finish_date').val()!=''){
		filtered_tandems = true;
		$( "#applied-criteria ul" ).append('<li>' + $('#finish_date').data('title') + ': <em>' + $('#finish_date').val() + '</em></li>');
	}

	if($('#finished').val()!=-1){
		filtered_tandems = true;
		$( "#applied-criteria ul" ).append('<li>' + $('#finished').data('title') + ': <em>' + $('#finished option:selected').text() + '</em></li>');
	}

	if(filtered_tandems) $( '#applied-criteria' ).show();
	else $( '#applied-criteria' ).hide();
	
};

$(document).ready(function() {

	/* Modals */
	if($.modal!=undefined){
		$.extend($.modal.defaults, {
			closeHTML : '<a class="modalCloseImg simplemodal-close" title="Close"></a>',
			opacity:70,
	        escClose : true,
	        overlayClose : true,
			onShow : function(d) {
			   d.container.css('height', 'auto');
			   d.origHeight = 0;
			   $.modal.setContainerDimensions();
			   $.modal.setPosition();
			},
			onOpen: function (d) {
			    d.data.show();
				d.overlay.fadeIn(300, function () {
					d.container.fadeIn(300);
				});
			},
			onClose: function (d) {
				var s = this;
				d.container.fadeOut(300, function () {
					d.overlay.fadeOut(300, function () {
						s.close();
					});
				});
			}
		});
	}

	

	/* Alerts */

	$('.modal_bi').click(function(ev){
		ev.preventDefault();
		$.modal( $($(this).attr('href')) );
	});

	/* Duration Expand */

	$('.duration .expand').click(function(ev){
		ev.preventDefault();
		var self = this;
		$($(this).attr('href')).toggle( 0 , function() {
	    	if($(this).is(":visible")){
	    		$(self).addClass('open');
	    	}else{
	    		$(self).removeClass('open');
	    	}
		});
	});

	/* Custom File Input */

	$('.attach-input-file').change(function(ev){
		var val = $(this).val().split('\\').pop();
		var $lbl = $(this).closest('.frm-group').find('.frm-label');
		if(val!=''){
			$lbl.html($lbl.data('title-none'));
		}else{
			$lbl.html($lbl.data('title-file'));
		}
		$(this).closest('.attach-input').find('.attach-input-text').val(val).removeClass('none');
	});

	$('.attach-input-text').focus(function(ev){
		ev.preventDefault();
		$(this).blur();
		$(this).closest('.attach-input').find('.attach-input-file').trigger('click');
	});

	/* Exercises */

	$('#btn-new-exercise').click(function(ev){
		var self = this;
		ev.preventDefault();
	    $( "#frm-new-exercise" ).toggle( 0 , function() {
	    	if($(this).is(":visible")){
	    		$(self).addClass('open');
	    		$( "#frm-edit-exercise" ).hide();
	    		$( "#btn-edit-exercise" ).removeClass("open");
	    	}else{
	    		$(self).removeClass('open');
	    	}
		});
	});

	$('#btn-edit-exercise').click(function(ev){
		var self = this;
		ev.preventDefault();
	    $( "#frm-edit-exercise" ).toggle( 0 , function() {
	    	if($(this).is(":visible")){
	    		$(self).addClass('open');
	    		$( "#frm-new-exercise" ).hide();
	    		$( "#btn-new-exercise" ).removeClass("open");
	    	}else{
	    		$(self).removeClass('open');
	    	}
		});
	});

	/* Search Criteria */

	apply_tandem_filters();

	$('#btn-search-criteria').click(function(ev){
		var self = this;
		ev.preventDefault();
	    $( "#search-criteria" ).toggle( 0 , function() {
	    	if($(this).is(":visible")){
	    		$( "#applied-criteria" ).hide();
	    		$(self).addClass('open');
	    	}else{
	    		if (filtered_tandems) $( "#applied-criteria" ).show();
	    		$(self).removeClass('open');
	    	}
		});
	});

});