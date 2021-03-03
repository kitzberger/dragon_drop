<?php

namespace Kitzberger\DragonDrop\ViewHelpers\Be;

use TYPO3\CMS\Backend\Clipboard\Clipboard;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;

class PasteLinkViewHelper extends AbstractBackendViewHelper
{
    // We want to return HTML here, so no escaping please!
    protected $escapeOutput = false;
    protected $escapeChildren = false;

    protected static $clipboard = null;

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

    /**
     * Render an edit link for a given record.
     *
     * @return string the edit link
     */
    public function render()
    {
        // prepare parameters
        $target       = $this->arguments['target'];
        $override     = $this->arguments['override'];
        $irreRelation = $this->determineIrreRelation();

        // do all fields exist in TCA?
        $this->checkTca(
            array_unique(
                array_merge(
                    array_keys($override),
                    array_values($irreRelation)
                )
            )
        );

        if (empty(self::$clipboard)) {
            $this->initializeClipboard();
        }

        $elFromTable = self::$clipboard->elFromTable('tt_content');
        $pasteMode   = self::$clipboard->currentMode();

        if (!empty($elFromTable) && !empty($target)) {
            $pasteItem = substr(key($elFromTable), 11);
            $pasteRecord = BackendUtility::getRecord('tt_content', (int)$pasteItem);
            $pasteTitle = $pasteRecord['header'] ? $pasteRecord['header'] : $pasteItem;

            $pageRenderer = $this->getPageRenderer();
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/DragonDrop/Pastor');

            $link = sprintf('
                <a class="btn btn-default btn-sm ext-dragon-drop-pastor"
                   title="%s"
                   data-mode="%s"
                   data-source="%d"
                   data-title="%s"
                   data-pid="%d"
                   data-override=\'%s\'
                   data-irre=\'%s\'>
                   %s
                </a>',
                $GLOBALS['LANG']->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:cm.pasteinto'),
                $pasteMode,
                $pasteItem,
                $pasteTitle,
                $target['pid'],
                json_encode($override),
                json_encode($irreRelation),
                $this->getText()
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
        $iconFactory = GeneralUtility::makeInstance(
            IconFactory::class
        );
        $icon = $iconFactory->getIcon(
            $key,
            Icon::SIZE_SMALL
        );

        return $icon->render();
    }

    protected function determineIrreRelation()
    {
        if ($this->arguments['irreParentField'] &&
            $this->arguments['irreChildrenField']) {

            return [
                'parent' => $this->arguments['irreParentField'],
                'children' => $this->arguments['irreChildrenField'],
            ];
        }

        // Assumption: it's a EXT:mask IRRE relation with a parent
        // field ending with '_parent'
        foreach (array_keys($this->arguments['override']) as $fieldName) {
            if (str_ends_with($fieldName, '_parent')) {
                return [
                    'parent' => $fieldName,
                    'children' => substr($fieldName, 0, -7), // strip off '_parent'
                ];
            }
        }

        throw new \Exception('Cannot determine IRRE relation automatically. Please specify the attributes "irreParentField" and "irreChildrenField" manually!');
    }
}
