
--
-- Table structure for table `vol_client_association`
--

DROP TABLE IF EXISTS `vol_client_association`;
CREATE TABLE IF NOT EXISTS `vol_client_association` (
  `vca_lKeyID`        int(11) NOT NULL AUTO_INCREMENT,
  `vca_lVolID`        int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to volunteers',
  `vca_lClientID`     int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to client_records',
  `vca_Notes`         text,
  `vca_bRetired`      tinyint(1) NOT NULL DEFAULT '0',
  `vca_lOriginID`     int(11) NOT NULL DEFAULT '0',
  `vca_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
  `vca_dteOrigin`     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vca_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  
  PRIMARY KEY (`vca_lKeyID`),
  KEY `ufddl_lFieldID` (`vca_lVolID`),
  KEY `ufddl_lSortIDX` (`vca_lClientID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Associates volunteers with clients' AUTO_INCREMENT=1 ;
