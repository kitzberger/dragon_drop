define(['jquery', 'TYPO3/CMS/Backend/LayoutModule/DragDrop'], function($, DragDrop) {

  var Dropstor = {
    contentIdentifier: '.t3js-page-ce',
    dropZoneIdentifier: '.dropstor-dropzone',            // .t3js-page-ce-dropzone-available
    validDropZoneClass: 'dropstor-dropzone-active',      // active
    dropPossibleHoverClass: 'dropstor-dropzone-possible' // t3-page-ce-dropzone-possible
  };

  /**
   * initializes Drag+Drop for all content elements on the page
   */
  Dropstor.initialize = function() {
    $(Dropstor.contentIdentifier).on('dragstart', function(event, ui) {
      console.log('start')
      $(Dropstor.dropZoneIdentifier).addClass(Dropstor.validDropZoneClass)
    })
    $(Dropstor.contentIdentifier).on('dragstop', function(event, ui) {
      console.log('stop')
      $(Dropstor.dropZoneIdentifier).removeClass(Dropstor.validDropZoneClass)
    })
    $(Dropstor.dropZoneIdentifier)
      .each(function($i) {
        let element = $(this).closest('.t3js-page-ce')
        console.log('Adding dropzone for ' + element.data('table') + ':' + element.data('uid'))
      })
      .droppable({
        accept: this.contentIdentifier,
        scope: 'tt_content',
        tolerance: 'pointer',
        over: function(evt, ui) {
          console.log('over')
          Dropstor.onDropHoverOver($(ui.draggable), $(this));
        },
        out: function(evt, ui) {
          console.log('out')
          Dropstor.onDropHoverOut($(ui.draggable), $(this));
        },
        drop: function(evt, ui) {
          console.log('drop')
          Dropstor.onDrop($(ui.draggable), $(this), evt);
        }
      });
  };

  /**
   * adds CSS classes when hovering over a dropzone
   * @param $draggableElement
   * @param $droppableElement
   * @private
   */
  Dropstor.onDropHoverOver = function($draggableElement, $droppableElement) {
    $droppableElement.addClass(Dropstor.dropPossibleHoverClass);
  };

  /**
   * removes the CSS classes after hovering out of a dropzone again
   * @param $draggableElement
   * @param $droppableElement
   * @private
   */
  Dropstor.onDropHoverOut = function($draggableElement, $droppableElement) {
    $droppableElement.removeClass(Dropstor.dropPossibleHoverClass);
  };

  /**
   * this method does the whole logic when a draggable is dropped on to a dropzone
   * sending out the request and afterwards move the HTML element in the right place.
   *
   * @param $draggableElement
   * @param $droppableElement
   * @param {Event} evt the event
   * @private
   */
  Dropstor.onDrop = function($draggableElement, $droppableElement, evt) {

    $droppableElement.removeClass(Dropstor.dropPossibleHoverClass);
    var $pasteAction = typeof $draggableElement === 'number';

    // send an AJAX requst via the AjaxDataHandler
    var contentElementUid = $pasteAction ? $draggableElement : parseInt($draggableElement.data('uid'));
    if (contentElementUid > 0) {
      var parameters = {};
      // add the information about a possible column position change
      var targetFound = $droppableElement.closest(Dropstor.contentIdentifier).data('uid');
      // the item was moved to the top of the colPos, so the page ID is used here
      var targetPid = 0;
      if (typeof targetFound === 'undefined') {
        // the actual page is needed
        targetPid = $('[data-page]').first().data('page');
      } else {
        // the negative value of the content element after where it should be moved
        targetPid = 0 - parseInt(targetFound);
      }
      parameters['cmd'] = {tt_content: {}};
      parameters['data'] = {tt_content: {}};
      var copyAction = (evt && evt.originalEvent.ctrlKey || $droppableElement.hasClass('t3js-paste-copy'));
      if (copyAction) {
        parameters['cmd']['tt_content'][contentElementUid] = {
          copy: {
            action: 'paste',
            target: targetPid,
            update: $droppableElement.data('override')
          }
        };
      } else {
        if ($pasteAction) {
          parameters = {
            CB: {
              paste: 'tt_content|' + targetPid,
              update: $droppableElement.data('override')
            }
          };
        } else {
          parameters['cmd']['tt_content'][contentElementUid] = {
            move: {
              action: 'paste',
              target: targetPid,
              update: $droppableElement.data('override')
            }
          };
        }
      }

      console.dir({
        copyAction: copyAction,
        parameters: parameters
      })
      //return

      // fire the request, and show a message if it has failed
      Dropstor.ajaxAction($droppableElement, $draggableElement, parameters, copyAction, $pasteAction);
    }
  };

  /**
   * this method does the actual AJAX request for both, the move and the copy action.
   *
   * @param $droppableElement
   * @param $draggableElement
   * @param parameters
   * @param $copyAction
   * @param $pasteAction
   * @private
   */
  Dropstor.ajaxAction = function($droppableElement, $draggableElement, parameters, $copyAction, $pasteAction) {
    require(['TYPO3/CMS/Backend/AjaxDataHandler'], function(DataHandler) {
      DataHandler.process(parameters).done(function(result) {
        if (!result.hasErrors) {
          // insert draggable on the new position
          if (!$pasteAction) {
            if (!$droppableElement.parent().hasClass(Dropstor.contentIdentifier.substring(1))) {
              $draggableElement.detach().css({top: 0, left: 0})
                .insertAfter($droppableElement.closest(Dropstor.dropZoneIdentifier));
            } else {
              $draggableElement.detach().css({top: 0, left: 0})
                .insertAfter($droppableElement.closest(Dropstor.contentIdentifier));
            }
          }
          if ($('.t3js-page-lang-column').length || $copyAction || $pasteAction) {
            self.location.reload(true);
          }
        }
      });
    });
  };

  $(Dropstor.initialize);
  return Dropstor;
});
