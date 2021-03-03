<?php

namespace Kitzberger\DragonDrop\ViewHelpers\Be;

use TYPO3\CMS\Backend\Clipboard\Clipboard;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;

abstract class AbstractViewHelper extends AbstractBackendViewHelper
{
    // We want to return HTML here, so no escaping please!
    protected $escapeOutput = false;
    protected $escapeChildren = false;

    protected static $clipboard = null;

    public function initialize()
    {
        if (empty(self::$clipboard)) {
            $this->initializeClipboard();
        }
    }

    /**
     * Initializes the clipboard for generating paste links
     *
     * @see \TYPO3\CMS\Recordlist\RecordList::main() (TYPO3 8)
     * @see \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList (TYPO3 9, 10)
     * @see \TYPO3\CMS\Backend\Controller\ContextMenuController::clipboardAction()
     * @see \TYPO3\CMS\Filelist\Controller\FileListController::indexAction()
     */
    protected function initializeClipboard()
    {
        // Start clipboard
        self::$clipboard = GeneralUtility::makeInstance(Clipboard::class);

        // Initialize - reads the clipboard content from the user session
        self::$clipboard->initializeClipboard();

        // This locks the clipboard to the Normal for this request.
        self::$clipboard->lockToNormal();

        // Clean up pad
        self::$clipboard->cleanCurrent();

        // Save the clipboard content
        self::$clipboard->endClipboard();
    }

    protected function getElementFromClipboard()
    {
        if ($elFromTable = self::$clipboard->elFromTable('tt_content')) {
            return substr(key($elFromTable), 11);
        }
    }

    protected function getText($defaultIcon = 'content-clock')
    {
        $text = $this->renderChildren();

        if (is_null($text)) {
            $text = $this->getIcon($defaultIcon);
        }

        return $text;
    }

    protected function getIcon($key)
    {
        $iconFactory = GeneralUtility::makeInstance(
            IconFactory::class
        );
        $icon = $iconFactory->getIcon(
            $key,
            Icon::SIZE_SMALL
        );

        return $icon->render();
    }
}
