var inscription = {
  
  base_href : (document.location.href.split('_dev.php').length > 1 ? '/frontend_dev.php' : '/index.php'),
  
  // Initialisation
  init : function()
  {
	$('#questionnaire_patient_cp').mask('99999');
	$('#questionnaire_patient_tel').mask('9999999999');
	$('#questionnaire_patient_fax').mask('9999999999');
	$('#questionnaire_patient_cp_traitant').mask('99999');
	$('#questionnaire_patient_tel_traitant').mask('9999999999');
	$('#questionnaire_patient_cp_patient').mask('99999');
	$('#questionnaire_patient_tel_patient').mask('9999999999');
	$('#questionnaire_patient_mobile_patient').mask('9999999999');
	$('#questionnaire_patient_cp_representant').mask('99999');
	$('#questionnaire_patient_tel_representant').mask('9999999999');
	$('#questionnaire_patient_mobile_representant').mask('9999999999');
	
	/*$('#questionnaire_patient_naissance_patient').datepicker({
	    format: "dd/mm/yyyy",
	    weekStart: 1,
	    startView: 2,
	    language: "fr"
	});*/
	
	/*$('#questionnaire_patient_cp_patient').keyup(function(){
		if($('#questionnaire_patient_cp_patient').val().length >= 3
		&& $('#questionnaire_patient_nom_patient').val().length >= 3){
			$.ajax({
    			url:url_test_doublon,
    			type     : 'post',
    			dataType : 'json',
    			data: {
    				cp_patient: $('#questionnaire_patient_cp_patient').val(),
    				nom_patient: $('#questionnaire_patient_nom_patient').val()
    			},
    			success:function(retour){
    				if(retour.message.doublon){
    					$('#alert_doublon').closest('td').show();  					
    				}else{
    					$('#alert_doublon').closest('td').hide();  		
    				} 
    			}
    		});
			
		}
		
	});*/

	$('.traitant').hide();
	$('#questionnaire_patient_traitant').change(function(){
		if($('input[name="questionnaire_patient[traitant]"]:checked').val() != undefined){
			$('.traitant').show();
		}else{
			$('.traitant').hide();
		}
	});
	
	/*$('#questionnaire_patient_nom_patient').keyup(function(){
		if($('#questionnaire_patient_cp_patient').val().length >= 3
		&& $('#questionnaire_patient_nom_patient').val().length >= 3){
			$.ajax({
    			url:url_test_doublon,
    			type     : 'post',
    			dataType : 'json',
    			data: {
    				cp_patient: $('#questionnaire_patient_cp_patient').val(),
    				nom_patient: $('#questionnaire_patient_nom_patient').val()
    			},
    			success:function(retour){    				
    				if(retour.message.doublon){
    					$('#alert_doublon').closest('td').show();  					
    				}else{
    					$('#alert_doublon').closest('td').hide();  		
    				}   					
    			}
    		});
		}
		
	});*/
	  
	$('.generate_sms').click(function(){
	console.log('bob');
		if($('#questionnaire_patient_mobile_patient').val() == '' || $('#questionnaire_patient_mobile_patient').val() == undefined){
			$('#alert_sms').text('Vous devez définir un numéro de mobile pour le patient avant de cliquer');
			$('#alert_sms').addClass('alert-danger');
			$('#alert_sms').show();
		}else{
			$('#alert_sms').removeClass('alert-danger');
			$('#alert_sms').hide();
		
			if($('#code_sms').val() == '' || $('#code_sms').val() == undefined){
				var code = inscription.getRandom(10000,99999);
	    		$.ajax({
	    			url:url_envoi_code,
	    			type     : 'post',
	    			dataType : 'json',
	    			data: {
	    				code: code,
  	    			type: 'sms',
	    				mobile: $('#questionnaire_patient_mobile_patient').val()
	    			},
	    			success:function(retour){
							if(retour.envoi){
	    					$('#code_sms').val(retour.code);
	    					$('#alert_sms').text('Le code a été envoyé par SMS au patient');
	    					$('#alert_sms').addClass('alert-success');
			    			$('#alert_sms').show();	    					
	    				}
	    					
	    			}
	    		});
			}else{
				$('#alert_sms').text('Vous avez déjà envoyé le code de vérification par SMS ou par email');
				$('#alert_sms').addClass('alert-danger');
				$('#alert_sms').show();
			}
		}
		
	});

	$('.generate_email').click(function(){
	console.log('bob2');
  		if($('#questionnaire_patient_email_patient').val() == '' || $('#questionnaire_patient_email_patient').val() == undefined){
  			$('#alert_sms').text('Vous devez définir une adresse email pour le patient avant de cliquer');
  			$('#alert_sms').addClass('alert-danger');
  			$('#alert_sms').show();
  		}else{
  			$('#alert_sms').removeClass('alert-danger');
  			$('#alert_sms').hide();

  			if($('#code_sms').val() == '' || $('#code_sms').val() == undefined){
  				var code = inscription.getRandom(10000,99999);
  	    		$.ajax({
  	    			url:url_envoi_code,
  	    			type     : 'post',
  	    			dataType : 'json',
  	    			data: {
  	    				code: code,
  	    				type: 'email',
  	    				email: $('#questionnaire_patient_email_patient').val()
  	    			},
  	    			success:function(retour){
  							if(retour.envoi){
  	    					$('#code_sms').val(retour.code);
  	    					$('#alert_sms').text('Le code a été envoyé par email au patient');
  	    					$('#alert_sms').addClass('alert-success');
  			    			$('#alert_sms').show();
  	    				}

  	    			}
  	    		});
  			}else{
  				$('#alert_sms').text('Vous avez déjà envoyé le code de vérification par email ou par SMS');
  				$('#alert_sms').addClass('alert-danger');
  				$('#alert_sms').show();
  			}
  		}

  	});

	$('.renvoi_sms').click(function(){
		if($('#questionnaire_patient_mobile_patient').val() == '' || $('#questionnaire_patient_mobile_patient').val() == undefined){
			$('#alert_sms').text('Vous devez définir un numéro de mobile pour le patient avant de cliquer');
			$('#alert_sms').addClass('alert-danger');
			$('#alert_sms').show();
		}else{
			$('#alert_sms').removeClass('alert-danger');
			$('#alert_sms').hide();
		
			$('#code_sms').val('');
			$.ajax({
				url:url_envoi_code,
				type     : 'post',
				dataType : 'json',
				data: {
					mobile: $('#questionnaire_patient_mobile_patient').val()
				},
				success:function(retour){
					if(retour.envoi){
						$('#code_sms').val(retour.code);
						$('#alert_sms').text('Le code a été envoyé par SMS au patient');
						$('#alert_sms').addClass('alert-success');
						$('#alert_sms').show();
					}
				}
			}); 
		}
		return false;
	});
	

	'use strict';  
	
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {    	

			$(".textbtn1").click(function(event) {
				$(".textbtn1").attr('disabled','disabled');
				var valide = true;

				if($('#alert_doublon').closest('td').attr('style')=='')
						valide = false;


				var from = $("#questionnaire_patient_naissance_patient").val().split("-")
				var f = new Date(from[2], from[1] - 1, from[0])
				if(from[0] < 1900){
					valide = false;
					$(".dateError").show();
				}

				if($('input[name="questionnaire_patient[collecte_informatique]"]:checked').val() == undefined){
					$('#alert_coche').show();
					valide = false;
				}else{
					$('#alert_coche').hide();

					if($('input[name="questionnaire_patient[cgv]"]:checked').val() == undefined){
						$('#alert_coche_cgv').show();
						valide = false;
					}else{
						$('#alert_coche_cgv').hide();
					}
				}

				var codeValid = false;
				$.ajax({
					url:url_check_code,
					type     : 'post',
					dataType : 'json',
					async : false,
					data: {
						code: $('#questionnaire_patient_code_validation').val(),
						mobile: $('#questionnaire_patient_mobile_patient').val()
					},
					success:function(retour){
						if(retour.envoi){
							codeValid = true;
						}
					}
				});

				if($('#code_sms').val() == undefined || $('#code_sms').val() == ''){
					$('#alert_sms').text('Vous devez envoyer le code par SMS/Email au patient avant de continuer');
					$('#alert_sms').addClass('alert-danger');
					$('#alert_sms').show();
					valide = false;
				}else{
					$('#alert_sms').removeClass('alert-danger');
					$('#alert_sms').hide();
					if(codeValid != true){
						$('#alert_sms_diff').show();
						valide = false;
					}else{
						$('#alert_sms_diff').hide();
					}
				}
				if (form.checkValidity() === false) {
					event.preventDefault();
					event.stopPropagation();
					$(".textbtn1").removeAttr('disabled');
				}else{

					if(valide)
						$('#questionnaire').submit();
					else
						$(".textbtn1").removeAttr('disabled');
				}
				form.classList.add('was-validated');

			});
    });
  },
  
  getRandom: function(min, max) {
	  min = Math.ceil(min);
	  max = Math.floor(max);
	  return Math.floor(Math.random() * (max - min)) + min;
  }
};

$(function()
{
	inscription.init();
});

$.extend(
{
    redirectPost: function(location, args)
    {
        var form = '';
        $.each( args, function( key, value ) {
            form += '<input type="hidden" name="'+key+'" value="'+value+'">';
        });
        $('<form action="'+location+'" method="POST">'+form+'</form>').appendTo('body').submit();
    }
});