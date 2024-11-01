jQuery(document).ready(function(){
  jQuery('#fsbano-js-ui-datepicker').datepicker({
    dateFormat: 'dd/mm/yy',
    minDate: 1,
    maxDate: '+1M +10D'
  });
});
