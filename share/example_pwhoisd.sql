-- phpMyAdmin SQL Dump
-- version 4.2.4
-- http://www.phpmyadmin.net
--
-- Хост: localhost:3306
-- Время создания: Апр 20 2015 г., 05:04
-- Версия сервера: 5.6.19
-- Версия PHP: 5.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `pwhoisd`
--

-- --------------------------------------------------------

--
-- Структура таблицы `contact_pir`
--

CREATE TABLE IF NOT EXISTS `contact_pir` (
  `ID` varchar(64) NOT NULL,
  `Name` varchar(2048) NOT NULL,
  `Organization` varchar(2048) NOT NULL,
  `Street` text NOT NULL,
  `City` varchar(2048) NOT NULL,
  `StateProvince` varchar(2048) NOT NULL,
  `PostalCode` varchar(256) NOT NULL,
  `Country` varchar(256) NOT NULL,
  `Phone` varchar(256) NOT NULL,
  `PhoneExt` varchar(256) NOT NULL,
  `Fax` varchar(256) NOT NULL,
  `FaxExt` varchar(256) NOT NULL,
  `Email` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `contact_pir`
--

INSERT INTO `contact_pir` (`ID`, `Name`, `Organization`, `Street`, `City`, `StateProvince`, `PostalCode`, `Country`, `Phone`, `PhoneExt`, `Fax`, `FaxExt`, `Email`) VALUES
('DI_10634473', 'Domain Coordination Centre', 'OOO Informacionnye Seti', '1a Pugacheva St.\r\nNOTE: For spam or abuse issues\r\nNOTE: send requests to abuse@hsdn.org', 'Saratov', 'Saratovskaya oblast', '410004', 'RU', '+7.8452220456', '', '', '', 'domains@hsdn.org'),
('GODA-02131674', 'Registration Private', 'Domains By Proxy, LLC', ' DomainsByProxy.com\r\n 14747 N Northsight Blvd Suite 111, PMB 309', 'Scottsdale', 'Arizona', '85260', 'US', '+1.4806242599', '', ' +1.4806242598', '', 'PIR.ORG@domainsbyproxy.com');

-- --------------------------------------------------------

--
-- Структура таблицы `domain_pir`
--

CREATE TABLE IF NOT EXISTS `domain_pir` (
  `DomainName` varchar(256) NOT NULL,
  `DomainID` varchar(64) NOT NULL,
  `CreationDate` timestamp NULL DEFAULT NULL,
  `UpdatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `RegistryExpiryDate` timestamp NULL DEFAULT NULL,
  `TrademarkName` varchar(256) DEFAULT NULL,
  `TrademarkDate` varchar(256) DEFAULT NULL,
  `TrademarkCountry` varchar(256) DEFAULT NULL,
  `TrademarkNumber` varchar(256) DEFAULT NULL,
  `SponsoringRegistrar` varchar(256) NOT NULL,
  `SponsoringRegistrarIANAID` varchar(256) NOT NULL,
  `WHOISServer` varchar(256) NOT NULL,
  `ReferralURL` varchar(256) NOT NULL,
  `DomainStatus` text NOT NULL,
  `RegistrantID` varchar(256) DEFAULT NULL,
  `AdminID` varchar(256) DEFAULT NULL,
  `TechID` varchar(256) DEFAULT NULL,
  `BillingID` varchar(256) DEFAULT NULL,
  `NameServer1` varchar(256) NOT NULL,
  `NameServer2` varchar(256) NOT NULL,
  `NameServer3` varchar(256) NOT NULL,
  `NameServer4` varchar(256) NOT NULL,
  `NameServer5` varchar(256) NOT NULL,
  `NameServer6` varchar(256) NOT NULL,
  `NameServer7` varchar(256) NOT NULL,
  `NameServer8` varchar(256) NOT NULL,
  `NameServer9` varchar(256) NOT NULL,
  `NameServer10` varchar(256) NOT NULL,
  `NameServer11` varchar(256) NOT NULL,
  `NameServer12` varchar(256) NOT NULL,
  `NameServer13` varchar(256) NOT NULL,
  `DNSSEC` varchar(256) NOT NULL DEFAULT 'Unsigned',
  `DSCreated1` timestamp NULL DEFAULT NULL,
  `DSKeyTag1` varchar(256) DEFAULT NULL,
  `Algorithm1` varchar(256) DEFAULT NULL,
  `DigestType1` varchar(256) DEFAULT NULL,
  `Digest1` varchar(256) DEFAULT NULL,
  `DSMaximumSignatureLife1` varchar(256) DEFAULT NULL,
  `DSCreated2` timestamp NULL DEFAULT NULL,
  `DSKeyTag2` varchar(256) DEFAULT NULL,
  `Algorithm2` varchar(256) DEFAULT NULL,
  `DigestType2` varchar(256) DEFAULT NULL,
  `Digest2` varchar(256) DEFAULT NULL,
  `DSMaximumSignatureLife2` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `domain_pir`
--

INSERT INTO `domain_pir` (`DomainName`, `DomainID`, `CreationDate`, `UpdatedDate`, `RegistryExpiryDate`, `TrademarkName`, `TrademarkDate`, `TrademarkCountry`, `TrademarkNumber`, `SponsoringRegistrar`, `SponsoringRegistrarIANAID`, `WHOISServer`, `ReferralURL`, `DomainStatus`, `RegistrantID`, `AdminID`, `TechID`, `BillingID`, `NameServer1`, `NameServer2`, `NameServer3`, `NameServer4`, `NameServer5`, `NameServer6`, `NameServer7`, `NameServer8`, `NameServer9`, `NameServer10`, `NameServer11`, `NameServer12`, `NameServer13`, `DNSSEC`, `DSCreated1`, `DSKeyTag1`, `Algorithm1`, `DigestType1`, `Digest1`, `DSMaximumSignatureLife1`, `DSCreated2`, `DSKeyTag2`, `Algorithm2`, `DigestType2`, `Digest2`, `DSMaximumSignatureLife2`) VALUES
('HSDN.ORG', 'D119032872-LROR', '2006-03-23 10:21:15', '2015-03-11 20:07:03', '2016-03-23 10:21:15', NULL, NULL, NULL, NULL, 'PDR Ltd. d/b/a PublicDomainRegistry.com (R27-LROR)', '303', '', '', 'ok -- http://www.icann.org/epp#ok', 'DI_10634473', 'DI_10634473', 'DI_10634473', NULL, 'NS1.HSDN.ORG', 'NS2.HSDN.ORG', 'NS3.HSDN.ORG', 'NS4.HSDN.ORG', '', '', '', '', '', '', '', '', '', 'Unsigned', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('PIR.ORG', 'D96207-LROR', '1996-02-18 02:00:00', '2015-02-19 22:41:55', '2016-02-19 02:00:00', NULL, NULL, NULL, NULL, 'GoDaddy.com, LLC (R91-LROR)', '146', '', '', 'serverDeleteProhibited -- http://www.icann.org/epp#serverDeleteProhibited\r\nserverTransferProhibited -- http://www.icann.org/epp#serverTransferProhibited\r\nserverUpdateProhibited -- http://www.icann.org/epp#serverUpdateProhibited', 'GODA-02131674', 'GODA-02131674', 'GODA-02131674', NULL, 'NS1.AMS1.AFILIAS-NST.INFO', 'NS1.MIA1.AFILIAS-NST.INFO', 'NS1.SEA1.AFILIAS-NST.INFO', 'NS1.YYZ1.AFILIAS-NST.INFO', '', '', '', '', '', '', '', '', '', 'signedDelegation', '2010-03-26 13:52:50', '54135', '5', '1', '225F055ACB65C8B60AD18B3640062E8C23A5FD89', '1814400 seconds', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `domain_ripn`
--

CREATE TABLE IF NOT EXISTS `domain_ripn` (
`id` int(11) NOT NULL,
  `domain` varchar(256) NOT NULL,
  `nserver1` varchar(256) NOT NULL,
  `nserver2` varchar(256) NOT NULL,
  `nserver3` varchar(256) NOT NULL,
  `nserver4` varchar(256) NOT NULL,
  `state` varchar(256) NOT NULL,
  `person` varchar(256) NOT NULL DEFAULT 'Private person',
  `org` varchar(256) NOT NULL,
  `registrar` varchar(256) NOT NULL,
  `admin_contact` varchar(256) NOT NULL,
  `descr` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_till` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `free_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `source` varchar(256) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `domain_ripn`
--

INSERT INTO `domain_ripn` (`id`, `domain`, `nserver1`, `nserver2`, `nserver3`, `nserver4`, `state`, `person`, `org`, `registrar`, `admin_contact`, `descr`, `created`, `paid_till`, `free_date`, `source`, `updated`) VALUES
(1, 'YA.RU', 'ns1.yandex.ru.', 'ns2.yandex.ru.', '', '', 'REGISTERED, DELEGATED, VERIFIED', '', 'YANDEX, LLC.', 'RU-CENTER-RU', 'https://www.nic.ru/whois', '', '1999-07-11 20:00:00', '2015-04-15 04:36:24', '2015-04-15 04:36:24', 'RIPN', '2015-04-20 02:00:50'),
(2, 'HSDN.RU', 'ns1.hsdn.org.', 'ns2.hsdn.org.', 'ns3.hsdn.org.', 'ns4.hsdn.org.', 'REGISTERED, DELEGATED, VERIFIED', '', 'OOO "Informacionnye Seti"', 'NAUNET-RU', 'domains@hsdn.org', 'NOTE: For spam or abuse issues\r\nNOTE: send requests to abuse@hsdn.org', '2009-02-04 21:00:00', '2016-02-04 21:00:00', '2016-03-06 21:00:00', 'TCI', '2015-04-20 02:01:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_pir`
--
ALTER TABLE `contact_pir`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `domain_pir`
--
ALTER TABLE `domain_pir`
 ADD PRIMARY KEY (`DomainID`), ADD KEY `DomainName` (`DomainName`(255));

--
-- Indexes for table `domain_ripn`
--
ALTER TABLE `domain_ripn`
 ADD PRIMARY KEY (`id`), ADD KEY `updated` (`updated`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `domain_ripn`
--
ALTER TABLE `domain_ripn`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
