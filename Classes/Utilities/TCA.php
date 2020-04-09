<?php
namespace RKW\RkwShop\Utilities;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * TCA
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TCA
{

    public function buildProductTitle(&$parameters)
    {
        $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);

        $recordProductType = BackendUtility::getRecord('tx_rkwshop_domain_model_producttype', $record['product_type']);

        $newTitle = $record['title'] . ' [' . $record['article_number'] . ' - ' . $recordProductType['title'] . ']';

        $parameters['title'] = $newTitle;
    }


    public function buildOrderTitle(&$parameters)
    {
        $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        $status = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.status.' . $record['status'], 'rkw_shop'
        );

        $frontendUser = BackendUtility::getRecord('fe_users', $record['frontend_user']);

        $newTitle = date('d.m.Y H:i', $record['crdate']) . ': ' . $record['order_number'] . ' [' . $frontendUser['first_name'] . ' ' . $frontendUser['last_name'] . '] - ' . $status;

        $parameters['title'] = $newTitle;
    }

    public function buildCartTitle(&$parameters)
    {
        $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);

        $newTitle = date('d.m.Y H:i', $record['crdate']);

        if ($record['frontend_user']) {
            $frontendUser = BackendUtility::getRecord('fe_users', $record['frontend_user']);

            $newTitle = date('d.m.Y H:i', $record['crdate']) . ': ' . $frontendUser['first_name'] . ' ' . $frontendUser['last_name'];
        }

        $parameters['title'] = $newTitle;
    }

}