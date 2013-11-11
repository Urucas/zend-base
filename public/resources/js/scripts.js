$(document).ready(function() {
	init();
});

function init(){
	parseBirth();
	jqueryUI();
	formElements();
}

function jqueryUI() {
	$('select#category').selectmenu({style:'dropdown'});
	$('select#birth').selectmenu({
		style:'dropdown',
		width:80,
		menuWidth: 80,
		maxHeight:180
	});
	$( "#radio" ).buttonset();
}

function formElements() {
	/*
	$('input:text').each(function(){
		var txtval = $(this).val();
		$(this).focus(function(){
			$(this).val('').addClass('focused');
			
		});
		$(this).blur(function(){
			if($(this).val() == ""){
				$(this).val(txtval).removeClass('focused');
			}
		});
	});
	*/
	$(".carousel").jCarouselLite({
        btnNext: ".next",
        btnPrev: ".prev",
		visible: 9
    });
	
	$('.menu-user').hover(function(){
		$(this).toggleClass('active');
	});
	
	$('a.make-review').bind('click',function(e){
		e.preventDefault();
		$(this).toggleClass('active');
		$('.review-content').slideToggle();
	});
	
}

function parseBirth() {
	var i=1,x=1,y=1930;
	for (i=1;i<=30;i++){
		$('select.days-month').append('<option value="' + i + '">' + i + '</option>');
	}
	for (x=1;x<=12;x++){
		$('select.per-month').append('<option value="' + x + '">' + x + '</option>');
	}
	for (y=1930;y<=2005;y++){
		$('select.per-year').append('<option value="' + y + '">' + y + '</option>');
	}		
}
