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

        $this->registerArgument(
            'table',
            'string',
            null,
            false,
            'tt_content'
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
        $table = $this->arguments['table'];

        $pasteItem = $this->getElementFromClipboard();
        $pasteMode = self::$clipboard->currentMode();

        if ($uid == $pasteItem && $pasteMode === 'copy') {
            $link = sprintf(
                '<a class="btn btn-default btn-sm ext-dragon-drop-release"
                   title="%s"
                   data-table="%s"
                   data-uid="%d">
                   %s
                </a>',
                $GLOBALS['LANG']->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.clipboard.clear_clipboard'),
                htmlspecialchars($table),
                $uid,
                $this->getText('actions-edit-copy-release')
            );
        } else {
            $link = sprintf(
                '<a class="btn btn-default btn-sm ext-dragon-drop-copper"
                   title="%s"
                   data-table="%s"
                   data-uid="%d">
                   %s
                </a>',
                $GLOBALS['LANG']->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:cm.copy'),
                htmlspecialchars($table),
                $uid,
                $this->getText('actions-edit-copy')
            );
        }

        return $link;
    }
}
