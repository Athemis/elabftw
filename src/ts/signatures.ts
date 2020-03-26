/**
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
import { notif } from './misc';

const Signatures = {
  controller: 'app/controllers/SignaturesAjaxController.php',
  create: function(): void {
    (document.getElementById('signaturesCreateButton') as HTMLButtonElement).disabled = true;
    const type = $('#info').data('type');
    const id = $('#info').data('id');
    const revnum = $('#info').data('revnum');

    $.post(this.controller, {
      create: true,
      type: type,
      id: id,
      revnum: revnum,
    }).done(function(json) {
      notif(json);
      console.log(JSON.stringify(json));
      if (json.res) {
        $('#signature_container').load('?mode=view&id=' + id + ' #signature');
      } else {
        (document.getElementById('signaturesCreateButton') as HTMLButtonElement).disabled = false;
      }
    });
  },
  destroy: function(signatureId: string): void {
    const id = $('#info').data('id');
    const confirmText = $('#info').data('confirm');
    if (confirm(confirmText)) {
      $.post(this.controller, {
        destroy: true,
        type: $('#info').data('type'),
        id: signatureId
      }).done(function(json) {
        notif(json);
        if (json.res) {
          $('#signature_container').load('?mode=view&id=' + id + ' #signature');
        }
      });
    }
  }
};

// CREATE SIGNATURES
$(document).on('click', '#signaturesCreateButton', function() {
  Signatures.create();
});

$(document).on('mouseenter', '.signature', function() {
  ($(this) as any).editable('app/controllers/SignaturesAjaxController.php', {
    name: 'update',
    type : 'textarea',
    submitdata: {
      type: $(this).data('type')
    },
    width: '80%',
    height: '200',
    tooltip : 'Click to edit',
    indicator : $(this).data('indicator'),
    submit : $(this).data('submit'),
    cancel : $(this).data('cancel'),
    style : 'display:inline',
    submitcssclass : 'button btn btn-primary mt-2',
    cancelcssclass : 'button btn btn-danger mt-2',
    callback : function(data: string) {
      const json = JSON.parse(data);
      notif(json);
      // show result in comment box
      if (json.res) {
        $(this).html(json.update);
      }
    }
  });
});

// DESTROY COMMENTS
$(document).on('click', '.signaturesDestroy', function() {
  Signatures.destroy($(this).data('id'));
});

