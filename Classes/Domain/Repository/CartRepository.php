<?php

namespace RKW\RkwShop\Domain\Repository;

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
 * Class CartRepository
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CartRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * Find all carts of a frontendUser
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
     * Find all carts by frontend user session hash
     *
     * @param string $hash
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByFrontendUserOrFrontendUserSessionHash($frontendUser = null, $hash = '')
    {

        $hash = ($hash !== '') ? $hash : $_COOKIE[FrontendUserAuthentication::getCookieName()];

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalOr(
                $query->equals('frontendUserSessionHash', $hash),
                $query->equals('frontendUser', $frontendUser)
            )
        );

        return $query->execute()->getFirst();
        //===
    }

}