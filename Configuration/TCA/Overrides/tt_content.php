<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {

    $extConf = null;

    if ($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['dragon_drop']) {
        $extConf = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['dragon_drop'];
    } elseif ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dragon_drop']) {
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dragon_drop']);
    }

    if ($extConf) {
        $parentFields = TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $extConf['parentFields'], true);

        if ($parentFields) {
            foreach ($parentFields as $parentField) {
                if (!isset($GLOBALS['TCA']['tt_content']['columns'][$parentField])) {
                    // Having these fields present in TCA is mandatory for TYPO3's DataHandler
                    // to be able to use those fields when updating records via /ajax/record/process
                    $GLOBALS['TCA']['tt_content']['columns'][$parentField] = [
                        'config' => [
                            'type' => 'passthrough'
                        ],
                    ];
                }
            }
        }
    }
});
