<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_cart',
        'label' => 'status',
        'label_alt' => 'email',
        'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
		],
        'default_sortby' => 'ORDER BY crdate ASC',
        'searchFields' => 'uid,frontend_user,pages,',
		'iconfile' => 'EXT:rkw_shop/Resources/Public/Icons/tx_rkwshop_domain_model_cart.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'hidden, frontend_user, order_item',
	],
	'types' => [
		'1' => ['showitem' => 'hidden,--palette--;;1, frontend_user, order_item'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

		'hidden' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'frontend_user' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_cart.frontendUser',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'fe_users',
				'foreign_table_where' => 'AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY username ASC',
				'minitems' => 1,
				'maxitems' => 1,
                'readOnly' => true
			],
		],
        'frontend_user_session_hash' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_cart.remark',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'readOnly' => true
            ]
        ],
        'order_item' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_cart.orderItem',
            'config' => [
                'type' => 'inline',
                'internal_type' => 'db',
                'foreign_table' => 'tx_rkwshop_domain_model_cartitem',
                'foreign_field' => 'ext_order',
                'minitems' => 1,
                'maxitems' => 99,
                'size'  => 5,
                'show_thumbs' =>  true,
                'appearance' => [
                    'elementBrowserType' => 'db',
                    'useSortable' => false,
                    'showPossibleLocalizationRecords' => false,
                    'showRemovedLocalizationRecords' => false,
                    'showSynchronizationLink' => false,
                    'showAllLocalizationLink' => false,
                    'enabledControls' => [
                        'info' => true,
                        'new' => true,
                        'dragdrop' => false,
                        'sort' => false,
                        'hide' => false,
                        'delete' => true,
                        'localize' => false,
                    ],
                ],
                'readOnly' => true
            ],
        ],
	],
];