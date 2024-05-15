var recherche = {

  init: function(){

    $('#rechercher').autocomplete(
    {
      source    : $('#rechercher').attr('data-url'),
      minLength : 3,
      select    : function(event, ui)
      {
        event.preventDefault();

        window.location.href = ui.item.redirect;      },
      response  : function(event, ui)
      {
        if (0 == ui.content.length)
        {
          $('#rechercher').css('color', 'red');
        }

        if (0 < ui.content.length)
        {
          $('#rechercher').css('color', 'black');
        }
      }
    })
    .keyup(function()
    {
      $('#rechercher').css('color', 'black');
    });;


  }

};


$(document).ready(function(){
  recherche.init();
});
