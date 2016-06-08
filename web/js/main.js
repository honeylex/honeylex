$( document ).ready(function() {
    $('#deletePrompt').on('show.bs.modal', function (event) {
      var $button = $(event.relatedTarget);
      var $modal = $(this);
      var $activeForm = $button.parent('form').clone();
      var $modalForm = $modal.find('.delete-form');

      $modalForm.attr('action', $activeForm.attr('action'));
      $modalForm.find('.revision-val').val($activeForm.find('.revision-val').val());
      $modalForm.find('.workflow-state-val').val($activeForm.find('.workflow-state-val').val());
    });
});