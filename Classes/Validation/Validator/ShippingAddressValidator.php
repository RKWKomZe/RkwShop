<?php

namespace RKW\RkwShop\Validation\Validator;

use Madj2k\CoreExtended\Utility\GeneralUtility as Common;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * Class ShippingAddressValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ShippingAddressValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * Validator
     *
     * @var \RKW\RkwShop\Domain\Model\Order $order
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($order): bool
    {
        $isValid = true;
        $settings = $this->getSettings();

        if (
            ($order instanceof \RKW\RkwShop\Domain\Model\Order)
            && ($shippingAddress = $order->getShippingAddress())
            && ($requiredFields = GeneralUtility::trimExplode(',', $settings['requiredFields']))
            && ($methods = get_class_methods($shippingAddress))
        ){

            foreach ($requiredFields as $requiredField) {

                $getter = 'get' . ucfirst($requiredField);
                if (in_array($getter, $methods)) {

                    // check if given fields have been filled out
                    if (
                        (
                            ($requiredField == 'gender')
                            && ($shippingAddress->$getter() == 99)
                        )
                        || (
                            ($requiredField != 'gender')
                            && (empty($shippingAddress->$getter()))
                        )
                    ) {

                        $this->result->forProperty($requiredField)->addError(
                            new \TYPO3\CMS\Extbase\Error\Error(
                                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                    'shippingAddressValidator.notFilled',
                                    'rkw_shop',
                                    [
                                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                            'shippingAddressValidator.field.' . $requiredField,
                                            'rkw_shop'
                                        )
                                    ]
                                ), 1541170767
                            )
                        );
                        $isValid = false;
                    }
                }
            }

            // check validity of e-mail
            if (! \Madj2k\FeRegister\Utility\FrontendUserUtility::isEmailValid($order->getEmail())) {

                $this->result->forProperty($requiredField)->addError(
                    new \TYPO3\CMS\Extbase\Error\Error(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'shippingAddressValidator.invalidEmail',
                            'rkw_shop'
                        ), 1541170768
                    )
                );
                $isValid = false;
            }

        }

        return $isValid;
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return Common::getTypoScriptConfiguration('Rkwshop', $which);
    }
}

