#
# Table structure for table "tx_rkwshop_domain_model_producttype"
#
DROP TABLE IF EXISTS tx_rkwshop_domain_model_producttype;
CREATE TABLE tx_rkwshop_domain_model_producttype (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
    description text,
	model varchar(255) DEFAULT '' NOT NULL,
	has_sku tinyint(4) unsigned DEFAULT '0' NOT NULL,

	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid)

);

INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('1', '0', 'Product', 'Description Typ Product', '\\RKW\\RkwShop\\Domain\\Model\\Product', '1', '0', '0', '0', '0', '');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('2', '0', 'ProductBundle', 'Description Typ ProductBundle', '\\RKW\\RkwShop\\Domain\\Model\\ProductBundle', '1', '0', '0', '0', '0', '');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('3', '0', 'ProductSubscription', 'Description Typ ProductSubscription', '\\RKW\\RkwShop\\Domain\\Model\\ProductSubscription', '1', '0', '0', '0', '0', '');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('4', '0', 'ProductDownload', 'Description Typ ProductDownload', '\\RKW\\RkwShop\\Domain\\Model\\ProductDownload', '1', '0', '0', '0', '0', '');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('5', '0', 'ProductCollection', 'Description Typ ProductCollection', '\\RKW\\RkwShop\\Domain\\Model\\ProductCollection', '0', '0', '0', '0', '0', '');

