<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwshop_domain_model_orderitem', 'EXT:rkw_shop/Resources/Private/Language/locallang_csh_tx_rkwshop_domain_model_orderitem.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwshop_domain_model_orderitem');
$GLOBALS['TCA']['tx_rkwshop_domain_model_orderitem'] = [
	'ctrl' => [
        'hideTable' => true,
        'title'	=> 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem',
        'label' => 'product',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
        'delete' => 'deleted',
        'searchFields' => 'uid, order, product, amount, is_pre_order',
		'iconfile' => 'EXT:rkw_shop/Resources/Public/Icons/tx_rkwshop_domain_model_orderitem.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'order, product, amount, is_pre_order',
	],
	'types' => [
		'1' => ['showitem' => 'order, product, amount, is_pre_order'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

        'order' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'product' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.product',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_rkwshop_domain_model_product',
                'foreign_table_where' => 'AND tx_rkwshop_domain_model_product.hidden = 0 AND tx_rkwshop_domain_model_product.deleted = 0',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],

        'amount' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.amount',
			'config' => [
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			]
		],

        'is_pre_order' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.isPreOrder',
            'config' => [
                'type' => 'check',
                'readOnly' => 0,
            ],
        ],
	],
];