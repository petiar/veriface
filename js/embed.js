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
        //Vlastna funkcia ak prislo k uspesnemu ukonceniu overovacieho procesu (napr. presmerovanie, refresh, ...)
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
/*
//Inicializacia Bootstrap modal dialog
var verificationModal = new bootstrap.Modal(verificationModalEl, {keyboard: false, backdrop: 'static'});

//Otvorenie dialogu s overovacim procesom
verificationModalEl.addEventListener('shown.bs.modal', () => {
  applySafariScrollingFix();

  //Ziskat openCode
  //Pozor! Z bezpecnostnych dovodov nie je mozne a neodporucame volat API koncovy bod VeriFace.
  //Je nutne volat sluzbu, ktoru mate implementovanu na Vasej strane, ktora poziadavku na Vytvorenie overenia obsluzi
  fetch('zaloz-overenie.php').then(response => response.text()).then(response => {
    //Odpovedou moze byt parameter openCode
    var modalBody = document.querySelectorAll('#verificationModal .modal-body > div');
    var frameEl = document.createElement('iframe');
    frameEl.setAttribute('src', '{{appHost}}?oc=' + response + '&embedded=true');
    frameEl.setAttribute('allow', 'camera;microphone');
    frameEl.setAttribute('scrolling', 'no');
    frameEl.setAttribute('onload', 'window.parent.parent.scrollTo(0,0)');
    frameEl.setAttribute('style', 'width: 100%; height: 300px; transition: all .5s;  min-height: 300px; overflow: none');
    modalBody[0].appendChild(frameEl);
  });
});

//Zatvorenie dialogu s overovacim procesom
verificationModalEl.addEventListener('hidden.bs.modal', () => {
  removeSafariScrollingFix();
  var modalBodyIframe = document.querySelectorAll('#verificationModal .modal-body iframe');
  if (modalBodyIframe.length>0) {
    modalBodyIframe[0].remove(); //Odstranenie prvku iframe
  }
});

//Automaticka zmena vysky prvku iframe podla obsahu
var prevHeight = 0;
window.addEventListener('message', (event) => {
  //spracovavat iba spravy od VeriFace aplikacie
  if (event.origin !== '{{appHost}}') return;

  //Pouzivatel zavrel, alebo bol automaticky zavrety overovaci proces
  if (event.data && event.data['action'] && event.data['action'] === 'close') {

    //Skrytie modal dialogu
    verificationModal.hide();

    if (event.data['result'] === 'SUCCESS'){
      //Vlastna funkcia ak prislo k uspesnemu ukonceniu overovacieho procesu (napr. presmerovanie, refresh, ...)
    } else {
      //Pre ine stavy ma zmysel modal dialog iba zavriet
    }
  } else if (event.data && event.data['action'] && event.data['action'] === 'height') { //Zmenila sa vyska obsahu
    if (prevHeight != event.data['value'] ){
      var modalBodyIframe = document.querySelectorAll('#verificationModal .modal-body iframe');
      if (modalBodyIframe.length>0) {
        prevHeight = event.data['value'];
        modalBodyIframe[0].setAttribute('style', 'width: 100%; height: ' + event.data['value'] + 'px; min-height: 300px;' +
          'transition: all .3s; overflow: none');
      }
    }
  }
});

var previousScrollY = 0;

function applySafariScrollingFix(){
  previousScrollY = window.scrollY;
  var htmlEl = document.querySelectorAll('html')[0];
  htmlEl.classList.add('modal-open');
  htmlEl.style['marginTop'] = -previousScrollY;
  htmlEl.style['overflow'] = 'hidden';
  htmlEl.style['left'] = 0;
  htmlEl.style['right'] = 0;
  htmlEl.style['top'] = 0;
  htmlEl.style['bottom'] = 0;
  htmlEl.style['position'] = 'fixed';
}

function removeSafariScrollingFix(){
  var htmlEl = document.querySelectorAll('html')[0];
  htmlEl.classList.remove('modal-open');
  htmlEl.style['marginTop'] = 0;
  htmlEl.style['overflow'] = 'visible';
  htmlEl.style['left'] = 'auto';
  htmlEl.style['right'] = 'auto';
  htmlEl.style['top'] = 'auto';
  htmlEl.style['bottom'] = 'auto';
  htmlEl.style['position'] = 'static';
  window.scrollTo(0, previousScrollY);
}

function openVerificationModal(){
  verificationModal.show();
}
*/
