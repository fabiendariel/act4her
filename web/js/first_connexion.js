var first = {


  // ------------------------------------------------------------------------
  // Lance les fonctions relatives au sfGuard
  init: function()
  {
    this.confirm_request_new_password($('#div_first_connexion_form').find('#btn_send_request'));
    this.block_submit_on_enter_key();
  },


  // ------------------------------------------------------------------------
  // Bloque la soumission de l'adresse par pression de la touche enter
  block_submit_on_enter_key: function()
  {
    $('form').on('keypress keyup', function(event)
    {
      var code = event.keyCode || event.which;
      if (code == 13)
      {
        event.preventDefault();
        return false;
      }
    });
  },

  // ------------------------------------------------------------------------
  // Fonction qui permet d'afficher une popup de connfirmation en cas de succès
  // de demande de mot de passe
  confirm_request_new_password: function(element)
  {
    var self = this;

    var url_post = $(element).attr('href');

    $(element).click(function(event)
    {
      // stoppe le submit
      event.preventDefault();

      $('body').addClass('loading');

      var form = $(this).closest('form');

      $.post(
        url_post,
        form.serialize(),
        function (retour)
        {
          $('body').removeClass('loading');

          $('#login_message').html(retour.message);
          $('#login_message').show();

          $('#div_first_connexion_form').html(retour.form);
          self.block_submit_on_enter_key();

          if (true === retour.has_error)
          {
            self.confirm_request_new_password($('#div_first_connexion_form').find('#btn_send_request'));
            main.tooltip();
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
  first.init();
});
