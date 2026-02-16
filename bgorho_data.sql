-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 16 Şub 2026, 08:32:50
-- Sunucu sürümü: 8.0.32-cll-lve
-- PHP Sürümü: 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `bgorho_data`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_admin_logs`
--

CREATE TABLE `dle_admin_logs` (
  `id` int NOT NULL,
  `name` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` int UNSIGNED NOT NULL DEFAULT '0',
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `action` int NOT NULL DEFAULT '0',
  `extras` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_admin_logs`
--

INSERT INTO `dle_admin_logs` (`id`, `name`, `date`, `ip`, `action`, `extras`) VALUES
(1, 'admin', 1763470873, '31.171.106.163', 82, ''),
(2, 'admin', 1763470882, '31.171.106.163', 14, 'FAQ'),
(3, 'admin', 1763470889, '31.171.106.163', 26, 'Добро пожаловать'),
(4, 'admin', 1763470889, '31.171.106.163', 26, 'Приобретение и оплата скрипта'),
(5, 'admin', 1763470889, '31.171.106.163', 26, 'Осуществление технической поддержки скрипта'),
(6, 'admin', 1763471156, '31.171.106.163', 82, ''),
(7, 'admin', 1763471205, '31.171.106.163', 48, ''),
(8, 'admin', 1763471228, '31.171.106.163', 48, ''),
(9, 'admin', 1763471386, '31.171.106.163', 14, 'Slider'),
(10, 'admin', 1763471434, '31.171.106.163', 36, 'drop.webp'),
(11, 'admin', 1763471458, '31.171.106.163', 1, 'BLACK GOLD - GLOBAL STARTUP PITCHDECK'),
(12, 'admin', 1763472902, '31.171.106.163', 14, 'About'),
(13, 'admin', 1763475027, '31.171.106.137', 14, 'Vision and mission'),
(14, 'admin', 1763475039, '31.171.106.137', 14, 'Black Gold'),
(15, 'admin', 1763475049, '31.171.106.137', 14, 'Green Commitment'),
(16, 'admin', 1763475059, '31.171.106.137', 14, 'BGC Token Giveaway'),
(17, 'admin', 1763475560, '31.171.106.137', 1, 'Post 1'),
(18, 'admin', 1763475664, '31.171.106.137', 14, 'Vision and mission'),
(19, 'admin', 1763475669, '31.171.106.137', 14, 'Black Gold'),
(20, 'admin', 1763475674, '31.171.106.137', 14, 'Green Commitment'),
(21, 'admin', 1763475679, '31.171.106.137', 14, 'BGC Token Giveaway'),
(22, 'admin', 1763475731, '31.171.106.137', 1, 'Post 2'),
(23, 'admin', 1763476101, '31.171.106.137', 14, 'Bgorium Cards'),
(24, 'admin', 1763476589, '31.171.106.137', 12, 'Service'),
(25, 'admin', 1763476744, '31.171.106.137', 36, 'blockchain.webp'),
(26, 'admin', 1763476749, '31.171.106.137', 1, 'Blockchain Technology'),
(27, 'admin', 1763476785, '31.171.106.137', 36, 'fintech.webp'),
(28, 'admin', 1763476800, '31.171.106.137', 1, 'Fintech Solutions'),
(29, 'admin', 1763476834, '31.171.106.137', 36, 'tradingplat.webp'),
(30, 'admin', 1763476838, '31.171.106.137', 1, 'Trading Platforms'),
(31, 'admin', 1763476871, '31.171.106.137', 36, 'da22698d7ad51d10c6f98bcbcbc810e1-removebg-preview.png'),
(32, 'admin', 1763476876, '31.171.106.137', 1, 'Environmental Initiatives'),
(33, 'admin', 1763476905, '31.171.106.137', 36, 'p-main__list-icon.png'),
(34, 'admin', 1763476909, '31.171.106.137', 1, 'Education &amp; Training'),
(35, 'admin', 1763477248, '31.171.106.137', 25, 'Education &amp; Training'),
(36, 'admin', 1763477306, '31.171.106.137', 14, 'Service'),
(37, 'admin', 1763477342, '31.171.106.137', 14, 'Service'),
(38, 'admin', 1763477361, '31.171.106.137', 14, 'Service'),
(39, 'admin', 1763477835, '31.171.106.137', 74, 'title1'),
(40, 'admin', 1763477850, '31.171.106.137', 74, 'desc1'),
(41, 'admin', 1763477862, '31.171.106.137', 74, 'title2'),
(42, 'admin', 1763477873, '31.171.106.137', 74, 'desc2'),
(43, 'admin', 1763477884, '31.171.106.137', 74, 'title3'),
(44, 'admin', 1763477894, '31.171.106.137', 74, 'desc3'),
(45, 'admin', 1763477941, '31.171.106.137', 25, 'Education &amp; Training'),
(46, 'admin', 1763477993, '31.171.106.137', 25, 'Environmental Initiatives'),
(47, 'admin', 1763478006, '31.171.106.137', 25, 'Trading Platforms'),
(48, 'admin', 1763478023, '31.171.106.137', 25, 'Fintech Solutions'),
(49, 'admin', 1763478038, '31.171.106.137', 25, 'Blockchain Technology'),
(50, 'admin', 1763478172, '31.171.106.137', 82, ''),
(51, 'admin', 1763478282, '31.171.106.137', 26, 'Post 1'),
(52, 'admin', 1763478295, '31.171.106.137', 25, 'Post 2'),
(53, 'admin', 1763478314, '31.171.106.137', 25, 'Post 2'),
(54, 'admin', 1763478326, '31.171.106.137', 25, 'Post 2'),
(55, 'admin', 1763478377, '31.171.106.137', 25, 'Education &amp; Training'),
(56, 'admin', 1763478635, '31.171.106.137', 82, ''),
(57, 'admin', 1763478780, '31.171.106.137', 12, 'Tətbiq sahələri'),
(58, 'admin', 1763478894, '31.171.106.137', 36, 'ecology.webp'),
(59, 'admin', 1763478928, '31.171.106.137', 1, 'Ecology &amp; Projects'),
(60, 'admin', 1763479085, '31.171.106.137', 36, 'oil.webp'),
(61, 'admin', 1763479099, '31.171.106.137', 1, 'Oil prices'),
(62, 'admin', 1763479120, '31.171.106.137', 36, 'bank.png'),
(63, 'admin', 1763479130, '31.171.106.137', 1, 'Bank &amp; Cards'),
(64, 'admin', 1763479147, '31.171.106.137', 36, 'statik.webp'),
(65, 'admin', 1763479172, '31.171.106.137', 1, 'Trading &amp; Marketplaces'),
(66, 'admin', 1763479198, '31.171.106.137', 36, 'renewable-energy-3d-icon-png-download-5386087.webp'),
(67, 'admin', 1763479202, '31.171.106.137', 1, 'Energy Resources &amp; Staking'),
(68, 'admin', 1763479221, '31.171.106.137', 36, 'share-3d-icon-isolated-on-transparent-background-gold-texture-3d-rendering-free-png.webp'),
(69, 'admin', 1763479230, '31.171.106.137', 1, 'Company shares'),
(70, 'admin', 1763479823, '31.171.106.137', 14, 'Tətbiq sahələri'),
(71, 'admin', 1763479939, '31.171.106.137', 14, 'Tətbiq sahələri'),
(72, 'admin', 1763479981, '31.171.106.137', 74, 'title1'),
(73, 'admin', 1763479987, '31.171.106.137', 74, 'desc1'),
(74, 'admin', 1763479992, '31.171.106.137', 74, 'title2'),
(75, 'admin', 1763479999, '31.171.106.137', 74, 'desc2'),
(76, 'admin', 1763480004, '31.171.106.137', 74, 'title3'),
(77, 'admin', 1763480010, '31.171.106.137', 74, 'desc3'),
(78, 'admin', 1763480034, '31.171.106.137', 25, 'Company shares'),
(79, 'admin', 1763480218, '31.171.106.137', 25, 'Trading &amp; Marketplaces'),
(80, 'admin', 1763480231, '31.171.106.137', 25, 'Bank &amp; Cards'),
(81, 'admin', 1763480249, '31.171.106.137', 25, 'Oil prices'),
(82, 'admin', 1763480265, '31.171.106.137', 25, 'Ecology &amp; Projects'),
(83, 'admin', 1763480316, '31.171.106.137', 25, 'Energy Resources &amp; Staking'),
(84, 'admin', 1763535051, '31.171.106.137', 86, 'https://bgorium.81.whm.az/admin.php?mod=editnews&amp;action=list'),
(85, 'admin', 1763535233, '31.171.106.138', 89, ''),
(86, 'admin', 1763535236, '31.171.106.138', 89, ''),
(87, 'admin', 1763535277, '31.171.106.138', 82, ''),
(88, 'admin', 1763535862, '31.171.106.138', 25, 'Company shares'),
(89, 'admin', 1763535880, '31.171.106.138', 25, 'Energy Resources &amp; Staking'),
(90, 'admin', 1763535894, '31.171.106.138', 25, 'Trading &amp; Marketplaces'),
(91, 'admin', 1763535909, '31.171.106.138', 25, 'Oil prices'),
(92, 'admin', 1763535927, '31.171.106.138', 25, 'Bank &amp; Cards'),
(93, 'admin', 1763535938, '31.171.106.138', 25, 'Ecology &amp; Projects'),
(94, 'admin', 1763541273, '31.171.106.137', 82, ''),
(95, 'admin', 1763541278, '31.171.106.137', 48, ''),
(96, 'admin', 1763548524, '31.171.106.138', 86, 'Direct DLE Adminpanel'),
(97, 'admin', 1763562927, '31.171.106.137', 82, ''),
(98, 'admin', 1763640279, '31.171.106.138', 86, 'Direct DLE Adminpanel'),
(99, 'admin', 1763640845, '31.171.106.137', 82, ''),
(100, 'admin', 1763641252, '31.171.106.137', 48, ''),
(101, 'admin', 1763645022, '31.171.106.138', 89, ''),
(102, 'admin', 1763645042, '31.171.106.138', 89, ''),
(103, 'admin', 1763645083, '31.171.106.138', 82, ''),
(104, 'admin', 1763645217, '31.171.106.138', 1, 'Post 1'),
(105, 'admin', 1763645228, '31.171.106.138', 26, 'Post 2'),
(106, 'admin', 1763645328, '31.171.106.138', 1, 'BGC Token'),
(107, 'admin', 1763645512, '31.171.106.138', 74, 'title4'),
(108, 'admin', 1763645525, '31.171.106.138', 74, 'desc4'),
(109, 'admin', 1763645702, '31.171.106.138', 25, 'Education &amp; Training'),
(110, 'admin', 1763645781, '31.171.106.138', 25, 'Environmental Initiatives'),
(111, 'admin', 1763645834, '31.171.106.138', 25, 'Trading Platforms'),
(112, 'admin', 1763645890, '31.171.106.138', 25, 'Fintech Solutions'),
(113, 'admin', 1763646905, '31.171.106.138', 1, 'Post 1'),
(114, 'admin', 1763647049, '31.171.106.138', 1, 'Black gold'),
(115, 'admin', 1763647205, '31.171.106.138', 38, ''),
(116, 'admin', 1763647207, '31.171.106.138', 38, ''),
(117, 'admin', 1763649162, '31.171.106.137', 86, 'https://bgorium.com/admin.php'),
(118, 'admin', 1763650056, '31.171.106.137', 82, ''),
(119, 'admin', 1763650069, '31.171.106.137', 38, ''),
(120, 'admin', 1763967944, '31.171.106.137', 86, 'https://bgorium.com/admin.php'),
(121, 'admin', 1764676736, '31.171.106.244', 82, ''),
(122, 'admin', 1764676743, '31.171.106.244', 12, 'Blog'),
(123, 'admin', 1764677081, '31.171.106.244', 1, 'What Is BlackGold Coin? The Complete Beginner’s Guide'),
(124, 'admin', 1764677102, '31.171.106.244', 1, 'Why Asset-Backed Cryptocurrencies Are the Future of Digital Finance'),
(125, 'admin', 1764677126, '31.171.106.244', 1, 'How BlackGold Coin Generates Passive Income Through Staking'),
(126, 'admin', 1764677151, '31.171.106.244', 1, 'The Vision Behind BGORIUM: A New Standard in Fintech'),
(127, 'admin', 1764677169, '31.171.106.244', 1, 'Tokenization of Real Assets: How BlackGold Coin Leads the Movement'),
(128, 'admin', 1764677185, '31.171.106.244', 1, 'Energy-Backed Cryptocurrencies: Why They Are More Resilient'),
(129, 'admin', 1764677202, '31.171.106.244', 1, 'BlackGold Coin vs. Traditional Cryptocurrencies: What Makes It Different?'),
(130, 'admin', 1764677224, '31.171.106.244', 1, 'The BlackGold Card: A New Way to Use Crypto in Everyday Life'),
(131, 'admin', 1764677265, '31.171.106.244', 25, 'The Vision Behind BGORIUM: A New Standard in Fintech'),
(132, 'admin', 1764677271, '31.171.106.244', 36, 'dalle-2025-12-02-16_07_21-a-high-detail-3d-golden-coin-with-the-letters-bgc-engraved-glowing-energy-rings-behind-it-black-and-gold-color-palette-futuristic-blockchain-backgr.webp'),
(133, 'admin', 1764677274, '31.171.106.244', 25, 'What Is BlackGold Coin? The Complete Beginner’s Guide'),
(134, 'admin', 1764677316, '31.171.106.244', 36, 'dalle-2025-12-02-16_08_29-a-balanced-scale-with-a-crypto-coin-on-one-side-and-barrels-of-oil-plus-renewable-energy-icons-on-the-other-symbolizing-asset-backed-stability_-elega.webp'),
(135, 'admin', 1764677318, '31.171.106.244', 25, 'Why Asset-Backed Cryptocurrencies Are the Future of Digital Finance'),
(136, 'admin', 1764677362, '31.171.106.244', 36, 'dalle-2025-12-02-16_09_16-a-glowing-digital-vault-with-blackgold-coins-entering-and-multiplying-representing-passive-income_-a-financial-growth-chart-in-the-background-black.webp'),
(137, 'admin', 1764677395, '31.171.106.244', 25, 'How BlackGold Coin Generates Passive Income Through Staking'),
(138, 'admin', 1764677400, '31.171.106.244', 36, 'dalle-2025-12-02-16_09_51-a-futuristic-smart-city-skyline-connected-by-glowing-blockchain-lines-with-a-blackgold-coin-symbol-floating-above-the-city_-clean-fintech-aesthetic.webp'),
(139, 'admin', 1764677403, '31.171.106.244', 25, 'The Vision Behind BGORIUM: A New Standard in Fintech'),
(140, 'admin', 1764677450, '31.171.106.244', 36, 'dalle-2025-12-02-16_10_46-oil-barrels-solar-panels-and-gold-bars-transforming-into-glowing-blockchain-tokens_-particle-effects-and-dynamic-motion-black-background-with-gold.webp'),
(141, 'admin', 1764677452, '31.171.106.244', 25, 'Tokenization of Real Assets: How BlackGold Coin Leads the Movement'),
(142, 'admin', 1764677485, '31.171.106.244', 36, 'dalle-2025-12-02-16_11_21-a-glowing-blackgold-coin-connected-to-oil-rigs-and-renewable-energy-turbines-via-energy-beams_-dramatic-lighting-black-and-amber-color-palette-ultra.webp'),
(143, 'admin', 1764677488, '31.171.106.244', 25, 'Energy-Backed Cryptocurrencies: Why They Are More Resilient'),
(144, 'admin', 1764677646, '31.171.106.244', 36, 'dalle-2025-12-02-16_14_02-a-comparison-visual-showing-a-shaky-fragmented-crypto-coin-on-the-left-side-and-a-solid-glowing-blackgold-coin-on-the-right-side_-the-theme-is-stabi.webp'),
(145, 'admin', 1764677650, '31.171.106.244', 25, 'BlackGold Coin vs. Traditional Cryptocurrencies: What Makes It Different?'),
(146, 'admin', 1764677700, '31.171.106.244', 36, 'dalle-2025-12-02-16_14_54-a-luxurious-black-and-gold-crypto-debit-card-with-the-bgorium-emblem-shining-under-a-focused-spotlight_-minimalistic-dark-background-premium-fintech.webp'),
(147, 'admin', 1764677702, '31.171.106.244', 25, 'The BlackGold Card: A New Way to Use Crypto in Everyday Life'),
(148, 'admin', 1765198907, '31.171.106.244', 86, 'Direct DLE Adminpanel'),
(149, 'admin', 1765199048, '31.171.106.244', 116, 'Multi-Language'),
(150, 'admin', 1765267903, '31.171.106.244', 86, 'https://bgorium.com/admin.php?mod=multilanguage'),
(151, 'admin', 1765353892, '31.171.106.206', 86, 'Direct DLE Adminpanel'),
(152, 'admin', 1765354930, '31.171.106.243', 82, ''),
(153, 'admin', 1765355265, '31.171.106.243', 25, 'The BlackGold Card: A New Way to Use Crypto in Everyday Life'),
(154, 'admin', 1765355355, '31.171.106.243', 25, 'The BlackGold Card: A New Way to Use Crypto in Everyday Life'),
(155, 'admin', 1765355550, '31.171.106.243', 25, 'BlackGold Coin vs. Traditional Cryptocurrencies: What Makes It Different?'),
(156, 'admin', 1765355700, '31.171.106.243', 25, 'Energy-Backed Cryptocurrencies: Why They Are More Resilient'),
(157, 'admin', 1765355725, '31.171.106.243', 25, 'The BlackGold Card: A New Way to Use Crypto in Everyday Life'),
(158, 'admin', 1765355738, '31.171.106.243', 25, 'BlackGold Coin vs. Traditional Cryptocurrencies: What Makes It Different?'),
(159, 'admin', 1765355939, '31.171.106.243', 25, 'Tokenization of Real Assets: How BlackGold Coin Leads the Movement'),
(160, 'admin', 1765356230, '31.171.106.243', 25, 'The Vision Behind BGORIUM: A New Standard in Fintech'),
(161, 'admin', 1765356248, '31.171.106.243', 25, 'The Vision Behind BGORIUM: A New Standard in Fintech'),
(162, 'admin', 1765356416, '31.171.106.243', 25, 'How BlackGold Coin Generates Passive Income Through Staking'),
(163, 'admin', 1765356909, '31.171.106.243', 25, 'Why Asset-Backed Cryptocurrencies Are the Future of Digital Finance'),
(164, 'admin', 1765357075, '31.171.106.243', 25, 'What Is BlackGold Coin? The Complete Beginner’s Guide'),
(165, 'admin', 1765357445, '31.171.106.243', 25, 'Black gold'),
(166, 'admin', 1765360987, '31.171.106.243', 86, 'https://bgorium.com/admin.php?mod=editnews&amp;action=editnews&amp;id=21'),
(167, 'admin', 1765361344, '31.171.106.243', 25, 'Post 1'),
(168, 'admin', 1765361458, '31.171.106.243', 25, 'Black gold'),
(169, 'admin', 1765361677, '31.171.106.243', 25, 'BGC Token'),
(170, 'admin', 1765361758, '31.171.106.243', 25, 'Black gold'),
(171, 'admin', 1765361901, '31.171.106.243', 25, 'Post 1'),
(172, 'admin', 1765362115, '31.171.106.243', 25, 'Post 1'),
(173, 'admin', 1765362461, '31.171.106.243', 25, 'Company shares'),
(174, 'admin', 1765362594, '31.171.106.243', 25, 'Energy Resources &amp; Staking'),
(175, 'admin', 1765363031, '31.171.106.243', 25, 'Trading &amp; Marketplaces'),
(176, 'admin', 1765363174, '31.171.106.243', 25, 'Bank &amp; Cards'),
(177, 'admin', 1765363494, '31.171.106.243', 25, 'Oil prices'),
(178, 'admin', 1765363613, '31.171.106.243', 25, 'Ecology &amp; Projects'),
(179, 'admin', 1765363709, '31.171.106.243', 25, 'Education &amp; Training'),
(180, 'admin', 1765363891, '31.171.106.243', 25, 'Environmental Initiatives'),
(181, 'admin', 1765363926, '31.171.106.243', 25, 'Environmental Initiatives'),
(182, 'admin', 1765364106, '31.171.106.243', 25, 'Trading Platforms'),
(183, 'admin', 1765364226, '31.171.106.243', 25, 'Fintech Solutions'),
(184, 'admin', 1765364646, '31.171.106.243', 25, 'Blockchain Technology'),
(185, 'admin', 1765364665, '31.171.106.243', 25, 'Blockchain Technology'),
(186, 'admin', 1765365031, '31.171.106.243', 25, 'BLACK GOLD - GLOBAL STARTUP PITCHDECK'),
(187, 'admin', 1765365088, '31.171.106.243', 25, 'The BlackGold Card: A New Way to Use Crypto in Everyday Life'),
(188, 'admin', 1765365238, '31.171.106.243', 25, 'BlackGold Coin vs. Traditional Cryptocurrencies: What Makes It Different?'),
(189, 'admin', 1765365261, '31.171.106.243', 25, 'Energy-Backed Cryptocurrencies: Why They Are More Resilient'),
(190, 'admin', 1765365450, '31.171.106.243', 25, 'Tokenization of Real Assets: How BlackGold Coin Leads the Movement'),
(191, 'admin', 1765365606, '31.171.106.243', 25, 'Tokenization of Real Assets: How BlackGold Coin Leads the Movement'),
(192, 'admin', 1765365628, '31.171.106.243', 25, 'The Vision Behind BGORIUM: A New Standard in Fintech'),
(193, 'admin', 1765365651, '31.171.106.243', 25, 'How BlackGold Coin Generates Passive Income Through Staking'),
(194, 'admin', 1765365697, '31.171.106.243', 25, 'Why Asset-Backed Cryptocurrencies Are the Future of Digital Finance'),
(195, 'admin', 1765365706, '31.171.106.243', 25, 'Why Asset-Backed Cryptocurrencies Are the Future of Digital Finance'),
(196, 'admin', 1765365726, '31.171.106.243', 25, 'What Is BlackGold Coin? The Complete Beginner’s Guide'),
(197, 'admin', 1765365769, '31.171.106.243', 25, 'Black gold'),
(198, 'admin', 1765365795, '31.171.106.243', 25, 'Post 1'),
(199, 'admin', 1765366090, '31.171.106.243', 25, 'BGC Token'),
(200, 'admin', 1765366120, '31.171.106.243', 25, 'Post 1'),
(201, 'admin', 1765366150, '31.171.106.243', 25, 'Company shares'),
(202, 'admin', 1765366204, '31.171.106.243', 25, 'BLACK GOLD - GLOBAL STARTUP PITCHDECK'),
(203, 'admin', 1765366225, '31.171.106.243', 25, 'Blockchain Technology'),
(204, 'admin', 1765366245, '31.171.106.243', 25, 'Fintech Solutions'),
(205, 'admin', 1765366267, '31.171.106.243', 25, 'Trading Platforms'),
(206, 'admin', 1765366288, '31.171.106.243', 25, 'Environmental Initiatives'),
(207, 'admin', 1765366307, '31.171.106.243', 25, 'Environmental Initiatives'),
(208, 'admin', 1765366327, '31.171.106.243', 25, 'Education &amp; Training'),
(209, 'admin', 1765366361, '31.171.106.243', 25, 'Ecology &amp; Projects'),
(210, 'admin', 1765366372, '31.171.106.243', 25, 'Ecology &amp; Projects'),
(211, 'admin', 1765366419, '31.171.106.243', 25, 'Oil prices'),
(212, 'admin', 1765366444, '31.171.106.243', 25, 'Bank &amp; Cards'),
(213, 'admin', 1765366466, '31.171.106.243', 25, 'Trading &amp; Marketplaces'),
(214, 'admin', 1765366494, '31.171.106.243', 25, 'Energy Resources &amp; Staking'),
(215, 'admin', 1765366505, '31.171.106.243', 25, 'Energy Resources &amp; Staking'),
(216, 'admin', 1765366536, '31.171.106.243', 25, 'Company shares'),
(217, 'admin', 1765368481, '31.171.106.243', 86, 'https://bgorium.com/admin.php?mod=editnews&amp;action=editnews&amp;id=17'),
(218, 'admin', 1765548923, '31.171.106.206', 82, ''),
(219, 'admin', 1765556498, '31.171.106.206', 86, 'https://bgorium.com/admin.php');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_admin_sections`
--

CREATE TABLE `dle_admin_sections` (
  `id` mediumint NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `descr` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `icon` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_groups` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_admin_sections`
--

INSERT INTO `dle_admin_sections` (`id`, `name`, `title`, `descr`, `icon`, `allow_groups`) VALUES
(1, 'multilanguage', 'Multi-Language', 'Модуль для создания мультиязычного сайта', 'engine/skins/images/multilanguage.png', '1');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_banned`
--

CREATE TABLE `dle_banned` (
  `id` smallint NOT NULL,
  `users_id` int NOT NULL DEFAULT '0',
  `descr` text COLLATE utf8mb4_general_ci NOT NULL,
  `date` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `days` smallint NOT NULL DEFAULT '0',
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `banned_from` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_banners`
--

CREATE TABLE `dle_banners` (
  `id` smallint NOT NULL,
  `banner_tag` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `descr` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `code` text COLLATE utf8mb4_general_ci NOT NULL,
  `approve` tinyint(1) NOT NULL DEFAULT '0',
  `short_place` tinyint(1) NOT NULL DEFAULT '0',
  `bstick` tinyint(1) NOT NULL DEFAULT '0',
  `main` tinyint(1) NOT NULL DEFAULT '0',
  `category` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `grouplevel` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'all',
  `start` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `end` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `fpage` tinyint(1) NOT NULL DEFAULT '0',
  `innews` tinyint(1) NOT NULL DEFAULT '0',
  `devicelevel` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_views` tinyint(1) NOT NULL DEFAULT '0',
  `max_views` int NOT NULL DEFAULT '0',
  `allow_counts` tinyint(1) NOT NULL DEFAULT '0',
  `max_counts` int NOT NULL DEFAULT '0',
  `views` int NOT NULL DEFAULT '0',
  `clicks` int NOT NULL DEFAULT '0',
  `rubric` mediumint NOT NULL DEFAULT '0',
  `comments_place` tinyint(1) NOT NULL DEFAULT '0',
  `allowed_country` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `not_allowed_country` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_banners`
--

INSERT INTO `dle_banners` (`id`, `banner_tag`, `descr`, `code`, `approve`, `short_place`, `bstick`, `main`, `category`, `grouplevel`, `start`, `end`, `fpage`, `innews`, `devicelevel`, `allow_views`, `max_views`, `allow_counts`, `max_counts`, `views`, `clicks`, `rubric`, `comments_place`, `allowed_country`, `not_allowed_country`) VALUES
(1, 'header', 'Верхний баннер', '<div style=\"text-align:center;\"><a href=\"https://dle-news.ru/\" target=\"_blank\"><img src=\"https://bgorium.com/templates/Default/images/_banner_.gif\" style=\"border: none;\" alt=\"\" /></a></div>', 1, 0, 0, 0, '0', 'all', '', '', 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, '', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_banners_logs`
--

CREATE TABLE `dle_banners_logs` (
  `id` int UNSIGNED NOT NULL,
  `bid` int NOT NULL DEFAULT '0',
  `click` tinyint(1) NOT NULL DEFAULT '0',
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_banners_rubrics`
--

CREATE TABLE `dle_banners_rubrics` (
  `id` mediumint NOT NULL,
  `parentid` mediumint NOT NULL DEFAULT '0',
  `title` varchar(70) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_category`
--

CREATE TABLE `dle_category` (
  `id` mediumint NOT NULL,
  `parentid` mediumint NOT NULL DEFAULT '0',
  `posi` mediumint NOT NULL DEFAULT '1',
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `alt_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `icon` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `skin` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `descr` varchar(300) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `keywords` text COLLATE utf8mb4_general_ci NOT NULL,
  `news_sort` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `news_msort` varchar(4) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `news_number` smallint NOT NULL DEFAULT '0',
  `short_tpl` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `full_tpl` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `metatitle` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `show_sub` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rss` tinyint(1) NOT NULL DEFAULT '1',
  `fulldescr` text COLLATE utf8mb4_general_ci NOT NULL,
  `disable_search` tinyint(1) NOT NULL DEFAULT '0',
  `disable_main` tinyint(1) NOT NULL DEFAULT '0',
  `disable_rating` tinyint(1) NOT NULL DEFAULT '0',
  `disable_comments` tinyint(1) NOT NULL DEFAULT '0',
  `enable_dzen` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `rating_type` tinyint(1) NOT NULL DEFAULT '-1',
  `schema_org` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1',
  `disable_index` tinyint(1) NOT NULL DEFAULT '0',
  `name_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `alt_name_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `metatitle_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `descr_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `keywords_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `fulldescr_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `name_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `alt_name_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `metatitle_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `descr_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `keywords_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `fulldescr_ru` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_category`
--

INSERT INTO `dle_category` (`id`, `parentid`, `posi`, `name`, `alt_name`, `icon`, `skin`, `descr`, `keywords`, `news_sort`, `news_msort`, `news_number`, `short_tpl`, `full_tpl`, `metatitle`, `show_sub`, `allow_rss`, `fulldescr`, `disable_search`, `disable_main`, `disable_rating`, `disable_comments`, `enable_dzen`, `active`, `rating_type`, `schema_org`, `disable_index`, `name_az`, `alt_name_az`, `metatitle_az`, `descr_az`, `keywords_az`, `fulldescr_az`, `name_ru`, `alt_name_ru`, `metatitle_ru`, `descr_ru`, `keywords_ru`, `fulldescr_ru`) VALUES
(1, 0, 1, 'FAQ', 'faq', '', '', '', '', '', '', 0, '', '', '', 0, 1, '', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(2, 0, 1, 'Slider', 'slider', '', '', '', '', '', '', 0, '', '', '', 0, 1, '', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(3, 0, 1, 'About', 'about', '', '', '', '', '', '', 0, '', '', '', 0, 1, '', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(4, 0, 1, 'Vision and mission', 'vision-and-mission', '', '', '', '', '', '', 0, '', '', '', 0, 1, 'Lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(5, 0, 1, 'Black Gold', 'black-gold', '', '', '', '', '', '', 0, '', '', '', 0, 1, 'Lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(6, 0, 1, 'Green Commitment', 'green-commitment', '', '', '', '', '', '', 0, '', '', '', 0, 1, 'Lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(7, 0, 1, 'BGC Token Giveaway', 'bgc-token-giveaway', '', '', '', '', '', '', 0, '', '', '', 0, 1, 'Lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(8, 0, 1, 'Bgorium Cards', 'bgorium-cards', '', '', '', '', '', '', 0, '', '', '', 0, 1, '', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(9, 0, 1, 'Service', 'service', '', '', '', '', '', '', 0, '', '', '', 0, 1, 'Lorem lorem lorem lorem Lorem lorem lorem loremLorem lorem lorem loremLorem lorem lorem loremLorem lorem lorem loremLorem lorem lorem lorem', 0, 0, 0, 1, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(10, 0, 1, 'Tətbiq sahələri', 'tetbiq-saheleri', '', '', '', '', '', '', 0, '', '', '', 0, 1, '', 0, 0, 0, 1, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', ''),
(11, 0, 1, 'Blog', 'blog', '', '', '', '', '', '', 0, '', '', '', 0, 1, '', 0, 0, 0, 0, 1, 1, -1, '1', 0, '', '', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_comments`
--

CREATE TABLE `dle_comments` (
  `id` int UNSIGNED NOT NULL,
  `post_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `autor` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `email` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `text` text COLLATE utf8mb4_general_ci NOT NULL,
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `is_register` tinyint(1) NOT NULL DEFAULT '0',
  `approve` tinyint(1) NOT NULL DEFAULT '1',
  `rating` int NOT NULL DEFAULT '0',
  `vote_num` int NOT NULL DEFAULT '0',
  `parent` int NOT NULL DEFAULT '0',
  `lang` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_comments_files`
--

CREATE TABLE `dle_comments_files` (
  `id` int NOT NULL,
  `c_id` int NOT NULL DEFAULT '0',
  `author` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `driver` mediumint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_comment_rating_log`
--

CREATE TABLE `dle_comment_rating_log` (
  `id` int UNSIGNED NOT NULL,
  `c_id` int NOT NULL DEFAULT '0',
  `member` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `rating` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_complaint`
--

CREATE TABLE `dle_complaint` (
  `id` int UNSIGNED NOT NULL,
  `p_id` int NOT NULL DEFAULT '0',
  `c_id` int NOT NULL DEFAULT '0',
  `n_id` int NOT NULL DEFAULT '0',
  `text` text COLLATE utf8mb4_general_ci NOT NULL,
  `from` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `to` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` int UNSIGNED NOT NULL DEFAULT '0',
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_conversations`
--

CREATE TABLE `dle_conversations` (
  `id` int UNSIGNED NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `created_at` int UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` int UNSIGNED NOT NULL DEFAULT '0',
  `sender_id` int NOT NULL,
  `recipient_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_conversations_messages`
--

CREATE TABLE `dle_conversations_messages` (
  `id` int UNSIGNED NOT NULL,
  `conversation_id` int UNSIGNED NOT NULL,
  `sender_id` int NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_conversation_reads`
--

CREATE TABLE `dle_conversation_reads` (
  `user_id` int UNSIGNED NOT NULL,
  `conversation_id` int UNSIGNED NOT NULL,
  `last_read_at` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_conversation_users`
--

CREATE TABLE `dle_conversation_users` (
  `user_id` int UNSIGNED NOT NULL,
  `conversation_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_downloads_log`
--

CREATE TABLE `dle_downloads_log` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `file_id` int NOT NULL DEFAULT '0',
  `date` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_email`
--

CREATE TABLE `dle_email` (
  `id` tinyint UNSIGNED NOT NULL,
  `name` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `template` text COLLATE utf8mb4_general_ci NOT NULL,
  `use_html` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_email`
--

INSERT INTO `dle_email` (`id`, `name`, `template`, `use_html`) VALUES
(1, 'reg_mail', '{%username%},\r\n\r\nЭто письмо отправлено с сайта https://bgorium.81.whm.az/\r\n\r\nВы получили это письмо, так как этот e-mail адрес был использован при регистрации на сайте. Если Вы не регистрировались на этом сайте, просто проигнорируйте это письмо и удалите его. Вы больше не получите такого письма.\r\n\r\n------------------------------------------------\r\nВаш логин и пароль на сайте:\r\n------------------------------------------------\r\n\r\nЛогин: {%username%}\r\nПароль: {%password%}\r\n\r\n------------------------------------------------\r\nИнструкция по активации\r\n------------------------------------------------\r\n\r\nБлагодарим Вас за регистрацию.\r\nМы требуем от Вас подтверждения Вашей регистрации, для проверки того, что введённый Вами e-mail адрес - реальный. Это требуется для защиты от нежелательных злоупотреблений и спама.\r\n\r\nДля активации Вашего аккаунта, зайдите по следующей ссылке:\r\n\r\n{%validationlink%}\r\n\r\nЕсли и при этих действиях ничего не получилось, возможно Ваш аккаунт удалён. В этом случае, обратитесь к Администратору, для разрешения проблемы.\r\n\r\nС уважением,\r\n\r\nАдминистрация https://bgorium.81.whm.az/.', 0),
(2, 'feed_mail', '{%username_to%},\r\n\r\nДанное письмо вам отправил {%username_from%} с сайта https://bgorium.81.whm.az/\r\n\r\n------------------------------------------------\r\nТекст сообщения\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nIP адрес отправителя: {%ip%}\r\nГруппа: {%group%}\r\n\r\n------------------------------------------------\r\nПомните, что администрация сайта не несет ответственности за содержание данного письма\r\n\r\nС уважением,\r\n\r\nАдминистрация https://bgorium.81.whm.az/', 0),
(3, 'lost_mail', 'Уважаемый {%username%},\r\n\r\nВы сделали запрос на получение забытого пароля на сайте https://bgorium.81.whm.az/ Однако в целях безопасности все пароли хранятся в зашифрованном виде, поэтому мы не можем сообщить вам ваш старый пароль, поэтому если вы хотите сгенерировать новый пароль, зайдите по следующей ссылке: \r\n\r\n{%lostlink%}\r\n\r\nЕсли вы не делали запроса для получения пароля, то просто удалите данное письмо, ваш пароль храниться в надежном месте, и недоступен посторонним лицам.\r\n\r\nIP адрес отправителя: {%ip%}\r\n\r\nС уважением,\r\n\r\nАдминистрация https://bgorium.81.whm.az/', 0),
(4, 'new_news', 'Уважаемый администратор,\r\n\r\nуведомляем вас о том, что на сайт  https://bgorium.81.whm.az/ была добавлена новость, которая в данный момент ожидает модерации.\r\n\r\n------------------------------------------------\r\nКраткая информация о новости\r\n------------------------------------------------\r\n\r\nАвтор: {%username%}\r\nЗаголовок новости: {%title%}\r\nКатегория: {%category%}\r\nДата добавления: {%date%}\r\n\r\nС уважением,\r\n\r\nАдминистрация https://bgorium.81.whm.az/', 0),
(5, 'comments', 'Уважаемый {%username_to%},\r\n\r\nуведомляем вас о том, что на сайт  https://bgorium.81.whm.az/ был добавлен комментарий к новости, на которую вы были подписаны.\r\n\r\n------------------------------------------------\r\nКраткая информация о комментарии\r\n------------------------------------------------\r\n\r\nАвтор: {%username%}\r\nДата добавления: {%date%}\r\nСсылка на новость: {%link%}\r\n\r\n------------------------------------------------\r\nТекст комментария\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\n------------------------------------------------\r\n\r\nЕсли вы не хотите больше получать уведомлений о новых комментариях к данной новости, то проследуйте по данной ссылке: {%unsubscribe%}\r\n\r\nС уважением,\r\n\r\nАдминистрация https://bgorium.81.whm.az/', 0),
(6, 'pm', 'Уважаемый {%username%},\r\n\r\nуведомляем вас о том, что на сайте  https://bgorium.81.whm.az/ вам было отправлено персональное сообщение.\r\n\r\n------------------------------------------------\r\nКраткая информация о сообщении\r\n------------------------------------------------\r\n\r\nОтправитель: {%fromusername%}\r\nДата  получения: {%date%}\r\nЗаголовок: {%title%}\r\n\r\n------------------------------------------------\r\nТекст сообщения\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nС уважением,\r\n\r\nАдминистрация https://bgorium.81.whm.az/', 0),
(7, 'wait_mail', 'Уважаемый {%username%},\r\n\r\nВы сделали запрос на обьединение  вашего аккаунта на сайте https://bgorium.81.whm.az/ с аккаунтом в социальной сети {%network%}.  Однако в целях безопасности вам необходимо подтвердить данное действие по следующей ссылке: \r\n\r\n------------------------------------------------\r\n{%link%}\r\n------------------------------------------------\r\n\r\nЕсли вы не делали данного запроса, то просто удалите это письмо, данные вашего аккаунта хранятся в надежном месте, и недоступны посторонним лицам.\r\n\r\nIP адрес отправителя: {%ip%}\r\n\r\nС уважением,\r\n\r\nАдминистрация https://bgorium.81.whm.az/', 0),
(8, 'newsletter', '<html>\r\n<head>\r\n<title>{%title%}</title>\r\n<meta content=\"text/html; charset={%charset%}\" http-equiv=Content-Type>\r\n<style type=\"text/css\">\r\nhtml,body{\r\n    font-family: Verdana;\r\n    word-spacing: 0.1em;\r\n    letter-spacing: 0;\r\n    line-height: 1.5em;\r\n    font-size: 11px;\r\n}\r\n\r\np {\r\n	margin:0px;\r\n	padding: 0px;\r\n}\r\n\r\na:active,\r\na:visited,\r\na:link {\r\n	color: #4b719e;\r\n	text-decoration:none;\r\n}\r\n\r\na:hover {\r\n	color: #4b719e;\r\n	text-decoration: underline;\r\n}\r\n</style>\r\n</head>\r\n<body>\r\n{%content%}\r\n</body>\r\n</html>', 0),
(9, 'twofactor', '{%username%},\r\n\r\nЭто письмо отправлено с сайта https://bgorium.81.whm.az/\r\n\r\nВы получили это письмо, так как для вашего аккаунта включена двухфакторная авторизация. Для авторизации на сайте вам необходимо ввести полученный вами пин-код.\r\n\r\n------------------------------------------------\r\nПин-код:\r\n------------------------------------------------\r\n\r\n{%pin%}\r\n\r\n------------------------------------------------\r\nЕсли Вы не авторизовывались на нашем сайте, то ваш пароль известен посторонним лицам. Вам нужно незамедлительно зайти на сайт под своим логином и паролем, и в своем профиле изменить свой пароль.\r\n\r\nIP пользователя который ввел пароль: {%ip%}\r\n\r\nС уважением,\r\n\r\nАдминистрация https://bgorium.81.whm.az/', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_files`
--

CREATE TABLE `dle_files` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `name` varchar(250) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `onserver` varchar(250) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `author` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `dcount` int NOT NULL DEFAULT '0',
  `size` bigint NOT NULL DEFAULT '0',
  `checksum` char(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `driver` mediumint NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_flood`
--

CREATE TABLE `dle_flood` (
  `f_id` int UNSIGNED NOT NULL,
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `flag` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_ignore_list`
--

CREATE TABLE `dle_ignore_list` (
  `id` int UNSIGNED NOT NULL,
  `user` int NOT NULL DEFAULT '0',
  `user_from` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_images`
--

CREATE TABLE `dle_images` (
  `id` int UNSIGNED NOT NULL,
  `images` text COLLATE utf8mb4_general_ci NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `author` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_images`
--

INSERT INTO `dle_images` (`id`, `images`, `news_id`, `author`, `date`) VALUES
(1, '2025-11/drop.webp|1|0|240x336|19.54 Kb|0', 4, 'admin', '1763471434'),
(2, '2025-11/blockchain.webp|1|1|980x980|123.71 Kb|0', 7, 'admin', '1763476744'),
(3, '2025-11/fintech.webp|1|1|980x980|196.38 Kb|0', 8, 'admin', '1763476785'),
(4, '2025-11/tradingplat.webp|1|1|640x640|17.24 Kb|0', 9, 'admin', '1763476834'),
(5, '2025-11/da22698d7ad51d10c6f98bcbcbc810e1-removebg-preview.png|1|1|500x500|104.54 Kb|0', 10, 'admin', '1763476871'),
(6, '2025-11/p-main__list-icon.png|0|0|160x161|2.95 Kb|0', 11, 'admin', '1763476905'),
(7, '2025-11/ecology.webp|1|1|1024x1024|20.32 Kb|0', 12, 'admin', '1763478894'),
(8, '2025-11/oil.webp|1|0|450x450|74.13 Kb|0', 13, 'admin', '1763479085'),
(9, '2025-11/bank.png|1|1|512x512|68.66 Kb|0', 14, 'admin', '1763479120'),
(10, '2025-11/statik.webp|1|0|225x225|3.88 Kb|0', 15, 'admin', '1763479147'),
(11, '2025-11/renewable-energy-3d-icon-png-download-5386087.webp|1|0|450x450|80.73 Kb|0', 16, 'admin', '1763479198'),
(12, '2025-11/share-3d-icon-isolated-on-transparent-background-gold-texture-3d-rendering-free-png.webp|1|1|980x980|210.93 Kb|0', 17, 'admin', '1763479221'),
(13, '2025-12/dalle-2025-12-02-16_07_21-a-high-detail-3d-golden-coin-with-the-letters-bgc-engraved-glowing-energy-rings-behind-it-black-and-gold-color-palette-futuristic-blockchain-backgr.webp|1|1|1024x1024|471.87 Kb|0', 22, 'admin', '1764677271'),
(14, '2025-12/dalle-2025-12-02-16_08_29-a-balanced-scale-with-a-crypto-coin-on-one-side-and-barrels-of-oil-plus-renewable-energy-icons-on-the-other-symbolizing-asset-backed-stability_-elega.webp|1|1|1024x1024|338.48 Kb|0', 23, 'admin', '1764677316'),
(15, '2025-12/dalle-2025-12-02-16_09_16-a-glowing-digital-vault-with-blackgold-coins-entering-and-multiplying-representing-passive-income_-a-financial-growth-chart-in-the-background-black.webp|1|1|1024x1024|421.63 Kb|0', 24, 'admin', '1764677362'),
(16, '2025-12/dalle-2025-12-02-16_09_51-a-futuristic-smart-city-skyline-connected-by-glowing-blockchain-lines-with-a-blackgold-coin-symbol-floating-above-the-city_-clean-fintech-aesthetic.webp|1|1|1024x1024|473.28 Kb|0', 25, 'admin', '1764677400'),
(17, '2025-12/dalle-2025-12-02-16_10_46-oil-barrels-solar-panels-and-gold-bars-transforming-into-glowing-blockchain-tokens_-particle-effects-and-dynamic-motion-black-background-with-gold.webp|1|1|1024x1024|496.05 Kb|0', 26, 'admin', '1764677450'),
(18, '2025-12/dalle-2025-12-02-16_11_21-a-glowing-blackgold-coin-connected-to-oil-rigs-and-renewable-energy-turbines-via-energy-beams_-dramatic-lighting-black-and-amber-color-palette-ultra.webp|1|1|1024x1024|396.74 Kb|0', 27, 'admin', '1764677485'),
(19, '2025-12/dalle-2025-12-02-16_14_02-a-comparison-visual-showing-a-shaky-fragmented-crypto-coin-on-the-left-side-and-a-solid-glowing-blackgold-coin-on-the-right-side_-the-theme-is-stabi.webp|1|1|1024x1024|495.73 Kb|0', 28, 'admin', '1764677646'),
(20, '2025-12/dalle-2025-12-02-16_14_54-a-luxurious-black-and-gold-crypto-debit-card-with-the-bgorium-emblem-shining-under-a-focused-spotlight_-minimalistic-dark-background-premium-fintech.webp|1|1|1024x1024|391.26 Kb|0', 29, 'admin', '1764677700');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_links`
--

CREATE TABLE `dle_links` (
  `id` int UNSIGNED NOT NULL,
  `word` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `link` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `only_one` tinyint(1) NOT NULL DEFAULT '0',
  `replacearea` tinyint(1) NOT NULL DEFAULT '1',
  `rcount` tinyint NOT NULL DEFAULT '0',
  `targetblank` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_login_log`
--

CREATE TABLE `dle_login_log` (
  `id` int UNSIGNED NOT NULL,
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `count` smallint NOT NULL DEFAULT '0',
  `date` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_logs`
--

CREATE TABLE `dle_logs` (
  `id` int UNSIGNED NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `member` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `rating` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_lostdb`
--

CREATE TABLE `dle_lostdb` (
  `id` mediumint NOT NULL,
  `lostname` mediumint NOT NULL DEFAULT '0',
  `lostid` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_mail_log`
--

CREATE TABLE `dle_mail_log` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `mail` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `hash` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_metatags`
--

CREATE TABLE `dle_metatags` (
  `id` int UNSIGNED NOT NULL,
  `url` varchar(250) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `title` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(300) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `keywords` text COLLATE utf8mb4_general_ci NOT NULL,
  `page_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `page_description` text COLLATE utf8mb4_general_ci NOT NULL,
  `robots` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_multilanguage`
--

CREATE TABLE `dle_multilanguage` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `dir` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `status` int NOT NULL,
  `code` varchar(2) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `icon` varchar(250) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_multilanguage`
--

INSERT INTO `dle_multilanguage` (`id`, `name`, `dir`, `status`, `code`, `icon`) VALUES
(1, 'Azerbaijan', 'Ukrainian', 1, 'az', '/engine/skins/flags/az.png'),
(2, 'Russian', 'Russian', 1, 'ru', '/engine/skins/flags/ru.png');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_notice`
--

CREATE TABLE `dle_notice` (
  `id` mediumint NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `notice` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_plugins`
--

CREATE TABLE `dle_plugins` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `icon` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `version` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `dleversion` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `versioncompare` char(2) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `mysqlinstall` text COLLATE utf8mb4_general_ci NOT NULL,
  `mysqlupgrade` text COLLATE utf8mb4_general_ci NOT NULL,
  `mysqlenable` text COLLATE utf8mb4_general_ci NOT NULL,
  `mysqldisable` text COLLATE utf8mb4_general_ci NOT NULL,
  `mysqldelete` text COLLATE utf8mb4_general_ci NOT NULL,
  `filedelete` tinyint(1) NOT NULL DEFAULT '0',
  `filelist` text COLLATE utf8mb4_general_ci NOT NULL,
  `upgradeurl` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `needplugin` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `phpinstall` text COLLATE utf8mb4_general_ci NOT NULL,
  `phpupgrade` text COLLATE utf8mb4_general_ci NOT NULL,
  `phpenable` text COLLATE utf8mb4_general_ci NOT NULL,
  `phpdisable` text COLLATE utf8mb4_general_ci NOT NULL,
  `phpdelete` text COLLATE utf8mb4_general_ci NOT NULL,
  `notice` text COLLATE utf8mb4_general_ci NOT NULL,
  `mnotice` tinyint(1) NOT NULL DEFAULT '0',
  `posi` mediumint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_plugins`
--

INSERT INTO `dle_plugins` (`id`, `name`, `description`, `icon`, `version`, `dleversion`, `versioncompare`, `active`, `mysqlinstall`, `mysqlupgrade`, `mysqlenable`, `mysqldisable`, `mysqldelete`, `filedelete`, `filelist`, `upgradeurl`, `needplugin`, `phpinstall`, `phpupgrade`, `phpenable`, `phpdisable`, `phpdelete`, `notice`, `mnotice`, `posi`) VALUES
(1, 'Multi-Language', 'Модуль для создание мультиязычного сайта на CMS DataLife Engine', 'engine/skins/images/multilanguage.png', '4.11', '13.0', '>=', 1, 'CREATE TABLE IF NOT EXISTS `{prefix}_multilanguage` (\n	`id` int(11) NOT NULL AUTO_INCREMENT,\n	`name` varchar(50) NOT NULL DEFAULT \'\',\n	`dir` varchar(50) NOT NULL,\n	`status` int(1) NOT NULL,\n	`code` varchar(2) NOT NULL DEFAULT \'\',\n	`icon` varchar(250) NOT NULL DEFAULT \'\',\n	PRIMARY KEY (`id`)\n) DEFAULT CHARACTER SET {charset} COLLATE {charset}_general_ci;\nALTER TABLE `{prefix}_comments` ADD `lang` varchar(10) NOT NULL default \'\';', '', 'INSERT INTO `{prefix}_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`) VALUES (\'multilanguage\', \'Multi-Language\', \'Модуль для создания мультиязычного сайта\', \'engine/skins/images/multilanguage.png\', \'1\');', 'DELETE FROM `{prefix}_admin_sections` WHERE name = \'multilanguage\';', 'DROP TABLE IF EXISTS `{prefix}_multilanguage`;\nALTER TABLE `{prefix}_comments` DROP COLUMN `lang`;\nDELETE FROM `{prefix}_admin_sections` WHERE name = \'multilanguage\';', 1, 'engine/inc/multilanguage.php,engine/modules/multilanguage/.htaccess,engine/modules/multilanguage/admin.8.1.php,engine/modules/multilanguage/admin.8.2.php,engine/modules/multilanguage/admin.php,engine/modules/multilanguage/core.8.1.php,engine/modules/multilanguage/core.8.2.php,engine/modules/multilanguage/core.php,engine/modules/multilanguage/google.8.1.php,engine/modules/multilanguage/google.8.2.php,engine/modules/multilanguage/google.php,engine/modules/multilanguage/tags.8.1.php,engine/modules/multilanguage/tags.8.2.php,engine/modules/multilanguage/tags.php,engine/skins/flags/ad.png,engine/skins/flags/ae.png,engine/skins/flags/af.png,engine/skins/flags/ag.png,engine/skins/flags/ai.png,engine/skins/flags/al.png,engine/skins/flags/am.png,engine/skins/flags/an.png,engine/skins/flags/ao.png,engine/skins/flags/aq.png,engine/skins/flags/ar.png,engine/skins/flags/as.png,engine/skins/flags/at.png,engine/skins/flags/au.png,engine/skins/flags/aw.png,engine/skins/flags/az.png,engine/skins/flags/ba.png,engine/skins/flags/bb.png,engine/skins/flags/bd.png,engine/skins/flags/be.png,engine/skins/flags/bf.png,engine/skins/flags/bg.png,engine/skins/flags/bh.png,engine/skins/flags/bi.png,engine/skins/flags/bj.png,engine/skins/flags/bm.png,engine/skins/flags/bn.png,engine/skins/flags/bo.png,engine/skins/flags/br.png,engine/skins/flags/bs.png,engine/skins/flags/bt.png,engine/skins/flags/bw.png,engine/skins/flags/by.png,engine/skins/flags/bz.png,engine/skins/flags/ca.png,engine/skins/flags/cd.png,engine/skins/flags/cf.png,engine/skins/flags/cg.png,engine/skins/flags/ch.png,engine/skins/flags/ci.png,engine/skins/flags/ck.png,engine/skins/flags/cl.png,engine/skins/flags/cm.png,engine/skins/flags/cn.png,engine/skins/flags/co.png,engine/skins/flags/cr.png,engine/skins/flags/cu.png,engine/skins/flags/cv.png,engine/skins/flags/cy.png,engine/skins/flags/cz.png,engine/skins/flags/de.png,engine/skins/flags/dj.png,engine/skins/flags/dk.png,engine/skins/flags/dm.png,engine/skins/flags/do.png,engine/skins/flags/dz.png,engine/skins/flags/ec.png,engine/skins/flags/ee.png,engine/skins/flags/eg.png,engine/skins/flags/eh.png,engine/skins/flags/er.png,engine/skins/flags/es.png,engine/skins/flags/et.png,engine/skins/flags/fi.png,engine/skins/flags/fj.png,engine/skins/flags/fm.png,engine/skins/flags/fo.png,engine/skins/flags/fr.png,engine/skins/flags/ga.png,engine/skins/flags/gb.png,engine/skins/flags/gd.png,engine/skins/flags/ge.png,engine/skins/flags/gg.png,engine/skins/flags/gh.png,engine/skins/flags/gi.png,engine/skins/flags/gl.png,engine/skins/flags/gm.png,engine/skins/flags/gn.png,engine/skins/flags/gp.png,engine/skins/flags/gq.png,engine/skins/flags/gr.png,engine/skins/flags/gt.png,engine/skins/flags/gu.png,engine/skins/flags/gw.png,engine/skins/flags/gy.png,engine/skins/flags/hk.png,engine/skins/flags/hn.png,engine/skins/flags/hr.png,engine/skins/flags/ht.png,engine/skins/flags/hu.png,engine/skins/flags/id.png,engine/skins/flags/ie.png,engine/skins/flags/il.png,engine/skins/flags/im.png,engine/skins/flags/in.png,engine/skins/flags/iq.png,engine/skins/flags/ir.png,engine/skins/flags/is.png,engine/skins/flags/it.png,engine/skins/flags/je.png,engine/skins/flags/jm.png,engine/skins/flags/jo.png,engine/skins/flags/jp.png,engine/skins/flags/ke.png,engine/skins/flags/kg.png,engine/skins/flags/kh.png,engine/skins/flags/ki.png,engine/skins/flags/km.png,engine/skins/flags/kn.png,engine/skins/flags/kp.png,engine/skins/flags/kr.png,engine/skins/flags/kw.png,engine/skins/flags/ky.png,engine/skins/flags/kz.png,engine/skins/flags/la.png,engine/skins/flags/lb.png,engine/skins/flags/lc.png,engine/skins/flags/li.png,engine/skins/flags/lk.png,engine/skins/flags/lr.png,engine/skins/flags/ls.png,engine/skins/flags/lt.png,engine/skins/flags/lu.png,engine/skins/flags/lv.png,engine/skins/flags/ly.png,engine/skins/flags/ma.png,engine/skins/flags/mc.png,engine/skins/flags/md.png,engine/skins/flags/me.png,engine/skins/flags/mg.png,engine/skins/flags/mh.png,engine/skins/flags/mk.png,engine/skins/flags/ml.png,engine/skins/flags/mm.png,engine/skins/flags/mn.png,engine/skins/flags/mo.png,engine/skins/flags/mq.png,engine/skins/flags/mr.png,engine/skins/flags/ms.png,engine/skins/flags/mt.png,engine/skins/flags/mu.png,engine/skins/flags/mv.png,engine/skins/flags/mw.png,engine/skins/flags/mx.png,engine/skins/flags/my.png,engine/skins/flags/mz.png,engine/skins/flags/na.png,engine/skins/flags/nc.png,engine/skins/flags/ne.png,engine/skins/flags/ng.png,engine/skins/flags/ni.png,engine/skins/flags/nl.png,engine/skins/flags/no.png,engine/skins/flags/np.png,engine/skins/flags/nr.png,engine/skins/flags/nz.png,engine/skins/flags/om.png,engine/skins/flags/pa.png,engine/skins/flags/pe.png,engine/skins/flags/pf.png,engine/skins/flags/pg.png,engine/skins/flags/ph.png,engine/skins/flags/pk.png,engine/skins/flags/pl.png,engine/skins/flags/pr.png,engine/skins/flags/ps.png,engine/skins/flags/pt.png,engine/skins/flags/pw.png,engine/skins/flags/py.png,engine/skins/flags/qa.png,engine/skins/flags/re.png,engine/skins/flags/ro.png,engine/skins/flags/rs.png,engine/skins/flags/ru.png,engine/skins/flags/rw.png,engine/skins/flags/sa.png,engine/skins/flags/sb.png,engine/skins/flags/sc.png,engine/skins/flags/sd.png,engine/skins/flags/se.png,engine/skins/flags/sg.png,engine/skins/flags/si.png,engine/skins/flags/sk.png,engine/skins/flags/sl.png,engine/skins/flags/sm.png,engine/skins/flags/sn.png,engine/skins/flags/so.png,engine/skins/flags/sr.png,engine/skins/flags/st.png,engine/skins/flags/sv.png,engine/skins/flags/sy.png,engine/skins/flags/sz.png,engine/skins/flags/tc.png,engine/skins/flags/td.png,engine/skins/flags/tg.png,engine/skins/flags/th.png,engine/skins/flags/tj.png,engine/skins/flags/tl.png,engine/skins/flags/tm.png,engine/skins/flags/tn.png,engine/skins/flags/to.png,engine/skins/flags/tr.png,engine/skins/flags/tt.png,engine/skins/flags/tv.png,engine/skins/flags/tw.png,engine/skins/flags/tz.png,engine/skins/flags/ua.png,engine/skins/flags/ug.png,engine/skins/flags/us.png,engine/skins/flags/uy.png,engine/skins/flags/uz.png,engine/skins/flags/va.png,engine/skins/flags/vc.png,engine/skins/flags/ve.png,engine/skins/flags/vg.png,engine/skins/flags/vi.png,engine/skins/flags/vn.png,engine/skins/flags/vu.png,engine/skins/flags/ws.png,engine/skins/flags/ye.png,engine/skins/flags/za.png,engine/skins/flags/zm.png,engine/skins/flags/zw.png,engine/skins/images/multilanguage.png,engine/skins/images/multilanguage_activate.png,engine/skins/images/multilanguage_add.png,engine/skins/images/multilanguage_list.png,engine/skins/images/multilanguage_settings.png,language/English/multilanguage.lng,language/Russian/multilanguage.lng', '', '', '', '', '', '', '', '', 0, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_plugins_files`
--

CREATE TABLE `dle_plugins_files` (
  `id` int NOT NULL,
  `plugin_id` int NOT NULL DEFAULT '0',
  `file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `action` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `searchcode` text COLLATE utf8mb4_general_ci NOT NULL,
  `replacecode` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `searchcount` smallint NOT NULL DEFAULT '0',
  `replacecount` smallint NOT NULL DEFAULT '0',
  `filedisable` tinyint(1) NOT NULL DEFAULT '1',
  `filedleversion` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `fileversioncompare` char(2) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_plugins_files`
--

INSERT INTO `dle_plugins_files` (`id`, `plugin_id`, `file`, `action`, `searchcode`, `replacecode`, `active`, `searchcount`, `replacecount`, `filedisable`, `filedleversion`, `fileversioncompare`) VALUES
(1, 1, 'engine/engine.php', 'before', 'if ($do == \"cat\" AND $category AND !$subaction) {\n	$allow_sub_cats = true;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_engine();\n// Multi-Language', 1, 0, 0, 1, '17.3', '>='),
(2, 1, 'engine/engine.php', 'before', 'if ($do == \"cat\" and $category != \'\' and $subaction == \'\') {\n	$allow_sub_cats = true;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_engine();\n// Multi-Language', 1, 0, 0, 1, '17.2', '<='),
(3, 1, 'engine/engine.php', 'before', '$sql_select_ids = $db->super_query($sql_select_ids, true);', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_select_ids = multilang_select_ids($sql_select_ids);\n// Multi-Language', 1, 0, 0, 1, '16.0', '>='),
(4, 1, 'engine/init.php', 'before', '$db->query( \"SELECT category, COUNT(*) AS count FROM', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_newscount()); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(5, 1, 'engine/init.php', 'before', 'dle_session();', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) {if ($_SERVER[\'QUERY_STRING\'] == \"lang=\" . LANGUAGE_CODE) $_SERVER[\'QUERY_STRING\'] = \"\";}\n// Multi-Language', 1, 0, 0, 1, '', ''),
(6, 1, 'engine/rss.php', 'before', 'echo $rss_content;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $rss_content = multilang_rsscontent($rss_content);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(7, 1, 'engine/ajax/allvotes.php', 'before', 'echo', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $content = multilang_allvotes($content);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(8, 1, 'engine/ajax/calendar.php', 'before', '$db->query( $sql );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql = multilang_sqlcalendar($sql);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(9, 1, 'engine/ajax/calendar.php', 'before', 'echo $buffer;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $buffer = multilang_echocalendar($buffer);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(10, 1, 'engine/ajax/editnews.php', 'before', '$row = $db->super_query( \"SELECT p.id, p.autor,', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row = $db->super_query(multilang_ajaxeditnews_query()); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(11, 1, 'engine/ajax/editnews.php', 'before', '$temp_array = array();', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $xf_search_words = multilang_xfsearch($xf_search_words);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(12, 1, 'engine/ajax/editnews.php', 'before', 'echo $buffer;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_ajaxeditnewsecho();\n// Multi-Language', 1, 0, 0, 1, '', ''),
(13, 1, 'engine/ajax/find_tags.php', 'before', '$db->query(\"SELECT tag, COUNT(*) AS count FROM', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_find_tags()); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(14, 1, 'engine/ajax/poll.php', 'before', 'echo $buffer;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $buffer = multilang_echopoll($buffer);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(15, 1, 'engine/ajax/rebuild.php', 'before', 'foreach ( $xf_search_words as $value ) {', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $xf_search_words = multilang_xfsearch($xf_search_words);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(16, 1, 'engine/ajax/search.php', 'before', '$db->query(\"SELECT id, short_story, title,', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_fastsearch()); else\n// Multi-Language', 1, 0, 0, 1, '16.0', '<='),
(17, 1, 'engine/ajax/search.php', 'before', '$row[\'date\'] = strtotime( $row[\'date\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row = multilang_fastsearchrow($row);\n// Multi-Language', 1, 0, 0, 1, '16.0', '<='),
(18, 1, 'engine/ajax/search.php', 'before', 'echo $buffer', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $buffer = multilang_echofastsearch($buffer);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(19, 1, 'engine/ajax/search.php', 'before', '$sql_result = $db->query($sql);', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql = multilang_fastsearchsql($sql);\n// Multi-Language', 1, 0, 0, 1, '16.1', '>='),
(20, 1, 'engine/ajax/search.php', 'before', '$not_found =', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $link_query = multilang_echofastsearch($link_query);\n// Multi-Language', 1, 0, 0, 1, '16.1', '>='),
(21, 1, 'engine/ajax/sitemap.php', 'before', 'echo', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_sendsitemap();\n// Multi-Language', 1, 0, 0, 1, '17.0', '<='),
(22, 1, 'engine/ajax/upload.php', 'before', 'foreach ( $filecontents as $name => $value ) {', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $filecontents = multilang_xfupload($filecontents);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(23, 1, 'engine/classes/comments.class.php', 'before', '$row[\'date\'] = strtotime( $row[\'date\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row = multilang_commrow($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(24, 1, 'engine/classes/comments.class.php', 'before', '$this->customshow = true;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $this->query = multilang_customcommselect($this->query);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(25, 1, 'engine/classes/comments.class.php', 'before', '$rows = false;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $this->query = multilang_lastcommselect($this->query);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(26, 1, 'engine/classes/google.class.php', 'after', '$stop_list = \"\";', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $stop_list .= multilang_selectdefault();\n// Multi-Language', 1, 0, 0, 1, '', ''),
(27, 1, 'engine/classes/mail.class.php', 'after', 'function send($to, $subject, $message) {', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $message = multilang_templateload($message);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(28, 1, 'engine/classes/templates.class.php', 'after', '$this->template = $this->check_module($this->template);', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $this->template = multilang_templateload($this->template);\n// Multi-Language', 1, 0, 0, 1, '15.2', '<='),
(29, 1, 'engine/classes/templates.class.php', 'after', '$template = $this->check_module($template);', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $template = multilang_templateload($template);\n// Multi-Language', 1, 0, 0, 1, '15.2', '<='),
(30, 1, 'engine/classes/templates.class.php', 'before', 'if( defined( \'NEWS_ID\' ) AND !$this->is_custom) $content = ', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $content = multilang_templateload($content);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(31, 1, 'engine/classes/templates.class.php', 'before', '$this->_clear();', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $this->result[$tpl] = multilang_compiletemplate($this->result[$tpl], $tpl);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(32, 1, 'engine/modules/addcomments.php', 'after', '$added_comments_id = $db->insert_id();', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_addcomments($added_comments_id);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(33, 1, 'engine/modules/addnews.php', 'before', 'foreach ( $xf_search_words as $value )', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $xf_search_words = multilang_xfsearch($xf_search_words);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(34, 1, 'engine/modules/addnews.php', 'before', 'if( $approve ) {\n	clear_cache( array(\'news_\', \'related_\', \'tagscloud_\',', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_siteaddnewssave($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(35, 1, 'engine/modules/addnews.php', 'before', '$xfieldsaction = \"categoryfilter\";', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $onload_scripts[] = multilang_siteaddnewshtml($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(36, 1, 'engine/modules/addnews.php', 'after', '= $db->insert_id();', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $lang_added_id = $row[\'id\'];\n// Multi-Language', 1, 0, 0, 1, '', ''),
(37, 1, 'engine/modules/calendar.php', 'before', '$db->query( $sql );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql = multilang_sqlcalendar($sql);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(38, 1, 'engine/modules/calendar.php', 'before', '$db->query( \"SELECT DATE_FORMAT(date,', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_sqlcalendararchive()); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(39, 1, 'engine/modules/functions.php', 'before', 'if ( $data !== false ) {', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) return multilang_getvars($data, $file);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(40, 1, 'engine/modules/functions.php', 'before', 'if( $is_logged ) $end_file = $member_id[\'user_group\'];', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $prefix = multilang_cacheprefix($prefix);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(41, 1, 'engine/modules/functions.php', 'before', '$db->query ( \"SELECT news_id FROM', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_customtags($temp_array)); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(42, 1, 'engine/modules/functions.php', 'before', '$sql_result = $db->query( $sql_select );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_select = multilang_customselect($sql_select);\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_count = multilang_customcount($sql_count);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(43, 1, 'engine/modules/functions.php', 'before', 'return $fields;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $fields = multilang_xfieldsload($fields);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(44, 1, 'engine/modules/functions.php', 'before', '$result = $db->query( \"SELECT id FROM', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_deletetags($id);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(45, 1, 'engine/modules/functions.php', 'after', '$data[$xfielddataname] = trim($xfielddatavalue);\n}', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $data = multilang_xfdataload($data);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(46, 1, 'engine/modules/functions.php', 'before', 'function dle_session', '// Multi-Language\ninclude_once (ENGINE_DIR . \"/modules/multilanguage/core.php\");\n// Multi-Language', 1, 0, 0, 1, '', ''),
(47, 1, 'engine/modules/lastcomments.php', 'before', '$row_count = $db->super_query( $sql_count );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_count = multilang_lastcommselectcount($sql_count);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(48, 1, 'engine/modules/main.php', 'before', '$tpl->load_template ( \'main.tpl\' );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $metatags .= multilang_metatags();\n// Multi-Language', 1, 0, 0, 1, '', ''),
(49, 1, 'engine/modules/main.php', 'before', 'echo $tpl->result[\'main\'];', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_echomain();\n// Multi-Language', 1, 0, 0, 1, '', ''),
(50, 1, 'engine/modules/search.php', 'before', '$result_count = $db->super_query( \"SELECT COUNT(*) as count $sql_from $where\" );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $where = multilang_searchsql($where);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(51, 1, 'engine/modules/search.php', 'before', '$from_num = $search_start + 1;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_find = multilang_searchsql($sql_find);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(52, 1, 'engine/modules/search.php', 'before', '$row[\'grouplevel\'] = explode( \',\', $row[\'grouplevel\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row = multilang_staticrow($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(53, 1, 'engine/modules/show.custom.php', 'before', '$news_found = true;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row = multilang_customrow($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(54, 1, 'engine/modules/show.full.php', 'before', '$row = dle_cache ( $cprefix, $sql_news );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_news = multilang_fullselect($sql_news);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(55, 1, 'engine/modules/show.full.php', 'before', '$options = news_permission( $row[\'access\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row = multilang_fullrow($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(56, 1, 'engine/modules/show.full.php', 'before', 'header(\"HTTP/1.0 301 Moved Permanently\");', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $re_url = multilang_fullseocontrol($re_url);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(57, 1, 'engine/modules/show.full.php', 'before', '$comments_num = $row[\'comm_num\'];', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_fulllinks($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(58, 1, 'engine/modules/show.full.php', 'before', '$db->query( \"SELECT id, date, short_story, xfields, title, category, alt_name FROM \" . PREFIX . \"_post WHERE id IN', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_relatedselect1()); else\n// Multi-Language', 1, 0, 0, 1, '18.0', '<='),
(59, 1, 'engine/modules/show.full.php', 'after', '} else $where_category = \"\";', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_relatedselect2()); else\n// Multi-Language', 1, 0, 0, 1, '18.0', '<='),
(60, 1, 'engine/modules/show.full.php', 'before', '$related[\'date\'] = strtotime( $related[\'date\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $related = multilang_relatedrow($related);\n// Multi-Language', 1, 0, 0, 1, '18.0', '<='),
(61, 1, 'engine/modules/show.full.php', 'before', '$row_link = $db->super_query', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row_link = multilang_prevnextselect(); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(62, 1, 'engine/modules/show.related.php', 'before', '$db->query( \"SELECT id, date, short_story, xfields, title, category, alt_name FROM \" . PREFIX . \"_post WHERE id IN', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_relatedselectnew()); else\n// Multi-Language', 1, 0, 0, 1, '18.1', '>='),
(63, 1, 'engine/modules/show.related.php', 'before', '$related[\'date\'] = strtotime( $related[\'date\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $related = multilang_relatedrow($related);\n// Multi-Language', 1, 0, 0, 1, '18.1', '>='),
(64, 1, 'engine/modules/show.short.php', 'before', '$sql_result = $db->query( $sql_select );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_select = multilang_shortselect($sql_select);\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_count = multilang_shortcount($sql_count);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(65, 1, 'engine/modules/show.short.php', 'before', '$news_found = TRUE;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row = multilang_shortrow($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(66, 1, 'engine/modules/static.php', 'before', 'if(!isset($static_result[\'id\'])', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $static_result = $db->super_query(multilang_getstatic()); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(67, 1, 'engine/modules/static.php', 'before', 'if ($static_result[\'allow_count\'])', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $static_result = multilang_staticrow($static_result);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(68, 1, 'engine/modules/static.php', 'before', 'header(\"HTTP/1.0 301 Moved Permanently\");', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $re_url = multilang_staticseocontrol($re_url);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(69, 1, 'engine/modules/static.php', 'before', 'if( $config[\'start_site\'] == 3 AND', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_staticlinks($static_result);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(70, 1, 'engine/modules/tagscloud.php', 'before', '$db->query(\"SELECT', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_tagscloud()); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(71, 1, 'engine/modules/tagscloud.php', 'before', '$db->query($sql_select);', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $sql_select = multilang_alltags($sql_select);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(72, 1, 'engine/modules/topnews.php', 'before', '$db->query( \"SELECT p.id, p.date, p.short_story, p.xfields, p.title,', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query(multilang_topnewsselect()); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(73, 1, 'engine/modules/topnews.php', 'before', '$row[\'date\'] = strtotime( $row[\'date\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row = multilang_topnewsrow($row);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(74, 1, 'engine/inc/addnews.php', 'before', 'foreach ( $xf_search_words as $value )', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $xf_search_words = multilang_xfsearch($xf_search_words);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(75, 1, 'engine/inc/editnews.php', 'before', '$db->query( \"SELECT p.id, p.date, p.title, p.category,', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $db->query( multilang_editnewslistsql() ); else\n// Multi-Language', 1, 0, 0, 1, '', ''),
(76, 1, 'engine/inc/editnews.php', 'after', '$entries .= $erlaub;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $entries .= multilang_listentries();\n// Multi-Language', 1, 0, 0, 1, '', ''),
(77, 1, 'engine/inc/editnews.php', 'before', 'foreach ( $xf_search_words as $value )', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $xf_search_words = multilang_xfsearch($xf_search_words);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(78, 1, 'engine/inc/googlemap.php', 'after', '$stop_list = \"\";', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $stop_list .= multilang_selectdefault();\n// Multi-Language', 1, 0, 0, 1, '15.1', '<='),
(79, 1, 'engine/inc/googlemap.php', 'before', 'die(\"done\");', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_functions();\n// Multi-Language', 1, 0, 0, 1, '', ''),
(80, 1, 'engine/inc/tagscloud.php', 'before', '$news_per_page = 50;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\') AND isset($_GET[\'lg\'])) include (ENGINE_DIR . \'/modules/multilanguage/tags.php\');\n// Multi-Language', 1, 0, 0, 1, '', ''),
(81, 1, 'engine/inc/xfields.php', 'before', 'ksort($editedxfield);', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $editedxfield = multilang_xfsave($editedxfield);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(82, 1, 'engine/inc/xfields.php', 'before', '$fieldname = $value[0];', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) if(!$value = multilang_xfchange($value, $code)) continue;\n// Multi-Language', 1, 0, 0, 1, '', ''),
(83, 1, 'engine/inc/include/functions.inc.php', 'before', '$result = $db->query( \"SELECT id FROM', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_deletetags($id);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(84, 1, 'engine/inc/include/functions.inc.php', 'before', 'echofooter();', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_functions($type);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(85, 1, 'engine/inc/include/functions.inc.php', 'before', 'if( $is_loged_in ) echo $skin_footer;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) multilang_functions();\n// Multi-Language', 1, 0, 0, 1, '', ''),
(86, 1, 'engine/inc/include/functions.inc.php', 'before', 'return $fields;', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $fields = multilang_xfieldsload($fields);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(87, 1, 'engine/inc/include/functions.inc.php', 'before', 'function dle_session', '// Multi-Language\ninclude_once (ENGINE_DIR . \"/modules/multilanguage/core.php\");\n// Multi-Language', 1, 0, 0, 1, '', ''),
(88, 1, 'engine/modules/register.php', 'before', '$mail->send( $email, $lang[\'reg_subj\'], $row[\'template\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row[\'template\'] = multilang_registeremail($row[\'template\']);\n// Multi-Language', 1, 0, 0, 1, '', ''),
(89, 1, 'engine/modules/lostpassword.php', 'before', '$mail->send( $lostmail, $lang[\'lost_subj\'], $row[\'template\'] );', '// Multi-Language\nif(defined(\'LANGUAGE_MOD_ACTIVE\')) $row[\'template\'] = multilang_lostemail($row[\'template\']);\n// Multi-Language', 1, 0, 0, 1, '', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_plugins_logs`
--

CREATE TABLE `dle_plugins_logs` (
  `id` int NOT NULL,
  `plugin_id` int NOT NULL DEFAULT '0',
  `area` text COLLATE utf8mb4_general_ci NOT NULL,
  `error` text COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `action_id` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_poll`
--

CREATE TABLE `dle_poll` (
  `id` mediumint UNSIGNED NOT NULL,
  `news_id` int UNSIGNED NOT NULL DEFAULT '0',
  `title` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `frage` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `body` text COLLATE utf8mb4_general_ci NOT NULL,
  `votes` mediumint NOT NULL DEFAULT '0',
  `multiple` tinyint(1) NOT NULL DEFAULT '0',
  `answer` text COLLATE utf8mb4_general_ci NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `date_closed` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_poll_log`
--

CREATE TABLE `dle_poll_log` (
  `id` int UNSIGNED NOT NULL,
  `news_id` int UNSIGNED NOT NULL DEFAULT '0',
  `member` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_post`
--

CREATE TABLE `dle_post` (
  `id` int NOT NULL,
  `autor` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `short_story` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `full_story` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `xfields` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `descr` varchar(300) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `keywords` text COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(190) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `alt_name` varchar(190) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `comm_num` mediumint UNSIGNED NOT NULL DEFAULT '0',
  `allow_comm` tinyint(1) NOT NULL DEFAULT '1',
  `allow_main` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `approve` tinyint(1) NOT NULL DEFAULT '0',
  `fixed` tinyint(1) NOT NULL DEFAULT '0',
  `allow_br` tinyint(1) NOT NULL DEFAULT '1',
  `symbol` varchar(3) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `tags` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `metatitle` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `title_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `alt_name_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `short_story_az` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `full_story_az` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `metatitle_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `descr_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `keywords_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `tags_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `title_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `alt_name_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `short_story_ru` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `full_story_ru` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `metatitle_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `descr_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `keywords_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `tags_ru` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_post`
--

INSERT INTO `dle_post` (`id`, `autor`, `date`, `short_story`, `full_story`, `xfields`, `title`, `descr`, `keywords`, `category`, `alt_name`, `comm_num`, `allow_comm`, `allow_main`, `approve`, `fixed`, `allow_br`, `symbol`, `tags`, `metatitle`, `title_az`, `alt_name_az`, `short_story_az`, `full_story_az`, `metatitle_az`, `descr_az`, `keywords_az`, `tags_az`, `title_ru`, `alt_name_ru`, `short_story_ru`, `full_story_ru`, `metatitle_ru`, `descr_ru`, `keywords_ru`, `tags_ru`) VALUES
(4, 'admin', '2025-11-18 08:10:58', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/drop.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">BLACK GOLD - Digital Energy &amp; Eco Finance Ecosystem </p>', '', '', 'BLACK GOLD - GLOBAL STARTUP PITCHDECK', 'BLACK GOLD - Digital Energy &amp; Eco Finance Ecosystem', 'BLACK, Digital, Energy, Finance, Ecosystem', '2', 'black-gold-global-startup-pitchdeck', 0, 1, 1, 1, 0, 0, '', '', '', 'BLACK GOLD – QLOBAL STARTAP PİTÇDEKİ', 'black-gold-global-startup-pitchdeck', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/drop.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">BLACK GOLD – Rəqəmsal Enerji və Ekoloji Maliyyə Ekosistemi</p>', '', '', 'BLACK GOLD – Rəqəmsal Enerji və Ekoloji Maliyyə Ekosistemi', 'BLACK, Rəqəmsal, Enerji, Ekoloji, Maliyyə, Ekosistemi', '', 'BLACK GOLD — ГЛОБАЛЬНАЯ ПРЕЗЕНТАЦИЯ СТАРТАПА', 'black-gold-globalnaya-prezentaciya-startapa', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/drop.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">BLACK GOLD — Цифровая экосистема энергетики и экологических финансов</p>', '', '', 'BLACK GOLD — Цифровая экосистема энергетики и экологических финансов', 'BLACK, Цифровая, экосистема, энергетики, экологических, финансов', ''),
(7, 'admin', '2025-11-18 09:39:09', '<p>BGORIUM views blockchain not just as cryptocurrency, but as a foundation for trust within the modern economic system.<img src=\\\"https://bgorium.com/uploads/posts/2025-11/blockchain.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', 'title1|title1||desc1|desc1||title2|title2||desc2|desc2||title3|title3||desc3|desc3||title4|title4||desc4|desc4', 'Blockchain Technology', 'BGORIUM views blockchain not just as cryptocurrency, but as a foundation for trust within the modern economic system.', 'BGORIUM, views, blockchain, cryptocurrency, foundation, trust, within, modern, economic, system', '9', 'blockchain-technology', 0, 1, 1, 1, 0, 0, '', '', '', 'Blokçeyn Texnologiyası', 'blokceyn-texnologiyas', '<p>BGORIUM blokçeyni yalnız kriptovalyuta kimi deyil, müasir iqtisadi sistemdə etibarın təməli kimi görür.<br><br><img src=\\\"https://bgorium.com/uploads/posts/2025-11/blockchain.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', '', 'BGORIUM blokçeyni yalnız kriptovalyuta kimi deyil, müasir iqtisadi sistemdə etibarın təməli kimi görür.', 'BGORIUM, blokçeyni, yalnız, kriptovalyuta, deyil, müasir, iqtisadi, sistemdə, etibarın, təməli, görür', '', 'Технология блокчейн', 'tehnologiya-blokcheyn', '<p>BGORIUM рассматривает блокчейн не просто как криптовалюту, а как основу доверия в современной экономической системе.<br><br><img src=\\\"https://bgorium.com/uploads/posts/2025-11/blockchain.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', '', 'BGORIUM рассматривает блокчейн не просто как криптовалюту, а как основу доверия в современной экономической системе.', 'BGORIUM, рассматривает, блокчейн, просто, криптовалюту, основу, доверия, современной, экономической, системе', ''),
(8, 'admin', '2025-11-18 09:40:00', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/fintech.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">BGORIUM’s fintech division merges traditional banking with digital payments and crypto-financial technologies.</p>', '', 'title1|BGC Digital Payments||desc1|Through the BGC token, users can make instant, secure, and low-fee payments from anywhere in the world. The blockchain-based payment mechanism ensures that all transactions are immutable and transparent. This service is suitable for both personal payments and business operations. Goal — to make global payments more flexible and accessible.||title2|BLACK GOLD Card Services||desc2|BLACK GOLD cards (Classic, World, Premium) allow users to spend their digital assets in real life. The cards offer benefits such as cashback, GPS tracking, premium services, and global payment support. Financial operations are protected with high-level security. Goal — to integrate digital currency into everyday life.||title3|AI-Powered Financial Analytics||desc3|The AI system provides users with market analyses, token price forecasts, and personalized financial recommendations. The algorithms rely on energy values, ecological activity, and market indicators. This helps investors make smarter and more informed decisions. Goal — to make advanced analytical support accessible to all users.||title4|Secure Blockchain Infrastructure||desc4|The BLACK GOLD private blockchain infrastructure delivers high security, fast transaction processing, and a resilient architecture. All data is stored in encrypted form, and every transaction is recorded immutably. Maximum transparency and security are ensured for all users. Goal — to raise the reliability standard in digital finance.', 'Fintech Solutions', 'BGORIUM’s fintech division merges traditional banking with digital payments and crypto-financial technologies.', 'BGORIUM’s, fintech, division, merges, traditional, banking, digital, payments, cryptofinancial, technologies', '9', 'fintech-solutions', 0, 1, 1, 1, 0, 0, '', '', '', 'Fintech Həlləri', 'fintech-hllri', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/fintech.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">BGORIUM-un fintech şöbəsi ənənəvi bankçılığı rəqəmsal ödənişlər və kripto-maliyyə texnologiyaları ilə birləşdirir.</p>', '', '', 'BGORIUM-un fintech şöbəsi ənənəvi bankçılığı rəqəmsal ödənişlər və kripto-maliyyə texnologiyaları ilə birləşdirir.', 'BGORIUM, fintech, şöbəsi, ənənəvi, bankçılığı, rəqəmsal, ödənişlər, kripto, maliyyə, texnologiyaları, birləşdirir', '', 'Финтех-решения', 'finteh-resheniya', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/fintech.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Финтех-подразделение BGORIUM объединяет традиционный банкинг с цифровыми платежами и криптофинансовыми технологиями.</p>', '', '', 'Финтех-подразделение BGORIUM объединяет традиционный банкинг с цифровыми платежами и криптофинансовыми технологиями.', 'Финтех, подразделение, BGORIUM, объединяет, традиционный, банкинг, цифровыми, платежами, криптофинансовыми, технологиями', ''),
(9, 'admin', '2025-11-18 09:40:39', '<p>BGORIUM also builds a comprehensive digital market infrastructure.<img src=\\\"https://bgorium.com/uploads/posts/2025-11/tradingplat.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', 'title1|Web Trading Platform||desc1|This platform allows users to trade BGC and ecosystem tokens directly through their web browser. It is equipped with live charts, order types, analytics, and security modules. Users can securely access the platform from anywhere. Goal — to create a powerful and accessible trading environment for everyone.||title2|Mobile Trading App||desc2|The mobile application provides real-time trading capabilities even while users are on the move. Push notifications, AI-powered analytics, and a user-friendly design make the experience seamless. Blockchain-based identification ensures maximum security. Goal — to make trading fast, portable, and convenient.||title3|Desktop Pro Trading Suite||desc3|A fully featured desktop terminal designed for professional traders is offered. It includes advanced analytics, multiple chart windows, and real-time market data. Algorithmic trading is supported through API integration. Goal — to deliver institutional-level performance.||title4|API & Algorithmic Trading||desc4|A high-speed API infrastructure is provided for developers and large-scale traders. Trading bots, automated strategies, and AI-driven algorithms can be integrated with the platform. Data flows are delivered and received with minimal latency. Goal — to connect the ecosystem to global trading infrastructures in a competitive and efficient way.', 'Trading Platforms', 'BGORIUM also builds a comprehensive digital market infrastructure.', 'BGORIUM, builds, comprehensive, digital, market, infrastructure', '9', 'trading-platforms', 0, 1, 1, 1, 0, 0, '', '', '', 'Ticarət Platformaları', 'ticart-platformalar', '<p>BGORIUM həmçinin geniş rəqəmsal bazar infrastrukturu yaradır.<br><img src=\\\"https://bgorium.com/uploads/posts/2025-11/tradingplat.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', '', 'BGORIUM həmçinin geniş rəqəmsal bazar infrastrukturu yaradır.', 'BGORIUM, həmçinin, geniş, rəqəmsal, bazar, infrastrukturu, yaradır', '', 'Торговые платформы', 'torgovye-platformy', '<p>BGORIUM также создаёт комплексную цифровую рыночную инфраструктуру.<br><br><img src=\\\"https://bgorium.com/uploads/posts/2025-11/tradingplat.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', '', 'BGORIUM также создаёт комплексную цифровую рыночную инфраструктуру.', 'BGORIUM, также, создаёт, комплексную, цифровую, рыночную, инфраструктуру', ''),
(10, 'admin', '2025-11-18 09:41:16', '<p>Every BGORIUM project operates under the principle of environmental responsibility.<img src=\\\"https://bgorium.com/uploads/posts/2025-11/da22698d7ad51d10c6f98bcbcbc810e1-removebg-preview.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', 'title1|Renewable Energy Integration||desc1|The BGORIUM ecosystem promotes solar, wind, and other clean energy sources and converts their usage into digital value. Users are rewarded with GREEN BGC as they choose renewable energy options. This contributes to cleaner energy consumption and a reduction in carbon footprint. Goal — to standardize the use of clean energy across a wide audience.||title2|Carbon Footprint Reduction||desc2|The platform provides digital systems that track the carbon emissions of users and companies. Emission reductions are digitally measured and rewarded. This enables increased ecological awareness and the transformation of actions into real impact. Target — the gradual reduction of the global carbon burden.||title3|Recycling & Waste Management Initiatives||desc3|Proper waste separation and recycling by users are among the rewarded activities within the platform. BGORIUM verifies these actions through digital tracking and generates value using GREEN BGC. This initiative helps cultivate a culture of environmental responsibility within society. Goal — reducing waste and increasing recycling rates.||title4|Environmental Awareness & Education||desc4|The platform offers trainings, awareness programs, and digital campaigns to increase environmental knowledge among people. Building an informed society capable of addressing ecological challenges is a primary priority. This approach not only rewards users but also promotes meaningful behavioral change. Goal — to shape a healthier and greener planet for future generations.', 'Environmental Initiatives', 'Every BGORIUM project operates under the principle of environmental responsibility.', 'Every, BGORIUM, project, operates, under, principle, environmental, responsibility', '9', 'environmental-initiatives', 0, 1, 1, 1, 0, 0, '', '', '', 'Ətraf Mühit Təşəbbüsləri', 'traf-muhit-tbbuslri', '<p>Hər bir BGORIUM layihəsi ekoloji məsuliyyət prinsipi ilə fəaliyyət göstərir.<br><img src=\\\"https://bgorium.com/uploads/posts/2025-11/da22698d7ad51d10c6f98bcbcbc810e1-removebg-preview.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', '', 'Hər bir BGORIUM layihəsi ekoloji məsuliyyət prinsipi ilə fəaliyyət göstərir.', 'BGORIUM, layihəsi, ekoloji, məsuliyyət, prinsipi, fəaliyyət, göstərir', '', 'Экологические инициативы', 'ekologicheskie-iniciativy', '<p>Каждый проект BGORIUM реализуется на принципах экологической ответственности.<br><img src=\\\"https://bgorium.com/uploads/posts/2025-11/da22698d7ad51d10c6f98bcbcbc810e1-removebg-preview.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '', '', 'Каждый проект BGORIUM реализуется на принципах экологической ответственности.', 'Каждый, проект, BGORIUM, реализуется, принципах, экологической, ответственности', ''),
(11, 'admin', '2025-11-18 09:41:49', '<p>BGORIUM not only develops future technologies — it also teaches them.<img src=\\\"https://bgorium.com/uploads/posts/2025-11/p-main__list-icon.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '<p>sd</p>', 'title1|Blockchain & Digital Economy Training||desc1|In this module, users are taught the fundamentals of blockchain technology, the structure of the digital economy, and the operating principles of token systems. The training consists of both theoretical and practical components. Participants gain hands-on experience by performing test operations within a real ecosystem. The goal is to develop a deep understanding of the digitalization of energy and ecological value.||title2|Energy & Ecology Awareness Program||desc2|This module provides real knowledge on measuring environmental impact, reducing carbon emissions, and improving energy efficiency. The program explains the GREEN BGC mechanism and how ecological activities are transformed into digital value. The training is enriched with real-life examples. The goal is to foster environmental responsibility and build a culture of green behavior in society.||title3|Fintech & Payment Systems Education||desc3|Here, users learn about next-generation digital payment systems, AI-powered financial models, and the operating principles of BLACK GOLD cards. The training is aligned with global trends in financial technologies. Participants develop practical skills for payment systems used in both personal and business contexts. The goal is to enhance digital financial literacy.||title4|AI & Data Analytics Training||desc4|In this module, topics such as energy analysis using artificial intelligence, oil price forecasting, and monitoring environmental indicators are taught. Participants learn to work with real datasets and build analytical models through hands-on exercises. The training is designed for both beginner and professional levels. The goal is to prepare future specialists in the energy-fintech field.', 'Education &amp; Training', 'BGORIUM not only develops future technologies — it also teaches them.', 'BGORIUM, develops, future, technologies, teaches', '9', 'education-training', 0, 1, 1, 1, 0, 0, '', '', '', 'Təhsil və Təlim', 'thsil-v-tlim', '<p>BGORIUM yalnız gələcək texnologiyalarını inkişaf etdirmir — həm də onları öyrədir.<br><img src=\\\"https://bgorium.com/uploads/posts/2025-11/p-main__list-icon.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '<p>sd</p>', '', 'BGORIUM yalnız gələcək texnologiyalarını inkişaf etdirmir — həm də onları öyrədir.', 'BGORIUM, yalnız, gələcək, texnologiyalarını, inkişaf, etdirmir, onları, öyrədir', '', 'Образование и обучение', 'obrazovanie-i-obuchenie', '<p>BGORIUM не только разрабатывает технологии будущего — оно также обучает им.<br><br><img src=\\\"https://bgorium.com/uploads/posts/2025-11/p-main__list-icon.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\"></p>', '<p>сд</p>', '', 'BGORIUM не только разрабатывает технологии будущего — оно также обучает им.', 'BGORIUM, только, разрабатывает, технологии, будущего, также, обучает', ''),
(12, 'admin', '2025-11-18 10:15:28', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/ecology.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Smart solutions to help you save time, resources, and budget while managing complex eco-projects without extra effort.</p>', '<p>Smart solutions to help you save time, resources, and budget while managing complex eco-projects without extra effort.</p>', 'title1|title1||desc1|desc1||title2|title2||desc2|desc2||title3|title3||desc3|desc3', 'Ecology &amp; Projects', 'Поможем сэкономить время, силы и фи', 'Поможем, сэкономить, время', '10', 'ecology-projects', 0, 1, 1, 1, 0, 0, '', '', '', 'Ekologiya və Layihələr', 'ekologiya-v-layihlr', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/ecology.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Ağılcı həllər: mürəkkəb ekoloji layihələri əlavə çaba olmadan idarə edərkən vaxt, resurs və büdcəni qənaət edin.</p>', '<p>Ağılcı həllər: mürəkkəb ekoloji layihələri əlavə çaba olmadan idarə edərkən vaxt, resurs və büdcəni qənaət edin.</p>', '', 'Ağılcı həllər: mürəkkəb ekoloji layihələri əlavə çaba olmadan idarə edərkən vaxt, resurs və büdcəni qənaət edin.', 'Ağılcı, həllər, mürəkkəb, ekoloji, layihələri, əlavə, olmadan, idarə, edərkən, resurs, büdcəni, qənaət', '', 'Экология и проекты', 'ekologiya-i-proekty', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/ecology.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Интеллектуальные решения, которые помогают экономить время, ресурсы и бюджет при управлении сложными экологическими проектами без лишних усилий.</p>', '<p>Интеллектуальные решения, которые помогают экономить время, ресурсы и бюджет при управлении сложными экологическими проектами без лишних усилий.</p>', '', 'Интеллектуальные решения, которые помогают экономить время, ресурсы и бюджет при управлении сложными экологическими проектами без лишних усилий.', 'Интеллектуальные, решения, которые, помогают, экономить, время, ресурсы, бюджет, управлении, сложными, экологическими, проектами, лишних, усилий', ''),
(13, 'admin', '2025-11-18 10:18:19', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/oil.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">A powerful analytics system for monitoring and predicting oil prices.<br>Cloud infrastructure and server-grade tools tailored for your business needs.</p>', '<p>A powerful analytics system for monitoring and predicting oil prices.<br>Cloud infrastructure and server-grade tools tailored for your business needs.</p>', 'title1|title1||desc1|desc1||title2|title2||desc2|desc2||title3|title3||desc3|desc3', 'Oil prices', 'Облачная платформа и серверное оборудование для решения ваших бизнес-задач', 'Облачная, платформа, серверное, оборудование, решения, ваших, бизнесзадач', '10', 'oil-prices', 0, 1, 1, 1, 0, 0, '', '', '', 'Neft qiymətləri', 'neft-qiymtlri', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/oil.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Neft qiymətlərini izləmək və proqnozlaşdırmaq üçün güclü analitika sistemi.<br>Biznes ehtiyaclarınıza uyğun bulud infrastrukturu və server səviyyəli alətlər.</p>', '<p>Neft qiymətlərini izləmək və proqnozlaşdırmaq üçün güclü analitika sistemi.<br>Biznes ehtiyaclarınıza uyğun bulud infrastrukturu və server səviyyəli alətlər.</p>', '', 'Neft qiymətlərini izləmək və proqnozlaşdırmaq üçün güclü analitika sistemi. Biznes ehtiyaclarınıza uyğun bulud infrastrukturu və server səviyyəli alətlər.', 'qiymətlərini, izləmək, proqnozlaşdırmaq, güclü, analitika, sistemi, Biznes, ehtiyaclarınıza, uyğun, bulud, infrastrukturu, server, səviyyəli, alətlər', '', 'Цены на нефть', 'ceny-na-neft', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/oil.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Мощная аналитическая система для отслеживания и прогнозирования цен на нефть.<br>Облачная инфраструктура и серверные инструменты, адаптированные под потребности вашего бизнеса.</p>', '<p>Мощная аналитическая система для отслеживания и прогнозирования цен на нефть.<br>Облачная инфраструктура и серверные инструменты, адаптированные под потребности вашего бизнеса.</p>', '', 'Мощная аналитическая система для отслеживания и прогнозирования цен на нефть. Облачная инфраструктура и серверные инструменты, адаптированные под потребности вашего бизнеса.', 'Мощная, аналитическая, система, отслеживания, прогнозирования, нефть, Облачная, инфраструктура, серверные, инструменты, адаптированные, потребности, вашего, бизнеса', ''),
(14, 'admin', '2025-11-18 10:18:50', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/bank.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Reliable financial tools for seamless payments, card management, and secure operations at an optimal cost.</p>', '<p>Reliable financial tools for seamless payments, card management, and secure operations at an optimal cost.</p>', 'title1|title1||desc1|desc1||title2|title2||desc2|desc2||title3|title3||desc3|desc3', 'Bank &amp; Cards', 'Качественный хостинг по оптимальной цене', 'Качественный, хостинг, оптимальной', '10', 'bank-cards', 0, 1, 1, 1, 0, 0, '', '', '', 'Bank və Kartlar', 'bank-v-kartlar', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/bank.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Rahat ödənişlər, kart idarəsi və təhlükəsiz əməliyyatlar üçün etibarlı maliyyə alətləri, optimal xərclərlə.</p>', '<p>Rahat ödənişlər, kart idarəsi və təhlükəsiz əməliyyatlar üçün etibarlı maliyyə alətləri, optimal xərclərlə.</p>', '', 'Rahat ödənişlər, kart idarəsi və təhlükəsiz əməliyyatlar üçün etibarlı maliyyə alətləri, optimal xərclərlə.', 'Rahat, ödənişlər, idarəsi, təhlükəsiz, əməliyyatlar, etibarlı, maliyyə, alətləri, optimal, xərclərlə', '', 'Банк и карты', 'bank-i-karty', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/bank.png\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Надёжные финансовые инструменты для удобных платежей, управления картами и безопасных операций с оптимальными затратами.</p>', '<p>Надёжные финансовые инструменты для удобных платежей, управления картами и безопасных операций с оптимальными затратами.</p>', '', 'Надёжные финансовые инструменты для удобных платежей, управления картами и безопасных операций с оптимальными затратами.', 'Надёжные, финансовые, инструменты, удобных, платежей, управления, картами, безопасных, операций, оптимальными, затратами', ''),
(15, 'admin', '2025-11-18 10:19:32', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/statik.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">A secure platform for data exchange, automation, and scaling your online trading ecosystem with confidence.</p>', '<p>A secure platform for data exchange, automation, and scaling your online trading ecosystem with confidence.</p>', 'title1|title1||desc1|desc1||title2|title2||desc2|desc2||title3|title3||desc3|desc3', 'Trading &amp; Marketplaces', 'Для безопасного обмена данными между сайтом и его посетителями', 'безопасного, обмена, данными, между, сайтом, посетителями', '10', 'trading-marketplaces', 0, 1, 1, 1, 0, 0, '', '', '', 'Ticarət və Bazarlar', 'ticart-v-bazarlar', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/statik.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Təhlükəsiz platforma: məlumat mübadiləsi, avtomatlaşdırma və onlayn ticarət ekosisteminizi etibarla genişləndirmək üçün.</p>', '<p>Təhlükəsiz platforma: məlumat mübadiləsi, avtomatlaşdırma və onlayn ticarət ekosisteminizi etibarla genişləndirmək üçün.</p>', '', 'Təhlükəsiz platforma: məlumat mübadiləsi, avtomatlaşdırma və onlayn ticarət ekosisteminizi etibarla genişləndirmək üçün.', 'Təhlükəsiz, platforma, məlumat, mübadiləsi, avtomatlaşdırma, onlayn, ticarət, ekosisteminizi, etibarla, genişləndirmək', '', 'Торговля и торговые площадки', 'torgovlya-i-torgovye-ploschadki', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/statik.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Безопасная платформа для обмена данными, автоматизации и масштабирования вашей онлайн-торговой экосистемы с уверенностью.</p>', '<p>Безопасная платформа для обмена данными, автоматизации и масштабирования вашей онлайн-торговой экосистемы с уверенностью.</p>', '', 'Безопасная платформа для обмена данными, автоматизации и масштабирования вашей онлайн-торговой экосистемы с уверенностью.', 'Безопасная, платформа, обмена, данными, автоматизации, масштабирования, вашей, онлайн, торговой, экосистемы, уверенностью', ''),
(16, 'admin', '2025-11-18 10:20:03', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/renewable-energy-3d-icon-png-download-5386087.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Innovative solutions for managing energy assets and maximizing returns through secure digital staking.</p>', '<p>Innovative solutions for managing energy assets and maximizing returns through secure digital staking.</p>', 'title1|title1||desc1|desc1||title2|title2||desc2|desc2||title3|title3||desc3|desc3', 'Energy Resources &amp; Staking', 'Description for item 5', 'Description', '10', 'energy-resources-staking', 0, 1, 1, 1, 0, 0, '', '', '', 'Enerji Resursları və Staking', 'enerji-resurslar-v-staking', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/renewable-energy-3d-icon-png-download-5386087.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Təhlükəsiz rəqəmsal staking vasitəsilə enerji aktivlərinin idarə olunması və gəlirin maksimuma çatdırılması üçün innovativ həllər.</p>', '<p>Təhlükəsiz rəqəmsal staking vasitəsilə enerji aktivlərinin idarə olunması və gəlirin maksimuma çatdırılması üçün innovativ həllər.</p>', '', 'Təhlükəsiz rəqəmsal staking vasitəsilə enerji aktivlərinin idarə olunması və gəlirin maksimuma çatdırılması üçün innovativ həllər.', 'Təhlükəsiz, rəqəmsal, staking, vasitəsilə, enerji, aktivlərinin, idarə, olunması, gəlirin, maksimuma, çatdırılması, innovativ, həllər', '', 'Энергетические ресурсы и стейкинг', 'energeticheskie-resursy-i-steyking', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/renewable-energy-3d-icon-png-download-5386087.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Инновационные решения для управления энергетическими активами и максимизации доходности через безопасный цифровой стейкинг.</p>', '<p>Инновационные решения для управления энергетическими активами и максимизации доходности через безопасный цифровой стейкинг.</p>', '', 'Инновационные решения для управления энергетическими активами и максимизации доходности через безопасный цифровой стейкинг.', 'Инновационные, решения, управления, энергетическими, активами, максимизации, доходности, через, безопасный, цифровой, стейкинг', ''),
(17, 'admin', '2025-11-18 10:20:30', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/share-3d-icon-isolated-on-transparent-background-gold-texture-3d-rendering-free-png.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Easy access to corporate stocks with clear insights, analytics, and tools for informed investment decisions.</p>', '<p>Easy access to corporate stocks with clear insights, analytics, and tools for informed investment decisions.</p>', 'title1|title1||desc1|desc1||title2|title2||desc2|desc2||title3|title3||desc3|desc3', 'Company shares', 'Description for item 6', 'Description', '10', 'company-shares', 0, 1, 1, 1, 0, 0, '', '', '', 'Şirkət səhmləri', 'irkt-shmlri', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/share-3d-icon-isolated-on-transparent-background-gold-texture-3d-rendering-free-png.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Şirkət səhmlərinə asan çıxış, şəffaf məlumatlar, analizlər və məlumatlı investisiya qərarları üçün alətlər təqdim edir.</p>', '<p>Şirkət səhmlərinə asan çıxış, aydın məlumatlar, analizlər və məlumatlı investisiya qərarları üçün alətlər təqdim edir.</p>', '', 'Şirkət səhmlərinə asan çıxış, aydın məlumatlar, analizlər və məlumatlı investisiya qərarları üçün alətlər təqdim edir.', 'Şirkət, səhmlərinə, çıxış, aydın, məlumatlar, analizlər, məlumatlı, investisiya, qərarları, alətlər, təqdim', '', 'Акции компании', 'akcii-kompanii', '<p><img src=\\\"https://bgorium.com/uploads/posts/2025-11/share-3d-icon-isolated-on-transparent-background-gold-texture-3d-rendering-free-png.webp\\\" alt=\\\"\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\">Простой доступ к корпоративным акциям с прозрачной информацией, аналитикой и инструментами для принятия обоснованных инвестиционных решений.</p>', '<p>Простой доступ к акциям компаний с прозрачной информацией, аналитикой и инструментами для принятия обоснованных инвестиционных решений.</p>', '', 'Простой доступ к акциям компаний с прозрачной информацией, аналитикой и инструментами для принятия обоснованных инвестиционных решений.', 'Простой, доступ, акциям, компаний, прозрачной, информацией, аналитикой, инструментами, принятия, обоснованных, инвестиционных, решений', ''),
(18, 'admin', '2025-11-20 08:26:58', '<p>Green Commitment is the core philosophy of the BLACK GOLD ecosystem, representing a strategic pledge aimed at creating a cleaner, more sustainable, and environmentally healthy world for future generations. This concept shows that the ecosystem is not built solely on energy and financial value, but also strives to enhance ecological impact and make a tangible contribution to environmental protection. Green Commitment introduces a new system that transforms every user’s ecological activity into a measurable, trackable, and rewardable format. Within this commitment, users’ eco-friendly actions are evaluated through the GREEN BGC token, turning each environmental activity into real economic value. The platform implements environmental initiatives not just as slogans but as a motivation model supported by digital assets. Through this model, the BLACK GOLD ecosystem proves that ecological activities not only create social benefits but also generate economic value. Activities such as tree planting, waste sorting, energy saving, and the promotion of clean transportation are recorded digitally and generate additional value for users. Green Commitment also brings measurable transparency to the environmental actions of companies and organizations, strengthening their level of corporate social responsibility and setting new standards for ecological reporting, carbon emission tracking, and monitoring green initiatives at the governmental level.</p>\n<p>The mission of the Green Commitment is not only to build a healthier global ecosystem but also to drive long-term behavioral change in society. The GREEN BGC token stands at the center of this commitment, ensuring that every environmental action earns digital value. The platform provides incentive mechanisms at individual, corporate, and national levels to strengthen environmental impact, positioning Green Commitment as a global model at the intersection of technology and environmental responsibility—one that will play a critical role in the future development of the digital economy. Through this commitment, the BLACK GOLD ecosystem supports the balancing of ecological impacts arising from energy production and consumption while offering additional incentives for carbon reduction and increased energy efficiency. Users not only earn rewards but also create real, measurable environmental benefits, enabling millions worldwide to engage in eco-responsible behavior. This long-term strategy aims to build a sustainable bridge between energy, ecology, and digital finance systems, transforming ecological value into economic value on a global scale. In conclusion, the Green Commitment is not just an environmental program—it is the foundational value system that shapes the philosophy of BLACK GOLD and a powerful digital incentive mechanism for the global green transition.</p>', '', '', 'Post 1', 'Green Commitment is the core philosophy of the BLACK GOLD ecosystem, representing a strategic pledge aimed at creating a cleaner, more sustainable, and environmentally healthy world for future generations. This concept shows that the ecosystem is not built solely on energy and financial value, but', 'environmental, value, Green, Commitment, ecological, ecosystem, energy, digital, BLACK, global, measurable, commitment, economic, model, philosophy, sustainable, future, impact, system, every', '6', 'post-1', 0, 1, 1, 1, 0, 0, '', '', '', 'Post 1', 'post-1', '<p>“Green Commitment BLACK GOLD ekosisteminin əsas fəlsəfəsidir və gələcək nəsillər üçün daha təmiz, davamlı və ekoloji cəhətdən sağlam dünya yaratmağı hədəfləyən strateji öhdəliyi təmsil edir. Bu konsept göstərir ki, ekosistem yalnız enerji və maliyyə dəyərinə əsaslanmır, həm də ekoloji təsiri artırmaq və ətraf mühitin qorunmasına əhəmiyyətli töhfə vermək məqsədi güdür. Green Commitment hər bir istifadəçinin ekoloji fəaliyyətini ölçülə bilən, izlənə bilən və mükafatlandırıla bilən formata çevirən yeni bir sistem təqdim edir.</p>\n<p>Bu öhdəlik çərçivəsində istifadəçilərin ekoloji dostu hərəkətləri GREEN BGC tokeni vasitəsilə qiymətləndirilir və hər bir ekoloji fəaliyyət real iqtisadi dəyərə çevrilir. Platforma ətraf mühit təşəbbüslərini yalnız şüar kimi deyil, rəqəmsal aktivlərlə dəstəklənən motivasiya modeli kimi həyata keçirir. Bu model vasitəsilə BLACK GOLD ekosistemi göstərir ki, ekoloji fəaliyyətlər yalnız sosial fayda yaratmaqla kifayətlənmir, həm də iqtisadi dəyər yaradır. Məsələn, ağac əkmə, tullantıların çeşidlənməsi, enerji qənaəti və təmiz nəqliyyatın təşviqi rəqəmsal şəkildə qeyd olunur və istifadəçilər üçün əlavə dəyər yaradır.</p>\n<p>Green Commitment həmçinin şirkətlərin və təşkilatların ekoloji fəaliyyətlərinə ölçülə bilən şəffaflıq gətirir, onların korporativ sosial məsuliyyət səviyyəsini gücləndirir və ekoloji hesabat, karbon emissiyası izləmə və hökumət səviyyəsində yaşıl təşəbbüslərin monitorinqi üçün yeni standartlar müəyyən edir.</p>\n<p>Green Commitment-in missiyası yalnız sağlam qlobal ekosistem qurmaq deyil, həm də cəmiyyətdə uzunmüddətli davranış dəyişikliyini təşviq etməkdir. GREEN BGC tokeni bu öhdəliyin mərkəzində dayanır və hər bir ekoloji hərəkətin rəqəmsal dəyər qazanmasını təmin edir. Platforma fərdi, korporativ və milli səviyyələrdə təşviq mexanizmləri təqdim edərək ekoloji təsiri gücləndirir və Green Commitment-i texnologiya və ekoloji məsuliyyətin kəsişməsində qlobal model kimi mövqeləndirir—bu model rəqəmsal iqtisadiyyatın gələcək inkişafında kritik rol oynayacaq.</p>\n<p>Bu öhdəlik vasitəsilə BLACK GOLD ekosistemi enerji istehsalı və istehlakı nəticəsində yaranan ekoloji təsirlərin balanslaşdırılmasını dəstəkləyir, karbon azaldılması və enerji səmərəliliyinin artırılması üçün əlavə təşviqlər təqdim edir. İstifadəçilər yalnız mükafat qazanmaqla kifayətlənmir, həm də real, ölçülə bilən ekoloji faydalar yaradır və milyonlarla insanı ekoloji məsuliyyətli davranışa cəlb edir.</p>\n<p>Bu uzunmüddətli strategiya enerji, ekologiya və rəqəmsal maliyyə sistemləri arasında davamlı körpü yaratmağı, ekoloji dəyəri qlobal miqyasda iqtisadi dəyərə çevirməyi hədəfləyir. Nəticə etibarilə, Green Commitment yalnız ekoloji proqram deyil—o, BLACK GOLD fəlsəfəsini formalaşdıran əsas dəyər sistemi və qlobal yaşıl keçid üçün güclü rəqəmsal təşviq mexanizmidir.”</p>', '', '', '“Green Commitment BLACK GOLD ekosisteminin əsas fəlsəfəsidir və gələcək nəsillər üçün daha təmiz, davamlı və ekoloji cəhətdən sağlam dünya yaratmağı hədəfləyən strateji öhdəliyi təmsil edir. Bu', 'ekoloji, Commitment, yalnız, rəqəmsal, enerji, Green, bilən, BLACK, dəyər, qlobal, ölçülə, təqdim, vasitəsilə, iqtisadi, model, yaradır, təşviq, gələcək, təmiz, davamlı', '', 'Пост 1', 'post-1', '<p>«Green Commitment» является ключевой философией экосистемы BLACK GOLD, представляя стратегическое обязательство по созданию более чистого, устойчивого и экологически здорового мира для будущих поколений. Эта концепция демонстрирует, что экосистема основана не только на энергии и финансовой ценности, но и стремится усиливать экологическое влияние и вносить реальный вклад в охрану окружающей среды.</p>\n<p>«Green Commitment» внедряет систему, которая превращает экологическую активность каждого пользователя в измеримый, отслеживаемый и вознаграждаемый формат. Действия пользователей оцениваются через токен <strong>GREEN BGC</strong>, превращая каждое экологическое мероприятие в реальную экономическую ценность. Платформа реализует экологические инициативы не как лозунги, а как модель мотивации, поддерживаемую цифровыми активами. Экосистема BLACK GOLD демонстрирует, что экологические действия создают не только социальную пользу, но и экономическую ценность. Деятельность, такая как посадка деревьев, сортировка отходов, энергосбережение и продвижение чистого транспорта, фиксируется в цифровом виде и приносит дополнительную ценность пользователям.</p>\n<p>«Green Commitment» обеспечивает измеримую прозрачность экологических действий компаний и организаций, усиливает их корпоративную социальную ответственность и задаёт новые стандарты экологической отчётности, отслеживания выбросов углерода и мониторинга «зелёных» инициатив на государственном уровне.</p>\n<p>Миссия «Green Commitment» заключается не только в создании более здоровой глобальной экосистемы, но и в стимулировании долгосрочных изменений в поведении общества. Токен <strong>GREEN BGC</strong> является центром этой инициативы, обеспечивая получение цифровой ценности за каждое экологическое действие. Платформа предоставляет механизмы стимулирования на индивидуальном, корпоративном и национальном уровнях, укрепляя экологическое воздействие. «Green Commitment» позиционируется как глобальная модель на пересечении технологий и экологической ответственности, играющая ключевую роль в развитии цифровой экономики будущего.</p>\n<p>Через эту инициативу экосистема BLACK GOLD поддерживает баланс экологического воздействия, возникающего при производстве и потреблении энергии, предлагая дополнительные стимулы для сокращения углеродных выбросов и повышения энергоэффективности. Пользователи не только получают вознаграждения, но и создают реальные, измеримые экологические преимущества, вовлекая миллионы людей по всему миру в экологически ответственное поведение.</p>\n<p>Долгосрочная стратегия направлена на создание устойчивого моста между энергетикой, экологией и цифровыми финансовыми системами, превращая экологическую ценность в экономическую на глобальном уровне. В заключение, «Green Commitment» — это не просто экологическая программа, а основополагающая система ценностей, формирующая философию BLACK GOLD и мощный цифровой механизм стимулов для глобального перехода к «зелёной» экономике.</p>', '', '', '«Green Commitment» является ключевой философией экосистемы BLACK GOLD, представляя стратегическое обязательство по созданию более чистого, устойчивого и экологически здорового мира для будущих', '«Green, Commitment», BLACK, только, экологическое, ценность, экономическую, экологические, цифровой, является, экосистемы, более, чистого, устойчивого, экологически, демонстрирует, экосистема, энергии, ценности, экологическую', ''),
(19, 'admin', '2025-11-20 08:28:49', '<p>The BGC Token is the main digital currency of the BLACK GOLD ecosystem, serving as the central token where energy, ecology, and financial flows ultimately converge. The value passing through the OILCOIN and GREEN BGC stages is finalized and enters the digital economy in the form of the BGC Token. Its core purpose is to connect the digital value generated from energy to the real economic ecosystem, offering users an asset that can be practically utilized in everyday spending. The token plays a key role across all ecosystem services — including payments, the card system, the staking mechanism, and the reward model. The primary objective behind the creation of the BGC Token is to transform energy and ecological value into a fully functional blockchain-based economy. Acting as an internal value carrier, the token ensures that transactions between users are executed securely, quickly, and transparently. Through BLACK GOLD cards, BGC can also be used in real commerce, enabling users to spend their digital assets in daily purchases.</p>\n<p>At the same time, the BGC Token serves as the main stabilizing element of the ecosystem, designed to regulate liquidity flows and maintain economic balance. One of its core goals is to create a global digital economic foundation that connects millions of people to energy and ecological value. The Token also transfers rewards generated from GREEN BGC ecological activities, giving real economic value to environmental contributions. The ecosystem’s staking system operates through BGC, offering users long-term earning opportunities. Another goal of the token is to form a value exchange model adaptable to the global energy market. BGC Token will be open for global use while being safeguarded on a secure proprietary blockchain. It will play a significant role for companies and governmental institutions by converting energy and ecological activities into digital economic indicators. In addition, the token may provide certain governance and decision-making rights within the ecosystem. Designed for long-term integration into the energy–information economy, BGC supports all pricing models, reward mechanisms, and economic motivation systems of the BLACK GOLD ecosystem. Its mission is not just to serve as a digital asset but to establish an economic standard that carries both energy and ecological value. Ultimately, the long-term goal of the BGC Token is to build a sustainable global value infrastructure connecting hundreds of millions of people to the ecosystem. In conclusion, the BGC Token is the heart of the BLACK GOLD ecosystem and the strategic unit where energy, ecology, and financial flows converge.</p>', '', '', 'BGC Token', 'The BGC Token is the main digital currency of the BLACK GOLD ecosystem, serving as the central token where energy, ecology, and financial flows ultimately converge. The value passing through the OILCOIN and GREEN BGC stages is finalized and enters the digital economy in the form of the BGC Token.', 'value, Token, ecosystem, energy, digital, economic, token, ecological, BLACK, users, global, flows, economy, longterm, where, ecology, financial, converge, through, GREEN', '7', 'bgc-token', 0, 1, 1, 1, 0, 0, '', '', '', 'BGC Tokeni', 'bgc-tokeni', '<p>BGC Token BLACK GOLD ekosisteminin əsas rəqəmsal valyutasıdır və enerji, ekologiya və maliyyə axınlarının yekunlaşdığı mərkəzi token rolunu oynayır. OILCOIN və GREEN BGC mərhələlərindən keçən dəyər yekunlaşaraq BGC Token şəklində rəqəmsal iqtisadiyyata daxil olur. Tokenin əsas məqsədi enerjidən yaranan rəqəmsal dəyəri real iqtisadi ekosistemə bağlamaq və istifadəçilərə gündəlik xərclərdə praktik olaraq istifadə edə biləcəkləri aktiv təqdim etməkdir. Token ekosistemin bütün xidmətlərində—ödənişlərdə, kart sistemində, staking mexanizmində və mükafat modelində—əsas rol oynayır.</p>\n<p>BGC Tokenin yaradılmasının əsas məqsədi enerji və ekoloji dəyəri tam funksional blokçeyn əsaslı iqtisadiyyata çevirməkdir. Daxili dəyər daşıyıcısı kimi fəaliyyət göstərərək istifadəçilər arasında əməliyyatların təhlükəsiz, sürətli və şəffaf həyata keçirilməsini təmin edir. BLACK GOLD kartları vasitəsilə BGC real ticarətdə də istifadə oluna bilər və istifadəçilər rəqəmsal aktivlərini gündəlik alış-verişdə xərcləyə bilərlər.</p>\n<p>Eyni zamanda, BGC Token ekosistemin əsas stabilləşdirici elementi kimi çıxış edir, likvidlik axınlarını tənzimləmək və iqtisadi balansı qorumaq üçün nəzərdə tutulub. Tokenin əsas məqsədlərindən biri milyonlarla insanı enerji və ekoloji dəyərlə birləşdirən qlobal rəqəmsal iqtisadi əsas yaratmaqdır. Token GREEN BGC ekoloji fəaliyyətlərindən yaranan mükafatları da ötürür, ekoloji töhfələrə real iqtisadi dəyər verir. Ekosistemin staking sistemi BGC vasitəsilə fəaliyyət göstərir və istifadəçilərə uzunmüddətli qazanc imkanları təqdim edir.</p>\n<p>Tokenin digər məqsədi qlobal enerji bazarına uyğun dəyər mübadiləsi modelini formalaşdırmaqdır. BGC Token qlobal istifadə üçün açıq olacaq və təhlükəsiz, özəl blokçeyn üzərində qorunacaq. Şirkətlər və dövlət qurumları üçün enerji və ekoloji fəaliyyətləri rəqəmsal iqtisadi göstəricilərə çevirməkdə əhəmiyyətli rol oynayacaq. Bundan əlavə, token ekosistem daxilində müəyyən idarəetmə və qərarvermə hüquqları təmin edə bilər.</p>\n<p>Enerji–informasiya iqtisadiyyatına uzunmüddətli inteqrasiya üçün dizayn edilən BGC BLACK GOLD ekosisteminin bütün qiymət modellərini, mükafat mexanizmlərini və iqtisadi təşviq sistemlərini dəstəkləyir. Onun missiyası yalnız rəqəmsal aktiv kimi xidmət etmək deyil, həm enerji, həm də ekoloji dəyəri daşıyan iqtisadi standart yaratmaqdır.</p>\n<p>Nəticə etibarilə, BGC Token davamlı qlobal dəyər infrastrukturunu qurmaq və yüz milyonlarla insanı ekosistemə bağlamaq məqsədini daşıyır. BGC Token BLACK GOLD ekosisteminin ürəyi və enerji, ekologiya və maliyyə axınlarının birləşdiyi strateji vahiddir.</p>', '', '', 'BGC Token BLACK GOLD ekosisteminin əsas rəqəmsal valyutasıdır və enerji, ekologiya və maliyyə axınlarının yekunlaşdığı mərkəzi token rolunu oynayır. OILCOIN və GREEN BGC mərhələlərindən keçən dəyər', 'Token, rəqəmsal, enerji, iqtisadi, ekoloji, dəyər, BLACK, Tokenin, qlobal, ekosisteminin, məqsədi, dəyəri, istifadə, ekologiya, maliyyə, axınlarının, token, oynayır, GREEN, iqtisadiyyata', '', 'Токен BGC', 'token-bgc', '<p>Токен BGC является основной цифровой валютой экосистемы BLACK GOLD и служит центральным токеном, куда в конечном итоге сходятся потоки энергии, экологии и финансов. Ценность, проходящая через стадии OILCOIN и GREEN BGC, фиксируется и входит в цифровую экономику в форме токена BGC. Его основная цель — связать цифровую ценность, созданную из энергии, с реальной экономикой, предоставляя пользователям актив, который можно практически использовать в повседневных расходах.</p>\n<p>Токен играет ключевую роль во всех сервисах экосистемы — включая платежи, систему карт, механизм стейкинга и модель вознаграждений. Основная задача создания токена BGC — преобразовать ценность энергии и экологии в полнофункциональную экономику на блокчейне. Будучи внутренним носителем ценности, токен обеспечивает выполнение транзакций между пользователями безопасно, быстро и прозрачно. Через карты BLACK GOLD BGC также может использоваться в реальной коммерции, позволяя пользователям тратить цифровые активы при ежедневных покупках.</p>\n<p>В то же время токен BGC является основным стабилизирующим элементом экосистемы, предназначенным для регулирования потоков ликвидности и поддержания экономического баланса. Одной из ключевых целей является создание глобальной цифровой экономической основы, соединяющей миллионы людей с ценностью энергии и экологии. Токен также передаёт вознаграждения, генерируемые экологическими действиями GREEN BGC, придавая реальную экономическую ценность вкладу в экологию.</p>\n<p>Система стейкинга экосистемы функционирует через BGC, предоставляя пользователям возможности долгосрочного заработка. Ещё одной задачей токена является формирование модели обмена ценностью, адаптируемой к глобальному энергетическому рынку. Токен BGC будет доступен для глобального использования и защищён на безопасном собственном блокчейне. Он играет значимую роль для компаний и государственных учреждений, конвертируя энергетические и экологические действия в цифровые экономические показатели. Кроме того, токен может предоставлять определённые права голосования и участия в принятии решений внутри экосистемы.</p>\n<p>Разработанный для долгосрочной интеграции в энерг информационную экономику, BGC поддерживает все модели ценообразования, механизмы вознаграждений и системы экономической мотивации экосистемы BLACK GOLD. Его миссия — не только служить цифровым активом, но и устанавливать экономический стандарт, несущий ценность энергии и экологии. В конечном итоге долгосрочная цель токена BGC — построить устойчивую глобальную инфраструктуру ценности, соединяющую сотни миллионов людей с экосистемой.</p>\n<p>В заключение, токен BGC является сердцем экосистемы BLACK GOLD и стратегическим элементом, где сходятся потоки энергии, экологии и финансов.</p>', '', '', 'Токен BGC является основной цифровой валютой экосистемы BLACK GOLD и служит центральным токеном, куда в конечном итоге сходятся потоки энергии, экологии и финансов. Ценность, проходящая через стадии', 'экосистемы, энергии, является, экологии, Токен, BLACK, токена, ценность, токен, экономику, пользователям, цифровой, конечном, итоге, сходятся, потоки, финансов, через, GREEN, цифровую', '');
INSERT INTO `dle_post` (`id`, `autor`, `date`, `short_story`, `full_story`, `xfields`, `title`, `descr`, `keywords`, `category`, `alt_name`, `comm_num`, `allow_comm`, `allow_main`, `approve`, `fixed`, `allow_br`, `symbol`, `tags`, `metatitle`, `title_az`, `alt_name_az`, `short_story_az`, `full_story_az`, `metatitle_az`, `descr_az`, `keywords_az`, `tags_az`, `title_ru`, `alt_name_ru`, `short_story_ru`, `full_story_ru`, `metatitle_ru`, `descr_ru`, `keywords_ru`, `tags_ru`) VALUES
(20, 'admin', '2025-11-20 08:55:05', '<p>The primary goal of <b>BGORIUM LLC</b> is to create a global ecosystem that unites the fields of energy, ecology, and digital finance. We aim to preserve the real value generated from energy, prevent its loss, and transfer this value into the digital economy, opening new economic opportunities for millions of people. Our mission is to ensure that the value of natural resources is not only recognized in physical markets but also measured, tracked, and transformed into user-benefiting digital value. Through the BLACK GOLD ecosystem, we enable the digitalization of energy value, the measurement of ecological incentives, and the enhancement of real economic impact through digital assets. We envision transparent management of data arising from energy production and consumption through blockchain technology as a core objective. As a company, we encourage individuals, businesses, and government institutions to join the digital economy driven by energy value. Our mission is to transform ecological actions from mere social responsibility into a real incentive model with measurable economic benefits. Through the OILCOIN, GREEN BGC, and BGC token flow, we are creating a new global model of transitioning energy into value—one that optimizes energy consumption while providing real rewards for ecological initiatives.</p>\n<p>BGORIUM’s vision is to ensure that people experience the digital value of energy in their everyday lives. Through blockchain technology, we are building a system that is immutable, transparent, and highly secure. Our mission also includes modernizing global payment systems and providing users with safe, fast, and innovative payment solutions through BLACK GOLD cards. By merging energy value with financial systems, our goal is to establish the world’s first energy–ecology–fintech bridge. BGORIUM’s long-term ambition is to create a digital model that reduces carbon emissions, expands ecological activities, and strengthens global sustainability. We see technology not merely as a tool for generating profit, but as a powerful medium for amplifying social and environmental impact. As a company, we strive to position Azerbaijan as a regional leader in innovation by building the world’s first energy–ecology digital platform. Our mission is to inspire youth, entrepreneurs, and businesses to build an environmentally responsible digital economy. BGORIUM also aims to play a leading role in developing new digital standards for the energy sector. By collaborating with both the public and private sectors, we seek to shape the future of the digital energy economy. Ultimately, the company’s mission is to create a new global system in which digital assets are integrated into the real economy and where users participate transparently and securely. BGORIUM LLC works tirelessly to become the first ecosystem to prove that creating value from energy on a global scale is not only possible but transformative—combining traditional energy with digital innovation to create long-lasting and sustainable global impact.</p>', '', '', 'Post 1', 'The primary goal of BGORIUM LLC is to create a global ecosystem that unites the fields of energy, ecology, and digital finance. We aim to preserve the real value generated from energy, prevent its loss, and transfer this value into the digital economy, opening new economic opportunities for', 'energy, digital, value, global, economy, mission, create, ecological, BGORIUM, ecosystem, economic, Through, impact, through, technology, model, first, people, ensure, BLACK', '4', 'post-1', 0, 1, 1, 1, 0, 0, '', '', '', 'Post 1', 'post-1', '<p>BGORIUM MMC-nin əsas məqsədi enerji, ekologiya və rəqəmsal maliyyə sahələrini birləşdirən qlobal ekosistem yaratmaqdır. Biz enerjidən yaranan real dəyəri qorumağı, onun itirilməsinin qarşısını almağı və bu dəyəri rəqəmsal iqtisadiyyata köçürməyi hədəfləyirik, bununla da milyonlarla insan üçün yeni iqtisadi imkanlar açırıq. Missiyamız təbii resursların dəyərinin yalnız fiziki bazarlarda tanınmasını deyil, həmçinin ölçülməsini, izlənməsini və istifadəçiyə fayda verən rəqəmsal dəyərə çevrilməsini təmin etməkdir.</p>\n<p>BLACK GOLD ekosistemi vasitəsilə biz enerji dəyərinin rəqəmsallaşdırılmasını, ekoloji təşviqlərin ölçülməsini və rəqəmsal aktivlər vasitəsilə real iqtisadi təsirin artırılmasını mümkün edirik. Biz blokçeyn texnologiyası vasitəsilə enerji istehsalı və istehlakı nəticəsində yaranan məlumatların şəffaf idarə olunmasını əsas hədəf kimi görürük.</p>\n<p>Şirkət olaraq, fərdləri, biznesləri və dövlət qurumlarını enerji dəyəri ilə idarə olunan rəqəmsal iqtisadiyyata qoşulmağa təşviq edirik. Missiyamız ekoloji fəaliyyətləri yalnız sosial məsuliyyət kimi deyil, ölçülə bilən iqtisadi fayda verən real təşviq modelinə çevirməkdir. OILCOIN, GREEN BGC və BGC token axını vasitəsilə enerjini dəyərə çevirməyin yeni qlobal modelini yaradırıq—bu model enerji istehlakını optimallaşdırır və ekoloji təşəbbüslər üçün real mükafat təmin edir.</p>\n<p>BGORIUM-un vizyonu insanların gündəlik həyatlarında enerji dəyərinin rəqəmsal təcrübəsini yaşamasını təmin etməkdir. Blokçeyn texnologiyası vasitəsilə dəyişməz, şəffaf və yüksək təhlükəsiz sistem qururuq. Missiyamız həmçinin qlobal ödəniş sistemlərini modernləşdirmək və istifadəçilərə BLACK GOLD kartları vasitəsilə təhlükəsiz, sürətli və innovativ ödəniş həlləri təqdim etməkdir.</p>\n<p>Enerji dəyərini maliyyə sistemləri ilə birləşdirərək məqsədimiz dünyanın ilk enerji–ekologiya–fintech körpüsünü yaratmaqdır. BGORIUM-un uzunmüddətli ambisiyası karbon emissiyalarını azaltmaq, ekoloji fəaliyyətləri genişləndirmək və qlobal davamlılığı gücləndirən rəqəmsal model yaratmaqdır. Biz texnologiyanı yalnız mənfəət əldə etmək aləti kimi deyil, həm də sosial və ekoloji təsiri gücləndirən güclü vasitə kimi görürük.</p>\n<p>Şirkət olaraq, Azərbaycanı regionda innovasiyada lider mövqeyə çıxarmağı və dünyanın ilk enerji–ekologiya rəqəmsal platformasını qurmağı hədəfləyirik. Missiyamız gəncləri, sahibkarları və biznesləri ekoloji məsuliyyətli rəqəmsal iqtisadiyyat yaratmağa ilhamlandırmaqdır. BGORIUM həmçinin enerji sektoru üçün yeni rəqəmsal standartların inkişafında aparıcı rol oynamağı planlaşdırır.</p>\n<p>Həm ictimai, həm də özəl sektorlarla əməkdaşlıq edərək rəqəmsal enerji iqtisadiyyatının gələcəyini formalaşdırmağı hədəfləyirik. Nəticədə, şirkətin missiyası rəqəmsal aktivlərin real iqtisadiyyata inteqrasiya olunduğu və istifadəçilərin şəffaf və təhlükəsiz şəkildə iştirak etdiyi yeni qlobal sistem yaratmaqdır. BGORIUM MMC qlobal miqyasda enerjidən dəyər yaratmağın mümkün olduğunu və bunu dəyişdirici şəkildə həyata keçirə biləcəyini sübut edən ilk ekosistem olmağa çalışır—ənənəvi enerji ilə rəqəmsal innovasiyanı birləşdirərək uzunmüddətli və davamlı qlobal təsir yaradır.</p>', '', '', 'BGORIUM MMC-nin əsas məqsədi enerji, ekologiya və rəqəmsal maliyyə sahələrini birləşdirən qlobal ekosistem yaratmaqdır. Biz enerjidən yaranan real dəyəri qorumağı, onun itirilməsinin qarşısını almağı', 'rəqəmsal, enerji, qlobal, vasitəsilə, ekoloji, BGORIUM, yaratmaqdır, Missiyamız, dəyəri, iqtisadiyyata, hədəfləyirik, iqtisadi, dəyərinin, yalnız, deyil, həmçinin, təmin, etməkdir, şəffaf, təhlükəsiz', '', 'Пост 1', 'post-1', '<p>Основная цель BGORIUM LLC — создать глобальную экосистему, объединяющую энергетику, экологию и цифровые финансы. Компания стремится сохранить реальную ценность, создаваемую энергетикой, предотвратить её потерю и перенести в цифровую экономику, открывая новые экономические возможности для миллионов людей. Миссия компании заключается в том, чтобы ценность природных ресурсов не только признавалась на физических рынках, но и измерялась, отслеживалась и превращалась в цифровую ценность, приносящую выгоду пользователям.</p>\n<p>Через экосистему BLACK GOLD BGORIUM обеспечивает цифровизацию ценности энергии, измерение экологических стимулов и усиление реального экономического воздействия через цифровые активы. Прозрачное управление данными о производстве и потреблении энергии с использованием технологии блокчейн является ключевой целью компании. BGORIUM привлекает физических лиц, бизнес и государственные учреждения к участию в цифровой экономике, основанной на ценности энергии, превращая экологические действия из социальной ответственности в реальную модель стимулов с измеримыми экономическими выгодами. Через поток токенов OILCOIN → GREEN BGC → BGC создаётся новая глобальная модель преобразования энергии в ценность, оптимизирующая потребление энергии и предоставляющая реальные вознаграждения за экологические инициативы.</p>\n<p>Видение BGORIUM — обеспечить людям возможность ощущать цифровую ценность энергии в повседневной жизни. С помощью блокчейн-технологий создаётся система, которая является неизменяемой, прозрачной и безопасной. Миссия также включает модернизацию глобальных платёжных систем и предоставление пользователям безопасных, быстрых и инновационных платёжных решений через карты BLACK GOLD. Объединяя ценность энергии с финансовыми системами, компания стремится создать первый в мире мост «энергия–экология–финтех».</p>\n<p>Долгосрочные цели BGORIUM включают создание цифровой модели, способствующей сокращению выбросов углерода, расширению экологической деятельности и укреплению глобальной устойчивости. Технология рассматривается не только как инструмент получения прибыли, но и как мощное средство для усиления социального и экологического воздействия. Компания стремится позиционировать Азербайджан как регионального лидера в области инноваций, создавая первую в мире цифровую платформу «энергия–экология». BGORIUM вдохновляет молодёжь, предпринимателей и бизнес на создание экологически ответственной цифровой экономики и играет ведущую роль в разработке новых цифровых стандартов для энергетического сектора.</p>\n<p>Путём сотрудничества с государственным и частным секторами компания формирует будущее цифровой энергетической экономики. В конечном итоге миссия BGORIUM — создать глобальную систему, в которой цифровые активы интегрированы в реальную экономику, а пользователи участвуют в ней прозрачно и безопасно. BGORIUM LLC работает над тем, чтобы стать первой экосистемой, доказавшей, что создание ценности из энергии в глобальном масштабе не только возможно, но и трансформативно, объединяя традиционную энергетику с цифровыми инновациями для создания долговременного и устойчивого глобального эффекта.</p>', '', '', 'Основная цель BGORIUM LLC — создать глобальную экосистему, объединяющую энергетику, экологию и цифровые финансы. Компания стремится сохранить реальную ценность, создаваемую энергетикой, предотвратить', 'BGORIUM, энергии, ценность, цифровую, цифровой, создать, цифровые, стремится, реальную, только, ценности, создание, глобальную, экосистему, энергетику, Компания, экономику, Миссия, компании, чтобы', ''),
(21, 'admin', '2025-11-20 08:57:29', '<p><b>BLACK GOLD</b> is a global-scale blockchain ecosystem that combines energy, ecological impact, and digital finance. This platform converts the real value generated from energy into digital form and offers next-generation economic opportunities to users, companies, and governments. BLACK GOLD is the first energy–ecology–fintech model built on three core token flows — OILCOIN → GREEN BGC → BGC. OILCOIN represents the value generated from energy, GREEN BGC reflects ecological impact, and BGC serves as the main asset of the digital economy. This token system is designed to create value from energy, strengthen ecological incentives, and unite digital payments within a single ecosystem. The primary goal of BLACK GOLD is to prevent the loss of energy value and redirect it into real economic circulation. The ecosystem creates digital transparency and ensures that data on energy production and consumption is stored immutably on the blockchain. BLACK GOLD also assigns real economic value to environmental activities, creating motivation for both individuals and companies. This system allows users to track and assess their ecological impact and receive digital value in return. The vision of BLACK GOLD is to build the world’s largest digital value ecosystem in the fields of energy and ecology. The platform aims to deliver digital financial services to the masses through BLACK GOLD cards integrated with global payment systems. Through BLACK GOLD cards (Classic, World, Premium), users will be able to spend digital assets generated from energy value in real-world payments. Premium cards will offer extended premium features such as GPS tracking, cashback, and exclusive services.</p>\n<p>BLACK GOLD also creates additional value for users by providing AI-based energy analytics, oil price forecasts, and environmental monitoring systems. The long-term goal of the platform is to support the reduction of carbon emissions and to stimulate ecological initiatives on a global scale. BLACK GOLD plans to build strategic partnerships with government institutions, banks, energy companies, and environmental organizations. One of the major objectives of the ecosystem is to create an international standard that integrates energy and ecological value into the digital economy. In future stages, products such as a digital marketplace for energy trading, a carbon credits ecosystem, and AI-based environmental impact monitoring tools will also be launched. The platform offers broad applicability for both developed and developing countries and aims to lead the digital transformation of the global energy sector. BLACK GOLD is also designed to be the first energy–ecology digital ecosystem that starts from Azerbaijan and expands to the global market. This project aims to showcase the country’s innovation potential to the world and become the strongest fintech–energy project in the region. In conclusion, BLACK GOLD is not just a digital token project, but a new global economic model that shapes the future of energy and ecology.</p>', '', '', 'Black gold', 'BLACK GOLD is a global-scale blockchain ecosystem that combines energy, ecological impact, and digital finance. This platform converts the real value generated from energy into digital form and offers next-generation economic opportunities to users, companies, and governments. BLACK GOLD is the', 'energy, digital, BLACK, value, ecosystem, ecological, global, impact, platform, economic, users, environmental, generated, companies, token, cards, project, blockchain, offers, first', '5', 'black-gold', 0, 1, 1, 1, 0, 0, '', '', '', 'Black gold', 'black-gold', '<p>BLACK GOLD enerji, ekoloji təsir və rəqəmsal maliyyəni birləşdirən qlobal miqyaslı blokçeyn ekosistemidir. Bu platforma enerjidən yaranan real dəyəri rəqəmsal formaya çevirir və istifadəçilərə, şirkətlərə və hökumətlərə növbəti nəsil iqtisadi imkanlar təqdim edir.</p>\n<p>BLACK GOLD üç əsas token axını əsasında qurulmuş ilk enerji–ekologiya–fintech modelidir — <strong>OILCOIN → GREEN BGC → BGC</strong>. OILCOIN enerji ilə yaranan dəyəri təmsil edir, GREEN BGC ekoloji təsiri göstərir və BGC rəqəmsal iqtisadiyyatın əsas aktividir. Bu token sistemi enerjidən dəyər yaratmaq, ekoloji təşviqləri gücləndirmək və rəqəmsal ödənişləri tək ekosistem daxilində birləşdirmək üçün hazırlanmışdır. BLACK GOLD-un əsas məqsədi enerji dəyərinin itirilməsinin qarşısını almaq və onu real iqtisadi dövriyyəyə yönəltməkdir.</p>\n<p>Ekosistem rəqəmsal şəffaflıq yaradır və enerji istehsalı və istehlakı barədə məlumatların blokçeyndə dəyişməz şəkildə saxlanmasını təmin edir. BLACK GOLD həmçinin ekoloji fəaliyyətlərə real iqtisadi dəyər verir, bununla da fərdlər və şirkətlər üçün motivasiya yaradır. Bu sistem istifadəçilərə öz ekoloji təsirini izləmək və qiymətləndirmək, həmçinin rəqəmsal dəyər əldə etmək imkanı verir.</p>\n<p>BLACK GOLD-un vizyonu enerji və ekologiya sahələrində dünyanın ən böyük rəqəmsal dəyər ekosistemini yaratmaqdır. Platforma qlobal ödəniş sistemlərinə inteqrasiya olunmuş BLACK GOLD kartları vasitəsilə kütləyə rəqəmsal maliyyə xidmətləri təqdim etməyi hədəfləyir. BLACK GOLD kartları (Classic, World, Premium) istifadəçilərə enerjidən yaranan rəqəmsal aktivləri real ödənişlərdə xərcləmək imkanı verəcək. Premium kartlar GPS izləmə, cashback və eksklüziv xidmətlər kimi genişləndirilmiş üstünlüklər təqdim edəcək.</p>\n<p>BLACK GOLD istifadəçilər üçün əlavə dəyər yaradaraq AI əsaslı enerji analitikası, neft qiymətləri proqnozları və ekoloji monitorinq sistemləri təmin edir. Platformanın uzunmüddətli məqsədi karbon emissiyalarının azaldılmasını dəstəkləmək və qlobal miqyasda ekoloji təşəbbüsləri stimullaşdırmaqdır. BLACK GOLD hökumət qurumları, banklar, enerji şirkətləri və ekoloji təşkilatlarla strateji tərəfdaşlıqlar qurmağı planlaşdırır.</p>\n<p>Ekosistemin əsas məqsədlərindən biri enerji və ekoloji dəyəri rəqəmsal iqtisadiyyatla birləşdirən beynəlxalq standart yaratmaqdır. Gələcək mərhələlərdə enerji ticarəti üçün rəqəmsal bazar, karbon kreditləri ekosistemi və AI əsaslı ekoloji təsir monitorinqi kimi məhsullar da istifadəyə veriləcək. Platforma həm inkişaf etmiş, həm də inkişaf etməkdə olan ölkələr üçün geniş tətbiq imkanları təqdim edir və qlobal enerji sektorunun rəqəmsal transformasiyasına liderlik etməyi hədəfləyir.</p>\n<p>BLACK GOLD həmçinin Azərbaycan-dan başlayan və qlobal bazara yayılan ilk enerji–ekologiya rəqəmsal ekosistemi kimi dizayn edilib. Bu layihə ölkənin innovasiya potensialını dünyaya nümayiş etdirməyi və regionda ən güclü fintech–enerji layihəsinə çevrilməyi hədəfləyir.</p>\n<p>Nəticə etibarilə, BLACK GOLD yalnız rəqəmsal token layihəsi deyil, enerji və ekologiyanın gələcəyini formalaşdıran yeni qlobal iqtisadi modeldir.</p>', '', '', 'BLACK GOLD enerji, ekoloji təsir və rəqəmsal maliyyəni birləşdirən qlobal miqyaslı blokçeyn ekosistemidir. Bu platforma enerjidən yaranan real dəyəri rəqəmsal formaya çevirir və istifadəçilərə,', 'rəqəmsal, BLACK, enerji, ekoloji, qlobal, dəyər, iqtisadi, təqdim, enerjidən, yaranan, dəyəri, istifadəçilərə, token, həmçinin, hədəfləyir, təsir, birləşdirən, OILCOIN, GREEN, məqsədi', '', 'Black gold', 'black-gold', '<p><b>BLACK GOLD</b> — глобальная блокчейн-экосистема, объединяющая энергетику, экологическое воздействие и цифровые финансы. Платформа конвертирует реальную ценность, созданную энергетикой, в цифровую форму и предлагает экономические возможности для пользователей, компаний и государственных органов. BLACK GOLD построен на трёх токенах — OILCOIN → GREEN BGC → BGC. OILCOIN отражает стоимость, генерируемую энергией, GREEN BGC показывает экологическое влияние, а BGC является основным активом цифровой экономики.</p>\n<p>Система токенов создаёт ценность из энергии, усиливает экологические стимулы и объединяет цифровые платежи в единой экосистеме. Главная цель BLACK GOLD — предотвратить потерю стоимости энергии и направить её в экономический оборот. Данные о производстве и потреблении энергии хранятся на блокчейне в неизменяемой форме.</p>\n<p>BLACK GOLD присваивает экономическую ценность экологическим действиям, позволяя пользователям отслеживать и оценивать своё экологическое воздействие и получать цифровую ценность взамен. Платформа стремится создать крупнейшую в мире цифровую экосистему ценности в сферах энергетики и экологии.</p>\n<p>Пользователи смогут использовать карты BLACK GOLD (Classic, World, Premium) для трат цифровых активов, созданных из энергии. Премиальные карты предлагают функции GPS-трекинга, кэшбэк и эксклюзивные сервисы.</p>\n<p>BLACK GOLD предоставляет AI-аналитику энергии, прогнозы цен на нефть и системы мониторинга окружающей среды. Платформа планирует сотрудничество с государственными учреждениями, банками, энергетическими компаниями и экологическими организациями. Цель — создать международный стандарт, интегрирующий энергетическую и экологическую ценность в цифровую экономику.</p>\n<p>В будущем планируется запуск цифрового рынка для торговли энергией, экосистемы углеродных кредитов и инструментов мониторинга экологического воздействия на базе ИИ. Платформа предназначена для применения в развитых и развивающихся странах и стартует из Азербайджана с выходом на мировой рынок. BLACK GOLD — первая цифровая экосистема «энергия–экология», которая формирует новую глобальную экономическую модель.</p>', '', '', 'BLACK GOLD — глобальная блокчейн-экосистема, объединяющая энергетику, экологическое воздействие и цифровые финансы. Платформа конвертирует реальную ценность, созданную энергетикой, в цифровую форму и', 'BLACK, ценность, энергии, Платформа, цифровую, экологическое, экосистема, воздействие, цифровые, OILCOIN, GREEN, энергией, экономическую, создать, карты, мониторинга, глобальная, блокчейн, объединяющая, энергетику', ''),
(22, 'admin', '2025-12-02 07:04:41', '<p><a href=\"https://bgorium.com/uploads/posts/2025-12/dalle-2025-12-02-16_07_21-a-high-detail-3d-golden-coin-with-the-letters-bgc-engraved-glowing-energy-rings-behind-it-black-and-gold-color-palette-futuristic-blockchain-backgr.webp\" class=\"highslide\"><img src=\\\"https://bgorium.com/uploads/posts/2025-12/medium/dalle-2025-12-02-16_07_21-a-high-detail-3d-golden-coin-with-the-letters-bgc-engraved-glowing-energy-rings-behind-it-black-and-gold-color-palette-futuristic-blockchain-backgr.webp\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\" alt=\\\"\\\"></a></p>', '<p>BlackGold Coin (BGC) is a next-generation digital asset designed to bring stability, transparency, and real-world value into the blockchain space. Unlike purely speculative cryptocurrencies, BGC is backed by tangible physical resources—specifically energy-related commodities such as oil and renewable-energy infrastructure.</p>\n<p>This asset-backed approach reduces volatility and builds investor confidence. With the BGORIUM ecosystem, BGC holders gain access to staking rewards, real-world partnerships, fintech solutions, and a secure investment model protected by modern blockchain technology.</p>\n<p>BlackGold Coin represents a bridge between traditional commodity finance and decentralized digital wealth, making it a strong choice for investors seeking both innovation and asset security.</p>\n<p><br></p>', '', 'What Is BlackGold Coin? The Complete Beginner’s Guide', 'BlackGold Coin (BGC) is a next-generation digital asset designed to bring stability, transparency, and real-world value into the blockchain space. Unlike purely speculative cryptocurrencies, BGC is backed by tangible physical resources—specifically energy-related commodities such as oil and', 'BlackGold, digital, asset, realworld, blockchain, (BGC), nextgeneration, designed, bring, stability, transparency, value, space, Unlike, purely, speculative, cryptocurrencies, backed, tangible, physical', '11', 'what-is-blackgold-coin-the-complete-beginners-guide', 0, 1, 1, 1, 0, 0, '', '', '', 'BlackGold Coin Nədir? Başlayanlar Üçün Tam Bələdçi', 'blackgold-coin-ndir-balayanlar-ucun-tam-bldci', '', '<p>BlackGold Coin (BGC) sabitlik, şəffaflıq və real dünya dəyərini blokçeyn sahəsinə gətirmək üçün nəzərdə tutulmuş növbəti nəsil rəqəmsal aktivdir. Tamamilə spekulyativ kriptovalyutalardan fərqli olaraq, BGC maddi fiziki resurslarla—xüsusilə enerji ilə bağlı əmtəələr, məsələn, neft və bərpa olunan enerji infrastrukturu ilə dəstəklənir.</p>\n<p>Bu aktivlərlə dəstəklənən yanaşma dəyişkənliyi azaldır və investorların inamını artırır. BGORIUM ekosistemi ilə BGC sahibləri staking mükafatlarına, real dünya tərəfdaşlıqlarına, fintech həllərinə və müasir blokçeyn texnologiyası ilə qorunan təhlükəsiz investisiya modelinə çıxış əldə edirlər.</p>\n<p>BlackGold Coin ənənəvi əmtəə maliyyəsi ilə mərkəzləşdirilməmiş rəqəmsal sərvət arasında körpü rolunu oynayır və həm innovasiya, həm də aktiv təhlükəsizliyi axtaran investorlar üçün güclü seçimdir.</p>', '', 'BlackGold Coin (BGC) sabitlik, şəffaflıq və real dünya dəyərini blokçeyn sahəsinə gətirmək üçün nəzərdə tutulmuş növbəti nəsil rəqəmsal aktivdir. Tamamilə spekulyativ kriptovalyutalardan fərqli', 'BlackGold, dünya, blokçeyn, rəqəmsal, enerji, sabitlik, şəffaflıq, dəyərini, sahəsinə, gətirmək, nəzərdə, tutulmuş, növbəti, nəsil, aktivdir, Tamamilə, spekulyativ, kriptovalyutalardan, fərqli, olaraq', '', 'Что такое BlackGold Coin? Полное руководство для начинающих', 'chto-takoe-blackgold-coin-polnoe-rukovodstvo-dlya-nachinayuschih', '', '<p>BlackGold Coin (BGC) — это цифровой актив нового поколения, созданный для того, чтобы привнести стабильность, прозрачность и реальную ценность в блокчейн-пространство. В отличие от чисто спекулятивных криптовалют, BGC обеспечен материальными физическими ресурсами — в частности, энергетическими товарами, такими как нефть и инфраструктура возобновляемой энергии.</p>\n<p>Такой подход с обеспечением активами снижает волатильность и укрепляет доверие инвесторов. В экосистеме BGORIUM держатели BGC получают доступ к вознаграждениям за стейкинг, реальным партнерствам, финтех-решениям и безопасной инвестиционной модели, защищённой современными технологиями блокчейна.</p>\n<p>BlackGold Coin представляет собой мост между традиционными финансовыми рынками сырья и децентрализованным цифровым богатством, делая его отличным выбором для инвесторов, ищущих как инновации, так и безопасность активов.</p>', '', 'BlackGold Coin (BGC) — это цифровой актив нового поколения, созданный для того, чтобы привнести стабильность, прозрачность и реальную ценность в блокчейн-пространство. В отличие от чисто', 'BlackGold, инвесторов, цифровой, актив, нового, поколения, созданный, чтобы, привнести, стабильность, прозрачность, реальную, ценность, блокчейн, пространство, отличие, чисто, спекулятивных, криптовалют, обеспечен', ''),
(23, 'admin', '2025-12-02 07:05:02', '<p><a href=\"https://bgorium.com/uploads/posts/2025-12/dalle-2025-12-02-16_08_29-a-balanced-scale-with-a-crypto-coin-on-one-side-and-barrels-of-oil-plus-renewable-energy-icons-on-the-other-symbolizing-asset-backed-stability_-elega.webp\" class=\"highslide\"><img src=\\\"https://bgorium.com/uploads/posts/2025-12/medium/dalle-2025-12-02-16_08_29-a-balanced-scale-with-a-crypto-coin-on-one-side-and-barrels-of-oil-plus-renewable-energy-icons-on-the-other-symbolizing-asset-backed-stability_-elega.webp\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\" alt=\\\"\\\"></a></p>', '<p>The crypto industry has matured. Investors no longer chase hype—they seek stability, security, and long-term value. This is where asset-backed cryptocurrencies, especially BlackGold Coin, stand out.</p>\n<p>Traditional crypto tokens can fall dramatically during market downturns. In contrast, BGC is tied to real physical assets whose value does not vanish overnight. Asset-backed digital assets combine two worlds:</p>\n<ul>\n<li>\n<p><strong>Stability of commodities</strong></p>\n</li>\n<li>\n<p><strong>Speed and transparency of blockchain</strong></p>\n</li>\n<li>\n<p><strong>Long-term investment security</strong></p>\n</li>\n</ul>\n<p>As global finance moves toward tokenization, commodity-backed cryptocurrencies are expected to dominate the next decade. BlackGold Coin is positioned at the center of this revolution.</p>', '', 'Why Asset-Backed Cryptocurrencies Are the Future of Digital Finance', 'The crypto industry has matured. Investors no longer chase hype—they seek stability, security, and long-term value. This is where asset-backed cryptocurrencies, especially BlackGold Coin, stand out. Traditional crypto tokens can fall dramatically during market downturns. In contrast, BGC is tied to', 'crypto, security, value, cryptocurrencies, BlackGold, assets, industry, matured, Investors, longer, chase, hype—they, stability, longterm, where, assetbacked, especially, stand, Traditional, tokens', '11', 'why-asset-backed-cryptocurrencies-are-the-future-of-digital-finance', 0, 1, 1, 1, 0, 0, '', '', '', 'Aktivlərlə Dəstəklənən Kriptovalyutalar Niyə Rəqəmsal Maliyyənin Gələcəyidi', 'aktivlrl-dstklnn-kriptovalyutalar-niy-rqmsal-maliyynin-glcyidi', '', '<p>Kripto sənayesi yetkinləşib. İnvestorlar artıq həyəcan arxasınca qaçmırlar—they sabitlik, təhlükəsizlik və uzunmüddətli dəyər axtarırlar. Burada aktivlərlə dəstəklənən kriptovalyutalar, xüsusilə BlackGold Coin fərqlənir.</p>\n<p>Ənənəvi kripto tokenlər bazar enişləri zamanı dramatik şəkildə düşə bilər. Əksinə, BGC real fiziki aktivlərə bağlanıb və onların dəyəri bir gecədə itmir. Aktivlərlə dəstəklənən rəqəmsal aktivlər iki dünyanı birləşdirir:</p>\n<ul>\n<li>\n<p>Əmtəələrin sabitliyi</p>\n</li>\n<li>\n<p>Blokçeynin sürəti və şəffaflığı</p>\n</li>\n<li>\n<p>Uzunmüddətli investisiya təhlükəsizliyi</p>\n</li>\n</ul>\n<p>Qlobal maliyyə tokenləşdirməyə doğru irəlilədikcə, əmtəə ilə dəstəklənən kriptovalyutaların növbəti onillikdə üstünlük təşkil etməsi gözlənilir. BlackGold Coin bu inqilabın mərkəzində yerləşir.</p>', '', 'Kripto sənayesi yetkinləşib. İnvestorlar artıq həyəcan arxasınca qaçmırlar—they sabitlik, təhlükəsizlik və uzunmüddətli dəyər axtarırlar. Burada aktivlərlə dəstəklənən kriptovalyutalar, xüsusilə', 'dəstəklənən, BlackGold, Kripto, sənayesi, yetkinləşib, İnvestorlar, artıq, həyəcan, arxasınca, qaçmırlar—they, sabitlik, təhlükəsizlik, uzunmüddətli, dəyər, axtarırlar, Burada, aktivlərlə, kriptovalyutalar, xüsusilə, fərqlənir', '', 'Почему криптовалюты с обеспечением активами — будущее цифровых финансов', 'pochemu-kriptovalyuty-s-obespecheniem-aktivami-buduschee-cifrovyh-finansov', '', '<p>Криптоиндустрия достигла зрелости. Инвесторы больше не гонятся за хайпом — они ищут стабильность, безопасность и долгосрочную ценность. В этом особенно выделяются криптовалюты с обеспечением активами, такие как BlackGold Coin.</p>\n<p>Традиционные криптотокены могут резко падать во время рыночных спадов. В отличие от них, BGC привязан к реальным физическим активам, чья стоимость не исчезает за ночь. Цифровые активы с обеспечением объединяют два мира:</p>\n<ul>\n<li>\n<p>Стабильность сырьевых товаров</p>\n</li>\n<li>\n<p>Скорость и прозрачность блокчейна</p>\n</li>\n<li>\n<p>Долгосрочная инвестиционная надежность</p>\n</li>\n</ul>\n<p>По мере того как мировые финансы движутся к токенизации, ожидается, что криптовалюты, обеспеченные товарами, будут доминировать в следующем десятилетии. BlackGold Coin занимает центральное место в этой революции.</p>', '', 'Криптоиндустрия достигла зрелости. Инвесторы больше не гонятся за хайпом — они ищут стабильность, безопасность и долгосрочную ценность. В этом особенно выделяются криптовалюты с обеспечением', 'криптовалюты, обеспечением, BlackGold, Криптоиндустрия, достигла, зрелости, Инвесторы, больше, гонятся, хайпом, стабильность, безопасность, долгосрочную, ценность, особенно, выделяются, активами, такие, Традиционные, криптотокены', ''),
(24, 'admin', '2025-12-02 07:05:27', '<p><a href=\"https://bgorium.com/uploads/posts/2025-12/dalle-2025-12-02-16_09_16-a-glowing-digital-vault-with-blackgold-coins-entering-and-multiplying-representing-passive-income_-a-financial-growth-chart-in-the-background-black.webp\" class=\"highslide\"><img src=\\\"https://bgorium.com/uploads/posts/2025-12/medium/dalle-2025-12-02-16_09_16-a-glowing-digital-vault-with-blackgold-coins-entering-and-multiplying-representing-passive-income_-a-financial-growth-chart-in-the-background-black.webp\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\" alt=\\\"\\\"></a></p>', '<p>Staking has become one of the most attractive passive-income opportunities in crypto. BlackGold Coin offers a secure, transparent staking system where users earn rewards simply by holding and locking their tokens.</p>\n<p>Benefits of BGC staking include:</p>\n<ul>\n<li>\n<p><strong>Stable annual returns (2%–6%)</strong></p>\n</li>\n<li>\n<p><strong>Low risk due to asset-backed value</strong></p>\n</li>\n<li>\n<p><strong>Daily reward distribution</strong></p>\n</li>\n<li>\n<p><strong>High liquidity after staking period</strong></p>\n</li>\n</ul>\n<p>The BGORIUM staking model is designed for long-term sustainability rather than short-term hype. By participating, investors not only earn passive income but also strengthen the ecosystem.</p>', '', 'How BlackGold Coin Generates Passive Income Through Staking', 'Staking has become one of the most attractive passive-income opportunities in crypto. BlackGold Coin offers a secure, transparent staking system where users earn rewards simply by holding and locking their tokens. Benefits of BGC staking include: Stable annual returns (2%–6%) Low risk due to', 'staking, Staking, become, attractive, passiveincome, opportunities, crypto, BlackGold, offers, secure, transparent, system, where, users, rewards, simply, holding, locking, their, tokens', '11', 'how-blackgold-coin-generates-passive-income-through-staking', 0, 1, 1, 1, 0, 0, '', '', '', 'BlackGold Coin Staking vasitəsilə Passiv Gəlir Necə Yaradılır', 'blackgold-coin-staking-vasitsil-passiv-glir-nec-yaradlr', '', '<p>Staking kriptovalyutada ən cəlbedici passiv gəlir imkanlarından birinə çevrilib. BlackGold Coin istifadəçilərə tokenlərini saxlamaq və kilidləmək yolu ilə mükafat qazandıqları təhlükəsiz və şəffaf staking sistemi təqdim edir.</p>\n<p>BGC staking-in üstünlükləri:</p>\n<ul>\n<li>\n<p>Sabit illik gəlir (2%–6%)</p>\n</li>\n<li>\n<p>Aktivlərlə dəstəkləndiyi üçün aşağı risk</p>\n</li>\n<li>\n<p>Gündəlik mükafat paylanması</p>\n</li>\n<li>\n<p>Staking dövründən sonra yüksək likvidlik</p>\n</li>\n</ul>\n<p>BGORIUM-un staking modeli qısa müddətli həyəcdən çox uzunmüddətli davamlılıq üçün nəzərdə tutulub. İştirak etməklə, investorlar yalnız passiv gəlir qazanmaqla kifayətlənmirlər, eyni zamanda ekosistemi gücləndirirlər.</p>', '', 'Staking kriptovalyutada ən cəlbedici passiv gəlir imkanlarından birinə çevrilib. BlackGold Coin istifadəçilərə tokenlərini saxlamaq və kilidləmək yolu ilə mükafat qazandıqları təhlükəsiz və şəffaf', 'gəlir, staking, passiv, mükafat, Staking, kriptovalyutada, cəlbedici, imkanlarından, birinə, çevrilib, BlackGold, istifadəçilərə, tokenlərini, saxlamaq, kilidləmək, qazandıqları, təhlükəsiz, şəffaf, sistemi, təqdim', '', 'Как BlackGold Coin приносит пассивный доход через стейкинг', 'kak-blackgold-coin-prinosit-passivnyy-dohod-cherez-steyking', '', '<p>Стейкинг стал одной из самых привлекательных возможностей пассивного дохода в криптовалюте. BlackGold Coin предлагает безопасную и прозрачную систему стейкинга, где пользователи зарабатывают вознаграждения просто за хранение и блокировку своих токенов.</p>\n<p>Преимущества стейкинга BGC:</p>\n<ul>\n<li>\n<p>Стабильная годовая доходность (2%–6%)</p>\n</li>\n<li>\n<p>Низкий риск благодаря обеспечению активами</p>\n</li>\n<li>\n<p>Ежедневное распределение вознаграждений</p>\n</li>\n<li>\n<p>Высокая ликвидность после периода стейкинга</p>\n</li>\n</ul>\n<p>Модель стейкинга BGORIUM рассчитана на долгосрочную устойчивость, а не на краткосрочный хайп. Участвуя, инвесторы не только получают пассивный доход, но и укрепляют экосистему.</p>', '', 'Стейкинг стал одной из самых привлекательных возможностей пассивного дохода в криптовалюте. BlackGold Coin предлагает безопасную и прозрачную систему стейкинга, где пользователи зарабатывают', 'стейкинга, Стейкинг, одной, самых, привлекательных, возможностей, пассивного, дохода, криптовалюте, BlackGold, предлагает, безопасную, прозрачную, систему, пользователи, зарабатывают, вознаграждения, просто, хранение, блокировку', ''),
(25, 'admin', '2025-12-02 07:05:51', '<p><a href=\"https://bgorium.com/uploads/posts/2025-12/dalle-2025-12-02-16_09_51-a-futuristic-smart-city-skyline-connected-by-glowing-blockchain-lines-with-a-blackgold-coin-symbol-floating-above-the-city_-clean-fintech-aesthetic.webp\" class=\"highslide\"><img src=\\\"https://bgorium.com/uploads/posts/2025-12/medium/dalle-2025-12-02-16_09_51-a-futuristic-smart-city-skyline-connected-by-glowing-blockchain-lines-with-a-blackgold-coin-symbol-floating-above-the-city_-clean-fintech-aesthetic.webp\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\" alt=\\\"\\\"></a></p>', '<p>BGORIUM is built on the belief that future financial systems must combine decentralization, real-world value, and advanced fintech tools. The platform supports secure digital payments, asset management, staking, and a global ecosystem for commodity-based digital assets.</p>\n<p>BlackGold Coin is the flagship token within this system—representing a reliable and transparent financial instrument backed by physical value.</p>\n<p>BGORIUM’s mission includes:</p>\n<ul>\n<li>\n<p>Making commodity investment accessible worldwide</p>\n</li>\n<li>\n<p>Reducing volatility in digital markets</p>\n</li>\n<li>\n<p>Offering transparent financial tools</p>\n</li>\n<li>\n<p>Providing security through blockchain and real assets</p>\n</li>\n</ul>\n<p>With its unique combination of real economy + blockchain innovation, BGORIUM is becoming a leader in next-generation fintech.</p>', '', 'The Vision Behind BGORIUM: A New Standard in Fintech', 'BGORIUM is built on the belief that future financial systems must combine decentralization, real-world value, and advanced fintech tools. The platform supports secure digital payments, asset management, staking, and a global ecosystem for commodity-based digital assets. BlackGold Coin is the', 'financial, digital, BGORIUM, value, fintech, tools, assets, transparent, blockchain, built, belief, future, systems, combine, decentralization, realworld, advanced, platform, supports, secure', '11', 'the-vision-behind-bgorium-a-new-standard-in-fintech', 0, 1, 1, 1, 0, 0, '', '', '', 'BGORIUM-un Vizyonu: Fintech-də Yeni Standart', 'bgorium-un-vizyonu-fintech-d-yeni-standart', '', '<p>BGORIUM gələcək maliyyə sistemlərinin mərkəzləşdirilməmiş yanaşma, real dünya dəyəri və qabaqcıl fintech alətlərini birləşdirməli olduğuna inam əsasında qurulub. Platforma təhlükəsiz rəqəmsal ödənişləri, aktiv idarəsini, staking-i və əmtəəyə əsaslanan rəqəmsal aktivlər üçün qlobal ekosistemi dəstəkləyir.</p>\n<p>BlackGold Coin bu sistemin əsas tokenidir—fiziki dəyərlə dəstəklənən etibarlı və şəffaf maliyyə alətini təmsil edir.</p>\n<p>BGORIUM-un missiyası bunları əhatə edir:</p>\n<ul>\n<li>\n<p>Əmtəə investisiyasını dünya üzrə əlçatan etmək</p>\n</li>\n<li>\n<p>Rəqəmsal bazarlarda dəyişkənliyi azaltmaq</p>\n</li>\n<li>\n<p>Şəffaf maliyyə alətləri təqdim etmək</p>\n</li>\n<li>\n<p>Blokçeyn və real aktivlər vasitəsilə təhlükəsizlik təmin etmək</p>\n</li>\n</ul>\n<p>Real iqtisadiyyat və blokçeyn innovasiyasının unikallığı ilə BGORIUM növbəti nəsil fintech-də liderliyini qoruyur.</p>', '', 'BGORIUM gələcək maliyyə sistemlərinin mərkəzləşdirilməmiş yanaşma, real dünya dəyəri və qabaqcıl fintech alətlərini birləşdirməli olduğuna inam əsasında qurulub. Platforma təhlükəsiz rəqəmsal', 'BGORIUM, maliyyə, dünya, fintech, rəqəmsal, aktivlər, gələcək, sistemlərinin, mərkəzləşdirilməmiş, yanaşma, dəyəri, qabaqcıl, alətlərini, birləşdirməli, olduğuna, əsasında, qurulub, Platforma, təhlükəsiz, ödənişləri', '', 'Видение BGORIUM: новый стандарт в финтехе', 'videnie-bgorium-novyy-standart-v-fintehe', '', '<p>BGORIUM создан на основе убеждения, что будущие финансовые системы должны сочетать децентрализацию, реальную ценность и передовые финтех-инструменты. Платформа поддерживает безопасные цифровые платежи, управление активами, стейкинг и глобальную экосистему цифровых активов, обеспеченных товарами.</p>\n<p>BlackGold Coin является флагманским токеном этой системы — представляя надежный и прозрачный финансовый инструмент, обеспеченный реальной стоимостью.</p>\n<p>Миссия BGORIUM включает:</p>\n<ul>\n<li>\n<p>Обеспечение глобального доступа к инвестициям в товары</p>\n</li>\n<li>\n<p>Снижение волатильности на цифровых рынках</p>\n</li>\n<li>\n<p>Предоставление прозрачных финансовых инструментов</p>\n</li>\n<li>\n<p>Обеспечение безопасности с помощью блокчейна и реальных активов</p>\n</li>\n</ul>\n<p>Благодаря уникальному сочетанию реальной экономики и блокчейн-инноваций, BGORIUM становится лидером финтеха нового поколения.</p>', '', 'BGORIUM создан на основе убеждения, что будущие финансовые системы должны сочетать децентрализацию, реальную ценность и передовые финтех-инструменты. Платформа поддерживает безопасные цифровые', 'BGORIUM, системы, цифровых, реальной, создан, основе, убеждения, будущие, финансовые, должны, сочетать, децентрализацию, реальную, ценность, передовые, финтех, инструменты, Платформа, поддерживает, безопасные', ''),
(26, 'admin', '2025-12-02 07:06:09', '<p><a href=\"https://bgorium.com/uploads/posts/2025-12/dalle-2025-12-02-16_10_46-oil-barrels-solar-panels-and-gold-bars-transforming-into-glowing-blockchain-tokens_-particle-effects-and-dynamic-motion-black-background-with-gold.webp\" class=\"highslide\"><img src=\\\"https://bgorium.com/uploads/posts/2025-12/medium/dalle-2025-12-02-16_10_46-oil-barrels-solar-panels-and-gold-bars-transforming-into-glowing-blockchain-tokens_-particle-effects-and-dynamic-motion-black-background-with-gold.webp\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\" alt=\\\"\\\"></a></p>', '<p>Tokenization is the process of converting physical assets into digital tokens that can be traded on the blockchain. This innovation allows commodities such as oil, energy infrastructure, and metals to become more accessible and transparent.</p>\n<p>BlackGold Coin uses tokenization to bring real-world assets to the blockchain, offering:</p>\n<ul>\n<li>\n<p><strong>Transparent asset reserves</strong></p>\n</li>\n<li>\n<p><strong>Easy global access to commodity investment</strong></p>\n</li>\n<li>\n<p><strong>Lower entry barriers for investors</strong></p>\n</li>\n<li>\n<p><strong>Reduced manipulation and increased trust</strong></p>\n</li>\n</ul>\n<p>BGC eliminates the complexity traditionally associated with commodity markets, allowing anyone to participate through a secure digital asset.</p>', '', 'Tokenization of Real Assets: How BlackGold Coin Leads the Movement', 'Tokenization is the process of converting physical assets into digital tokens that can be traded on the blockchain. This innovation allows commodities such as oil, energy infrastructure, and metals to become more accessible and transparent. BlackGold Coin uses tokenization to bring real-world', 'assets, digital, blockchain, asset, commodity, Tokenization, process, converting, physical, tokens, traded, innovation, allows, commodities, energy, infrastructure, metals, become, accessible, transparent', '11', 'tokenization-of-real-assets-how-blackgold-coin-leads-the-movement', 0, 1, 1, 1, 0, 0, '', '', '', 'Real Aktivlərin Tokenləşdirilməsi: BlackGold Coin Hərəkətə Necə Liderlik Edir', 'real-aktivlrin-tokenldirilmsi-blackgold-coin-hrkt-nec-liderlik-edir', '', '<p>Tokenləşdirmə fiziki aktivlərin blokçeyn üzərində ticarət edilə bilən rəqəmsal tokenlərə çevrilməsi prosesidir. Bu innovasiya neft, enerji infrastrukturu və metallar kimi əmtəələrin daha əlçatan və şəffaf olmasına imkan verir.</p>\n<p>BlackGold Coin tokenləşdirmədən istifadə edərək real dünya aktivlərini blokçeynə gətirir və aşağıdakı imkanları təqdim edir:</p>\n<ul>\n<li>\n<p>Şəffaf aktiv ehtiyatları</p>\n</li>\n<li>\n<p>Əmtəə investisiyasına qlobal asan çıxış</p>\n</li>\n<li>\n<p>İnvestorlar üçün daha aşağı giriş baryerləri</p>\n</li>\n<li>\n<p>Manipulyasiyaların azalması və etibarın artması</p>\n</li>\n</ul>\n<p>BGC ənənəvi olaraq əmtəə bazarları ilə bağlı mürəkkəbliyi aradan qaldırır və hər kəsin təhlükəsiz rəqəmsal aktiv vasitəsilə iştirak etməsinə imkan yaradır.</p>', '', 'Tokenləşdirmə fiziki aktivlərin blokçeyn üzərində ticarət edilə bilən rəqəmsal tokenlərə çevrilməsi prosesidir. Bu innovasiya neft, enerji infrastrukturu və metallar kimi əmtəələrin daha əlçatan və', 'rəqəmsal, imkan, aktiv, Tokenləşdirmə, fiziki, aktivlərin, blokçeyn, üzərində, ticarət, edilə, bilən, tokenlərə, çevrilməsi, prosesidir, innovasiya, enerji, infrastrukturu, metallar, əmtəələrin, əlçatan', '', 'Токенизация реальных активов: как BlackGold Coin возглавляет движение', 'tokenizaciya-realnyh-aktivov-kak-blackgold-coin-vozglavlyaet-dvizhenie', '', '<article>\n<p>Токенизация — это процесс преобразования физических активов в цифровые токены, которыми можно торговать на блокчейне. Эта инновация делает такие товары, как нефть, энергетическая инфраструктура и металлы, более доступными и прозрачными.</p>\n<p>BlackGold Coin использует токенизацию для переноса реальных активов на блокчейн, предоставляя:</p>\n<ul>\n<li>\n<p>Прозрачные резервы активов</p>\n</li>\n<li>\n<p>Легкий глобальный доступ к инвестициям в товары</p>\n</li>\n<li>\n<p>Сниженные барьеры для входа инвесторов</p>\n</li>\n<li>\n<p>Меньшую возможность манипуляций и повышенное доверие</p>\n</li>\n</ul>\n<p>BGC устраняет сложности, традиционно связанные с рынками сырья, позволяя каждому участвовать через безопасный цифровой актив.</p>\n</article>', '', 'Токенизация — это процесс преобразования физических активов в цифровые токены, которыми можно торговать на блокчейне. Эта инновация делает такие товары, как нефть, энергетическая инфраструктура и', 'активов, Токенизация, процесс, преобразования, физических, цифровые, токены, которыми, можно, торговать, блокчейне, инновация, делает, такие, товары, нефть, энергетическая, инфраструктура, металлы, более', '');
INSERT INTO `dle_post` (`id`, `autor`, `date`, `short_story`, `full_story`, `xfields`, `title`, `descr`, `keywords`, `category`, `alt_name`, `comm_num`, `allow_comm`, `allow_main`, `approve`, `fixed`, `allow_br`, `symbol`, `tags`, `metatitle`, `title_az`, `alt_name_az`, `short_story_az`, `full_story_az`, `metatitle_az`, `descr_az`, `keywords_az`, `tags_az`, `title_ru`, `alt_name_ru`, `short_story_ru`, `full_story_ru`, `metatitle_ru`, `descr_ru`, `keywords_ru`, `tags_ru`) VALUES
(27, 'admin', '2025-12-02 07:06:25', '<p><a href=\"https://bgorium.com/uploads/posts/2025-12/dalle-2025-12-02-16_11_21-a-glowing-blackgold-coin-connected-to-oil-rigs-and-renewable-energy-turbines-via-energy-beams_-dramatic-lighting-black-and-amber-color-palette-ultra.webp\" class=\"highslide\"><img src=\\\"https://bgorium.com/uploads/posts/2025-12/medium/dalle-2025-12-02-16_11_21-a-glowing-blackgold-coin-connected-to-oil-rigs-and-renewable-energy-turbines-via-energy-beams_-dramatic-lighting-black-and-amber-color-palette-ultra.webp\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\" alt=\\\"\\\"></a></p>', '<p>Energy is one of the world’s most essential economic sectors. By backing a cryptocurrency with energy-related assets, volatility decreases and long-term value becomes more predictable.</p>\n<p>This is exactly what BlackGold Coin achieves. Its value reflects a sector that is fundamental to modern civilization—energy production, oil reserves, and renewable-energy infrastructure.</p>\n<p>Energy-backed tokens offer:</p>\n<ul>\n<li>\n<p>Lower volatility</p>\n</li>\n<li>\n<p>Higher trust</p>\n</li>\n<li>\n<p>Natural demand</p>\n</li>\n<li>\n<p>Better protection during market crashes</p>\n</li>\n</ul>\n<p>BGC represents a future where digital assets and the global energy economy work together.</p>', '', 'Energy-Backed Cryptocurrencies: Why They Are More Resilient', 'Energy is one of the world’s most essential economic sectors. By backing a cryptocurrency with energy-related assets, volatility decreases and long-term value becomes more predictable. This is exactly what BlackGold Coin achieves. Its value reflects a sector that is fundamental to modern', 'assets, volatility, value, Energy, world’s, essential, economic, sectors, backing, cryptocurrency, energyrelated, decreases, longterm, becomes, predictable, exactly, BlackGold, achieves, reflects, sector', '11', 'energy-backed-cryptocurrencies-why-they-are-more-resilient', 0, 1, 1, 1, 0, 0, '', '', '', 'Enerji ilə Dəstəklənən Kriptovalyutalar: Niyə Daha Davamlıdırlar', 'enerji-il-dstklnn-kriptovalyutalar-niy-daha-davamldrlar', '', '<p>Enerji dünyanın ən vacib iqtisadi sahələrindən biridir. Kriptovalyutanı enerji ilə əlaqəli aktivlərlə dəstəkləməklə, dəyişkənlik azalır və uzunmüddətli dəyər daha proqnozlaşdırıla bilən olur.</p>\n<p>BlackGold Coin də məhz bunu həyata keçirir. Onun dəyəri müasir sivilizasiyanın əsas sahəsini—enerji istehsalı, neft ehtiyatları və bərpa olunan enerji infrastrukturu—əks etdirir.</p>\n<p>Enerji ilə dəstəklənən tokenlər aşağıdakı üstünlükləri təqdim edir:</p>\n<ul>\n<li>\n<p>Daha aşağı dəyişkənlik</p>\n</li>\n<li>\n<p>Daha yüksək etibar</p>\n</li>\n<li>\n<p>Təbii tələb</p>\n</li>\n<li>\n<p>Bazar çökmələri zamanı daha yaxşı qorunma</p>\n</li>\n</ul>\n<p>BGC rəqəmsal aktivlərin və qlobal enerji iqtisadiyyatının birgə işlədiyi gələcəyi təmsil edir.</p>', '', 'Enerji dünyanın ən vacib iqtisadi sahələrindən biridir. Kriptovalyutanı enerji ilə əlaqəli aktivlərlə dəstəkləməklə, dəyişkənlik azalır və uzunmüddətli dəyər daha proqnozlaşdırıla bilən', 'enerji, Enerji, dünyanın, vacib, iqtisadi, sahələrindən, biridir, Kriptovalyutanı, əlaqəli, aktivlərlə, dəstəkləməklə, dəyişkənlik, azalır, uzunmüddətli, dəyər, proqnozlaşdırıla, bilən, BlackGold, həyata, keçirir', '', 'Криптовалюты с обеспечением энергией: почему они более устойчивы', 'kriptovalyuty-s-obespecheniem-energiey-pochemu-oni-bolee-ustoychivy', '', '<p>Энергетика — один из самых важных секторов мировой экономики. Обеспечение криптовалюты активами, связанными с энергией, снижает волатильность и делает долгосрочную ценность более предсказуемой.</p>\n<p>Именно этого достигает BlackGold Coin. Его стоимость отражает сектор, фундаментальный для современной цивилизации — производство энергии, запасы нефти и инфраструктуру возобновляемых источников энергии.</p>\n<p>Токены, обеспеченные энергией, предлагают:</p>\n<ul>\n<li>\n<p>Более низкую волатильность</p>\n</li>\n<li>\n<p>Более высокое доверие</p>\n</li>\n<li>\n<p>Естественный спрос</p>\n</li>\n<li>\n<p>Лучшую защиту во время рыночных спадов</p>\n</li>\n</ul>\n<p>BGC представляет будущее, где цифровые активы и мировая энергетическая экономика работают вместе.</p>', '', 'Энергетика — один из самых важных секторов мировой экономики. Обеспечение криптовалюты активами, связанными с энергией, снижает волатильность и делает долгосрочную ценность более предсказуемой.Именно', 'энергией, энергии, Энергетика, самых, важных, секторов, мировой, экономики, Обеспечение, криптовалюты, активами, связанными, снижает, волатильность, делает, долгосрочную, ценность, более, предсказуемой, Именно', ''),
(28, 'admin', '2025-12-02 07:06:42', '<p><a href=\"https://bgorium.com/uploads/posts/2025-12/dalle-2025-12-02-16_14_02-a-comparison-visual-showing-a-shaky-fragmented-crypto-coin-on-the-left-side-and-a-solid-glowing-blackgold-coin-on-the-right-side_-the-theme-is-stabi.webp\" class=\"highslide\"><img src=\\\"https://bgorium.com/uploads/posts/2025-12/medium/dalle-2025-12-02-16_14_02-a-comparison-visual-showing-a-shaky-fragmented-crypto-coin-on-the-left-side-and-a-solid-glowing-blackgold-coin-on-the-right-side_-the-theme-is-stabi.webp\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\" alt=\\\"\\\"></a></p>', '<p>Most cryptocurrencies rely on speculation, hype, and market cycles. While some deliver great returns, they also carry high risk. BlackGold Coin takes a very different approach.</p>\n<p><strong>Traditional Crypto</strong><br>❌ High volatility<br>❌ No real backing<br>❌ Influenced by social media and market hype</p>\n<p><strong>BlackGold Coin</strong><br>✔ Backed by real-world commodities<br>✔ More stable long-term value<br>✔ Transparent ecosystem<br>✔ Integrated fintech services</p>\n<p>This combination makes BGC ideal for investors who want security, reliability, and real economic foundations rather than speculation.</p>', '', 'BlackGold Coin vs. Traditional Cryptocurrencies: What Makes It Different?', 'Most cryptocurrencies rely on speculation, hype, and market cycles. While some deliver great returns, they also carry high risk. BlackGold Coin takes a very different approach. Traditional Crypto ❌ High volatility ❌ No real backing ❌ Influenced by social media and market hype BlackGold Coin ✔', 'speculation, market, BlackGold, cryptocurrencies, cycles, While, deliver, great, returns, carry, takes, different, approach, Traditional, Crypto, volatility, backing, Influenced, social, media', '11', 'blackgold-coin-vs-traditional-cryptocurrencies-what-makes-it-different', 0, 1, 1, 1, 0, 0, '', '', '', 'BlackGold Coin və Ənənəvi Kriptovalyutalar: Onu Fərqləndirən Nədir?', 'blackgold-coin-v-nnvi-kriptovalyutalar-onu-frqlndirn-ndir', '', '<p>Əksər kriptovalyutalar spekulyasiya, həyəcan və bazar dövrlərinə əsaslanır. Bəziləri yüksək gəlir gətirə bilər, amma eyni zamanda böyük risk daşıyır. BlackGold Coin isə tamamilə fərqli yanaşma tətbiq edir.</p>\n<p><strong>Ənənəvi Kripto</strong><br>❌ Yüksək dəyişkənlik<br>❌ Həqiqi təminat yoxdur<br>❌ Sosial media və bazar həyəcanından təsirlənir</p>\n<p><strong>BlackGold Coin</strong><br>✔ Həqiqi dünya əmtəələri ilə dəstəklənir<br>✔ Uzunmüddətli daha sabit dəyər<br>✔ Şəffaf ekosistem<br>✔ İnteqrasiya olunmuş fintech xidmətləri</p>\n<p>Bu kombinasiya BGC-ni, spekulyasiya yerinə təhlükəsizlik, etibarlılıq və real iqtisadi əsaslar axtaran investorlar üçün ideal edir.</p>', '', 'Əksər kriptovalyutalar spekulyasiya, həyəcan və bazar dövrlərinə əsaslanır. Bəziləri yüksək gəlir gətirə bilər, amma eyni zamanda böyük risk daşıyır. BlackGold Coin isə tamamilə fərqli yanaşma tətbiq', 'spekulyasiya, bazar, Həqiqi, Əksər, kriptovalyutalar, həyəcan, dövrlərinə, əsaslanır, Bəziləri, yüksək, gəlir, gətirə, bilər, zamanda, böyük, daşıyır, BlackGold, tamamilə, fərqli, yanaşma', '', 'BlackGold Coin против традиционных криптовалют: в чём отличие?', 'blackgold-coin-protiv-tradicionnyh-kriptovalyut-v-chem-otlichie', '', '<p>Большинство криптовалют основаны на спекуляциях, ажиотаже и циклах рынка. Некоторые из них могут приносить высокий доход, но одновременно несут значительные риски. BlackGold Coin же применяет совершенно иной подход.</p>\n<p><strong>Традиционная криптовалюта</strong><br>❌ Высокая волатильность<br>❌ Нет реальной поддержки<br>❌ Зависимость от социальных сетей и рыночного ажиотажа</p>\n<p><strong>BlackGold Coin</strong><br>✔ Поддерживается реальными товарами<br>✔ Более стабильная долгосрочная ценность<br>✔ Прозрачная экосистема<br>✔ Интегрированные финтех-сервисы</p>\n<p>Такое сочетание делает BGC идеальным для инвесторов, которые ищут безопасность, надежность и реальные экономические основы, а не спекуляции.</p>', '', 'Большинство криптовалют основаны на спекуляциях, ажиотаже и циклах рынка. Некоторые из них могут приносить высокий доход, но одновременно несут значительные риски. BlackGold Coin же применяет', 'Большинство, криптовалют, основаны, спекуляциях, ажиотаже, циклах, рынка, Некоторые, могут, приносить, высокий, доход, одновременно, несут, значительные, риски, BlackGold, применяет, совершенно, подход', ''),
(29, 'admin', '2025-12-02 07:07:04', '<p><a href=\"https://bgorium.com/uploads/posts/2025-12/dalle-2025-12-02-16_14_54-a-luxurious-black-and-gold-crypto-debit-card-with-the-bgorium-emblem-shining-under-a-focused-spotlight_-minimalistic-dark-background-premium-fintech.webp\" class=\"highslide\"><img src=\\\"https://bgorium.com/uploads/posts/2025-12/medium/dalle-2025-12-02-16_14_54-a-luxurious-black-and-gold-crypto-debit-card-with-the-bgorium-emblem-shining-under-a-focused-spotlight_-minimalistic-dark-background-premium-fintech.webp\\\" style=\\\"display:block;margin-left:auto;margin-right:auto;\\\" alt=\\\"\\\"></a></p>', '<p>The BlackGold Card is one of the most exciting innovations in the BGORIUM ecosystem. It allows users to spend their BlackGold Coins and other supported cryptocurrencies just like a traditional card—instantly and globally.</p>\n<p>Key features include:</p>\n<ul>\n<li>\n<p><strong>Instant digital payments</strong></p>\n</li>\n<li>\n<p><strong>Exclusive membership benefits</strong></p>\n</li>\n<li>\n<p><strong>Cashback and staking integration</strong></p>\n</li>\n<li>\n<p><strong>Secure wallet connection</strong></p>\n</li>\n</ul>\n<p>The card represents BGORIUM’s vision: merging digital assets with real-world usability. For investors and everyday users, this card transforms crypto from a stored value into a powerful financial tool.</p>', '', 'The BlackGold Card: A New Way to Use Crypto in Everyday Life', 'The BlackGold Card is one of the most exciting innovations in the BGORIUM ecosystem. It allows users to spend their BlackGold Coins and other supported cryptocurrencies just like a traditional card—instantly and globally. Key features include: Instant digital payments Exclusive membership benefits', 'BlackGold, users, digital, exciting, innovations, BGORIUM, ecosystem, allows, spend, their, Coins, other, supported, cryptocurrencies, traditional, card—instantly, globally, features, include, Instant', '11', 'the-blackgold-card-a-new-way-to-use-crypto-in-everyday-life', 0, 1, 1, 1, 0, 0, '', '', '', 'BlackGold Kartı: Kriptodan Gündəlik Həyatda İstifadə Etməyin Yeni Yolu', 'blackgold-kart-kriptodan-gundlik-hyatda-istifad-etmyin-yeni-yolu', '', '<p>BlackGold Kartı BGORIUM ekosistemindəki ən maraqlı innovasiyalardan biridir. Bu kart istifadəçilərə BlackGold Coin-lərini və digər dəstəklənən kriptovalyutaları ənənəvi kart kimi—ani və qlobal şəkildə xərcləməyə imkan verir.</p>\n<p>Əsas xüsusiyyətləri:</p>\n<ul>\n<li>\n<p>Ani rəqəmsal ödənişlər</p>\n</li>\n<li>\n<p>Eksklüziv üzvlük üstünlükləri</p>\n</li>\n<li>\n<p>Cashback və staking inteqrasiyası</p>\n</li>\n<li>\n<p>Təhlükəsiz cüzdan bağlantısı</p>\n</li>\n</ul>\n<p>Kart BGORIUM-un vizyonunu təmsil edir: rəqəmsal aktivləri real dünya istifadə ilə birləşdirmək. İnvestorlar və gündəlik istifadəçilər üçün bu kart kriptovalyutanı yalnız saxlanılan dəyərdən güclü maliyyə alətinə çevirir.</p>', '', 'BlackGold Kartı BGORIUM ekosistemindəki ən maraqlı innovasiyalardan biridir. Bu kart istifadəçilərə BlackGold Coin-lərini və digər dəstəklənən kriptovalyutaları ənənəvi kart kimi—ani və qlobal', 'BlackGold, BGORIUM, rəqəmsal, Kartı, ekosistemindəki, maraqlı, innovasiyalardan, biridir, istifadəçilərə, lərini, digər, dəstəklənən, kriptovalyutaları, ənənəvi, kimi—ani, qlobal, şəkildə, xərcləməyə, imkan, verir', '', 'Карта BlackGold: новый способ использования криптовалюты в повседневной жизни', 'karta-blackgold-novyy-sposob-ispolzovaniya-kriptovalyuty-v-povsednevnoy-zhizni', '', '<p>Карта BlackGold является одной из самых интересных инноваций в экосистеме BGORIUM. Эта карта позволяет пользователям тратить свои BlackGold Coins и другие поддерживаемые криптовалюты так же, как традиционную карту — мгновенно и по всему миру.</p>\n<p>Основные особенности:</p>\n<ul>\n<li>\n<p>Мгновенные цифровые платежи</p>\n</li>\n<li>\n<p>Эксклюзивные преимущества для участников</p>\n</li>\n<li>\n<p>Интеграция кэшбэка и стейкинга</p>\n</li>\n<li>\n<p>Безопасное подключение кошелька</p>\n</li>\n</ul>\n<p>Карта отражает видение BGORIUM: объединение цифровых активов с использованием в реальном мире. Для инвесторов и обычных пользователей эта карта превращает криптовалюту из просто хранимой ценности в мощный финансовый инструмент.</p>', '', 'Карта BlackGold является одной из самых интересных инноваций в экосистеме BGORIUM. Эта карта позволяет пользователям тратить свои BlackGold Coins и другие поддерживаемые криптовалюты так же, как', 'BlackGold, BGORIUM, карта, Карта, является, одной, самых, интересных, инноваций, экосистеме, позволяет, пользователям, тратить, Coins, другие, поддерживаемые, криптовалюты, традиционную, карту, мгновенно', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_post_extras`
--

CREATE TABLE `dle_post_extras` (
  `eid` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `news_read` int NOT NULL DEFAULT '0',
  `allow_rate` tinyint(1) NOT NULL DEFAULT '1',
  `rating` int NOT NULL DEFAULT '0',
  `vote_num` int NOT NULL DEFAULT '0',
  `votes` tinyint(1) NOT NULL DEFAULT '0',
  `view_edit` tinyint(1) NOT NULL DEFAULT '0',
  `disable_index` tinyint(1) NOT NULL DEFAULT '0',
  `related_ids` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `access` varchar(150) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `editdate` int UNSIGNED NOT NULL DEFAULT '0',
  `editor` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `reason` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_id` int NOT NULL DEFAULT '0',
  `disable_search` tinyint(1) NOT NULL DEFAULT '0',
  `need_pass` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rss` tinyint(1) NOT NULL DEFAULT '1',
  `allow_rss_dzen` tinyint(1) NOT NULL DEFAULT '1',
  `edited_now` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allowed_country` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `not_allowed_country` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_post_extras`
--

INSERT INTO `dle_post_extras` (`eid`, `news_id`, `news_read`, `allow_rate`, `rating`, `vote_num`, `votes`, `view_edit`, `disable_index`, `related_ids`, `access`, `editdate`, `editor`, `reason`, `user_id`, `disable_search`, `need_pass`, `allow_rss`, `allow_rss_dzen`, `edited_now`, `allowed_country`, `not_allowed_country`) VALUES
(4, 4, 0, 1, 0, 0, 0, 0, 0, '21,18,19,20,12', '', 1765366204, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368567}', '', ''),
(7, 7, 3, 1, 0, 0, 0, 0, 0, '4', '', 1765366225, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368571}', '', ''),
(8, 8, 14, 1, 0, 0, 0, 0, 0, '4,7', '', 1765366246, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368575}', '', ''),
(9, 9, 24, 1, 0, 0, 0, 0, 0, '8,4,7', '', 1765366267, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765371610}', '', ''),
(10, 10, 39, 1, 0, 0, 0, 0, 0, '8,9,7,4', '', 1765366307, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368550}', '', ''),
(11, 11, 37, 1, 0, 0, 0, 0, 0, '9,7,8,10,4', '', 1765366327, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368554}', '', ''),
(12, 12, 3, 1, 0, 0, 0, 0, 0, '13,14,9,8,7', '', 1765366372, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368559}', '', ''),
(13, 13, 6, 1, 0, 0, 0, 0, 0, '12', '', 1765366419, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(14, 14, 9, 1, 0, 0, 0, 0, 0, '13,12', '', 1765366444, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368490}', '', ''),
(15, 15, 0, 1, 0, 0, 0, 0, 0, '9,13', '', 1765366466, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(16, 16, 2, 1, 0, 0, 0, 0, 0, '4', '', 1765366505, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368511}', '', ''),
(17, 17, 2, 1, 0, 0, 0, 0, 0, '16', '', 1765366536, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765368507}', '', ''),
(18, 18, 0, 1, 0, 0, 0, 0, 0, '13,16,17,15,12', '', 1765366120, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(19, 19, 0, 1, 0, 0, 0, 0, 0, '18,16,14,12,13', '', 1765366090, 'admin', '', 1, 0, 0, 1, 1, '{\"name\":\"admin\",\"time\":1765366097}', '', ''),
(20, 20, 2, 1, 0, 0, 0, 0, 0, '18,19,10,11,8', '', 1765365795, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(21, 21, 4, 1, 0, 0, 0, 0, 0, '20,19,18,8,11', '', 1765365769, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(22, 22, 10, 1, 0, 0, 0, 0, 0, '21,20,19,8,18', '', 1765365726, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(23, 23, 4, 1, 0, 0, 0, 0, 0, '22,20,8,18,19', '', 1765365706, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(24, 24, 6, 1, 0, 0, 0, 0, 0, '23,19,20,18,21', '', 1765365651, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(25, 25, 4, 1, 0, 0, 0, 0, 0, '20,21,19,18,8', '', 1765365628, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(26, 26, 174, 1, 0, 0, 0, 0, 0, '20,21,22,19,8', '', 1765365607, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(27, 27, 5, 1, 0, 0, 0, 0, 0, '21,20,19,23,22', '', 1765365261, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(28, 28, 6, 1, 0, 0, 0, 0, 0, '24,8,20,21,23', '', 1765365238, 'admin', '', 1, 0, 0, 1, 1, '', '', ''),
(29, 29, 22, 1, 0, 0, 0, 0, 0, '21,20,18,19,8', '', 1765365088, 'admin', '', 1, 0, 0, 1, 1, '', '', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_post_extras_cats`
--

CREATE TABLE `dle_post_extras_cats` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `cat_id` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_post_extras_cats`
--

INSERT INTO `dle_post_extras_cats` (`id`, `news_id`, `cat_id`) VALUES
(4, 4, 2),
(13, 7, 9),
(14, 8, 9),
(15, 9, 9),
(16, 10, 9),
(17, 11, 9),
(18, 12, 10),
(19, 13, 10),
(20, 14, 10),
(21, 15, 10),
(22, 16, 10),
(23, 17, 10),
(24, 18, 6),
(25, 19, 7),
(26, 20, 4),
(27, 21, 5),
(28, 22, 11),
(29, 23, 11),
(30, 24, 11),
(31, 26, 11),
(32, 27, 11),
(33, 28, 11),
(34, 29, 11),
(35, 25, 11);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_post_log`
--

CREATE TABLE `dle_post_log` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `expires` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `action` tinyint(1) NOT NULL DEFAULT '0',
  `move_cat` varchar(190) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_post_pass`
--

CREATE TABLE `dle_post_pass` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `password` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_question`
--

CREATE TABLE `dle_question` (
  `id` int NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `answer` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_read_log`
--

CREATE TABLE `dle_read_log` (
  `id` int UNSIGNED NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_redirects`
--

CREATE TABLE `dle_redirects` (
  `id` int UNSIGNED NOT NULL,
  `from` varchar(250) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `to` varchar(250) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_rss`
--

CREATE TABLE `dle_rss` (
  `id` smallint NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `allow_main` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rating` tinyint(1) NOT NULL DEFAULT '0',
  `allow_comm` tinyint(1) NOT NULL DEFAULT '0',
  `text_type` tinyint(1) NOT NULL DEFAULT '0',
  `date` tinyint(1) NOT NULL DEFAULT '0',
  `search` text COLLATE utf8mb4_general_ci NOT NULL,
  `max_news` tinyint NOT NULL DEFAULT '0',
  `cookie` text COLLATE utf8mb4_general_ci NOT NULL,
  `category` smallint NOT NULL DEFAULT '0',
  `lastdate` int UNSIGNED NOT NULL DEFAULT '0',
  `allow_source` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_rss`
--

INSERT INTO `dle_rss` (`id`, `url`, `description`, `allow_main`, `allow_rating`, `allow_comm`, `text_type`, `date`, `search`, `max_news`, `cookie`, `category`, `lastdate`, `allow_source`) VALUES
(1, 'https://dle-news.ru/rss.xml', 'Официальный сайт DataLife Engine', 1, 1, 1, 1, 1, '<div class=\"card-body post-body pl-4 pr-3 pb-4 pt-0\">{get}<div class=\"card-footer d-flex align-content-center pt-0 pl-0 pr-4 pb-3\">', 5, '', 1, 0, 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_rssinform`
--

CREATE TABLE `dle_rssinform` (
  `id` smallint NOT NULL,
  `tag` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `descr` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `category` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `template` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `news_max` smallint NOT NULL DEFAULT '0',
  `tmax` smallint NOT NULL DEFAULT '0',
  `dmax` smallint NOT NULL DEFAULT '0',
  `approve` tinyint(1) NOT NULL DEFAULT '1',
  `rss_date_format` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_rssinform`
--

INSERT INTO `dle_rssinform` (`id`, `tag`, `descr`, `category`, `url`, `template`, `news_max`, `tmax`, `dmax`, `approve`, `rss_date_format`) VALUES
(1, 'dle', 'Новости', '0', 'https://www.ixbt.com/export/news.rss', 'informer', 3, 0, 200, 1, 'j F Y H:i');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_sendlog`
--

CREATE TABLE `dle_sendlog` (
  `id` int UNSIGNED NOT NULL,
  `user` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` int UNSIGNED NOT NULL DEFAULT '0',
  `flag` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_social_login`
--

CREATE TABLE `dle_social_login` (
  `id` int NOT NULL,
  `sid` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `uid` int NOT NULL DEFAULT '0',
  `password` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `provider` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `wait` tinyint(1) NOT NULL DEFAULT '0',
  `waitlogin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_spam_log`
--

CREATE TABLE `dle_spam_log` (
  `id` int UNSIGNED NOT NULL,
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `is_spammer` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_static`
--

CREATE TABLE `dle_static` (
  `id` mediumint NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `descr` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `template` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `allow_br` tinyint(1) NOT NULL DEFAULT '0',
  `allow_template` tinyint(1) NOT NULL DEFAULT '0',
  `grouplevel` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'all',
  `tpl` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `metadescr` varchar(300) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `metakeys` text COLLATE utf8mb4_general_ci NOT NULL,
  `views` mediumint NOT NULL DEFAULT '0',
  `template_folder` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` int UNSIGNED NOT NULL DEFAULT '0',
  `metatitle` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_count` tinyint(1) NOT NULL DEFAULT '1',
  `sitemap` tinyint(1) NOT NULL DEFAULT '1',
  `disable_index` tinyint(1) NOT NULL DEFAULT '0',
  `disable_search` tinyint(1) NOT NULL DEFAULT '0',
  `password` text COLLATE utf8mb4_general_ci NOT NULL,
  `name_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `descr_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `template_az` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `metadescr_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `metatitle_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `metakeys_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `name_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `descr_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `template_ru` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `metadescr_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `metatitle_ru` text COLLATE utf8mb4_general_ci NOT NULL,
  `metakeys_ru` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_static`
--

INSERT INTO `dle_static` (`id`, `name`, `descr`, `template`, `allow_br`, `allow_template`, `grouplevel`, `tpl`, `metadescr`, `metakeys`, `views`, `template_folder`, `date`, `metatitle`, `allow_count`, `sitemap`, `disable_index`, `disable_search`, `password`, `name_az`, `descr_az`, `template_az`, `metadescr_az`, `metatitle_az`, `metakeys_az`, `name_ru`, `descr_ru`, `template_ru`, `metadescr_ru`, `metatitle_ru`, `metakeys_ru`) VALUES
(1, 'dle-rules-page', 'Общие правила на сайте', '<p><b>Общие правила поведения на сайте:</b></p><p>Начнем с того, что на сайте общаются сотни людей, разных религий и взглядов, и все они являются полноправными посетителями нашего сайта, поэтому если мы хотим чтобы это сообщество людей функционировало нам и необходимы правила. Мы настоятельно рекомендуем прочитать настоящие правила, это займет у вас всего минут пять, но сбережет нам и вам время и поможет сделать сайт более интересным и организованным.</p><p>Начнем с того, что на нашем сайте нужно вести себя уважительно ко всем посетителям сайта. Не надо оскорблений по отношению к участникам, это всегда лишнее. Если есть претензии - обращайтесь к Админам или Модераторам (воспользуйтесь личными сообщениями). Оскорбление других посетителей считается у нас одним из самых тяжких нарушений и строго наказывается администрацией. <b>У нас строго запрещен расизм, религиозные и политические высказывания.</b> Заранее благодарим вас за понимание и за желание сделать наш сайт более вежливым и дружелюбным.</p><p><b>На сайте строго запрещено:</b></p><ul><li>сообщения, не относящиеся к содержанию статьи или к контексту обсуждения</li><li>оскорбление и угрозы в адрес посетителей сайта</li><li>в комментариях запрещаются выражения, содержащие ненормативную лексику, унижающие человеческое достоинство, разжигающие межнациональную рознь</li><li>спам, а также реклама любых товаров и услуг, иных ресурсов, СМИ или событий, не относящихся к контексту обсуждения статьи</li></ul><p>Давайте будем уважать друг друга и сайт, на который Вы и другие читатели приходят пообщаться и высказать свои мысли. Администрация сайта оставляет за собой право удалять комментарии или часть комментариев, если они не соответствуют данным требованиям.</p><p>При нарушении правил вам может быть дано <b>предупреждение</b>. В некоторых случаях может быть дан бан <b>без предупреждений</b>. По вопросам снятия бана писать администратору.</p><p><b>Оскорбление</b> администраторов или модераторов также караются <b>баном</b> - уважайте чужой труд.</p><p style=\"text-align: center;\">{ACCEPT-DECLINE}</p>', 1, 1, 'all', '', 'Общие правила на сайте', 'Общие правила на сайте', 0, '', 1763470604, '', 1, 1, 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_static_files`
--

CREATE TABLE `dle_static_files` (
  `id` int NOT NULL,
  `static_id` int NOT NULL DEFAULT '0',
  `author` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `onserver` varchar(190) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `dcount` int NOT NULL DEFAULT '0',
  `size` bigint NOT NULL DEFAULT '0',
  `checksum` char(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `driver` mediumint NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_storage`
--

CREATE TABLE `dle_storage` (
  `id` mediumint NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `type` smallint NOT NULL DEFAULT '0',
  `accesstype` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `connect_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `connect_port` mediumint NOT NULL DEFAULT '0',
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `http_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `client_key` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `secret_key` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `bucket` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `region` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `default_storage` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `posi` mediumint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_subscribe`
--

CREATE TABLE `dle_subscribe` (
  `id` int NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `name` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `news_id` int NOT NULL DEFAULT '0',
  `hash` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_tags`
--

CREATE TABLE `dle_tags` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `tag` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_tags_az`
--

CREATE TABLE `dle_tags_az` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `tag` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_tags_ru`
--

CREATE TABLE `dle_tags_ru` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `tag` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_twofactor`
--

CREATE TABLE `dle_twofactor` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `pin` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `attempt` tinyint(1) NOT NULL DEFAULT '0',
  `date` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_usergroups`
--

CREATE TABLE `dle_usergroups` (
  `id` smallint NOT NULL,
  `group_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_cats` text COLLATE utf8mb4_general_ci NOT NULL,
  `allow_adds` tinyint(1) NOT NULL DEFAULT '1',
  `cat_add` text COLLATE utf8mb4_general_ci NOT NULL,
  `allow_admin` tinyint(1) NOT NULL DEFAULT '0',
  `allow_addc` tinyint(1) NOT NULL DEFAULT '0',
  `allow_editc` tinyint(1) NOT NULL DEFAULT '0',
  `allow_delc` tinyint(1) NOT NULL DEFAULT '0',
  `edit_allc` tinyint(1) NOT NULL DEFAULT '0',
  `del_allc` tinyint(1) NOT NULL DEFAULT '0',
  `moderation` tinyint(1) NOT NULL DEFAULT '0',
  `allow_all_edit` tinyint(1) NOT NULL DEFAULT '0',
  `allow_edit` tinyint(1) NOT NULL DEFAULT '0',
  `allow_pm` tinyint(1) NOT NULL DEFAULT '0',
  `max_pm` smallint NOT NULL DEFAULT '0',
  `max_foto` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_files` tinyint(1) NOT NULL DEFAULT '0',
  `allow_hide` tinyint(1) NOT NULL DEFAULT '1',
  `allow_short` tinyint(1) NOT NULL DEFAULT '0',
  `time_limit` tinyint(1) NOT NULL DEFAULT '0',
  `rid` smallint NOT NULL DEFAULT '0',
  `allow_fixed` tinyint(1) NOT NULL DEFAULT '0',
  `allow_feed` tinyint(1) NOT NULL DEFAULT '1',
  `allow_search` tinyint(1) NOT NULL DEFAULT '1',
  `allow_poll` tinyint(1) NOT NULL DEFAULT '1',
  `allow_main` tinyint(1) NOT NULL DEFAULT '1',
  `captcha` tinyint(1) NOT NULL DEFAULT '0',
  `icon` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_modc` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rating` tinyint(1) NOT NULL DEFAULT '1',
  `allow_offline` tinyint(1) NOT NULL DEFAULT '0',
  `allow_image_upload` tinyint(1) NOT NULL DEFAULT '0',
  `allow_file_upload` tinyint(1) NOT NULL DEFAULT '0',
  `allow_signature` tinyint(1) NOT NULL DEFAULT '0',
  `allow_url` tinyint(1) NOT NULL DEFAULT '1',
  `news_sec_code` tinyint(1) NOT NULL DEFAULT '1',
  `allow_image` tinyint(1) NOT NULL DEFAULT '0',
  `max_signature` smallint NOT NULL DEFAULT '0',
  `max_info` smallint NOT NULL DEFAULT '0',
  `admin_addnews` tinyint(1) NOT NULL DEFAULT '0',
  `admin_editnews` tinyint(1) NOT NULL DEFAULT '0',
  `admin_comments` tinyint(1) NOT NULL DEFAULT '0',
  `admin_categories` tinyint(1) NOT NULL DEFAULT '0',
  `admin_editusers` tinyint(1) NOT NULL DEFAULT '0',
  `admin_wordfilter` tinyint(1) NOT NULL DEFAULT '0',
  `admin_xfields` tinyint(1) NOT NULL DEFAULT '0',
  `admin_userfields` tinyint(1) NOT NULL DEFAULT '0',
  `admin_static` tinyint(1) NOT NULL DEFAULT '0',
  `admin_editvote` tinyint(1) NOT NULL DEFAULT '0',
  `admin_newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `admin_blockip` tinyint(1) NOT NULL DEFAULT '0',
  `admin_banners` tinyint(1) NOT NULL DEFAULT '0',
  `admin_rss` tinyint(1) NOT NULL DEFAULT '0',
  `admin_iptools` tinyint(1) NOT NULL DEFAULT '0',
  `admin_rssinform` tinyint(1) NOT NULL DEFAULT '0',
  `admin_googlemap` tinyint(1) NOT NULL DEFAULT '0',
  `allow_html` tinyint(1) NOT NULL DEFAULT '1',
  `group_prefix` text COLLATE utf8mb4_general_ci NOT NULL,
  `group_suffix` text COLLATE utf8mb4_general_ci NOT NULL,
  `allow_subscribe` tinyint(1) NOT NULL DEFAULT '0',
  `allow_image_size` tinyint(1) NOT NULL DEFAULT '0',
  `cat_allow_addnews` text COLLATE utf8mb4_general_ci NOT NULL,
  `flood_news` smallint NOT NULL DEFAULT '0',
  `max_day_news` smallint NOT NULL DEFAULT '0',
  `force_leech` tinyint(1) NOT NULL DEFAULT '0',
  `edit_limit` smallint NOT NULL DEFAULT '0',
  `captcha_pm` tinyint(1) NOT NULL DEFAULT '0',
  `max_pm_day` smallint NOT NULL DEFAULT '0',
  `max_mail_day` smallint NOT NULL DEFAULT '0',
  `admin_tagscloud` tinyint(1) NOT NULL DEFAULT '0',
  `allow_vote` tinyint(1) NOT NULL DEFAULT '0',
  `admin_complaint` tinyint(1) NOT NULL DEFAULT '0',
  `news_question` tinyint(1) NOT NULL DEFAULT '0',
  `comments_question` tinyint(1) NOT NULL DEFAULT '0',
  `max_comment_day` smallint NOT NULL DEFAULT '0',
  `max_images` smallint NOT NULL DEFAULT '0',
  `max_files` smallint NOT NULL DEFAULT '0',
  `disable_news_captcha` smallint NOT NULL DEFAULT '0',
  `disable_comments_captcha` smallint NOT NULL DEFAULT '0',
  `pm_question` tinyint(1) NOT NULL DEFAULT '0',
  `captcha_feedback` tinyint(1) NOT NULL DEFAULT '1',
  `feedback_question` tinyint(1) NOT NULL DEFAULT '0',
  `files_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `max_file_size` mediumint NOT NULL DEFAULT '0',
  `files_max_speed` smallint NOT NULL DEFAULT '0',
  `spamfilter` tinyint(1) NOT NULL DEFAULT '2',
  `allow_comments_rating` tinyint(1) NOT NULL DEFAULT '1',
  `max_edit_days` tinyint(1) NOT NULL DEFAULT '0',
  `spampmfilter` tinyint(1) NOT NULL DEFAULT '0',
  `force_reg` tinyint(1) NOT NULL DEFAULT '0',
  `force_reg_days` mediumint NOT NULL DEFAULT '0',
  `force_reg_group` smallint NOT NULL DEFAULT '4',
  `force_news` tinyint(1) NOT NULL DEFAULT '0',
  `force_news_count` mediumint NOT NULL DEFAULT '0',
  `force_news_group` smallint NOT NULL DEFAULT '4',
  `force_comments` tinyint(1) NOT NULL DEFAULT '0',
  `force_comments_count` mediumint NOT NULL DEFAULT '0',
  `force_comments_group` smallint NOT NULL DEFAULT '4',
  `force_rating` tinyint(1) NOT NULL DEFAULT '0',
  `force_rating_count` mediumint NOT NULL DEFAULT '0',
  `force_rating_group` smallint NOT NULL DEFAULT '4',
  `not_allow_cats` text COLLATE utf8mb4_general_ci NOT NULL,
  `allow_up_image` tinyint(1) NOT NULL DEFAULT '0',
  `allow_up_watermark` tinyint(1) NOT NULL DEFAULT '0',
  `allow_up_thumb` tinyint(1) NOT NULL DEFAULT '0',
  `up_count_image` smallint NOT NULL DEFAULT '0',
  `up_image_side` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `up_image_size` mediumint NOT NULL DEFAULT '0',
  `up_thumb_size` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_mail_files` tinyint(1) NOT NULL DEFAULT '0',
  `max_mail_files` smallint NOT NULL DEFAULT '0',
  `max_mail_allfiles` mediumint NOT NULL DEFAULT '0',
  `mail_files_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `video_comments` tinyint(1) NOT NULL DEFAULT '0',
  `media_comments` tinyint(1) NOT NULL DEFAULT '0',
  `min_image_side` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_public_file_upload` tinyint(1) NOT NULL DEFAULT '0',
  `force_comments_rating` tinyint(1) NOT NULL DEFAULT '0',
  `force_comments_rating_count` mediumint NOT NULL DEFAULT '0',
  `force_comments_rating_group` smallint NOT NULL DEFAULT '0',
  `max_downloads` smallint NOT NULL DEFAULT '0',
  `admin_links` tinyint(1) NOT NULL DEFAULT '0',
  `admin_meta` tinyint(1) NOT NULL DEFAULT '0',
  `admin_redirects` tinyint(1) NOT NULL DEFAULT '0',
  `allow_change_storage` tinyint(1) NOT NULL DEFAULT '0',
  `self_delete` tinyint(1) NOT NULL DEFAULT '2',
  `allow_complaint_news` tinyint(1) NOT NULL DEFAULT '1',
  `allow_complaint_comments` tinyint(1) NOT NULL DEFAULT '1',
  `allow_complaint_orfo` tinyint(1) NOT NULL DEFAULT '1',
  `flood_time` smallint NOT NULL DEFAULT '0',
  `max_c_negative` smallint NOT NULL DEFAULT '0',
  `max_n_negative` smallint NOT NULL DEFAULT '0',
  `group_name_az` text COLLATE utf8mb4_general_ci NOT NULL,
  `group_name_ru` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_usergroups`
--

INSERT INTO `dle_usergroups` (`id`, `group_name`, `allow_cats`, `allow_adds`, `cat_add`, `allow_admin`, `allow_addc`, `allow_editc`, `allow_delc`, `edit_allc`, `del_allc`, `moderation`, `allow_all_edit`, `allow_edit`, `allow_pm`, `max_pm`, `max_foto`, `allow_files`, `allow_hide`, `allow_short`, `time_limit`, `rid`, `allow_fixed`, `allow_feed`, `allow_search`, `allow_poll`, `allow_main`, `captcha`, `icon`, `allow_modc`, `allow_rating`, `allow_offline`, `allow_image_upload`, `allow_file_upload`, `allow_signature`, `allow_url`, `news_sec_code`, `allow_image`, `max_signature`, `max_info`, `admin_addnews`, `admin_editnews`, `admin_comments`, `admin_categories`, `admin_editusers`, `admin_wordfilter`, `admin_xfields`, `admin_userfields`, `admin_static`, `admin_editvote`, `admin_newsletter`, `admin_blockip`, `admin_banners`, `admin_rss`, `admin_iptools`, `admin_rssinform`, `admin_googlemap`, `allow_html`, `group_prefix`, `group_suffix`, `allow_subscribe`, `allow_image_size`, `cat_allow_addnews`, `flood_news`, `max_day_news`, `force_leech`, `edit_limit`, `captcha_pm`, `max_pm_day`, `max_mail_day`, `admin_tagscloud`, `allow_vote`, `admin_complaint`, `news_question`, `comments_question`, `max_comment_day`, `max_images`, `max_files`, `disable_news_captcha`, `disable_comments_captcha`, `pm_question`, `captcha_feedback`, `feedback_question`, `files_type`, `max_file_size`, `files_max_speed`, `spamfilter`, `allow_comments_rating`, `max_edit_days`, `spampmfilter`, `force_reg`, `force_reg_days`, `force_reg_group`, `force_news`, `force_news_count`, `force_news_group`, `force_comments`, `force_comments_count`, `force_comments_group`, `force_rating`, `force_rating_count`, `force_rating_group`, `not_allow_cats`, `allow_up_image`, `allow_up_watermark`, `allow_up_thumb`, `up_count_image`, `up_image_side`, `up_image_size`, `up_thumb_size`, `allow_mail_files`, `max_mail_files`, `max_mail_allfiles`, `mail_files_type`, `video_comments`, `media_comments`, `min_image_side`, `allow_public_file_upload`, `force_comments_rating`, `force_comments_rating_count`, `force_comments_rating_group`, `max_downloads`, `admin_links`, `admin_meta`, `admin_redirects`, `allow_change_storage`, `self_delete`, `allow_complaint_news`, `allow_complaint_comments`, `allow_complaint_orfo`, `flood_time`, `max_c_negative`, `max_n_negative`, `group_name_az`, `group_name_ru`) VALUES
(1, 'Администраторы', 'all', 1, 'all', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 50, '101', 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, '{THEME}/images/icon_1.gif', 0, 1, 1, 1, 1, 1, 1, 0, 1, 500, 1000, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '<b><span style=\"color:red\">', '</span></b>', 1, 1, 'all', 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'zip,rar,doc,pdf,mp3,mp4', 4096, 0, 2, 1, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, '', 1, 1, 1, 3, '800x600', 300, '200x150', 1, 3, 1000, 'jpg,png,zip,pdf', 1, 1, '10x10', 1, 0, 0, 4, 0, 1, 1, 1, 1, 0, 1, 1, 1, 0, 0, 0, '', ''),
(2, 'Главные редакторы', 'all', 1, 'all', 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 50, '101', 1, 1, 1, 0, 2, 1, 1, 1, 1, 1, 0, '{THEME}/images/icon_2.gif', 0, 1, 0, 1, 1, 1, 1, 0, 1, 500, 1000, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '', 1, 1, 'all', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'zip,rar,doc,pdf,mp3,mp4', 4096, 0, 2, 1, 0, 0, 0, 0, 2, 0, 0, 2, 0, 0, 2, 0, 0, 2, '', 1, 1, 1, 3, '800x600', 300, '200x150', 1, 3, 1000, 'jpg,png,zip,pdf', 1, 1, '10x10', 1, 0, 0, 4, 0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 0, 0, '', ''),
(3, 'Журналисты', 'all', 1, 'all', 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 50, '101', 1, 1, 1, 0, 3, 0, 1, 1, 1, 1, 0, '{THEME}/images/icon_3.gif', 0, 1, 0, 1, 1, 1, 1, 0, 1, 500, 1000, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '', 1, 1, 'all', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'zip,rar,doc,pdf,mp3,mp4', 4096, 0, 2, 1, 0, 0, 0, 0, 3, 0, 0, 3, 0, 0, 3, 0, 0, 3, '', 1, 1, 1, 3, '800x600', 300, '200x150', 0, 3, 1000, 'jpg,png,zip,pdf', 1, 1, '10x10', 0, 0, 0, 4, 0, 0, 0, 0, 1, 2, 1, 1, 1, 30, 0, 0, '', ''),
(4, 'Посетители', 'all', 1, 'all', 0, 1, 1, 1, 0, 0, 0, 0, 0, 1, 20, '101', 1, 1, 1, 0, 4, 0, 1, 1, 1, 1, 0, '{THEME}/images/icon_4.gif', 0, 1, 0, 1, 0, 1, 1, 1, 0, 500, 1000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', '', 1, 0, 'all', 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'zip,rar,doc,pdf,mp3,mp4', 4096, 0, 2, 1, 0, 2, 0, 0, 4, 0, 0, 4, 0, 0, 4, 0, 0, 4, '', 0, 0, 0, 1, '800x600', 300, '200x150', 0, 3, 1000, 'jpg,png,zip,pdf', 0, 0, '10x10', 0, 0, 0, 4, 0, 0, 0, 0, 0, 2, 1, 1, 1, 30, 3, 3, '', ''),
(5, 'Гости', 'all', 0, 'all', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, '0', 1, 0, 1, 0, 5, 0, 1, 1, 1, 0, 1, '{THEME}/images/icon_5.gif', 0, 1, 0, 0, 0, 0, 1, 1, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 'all', 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', 0, 0, 2, 1, 0, 2, 0, 0, 5, 0, 0, 5, 0, 0, 5, 0, 0, 5, '', 0, 0, 0, 1, '800x600', 300, '200x150', 0, 3, 1000, 'jpg,png,zip,pdf', 0, 0, '10x10', 0, 0, 0, 4, 0, 0, 0, 0, 0, 0, 1, 1, 1, 30, 3, 3, '', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_users`
--

CREATE TABLE `dle_users` (
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_id` int NOT NULL,
  `news_num` mediumint NOT NULL DEFAULT '0',
  `comm_num` mediumint NOT NULL DEFAULT '0',
  `user_group` smallint NOT NULL DEFAULT '4',
  `lastdate` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `reg_date` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `banned` varchar(5) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `allow_mail` tinyint(1) NOT NULL DEFAULT '1',
  `info` text COLLATE utf8mb4_general_ci NOT NULL,
  `signature` text COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `fullname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `land` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `favorites` text COLLATE utf8mb4_general_ci NOT NULL,
  `pm_all` smallint NOT NULL DEFAULT '0',
  `pm_unread` smallint NOT NULL DEFAULT '0',
  `time_limit` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `xfields` text COLLATE utf8mb4_general_ci NOT NULL,
  `allowed_ip` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `hash` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `logged_ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `restricted` tinyint(1) NOT NULL DEFAULT '0',
  `restricted_days` smallint NOT NULL DEFAULT '0',
  `restricted_date` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `timezone` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `news_subscribe` tinyint(1) NOT NULL DEFAULT '0',
  `comments_reply_subscribe` tinyint(1) NOT NULL DEFAULT '0',
  `twofactor_auth` tinyint(1) NOT NULL DEFAULT '0',
  `cat_add` varchar(500) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `cat_allow_addnews` varchar(500) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `twofactor_secret` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_users`
--

INSERT INTO `dle_users` (`email`, `password`, `name`, `user_id`, `news_num`, `comm_num`, `user_group`, `lastdate`, `reg_date`, `banned`, `allow_mail`, `info`, `signature`, `foto`, `fullname`, `land`, `favorites`, `pm_all`, `pm_unread`, `time_limit`, `xfields`, `allowed_ip`, `hash`, `logged_ip`, `restricted`, `restricted_days`, `restricted_date`, `timezone`, `news_subscribe`, `comments_reply_subscribe`, `twofactor_auth`, `cat_add`, `cat_allow_addnews`, `twofactor_secret`) VALUES
('admin@gmail.com', '$2y$10$vgwokizT3dWnMke7OvIKoeBxuMrh6DQDefcaH8cOGXc4GsrrmEPoS', 'admin', 1, 24, 0, 1, '1767605571', '1763470604', '', 1, '', '', '', '', '', '', 0, 0, '', '', '', '8b84b4c67496c489aafa9c7cef60ae9e', '31.171.106.206', 0, 0, '', '', 0, 0, 0, '', '', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_users_delete`
--

CREATE TABLE `dle_users_delete` (
  `id` int NOT NULL,
  `user_id` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_views`
--

CREATE TABLE `dle_views` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_vote`
--

CREATE TABLE `dle_vote` (
  `id` mediumint NOT NULL,
  `category` text COLLATE utf8mb4_general_ci NOT NULL,
  `vote_num` mediumint NOT NULL DEFAULT '0',
  `date` varchar(25) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `title` varchar(200) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `body` text COLLATE utf8mb4_general_ci NOT NULL,
  `approve` tinyint(1) NOT NULL DEFAULT '1',
  `start` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `end` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `grouplevel` varchar(250) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'all'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dle_vote`
--

INSERT INTO `dle_vote` (`id`, `category`, `vote_num`, `date`, `title`, `body`, `approve`, `start`, `end`, `grouplevel`) VALUES
(1, 'all', 0, '2025-11-18 07:56:44', 'Оцените работу движка', 'Лучший из новостных<br>Неплохой движок<br>Устраивает ... но ...<br>Встречал и получше<br>Совсем не понравился', 1, '', '', 'all');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_vote_result`
--

CREATE TABLE `dle_vote_result` (
  `id` int NOT NULL,
  `ip` varchar(46) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `vote_id` mediumint NOT NULL DEFAULT '0',
  `answer` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dle_xfsearch`
--

CREATE TABLE `dle_xfsearch` (
  `id` int NOT NULL,
  `news_id` int NOT NULL DEFAULT '0',
  `tagname` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `tagvalue` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `dle_admin_logs`
--
ALTER TABLE `dle_admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`date`);

--
-- Tablo için indeksler `dle_admin_sections`
--
ALTER TABLE `dle_admin_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Tablo için indeksler `dle_banned`
--
ALTER TABLE `dle_banned`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`users_id`);

--
-- Tablo için indeksler `dle_banners`
--
ALTER TABLE `dle_banners`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_banners_logs`
--
ALTER TABLE `dle_banners_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bid` (`bid`),
  ADD KEY `ip` (`ip`);

--
-- Tablo için indeksler `dle_banners_rubrics`
--
ALTER TABLE `dle_banners_rubrics`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_category`
--
ALTER TABLE `dle_category`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_comments`
--
ALTER TABLE `dle_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `approve` (`approve`),
  ADD KEY `parent` (`parent`),
  ADD KEY `rating` (`rating`),
  ADD KEY `approve_id` (`approve`,`id`);
ALTER TABLE `dle_comments` ADD FULLTEXT KEY `text` (`text`);

--
-- Tablo için indeksler `dle_comments_files`
--
ALTER TABLE `dle_comments_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `c_id` (`c_id`),
  ADD KEY `author` (`author`);

--
-- Tablo için indeksler `dle_comment_rating_log`
--
ALTER TABLE `dle_comment_rating_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `c_id` (`c_id`),
  ADD KEY `member` (`member`),
  ADD KEY `ip` (`ip`);

--
-- Tablo için indeksler `dle_complaint`
--
ALTER TABLE `dle_complaint`
  ADD PRIMARY KEY (`id`),
  ADD KEY `c_id` (`c_id`),
  ADD KEY `p_id` (`p_id`),
  ADD KEY `n_id` (`n_id`);

--
-- Tablo için indeksler `dle_conversations`
--
ALTER TABLE `dle_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `updated_at` (`updated_at`);

--
-- Tablo için indeksler `dle_conversations_messages`
--
ALTER TABLE `dle_conversations_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Tablo için indeksler `dle_conversation_reads`
--
ALTER TABLE `dle_conversation_reads`
  ADD PRIMARY KEY (`user_id`,`conversation_id`),
  ADD KEY `last_read_at` (`last_read_at`);

--
-- Tablo için indeksler `dle_conversation_users`
--
ALTER TABLE `dle_conversation_users`
  ADD PRIMARY KEY (`user_id`,`conversation_id`);

--
-- Tablo için indeksler `dle_downloads_log`
--
ALTER TABLE `dle_downloads_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `date` (`date`);

--
-- Tablo için indeksler `dle_email`
--
ALTER TABLE `dle_email`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_files`
--
ALTER TABLE `dle_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`);

--
-- Tablo için indeksler `dle_flood`
--
ALTER TABLE `dle_flood`
  ADD PRIMARY KEY (`f_id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `id` (`id`),
  ADD KEY `flag` (`flag`);

--
-- Tablo için indeksler `dle_ignore_list`
--
ALTER TABLE `dle_ignore_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `user_from` (`user_from`);

--
-- Tablo için indeksler `dle_images`
--
ALTER TABLE `dle_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author` (`author`),
  ADD KEY `news_id` (`news_id`);

--
-- Tablo için indeksler `dle_links`
--
ALTER TABLE `dle_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`enabled`);

--
-- Tablo için indeksler `dle_login_log`
--
ALTER TABLE `dle_login_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip` (`ip`),
  ADD KEY `date` (`date`);

--
-- Tablo için indeksler `dle_logs`
--
ALTER TABLE `dle_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `member` (`member`),
  ADD KEY `ip` (`ip`);

--
-- Tablo için indeksler `dle_lostdb`
--
ALTER TABLE `dle_lostdb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lostid` (`lostid`);

--
-- Tablo için indeksler `dle_mail_log`
--
ALTER TABLE `dle_mail_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hash` (`hash`);

--
-- Tablo için indeksler `dle_metatags`
--
ALTER TABLE `dle_metatags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`enabled`);

--
-- Tablo için indeksler `dle_multilanguage`
--
ALTER TABLE `dle_multilanguage`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_notice`
--
ALTER TABLE `dle_notice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `dle_plugins`
--
ALTER TABLE `dle_plugins`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_plugins_files`
--
ALTER TABLE `dle_plugins_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plugin_id` (`plugin_id`),
  ADD KEY `active` (`active`);

--
-- Tablo için indeksler `dle_plugins_logs`
--
ALTER TABLE `dle_plugins_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plugin_id` (`plugin_id`);

--
-- Tablo için indeksler `dle_poll`
--
ALTER TABLE `dle_poll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`);

--
-- Tablo için indeksler `dle_poll_log`
--
ALTER TABLE `dle_poll_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `member` (`member`);

--
-- Tablo için indeksler `dle_post`
--
ALTER TABLE `dle_post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor` (`autor`),
  ADD KEY `alt_name` (`alt_name`),
  ADD KEY `category` (`category`),
  ADD KEY `approve` (`approve`),
  ADD KEY `allow_main` (`allow_main`),
  ADD KEY `date` (`date`),
  ADD KEY `symbol` (`symbol`),
  ADD KEY `comm_num` (`comm_num`),
  ADD KEY `fixed` (`fixed`),
  ADD KEY `approve_id` (`approve`,`id`);
ALTER TABLE `dle_post` ADD FULLTEXT KEY `short_story` (`short_story`,`full_story`,`xfields`,`title`);
ALTER TABLE `dle_post` ADD FULLTEXT KEY `short_story_az` (`short_story_az`,`full_story_az`,`xfields`,`title_az`);
ALTER TABLE `dle_post` ADD FULLTEXT KEY `short_story_ru` (`short_story_ru`,`full_story_ru`,`xfields`,`title_ru`);

--
-- Tablo için indeksler `dle_post_extras`
--
ALTER TABLE `dle_post_extras`
  ADD PRIMARY KEY (`eid`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `editdate` (`editdate`),
  ADD KEY `rating` (`rating`),
  ADD KEY `disable_search` (`disable_search`),
  ADD KEY `allow_rss` (`allow_rss`),
  ADD KEY `allow_rss_dzen` (`allow_rss_dzen`),
  ADD KEY `news_read` (`news_read`);

--
-- Tablo için indeksler `dle_post_extras_cats`
--
ALTER TABLE `dle_post_extras_cats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Tablo için indeksler `dle_post_log`
--
ALTER TABLE `dle_post_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `expires` (`expires`);

--
-- Tablo için indeksler `dle_post_pass`
--
ALTER TABLE `dle_post_pass`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`);

--
-- Tablo için indeksler `dle_question`
--
ALTER TABLE `dle_question`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_read_log`
--
ALTER TABLE `dle_read_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `ip` (`ip`);

--
-- Tablo için indeksler `dle_redirects`
--
ALTER TABLE `dle_redirects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`enabled`);

--
-- Tablo için indeksler `dle_rss`
--
ALTER TABLE `dle_rss`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_rssinform`
--
ALTER TABLE `dle_rssinform`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_sendlog`
--
ALTER TABLE `dle_sendlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `date` (`date`),
  ADD KEY `flag` (`flag`);

--
-- Tablo için indeksler `dle_social_login`
--
ALTER TABLE `dle_social_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sid` (`sid`);

--
-- Tablo için indeksler `dle_spam_log`
--
ALTER TABLE `dle_spam_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `is_spammer` (`is_spammer`);

--
-- Tablo için indeksler `dle_static`
--
ALTER TABLE `dle_static`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `disable_search` (`disable_search`);
ALTER TABLE `dle_static` ADD FULLTEXT KEY `template` (`template`);

--
-- Tablo için indeksler `dle_static_files`
--
ALTER TABLE `dle_static_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `static_id` (`static_id`),
  ADD KEY `onserver` (`onserver`),
  ADD KEY `author` (`author`);

--
-- Tablo için indeksler `dle_storage`
--
ALTER TABLE `dle_storage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`enabled`);

--
-- Tablo için indeksler `dle_subscribe`
--
ALTER TABLE `dle_subscribe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `dle_tags`
--
ALTER TABLE `dle_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `tag` (`tag`);

--
-- Tablo için indeksler `dle_tags_az`
--
ALTER TABLE `dle_tags_az`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `tag` (`tag`);

--
-- Tablo için indeksler `dle_tags_ru`
--
ALTER TABLE `dle_tags_ru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `tag` (`tag`);

--
-- Tablo için indeksler `dle_twofactor`
--
ALTER TABLE `dle_twofactor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pin` (`pin`),
  ADD KEY `date` (`date`);

--
-- Tablo için indeksler `dle_usergroups`
--
ALTER TABLE `dle_usergroups`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_users`
--
ALTER TABLE `dle_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `dle_users_delete`
--
ALTER TABLE `dle_users_delete`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `dle_views`
--
ALTER TABLE `dle_views`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `dle_vote`
--
ALTER TABLE `dle_vote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approve` (`approve`);

--
-- Tablo için indeksler `dle_vote_result`
--
ALTER TABLE `dle_vote_result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer` (`answer`),
  ADD KEY `vote_id` (`vote_id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `name` (`name`);

--
-- Tablo için indeksler `dle_xfsearch`
--
ALTER TABLE `dle_xfsearch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `tagname` (`tagname`),
  ADD KEY `tagvalue` (`tagvalue`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `dle_admin_logs`
--
ALTER TABLE `dle_admin_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- Tablo için AUTO_INCREMENT değeri `dle_admin_sections`
--
ALTER TABLE `dle_admin_sections`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `dle_banned`
--
ALTER TABLE `dle_banned`
  MODIFY `id` smallint NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_banners`
--
ALTER TABLE `dle_banners`
  MODIFY `id` smallint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `dle_banners_logs`
--
ALTER TABLE `dle_banners_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_banners_rubrics`
--
ALTER TABLE `dle_banners_rubrics`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_category`
--
ALTER TABLE `dle_category`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tablo için AUTO_INCREMENT değeri `dle_comments`
--
ALTER TABLE `dle_comments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_comments_files`
--
ALTER TABLE `dle_comments_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_comment_rating_log`
--
ALTER TABLE `dle_comment_rating_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_complaint`
--
ALTER TABLE `dle_complaint`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_conversations`
--
ALTER TABLE `dle_conversations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_conversations_messages`
--
ALTER TABLE `dle_conversations_messages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_downloads_log`
--
ALTER TABLE `dle_downloads_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_email`
--
ALTER TABLE `dle_email`
  MODIFY `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `dle_files`
--
ALTER TABLE `dle_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_flood`
--
ALTER TABLE `dle_flood`
  MODIFY `f_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_ignore_list`
--
ALTER TABLE `dle_ignore_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_images`
--
ALTER TABLE `dle_images`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Tablo için AUTO_INCREMENT değeri `dle_links`
--
ALTER TABLE `dle_links`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_login_log`
--
ALTER TABLE `dle_login_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_logs`
--
ALTER TABLE `dle_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_lostdb`
--
ALTER TABLE `dle_lostdb`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_mail_log`
--
ALTER TABLE `dle_mail_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_metatags`
--
ALTER TABLE `dle_metatags`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_multilanguage`
--
ALTER TABLE `dle_multilanguage`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `dle_notice`
--
ALTER TABLE `dle_notice`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_plugins`
--
ALTER TABLE `dle_plugins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `dle_plugins_files`
--
ALTER TABLE `dle_plugins_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- Tablo için AUTO_INCREMENT değeri `dle_plugins_logs`
--
ALTER TABLE `dle_plugins_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_poll`
--
ALTER TABLE `dle_poll`
  MODIFY `id` mediumint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_poll_log`
--
ALTER TABLE `dle_poll_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_post`
--
ALTER TABLE `dle_post`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Tablo için AUTO_INCREMENT değeri `dle_post_extras`
--
ALTER TABLE `dle_post_extras`
  MODIFY `eid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Tablo için AUTO_INCREMENT değeri `dle_post_extras_cats`
--
ALTER TABLE `dle_post_extras_cats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Tablo için AUTO_INCREMENT değeri `dle_post_log`
--
ALTER TABLE `dle_post_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_post_pass`
--
ALTER TABLE `dle_post_pass`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_question`
--
ALTER TABLE `dle_question`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_read_log`
--
ALTER TABLE `dle_read_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_redirects`
--
ALTER TABLE `dle_redirects`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_rss`
--
ALTER TABLE `dle_rss`
  MODIFY `id` smallint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `dle_rssinform`
--
ALTER TABLE `dle_rssinform`
  MODIFY `id` smallint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `dle_sendlog`
--
ALTER TABLE `dle_sendlog`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_social_login`
--
ALTER TABLE `dle_social_login`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_spam_log`
--
ALTER TABLE `dle_spam_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_static`
--
ALTER TABLE `dle_static`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `dle_static_files`
--
ALTER TABLE `dle_static_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_storage`
--
ALTER TABLE `dle_storage`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_subscribe`
--
ALTER TABLE `dle_subscribe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_tags`
--
ALTER TABLE `dle_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `dle_tags_az`
--
ALTER TABLE `dle_tags_az`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_tags_ru`
--
ALTER TABLE `dle_tags_ru`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_twofactor`
--
ALTER TABLE `dle_twofactor`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_usergroups`
--
ALTER TABLE `dle_usergroups`
  MODIFY `id` smallint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `dle_users`
--
ALTER TABLE `dle_users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `dle_users_delete`
--
ALTER TABLE `dle_users_delete`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_views`
--
ALTER TABLE `dle_views`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_vote`
--
ALTER TABLE `dle_vote`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `dle_vote_result`
--
ALTER TABLE `dle_vote_result`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dle_xfsearch`
--
ALTER TABLE `dle_xfsearch`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
