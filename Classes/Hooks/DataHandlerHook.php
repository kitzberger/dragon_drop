<?php

namespace Kitzberger\DragonDrop\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerHook
{
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
            if ($command === 'copy' && isset($dataHandler->datamap['dragon_drop_irre'])) {
                $irreRelation  = $dataHandler->datamap['dragon_drop_irre'];
                $parentField   = $irreRelation['parent'];
                $childrenField = $irreRelation['children'];

                if ($pasteUpdate[$parentField]) {
                    // container element
                    $parentUid = $pasteUpdate[$parentField];

                    // get a list of all child element uids of this container element
                    $children = $this->getChildrenUids($table, $parentField, $parentUid);

                    // add the new elements to this list
                    $children = array_merge($children, array_keys($pasteDatamap[$table]));

                    // set full list
                    $pasteDatamap[$table][$parentUid][$childrenField] = join(',', $children);
                }

                unset($dataHandler->datamap['dragon_drop_irre']);
            } elseif ($command === 'copy' || $command === 'move') {
                $record = BackendUtility::getRecord($table, $id);

                if ($record['colPos'] == 999) {
                    // record has probably been inside a EXT:mask IRRE relation!

                    // find the parent field that holds a value
                    $possibleParentFields = [];
                    foreach($record as $fieldName => $value) {
                        if (preg_match('/_parent$/', $fieldName) && !empty($value)) {
                            $possibleParentFields[] = $fieldName;
                        }
                    }
                    if (count($possibleParentFields) === 1) {
                        // unset the parent field
                        $id = $command === 'move' ? $id : $dataHandler->copyMappingArray[$table][$id];
                        $pasteDatamap[$table][$id][$possibleParentFields[0]] = 0;
                    } else {
                        // somethings weird!
                        // flashmessages don't seem to be working from here ;-/
                        // maybe use DataHandlers->log() instead?!
                    }

                    #var_dump($record['colPos'], $possibleParentFields, $pasteUpdate, $pasteDatamap); die('DIE!!');
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
}
