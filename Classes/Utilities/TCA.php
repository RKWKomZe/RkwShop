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

    public function productTitle(&$parameters)
    {
        $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);

        $newTitle = '[' . $record['article_number'] . ' - ' . $record['record_type'] . '] ' . $record['title'];

        if ($record['subtitle'] !== '') {
            $newTitle = $newTitle . ', ' . $record['subtitle'];
        }

        $parameters['title'] = $newTitle;
    }

}