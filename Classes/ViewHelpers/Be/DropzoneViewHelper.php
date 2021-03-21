<?php

namespace Kitzberger\DragonDrop\ViewHelpers\Be;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DropzoneViewHelper extends AbstractViewHelper
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
            'allowed',
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
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/DragonDrop/Dropstor');
        $pageRenderer->addCssFile('EXT:dragon_drop/Resources/Public/CSS/Backend.css');

        // prepare parameters
        $target       = $this->arguments['target'];
        $override     = $this->arguments['override'];
        $allowed      = GeneralUtility::trimExplode(',', $this->arguments['allowed'], true);

        // do all fields exist in TCA?
        $this->checkTca(array_keys($override));

        if ($allowed && !in_array($pasteRecord['CType'], $allowed)) {
            return '';
        }

        // create link
        $link = sprintf('
            <div class="dropstor-dropzone" data-override=\'%s\'></div>
            ',
            json_encode($override)
        );

        return $link;
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
