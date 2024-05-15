var forgot = {


  // ------------------------------------------------------------------------
  // Lance les fonctions relatives au sfGuard
  init: function()
  {
    this.confirm_request_new_password();
  },


  // ------------------------------------------------------------------------
  // Fonction qui permet d'afficher une popup de connfirmation en cas de succès
  // de demande de mot de passe
  confirm_request_new_password: function()
  {
    $('#btn_send_request').click(function(event)
    {
      // stoppe le submit
      event.preventDefault();

      var self = $(this);

      $('body').addClass('loading');

      var form = $(this).closest('form');

      $.post(
        main.base_href + '/forgot',
        form.serialize(),
        function (retour)
        {
          $('body').removeClass('loading');

          $('#login_message').html(retour.message);
          $('#login_message').show();

          if (false === retour.has_error && undefined != retour.redirect)
          {
            // var redirection = 'window.location.replace("' + retour.redirect + '");';
            // setTimeout(redirection, 800);
          }

          return;
        },
        'json'
      );
    });
  }

};

$(function()
{
  forgot.init();
});
