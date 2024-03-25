(function ($) {
  let dialog;

  // $('body').append('<div id="embed-dialog"></div>');

  btns = {};

  dialog = $('#embed-dialog').dialog({
    modal: true,
    autoOpen: false,
    width: 800,
    closeOnEscape: false,
    resizable: true,
    draggable: true,
    title: Drupal.t('VeriFace Verification'),
    dialogClass: 'jquery_ui_dialog-dialog',
    buttons: btns,
    position: { my: "center top", at: "center top", of: window },
    close: function (event, ui) {
      $(this).dialog('close');
    }
  });

  $('#openVerificationModal').click(function() {
    dialog.dialog('open');
    $('.ui-dialog-titlebar-close').remove();
    fetch('/veriface/open-verification')
      .then(response => response.json())
      .then(response =>
      {
        let modalBody = document.querySelectorAll('#embed-dialog');
        let frameEl = document.createElement('iframe');
        frameEl.setAttribute('src', 'https://app.veriface.eu?oc=' + response.open_code + '&embedded=true');
        frameEl.setAttribute('allow', 'camera;microphone');
        frameEl.setAttribute('scrolling', 'no');
        // frameEl.setAttribute('onload', 'window.parent.parent.scrollTo(0,0)');
        frameEl.setAttribute('style', 'width: 100%; height: 500px; transition: all .5s;  min-height: 200px; overflow: none');
        modalBody[0].appendChild(frameEl);
        $('#veriface_open_code').val(response.open_code);
        $('#veriface_session_id').val(response.session_id);
      });
    $('#verification-messages').html('<em>Prebieha overenie, čakajte, prosím...</em>');
  })

  var prevHeight = 0;
  window.addEventListener('message', (event) => {
    if (event.origin !== 'https://app.veriface.eu') return;
    if (event.data && event.data['action'] && event.data['action'] === 'close') {
      dialogClose(event);
    } else if (event.data && event.data['action'] && event.data['action'] === 'height') { //Zmenila sa vyska obsahu
      if (prevHeight != event.data['value'] ){
        var modalBodyIframe = document.querySelectorAll('#embed-dialog iframe');
        if (modalBodyIframe.length>0) {
          prevHeight = event.data['value'];
          modalBodyIframe[0].setAttribute('style', 'width: 100%; height: ' + event.data['value'] + 'px; min-height: 300px;' +
            'transition: all .3s; overflow: none');
        }
      }
    }
  });

  function dialogClose(event) {
    var modalBodyIframe = document.querySelectorAll('#embed-dialog iframe');
    if (modalBodyIframe.length>0) {
      modalBodyIframe[0].remove(); //Odstranenie prvku iframe
    }
    dialog.dialog('close');
    if (event.data['result'] === 'SUCCESS'){
      $.post('/veriface/save-verification',
        {
          session_id: event.data['sessionId'],
          status: event.data['status'],
        },
        function(response) {
          if (response.data.length === 0) {
            $('#verification-messages').html('<strong>Overenie prebehlo úspešne! Aby ste úspešne uložili dáta, nezabudnite, prosím, uložiť formulár.</strong>');
          }
          else {
            $('#verification-messages').html('Overenie prebehlo úspešne! Stav overenia: ' + response.data.status_human);
          }
          $('#openVerificationModal').addClass('is-disabled');
        });
    } else {
      //Pre ine stavy ma zmysel modal dialog iba zavriet
      $.post('/veriface/save-verification',
        {
          session_id: event.data['sessionId'],
          status: event.data['status'],
        },
        function(response) {
          $('#verification-messages').html('Overenie bolo neúspešné, alebo prečasne ukončené. Stav overenia: ' + response.data.status_human);
        });
    }
  }
})(jQuery);
