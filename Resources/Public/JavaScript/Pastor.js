define(['jquery', 'TYPO3/CMS/Backend/AjaxDataHandler'], function($, DataHandler) {
    var Pastor = {
        selector: '.ext-dragon-drop-pastor'
    };

    Pastor.initialize = function() {
        // console.log('Pastor init!')

        $(Pastor.selector).on('click', function() {

            var link = $(this)

            var mode     = $(this).data('mode')
            var source   = $(this).data('source')
            var pid      = $(this).data('pid')
            var override = $(this).data('override')

            var parameters = {};
            parameters['cmd'] = {tt_content: {}};
            parameters['cmd']['tt_content'][source] = {}
            parameters['cmd']['tt_content'][source][mode] = {
                action: 'paste',
                target: pid,
                update: override
            }

            // console.dir(parameters)

            DataHandler.process(parameters).done(function(response) {
                // console.dir('done')
                // console.dir(response)
                if (response.hasErrors === false) {
                    link.replaceWith('<b>' + link.data('title') + '</b>')
                }
            }).fail(function(response) {
                console.dir('fail')
                console.dir(response)
            });
        })
    };

   $(Pastor.initialize);

   return Pastor;
});


