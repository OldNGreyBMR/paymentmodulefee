jQuery(document).ready(function() {
  jQuery('input[name="payment"]').live('click', function() {
    updateForm();
  });
});