define(['jquery', 'TYPO3/CMS/Backend/AjaxDataHandler', 'TYPO3/CMS/Backend/ContextMenuActions'], function($, DataHandler, ContextMenuActions) {
    var Pastor = {
        selectorPaste: '.ext-dragon-drop-pastor'
    }

    Pastor.initialize = function() {
        // console.log('Pastor init!')

        $(Pastor.selectorPaste).on('click', function() {

            var link = $(this)

            var mode     = link.data('mode')
            var source   = link.data('source')
            var pid      = link.data('pid')
            var override = link.data('override')
            var irre     = link.data('irre')

            var parameters = {}
            parameters['cmd'] = {tt_content: {}}
            parameters['cmd']['tt_content'][source] = {}
            parameters['cmd']['tt_content'][source][mode] = {
                action: 'paste',
                target: pid,
                update: override
            }
            parameters['data'] = {dragon_drop_irre: irre};

            // console.dir(parameters)

            // SimpleDataHandlerController::processAjaxRequest
            DataHandler.process(parameters).done(function(response) {
                // console.dir('done')
                // console.dir(response)
                if (response.hasErrors === false) {
                    link.replaceWith('<b>' + link.data('title') + '</b>')
                }
            }).fail(function(response) {
                console.dir('fail')
                console.dir(response)
            })
        })
    }

   $(Pastor.initialize)

   return Pastor;
})
