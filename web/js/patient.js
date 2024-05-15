var patient = {

	base_href : (document.location.href.split('_dev.php').length > 1 ? '/frontend_dev.php' : '/index.php'),

	// Initialisation
	init : function()
	{
		'use strict';

		// Fetch all the forms we want to apply custom Bootstrap validation styles to

		$('.alertes').each(function(){
			var h = $(this).height();
			$(this).closest('tr').find('td').each(function(){
				$(this).height(h);
			});
		});

		$('#new_doc_submit2').click(function() {
			event.preventDefault();
			event.stopPropagation();
			//var fd = new FormData();
			//var files = $('#new_doc_doc')[0].files;
			var patient = $(this).data('id');
			var comm = $('#new_doc_comm').val();
			$.ajax({
				url: (document.location.href.split('_dev.php').length > 1 ? '/app_dev.php' : '/app.php')+'/professionnel_document',
				type: 'post',
				data: {
					patient: patient,
					form: $('#new_doc_form').serializeArray()
				},
				contentType: false,
				processData: false,
				success: function(response){
					console.log(response)
				},
			});
			// Check file selected or not
			/*if(files.length > 0 ){
				fd.append('file',files[0]);

				$.ajax({
					url: (document.location.href.split('_dev.php').length > 1 ? '/app_dev.php' : '/app.php')+'/professionnel_document',
					type: 'post',
					data: {
						patient: patient,
						form: $('#new_doc_form').serializeArray()
					},
					contentType: false,
					processData: false,
					success: function(response){
						console.log(response)
					},
				});
			}else{
				alert("Please select a file.");
			}*/
		});
	}

};

$(function()
{
	patient.init();
});
