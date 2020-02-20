<?php

namespace RKW\RkwShop\Helper;
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

/**
 * DivUtility
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DivUtility
{

    /**
     * furtherResultsAvailable
     * to manage lazy loading in fluid
     *
     * @author Christian Dilger
     * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array $queryResult
     * @param int $limit
     * @param int $page
     * @param \RKW\RkwShop\Domain\Model\Product &$firstItem
     * @return array
     */
    public static function prepareResultsList($queryResult, $limit, $page = 0, &$firstItem = null)
    {

        $productList = $queryResult->toArray();

//        // kill first item at first :)
//        // we use this item only to be able to append products to existing groupings in frontend
//        if (
//            ($page > 0)
//            && ($productList[0])
//            && ($productList[0] instanceof \RKW\RkwShop\Domain\Model\Product)
//        ) {
//            $firstItem = $productList[0];
//            array_shift($productList);
//        }
//
//
//        // We always have our result set +1 optional result (to know if there would be more results)
//        // If the count of the queryResult greater than the given limit, than there are more results
//        // -> we remove the optional item in this case. Otherwise we do nothing special.
//        if (count($productList) > $limit) {
//            array_pop($productList);
//        }

        return $productList;
        //===
    }

}
