jQuery(document).ready(function($) {
   $('.review').click(function(e) {
      e.preventDefault();
   });

   $('#add-row').on('click', function() {
      var row = $('.empty-row.screen-reader-text').clone(true);
      row.removeClass('empty-row screen-reader-text');
      row.insertBefore('#fieldset-one tbody>tr:last');
      return false;  
   });

   $('.remove-row').on('click', function() {
      $(this).parents('tr').remove(); // eliminamos todos los tr de esa fila
      return false;
   });
});