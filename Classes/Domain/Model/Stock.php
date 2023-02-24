<?php

namespace RKW\RkwShop\Domain\Model;

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
 * Class Stock
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Stock extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

     /**
     * amount
     *
     * @var int
     */
    protected $amount = 0;


    /**
     * deliveryStart
     *
     * @var int
     */
    protected $deliveryStart;


    /**
     * comment
     *
     * @var string
     */
    protected $comment;


    /**
     * isExternal
     *
     * @var bool
     */
    protected $isExternal;


    /**
     * Returns the amount
     *
     * @return int $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the amount
     *
     * @param int $amount
     * @return void
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }


    /**
     * Returns the deliveryStart
     *
     * @return int $deliveryStart
     */
    public function getDeliveryStart()
    {
        return $this->deliveryStart;
    }


    /**
     * Sets the deliveryStart
     *
     * @param int $deliveryStart
     * @return void
     */
    public function setDeliveryStart($deliveryStart): void
    {
        $this->deliveryStart = $deliveryStart;
    }



    /**
     * Returns the comment
     *
     * @return string $comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Sets the comment
     *
     * @param string $comment
     * @return void
     */
    public function setComment($comment): void
    {
        $this->comment = $comment;
    }

    /**
     * Returns the isExternal
     *
     * @return bool $isExternal
     */
    public function getIsExternal()
    {
        return $this->isExternal;
    }


    /**
     * Sets the isExternal
     *
     * @param bool $isExternal
     * @return void
     */
    public function setIsExternal($isExternal): void
    {
        $this->isExternal = $isExternal;
    }


}
