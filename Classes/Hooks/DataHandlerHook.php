<?php

namespace Kitzberger\DragonDrop\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerHook
{
    protected static $extConf      = [];
    protected static $parentFields = [];

    private $recordBefore        = [];
    private $recordAfter         = [];
    private $parentFieldBefore   = null;
    private $parentFieldAfter    = null;
    private $childrenFieldBefore = null;
    private $childrenFieldAfter  = null;

    /**
     * Build up the internal properties:
     * - parentFieldBefore
     * - parentFieldAfter
     * - childrenFieldBefore
     * - childrenFieldAfter
     * - recordBefore
     * - recordAfter
     *
     * @param  string      $command
     * @param  string      $table
     * @param  int         $id
     * @param  mixed       $value
     * @param  boolean     $commandIsProcessed
     * @param  DataHandler $dataHandler
     * @param  array       $pasteUpdate
     *
     * @return void
     */
    public function processCmdmap($command, $table, $id, $value, $commandIsProcessed, $dataHandler, $pasteUpdate)
    {
        if ($table === 'tt_content') {
            if ($command === 'copy' || $command === 'move') {
                if ($this->getExtConf('parentFields')) {
                    // set parent fields to list set in extension settings
                    self::$parentFields = GeneralUtility::trimExplode(',', $this->getExtConf('parentFields'), true);
                } else {
                    // alternative: determine parent fields by guessing from TCA
                    foreach($GLOBALS['TCA'][$table]['columns'] as $fieldName => $fieldConfig) {
                        if (preg_match('/tx_(.+)_parent$/', $fieldName) &&
                            'passthrough' === $fieldConfig['config']['type']) {
                            self::$parentFields[] = $fieldName;
                        }
                    }
                }

                $this->recordBefore = BackendUtility::getRecord($table, $id, join(',', array_merge(self::$parentFields, ['colPos'])));
                $this->recordAfter = $pasteUpdate;

                foreach (self::$parentFields as $parentField) {
                    if (isset($this->recordBefore[$parentField]) &&
                        !empty($this->recordBefore[$parentField])) {
                        $this->parentFieldBefore = $parentField;
                        $this->childrenFieldBefore = substr($parentField, 0, -7); // strip off '_parent'
                    }
                    if (isset($this->recordAfter[$parentField]) &&
                        !empty($this->recordAfter[$parentField])) {
                        $this->parentFieldAfter = $parentField;
                        $this->childrenFieldAfter = substr($parentField, 0, -7); // strip off '_parent'
                    }
                }

                #var_dump($this->recordBefore, $this->recordAfter);
                #var_dump($this->parentFieldBefore, $this->childrenFieldBefore, $this->parentFieldAfter, $this->childrenFieldAfter); die();
            }
        }
    }

    /**
     * @param  string      $command
     * @param  string      $table
     * @param  int         $id             uid of original record
     * @param  mixed       $value          pid of target record
     * @param  DataHandler $dataHandler
     * @param  array       $pasteUpdate
     * @param  array       $pasteDatamap
     *
     * @return void
     */
    public function processCmdmap_postProcess($command, $table, $id, $value, $dataHandler, $pasteUpdate, &$pasteDatamap)
    {
        if ($table === 'tt_content') {
            // if it's a copy or move operation
            if ($command === 'copy' || $command === 'move') {
                // if colPos 999 is involved
                if ($this->recordBefore['colPos'] == 999 ||
                    $this->recordAfter['colPos'] == 999) {

                    // Determine correct record uid
                    $childUid = $command === 'move' ? $id : $dataHandler->copyMappingArray[$table][$id];

                    #var_dump($childUid, $pasteUpdate, $pasteDatamap); die('DIE!!');

                    try {
                        // -------------------------------------------------
                        // Case one: move from colPos 999 to regular column
                        // -------------------------------------------------
                        if ($this->recordBefore['colPos'] == 999 && $this->recordAfter['colPos'] != 999) {
                            if ($this->parentFieldBefore) {
                                // update child: unset the one parent field
                                $pasteDatamap[$table][$childUid][$this->parentFieldBefore] = 0;

                                // update parent: update counter field
                                $parentUid = $this->recordBefore[$this->parentFieldBefore];
                                $childrenUids = $this->getChildrenUids($table, $this->parentFieldBefore, $parentUid);
                                $childrenUids = array_diff($childrenUids, [$childUid]); // remove child
                                $pasteDatamap[$table][$parentUid][$this->childrenFieldBefore] = join(',', $childrenUids);
                            } else {
                                $dataHandler->newlog('Can\'t move element from mask to regular column due to missing parentFieldBefore!', 1);
                            }
                        }

                        // -------------------------------------------------
                        // Case two: move from regular column to colPos 999
                        // -------------------------------------------------
                        if ($this->recordBefore['colPos'] != 999 && $this->recordAfter['colPos'] == 999) {
                            if ($this->parentFieldAfter) {
                                // update child: set the one parent field
                                $pasteDatamap[$table][$childUid][$this->parentFieldAfter] = $this->recordAfter[$this->parentFieldAfter];

                                // update parent: update counter field
                                $parentUid = $this->recordAfter[$this->parentFieldAfter];
                                $childrenUids = $this->getChildrenUids($table, $this->parentFieldAfter, $parentUid);
                                $childrenUids = array_merge($childrenUids, [$childUid]); // add child
                                $pasteDatamap[$table][$parentUid][$this->childrenFieldAfter] = join(',', $childrenUids);
                            } else {
                                $dataHandler->newlog('Can\'t move element from regular to mask column due to missing parentFieldAfter!', 1);
                            }
                        }

                        // -------------------------------------------------
                        // Case three: stay in colPos 999
                        // -------------------------------------------------
                        if ($this->recordBefore['colPos'] == 999 && $this->recordAfter['colPos'] == 999) {
                            if ($this->parentFieldBefore && $this->parentFieldAfter) {
                                // update child: unset old parent field & set the new parent field
                                $pasteDatamap[$table][$childUid][$this->parentFieldBefore] = 0;
                                $pasteDatamap[$table][$childUid][$this->parentFieldAfter] = $this->recordAfter[$this->parentFieldAfter];

                                // update parent: update both counter fields
                                $parentUid = $this->recordBefore[$this->parentFieldBefore];
                                $childrenUids = $this->getChildrenUids($table, $this->parentFieldBefore, $parentUid);
                                $childrenUids = array_diff($childrenUids, [$childUid]); // remove child
                                $pasteDatamap[$table][$parentUid][$this->childrenFieldBefore] = join(',', $childrenUids);

                                $parentUid = $this->recordAfter[$this->parentFieldAfter];
                                $childrenUids = $this->getChildrenUids($table, $this->parentFieldAfter, $parentUid);
                                $childrenUids = array_merge($childrenUids, [$childUid]); // add child
                                $pasteDatamap[$table][$parentUid][$this->childrenFieldAfter] = join(',', $childrenUids);
                            } else {
                                $dataHandler->newlog('Can\'t move element from one mask column to another due to missing parentFieldBefore or parentFieldAfter!', 1);
                            }
                        }
                    } catch (\Exception $e) {
                        $dataHandler->newlog($e->getMessage(), 1);
                    }

                    #var_dump($childUid, $pasteUpdate, $pasteDatamap); die('DIE!!');
                } else {
                    // Determine correct record uid
                    $childUid = $command === 'move' ? $id : $dataHandler->copyMappingArray[$table][$id];

                    if ($this->parentFieldBefore) {
                        // update parent: update counter field
                        $parentUid = $this->recordBefore[$this->parentFieldBefore];
                        $childrenUids = $this->getChildrenUids($table, $this->parentFieldBefore, $parentUid);
                        $childrenUids = array_diff($childrenUids, [$childUid]); // remove child
                        $pasteDatamap[$table][$parentUid][$this->childrenFieldBefore] = join(',', $childrenUids);

                        // unset parent relation
                        $pasteDatamap[$table][$childUid][$this->parentFieldBefore] = 0;
                    }
                    #var_dump($this->recordBefore['colPos'], $this->recordAfter['colPos'], $this->parentFieldBefore, $this->parentFieldAfter, $pasteDatamap); die();
                }
            }
        }
    }

    /**
     * Get a list of all child element uids of this container element
     *
     * @param  string $table
     * @param  string $parentField
     * @param  int    $parentUid
     *
     * @return array
     */
    protected function getChildrenUids($table, $parentField, $parentUid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $children = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where($queryBuilder->expr()->eq($parentField, $queryBuilder->createNamedParameter($parentUid, \PDO::PARAM_INT)))
            ->execute()
            ->fetchAll();

        return array_column($children, 'uid');
    }

    protected function getExtConf($property)
    {
        if (empty(self::$extConf)) {
            if ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dragon_drop']) {
                self::$extConf = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dragon_drop'];
                self::$extConf = unserialize(self::$extConf);
            }
        }

        return self::$extConf[$property] ?? null;
    }
}
