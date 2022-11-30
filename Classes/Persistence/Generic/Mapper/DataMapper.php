<?php
namespace RKW\RkwShop\Persistence\Generic\Mapper;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;


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
 * Class DataMapper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DataMapper extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected $configurationManager;


    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }


    /**
     * Includes references to objects that have been deleted or are hidden
     *
     * @param DomainObjectInterface $parentObject
     * @param string $propertyName
     * @param string $fieldValue
     * @return Persistence\QueryInterface
     * @throws UnexpectedTypeException
     */
    protected function getPreparedQuery(DomainObjectInterface $parentObject, $propertyName, $fieldValue = '')
    {

        // do parent-stuff
        $query = parent::getPreparedQuery($parentObject, $propertyName, $fieldValue);

        // check if there are permanent properties
        if ($this->isPermanentProperty(get_class($parentObject), $propertyName))
        {
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings */
            $querySettings = $query->getQuerySettings();
            $querySettings->setIgnoreEnableFields(true);
            $querySettings->setIncludeDeleted(true);
        }

        return $query;
    }


    /**
     * checks if a property of a parent class is permanent,
     *
     * @param string $classNameParent
     * @param string $propertyName
     * @return bool
     */
    protected function isPermanentProperty($classNameParent, $propertyName)
    {
        if (
            ($list = $this->getPermanentProperties())
            && (isset($list[$classNameParent]))
            && ($listOfProperties = GeneralUtility::trimExplode(',', $list[$classNameParent], true))
            && (in_array($propertyName, $listOfProperties))
        ){
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns TYPO3 settings
     *
     * Typoscript-example
     * plugin.tx_rkwshop {
     *   persistence {
     *     permanentProperties {
     *        RKW\RkwShop\Domain\Model\OrderItem = product
     *        RKW\RkwShop\Domain\Model\Order = frontendUser,shippingAddress
     *        RKW\RkwShop\Domain\Model\Product = productBundle
     *      }
     *   }
     * }
     *
     * @return array
     */
    protected function getPermanentProperties()
    {
        try {
            $settings = $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
                'Rkwshop'
            );
            if (isset($settings['plugin.']['tx_rkwshop.']['persistence.']['permanentProperties.'])) {
                return $settings['plugin.']['tx_rkwshop.']['persistence.']['permanentProperties.'];
            } else {
                return [];
            }

        } catch (\Exception $e) {
            return [];
        }

    }
}
