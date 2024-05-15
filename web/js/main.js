var main = {
  
  base_href : (document.location.href.split('_dev.php').length > 1 ? '/frontend_dev.php' : '/index.php'),
  
  // Initialisation
  init : function()
  {
	  $('.reponse_1').click(function(){
		_this = this;	
		$(this).closest('table').find('.reponse_1').filter(function () {
			return this!=_this;
		}).attr("checked",false);
		$.ajax({
			url:url_save_ligne,
			type     : 'post',
			dataType : 'json',
			data: {
				num_fiche: $('#formulaire_questionnaire_num_fiche').val(),
				cle: $('#formulaire_questionnaire_cle').val(),
				que: $(this).attr('que'),
				qre: $(this).val()
			},
			success:function(retour){
				console.log(retour);
			}
		});		
	  });
	  
	  $('.select').on('change', function(){	    
		que_id = $(this).attr('id').replace('formulaire_questionnaire_que_','').replace('_select','');	
		val = $(this).find('option:selected').attr('value');
		$.ajax({
			url:url_save_ligne,
			type     : 'post',
			dataType : 'json',
			data: {
				num_fiche: $('#formulaire_questionnaire_num_fiche').val(),
				cle: $('#formulaire_questionnaire_cle').val(),
				que: que_id,
				qre: val
			},
			success:function(retour){
				//console.log(retour);
				if(retour.close)
					$('#form_questionnaire').submit();
			}
		});
	  });
	  
	  $("div.danger").each(function(){
		$(this).find('div.alert').show("slow")/*.delay(4000).hide("slow")*/;
      });	  
	  
	  $(".slider").each(function(){		  
		  id = '#'+$(this).attr('id');
		  que_id = id.replace('#formulaire_questionnaire_que_', '').replace('_slider', '');
		  $(id).attr('data-slider-min',1).attr('data-slider-max',10).attr('data-slider-step',1).attr('data-slider-value',$(this).val());
		  $(id).closest('tr').find(".value_slider").text($(this).val());
		  var slider = new Slider(id);
		  prevslideEvt = 0;		  
	  });	  
	  
	  $(".slider").on("change", function(slideEvt) {
		if($(this).attr('value') != undefined)
		  val = $(this).attr('value');			
		$(this).closest('tr').find(".value_slider").text(val);
	  });
	  
	  
	  $('a').tooltip();
	  
	  $('.suivis_patient').click(function(){
		  var patient = $(this).data('pat-id');
		  $.ajax({
			url:url_detail_patient,
			type     : 'post',
			dataType : 'json',
			data: {
				patient: patient
			},
			success:function(retour){
				console.log(retour);
			}
		});
	  });
	    
	  $('#_submit_inscription_professionnel').click(function(){		  		
		document.location.href=url_inscription_professionnel;
	  });
	  /*$('#_submit_inscription_patient').click(function(){
		  $('#signin_redirect').val('2');
		  console.log('bob');
		  $('form').submit();
	  });
	  $('#_submit_connexion').click(function(){
		  $('#signin_redirect').val('');
		  console.log('toto');
		  $('form').submit();
	  });*/
  }
};

// Evite les bug en cas de trace de console de debuggage
if (typeof console == 'undefined' || !console || !console.log)
{
  console = {log : function(){}};
}
