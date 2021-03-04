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

    protected function determineIrreRelation()
    {
        if ($this->arguments['irreParentField'] &&
            $this->arguments['irreChildrenField']) {
            return [
                'parent' => $this->arguments['irreParentField'],
                'children' => $this->arguments['irreChildrenField'],
            ];
        }

        if (isset($this->arguments['override']['colPos']) &&
            $this->arguments['override']['colPos'] == 999) {
            // Assumption: with colPos 999 it's most likely a EXT:mask container
            // with IRRE relation having the parent field ending with '_parent'
            foreach (array_keys($this->arguments['override']) as $fieldName) {
                if (preg_match('/_parent$/', $fieldName)) {
                    return [
                        'parent' => $fieldName,
                        'children' => substr($fieldName, 0, -7), // strip off '_parent'
                    ];
                }
            }
        }

        throw new \Exception('Cannot determine IRRE relation automatically. Please specify the attributes "irreParentField" and "irreChildrenField" manually!');
    }
}
