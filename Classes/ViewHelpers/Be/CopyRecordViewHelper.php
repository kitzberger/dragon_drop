<?php

namespace Kitzberger\DragonDrop\ViewHelpers\Be;

class CopyRecordViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument(
            'uid',
            'integer',
            null,
            true
        );
    }

    /**
     * Render an edit link for a given record.
     *
     * @return string the edit link
     */
    public function render()
    {
        $pageRenderer = $this->getPageRenderer();
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/DragonDrop/Maester');

        // prepare parameters
        $uid = $this->arguments['uid'];

        $pasteItem = $this->getElementFromClipboard();
        if ($uid == $pasteItem) {
            $link = sprintf(
                '<a class="btn btn-default btn-sm" disabled
                   title="%s"
                   data-table="tt_content"
                   data-uid="%d">
                   %s
                </a>',
                'Nope.',
                $uid,
                $this->getText('apps-pagetree-drag-place-denied')
            );
        } else {
            $link = sprintf(
                '<a class="btn btn-default btn-sm ext-dragon-drop-copper"
                   title="%s"
                   data-table="tt_content"
                   data-uid="%d">
                   %s
                </a>',
                $GLOBALS['LANG']->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:cm.copy'),
                $uid,
                $this->getText('actions-edit-copy')
            );
        }

        return $link;
    }
}
