<?php

namespace Kitzberger\DragonDrop\ViewHelpers\Be;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Imaging\Icon;

class PasteLinkViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{
    // We want to return HTML here, so no escaping please!
    protected $escapeOutput = false;
    protected $escapeChildren = false;

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
    }

    /**
     * Initializes the clipboard for generating paste links
     *
     * @see \TYPO3\CMS\Recordlist\RecordList::main()
     * @see \TYPO3\CMS\Backend\Controller\ContextMenuController::clipboardAction()
     * @see \TYPO3\CMS\Filelist\Controller\FileListController::indexAction()
     */
    protected function initializeClipboard()
    {
        // Start clipboard
        $this->clipboard = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Clipboard\Clipboard::class);

        // Initialize - reads the clipboard content from the user session
        $this->clipboard->initializeClipboard();

        // This locks the clipboard to the Normal for this request.
        $this->clipboard->lockToNormal();

        // Clean up pad
        $this->clipboard->cleanCurrent();

        // Save the clipboard content
        $this->clipboard->endClipboard();
    }

    /**
     * Render an edit link for a given record.
     *
     * @return string the edit link
     */
    public function render()
    {
        // container record
        $target   = $this->arguments['target'];
        $override = $this->arguments['override'];

        $pageRenderer = $this->getPageRenderer();
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/DragonDrop/Pastor');

        if (!empty($target)) {
            $this->initializeClipboard();
            $elFromTable = $this->clipboard->elFromTable('tt_content');
            $pasteMode   = $this->clipboard->currentMode();

            if (!empty($elFromTable)) {
                $pasteItem = substr(key($elFromTable), 11);
                $pasteRecord = BackendUtility::getRecord('tt_content', (int)$pasteItem);
                $pasteTitle = $pasteRecord['header'] ? $pasteRecord['header'] : $pasteItem;

                $link = sprintf('
                    <a class="btn btn-default btn-sm ext-dragon-drop-pastor"
                       title="%s"
                       data-mode="%s"
                       data-source="%d"
                       data-title="%s"
                       data-pid="%d"
                       data-override=\'%s\'>
                       %s
                    </a>',
                    $GLOBALS['LANG']->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:cm.pasteinto'),
                    $pasteMode,
                    $pasteItem,
                    $pasteTitle,
                    $target['pid'],
                    json_encode($override),
                    $this->getText()
                );

                return $link;
            }
        }
    }

    protected function getText()
    {
        $text = $this->renderChildren();

        if (is_null($text)) {
            $text = $this->getIcon('actions-document-paste-into');
        }

        return $text;
    }

    protected function getIcon($key)
    {
        $iconFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconFactory::class
        );
        $icon = $iconFactory->getIcon(
            $key,
            Icon::SIZE_SMALL
        );

        return $icon->getMarkup();
    }
}
