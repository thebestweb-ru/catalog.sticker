CREATE TABLE IF NOT EXISTS `tbw_catalog_sticker_list`
(
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(3) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `DATE_START` DATETIME DEFAULT NULL,
  `DATE_END` DATETIME DEFAULT NULL,
  `ACTIVE` char(1) DEFAULT '1',
  `SORT` int(11) DEFAULT '500',
  `TYPE` varchar(255) NOT NULL,
  `TYPE_OPTIONS` TEXT DEFAULT NULL,
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `tbw_catalog_sticker_list_sections`
(
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LIST_ID` int(11) NOT NULL,
  `IBLOCK_ID` int(11) NOT NULL,
  `SECTION_ID` varchar(255) NOT NULL,
  `TROUGHT_SECTION` char(1) DEFAULT '1',
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `tbw_catalog_sticker_item`
(
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LIST_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DATE_START` DATETIME DEFAULT NULL,
  `DATE_END` DATETIME DEFAULT NULL,
  `ACTIVE` char(1) DEFAULT '1',
  `SORT` int(11) DEFAULT '500',
  `TYPE` varchar(255) NOT NULL,
  `TYPE_OPTIONS` TEXT DEFAULT NULL,
  PRIMARY KEY (`ID`)
);

ALTER TABLE tbw_catalog_sticker_list_sections
   ADD FOREIGN KEY (`LIST_ID`) REFERENCES tbw_catalog_sticker_list (`ID`);
ALTER TABLE tbw_catalog_sticker_item
   ADD FOREIGN KEY (`LIST_ID`) REFERENCES tbw_catalog_sticker_list (`ID`);