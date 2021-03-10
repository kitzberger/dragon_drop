<?php

namespace Kitzberger\DragonDrop\ViewHelpers\Be;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PasteLinkViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument(
            'target',
            'array',
            null,
            true
        );

        $this->registerArgument(
            'override',
            'array',
            [],
            true
        );

        $this->registerArgument(
            'irreChildrenField',
            'string',
            null,
            false
        );

        $this->registerArgument(
            'irreParentField',
            'string',
            null,
            false
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
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/DragonDrop/Pastor');

        $pasteItem = $this->getElementFromClipboard();

        if (!empty($pasteItem)) {
            // prepare parameters
            $target       = $this->arguments['target'];
            $override     = $this->arguments['override'];

            // do all fields exist in TCA?
            $this->checkTca(array_keys($override));

            // gather paste data
            $pasteMode   = self::$clipboard->currentMode();
            $pasteRecord = BackendUtility::getRecord('tt_content', $pasteItem);
            $pasteTitle = $pasteRecord['header'] ? $pasteRecord['header'] : $pasteItem;

            // create link
            $link = sprintf(
                '<a class="btn btn-default btn-sm ext-dragon-drop-pastor"
                   title="%s"
                   data-mode="%s"
                   data-source="%d"
                   data-title="%s"
                   data-pid="%d"
                   data-override=\'%s\'>
                   %s
                </a>',
                $GLOBALS['LANG']->sL('LLL:EXT:backend/Resources/Private/Language/locallang_layout.xlf:paste.modal.title.paste'),
                $pasteMode,
                $pasteItem,
                $pasteTitle,
                $target['pid'],
                json_encode($override),
                $this->getText('actions-document-paste-into')
            );

            return $link;
        }
    }

    protected function checkTca($columns)
    {
        foreach ($columns as $column) {
            if (empty($GLOBALS['TCA']['tt_content']['columns'][$column])) {
                throw new \Exception('Column missing in TCA: tt_content.' . $column);
            }
        }
    }
}
