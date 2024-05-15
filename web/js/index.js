$(document).ready(function(){
  // GLOBAL
  $(".toggle").on('click',function(){
    $(this).toggleClass("open");    
    $(".message-info").toggleClass("open");    

  });
}); 

