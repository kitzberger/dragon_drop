<?php

namespace Kitzberger\DragonDrop\ViewHelpers\Be\Uri;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ToggleRecordViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int', 'uid of record to be hidden/unhidden', true);
        $this->registerArgument('table', 'string', 'target database table', true);
        $this->registerArgument('hidden', 'bool', 'current hidden state of the record', false);
        $this->registerArgument('redirect', 'string', 'return to this URL after closing the edit dialog', false, '');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        if ($arguments['uid'] < 1) {
            throw new \InvalidArgumentException('Uid must be a positive integer, ' . $arguments['uid'] . ' given.', 1526128259);
        }
        if (empty($arguments['redirect'])) {
            $arguments['redirect'] = GeneralUtility::getIndpEnv('REQUEST_URI');
        }

        if (isset($arguments['hidden'])) {
            $hiddenColumn = $GLOBALS['TCA'][$arguments['table']]['ctrl']['enablecolumns']['disabled'];
            $params = [
                'data' => [$arguments['table'] => [$arguments['uid'] => [$hiddenColumn => $arguments['hidden'] ? 0 : 1]]],
                'redirect' => $arguments['redirect'],
            ];
        } else {
            $params = [
                'cmd' => [$arguments['table'] => [$arguments['uid'] => ['delete' => 1]]],
                'redirect' => $arguments['redirect'],
            ];
        }

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute('tce_db', $params);
    }
}
