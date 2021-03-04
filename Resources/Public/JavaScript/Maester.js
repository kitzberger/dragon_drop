define(['jquery', 'TYPO3/CMS/Backend/ContextMenuActions'], function($, ContextMenuActions) {
    var Maester = {
        selectorCopy:    '.ext-dragon-drop-copper',
        selectorCut:     '.ext-dragon-drop-cutter',
        selectorRelease: '.ext-dragon-drop-release'
    }

    Maester.initialize = function() {
        // console.log('Maester init!')
        $(Maester.selectorCopy).on('click', function() {
          var link = $(this)
          var table = link.data('table')
          var uid = link.data('uid')
          //console.dir('copy ' + table + ':' + uid)
          ContextMenuActions.copy(table, uid)
        })

        $(Maester.selectorCut).on('click', function() {
          var link = $(this)
          var table = link.data('table')
          var uid = link.data('uid')
          //console.dir('cut ' + table + ':' + uid)
          ContextMenuActions.cut(table, uid)
        })

        $(Maester.selectorRelease).on('click', function() {
          var link = $(this)
          var table = link.data('table')
          var uid = link.data('uid')
          //console.dir('clipboardRelease ' + table + ':' + uid)
          ContextMenuActions.clipboardRelease(table, uid)
        })
    }

    $(Maester.initialize)

    return Maester;
})
