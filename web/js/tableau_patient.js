var tableau = {

  init: function()
  {
    this.bind_load_liste_patient('.load_liste');
    this.bind_checkbox_patient();
    this.bind_reload_liste();
    this.tooltip_liste_consultation();
  },

  tooltip_liste_consultation: function()
  {
    $('.liste_consultation').darkTooltip({
      animation : 'flipIn',
      gravity   : 'south',
      theme     : 'light'
    });
  },
  
  bind_checkbox_patient: function(selecteur)
  {
  	var self = this;
  	$(".traite_patient").change(function(event)
    {
    	var check = 0;
    	if ( $(this).prop( "checked" ) )
    		check = 1;
    	
    	rdv_id = $(this).attr('data-id');
  		dest    = main.base_href + '/professionnel/checkbox_patient?rdv_id=' + rdv_id + '&check=' +check;

  		$.ajax({
  		  url:        dest,
  		  cache:      false,
  		  dataType:   'json',
  		  success:    function(retourServeur){

  		  },
  		});
    });
  },

  bind_load_liste_patient: function(selecteur)
  {
    var self = this;

    $(selecteur).click(function(event)
    {
      event.preventDefault();

      var dest = $(this).attr('href');

      $.get(
        dest,
        function(retour)
        {
          if (false === retour.has_error)
          {
            $('#zone_liste').html(retour.html_liste);

            self.bind_load_liste_patient('.load_liste');
            self.tooltip_liste_consultation();
          }
        },
        'json'
      )
      .fail(function()
      {
        $('#zone_patient').html('<p>Une erreur est survenue au chargement de la liste. Veuillez recharger la page</p>');
      });
    });
  },

  bind_reload_liste: function(selecteur)
  {
    var self = this;

    $('.read_analyse').click(function(event)
    {
      // event.preventDefault();

      var dest = $(this).attr('data-source');

      $.get(
        dest,
        function(retour)
        {
          if (false === retour.has_error)
          {
            $('#zone_liste').html(retour.html_liste);

            self.bind_reload_liste();
            self.tooltip_liste_consultation();
          }
        },
        'json'
      )
      .fail(function()
      {
        $('#zone_patient').html('<p>Une erreur est survenue au chargement de la liste. Veuillez recharger la page</p>');
      });
    });
  }




};



$(document).ready(function() {
  tableau.init();
});
