<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TCA']['tx_rkwshop_domain_model_stock'] = [
	'ctrl' => [
        'hideTable' => true,
        'title'	=> 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_stock',
		'label' => 'comment',
        'label_alt' => 'amount',
        'label_alt_force' => 1,
		'default_sortby' => 'ORDER BY crdate',
        'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'delete' => 'deleted',
		'enablecolumns' => [
            'disabled' => 'hidden',
        ],
		'searchFields' => 'uid, product,amount,delivery_start,comment,',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('rkw_shop') . 'Resources/Public/Icons/tx_rkwshop_domain_model_stock.gif',
	],
	'interface' => [
		'showRecordFieldList' => 'product,amount,comment,delivery_start',
	],
	'types' => [
		'0' => ['showitem' => 'product,amount,comment,delivery_start'],
    ],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

	    'product' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'amount' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_stock.amount',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,int,required',
                'default' => 500
            ],
        ],

		'comment' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_stock.comment',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			],
		],
        'delivery_start' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_stock.deliveryStart',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime',
            ],
        ],
    ],
];