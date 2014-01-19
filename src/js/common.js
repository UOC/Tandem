	
$(document).ready(function() {

	/* Alerts */
	$('.alert').on('click','.close', function(ev){
		ev.preventDefault();
	    $(this).parent().fadeOut(300);
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

});