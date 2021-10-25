define(['jquery', 'TYPO3/CMS/Backend/AjaxDataHandler', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Severity'], function($, DataHandler, Modal, Severity) {
    var Pastor = {
        selectorPaste: '.ext-dragon-drop-pastor'
    }

    Pastor.initialize = function() {
        // console.log('Pastor init!')

        $(Pastor.selectorPaste).on('click', function() {

            var $anchorElement = $(this)

            var command  = $anchorElement.data('mode') === 'copy' ? 'copy' : 'move';
            var source   = $anchorElement.data('source')
            var pid      = $anchorElement.data('pid')
            var override = $anchorElement.data('override')

            var parameters = {}
            parameters['cmd'] = {tt_content: {}}
            parameters['cmd']['tt_content'][source] = {}
            parameters['cmd']['tt_content'][source][command] = {
                action: 'paste',
                target: pid,
                update: override
            }

            // console.dir(parameters)
            var performPaste = function() {
                // SimpleDataHandlerController::processAjaxRequest
                DataHandler.process(parameters).done(function(response) {
                    // console.dir('done')
                    // console.dir(response)
                    // if (response.hasErrors === false) {
                    //     $anchorElement.replaceWith('<b>' + $anchorElement.data('title') + '</b>')
                    // }
                    top.list_frame.location.reload(true);
                }).fail(function(response) {
                    console.dir('fail')
                    console.dir(response)
                })
            }

            var $modal = Modal.confirm(
              $anchorElement.attr('title') + ': "' + $anchorElement.data('title') + '"',
              $anchorElement.data('message') || TYPO3.lang['paste.modal.paste'],
              Severity.warning, [
                {
                  text: $(this).data('button-close-text') || TYPO3.lang['button.cancel'] || 'Cancel',
                  active: true,
                  btnClass: 'btn-default',
                  name: 'cancel'
                },
                {
                  text: $(this).data('button-ok-text') || TYPO3.lang['button.ok'] || 'OK',
                  btnClass: 'btn-warning',
                  name: 'ok'
                }
              ]
            );

            $modal.on('button.clicked', function(e) {
              if (e.target.name === 'ok') {
                performPaste();
              }
              Modal.dismiss();
            });
        })
    }

   $(Pastor.initialize)

   return Pastor;
})
