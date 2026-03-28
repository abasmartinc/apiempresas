SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `apiempresas`.`cnae_sections` LIKE `verifica_empresas`.`cnae_sections`;
INSERT IGNORE INTO `apiempresas`.`cnae_sections` SELECT * FROM `verifica_empresas`.`cnae_sections`;

CREATE TABLE IF NOT EXISTS `apiempresas`.`cnae_groups` LIKE `verifica_empresas`.`cnae_groups`;
INSERT IGNORE INTO `apiempresas`.`cnae_groups` SELECT * FROM `verifica_empresas`.`cnae_groups`;

CREATE TABLE IF NOT EXISTS `apiempresas`.`cnae_classes` LIKE `verifica_empresas`.`cnae_classes`;
INSERT IGNORE INTO `apiempresas`.`cnae_classes` SELECT * FROM `verifica_empresas`.`cnae_classes`;

CREATE TABLE IF NOT EXISTS `apiempresas`.`cnae_subclasses` LIKE `verifica_empresas`.`cnae_subclasses`;
INSERT IGNORE INTO `apiempresas`.`cnae_subclasses` SELECT * FROM `verifica_empresas`.`cnae_subclasses`;

SET FOREIGN_KEY_CHECKS = 1;
