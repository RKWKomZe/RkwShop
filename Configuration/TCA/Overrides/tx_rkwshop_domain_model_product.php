<?php

$settings = \RKW\RkwBasics\Helper\Common::getTyposcriptConfiguration('Rkwshop', \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT)['plugin.']['tx_rkwshop.']['settings.'];
$_LLL = 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf';

// Extend TCA when rkw_authors is available
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_authors')) {

    $GLOBALS['TCA']['tx_rkwshop_domain_model_product']['columns']['author'] = [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.author',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_rkwauthors_domain_model_authors',
            'foreign_table_where' => 'AND ((\'###PAGE_TSCONFIG_IDLIST###\' <> \'0\' AND FIND_IN_SET(tx_rkwauthors_domain_model_authors.pid,\'###PAGE_TSCONFIG_IDLIST###\')) OR (\'###PAGE_TSCONFIG_IDLIST###\' = \'0\')) AND tx_rkwauthors_domain_model_authors.sys_language_uid = ###REC_FIELD_sys_language_uid### ORDER BY tx_rkwauthors_domain_model_authors.last_name ASC',
            'maxitems'      => 9999,
            'minitems'      => 0,
            'size'          => 5,
        ],
    ];
    $GLOBALS['TCA']['tx_rkwshop_domain_model_product']['types'][0]['showitem'] = str_replace(', publishing_date,', ', publishing_date, author,', $GLOBALS['TCA']['tx_rkwshop_domain_model_product']['types'][0]['showitem']);
}
