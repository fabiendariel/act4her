var inscription = {
  
  base_href : (document.location.href.split('_dev.php').length > 1 ? '/frontend_dev.php' : '/index.php'),
  
  // Initialisation
  init : function()
  {

	'use strict';  
	
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
		
		
			$(".textbtn1").click(function(event) {


				if(!grecaptcha.getResponse()){
					$('#alert_captcha').show();
				}else{
					$('#alert_captcha').hide();
				}

				if (form.checkValidity() === false || inscription.validatePassword() == false) {
					event.preventDefault();
					event.stopPropagation();
				}else{
					var submit_1 = false;
					var submit_2 = false;
					submit_1 = true;

					if(!grecaptcha.getResponse()){
						$('#alert_captcha').show();
						}else{
							$('#alert_captcha').hide();
							submit_2 = true;
					}

					if(submit_1 && submit_2){
						$('.textbtn1').hide();
						var englobe = $('.textbtn1').closest('.buttons');
						$('<div class="spinner-border">').appendTo(englobe);
						$('#form_questionnaire').submit();
					}
				}
				form.classList.add('was-validated');
			});
    });

  },
  
  validatePassword: function()
  {
	  var value = $("#questionnaire_mdp").val();
	  var password = $("#questionnaire_confirm_mdp").val();

	  $("#questionnaire_mdp").removeClass("is-invalid");
	  $("#questionnaire_confirm_mdp").removeClass("is-invalid");
	  
	  $("#questionnaire_confirm_mdp").removeClass("is-valid");
	  if (value != password) {
		$('#form_questionnaire').removeClass("was-validated")
	    $("#questionnaire_confirm_mdp").addClass("is-invalid");
		return false;
	  } else {
		$("#questionnaire_mdp").removeClass("is-invalid");
		$("#questionnaire_confirm_mdp").addClass("is-valid");
		$("#questionnaire_confirm_mdp").removeClass("is-invalid");
		return true;
	  }
  }
};

$(function()
{
	inscription.init();
});
