(function ($) {
  let dialog;

  $('body').append('<div id="embed-dialog"></div>');

  fetch('/veriface/open-verification')
    .then(response => response.json())
    .then(response =>
  {
    console.log(response.open_code);
    let modalBody = document.querySelectorAll('#embed-dialog');
    let frameEl = document.createElement('iframe');
    frameEl.setAttribute('src', 'https://app.veriface.eu?oc=' + response.open_code + '&embedded=true');
    frameEl.setAttribute('allow', 'camera;microphone');
    frameEl.setAttribute('scrolling', 'no');
    frameEl.setAttribute('onload', 'window.parent.parent.scrollTo(0,0)');
    frameEl.setAttribute('style', 'width: 100%; height: 500px; transition: all .5s;  min-height: 300px; overflow: none');
    modalBody[0].appendChild(frameEl);
    $('#veriface_open_code').val(response.open_code);
    $('#veriface_session_id').val(response.session_id);
  });

  btns = {};

  dialog = $('#embed-dialog').dialog({
    modal: true,
    autoOpen: false,
    width: 800,
    closeOnEscape: true,
    resizable: true,
    draggable: true,
    title: Drupal.t('VeriFace Verification'),
    dialogClass: 'jquery_ui_dialog-dialog',
    buttons: btns,
    close: function (event, ui) {
      $(this).dialog('close');
    }
  });

  $('#openVerificationModal').click(function() {
    console.log('click');
    dialog.dialog('open');
  })

  var prevHeight = 0;
  window.addEventListener('message', (event) => {
    if (event.origin !== 'https://app.veriface.eu') return;
    if (event.data && event.data['action'] && event.data['action'] === 'close') {
      dialog.dialog('close');

      if (event.data['result'] === 'SUCCESS'){

      } else {
        //Pre ine stavy ma zmysel modal dialog iba zavriet
      }
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

})(jQuery);
