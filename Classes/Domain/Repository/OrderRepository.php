<?php

namespace RKW\RkwShop\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
 * Class OrderRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * Find all orders of a frontendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByFrontendUser($frontendUser)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalAnd(
                $query->equals('frontendUser', $frontendUser)
            )
        );

        return $query->execute();
        //===
    }


    /**
     * Find all orders by frontend user session hash
     *
     * @param string $hash
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     *
     * @throws \RKW\RkwShop\Exceptions\CartHashNotFoundException
     */
    public function findByFrontendUserSessionHash($hash = '')
    {

        $hash = ($hash != '') ? $hash : $_COOKIE[FrontendUserAuthentication::getCookieName()];

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalAnd(
                $query->equals('frontendUserSessionHash', $hash)
            )
        );

        return $query->execute();
        //===
    }

    /**
     * Find all orders that have been updated recently
     *
     * @param integer $timestamp
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     *  @api used by RKW Soap
     */
    public function findByTimestampSoap($timestamp)
    {

        $query = $this->createQuery();

        // $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->greaterThanOrEqual('tstamp', intval($timestamp))
        );

        $query->setOrderings(array('tstamp' => QueryInterface::ORDER_ASCENDING));
        return $query->execute();
    }


    /**
     * Finds an object matching the given identifier.
     *
     * @param int $uid The identifier of the object to find
     * @return \RKW\RkwShop\Domain\Model\Order The matching object if found, otherwise NULL
     * @api used by RKW Soap
     */
    public function findByUidSoap($uid)
    {
        $query = $this->createQuery();

        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->equals('uid', $uid)
        );

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }



}