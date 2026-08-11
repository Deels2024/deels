-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 01, 2018 at 09:30 PM
-- Server version: 5.6.35
-- PHP Version: 7.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `helpus`
--

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
CREATE TABLE `campaigns` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `campaign_owner_commission` decimal(8,2) DEFAULT NULL,
  `goal` decimal(8,2) DEFAULT NULL,
  `min_amount` decimal(8,2) DEFAULT NULL,
  `max_amount` decimal(8,2) DEFAULT NULL,
  `recommended_amount` decimal(8,2) DEFAULT NULL,
  `amount_prefilled` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_method` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `views` int(11) DEFAULT NULL,
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `country_id` mediumint(9) DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_funded` tinyint(4) DEFAULT NULL,
  `is_staff_picks` tinyint(4) DEFAULT NULL,
  `is_feature` tinyint(4) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `user_id`, `category_id`, `title`, `slug`, `short_description`, `description`, `campaign_owner_commission`, `goal`, `min_amount`, `max_amount`, `recommended_amount`, `amount_prefilled`, `end_method`, `views`, `video`, `feature_image`, `status`, `country_id`, `address`, `is_funded`, `is_staff_picks`, `is_feature`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Help a homeless, need a home.', 'help-a-homeless', 'Short Description Updated', '<p>Lorem ipsum dolor sit amet, ea postea essent voluptaria mel, ad eos quod oportere. Dicant virtute mel ne, has no magna senserit. No duo prima vocent sapientem, pro te quis elaboraret contentiones. Quaestio explicari qui ea, ut vel legendos interpretaris. Prima ocurreret his ei, vix odio reprehendunt id, mel esse adipiscing ei. Ex salutandi expetendis nec, vix accusata inimicus democritum ea.</p>\r\n\r\n<p>Ei nominavi lucilius per. Putent audire vituperatoribus ad quo, ludus maiorum tacimates ad mea. Erant dolore option ius ea. Sit sumo ullum veritus et, nam debet dicit ornatus at, cu vix omnes option. Laudem delectus mediocrem nec an.</p>\r\n\r\n<p>Vis odio veri ea. Nullam scaevola ex duo, mucius nominavi omittantur ad pro, ne impedit expetendis vituperatoribus vix. At eros graeco splendide vim, odio facer malorum in cum. Vis at habeo illum utinam, ad vel timeam blandit, nullam malorum utroque ut usu. At per graeci viderer.</p>\r\n\r\n<p>Eu sed epicuri percipit. Mutat adhuc ad quo. Primis insolens menandri eos an, at usu erant suscipiantur. At nulla nostrum pro, vix assum choro forensibus eu. Illud consul volutpat sea ea.</p>\r\n\r\n<p>Cu usu accusata philosophia, eu mel alii epicurei electram. Ut omnis salutatus appellantur cum, fuisset iudicabit pri no. Officiis delicata ad duo, cu usu dicit electram. Partiendo tincidunt referrentur in nec. No modo vitae nam, mei no praesent recteque.</p>\r\n\r\n<p><img alt=\"people.jpg\" src=\"http://localhost/helpus/source/public/uploads/images/people.jpg\" style=\"width: 1280px; height: 853px;\" /></p>', '80.00', '90000.00', NULL, NULL, NULL, '10,20,30,40', 'end_date', NULL, NULL, '32', 1, 17, '4919 Arbor CourtCasper, WY 82601', 1, 1, NULL, '2017-03-18', '2022-03-18', '2017-03-17 15:00:37', '2018-05-22 13:48:37'),
(2, 1, 2, 'New food enovation, support us', 'new-food-enovation-support-us', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s.', '<p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>\r\n\r\n<p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>', '80.00', '300000.00', NULL, NULL, '40.00', '10,20,30,40', 'end_date', NULL, NULL, '29', 1, 240, '4919 Arbor CourtCasper, WY 82601', NULL, NULL, NULL, '2017-03-22', '2023-05-31', '2017-03-22 10:52:43', '2018-05-22 13:33:14'),
(3, 1, 1, 'We need to keep forest green to save human', 'save-forest-save-human', 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.', '<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using &#39;Content here</p>\r\n\r\n<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using &#39;Content here</p>\r\n\r\n<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using &#39;Content here</p>', '80.00', '450000.00', NULL, NULL, '20.00', '10,30,50,60', 'end_date', NULL, NULL, '28', 1, 2, '4919 Arbor CourtCasper, WY 82601', NULL, NULL, NULL, '2017-03-22', '2021-06-30', '2017-03-22 11:12:33', '2018-05-22 13:16:30'),
(4, 5, 5, 'Dors Telescope, 10 times powerful then normal', 'new-botn-the-perfect-sweater-and-jogger-for-kids', 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintoc', '<p>Lorem ipsum dolor sit amet, ea postea essent voluptaria mel, ad eos quod oportere. Dicant virtute mel ne, has no magna senserit. No duo prima vocent sapientem, pro te quis elaboraret contentiones. Quaestio explicari qui ea, ut vel legendos interpretaris. Prima ocurreret his ei, vix odio reprehendunt id, mel esse adipiscing ei. Ex salutandi expetendis nec, vix accusata inimicus democritum ea.</p>\r\n\r\n<p>Ei nominavi lucilius per. Putent audire vituperatoribus ad quo, ludus maiorum tacimates ad mea. Erant dolore option ius ea. Sit sumo ullum veritus et, nam debet dicit ornatus at, cu vix omnes option. Laudem delectus mediocrem nec an.</p>\r\n\r\n<p>Vis odio veri ea. Nullam scaevola ex duo, mucius nominavi omittantur ad pro, ne impedit expetendis vituperatoribus vix. At eros graeco splendide vim, odio facer malorum in cum. Vis at habeo illum utinam, ad vel timeam blandit, nullam malorum utroque ut usu. At per graeci viderer.</p>\r\n\r\n<p>Eu sed epicuri percipit. Mutat adhuc ad quo. Primis insolens menandri eos an, at usu erant suscipiantur. At nulla nostrum pro, vix assum choro forensibus eu. Illud consul volutpat sea ea.</p>\r\n\r\n<p>Cu usu accusata philosophia, eu mel alii epicurei electram. Ut omnis salutatus appellantur cum, fuisset iudicabit pri no. Officiis delicata ad duo, cu usu dicit electram. Partiendo tincidunt referrentur in nec. No modo vitae nam, mei no praesent recteque.</p>', '80.00', '1200.00', NULL, NULL, '40.00', '20,40,60,80', 'goal_achieve', NULL, NULL, '39', 1, 235, 'London, UK', NULL, NULL, NULL, '2017-04-02', '2022-06-21', '2017-04-02 09:36:14', '2018-05-22 14:01:04'),
(5, 5, 3, 'Old car re-production', 'uneatable-premium-quality-t-shirt', 'randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem', '<div>\r\n<h2>Why do we use it?</h2>\r\n\r\n<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using &#39;Content here, content here&#39;, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for &#39;lorem ipsum&#39; will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>\r\n</div>\r\n\r\n<div>\r\n<h2>Where does it come from?</h2>\r\n\r\n<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of &quot;de Finibus Bonorum et Malorum&quot; (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, &quot;Lorem ipsum dolor sit amet..&quot;, comes from a line in section 1.10.32.</p>\r\n\r\n<p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from &quot;de Finibus Bonorum et Malorum&quot; by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p>\r\n\r\n<p><img alt=\"old-car-2.jpg\" src=\"http://localhost/helpus/source/public/uploads/images/old-car-2.jpg\" style=\"width: 1280px; height: 853px;\" /></p>\r\n</div>\r\n\r\n<h2>Where can I get some?</h2>\r\n\r\n<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don&#39;t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn&#39;t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>\r\n\r\n<p><img alt=\"old-car.jpg\" src=\"http://localhost/helpus/source/public/uploads/images/old-car.jpg\" style=\"width: 1280px; height: 853px;\" /></p>', '80.00', '500.00', NULL, NULL, '40.00', '20,40,60,80', 'goal_achieve', NULL, NULL, '38', 1, 16, 'Armenia', NULL, 1, NULL, '2017-04-02', '2023-12-29', '2017-04-02 10:23:05', '2018-05-22 13:55:48'),
(6, 6, 1, 'Save the animals to live human healthy', 'modern-gentleman-likes-handcarft-dress-shoes', 'We should show respect to all of the animals to protect our environment and the world', '<p>Lorem ipsum dolor sit amet, ea postea essent voluptaria mel, ad eos quod oportere. Dicant virtute mel ne, has no magna senserit. No duo prima vocent sapientem, pro te quis elaboraret contentiones. Quaestio explicari qui ea, ut vel legendos interpretaris. Prima ocurreret his ei, vix odio reprehendunt id, mel esse adipiscing ei. Ex salutandi expetendis nec, vix accusata inimicus democritum ea.</p>\r\n\r\n<p><img alt=\"animal-1.jpeg\" src=\"http://localhost/helpus/source/public/uploads/images/animal-1.jpeg\" style=\"width: 1280px; height: 720px;\" /></p>\r\n\r\n<p>Ei nominavi lucilius per. Putent audire vituperatoribus ad quo, ludus maiorum tacimates ad mea. Erant dolore option ius ea. Sit sumo ullum veritus et, nam debet dicit ornatus at, cu vix omnes option. Laudem delectus mediocrem nec an.</p>\r\n\r\n<p>Vis odio veri ea. Nullam scaevola ex duo, mucius nominavi omittantur ad pro, ne impedit expetendis vituperatoribus vix. At eros graeco splendide vim, odio facer malorum in cum. Vis at habeo illum utinam, ad vel timeam blandit, nullam malorum utroque ut usu. At per graeci viderer.</p>\r\n\r\n<p><img alt=\"animal-2.jpeg\" src=\"http://localhost/helpus/source/public/uploads/images/animal-2.jpeg\" style=\"width: 1280px; height: 851px;\" /></p>\r\n\r\n<p>Eu sed epicuri percipit. Mutat adhuc ad quo. Primis insolens menandri eos an, at usu erant suscipiantur. At nulla nostrum pro, vix assum choro forensibus eu. Illud consul volutpat sea ea.</p>\r\n\r\n<p>Cu usu accusata philosophia, eu mel alii epicurei electram. Ut omnis salutatus appellantur cum, fuisset iudicabit pri no. Officiis delicata ad duo, cu usu dicit electram. Partiendo tincidunt referrentur in nec. No modo vitae nam, mei no praesent recteque.</p>', '80.00', '500.00', NULL, NULL, '50.00', '30,50,70,90', 'goal_achieve', NULL, NULL, '21', 1, 84, 'Berlin, Germany', NULL, NULL, 0, '2017-04-04', '2023-12-30', '2017-04-03 12:23:13', '2018-05-22 12:28:32'),
(7, 6, 8, 'World\'s most efficient small pc', 'worlds-most-efficient-small-pc', 'A comedy photography project showing about what really happened in USA last night.', '<h2>Where can I get some?</h2>\r\n\r\n<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don&#39;t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn&#39;t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>\r\n\r\n<h2>Where can I get some?</h2>\r\n\r\n<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don&#39;t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn&#39;t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>\r\n\r\n<h2>Where can I get some?</h2>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don&#39;t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn&#39;t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>\r\n\r\n<h2>Where can I get some?</h2>\r\n\r\n<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don&#39;t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn&#39;t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>\r\n\r\n<h2>Where can I get some?</h2>\r\n\r\n<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don&#39;t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn&#39;t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>', '80.00', '200.00', NULL, NULL, '20.00', '10,20,30,40', 'end_date', NULL, 'https://www.youtube.com/watch?v=8b5-iEnW70k', '20', 1, 240, 'California, USA', 1, 1, NULL, '2017-04-05', '2023-04-12', '2017-04-05 11:26:02', '2018-05-22 12:17:41'),
(8, 6, 1, 'The prospective: A natural podecast', 'the-prospective-a-natural-podecast', 'For its fourth installment, the serialized Instagram anthology makes a leap into podcasting.', '<div>\r\n<h2>Why do we use it?</h2>\r\n<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>\r\n</div><p><br><br></p><div>\r\n<h2>Where does it come from?</h2>\r\n<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.</p><p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p>\r\n</div><p>\r\n<br></p><h2>Where can I get some?</h2><p>\r\n<br></p><p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p><p><br></p>', '80.00', '200.00', NULL, NULL, '20.00', '10,20,30,40', 'end_date', NULL, 'https://www.youtube.com/watch?v=8b5-iEnW70k', '', 1, 25, 'Rio de Janeiro, Brazil', 1, 0, NULL, '2017-03-31', '2017-04-11', '2017-04-09 12:55:01', '2018-04-08 05:35:38'),
(9, 6, 5, 'Innovative manual watch', 'innovative-manual-watch', 'Best Concealed Carry Pocket Knives for Everyday Carry - Lifetime Warranty - Bomber & Company engineers urban survival & paracord gear.', '<div>\r\n<h2>Why do we use it?</h2>\r\n\r\n<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using &#39;Content here, content here&#39;, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for &#39;lorem ipsum&#39; will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>\r\n</div>\r\n\r\n<p><br />\r\n&nbsp;</p>\r\n\r\n<div>\r\n<h2>Where does it come from?</h2>\r\n\r\n<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of &quot;de Finibus Bonorum et Malorum&quot; (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, &quot;Lorem ipsum dolor sit amet..&quot;, comes from a line in section 1.10.32.</p>\r\n\r\n<p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from &quot;de Finibus Bonorum et Malorum&quot; by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p>\r\n</div>\r\n\r\n<p><img alt=\"penknife-pocket-knife-blade-serrated-37863.jpeg\" src=\"http://localhost/helpus/source/public/uploads/images/penknife-pocket-knife-blade-serrated-37863.jpeg\" style=\"width: 1280px; height: 853px;\" /></p>\r\n\r\n<h2>Where can I get some?</h2>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don&#39;t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn&#39;t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>\r\n\r\n<p>&nbsp;</p>', '80.00', '300.00', NULL, NULL, '20.00', '10,20,30,40', 'goal_achieve', NULL, 'https://www.youtube.com/watch?v=8b5-iEnW70k', '5', 1, 240, 'California, USA', 1, 1, 0, '2017-04-10', '2020-04-12', '2017-04-09 13:05:05', '2018-05-20 12:56:42'),
(10, 1, 4, 'Food for unhealthy child', 'education-for-street-child', 'We are working to feeding child who can\'t afford food,  a single donation will make smile to the child.', '<p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>\r\n\r\n<p><img alt=\"child-3.jpg\" src=\"http://localhost/helpus/source/public/uploads/images/child-3.jpg\" style=\"width: 1280px; height: 853px;\" /></p>\r\n\r\n<p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>', '80.00', '900.00', NULL, NULL, '50.00', '10,30,50,60,70,80,90,100', 'end_date', NULL, NULL, '26', 1, 85, 'Ghana city, Ghana', 0, NULL, NULL, '2017-04-14', '2023-12-29', '2017-04-13 13:32:18', '2018-05-22 12:55:20'),
(13, 1, 4, 'Street child need education', 'education-for-street-child-copy', 'We are working with child education,  a single donation will make smile to the child.', '<p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>\r\n\r\n<p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>', '80.00', '900.00', NULL, NULL, '50.00', '10,30,50,60', 'end_date', NULL, NULL, '24', 1, 85, 'Ghana city, Ghana', 0, NULL, 0, '2017-04-14', '2023-12-29', '2017-04-13 13:32:18', '2018-05-22 12:31:59'),
(14, 5, 5, 'Intelligent robots will be your home assistance', 'intelligent-robots-will-be-your-home-assistance', 'An intelligent robots will do your home light tasks, keep floor clean', '<h2>What is Lorem Ipsum?</h2>\r\n\r\n<p><strong>Lorem Ipsum</strong>&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>\r\n\r\n<h2>Why do we use it?</h2>\r\n\r\n<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using &#39;Content here, content here&#39;, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for &#39;lorem ipsum&#39; will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<h2>Where does it come from?</h2>\r\n\r\n<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of &quot;de Finibus Bonorum et Malorum&quot; (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, &quot;Lorem ipsum dolor sit amet..&quot;, comes from a line in section 1.10.32.</p>', '80.00', '900.00', NULL, NULL, '50.00', '25,30,40,45,50,60,65', 'goal_achieve', NULL, NULL, '12', 1, 121, '#f5, #road 9, sector-3, demo city', 0, NULL, 0, '2018-05-20', '2020-03-31', '2018-05-19 12:06:23', '2018-05-19 13:07:42'),
(16, 5, 2, 'Coffee maker production', 'coffee-maker-production', 'This coffee maker will be impressed you, it will not required any water, coffee bean, milk or sugar. you can buy it to see how you can wast your money.', '<p>dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><img alt=\"coffee-802057_1280.jpg\" src=\"http://localhost/helpus/source/public/uploads/images/coffee-802057-1280.jpg\" style=\"width: 1280px; height: 853px;\" />In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum.</p>\r\n\r\n<p><img alt=\"clock-1850066_1280.jpg\" src=\"http://localhost/helpus/source/public/uploads/images/clock-1850066-1280.jpg\" style=\"width: 1280px; height: 853px;\" /></p>\r\n\r\n<p>Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc,</p>', '80.00', '18000.00', NULL, NULL, '60.00', '50,60,70,85,90,100,110,120', 'goal_achieve', NULL, NULL, '16', 1, 11, '#f5, #road 9, sector-3, melbourne', 0, NULL, 1, '2018-05-21', '2020-04-30', '2018-05-20 12:56:21', '2018-05-20 12:56:42'),
(17, 5, 4, 'Pain Less injection', 'pain-less-injection', 'This painless injection will cure you fast', '<p>Lorem ipsum dolor sit amet, ea postea essent voluptaria mel, ad eos quod oportere. Dicant virtute mel ne, has no magna senserit. No duo prima vocent sapientem, pro te quis elaboraret contentiones. Quaestio explicari qui ea, ut vel legendos interpretaris. Prima ocurreret his ei, vix odio reprehendunt id, mel esse adipiscing ei. Ex salutandi expetendis nec, vix accusata inimicus democritum ea.</p>\r\n\r\n<p>Ei nominavi lucilius per. Putent audire vituperatoribus ad quo, ludus maiorum tacimates ad mea. Erant dolore option ius ea. Sit sumo ullum veritus et, nam debet dicit ornatus at, cu vix omnes option. Laudem delectus mediocrem nec an.</p>\r\n\r\n<p>Vis odio veri ea. Nullam scaevola ex duo, mucius nominavi omittantur ad pro, ne impedit expetendis vituperatoribus vix. At eros graeco splendide vim, odio facer malorum in cum. Vis at habeo illum utinam, ad vel timeam blandit, nullam malorum utroque ut usu. At per graeci viderer.</p>', '80.00', '28000.00', NULL, NULL, '150.00', '100,150,200,250,300,350,400,450', 'goal_achieve', NULL, NULL, '40', 1, 25, 'rio de jenerio, 303 avenue road', 0, NULL, 0, '2018-05-23', '2021-03-29', '2018-05-22 14:07:59', '2018-05-22 14:11:11'),
(18, 5, 5, 'New office tobs', 'new-office-tobs', 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintoc', '<p>Lorem ipsum dolor sit amet, ea postea essent voluptaria mel, ad eos quod oportere. Dicant virtute mel ne, has no magna senserit. No duo prima vocent sapientem, pro te quis elaboraret contentiones. Quaestio explicari qui ea, ut vel legendos interpretaris. Prima ocurreret his ei, vix odio reprehendunt id, mel esse adipiscing ei. Ex salutandi expetendis nec, vix accusata inimicus democritum ea.</p>\r\n\r\n<p>Ei nominavi lucilius per. Putent audire vituperatoribus ad quo, ludus maiorum tacimates ad mea. Erant dolore option ius ea. Sit sumo ullum veritus et, nam debet dicit ornatus at, cu vix omnes option. Laudem delectus mediocrem nec an.</p>\r\n\r\n<p>Vis odio veri ea. Nullam scaevola ex duo, mucius nominavi omittantur ad pro, ne impedit expetendis vituperatoribus vix. At eros graeco splendide vim, odio facer malorum in cum. Vis at habeo illum utinam, ad vel timeam blandit, nullam malorum utroque ut usu. At per graeci viderer.</p>\r\n\r\n<p>Eu sed epicuri percipit. Mutat adhuc ad quo. Primis insolens menandri eos an, at usu erant suscipiantur. At nulla nostrum pro, vix assum choro forensibus eu. Illud consul volutpat sea ea.</p>\r\n\r\n<p>Cu usu accusata philosophia, eu mel alii epicurei electram. Ut omnis salutatus appellantur cum, fuisset iudicabit pri no. Officiis delicata ad duo, cu usu dicit electram. Partiendo tincidunt referrentur in nec. No modo vitae nam, mei no praesent recteque.</p>', '80.00', '1200.00', NULL, NULL, '40.00', '20,40,60,80', 'goal_achieve', NULL, NULL, '41', 1, 235, 'London, UK', NULL, NULL, NULL, '2017-04-02', '2022-06-21', '2017-04-02 09:36:14', '2018-05-22 14:16:29'),
(19, 5, 5, 'Pets healthy', 'pets-healthy', 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintoc', '<p>Lorem ipsum dolor sit amet, ea postea essent voluptaria mel, ad eos quod oportere. Dicant virtute mel ne, has no magna senserit. No duo prima vocent sapientem, pro te quis elaboraret contentiones. Quaestio explicari qui ea, ut vel legendos interpretaris. Prima ocurreret his ei, vix odio reprehendunt id, mel esse adipiscing ei. Ex salutandi expetendis nec, vix accusata inimicus democritum ea.</p>\r\n\r\n<p>Ei nominavi lucilius per. Putent audire vituperatoribus ad quo, ludus maiorum tacimates ad mea. Erant dolore option ius ea. Sit sumo ullum veritus et, nam debet dicit ornatus at, cu vix omnes option. Laudem delectus mediocrem nec an.</p>\r\n\r\n<p>Vis odio veri ea. Nullam scaevola ex duo, mucius nominavi omittantur ad pro, ne impedit expetendis vituperatoribus vix. At eros graeco splendide vim, odio facer malorum in cum. Vis at habeo illum utinam, ad vel timeam blandit, nullam malorum utroque ut usu. At per graeci viderer.</p>\r\n\r\n<p>Eu sed epicuri percipit. Mutat adhuc ad quo. Primis insolens menandri eos an, at usu erant suscipiantur. At nulla nostrum pro, vix assum choro forensibus eu. Illud consul volutpat sea ea.</p>\r\n\r\n<p>Cu usu accusata philosophia, eu mel alii epicurei electram. Ut omnis salutatus appellantur cum, fuisset iudicabit pri no. Officiis delicata ad duo, cu usu dicit electram. Partiendo tincidunt referrentur in nec. No modo vitae nam, mei no praesent recteque.</p>', '80.00', '1200.00', NULL, NULL, '40.00', '20,40,60,80', 'goal_achieve', NULL, NULL, '42', 1, 235, 'London, UK', NULL, NULL, NULL, '2017-04-02', '2023-09-21', '2017-04-02 09:36:14', '2018-05-22 14:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `slug`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Environment', 'environment', '', '2017-03-05 12:23:32', '2018-05-30 13:53:12'),
(2, 'Foods and Drinks', 'foods-and-drinks', '', '2017-03-06 12:49:10', '2017-04-02 09:08:53'),
(3, 'Travels & Outdoors', 'travels-outdoors', '', '2017-04-02 09:07:16', '2017-04-02 09:07:16'),
(4, 'Nonprofit', 'nonprofit', '', '2017-04-02 09:13:22', '2017-04-02 09:13:22'),
(5, 'Innovation', 'innovation', '', '2017-04-02 09:15:12', '2017-04-02 09:15:12'),
(6, 'Health and Fitness', 'health-and-fitness', '', '2017-04-02 09:15:58', '2017-04-02 09:15:58'),
(7, 'Fashion', 'fashion', '', '2017-04-02 09:17:00', '2017-04-02 09:17:00'),
(8, 'Community Projects', 'community-projects', '', '2017-04-02 09:19:22', '2017-04-02 09:19:22');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `author_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `approved` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `campaign_id`, `comment_id`, `author_name`, `author_email`, `author_ip`, `comment`, `approved`, `created_at`, `updated_at`) VALUES
(1, 0, 5, 0, 'Arnold', 'arnold@demo.com', '::1', 'Hello, is there any rewards available to your campaign?', '1', '2018-05-15 13:07:22', '2018-05-15 13:07:22'),
(2, 6, 5, 0, 'Kimberly A. Harrison', 'admin@demo.com', '::1', 'Well, Let us do something great, I will donate to your organization.', '1', '2018-05-15 13:08:11', '2018-05-15 13:08:11'),
(3, 6, 5, 2, 'Kimberly A. Harrison', 'admin@demo.com', '::1', 'Thank you, I will look over there', '1', '2018-05-15 13:16:04', '2018-05-15 13:16:04'),
(4, 6, 5, 2, 'Kimberly A. Harrison', 'admin@demo.com', '::1', 'Yes, here  I am commenting again to know more details :)', '1', '2018-05-15 13:16:44', '2018-05-15 13:16:44'),
(6, 6, 9, 0, 'Kimberly A. Harrison', 'admin@demo.com', '::1', 'when You will send the rewards product?', '1', '2018-05-18 14:45:28', '2018-05-18 14:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` int(10) UNSIGNED NOT NULL,
  `capital` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `citizenship` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_sub_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_symbol` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_decimals` int(11) DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iso_3166_2` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `iso_3166_3` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `region_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sub_region_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `eea` tinyint(1) NOT NULL DEFAULT '0',
  `calling_code` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `capital`, `citizenship`, `country_code`, `currency`, `currency_code`, `currency_sub_unit`, `currency_symbol`, `currency_decimals`, `full_name`, `iso_3166_2`, `iso_3166_3`, `name`, `region_code`, `sub_region_code`, `eea`, `calling_code`, `flag`) VALUES
(1, 'Kabul', 'Afghan', '004', 'afghani', 'AFN', 'pul', '؋', 2, 'Islamic Republic of Afghanistan', 'AF', 'AFG', 'Afghanistan', '142', '034', 0, '93', 'AF.png'),
(2, 'Tirana', 'Albanian', '008', 'lek', 'ALL', '(qindar (pl. qindarka))', 'Lek', 2, 'Republic of Albania', 'AL', 'ALB', 'Albania', '150', '039', 0, '355', 'AL.png'),
(3, 'Antartica', 'of Antartica', '010', '', '', '', '', 2, 'Antarctica', 'AQ', 'ATA', 'Antarctica', '', '', 0, '672', 'AQ.png'),
(4, 'Algiers', 'Algerian', '012', 'Algerian dinar', 'DZD', 'centime', 'DZD', 2, 'People’s Democratic Republic of Algeria', 'DZ', 'DZA', 'Algeria', '002', '015', 0, '213', 'DZ.png'),
(5, 'Pago Pago', 'American Samoan', '016', 'US dollar', 'USD', 'cent', '$', 2, 'Territory of American', 'AS', 'ASM', 'American Samoa', '009', '061', 0, '1', 'AS.png'),
(6, 'Andorra la Vella', 'Andorran', '020', 'euro', 'EUR', 'cent', '€', 2, 'Principality of Andorra', 'AD', 'AND', 'Andorra', '150', '039', 0, '376', 'AD.png'),
(7, 'Luanda', 'Angolan', '024', 'kwanza', 'AOA', 'cêntimo', 'Kz', 2, 'Republic of Angola', 'AO', 'AGO', 'Angola', '002', '017', 0, '244', 'AO.png'),
(8, 'St John’s', 'of Antigua and Barbuda', '028', 'East Caribbean dollar', 'XCD', 'cent', '$', 2, 'Antigua and Barbuda', 'AG', 'ATG', 'Antigua and Barbuda', '019', '029', 0, '1', 'AG.png'),
(9, 'Baku', 'Azerbaijani', '031', 'Azerbaijani manat', 'AZN', 'kepik (inv.)', 'ман', 2, 'Republic of Azerbaijan', 'AZ', 'AZE', 'Azerbaijan', '142', '145', 0, '994', 'AZ.png'),
(10, 'Buenos Aires', 'Argentinian', '032', 'Argentine peso', 'ARS', 'centavo', '$', 2, 'Argentine Republic', 'AR', 'ARG', 'Argentina', '019', '005', 0, '54', 'AR.png'),
(11, 'Canberra', 'Australian', '036', 'Australian dollar', 'AUD', 'cent', '$', 2, 'Commonwealth of Australia', 'AU', 'AUS', 'Australia', '009', '053', 0, '61', 'AU.png'),
(12, 'Vienna', 'Austrian', '040', 'euro', 'EUR', 'cent', '€', 2, 'Republic of Austria', 'AT', 'AUT', 'Austria', '150', '155', 1, '43', 'AT.png'),
(13, 'Nassau', 'Bahamian', '044', 'Bahamian dollar', 'BSD', 'cent', '$', 2, 'Commonwealth of the Bahamas', 'BS', 'BHS', 'Bahamas', '019', '029', 0, '1', 'BS.png'),
(14, 'Manama', 'Bahraini', '048', 'Bahraini dinar', 'BHD', 'fils (inv.)', 'BHD', 3, 'Kingdom of Bahrain', 'BH', 'BHR', 'Bahrain', '142', '145', 0, '973', 'BH.png'),
(15, 'Dhaka', 'Bangladeshi', '050', 'taka (inv.)', 'BDT', 'poisha (inv.)', 'BDT', 2, 'People’s Republic of Bangladesh', 'BD', 'BGD', 'Bangladesh', '142', '034', 0, '880', 'BD.png'),
(16, 'Yerevan', 'Armenian', '051', 'dram (inv.)', 'AMD', 'luma', 'AMD', 2, 'Republic of Armenia', 'AM', 'ARM', 'Armenia', '142', '145', 0, '374', 'AM.png'),
(17, 'Bridgetown', 'Barbadian', '052', 'Barbados dollar', 'BBD', 'cent', '$', 2, 'Barbados', 'BB', 'BRB', 'Barbados', '019', '029', 0, '1', 'BB.png'),
(18, 'Brussels', 'Belgian', '056', 'euro', 'EUR', 'cent', '€', 2, 'Kingdom of Belgium', 'BE', 'BEL', 'Belgium', '150', '155', 1, '32', 'BE.png'),
(19, 'Hamilton', 'Bermudian', '060', 'Bermuda dollar', 'BMD', 'cent', '$', 2, 'Bermuda', 'BM', 'BMU', 'Bermuda', '019', '021', 0, '1', 'BM.png'),
(20, 'Thimphu', 'Bhutanese', '064', 'ngultrum (inv.)', 'BTN', 'chhetrum (inv.)', 'BTN', 2, 'Kingdom of Bhutan', 'BT', 'BTN', 'Bhutan', '142', '034', 0, '975', 'BT.png'),
(21, 'Sucre (BO1)', 'Bolivian', '068', 'boliviano', 'BOB', 'centavo', '$b', 2, 'Plurinational State of Bolivia', 'BO', 'BOL', 'Bolivia, Plurinational State of', '019', '005', 0, '591', 'BO.png'),
(22, 'Sarajevo', 'of Bosnia and Herzegovina', '070', 'convertible mark', 'BAM', 'fening', 'KM', 2, 'Bosnia and Herzegovina', 'BA', 'BIH', 'Bosnia and Herzegovina', '150', '039', 0, '387', 'BA.png'),
(23, 'Gaborone', 'Botswanan', '072', 'pula (inv.)', 'BWP', 'thebe (inv.)', 'P', 2, 'Republic of Botswana', 'BW', 'BWA', 'Botswana', '002', '018', 0, '267', 'BW.png'),
(24, 'Bouvet island', 'of Bouvet island', '074', '', '', '', 'kr', 2, 'Bouvet Island', 'BV', 'BVT', 'Bouvet Island', '', '', 0, '47', 'BV.png'),
(25, 'Brasilia', 'Brazilian', '076', 'real (pl. reais)', 'BRL', 'centavo', 'R$', 2, 'Federative Republic of Brazil', 'BR', 'BRA', 'Brazil', '019', '005', 0, '55', 'BR.png'),
(26, 'Belmopan', 'Belizean', '084', 'Belize dollar', 'BZD', 'cent', 'BZ$', 2, 'Belize', 'BZ', 'BLZ', 'Belize', '019', '013', 0, '501', 'BZ.png'),
(27, 'Diego Garcia', 'Changosian', '086', 'US dollar', 'USD', 'cent', '$', 2, 'British Indian Ocean Territory', 'IO', 'IOT', 'British Indian Ocean Territory', '', '', 0, '246', 'IO.png'),
(28, 'Honiara', 'Solomon Islander', '090', 'Solomon Islands dollar', 'SBD', 'cent', '$', 2, 'Solomon Islands', 'SB', 'SLB', 'Solomon Islands', '009', '054', 0, '677', 'SB.png'),
(29, 'Road Town', 'British Virgin Islander;', '092', 'US dollar', 'USD', 'cent', '$', 2, 'British Virgin Islands', 'VG', 'VGB', 'Virgin Islands, British', '019', '029', 0, '1', 'VG.png'),
(30, 'Bandar Seri Begawan', 'Bruneian', '096', 'Brunei dollar', 'BND', 'sen (inv.)', '$', 2, 'Brunei Darussalam', 'BN', 'BRN', 'Brunei Darussalam', '142', '035', 0, '673', 'BN.png'),
(31, 'Sofia', 'Bulgarian', '100', 'lev (pl. leva)', 'BGN', 'stotinka', 'лв', 2, 'Republic of Bulgaria', 'BG', 'BGR', 'Bulgaria', '150', '151', 1, '359', 'BG.png'),
(32, 'Yangon', 'Burmese', '104', 'kyat', 'MMK', 'pya', 'K', 2, 'Union of Myanmar/', 'MM', 'MMR', 'Myanmar', '142', '035', 0, '95', 'MM.png'),
(33, 'Bujumbura', 'Burundian', '108', 'Burundi franc', 'BIF', 'centime', 'BIF', 0, 'Republic of Burundi', 'BI', 'BDI', 'Burundi', '002', '014', 0, '257', 'BI.png'),
(34, 'Minsk', 'Belarusian', '112', 'Belarusian rouble', 'BYR', 'kopek', 'p.', 2, 'Republic of Belarus', 'BY', 'BLR', 'Belarus', '150', '151', 0, '375', 'BY.png'),
(35, 'Phnom Penh', 'Cambodian', '116', 'riel', 'KHR', 'sen (inv.)', '៛', 2, 'Kingdom of Cambodia', 'KH', 'KHM', 'Cambodia', '142', '035', 0, '855', 'KH.png'),
(36, 'Yaoundé', 'Cameroonian', '120', 'CFA franc (BEAC)', 'XAF', 'centime', 'FCF', 0, 'Republic of Cameroon', 'CM', 'CMR', 'Cameroon', '002', '017', 0, '237', 'CM.png'),
(37, 'Ottawa', 'Canadian', '124', 'Canadian dollar', 'CAD', 'cent', '$', 2, 'Canada', 'CA', 'CAN', 'Canada', '019', '021', 0, '1', 'CA.png'),
(38, 'Praia', 'Cape Verdean', '132', 'Cape Verde escudo', 'CVE', 'centavo', 'CVE', 2, 'Republic of Cape Verde', 'CV', 'CPV', 'Cape Verde', '002', '011', 0, '238', 'CV.png'),
(39, 'George Town', 'Caymanian', '136', 'Cayman Islands dollar', 'KYD', 'cent', '$', 2, 'Cayman Islands', 'KY', 'CYM', 'Cayman Islands', '019', '029', 0, '1', 'KY.png'),
(40, 'Bangui', 'Central African', '140', 'CFA franc (BEAC)', 'XAF', 'centime', 'CFA', 0, 'Central African Republic', 'CF', 'CAF', 'Central African Republic', '002', '017', 0, '236', 'CF.png'),
(41, 'Colombo', 'Sri Lankan', '144', 'Sri Lankan rupee', 'LKR', 'cent', '₨', 2, 'Democratic Socialist Republic of Sri Lanka', 'LK', 'LKA', 'Sri Lanka', '142', '034', 0, '94', 'LK.png'),
(42, 'N’Djamena', 'Chadian', '148', 'CFA franc (BEAC)', 'XAF', 'centime', 'XAF', 0, 'Republic of Chad', 'TD', 'TCD', 'Chad', '002', '017', 0, '235', 'TD.png'),
(43, 'Santiago', 'Chilean', '152', 'Chilean peso', 'CLP', 'centavo', 'CLP', 0, 'Republic of Chile', 'CL', 'CHL', 'Chile', '019', '005', 0, '56', 'CL.png'),
(44, 'Beijing', 'Chinese', '156', 'renminbi-yuan (inv.)', 'CNY', 'jiao (10)', '¥', 2, 'People’s Republic of China', 'CN', 'CHN', 'China', '142', '030', 0, '86', 'CN.png'),
(45, 'Taipei', 'Taiwanese', '158', 'new Taiwan dollar', 'TWD', 'fen (inv.)', 'NT$', 2, 'Republic of China, Taiwan (TW1)', 'TW', 'TWN', 'Taiwan, Province of China', '142', '030', 0, '886', 'TW.png'),
(46, 'Flying Fish Cove', 'Christmas Islander', '162', 'Australian dollar', 'AUD', 'cent', '$', 2, 'Christmas Island Territory', 'CX', 'CXR', 'Christmas Island', '', '', 0, '61', 'CX.png'),
(47, 'Bantam', 'Cocos Islander', '166', 'Australian dollar', 'AUD', 'cent', '$', 2, 'Territory of Cocos (Keeling) Islands', 'CC', 'CCK', 'Cocos (Keeling) Islands', '', '', 0, '61', 'CC.png'),
(48, 'Santa Fe de Bogotá', 'Colombian', '170', 'Colombian peso', 'COP', 'centavo', '$', 2, 'Republic of Colombia', 'CO', 'COL', 'Colombia', '019', '005', 0, '57', 'CO.png'),
(49, 'Moroni', 'Comorian', '174', 'Comorian franc', 'KMF', '', 'KMF', 0, 'Union of the Comoros', 'KM', 'COM', 'Comoros', '002', '014', 0, '269', 'KM.png'),
(50, 'Mamoudzou', 'Mahorais', '175', 'euro', 'EUR', 'cent', '€', 2, 'Departmental Collectivity of Mayotte', 'YT', 'MYT', 'Mayotte', '002', '014', 0, '262', 'YT.png'),
(51, 'Brazzaville', 'Congolese', '178', 'CFA franc (BEAC)', 'XAF', 'centime', 'FCF', 0, 'Republic of the Congo', 'CG', 'COG', 'Congo', '002', '017', 0, '242', 'CG.png'),
(52, 'Kinshasa', 'Congolese', '180', 'Congolese franc', 'CDF', 'centime', 'CDF', 2, 'Democratic Republic of the Congo', 'CD', 'COD', 'Congo, the Democratic Republic of the', '002', '017', 0, '243', 'CD.png'),
(53, 'Avarua', 'Cook Islander', '184', 'New Zealand dollar', 'NZD', 'cent', '$', 2, 'Cook Islands', 'CK', 'COK', 'Cook Islands', '009', '061', 0, '682', 'CK.png'),
(54, 'San José', 'Costa Rican', '188', 'Costa Rican colón (pl. colones)', 'CRC', 'céntimo', '₡', 2, 'Republic of Costa Rica', 'CR', 'CRI', 'Costa Rica', '019', '013', 0, '506', 'CR.png'),
(55, 'Zagreb', 'Croatian', '191', 'kuna (inv.)', 'HRK', 'lipa (inv.)', 'kn', 2, 'Republic of Croatia', 'HR', 'HRV', 'Croatia', '150', '039', 1, '385', 'HR.png'),
(56, 'Havana', 'Cuban', '192', 'Cuban peso', 'CUP', 'centavo', '₱', 2, 'Republic of Cuba', 'CU', 'CUB', 'Cuba', '019', '029', 0, '53', 'CU.png'),
(57, 'Nicosia', 'Cypriot', '196', 'euro', 'EUR', 'cent', 'CYP', 2, 'Republic of Cyprus', 'CY', 'CYP', 'Cyprus', '142', '145', 1, '357', 'CY.png'),
(58, 'Prague', 'Czech', '203', 'Czech koruna (pl. koruny)', 'CZK', 'halér', 'Kč', 2, 'Czech Republic', 'CZ', 'CZE', 'Czech Republic', '150', '151', 1, '420', 'CZ.png'),
(59, 'Porto Novo (BJ1)', 'Beninese', '204', 'CFA franc (BCEAO)', 'XOF', 'centime', 'XOF', 0, 'Republic of Benin', 'BJ', 'BEN', 'Benin', '002', '011', 0, '229', 'BJ.png'),
(60, 'Copenhagen', 'Danish', '208', 'Danish krone', 'DKK', 'øre (inv.)', 'kr', 2, 'Kingdom of Denmark', 'DK', 'DNK', 'Denmark', '150', '154', 1, '45', 'DK.png'),
(61, 'Roseau', 'Dominican', '212', 'East Caribbean dollar', 'XCD', 'cent', '$', 2, 'Commonwealth of Dominica', 'DM', 'DMA', 'Dominica', '019', '029', 0, '1', 'DM.png'),
(62, 'Santo Domingo', 'Dominican', '214', 'Dominican peso', 'DOP', 'centavo', 'RD$', 2, 'Dominican Republic', 'DO', 'DOM', 'Dominican Republic', '019', '029', 0, '1', 'DO.png'),
(63, 'Quito', 'Ecuadorian', '218', 'US dollar', 'USD', 'cent', '$', 2, 'Republic of Ecuador', 'EC', 'ECU', 'Ecuador', '019', '005', 0, '593', 'EC.png'),
(64, 'San Salvador', 'Salvadoran', '222', 'Salvadorian colón (pl. colones)', 'SVC', 'centavo', '$', 2, 'Republic of El Salvador', 'SV', 'SLV', 'El Salvador', '019', '013', 0, '503', 'SV.png'),
(65, 'Malabo', 'Equatorial Guinean', '226', 'CFA franc (BEAC)', 'XAF', 'centime', 'FCF', 2, 'Republic of Equatorial Guinea', 'GQ', 'GNQ', 'Equatorial Guinea', '002', '017', 0, '240', 'GQ.png'),
(66, 'Addis Ababa', 'Ethiopian', '231', 'birr (inv.)', 'ETB', 'cent', 'ETB', 2, 'Federal Democratic Republic of Ethiopia', 'ET', 'ETH', 'Ethiopia', '002', '014', 0, '251', 'ET.png'),
(67, 'Asmara', 'Eritrean', '232', 'nakfa', 'ERN', 'cent', 'Nfk', 2, 'State of Eritrea', 'ER', 'ERI', 'Eritrea', '002', '014', 0, '291', 'ER.png'),
(68, 'Tallinn', 'Estonian', '233', 'euro', 'EUR', 'cent', 'kr', 2, 'Republic of Estonia', 'EE', 'EST', 'Estonia', '150', '154', 1, '372', 'EE.png'),
(69, 'Tórshavn', 'Faeroese', '234', 'Danish krone', 'DKK', 'øre (inv.)', 'kr', 2, 'Faeroe Islands', 'FO', 'FRO', 'Faroe Islands', '150', '154', 0, '298', 'FO.png'),
(70, 'Stanley', 'Falkland Islander', '238', 'Falkland Islands pound', 'FKP', 'new penny', '£', 2, 'Falkland Islands', 'FK', 'FLK', 'Falkland Islands (Malvinas)', '019', '005', 0, '500', 'FK.png'),
(71, 'King Edward Point (Grytviken)', 'of South Georgia and the South Sandwich Islands', '239', '', '', '', '£', 2, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', 'South Georgia and the South Sandwich Islands', '', '', 0, '44', 'GS.png'),
(72, 'Suva', 'Fijian', '242', 'Fiji dollar', 'FJD', 'cent', '$', 2, 'Republic of Fiji', 'FJ', 'FJI', 'Fiji', '009', '054', 0, '679', 'FJ.png'),
(73, 'Helsinki', 'Finnish', '246', 'euro', 'EUR', 'cent', '€', 2, 'Republic of Finland', 'FI', 'FIN', 'Finland', '150', '154', 1, '358', 'FI.png'),
(74, 'Mariehamn', 'Åland Islander', '248', 'euro', 'EUR', 'cent', NULL, NULL, 'Åland Islands', 'AX', 'ALA', 'Åland Islands', '150', '154', 0, '358', NULL),
(75, 'Paris', 'French', '250', 'euro', 'EUR', 'cent', '€', 2, 'French Republic', 'FR', 'FRA', 'France', '150', '155', 1, '33', 'FR.png'),
(76, 'Cayenne', 'Guianese', '254', 'euro', 'EUR', 'cent', '€', 2, 'French Guiana', 'GF', 'GUF', 'French Guiana', '019', '005', 0, '594', 'GF.png'),
(77, 'Papeete', 'Polynesian', '258', 'CFP franc', 'XPF', 'centime', 'XPF', 0, 'French Polynesia', 'PF', 'PYF', 'French Polynesia', '009', '061', 0, '689', 'PF.png'),
(78, 'Port-aux-Francais', 'of French Southern and Antarctic Lands', '260', 'euro', 'EUR', 'cent', '€', 2, 'French Southern and Antarctic Lands', 'TF', 'ATF', 'French Southern Territories', '', '', 0, '33', 'TF.png'),
(79, 'Djibouti', 'Djiboutian', '262', 'Djibouti franc', 'DJF', '', 'DJF', 0, 'Republic of Djibouti', 'DJ', 'DJI', 'Djibouti', '002', '014', 0, '253', 'DJ.png'),
(80, 'Libreville', 'Gabonese', '266', 'CFA franc (BEAC)', 'XAF', 'centime', 'FCF', 0, 'Gabonese Republic', 'GA', 'GAB', 'Gabon', '002', '017', 0, '241', 'GA.png'),
(81, 'Tbilisi', 'Georgian', '268', 'lari', 'GEL', 'tetri (inv.)', 'GEL', 2, 'Georgia', 'GE', 'GEO', 'Georgia', '142', '145', 0, '995', 'GE.png'),
(82, 'Banjul', 'Gambian', '270', 'dalasi (inv.)', 'GMD', 'butut', 'D', 2, 'Republic of the Gambia', 'GM', 'GMB', 'Gambia', '002', '011', 0, '220', 'GM.png'),
(83, NULL, 'Palestinian', '275', NULL, NULL, NULL, '₪', 2, NULL, 'PS', 'PSE', 'Palestinian Territory, Occupied', '142', '145', 0, '970', 'PS.png'),
(84, 'Berlin', 'German', '276', 'euro', 'EUR', 'cent', '€', 2, 'Federal Republic of Germany', 'DE', 'DEU', 'Germany', '150', '155', 1, '49', 'DE.png'),
(85, 'Accra', 'Ghanaian', '288', 'Ghana cedi', 'GHS', 'pesewa', '¢', 2, 'Republic of Ghana', 'GH', 'GHA', 'Ghana', '002', '011', 0, '233', 'GH.png'),
(86, 'Gibraltar', 'Gibraltarian', '292', 'Gibraltar pound', 'GIP', 'penny', '£', 2, 'Gibraltar', 'GI', 'GIB', 'Gibraltar', '150', '039', 0, '350', 'GI.png'),
(87, 'Tarawa', 'Kiribatian', '296', 'Australian dollar', 'AUD', 'cent', '$', 2, 'Republic of Kiribati', 'KI', 'KIR', 'Kiribati', '009', '057', 0, '686', 'KI.png'),
(88, 'Athens', 'Greek', '300', 'euro', 'EUR', 'cent', '€', 2, 'Hellenic Republic', 'GR', 'GRC', 'Greece', '150', '039', 1, '30', 'GR.png'),
(89, 'Nuuk', 'Greenlander', '304', 'Danish krone', 'DKK', 'øre (inv.)', 'kr', 2, 'Greenland', 'GL', 'GRL', 'Greenland', '019', '021', 0, '299', 'GL.png'),
(90, 'St George’s', 'Grenadian', '308', 'East Caribbean dollar', 'XCD', 'cent', '$', 2, 'Grenada', 'GD', 'GRD', 'Grenada', '019', '029', 0, '1', 'GD.png'),
(91, 'Basse Terre', 'Guadeloupean', '312', 'euro', 'EUR ', 'cent', '€', 2, 'Guadeloupe', 'GP', 'GLP', 'Guadeloupe', '019', '029', 0, '590', 'GP.png'),
(92, 'Agaña (Hagåtña)', 'Guamanian', '316', 'US dollar', 'USD', 'cent', '$', 2, 'Territory of Guam', 'GU', 'GUM', 'Guam', '009', '057', 0, '1', 'GU.png'),
(93, 'Guatemala City', 'Guatemalan', '320', 'quetzal (pl. quetzales)', 'GTQ', 'centavo', 'Q', 2, 'Republic of Guatemala', 'GT', 'GTM', 'Guatemala', '019', '013', 0, '502', 'GT.png'),
(94, 'Conakry', 'Guinean', '324', 'Guinean franc', 'GNF', '', 'GNF', 0, 'Republic of Guinea', 'GN', 'GIN', 'Guinea', '002', '011', 0, '224', 'GN.png'),
(95, 'Georgetown', 'Guyanese', '328', 'Guyana dollar', 'GYD', 'cent', '$', 2, 'Cooperative Republic of Guyana', 'GY', 'GUY', 'Guyana', '019', '005', 0, '592', 'GY.png'),
(96, 'Port-au-Prince', 'Haitian', '332', 'gourde', 'HTG', 'centime', 'G', 2, 'Republic of Haiti', 'HT', 'HTI', 'Haiti', '019', '029', 0, '509', 'HT.png'),
(97, 'Territory of Heard Island and McDonald Islands', 'of Territory of Heard Island and McDonald Islands', '334', '', '', '', '$', 2, 'Territory of Heard Island and McDonald Islands', 'HM', 'HMD', 'Heard Island and McDonald Islands', '', '', 0, '61', 'HM.png'),
(98, 'Vatican City', 'of the Holy See/of the Vatican', '336', 'euro', 'EUR', 'cent', '€', 2, 'the Holy See/ Vatican City State', 'VA', 'VAT', 'Holy See (Vatican City State)', '150', '039', 0, '39', 'VA.png'),
(99, 'Tegucigalpa', 'Honduran', '340', 'lempira', 'HNL', 'centavo', 'L', 2, 'Republic of Honduras', 'HN', 'HND', 'Honduras', '019', '013', 0, '504', 'HN.png'),
(100, '(HK3)', 'Hong Kong Chinese', '344', 'Hong Kong dollar', 'HKD', 'cent', '$', 2, 'Hong Kong Special Administrative Region of the People’s Republic of China (HK2)', 'HK', 'HKG', 'Hong Kong', '142', '030', 0, '852', 'HK.png'),
(101, 'Budapest', 'Hungarian', '348', 'forint (inv.)', 'HUF', '(fillér (inv.))', 'Ft', 2, 'Republic of Hungary', 'HU', 'HUN', 'Hungary', '150', '151', 1, '36', 'HU.png'),
(102, 'Reykjavik', 'Icelander', '352', 'króna (pl. krónur)', 'ISK', '', 'kr', 0, 'Republic of Iceland', 'IS', 'ISL', 'Iceland', '150', '154', 1, '354', 'IS.png'),
(103, 'New Delhi', 'Indian', '356', 'Indian rupee', 'INR', 'paisa', '₹', 2, 'Republic of India', 'IN', 'IND', 'India', '142', '034', 0, '91', 'IN.png'),
(104, 'Jakarta', 'Indonesian', '360', 'Indonesian rupiah (inv.)', 'IDR', 'sen (inv.)', 'Rp', 2, 'Republic of Indonesia', 'ID', 'IDN', 'Indonesia', '142', '035', 0, '62', 'ID.png'),
(105, 'Tehran', 'Iranian', '364', 'Iranian rial', 'IRR', '(dinar) (IR1)', '﷼', 2, 'Islamic Republic of Iran', 'IR', 'IRN', 'Iran, Islamic Republic of', '142', '034', 0, '98', 'IR.png'),
(106, 'Baghdad', 'Iraqi', '368', 'Iraqi dinar', 'IQD', 'fils (inv.)', 'IQD', 3, 'Republic of Iraq', 'IQ', 'IRQ', 'Iraq', '142', '145', 0, '964', 'IQ.png'),
(107, 'Dublin', 'Irish', '372', 'euro', 'EUR', 'cent', '€', 2, 'Ireland (IE1)', 'IE', 'IRL', 'Ireland', '150', '154', 1, '353', 'IE.png'),
(108, '(IL1)', 'Israeli', '376', 'shekel', 'ILS', 'agora', '₪', 2, 'State of Israel', 'IL', 'ISR', 'Israel', '142', '145', 0, '972', 'IL.png'),
(109, 'Rome', 'Italian', '380', 'euro', 'EUR', 'cent', '€', 2, 'Italian Republic', 'IT', 'ITA', 'Italy', '150', '039', 1, '39', 'IT.png'),
(110, 'Yamoussoukro (CI1)', 'Ivorian', '384', 'CFA franc (BCEAO)', 'XOF', 'centime', 'XOF', 0, 'Republic of Côte d’Ivoire', 'CI', 'CIV', 'Côte d\'Ivoire', '002', '011', 0, '225', 'CI.png'),
(111, 'Kingston', 'Jamaican', '388', 'Jamaica dollar', 'JMD', 'cent', '$', 2, 'Jamaica', 'JM', 'JAM', 'Jamaica', '019', '029', 0, '1', 'JM.png'),
(112, 'Tokyo', 'Japanese', '392', 'yen (inv.)', 'JPY', '(sen (inv.)) (JP1)', '¥', 0, 'Japan', 'JP', 'JPN', 'Japan', '142', '030', 0, '81', 'JP.png'),
(113, 'Astana', 'Kazakh', '398', 'tenge (inv.)', 'KZT', 'tiyn', 'лв', 2, 'Republic of Kazakhstan', 'KZ', 'KAZ', 'Kazakhstan', '142', '143', 0, '7', 'KZ.png'),
(114, 'Amman', 'Jordanian', '400', 'Jordanian dinar', 'JOD', '100 qirsh', 'JOD', 2, 'Hashemite Kingdom of Jordan', 'JO', 'JOR', 'Jordan', '142', '145', 0, '962', 'JO.png'),
(115, 'Nairobi', 'Kenyan', '404', 'Kenyan shilling', 'KES', 'cent', 'KES', 2, 'Republic of Kenya', 'KE', 'KEN', 'Kenya', '002', '014', 0, '254', 'KE.png'),
(116, 'Pyongyang', 'North Korean', '408', 'North Korean won (inv.)', 'KPW', 'chun (inv.)', '₩', 2, 'Democratic People’s Republic of Korea', 'KP', 'PRK', 'Korea, Democratic People\'s Republic of', '142', '030', 0, '850', 'KP.png'),
(117, 'Seoul', 'South Korean', '410', 'South Korean won (inv.)', 'KRW', '(chun (inv.))', '₩', 0, 'Republic of Korea', 'KR', 'KOR', 'Korea, Republic of', '142', '030', 0, '82', 'KR.png'),
(118, 'Kuwait City', 'Kuwaiti', '414', 'Kuwaiti dinar', 'KWD', 'fils (inv.)', 'KWD', 3, 'State of Kuwait', 'KW', 'KWT', 'Kuwait', '142', '145', 0, '965', 'KW.png'),
(119, 'Bishkek', 'Kyrgyz', '417', 'som', 'KGS', 'tyiyn', 'лв', 2, 'Kyrgyz Republic', 'KG', 'KGZ', 'Kyrgyzstan', '142', '143', 0, '996', 'KG.png'),
(120, 'Vientiane', 'Lao', '418', 'kip (inv.)', 'LAK', '(at (inv.))', '₭', 0, 'Lao People’s Democratic Republic', 'LA', 'LAO', 'Lao People\'s Democratic Republic', '142', '035', 0, '856', 'LA.png'),
(121, 'Beirut', 'Lebanese', '422', 'Lebanese pound', 'LBP', '(piastre)', '£', 2, 'Lebanese Republic', 'LB', 'LBN', 'Lebanon', '142', '145', 0, '961', 'LB.png'),
(122, 'Maseru', 'Basotho', '426', 'loti (pl. maloti)', 'LSL', 'sente', 'L', 2, 'Kingdom of Lesotho', 'LS', 'LSO', 'Lesotho', '002', '018', 0, '266', 'LS.png'),
(123, 'Riga', 'Latvian', '428', 'euro', 'EUR', 'cent', 'Ls', 2, 'Republic of Latvia', 'LV', 'LVA', 'Latvia', '150', '154', 1, '371', 'LV.png'),
(124, 'Monrovia', 'Liberian', '430', 'Liberian dollar', 'LRD', 'cent', '$', 2, 'Republic of Liberia', 'LR', 'LBR', 'Liberia', '002', '011', 0, '231', 'LR.png'),
(125, 'Tripoli', 'Libyan', '434', 'Libyan dinar', 'LYD', 'dirham', 'LYD', 3, 'Socialist People’s Libyan Arab Jamahiriya', 'LY', 'LBY', 'Libya', '002', '015', 0, '218', 'LY.png'),
(126, 'Vaduz', 'Liechtensteiner', '438', 'Swiss franc', 'CHF', 'centime', 'CHF', 2, 'Principality of Liechtenstein', 'LI', 'LIE', 'Liechtenstein', '150', '155', 1, '423', 'LI.png'),
(127, 'Vilnius', 'Lithuanian', '440', 'euro', 'EUR', 'cent', 'Lt', 2, 'Republic of Lithuania', 'LT', 'LTU', 'Lithuania', '150', '154', 1, '370', 'LT.png'),
(128, 'Luxembourg', 'Luxembourger', '442', 'euro', 'EUR', 'cent', '€', 2, 'Grand Duchy of Luxembourg', 'LU', 'LUX', 'Luxembourg', '150', '155', 1, '352', 'LU.png'),
(129, 'Macao (MO3)', 'Macanese', '446', 'pataca', 'MOP', 'avo', 'MOP', 2, 'Macao Special Administrative Region of the People’s Republic of China (MO2)', 'MO', 'MAC', 'Macao', '142', '030', 0, '853', 'MO.png'),
(130, 'Antananarivo', 'Malagasy', '450', 'ariary', 'MGA', 'iraimbilanja (inv.)', 'MGA', 2, 'Republic of Madagascar', 'MG', 'MDG', 'Madagascar', '002', '014', 0, '261', 'MG.png'),
(131, 'Lilongwe', 'Malawian', '454', 'Malawian kwacha (inv.)', 'MWK', 'tambala (inv.)', 'MK', 2, 'Republic of Malawi', 'MW', 'MWI', 'Malawi', '002', '014', 0, '265', 'MW.png'),
(132, 'Kuala Lumpur (MY1)', 'Malaysian', '458', 'ringgit (inv.)', 'MYR', 'sen (inv.)', 'RM', 2, 'Malaysia', 'MY', 'MYS', 'Malaysia', '142', '035', 0, '60', 'MY.png'),
(133, 'Malé', 'Maldivian', '462', 'rufiyaa', 'MVR', 'laari (inv.)', 'Rf', 2, 'Republic of Maldives', 'MV', 'MDV', 'Maldives', '142', '034', 0, '960', 'MV.png'),
(134, 'Bamako', 'Malian', '466', 'CFA franc (BCEAO)', 'XOF', 'centime', 'XOF', 0, 'Republic of Mali', 'ML', 'MLI', 'Mali', '002', '011', 0, '223', 'ML.png'),
(135, 'Valletta', 'Maltese', '470', 'euro', 'EUR', 'cent', 'MTL', 2, 'Republic of Malta', 'MT', 'MLT', 'Malta', '150', '039', 1, '356', 'MT.png'),
(136, 'Fort-de-France', 'Martinican', '474', 'euro', 'EUR', 'cent', '€', 2, 'Martinique', 'MQ', 'MTQ', 'Martinique', '019', '029', 0, '596', 'MQ.png'),
(137, 'Nouakchott', 'Mauritanian', '478', 'ouguiya', 'MRO', 'khoum', 'UM', 2, 'Islamic Republic of Mauritania', 'MR', 'MRT', 'Mauritania', '002', '011', 0, '222', 'MR.png'),
(138, 'Port Louis', 'Mauritian', '480', 'Mauritian rupee', 'MUR', 'cent', '₨', 2, 'Republic of Mauritius', 'MU', 'MUS', 'Mauritius', '002', '014', 0, '230', 'MU.png'),
(139, 'Mexico City', 'Mexican', '484', 'Mexican peso', 'MXN', 'centavo', '$', 2, 'United Mexican States', 'MX', 'MEX', 'Mexico', '019', '013', 0, '52', 'MX.png'),
(140, 'Monaco', 'Monegasque', '492', 'euro', 'EUR', 'cent', '€', 2, 'Principality of Monaco', 'MC', 'MCO', 'Monaco', '150', '155', 0, '377', 'MC.png'),
(141, 'Ulan Bator', 'Mongolian', '496', 'tugrik', 'MNT', 'möngö (inv.)', '₮', 2, 'Mongolia', 'MN', 'MNG', 'Mongolia', '142', '030', 0, '976', 'MN.png'),
(142, 'Chisinau', 'Moldovan', '498', 'Moldovan leu (pl. lei)', 'MDL', 'ban', 'MDL', 2, 'Republic of Moldova', 'MD', 'MDA', 'Moldova, Republic of', '150', '151', 0, '373', 'MD.png'),
(143, 'Podgorica', 'Montenegrin', '499', 'euro', 'EUR', 'cent', '€', 2, 'Montenegro', 'ME', 'MNE', 'Montenegro', '150', '039', 0, '382', 'ME.png'),
(144, 'Plymouth (MS2)', 'Montserratian', '500', 'East Caribbean dollar', 'XCD', 'cent', '$', 2, 'Montserrat', 'MS', 'MSR', 'Montserrat', '019', '029', 0, '1', 'MS.png'),
(145, 'Rabat', 'Moroccan', '504', 'Moroccan dirham', 'MAD', 'centime', 'MAD', 2, 'Kingdom of Morocco', 'MA', 'MAR', 'Morocco', '002', '015', 0, '212', 'MA.png'),
(146, 'Maputo', 'Mozambican', '508', 'metical', 'MZN', 'centavo', 'MT', 2, 'Republic of Mozambique', 'MZ', 'MOZ', 'Mozambique', '002', '014', 0, '258', 'MZ.png'),
(147, 'Muscat', 'Omani', '512', 'Omani rial', 'OMR', 'baiza', '﷼', 3, 'Sultanate of Oman', 'OM', 'OMN', 'Oman', '142', '145', 0, '968', 'OM.png'),
(148, 'Windhoek', 'Namibian', '516', 'Namibian dollar', 'NAD', 'cent', '$', 2, 'Republic of Namibia', 'NA', 'NAM', 'Namibia', '002', '018', 0, '264', 'NA.png'),
(149, 'Yaren', 'Nauruan', '520', 'Australian dollar', 'AUD', 'cent', '$', 2, 'Republic of Nauru', 'NR', 'NRU', 'Nauru', '009', '057', 0, '674', 'NR.png'),
(150, 'Kathmandu', 'Nepalese', '524', 'Nepalese rupee', 'NPR', 'paisa (inv.)', '₨', 2, 'Nepal', 'NP', 'NPL', 'Nepal', '142', '034', 0, '977', 'NP.png'),
(151, 'Amsterdam (NL2)', 'Dutch', '528', 'euro', 'EUR', 'cent', '€', 2, 'Kingdom of the Netherlands', 'NL', 'NLD', 'Netherlands', '150', '155', 1, '31', 'NL.png'),
(152, 'Willemstad', 'Curaçaoan', '531', 'Netherlands Antillean guilder (CW1)', 'ANG', 'cent', NULL, NULL, 'Curaçao', 'CW', 'CUW', 'Curaçao', '019', '029', 0, '599', NULL),
(153, 'Oranjestad', 'Aruban', '533', 'Aruban guilder', 'AWG', 'cent', 'ƒ', 2, 'Aruba', 'AW', 'ABW', 'Aruba', '019', '029', 0, '297', 'AW.png'),
(154, 'Philipsburg', 'Sint Maartener', '534', 'Netherlands Antillean guilder (SX1)', 'ANG', 'cent', NULL, NULL, 'Sint Maarten', 'SX', 'SXM', 'Sint Maarten (Dutch part)', '019', '029', 0, '721', NULL),
(155, NULL, 'of Bonaire, Sint Eustatius and Saba', '535', 'US dollar', 'USD', 'cent', NULL, NULL, NULL, 'BQ', 'BES', 'Bonaire, Sint Eustatius and Saba', '019', '029', 0, '599', NULL),
(156, 'Nouméa', 'New Caledonian', '540', 'CFP franc', 'XPF', 'centime', 'XPF', 0, 'New Caledonia', 'NC', 'NCL', 'New Caledonia', '009', '054', 0, '687', 'NC.png'),
(157, 'Port Vila', 'Vanuatuan', '548', 'vatu (inv.)', 'VUV', '', 'Vt', 0, 'Republic of Vanuatu', 'VU', 'VUT', 'Vanuatu', '009', '054', 0, '678', 'VU.png'),
(158, 'Wellington', 'New Zealander', '554', 'New Zealand dollar', 'NZD', 'cent', '$', 2, 'New Zealand', 'NZ', 'NZL', 'New Zealand', '009', '053', 0, '64', 'NZ.png'),
(159, 'Managua', 'Nicaraguan', '558', 'córdoba oro', 'NIO', 'centavo', 'C$', 2, 'Republic of Nicaragua', 'NI', 'NIC', 'Nicaragua', '019', '013', 0, '505', 'NI.png'),
(160, 'Niamey', 'Nigerien', '562', 'CFA franc (BCEAO)', 'XOF', 'centime', 'XOF', 0, 'Republic of Niger', 'NE', 'NER', 'Niger', '002', '011', 0, '227', 'NE.png'),
(161, 'Abuja', 'Nigerian', '566', 'naira (inv.)', 'NGN', 'kobo (inv.)', '₦', 2, 'Federal Republic of Nigeria', 'NG', 'NGA', 'Nigeria', '002', '011', 0, '234', 'NG.png'),
(162, 'Alofi', 'Niuean', '570', 'New Zealand dollar', 'NZD', 'cent', '$', 2, 'Niue', 'NU', 'NIU', 'Niue', '009', '061', 0, '683', 'NU.png'),
(163, 'Kingston', 'Norfolk Islander', '574', 'Australian dollar', 'AUD', 'cent', '$', 2, 'Territory of Norfolk Island', 'NF', 'NFK', 'Norfolk Island', '009', '053', 0, '672', 'NF.png'),
(164, 'Oslo', 'Norwegian', '578', 'Norwegian krone (pl. kroner)', 'NOK', 'øre (inv.)', 'kr', 2, 'Kingdom of Norway', 'NO', 'NOR', 'Norway', '150', '154', 1, '47', 'NO.png'),
(165, 'Saipan', 'Northern Mariana Islander', '580', 'US dollar', 'USD', 'cent', '$', 2, 'Commonwealth of the Northern Mariana Islands', 'MP', 'MNP', 'Northern Mariana Islands', '009', '057', 0, '1', 'MP.png'),
(166, 'United States Minor Outlying Islands', 'of United States Minor Outlying Islands', '581', 'US dollar', 'USD', 'cent', '$', 2, 'United States Minor Outlying Islands', 'UM', 'UMI', 'United States Minor Outlying Islands', '', '', 0, '1', 'UM.png'),
(167, 'Palikir', 'Micronesian', '583', 'US dollar', 'USD', 'cent', '$', 2, 'Federated States of Micronesia', 'FM', 'FSM', 'Micronesia, Federated States of', '009', '057', 0, '691', 'FM.png'),
(168, 'Majuro', 'Marshallese', '584', 'US dollar', 'USD', 'cent', '$', 2, 'Republic of the Marshall Islands', 'MH', 'MHL', 'Marshall Islands', '009', '057', 0, '692', 'MH.png'),
(169, 'Melekeok', 'Palauan', '585', 'US dollar', 'USD', 'cent', '$', 2, 'Republic of Palau', 'PW', 'PLW', 'Palau', '009', '057', 0, '680', 'PW.png'),
(170, 'Islamabad', 'Pakistani', '586', 'Pakistani rupee', 'PKR', 'paisa', '₨', 2, 'Islamic Republic of Pakistan', 'PK', 'PAK', 'Pakistan', '142', '034', 0, '92', 'PK.png'),
(171, 'Panama City', 'Panamanian', '591', 'balboa', 'PAB', 'centésimo', 'B/.', 2, 'Republic of Panama', 'PA', 'PAN', 'Panama', '019', '013', 0, '507', 'PA.png'),
(172, 'Port Moresby', 'Papua New Guinean', '598', 'kina (inv.)', 'PGK', 'toea (inv.)', 'PGK', 2, 'Independent State of Papua New Guinea', 'PG', 'PNG', 'Papua New Guinea', '009', '054', 0, '675', 'PG.png'),
(173, 'Asunción', 'Paraguayan', '600', 'guaraní', 'PYG', 'céntimo', 'Gs', 0, 'Republic of Paraguay', 'PY', 'PRY', 'Paraguay', '019', '005', 0, '595', 'PY.png'),
(174, 'Lima', 'Peruvian', '604', 'new sol', 'PEN', 'céntimo', 'S/.', 2, 'Republic of Peru', 'PE', 'PER', 'Peru', '019', '005', 0, '51', 'PE.png'),
(175, 'Manila', 'Filipino', '608', 'Philippine peso', 'PHP', 'centavo', 'Php', 2, 'Republic of the Philippines', 'PH', 'PHL', 'Philippines', '142', '035', 0, '63', 'PH.png'),
(176, 'Adamstown', 'Pitcairner', '612', 'New Zealand dollar', 'NZD', 'cent', '$', 2, 'Pitcairn Islands', 'PN', 'PCN', 'Pitcairn', '009', '061', 0, '649', 'PN.png'),
(177, 'Warsaw', 'Polish', '616', 'zloty', 'PLN', 'grosz (pl. groszy)', 'zł', 2, 'Republic of Poland', 'PL', 'POL', 'Poland', '150', '151', 1, '48', 'PL.png'),
(178, 'Lisbon', 'Portuguese', '620', 'euro', 'EUR', 'cent', '€', 2, 'Portuguese Republic', 'PT', 'PRT', 'Portugal', '150', '039', 1, '351', 'PT.png'),
(179, 'Bissau', 'Guinea-Bissau national', '624', 'CFA franc (BCEAO)', 'XOF', 'centime', 'XOF', 0, 'Republic of Guinea-Bissau', 'GW', 'GNB', 'Guinea-Bissau', '002', '011', 0, '245', 'GW.png'),
(180, 'Dili', 'East Timorese', '626', 'US dollar', 'USD', 'cent', '$', 2, 'Democratic Republic of East Timor', 'TL', 'TLS', 'Timor-Leste', '142', '035', 0, '670', 'TL.png'),
(181, 'San Juan', 'Puerto Rican', '630', 'US dollar', 'USD', 'cent', '$', 2, 'Commonwealth of Puerto Rico', 'PR', 'PRI', 'Puerto Rico', '019', '029', 0, '1', 'PR.png'),
(182, 'Doha', 'Qatari', '634', 'Qatari riyal', 'QAR', 'dirham', '﷼', 2, 'State of Qatar', 'QA', 'QAT', 'Qatar', '142', '145', 0, '974', 'QA.png'),
(183, 'Saint-Denis', 'Reunionese', '638', 'euro', 'EUR', 'cent', '€', 2, 'Réunion', 'RE', 'REU', 'Réunion', '002', '014', 0, '262', 'RE.png'),
(184, 'Bucharest', 'Romanian', '642', 'Romanian leu (pl. lei)', 'RON', 'ban (pl. bani)', 'lei', 2, 'Romania', 'RO', 'ROU', 'Romania', '150', '151', 1, '40', 'RO.png'),
(185, 'Moscow', 'Russian', '643', 'Russian rouble', 'RUB', 'kopek', 'руб', 2, 'Russian Federation', 'RU', 'RUS', 'Russian Federation', '150', '151', 0, '7', 'RU.png'),
(186, 'Kigali', 'Rwandan; Rwandese', '646', 'Rwandese franc', 'RWF', 'centime', 'RWF', 0, 'Republic of Rwanda', 'RW', 'RWA', 'Rwanda', '002', '014', 0, '250', 'RW.png'),
(187, 'Gustavia', 'of Saint Barthélemy', '652', 'euro', 'EUR', 'cent', NULL, NULL, 'Collectivity of Saint Barthélemy', 'BL', 'BLM', 'Saint Barthélemy', '019', '029', 0, '590', NULL),
(188, 'Jamestown', 'Saint Helenian', '654', 'Saint Helena pound', 'SHP', 'penny', '£', 2, 'Saint Helena, Ascension and Tristan da Cunha', 'SH', 'SHN', 'Saint Helena, Ascension and Tristan da Cunha', '002', '011', 0, '290', 'SH.png'),
(189, 'Basseterre', 'Kittsian; Nevisian', '659', 'East Caribbean dollar', 'XCD', 'cent', '$', 2, 'Federation of Saint Kitts and Nevis', 'KN', 'KNA', 'Saint Kitts and Nevis', '019', '029', 0, '1', 'KN.png'),
(190, 'The Valley', 'Anguillan', '660', 'East Caribbean dollar', 'XCD', 'cent', '$', 2, 'Anguilla', 'AI', 'AIA', 'Anguilla', '019', '029', 0, '1', 'AI.png'),
(191, 'Castries', 'Saint Lucian', '662', 'East Caribbean dollar', 'XCD', 'cent', '$', 2, 'Saint Lucia', 'LC', 'LCA', 'Saint Lucia', '019', '029', 0, '1', 'LC.png'),
(192, 'Marigot', 'of Saint Martin', '663', 'euro', 'EUR', 'cent', NULL, NULL, 'Collectivity of Saint Martin', 'MF', 'MAF', 'Saint Martin (French part)', '019', '029', 0, '590', NULL),
(193, 'Saint-Pierre', 'St-Pierrais; Miquelonnais', '666', 'euro', 'EUR', 'cent', '€', 2, 'Territorial Collectivity of Saint Pierre and Miquelon', 'PM', 'SPM', 'Saint Pierre and Miquelon', '019', '021', 0, '508', 'PM.png'),
(194, 'Kingstown', 'Vincentian', '670', 'East Caribbean dollar', 'XCD', 'cent', '$', 2, 'Saint Vincent and the Grenadines', 'VC', 'VCT', 'Saint Vincent and the Grenadines', '019', '029', 0, '1', 'VC.png'),
(195, 'San Marino', 'San Marinese', '674', 'euro', 'EUR ', 'cent', '€', 2, 'Republic of San Marino', 'SM', 'SMR', 'San Marino', '150', '039', 0, '378', 'SM.png'),
(196, 'São Tomé', 'São Toméan', '678', 'dobra', 'STD', 'centavo', 'Db', 2, 'Democratic Republic of São Tomé and Príncipe', 'ST', 'STP', 'Sao Tome and Principe', '002', '017', 0, '239', 'ST.png'),
(197, 'Riyadh', 'Saudi Arabian', '682', 'riyal', 'SAR', 'halala', '﷼', 2, 'Kingdom of Saudi Arabia', 'SA', 'SAU', 'Saudi Arabia', '142', '145', 0, '966', 'SA.png'),
(198, 'Dakar', 'Senegalese', '686', 'CFA franc (BCEAO)', 'XOF', 'centime', 'XOF', 0, 'Republic of Senegal', 'SN', 'SEN', 'Senegal', '002', '011', 0, '221', 'SN.png'),
(199, 'Belgrade', 'Serb', '688', 'Serbian dinar', 'RSD', 'para (inv.)', NULL, NULL, 'Republic of Serbia', 'RS', 'SRB', 'Serbia', '150', '039', 0, '381', NULL),
(200, 'Victoria', 'Seychellois', '690', 'Seychelles rupee', 'SCR', 'cent', '₨', 2, 'Republic of Seychelles', 'SC', 'SYC', 'Seychelles', '002', '014', 0, '248', 'SC.png'),
(201, 'Freetown', 'Sierra Leonean', '694', 'leone', 'SLL', 'cent', 'Le', 2, 'Republic of Sierra Leone', 'SL', 'SLE', 'Sierra Leone', '002', '011', 0, '232', 'SL.png'),
(202, 'Singapore', 'Singaporean', '702', 'Singapore dollar', 'SGD', 'cent', '$', 2, 'Republic of Singapore', 'SG', 'SGP', 'Singapore', '142', '035', 0, '65', 'SG.png'),
(203, 'Bratislava', 'Slovak', '703', 'euro', 'EUR', 'cent', 'Sk', 2, 'Slovak Republic', 'SK', 'SVK', 'Slovakia', '150', '151', 1, '421', 'SK.png'),
(204, 'Hanoi', 'Vietnamese', '704', 'dong', 'VND', '(10 hào', '₫', 2, 'Socialist Republic of Vietnam', 'VN', 'VNM', 'Viet Nam', '142', '035', 0, '84', 'VN.png'),
(205, 'Ljubljana', 'Slovene', '705', 'euro', 'EUR', 'cent', '€', 2, 'Republic of Slovenia', 'SI', 'SVN', 'Slovenia', '150', '039', 1, '386', 'SI.png'),
(206, 'Mogadishu', 'Somali', '706', 'Somali shilling', 'SOS', 'cent', 'S', 2, 'Somali Republic', 'SO', 'SOM', 'Somalia', '002', '014', 0, '252', 'SO.png'),
(207, 'Pretoria (ZA1)', 'South African', '710', 'rand', 'ZAR', 'cent', 'R', 2, 'Republic of South Africa', 'ZA', 'ZAF', 'South Africa', '002', '018', 0, '27', 'ZA.png'),
(208, 'Harare', 'Zimbabwean', '716', 'Zimbabwe dollar (ZW1)', 'ZWL', 'cent', 'Z$', 2, 'Republic of Zimbabwe', 'ZW', 'ZWE', 'Zimbabwe', '002', '014', 0, '263', 'ZW.png'),
(209, 'Madrid', 'Spaniard', '724', 'euro', 'EUR', 'cent', '€', 2, 'Kingdom of Spain', 'ES', 'ESP', 'Spain', '150', '039', 1, '34', 'ES.png'),
(210, 'Juba', 'South Sudanese', '728', 'South Sudanese pound', 'SSP', 'piaster', NULL, NULL, 'Republic of South Sudan', 'SS', 'SSD', 'South Sudan', '002', '015', 0, '211', NULL),
(211, 'Khartoum', 'Sudanese', '729', 'Sudanese pound', 'SDG', 'piastre', NULL, NULL, 'Republic of the Sudan', 'SD', 'SDN', 'Sudan', '002', '015', 0, '249', NULL),
(212, 'Al aaiun', 'Sahrawi', '732', 'Moroccan dirham', 'MAD', 'centime', 'MAD', 2, 'Western Sahara', 'EH', 'ESH', 'Western Sahara', '002', '015', 0, '212', 'EH.png'),
(213, 'Paramaribo', 'Surinamese', '740', 'Surinamese dollar', 'SRD', 'cent', '$', 2, 'Republic of Suriname', 'SR', 'SUR', 'Suriname', '019', '005', 0, '597', 'SR.png'),
(214, 'Longyearbyen', 'of Svalbard', '744', 'Norwegian krone (pl. kroner)', 'NOK', 'øre (inv.)', 'kr', 2, 'Svalbard and Jan Mayen', 'SJ', 'SJM', 'Svalbard and Jan Mayen', '150', '154', 0, '47', 'SJ.png'),
(215, 'Mbabane', 'Swazi', '748', 'lilangeni', 'SZL', 'cent', 'SZL', 2, 'Kingdom of Swaziland', 'SZ', 'SWZ', 'Swaziland', '002', '018', 0, '268', 'SZ.png'),
(216, 'Stockholm', 'Swedish', '752', 'krona (pl. kronor)', 'SEK', 'öre (inv.)', 'kr', 2, 'Kingdom of Sweden', 'SE', 'SWE', 'Sweden', '150', '154', 1, '46', 'SE.png'),
(217, 'Berne', 'Swiss', '756', 'Swiss franc', 'CHF', 'centime', 'CHF', 2, 'Swiss Confederation', 'CH', 'CHE', 'Switzerland', '150', '155', 1, '41', 'CH.png'),
(218, 'Damascus', 'Syrian', '760', 'Syrian pound', 'SYP', 'piastre', '£', 2, 'Syrian Arab Republic', 'SY', 'SYR', 'Syrian Arab Republic', '142', '145', 0, '963', 'SY.png'),
(219, 'Dushanbe', 'Tajik', '762', 'somoni', 'TJS', 'diram', 'TJS', 2, 'Republic of Tajikistan', 'TJ', 'TJK', 'Tajikistan', '142', '143', 0, '992', 'TJ.png'),
(220, 'Bangkok', 'Thai', '764', 'baht (inv.)', 'THB', 'satang (inv.)', '฿', 2, 'Kingdom of Thailand', 'TH', 'THA', 'Thailand', '142', '035', 0, '66', 'TH.png'),
(221, 'Lomé', 'Togolese', '768', 'CFA franc (BCEAO)', 'XOF', 'centime', 'XOF', 0, 'Togolese Republic', 'TG', 'TGO', 'Togo', '002', '011', 0, '228', 'TG.png'),
(222, '(TK2)', 'Tokelauan', '772', 'New Zealand dollar', 'NZD', 'cent', '$', 2, 'Tokelau', 'TK', 'TKL', 'Tokelau', '009', '061', 0, '690', 'TK.png'),
(223, 'Nuku’alofa', 'Tongan', '776', 'pa’anga (inv.)', 'TOP', 'seniti (inv.)', 'T$', 2, 'Kingdom of Tonga', 'TO', 'TON', 'Tonga', '009', '061', 0, '676', 'TO.png'),
(224, 'Port of Spain', 'Trinidadian; Tobagonian', '780', 'Trinidad and Tobago dollar', 'TTD', 'cent', 'TT$', 2, 'Republic of Trinidad and Tobago', 'TT', 'TTO', 'Trinidad and Tobago', '019', '029', 0, '1', 'TT.png'),
(225, 'Abu Dhabi', 'Emirian', '784', 'UAE dirham', 'AED', 'fils (inv.)', 'AED', 2, 'United Arab Emirates', 'AE', 'ARE', 'United Arab Emirates', '142', '145', 0, '971', 'AE.png'),
(226, 'Tunis', 'Tunisian', '788', 'Tunisian dinar', 'TND', 'millime', 'TND', 3, 'Republic of Tunisia', 'TN', 'TUN', 'Tunisia', '002', '015', 0, '216', 'TN.png'),
(227, 'Ankara', 'Turk', '792', 'Turkish lira (inv.)', 'TRY', 'kurus (inv.)', '₺', 2, 'Republic of Turkey', 'TR', 'TUR', 'Turkey', '142', '145', 0, '90', 'TR.png'),
(228, 'Ashgabat', 'Turkmen', '795', 'Turkmen manat (inv.)', 'TMT', 'tenge (inv.)', 'm', 2, 'Turkmenistan', 'TM', 'TKM', 'Turkmenistan', '142', '143', 0, '993', 'TM.png'),
(229, 'Cockburn Town', 'Turks and Caicos Islander', '796', 'US dollar', 'USD', 'cent', '$', 2, 'Turks and Caicos Islands', 'TC', 'TCA', 'Turks and Caicos Islands', '019', '029', 0, '1', 'TC.png'),
(230, 'Funafuti', 'Tuvaluan', '798', 'Australian dollar', 'AUD', 'cent', '$', 2, 'Tuvalu', 'TV', 'TUV', 'Tuvalu', '009', '061', 0, '688', 'TV.png'),
(231, 'Kampala', 'Ugandan', '800', 'Uganda shilling', 'UGX', 'cent', 'UGX', 0, 'Republic of Uganda', 'UG', 'UGA', 'Uganda', '002', '014', 0, '256', 'UG.png'),
(232, 'Kiev', 'Ukrainian', '804', 'hryvnia', 'UAH', 'kopiyka', '₴', 2, 'Ukraine', 'UA', 'UKR', 'Ukraine', '150', '151', 0, '380', 'UA.png'),
(233, 'Skopje', 'of the former Yugoslav Republic of Macedonia', '807', 'denar (pl. denars)', 'MKD', 'deni (inv.)', 'ден', 2, 'the former Yugoslav Republic of Macedonia', 'MK', 'MKD', 'Macedonia, the former Yugoslav Republic of', '150', '039', 0, '389', 'MK.png'),
(234, 'Cairo', 'Egyptian', '818', 'Egyptian pound', 'EGP', 'piastre', '£', 2, 'Arab Republic of Egypt', 'EG', 'EGY', 'Egypt', '002', '015', 0, '20', 'EG.png'),
(235, 'London', 'British', '826', 'pound sterling', 'GBP', 'penny (pl. pence)', '£', 2, 'United Kingdom of Great Britain and Northern Ireland', 'GB', 'GBR', 'United Kingdom', '150', '154', 1, '44', 'GB.png'),
(236, 'St Peter Port', 'of Guernsey', '831', 'Guernsey pound (GG2)', 'GGP (GG2)', 'penny (pl. pence)', NULL, NULL, 'Bailiwick of Guernsey', 'GG', 'GGY', 'Guernsey', '150', '154', 0, '44', NULL),
(237, 'St Helier', 'of Jersey', '832', 'Jersey pound (JE2)', 'JEP (JE2)', 'penny (pl. pence)', NULL, NULL, 'Bailiwick of Jersey', 'JE', 'JEY', 'Jersey', '150', '154', 0, '44', NULL),
(238, 'Douglas', 'Manxman; Manxwoman', '833', 'Manx pound (IM2)', 'IMP (IM2)', 'penny (pl. pence)', NULL, NULL, 'Isle of Man', 'IM', 'IMN', 'Isle of Man', '150', '154', 0, '44', NULL),
(239, 'Dodoma (TZ1)', 'Tanzanian', '834', 'Tanzanian shilling', 'TZS', 'cent', 'TZS', 2, 'United Republic of Tanzania', 'TZ', 'TZA', 'Tanzania, United Republic of', '002', '014', 0, '255', 'TZ.png'),
(240, 'Washington DC', 'American', '840', 'US dollar', 'USD', 'cent', '$', 2, 'United States of America', 'US', 'USA', 'United States', '019', '021', 0, '1', 'US.png'),
(241, 'Charlotte Amalie', 'US Virgin Islander', '850', 'US dollar', 'USD', 'cent', '$', 2, 'United States Virgin Islands', 'VI', 'VIR', 'Virgin Islands, U.S.', '019', '029', 0, '1', 'VI.png'),
(242, 'Ouagadougou', 'Burkinabe', '854', 'CFA franc (BCEAO)', 'XOF', 'centime', 'XOF', 0, 'Burkina Faso', 'BF', 'BFA', 'Burkina Faso', '002', '011', 0, '226', 'BF.png'),
(243, 'Montevideo', 'Uruguayan', '858', 'Uruguayan peso', 'UYU', 'centésimo', '$U', 0, 'Eastern Republic of Uruguay', 'UY', 'URY', 'Uruguay', '019', '005', 0, '598', 'UY.png'),
(244, 'Tashkent', 'Uzbek', '860', 'sum (inv.)', 'UZS', 'tiyin (inv.)', 'лв', 2, 'Republic of Uzbekistan', 'UZ', 'UZB', 'Uzbekistan', '142', '143', 0, '998', 'UZ.png'),
(245, 'Caracas', 'Venezuelan', '862', 'bolívar fuerte (pl. bolívares fuertes)', 'VEF', 'céntimo', 'Bs', 2, 'Bolivarian Republic of Venezuela', 'VE', 'VEN', 'Venezuela, Bolivarian Republic of', '019', '005', 0, '58', 'VE.png'),
(246, 'Mata-Utu', 'Wallisian; Futunan; Wallis and Futuna Islander', '876', 'CFP franc', 'XPF', 'centime', 'XPF', 0, 'Wallis and Futuna', 'WF', 'WLF', 'Wallis and Futuna', '009', '061', 0, '681', 'WF.png'),
(247, 'Apia', 'Samoan', '882', 'tala (inv.)', 'WST', 'sene (inv.)', 'WS$', 2, 'Independent State of Samoa', 'WS', 'WSM', 'Samoa', '009', '061', 0, '685', 'WS.png'),
(248, 'San’a', 'Yemenite', '887', 'Yemeni rial', 'YER', 'fils (inv.)', '﷼', 2, 'Republic of Yemen', 'YE', 'YEM', 'Yemen', '142', '145', 0, '967', 'YE.png'),
(249, 'Lusaka', 'Zambian', '894', 'Zambian kwacha (inv.)', 'ZMW', 'ngwee (inv.)', 'ZK', 2, 'Republic of Zambia', 'ZM', 'ZMB', 'Zambia', '002', '014', 0, '260', 'ZM.png');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `user_id`, `campaign_id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Will you need any more donation', 'No, We have no need any more donation, Updated', '2017-03-25 11:59:12', '2017-03-25 12:10:49'),
(3, 1, 2, 'A lot happened in the past 48 hours.', 'Dear Supporters,\r\n\r\nYou have helped us hit the goal! Now we believe that we have a product worth making and it\'s going to bring a lot of fun into the world.  Each of you are a big part of what made this possible!\r\n\r\nWe will keep updating everyone all latest news and announcements about our progress and plans. Please do not hesitate to let us know if you have any questions or concerns, and we will be do our best to provide answers as soon as possible.', '2017-03-25 12:16:49', '2017-03-25 12:16:49'),
(4, 1, 1, 'How you will deliver it', 'We will delivery it by air', '2017-03-25 13:31:56', '2017-03-25 13:31:56'),
(5, 1, 1, 'Is it secure?', 'Yes, its fully secure and you can trust us', '2017-03-25 13:32:21', '2017-03-25 13:32:21'),
(6, 6, 9, 'Q1: Lorem Ipsum is simply dummy text of', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry', '2017-04-09 13:07:23', '2017-04-09 13:07:23'),
(7, 6, 9, 'Q2: Lorem Ipsum is simply dummy text of the printing.', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry\r\n\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry', '2017-04-09 13:07:38', '2017-04-09 13:07:38'),
(8, 6, 9, 'Q3', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry\r\n\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry\r\n\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry', '2017-04-09 13:07:48', '2017-04-09 13:07:48'),
(9, 6, 9, 'Q4: Lorem Ipsum is simply dummy text of the printing and typesettin.', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry', '2017-04-09 13:08:03', '2017-04-09 13:08:03'),
(10, 5, 16, 'When it will be arrived?', 'Lorem ipsum dolor sit amet, ius nisl doctus prodesset ei, cu urbanitas appellantur per, eu eos tation tollit eloquentiam. Ex vix aliquid graecis albucius. Mel adhuc vidisse an. Ne graece expetenda eam. Noluisse salutatus ex usu. Aliquid impedit delicata at eos.', '2018-05-22 14:45:20', '2018-05-22 14:45:20'),
(11, 5, 16, 'What about supports after getting it?', 'Lorem ipsum dolor sit amet, ius nisl doctus prodesset ei, cu urbanitas appellantur per, eu eos tation tollit eloquentiam. Ex vix aliquid graecis albucius. Mel adhuc vidisse an. Ne graece expetenda eam. Noluisse salutatus ex usu. Aliquid impedit delicata at eos.', '2018-05-22 14:45:33', '2018-05-22 14:46:01'),
(12, 5, 16, 'It will be world wide available?', 'Lorem ipsum dolor sit amet, ius nisl doctus prodesset ei, cu urbanitas appellantur per, eu eos tation tollit eloquentiam. Ex vix aliquid graecis albucius. Mel adhuc vidisse an. Ne graece expetenda eam. Noluisse salutatus ex usu. Aliquid impedit delicata at eos.', '2018-05-22 14:45:44', '2018-05-22 14:45:44'),
(13, 5, 16, 'Can I sale it after getting one from you?', 'Lorem ipsum dolor sit amet, ius nisl doctus prodesset ei, cu urbanitas appellantur per, eu eos tation tollit eloquentiam. Ex vix aliquid graecis albucius. Mel adhuc vidisse an. Ne graece expetenda eam. Noluisse salutatus ex usu. Aliquid impedit delicata at eos.', '2018-05-22 14:49:26', '2018-05-22 14:49:26'),
(14, 5, 16, 'What about build quality?', 'Lorem ipsum dolor sit amet, ius nisl doctus prodesset ei, cu urbanitas appellantur per, eu eos tation tollit eloquentiam. Ex vix aliquid graecis albucius. Mel adhuc vidisse an. Ne graece expetenda eam. Noluisse salutatus ex usu. Aliquid impedit delicata at eos.', '2018-05-22 14:49:38', '2018-05-22 14:49:38'),
(15, 5, 16, 'Do i need coffee bean to make coffee in this machine?', 'Lorem ipsum dolor sit amet, ius nisl doctus prodesset ei, cu urbanitas appellantur per, eu eos tation tollit eloquentiam. Ex vix aliquid graecis albucius. Mel adhuc vidisse an. Ne graece expetenda eam. Noluisse salutatus ex usu. Aliquid impedit delicata at eos.', '2018-05-22 14:49:56', '2018-05-22 14:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug_ext` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `user_id`, `name`, `slug`, `slug_ext`, `file_size`, `mime_type`, `sort_order`, `created_at`, `updated_at`) VALUES
(2, 6, 'cofee-maker.jpg', 'cofee-maker', 'cofee-maker.jpg', '67627', 'image/jpeg', 0, '2018-05-06 12:19:05', '2018-05-06 12:19:05'),
(3, 6, 'penknife-pocket-knife-blade-serrated-37863.jpeg', 'penknife-pocket-knife-blade-serrated-37863', 'penknife-pocket-knife-blade-serrated-37863.jpeg', '132982', 'image/jpeg', 0, '2018-05-06 12:26:07', '2018-05-06 12:26:07'),
(4, 6, 'analog-clock.png', 'analog-clock', 'analog-clock.png', '797751', 'image/png', 0, '2018-05-08 13:32:01', '2018-05-08 13:32:01'),
(5, 6, 'analog-clock-2.png', 'analog-clock-2', 'analog-clock-2.png', '315927', 'image/png', 0, '2018-05-08 13:34:18', '2018-05-08 13:34:18'),
(6, 6, 'logo.png', 'logo', 'logo.png', '10182', 'image/png', 0, '2018-05-14 11:57:26', '2018-05-14 11:57:26'),
(7, 6, 'cookie.jpg', 'cookie', 'cookie.jpg', '50138', 'image/jpeg', 0, '2018-05-14 12:49:09', '2018-05-14 12:49:09'),
(9, 6, 'profile-photo-female.png', 'profile-photo-female', 'profile-photo-female.png', '29208', 'image/png', 0, '2018-05-14 13:25:57', '2018-05-14 13:25:57'),
(10, 6, 'bear-2203644_1280.jpg', 'bear-2203644-1280', 'bear-2203644-1280.jpg', '507785', 'image/jpeg', 0, '2018-05-18 14:31:03', '2018-05-18 14:31:03'),
(11, 6, 'home-tv-stand-shelves_925x.jpg', 'home-tv-stand-shelves-925x', 'home-tv-stand-shelves-925x.jpg', '40292', 'image/jpeg', 0, '2018-05-18 14:33:32', '2018-05-18 14:33:32'),
(12, 5, 'irobot.jpeg', 'irobot', 'irobot.jpeg', '157143', 'image/jpeg', 0, '2018-05-19 12:06:19', '2018-05-19 12:06:19'),
(16, 5, 'coffee-maker.png', 'coffee-maker', 'coffee-maker.png', '343773', 'image/png', 0, '2018-05-20 12:52:33', '2018-05-20 12:52:33'),
(17, 5, 'clock-1850066_1280.jpg', 'clock-1850066-1280', 'clock-1850066-1280.jpg', '211191', 'image/jpeg', 0, '2018-05-20 12:54:39', '2018-05-20 12:54:39'),
(18, 5, 'coffee-802057_1280.jpg', 'coffee-802057-1280', 'coffee-802057-1280.jpg', '206736', 'image/jpeg', 0, '2018-05-20 12:54:39', '2018-05-20 12:54:39'),
(19, 5, 'profile-photo.jpg', 'profile-photo', 'profile-photo.jpg', '18714', 'image/jpeg', 0, '2018-05-20 13:09:09', '2018-05-20 13:09:09'),
(20, 6, 'powerful-tab.jpg', 'powerful-tab', 'powerful-tab.jpg', '374289', 'image/jpeg', 0, '2018-05-22 12:17:36', '2018-05-22 12:17:36'),
(21, 6, 'pexels-photo-458976.jpeg', 'pexels-photo-458976', 'pexels-photo-458976.jpeg', '228081', 'image/jpeg', 0, '2018-05-22 12:27:28', '2018-05-22 12:27:28'),
(22, 6, 'animal-1.jpeg', 'animal-1', 'animal-1.jpeg', '93027', 'image/jpeg', 0, '2018-05-22 12:28:01', '2018-05-22 12:28:01'),
(23, 6, 'animal-2.jpeg', 'animal-2', 'animal-2.jpeg', '132829', 'image/jpeg', 0, '2018-05-22 12:28:02', '2018-05-22 12:28:02'),
(24, 1, 'children.jpg', 'children', 'children.jpg', '401174', 'image/jpeg', 0, '2018-05-22 12:31:56', '2018-05-22 12:31:56'),
(25, 1, 'child-3.jpg', 'child-3', 'child-3.jpg', '310012', 'image/jpeg', 0, '2018-05-22 12:53:27', '2018-05-22 12:53:27'),
(26, 1, 'childs-2.jpg', 'childs-2', 'childs-2.jpg', '352246', 'image/jpeg', 0, '2018-05-22 12:53:28', '2018-05-22 12:53:28'),
(28, 1, 'bavarian-forest.jpg', 'bavarian-forest', 'bavarian-forest.jpg', '475945', 'image/jpeg', 0, '2018-05-22 13:07:06', '2018-05-22 13:07:06'),
(29, 1, 'new-food.jpg', 'new-food', 'new-food.jpg', '337035', 'image/jpeg', 0, '2018-05-22 13:33:12', '2018-05-22 13:33:12'),
(31, 1, 'people.jpg', 'people', 'people.jpg', '286024', 'image/jpeg', 0, '2018-05-22 13:48:12', '2018-05-22 13:48:12'),
(32, 1, 'poor-homeless.jpg', 'poor-homeless', 'poor-homeless.jpg', '191546', 'image/jpeg', 0, '2018-05-22 13:48:13', '2018-05-22 13:48:13'),
(36, 5, 'old-car.jpg', 'old-car', 'old-car.jpg', '599935', 'image/jpeg', 0, '2018-05-22 13:55:15', '2018-05-22 13:55:15'),
(37, 5, 'old-car-2.jpg', 'old-car-2', 'old-car-2.jpg', '519733', 'image/jpeg', 0, '2018-05-22 13:55:16', '2018-05-22 13:55:16'),
(38, 5, 'old-yello-car.jpeg', 'old-yello-car', 'old-yello-car.jpeg', '336414', 'image/jpeg', 0, '2018-05-22 13:55:16', '2018-05-22 13:55:16'),
(39, 5, 'telescope-2127704_1280.jpg', 'telescope-2127704-1280', 'telescope-2127704-1280.jpg', '123176', 'image/jpeg', 0, '2018-05-22 14:00:56', '2018-05-22 14:00:56'),
(40, 5, 'injection.jpeg', 'injection', 'injection.jpeg', '70134', 'image/jpeg', 0, '2018-05-22 14:07:55', '2018-05-22 14:07:55'),
(41, 5, 'office-tobs.jpeg', 'office-tobs', 'office-tobs.jpeg', '147747', 'image/jpeg', 0, '2018-05-22 14:16:26', '2018-05-22 14:16:26'),
(42, 5, 'pets-healthy.jpeg', 'pets-healthy', 'pets-healthy.jpeg', '118943', 'image/jpeg', 0, '2018-05-22 14:22:04', '2018-05-22 14:22:04'),
(43, 5, 'pexels-photo-296888.jpeg', 'pexels-photo-296888', 'pexels-photo-296888.jpeg', '121501', 'image/jpeg', 0, '2018-05-22 14:26:27', '2018-05-22 14:26:27'),
(44, 5, 'pexels-photo-414645.jpeg', 'pexels-photo-414645', 'pexels-photo-414645.jpeg', '244889', 'image/jpeg', 0, '2018-05-22 14:26:27', '2018-05-22 14:26:27');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(21, '2014_10_12_000000_create_users_table', 1),
(22, '2014_10_12_100000_create_password_resets_table', 1),
(23, '2017_03_05_153630_create_categories_table', 1),
(24, '2017_03_11_154724_create_options_table', 2),
(29, '2017_03_17_163533_create_campaigns_table', 3),
(30, '2017_03_17_190257_setup_countries_table', 3),
(33, '2017_03_25_141611_create_rewards_table', 4),
(34, '2017_03_25_164308_create_updates_table', 5),
(35, '2017_03_25_174610_create_faqs_table', 6),
(57, '2016_07_23_110550_create_social_accounts_table', 7),
(58, '2016_08_28_095425_create_post_table', 7),
(59, '2017_03_26_141054_create_sessions_table', 7),
(60, '2017_04_01_115454_create_payments_table', 7),
(61, '2017_07_22_154825_create_withdrawal_preference', 7),
(62, '2017_07_22_184150_create_withdrawal_requests', 7),
(63, '2016_08_06_145145_create_language_table', 8),
(64, '2017_11_25_103526_create_media_table', 9),
(65, '2017_08_05_205220_create_comments_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
CREATE TABLE `options` (
  `id` int(10) UNSIGNED NOT NULL,
  `option_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_value` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `option_key`, `option_value`) VALUES
(1, 'currency_sign', 'USD'),
(2, 'enable_stripe', '1'),
(3, 'stripe_test_mode', '1'),
(4, 'paypal_receiver_email', 'MerchantKennethNBoyd@teleworm.us'),
(5, 'stripe_test_secret_key', 'sk_test_tJeAdA1KbhiYV8I8bfPmJcOL'),
(6, 'stripe_test_publishable_key', 'pk_test_P3TFmKrvT7l29Zpyy1f4pwk8'),
(7, 'stripe_live_secret_key', NULL),
(8, 'stripe_live_publishable_key', NULL),
(9, 'date_format', 'd/m/Y'),
(10, 'time_format', 'H:i'),
(11, 'site_name', 'HelpUs'),
(12, 'site_title', 'HelpUs'),
(13, 'email_address', 'themeqx@gmail.com'),
(14, 'default_timezone', 'Africa/Abidjan'),
(15, 'date_format_custom', 'd/m/Y'),
(16, 'time_format_custom', 'H:i'),
(17, 'enable_paypal', '1'),
(18, 'enable_paypal_sandbox', '1'),
(19, 'additional_css', NULL),
(20, 'additional_js', NULL),
(21, 'footer_about_us', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry\r\n\r\nLorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.'),
(22, 'footer_about_us_read_more_text', NULL),
(23, 'footer_address', '<li><i class=\"fa fa-map-marker\"></i> <span>4122 Riverside Drive\r\n<br /> Clarkesville, GA 30523 <br /> United States</span></li>\r\n<li><i class=\"fa fa-phone\"></i> <span>+01 234 567890</span></li>\r\n<li><i class=\"fa fa-envelope-o\"></i> <span>demo@themeqx.com</span></li>'),
(24, 'copyright_text', '[copyright_sign] [year] [site_name], All rights reserved.'),
(25, 'logo', '6'),
(26, 'logo_settings', 'show_image'),
(27, 'enable_social_login', '1'),
(28, 'enable_facebook_login', '1'),
(29, 'enable_google_login', '1'),
(30, 'fb_app_id', '807346162754117'),
(31, 'fb_app_secret', '6b93030d5c4f2715aa9d02be93256fbd'),
(32, 'google_client_id', '62019812075-0sp3u7h854tp7aknl1m8q7ens0pm4im0.apps.googleusercontent.com'),
(33, 'google_client_secret', 'xK8SHn-ds4GJtVSL95ExTzw3'),
(34, 'campaign_owner_commission', '80'),
(35, 'enable_disqus_comment', '0'),
(36, 'disqus_shortname', 'tclassifieds'),
(37, 'enable_bank_transfer', '1'),
(38, 'bank_swift_code', 'USDIIEDH'),
(39, 'account_number', '9879878798798'),
(40, 'branch_name', 'Bank of America'),
(41, 'branch_address', '7510 147th St W Apple Valley, MN 55124'),
(42, 'account_name', 'Dinger Brad'),
(43, 'iban', '2343'),
(44, 'enable_recaptcha_login', '1'),
(45, 'recaptcha_site_key', '6LfSyyMUAAAAAPA6f8iIP7WB51Bw3xKipBHlvdz_'),
(46, 'recaptcha_secret_key', '6LfSyyMUAAAAAHeMy-PyshlTJcbE3sbsddOGw6gk'),
(47, 'enable_recaptcha_registration', '1'),
(48, 'enable_recaptcha_contact_form', '1'),
(49, 'currency_position', 'left'),
(50, 'default_storage', 'public'),
(51, 'enable_cookie_alert', '1'),
(52, 'cookie_message', 'Cookies help us deliver services, By browsing our website, you agree to our use of cookies.'),
(53, 'cookie_learn_page', '1'),
(54, 'enable_comments', '1'),
(55, 'enable_twitter_login', '1'),
(56, 'twitter_consumer_key', 'LlngSMKGqvY4jTarS6KiHBHhL'),
(57, 'twitter_consumer_secret', 'aCSUXxutqBhjbLwiTNsMCK5g4NprjhWfVx3ZzySgf5G5lkCPdQ');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('mhshohel89@gmail.com', '$2y$10$E1DusUmbq0vMeW0D1tnJOOtCe0XCeBPDng1aP.agQjr8XvpnLWhuq', '2017-10-31 11:21:21');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reward_id` int(11) DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('initial','pending','success','failed','declined','dispute') COLLATE utf8mb4_unicode_ci DEFAULT 'initial',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_last4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_exp_month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_exp_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `charge_id_or_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `local_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_created` int(11) DEFAULT NULL,
  `contributor_name_display` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_swift_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `name`, `email`, `campaign_id`, `user_id`, `reward_id`, `amount`, `payment_method`, `status`, `currency`, `token_id`, `card_last4`, `card_id`, `card_brand`, `card_country`, `card_exp_month`, `card_exp_year`, `client_ip`, `charge_id_or_token`, `payer_email`, `description`, `local_transaction_id`, `payment_created`, `contributor_name_display`, `bank_swift_code`, `account_number`, `branch_name`, `branch_address`, `account_name`, `iban`, `created_at`, `updated_at`) VALUES
(1, 'John', 'Doe', 13, NULL, NULL, '50.00', 'bank_transfer', 'pending', 'USD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TRAN_1507659037OFP2ZP', NULL, 'full_name', '234234', '3453453§', 'Bank', 'address', 'Account name', 'Iban', '2017-10-10 12:10:37', '2017-10-10 12:10:37'),
(2, 'Kimberly A. Harrison', 'admin@demo.com', 6, 6, 11, '70.00', 'bank_transfer', 'success', 'USD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TRAN_1510499657VH8LOB', NULL, 'full_name', '234234', '3453453§', 'American Peoples Bank', 'California', 'Stiven Gerrud', 'Iban', '2017-11-12 09:14:17', '2017-11-12 09:14:17'),
(3, 'Kimberly A. Harrison', 'admin@demo.com', 9, 6, NULL, '40.00', 'stripe', 'success', 'USD', NULL, '4242', 'card_1CPETAIdV7MTb07GRfbf3Yaq', 'Visa', NULL, '12', '2019', NULL, 'ch_1CPETGIdV7MTb07GBk4SP2rx', NULL, 'Innovative Nano Blade | World\'s Smallest Tactical Pocket Knife [Contributing]', NULL, 1525719242, 'full_name', NULL, NULL, NULL, NULL, NULL, NULL, '2018-05-07 12:54:03', '2018-05-07 12:54:03'),
(4, 'Kimberly A. Harrison', 'admin@demo.com', 9, 6, NULL, '20.00', 'stripe', 'success', 'USD', NULL, '4242', 'card_1CTF61IdV7MTb07GjocTnK3F', 'Visa', NULL, '12', '2019', NULL, 'ch_1CTF67IdV7MTb07GRsPv8qvC', NULL, 'Innovative Nano Blade | World\'s Smallest Tactical Pocket Knife [Contributing]', NULL, 1526674963, 'full_name', NULL, NULL, NULL, NULL, NULL, NULL, '2018-05-18 14:22:48', '2018-05-18 14:22:48'),
(5, 'Kimberly A. Harrison', 'admin@demo.com', 16, 6, NULL, '110.00', 'stripe', 'success', 'USD', NULL, '4242', 'card_1CTx6PIdV7MTb07GUH9gxqWc', 'Visa', NULL, '12', '2021', NULL, 'ch_1CTx6UIdV7MTb07GFZNteWcr', NULL, 'Coffee maker production [Contributing]', NULL, 1526844122, 'full_name', NULL, NULL, NULL, NULL, NULL, NULL, '2018-05-20 13:22:03', '2018-05-20 13:22:03'),
(6, 'Kimberly A. Harrison', 'admin@demo.com', 16, 6, NULL, '5000.00', 'bank_transfer', 'success', 'USD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TRAN_15268441728FF3WU', NULL, 'full_name', '234234', '2342342345', 'American Peoples Bank', 'California', 'Stiven Gerrud', 'Iban', '2018-05-20 13:22:52', '2018-05-20 13:28:28');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_content` longtext COLLATE utf8mb4_unicode_ci,
  `feature_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('post','page') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `show_in_header_menu` tinyint(4) DEFAULT NULL,
  `show_in_footer_menu` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `slug`, `post_content`, `feature_image`, `type`, `status`, `show_in_header_menu`, `show_in_footer_menu`, `created_at`, `updated_at`) VALUES
(1, 6, 'Cookie and Privacy Policy', 'cookie-and-privacy-policy', '<p><img alt=\"cookie.jpg\" src=\"http://localhost/helpus/source/public/uploads/images/cookie.jpg\" style=\"width: 800px; height: 450px;\" /></p>\r\n\r\n<p>1.&nbsp;Cookies help us deliver services, By browsing our website, you agree to our use of cookies.</p>\r\n\r\n<p>2. We are not selling your personal data</p>\r\n\r\n<p>3. Your Personal Data will store to our server securly</p>', NULL, 'page', 1, 0, 1, '2018-05-14 12:40:11', '2018-05-14 12:49:25'),
(2, 6, 'About Us', 'about-us', '<p>Lorem ipsum dolor sit amet, et animal deserunt similique mel, illud erant vulputate mea ne. Pro postea imperdiet ad, appareat officiis id eam. At velit neglegentur vis. Ne ius graece deseruisse conclusionemque, at audiam suavitate usu. Persecuti inciderint dissentiunt vix et, primis gloriatur vis at, id vel dolor partem.</p>\r\n\r\n<p>Qui prompta offendit honestatis id, sed nusquam oportere deterruisset ut, has etiam evertitur aliquando eu. Nam ne hinc option utroque, nec no paulo laoreet. In bonorum oportere vis, cu omnium officiis periculis sed, no eos posse inani saperet. Ei vim liber ludus graecis, quo graeci aliquid et. Ei quod lorem persequeris vim. Aperiri temporibus eu pro.</p>\r\n\r\n<p>Vis clita civibus contentiones ex, eam idque iisque ne. Te modo mollis eam, vim iriure imperdiet id. Latine expetendis mea cu, et mei tale appareat moderatius, dolore dolorum cu his. At vel quot legendos, mei no exerci ceteros voluptaria. Illud everti sanctus ut usu, nam ei paulo noster, te dolorem epicurei facilisis his.</p>\r\n\r\n<p>Ea has ipsum vocibus nostrum. Te eos harum accusam accommodare, ex qui munere populo. Ius ne esse minimum patrioque. No dolores appareat constituam vim. Sit in utroque omnesque. Qui ei ridens accusata, dignissim expetendis duo et.</p>\r\n\r\n<p>Pri an justo possim, est in feugait tibique accusam. Mei et suas vide. Malis signiferumque eu pro, sed aliquip interesset vituperatoribus an, in qui probo propriae. His doctus efficiendi cotidieque eu.</p>', NULL, 'page', 1, 1, 0, '2018-05-21 14:39:30', '2018-05-21 14:39:30');

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

DROP TABLE IF EXISTS `rewards`;
CREATE TABLE `rewards` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `estimated_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `user_id`, `campaign_id`, `amount`, `description`, `estimated_delivery`, `quantity`) VALUES
(1, 1, 1, '30.00', '1 honorary crest with bless. child will be happy with your donation, you are invited to visit', '2018-12', 40),
(3, 1, 1, '40.00', 'You are geek, your name will be print in our nameplate with a historic sign as doner', '2018-11', 1),
(4, 5, 4, '50.00', 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour', '2023-01', 10),
(5, 6, 8, '15.00', 'The nature will save, you will get 15 HD photos', '2020-06', 10),
(6, 6, 8, '25.00', 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.', '2021-10', 5),
(7, 6, 9, '35.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry', '2022-07', 20),
(8, 6, 9, '45.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry', '2021-10', 15),
(9, 6, 9, '55.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry', '2021-10', 10),
(10, 6, 6, '50.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', '2022-11', 20),
(11, 6, 6, '70.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', '2023-07', 25),
(12, 6, 6, '80.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', '2022-11', 30),
(13, 6, 6, '90.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. \r\nLorem Ipsum is simply dummy text of the printing and typesetting industry.', '2023-07', 35),
(14, 6, 6, '100.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', '2023-12', 10),
(15, 1, 10, '20.00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.', '2024-11', 80),
(16, 5, 17, '100.00', '10 packs injections (20 injection each pack)', '2024-02', 50),
(17, 5, 17, '200.00', '30 packs injections (10 items each pack)', '2024-07', 60),
(18, 5, 16, '100.00', '1 coffee machine will send you to use it or resale', '2024-02', 100),
(19, 5, 16, '150.00', '2 coffee machine you will get to use or resale', '2024-05', 120),
(20, 5, 16, '200.00', '3 coffee machine you will get to use it or gift to your nearest person', '2024-05', 200),
(21, 5, 5, '40.00', 'A car will be sent to you for your personal  purpose and you can\'t sell it again', '2025-02', 10),
(22, 5, 5, '80.00', 'Car tyre you will get for re-sell, enjoy this reward', '2024-07', 50),
(23, 5, 5, '100.00', '4 refurbished car you can sell after getting from us, we will send you as soon as possible', '2024-12', 60),
(24, 5, 5, '160.00', 'Get 3% share from our company and you will get the revenue equally', '2023-11', 10);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_accounts`
--

DROP TABLE IF EXISTS `social_accounts`;
CREATE TABLE `social_accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `provider_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `updates`
--

DROP TABLE IF EXISTS `updates`;
CREATE TABLE `updates` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `updates`
--

INSERT INTO `updates` (`id`, `user_id`, `campaign_id`, `title`, `description`, `image_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'want to some update about your classified store', 'Themeqx Classified is one of the best classified ever. Updated', NULL, '2017-03-25 10:59:01', '2017-03-25 11:29:51'),
(3, 1, 2, 'Thank you our supporter', 'We are getting huge response from your doner and you are a part of this Thank you very much', NULL, '2017-03-25 11:33:00', '2017-03-25 11:33:00'),
(4, 1, 1, 'Love all supporters', 'Let us say thank to all of your supporters. They are awesome', NULL, '2017-03-25 13:29:55', '2017-03-25 13:29:55'),
(5, 1, 1, 'Few need more', 'We need few more supporters to reach our goal\r\n\r\nThanks', NULL, '2017-03-25 13:30:28', '2017-03-25 13:30:28'),
(6, 1, 1, 'Donation amount has been increased', 'From now, donation amount has been increased.\r\n\r\nstay with us to get latest updates\r\n\r\nBest regards', NULL, '2017-03-25 13:31:11', '2017-03-25 13:31:11'),
(7, 6, 9, 'Update one', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry', 11, '2017-04-09 13:06:36', '2018-05-18 14:33:37'),
(8, 6, 9, 'Update two', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry\r\n\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry', NULL, '2017-04-09 13:06:46', '2017-04-09 13:06:46'),
(9, 6, 9, 'Update three', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry', NULL, '2017-04-09 13:06:56', '2017-04-09 13:06:56'),
(10, 1, 10, 'Some angel required', 'We need really some angels', NULL, '2017-04-13 13:36:04', '2017-04-13 13:36:04'),
(11, 6, 9, 'Our Event will be start soon', 'This is a friendly notification that our event will be start soon and we are seeking volunteers from you guys, Please apply now.', 10, '2018-05-18 14:31:10', '2018-05-18 14:31:10'),
(12, 5, 16, 'Coffee machine started send response', 'Lorem ipsum dolor sit amet, ius nisl doctus prodesset ei, cu urbanitas appellantur per, eu eos tation tollit eloquentiam. Ex vix aliquid graecis albucius. Mel adhuc vidisse an. Ne graece expetenda eam. Noluisse salutatus ex usu. Aliquid impedit delicata at eos.\r\n\r\nEa saepe laudem luptatum duo, legere deleniti no duo. Dolorum similique quo ne, semper oporteat his an. Quod salutatus ad usu. Dicant feugait maluisset vis ne. Ad eos quaeque commune consetetur, ex diam ridens labores usu.', 43, '2018-05-22 14:26:35', '2018-05-22 14:26:35'),
(13, 5, 16, 'A cup of coffee just madden by this coffee machine', 'Lorem ipsum dolor sit amet, ius nisl doctus prodesset ei, cu urbanitas appellantur per, eu eos tation tollit eloquentiam. Ex vix aliquid graecis albucius. Mel adhuc vidisse an. Ne graece expetenda eam. Noluisse salutatus ex usu. Aliquid impedit delicata at eos.\r\n\r\nEa saepe laudem luptatum duo, legere deleniti no duo. Dolorum similique quo ne, semper oporteat his an. Quod salutatus ad usu. Dicant feugait maluisset vis ne. Ad eos quaeque commune consetetur, ex diam ridens labores usu.', 44, '2018-05-22 14:27:21', '2018-05-22 14:27:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active_status` tinyint(4) DEFAULT NULL,
  `request_deletion` tinyint(4) DEFAULT NULL,
  `request_deletion_time` datetime DEFAULT NULL,
  `cancel_deletion_time` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `country_id`, `gender`, `address`, `website`, `phone`, `photo`, `user_type`, `active_status`, `request_deletion`, `request_deletion_time`, `cancel_deletion_time`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'john@doe.com', '$2y$10$aMqPj6hEcYcu2mtwHksH0.qIbRPkZeHiKWPjPLWRs6wlq6UA.s6Bm', NULL, NULL, NULL, NULL, NULL, NULL, 'user', 1, NULL, NULL, NULL, 'tiyorRIQ8oDjaz0r1Z7HJ5sf8UtSRCvgvhPv0GDSEDE0tyeG7OV0ZilSYnjn', '2017-03-05 12:23:01', '2017-04-13 14:02:49'),
(2, 'Anita W. Bryant', 'AnitaWBryant@rhyta.com', '$2y$10$4YsQF7kv3D09t/gx281zWOWj0Iob/tpgOXeI/zT046WY3oBU82NdG', NULL, NULL, NULL, NULL, NULL, '9', 'user', 1, NULL, NULL, NULL, 'MrY6AOkKZH1zKhe8lT8aCHxoyaGqw5hKOxqQ2AlX8AOIfW5n8YM6bh3BdSB5', '2017-04-02 09:01:41', '2017-07-06 11:33:42'),
(3, 'Kathy R. Gilkey', 'KathyRGilkey@jourrapide.com', '$2y$10$KC5umKosFDyao8t1goqbK.g4GxxvBeaGZgv14OCvJgzmQgvpZxnr.', NULL, NULL, NULL, NULL, NULL, NULL, 'user', 1, NULL, NULL, NULL, 'ZYxZRttNPfnh3UhMg14Hc6lsIfbjEriVr5y382k6qepo8Eoz6fZflAMYwlbI', '2017-04-02 09:02:29', '2017-04-02 09:02:29'),
(5, 'John R. Coleman', 'JohnRColeman@armyspy.com', '$2y$10$JqLnjAFxFMiLiK1X2shPuew5IA.XU7/.NvBoh/8b0bmcCQp./FkTu', NULL, NULL, NULL, NULL, NULL, '19', 'user', 1, NULL, NULL, NULL, '5IJSS3NJ35Z4qOqLVyE59JgGfZZP946ZPPkR6g41krJdUxCF9F7Sxw8FHvmU', '2017-04-02 09:04:59', '2018-05-20 13:09:16'),
(6, 'Kimberly A. Harrison', 'admin@demo.com', '$2y$10$HdzFUGG7xTJGgj.3Pr5PhO.p0KtESK6.Y0J.Z/0kzBu/jL0Q4v8Z2', 235, 'male', 'London, UK', NULL, '02569841', '9', 'admin', 1, NULL, NULL, NULL, 'OlSwPdgcZJvpuVbGoQAQb5e8OHHXlSIMwyv3Rb5y4QJ1isYDQGkeUnLCTdYK', '2017-04-02 09:05:33', '2018-05-14 13:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_preferences`
--

DROP TABLE IF EXISTS `withdrawal_preferences`;
CREATE TABLE `withdrawal_preferences` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `default_withdrawal_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_holders_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `swift_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name_full` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdrawal_preferences`
--

INSERT INTO `withdrawal_preferences` (`id`, `user_id`, `default_withdrawal_account`, `paypal_email`, `bank_account_holders_name`, `bank_account_number`, `swift_code`, `bank_name_full`, `bank_branch_name`, `bank_branch_city`, `bank_branch_address`, `country_id`, `created_at`, `updated_at`) VALUES
(1, 6, 'paypal', 'pay@harrison.me', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2018-05-21 14:35:17', '2018-05-21 14:35:17');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_requests`
--

DROP TABLE IF EXISTS `withdrawal_requests`;
CREATE TABLE `withdrawal_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `total_amount` double(10,2) NOT NULL,
  `withdrawal_amount` double(10,2) NOT NULL,
  `platform_owner_commission` double(10,2) NOT NULL,
  `withdrawal_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_holders_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `swift_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name_full` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD UNIQUE KEY `sessions_id_unique` (`id`);

--
-- Indexes for table `social_accounts`
--
ALTER TABLE `social_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `updates`
--
ALTER TABLE `updates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `withdrawal_preferences`
--
ALTER TABLE `withdrawal_preferences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;
--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;
--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;
--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT for table `social_accounts`
--
ALTER TABLE `social_accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `updates`
--
ALTER TABLE `updates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `withdrawal_preferences`
--
ALTER TABLE `withdrawal_preferences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
