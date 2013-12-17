

	// variables globales
	var waitingLbl = '<span>waiting</span> <img src="img/waiting.gif" alt="waiting" />';
	var completedLbl = ' <img src="img/ok.png" alt="completed" />';
	var waitTimer = 0;
	var waitTimeout = 3000;

	/* Modal Dialogs */

	// Configuración ventanas modales por defecto
	$.extend($.modal.defaults, {
		closeHTML : '',
		opacity:80,
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


	// muestra la ventana modal "waiting partner"
	function wait(id){
		$.modal( $('#modal-waiting-partner') , {
            containerId: 'waiting-container',
            opacity:0,
            position:['0', null],
            onOpen: function (d) {
		    	d.overlay.fadeIn(200, function () {
					d.data.show();
					d.container.slideDown(200);
				});
			},
            onClose:function(d){
            	var s = this;
				d.container.animate( {top:'-' + (d.container.height() + 50)}, 200, function () { s.close(); } );
            }
        });
	}

	// inicio espera partner tarea con timer
	function waitTimerTask(){
		waitTimer = window.setTimeout(partnerTimerTaskReady, waitTimeout);
	}

	// inicio espera partner paso completado
	function waitStep(i){
		//abertranb - 20120925 - If is not active you can't press 
		if (!$('#step_'+i).hasClass('active')){
		 return;	
		}
		
 			$('#step_'+i).removeClass('active').addClass('waiting').removeAttr('href').removeAttr('onclick');
			$('#step_'+i+' .lbl').html(waitingLbl);
		
	}
	
	function EndwaitStep(i){
		i--;
		$('#step_'+i).removeClass('active').removeClass('waiting').addClass('completed').removeAttr('href').removeAttr('onclick');
		$('#step_'+i+' .lbl').html(completedLbl);
		if($('#step_'+i).parent().next().length > 0) {
			$('#step_'+i).parent().next().find('a').addClass('active');
		}
	}

	// inicio espera partner último paso completado
	function waitEndStep(){
		waitTimer = window.setTimeout(partnerEndStepReady, waitTimeout);
	}

	// fin espera partner paso completado
	function partnerStepReady(){
		var step = $('#steps li .waiting');
		stepCompleted(step);
		nextStep(step);
	}

	// fin espera partner último paso completado
	function partnerEndStepReady(){
		$.modal.close();
		stepCompleted($('#steps li .waiting'));
		showSolution();
	}

	// paso en espera
	function stepWait(step){
		var lbl = step.find('.lbl');
		step.addClass('waiting');
		lbl.data('lbl',lbl.html()).html(waitingLbl);
	}

	// fin espera paso activo
	function stepReady(step){
		var lbl = step.find('.lbl');
		step.removeClass('waiting');
		lbl.html(lbl.data('lbl'));
	}

	// fin espera paso completado
	function stepCompleted(step){
		var lbl = step.find('.lbl');
		step.removeClass('active').removeClass('waiting').addClass('completed');
		lbl.html(lbl.data('lbl')).append(completedLbl);
	}

	// activa paso siguiente
	function nextStep(step){
		if(step.parent().next().length > 0) {
			step.parent().next().find('a').addClass('active');
		}
	}

	// Muestra solución
	function showSolution(step){
		$('#steps .step').hide();
		$('#timeline').hide();
		$('#steps .solution').show();
		$('#next_task').addClass('active');
		$('#content').addClass('solution-content');
	}

	// ventana modal tiempo agotado
	function theEnd(){
		if ($("#modal-end-task").length > 0){
			$.modal( $('#modal-end-task') );
			accionTimer();
		}
	}

	// evento paso común
	/*$('#steps li a').not('.end').not('.next').click(function(ev) {
		ev.preventDefault();
		if($(this).hasClass('active')){
			waitStep();
		}
	});*/

	// evento último paso
	$('#steps li a.end').click(function(ev) {
		ev.preventDefault();
		waitEndStep();
	});


	/* InfoTips */

	$('.infotip').infoTip();

	/* TimeLine */

	// Ajuste timeline (anchura máxima)
	var lwidth = $('#timeline').outerWidth() - ($('#timeline .lbl').outerWidth() + $('#timeline .clock').outerWidth()) + 5;
	var lmargin = $('#timeline .lbl').outerWidth() - 5;
	$('#timeline .linewrap').css({'width': lwidth + 'px', 'margin-left' : lmargin + 'px'});
	var timeline;
	timerOn = function(minutos,segundos){
		// Configuración timeline
		timeline = $('#timeline').timeLineClock({
			time: {hh:0,mm:parseInt(minutos),ss:parseInt(segundos)},
			onEnd: theEnd
		}); 
	}
	
	// fin espera partner tarea con timer
	function partnerTimerTaskReady(){
		$.modal.close();
		stepReady($('#steps li .waiting'));
		timeline.start();
	}


	/* Otros a$ustes */

	// IE6 & IE7 z-index hack
	if ( $.browser.msie && ($.browser.version=='6.0' || $.browser.version=='7.0')) {
		var zIndexNumber = 1000;
		$('#wrapper div').each(function() {
			$(this).css('zIndex', zIndexNumber);
			zIndexNumber -= 10;
		});
	}

	// iframe auto height
	$('.iframe').attr('scrolling','no').iframeAutoHeight();


