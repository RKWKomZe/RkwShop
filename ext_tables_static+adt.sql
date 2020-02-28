#
# Table structure for table "tx_rkwshop_domain_model_producttype"
#
DROP TABLE IF EXISTS tx_rkwshop_domain_model_producttype;
CREATE TABLE tx_rkwshop_domain_model_producttype (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
    description text,
	class varchar(255) DEFAULT '' NOT NULL,
	has_article_number tinyint(4) unsigned DEFAULT '0' NOT NULL,
	allow_single_order tinyint(4) unsigned DEFAULT '0' NOT NULL,

	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid)

);

INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('1', '0', 'Product', 'Description Typ Product', '\\RKW\\RkwShop\\Domain\\Model\\Product', '0', '1', '0', '0');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('2', '0', 'ProductBundle', 'Description Typ ProductBundle', '\\RKW\\RkwShop\\Domain\\Model\\ProductBundle', '0', '1', '0', '0');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('3', '0', 'ProductSubscription', 'Description Typ ProductSubscription', '\\RKW\\RkwShop\\Domain\\Model\\ProductSubscription', '0', '1', '0', '0');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('4', '0', 'ProductDownload', 'Description Typ ProductDownload', '\\RKW\\RkwShop\\Domain\\Model\\ProductDownload', '0', '1', '0', '0');
