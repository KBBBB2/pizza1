-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Ápr 26. 12:37
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `pizzabazis`
--
CREATE DATABASE IF NOT EXISTS `pizzabazis` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pizzabazis`;

DELIMITER $$
--
-- Eljárások
--
DROP PROCEDURE IF EXISTS `getAccountLogin`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getAccountLogin` (IN `p_username` VARCHAR(255))   BEGIN
SELECT id, password, locked, disabled FROM account WHERE username = p_username;
END$$

DROP PROCEDURE IF EXISTS `getAllAccount`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getAllAccount` ()   BEGIN
	SELECT * FROM `account`;
END$$

DROP PROCEDURE IF EXISTS `pizza_product`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `pizza_product` ()   BEGIN
    SELECT p.*, fp.discounted_price 
        FROM pizza p
        LEFT JOIN featured_pizzas fp ON p.id = fp.pizza_id;
END$$

DROP PROCEDURE IF EXISTS `registAndCheck`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `registAndCheck` (IN `p_username` VARCHAR(255), IN `p_password` VARCHAR(255), IN `p_firstname` VARCHAR(255), IN `p_lastname` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_phonenumber` VARCHAR(255), OUT `p_result` INT)   BEGIN
    DECLARE user_count INT;
    DECLARE user_email INT;

    -- Ellenőrizzük, hogy van-e már ilyen felhasználónév
    SELECT COUNT(*) INTO user_count FROM account WHERE username = p_username;
    SELECT COUNT(*) INTO user_email FROM account WHERE email = p_email;
    
    IF user_count > 0 THEN
        SET p_result = 0;  -- Már létezik
    ELSEIF user_email > 0 THEN
    	SET p_result = 0;
    ELSE
        INSERT INTO account(username, password, firstname, lastname, email, phonenumber)
        VALUES (p_username, p_password, p_firstname, p_lastname, p_email, p_phonenumber);
        SET p_result = 1;  -- Sikeres regisztráció
    END IF;
END$$

DROP PROCEDURE IF EXISTS `sp_permBan`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_permBan` (IN `accId` INT)   BEGIN
  UPDATE account SET disabled = 1 WHERE id = accId;
END$$

DROP PROCEDURE IF EXISTS `sp_tempBan`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tempBan` (IN `accId` INT)   BEGIN
  UPDATE account SET locked = 1 WHERE id = accId;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` int(8) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `disabled` tinyint(1) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phonenumber` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ban_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `account`
--

INSERT INTO `account` (`id`, `firstname`, `lastname`, `username`, `password`, `locked`, `disabled`, `email`, `phonenumber`, `created`, `ban_expires_at`) VALUES
(40, 'vez', 'ker', 'most', '$2y$10$JM6.75Igo9G/k0oXuavPBeZeCd7VxeSCIwy7fR6fFpHR3CD87lpqG', 0, 0, 'massssil@gmail.com', '06', '2025-04-24 12:20:58', NULL),
(41, 'Keresztnév', 'Vezetéknév', 'admin', '$2y$10$K55cleNQmTlDcLJjsNcvMODhG25KXzBkq50wCwyqxrunvBt7prIpq', 0, 0, 'pizzabazistest@gmail.com', '06405557777', '2025-04-17 19:48:42', NULL),
(42, 'firsnametest', 'lastnametest', 'test', '$2y$10$lP5u4P1L5ukYk32d1yLeq.gjNclTZyCnDWadXJw5JGAUZqCsi.of2', 0, 0, 'test@test.hu', '06207651234', '2025-04-01 08:56:35', NULL),
(123, 'Károly', 'Nagy', 'Karcsi', '$2y$10$1DyG7lw6vFTwsZi4SHAUP.Ivnp7XvfdA58Jmuhwq.gI10oUZhRgXG', 1, 0, 'kar@gmail.com', '08207383454', '2025-04-24 23:52:44', '2025-05-05 03:57:44'),
(125, 'ker', 'vez', 'test12', '$2y$10$nPIfxPjby7I/CuoMmBjDG.q5FRrZ/Q26GjqNqoElMVgb0AivnaNtq', 0, 0, 'mail12@gmail.com', '36065819690', '2025-04-24 12:20:58', NULL),
(126, 'firsnametest', 'lastnametest', 'test123', '$2y$10$DxQuHTnLM/2CmNyne71/k.ismiXMWcU.LoqxxNEtVMfArk.s6vVRe', 0, 0, 'test@test.hu', '06207651234', '2025-04-24 21:52:31', NULL),
(127, 'firsnametest', 'lastnametest', 'testfel3', '$2y$10$xrXsyT896UQ/SaYsEh21vezMFvgw9rvIxCeFjH6IC7kiR75fOuxKK', 0, 0, 'test@mail.hu', '06207651234', '2025-04-14 15:49:58', NULL),
(128, 'fd', 'fd', 'mostasd', '$2y$10$3m7EkAqLzi.DBLwqeOeq4OB3U/zp.Wu3o5T3PdUeKVlvMU5LK31Sm', 1, 0, 'mail@gmail.com', '06305559999', '2025-04-20 18:18:31', '2025-04-26 00:28:31'),
(129, 'firsnametest', 'lastnametest', 'testfel35', '$2y$10$lVfqjAopY6dY2pN8FIAUjuXZb3E9l9sK0vjil3DoCrMnC9rSp3Dui', 0, 1, 'test5@mail.hu', '06207651234', '2025-04-24 12:20:58', NULL),
(131, 'ker', 'vez', 'ÉnVagyokAFutár', '$2y$10$OL2.VTgp2iYBdvi45OHvOuotw1YJHksTPu8EDwqj/rfK0HfVkW8AS', 0, 0, 'deliveryp@pizzabazis.hu', '06305559999', '2025-04-14 18:18:15', NULL),
(132, 'Test', 'User', 'testlogin2221', '$2y$10$rzeuxkn92g9UYZIEgtT7kuVy/phpdqHLoj7/f0.QCQ4oz.jiP00LG', 0, 0, 'testlogin2221@example.com', '123456789', '2025-04-23 09:37:39', NULL),
(133, 'Login', 'Tester', 'testlogin7302', '$2y$10$tnAXzlK.hA.fCzqNOqoraeREAsIU.E59eKDzNmsOS.qpBS/6peyuS', 0, 0, 'testlogin7302@example.com', '987654321', '2025-04-23 09:37:39', NULL),
(134, 'vez', 'ker', 'bfdb', '$2y$10$LW/K19tXisFlS/CnuHRfUu6eF5e4wzXO0s4n/4d.OSORzc8wSM3.2', 0, 0, 'hgfh@gma.hu', '06305559999', '2025-04-24 11:52:47', NULL),
(135, 'k', 'v', 'most123', '$2y$10$zwNeWQp4wkpXSPDn5DMXwOmJhVCo2HKPDAcmqF/aXm3k8d6ZtbdHi', 0, 0, 'KBB9182r@gmail.com', '06205558888', '2025-04-24 12:02:17', NULL),
(137, 'az', 'az', 'most', '$2y$10$OShHHX16SRWdX6nawOZveuy6BzO1il0abpemQxi6mB1mdrfPQbce6', 0, 0, 'KBB@gmail.com', '06305558888', '2025-04-24 12:22:58', NULL),
(138, 'vezeték', 'ker', 'test123456', '$2y$10$GjFN8FuoPDDwwSHArZ22ZuGQmFAsW29tBed29Nucg19q3WaeSx/ky', 0, 0, 'grgrmail@gmail.com', '06305559999', '2025-04-25 14:29:05', NULL),
(139, 'vez', 'ker', 'most12345', '$2y$10$gdzJxYBlVjtEy8j6xKaqPuCkg6gLG4avDVZKLSpc0yQV9ZY0mxepK', 0, 0, 'mailer@gmail.com', '06305559999', '2025-04-24 13:43:03', NULL),
(140, 'firsnametest', 'lastnametest', 'testfel325', '$2y$10$EBpkVyCHw4W1RhtJHaqBg.KzHf0dAF56q.gyEEucpS/ivDP.Xxnr6', 0, 0, 'test52@mail.hu', '06207651234', '2025-04-24 13:44:36', NULL),
(141, 'firsnametest', 'lastnametest', 'testfel3215', '$2y$10$Fho5N/.QsIBw74YwSABoROAyq4iVVJoJfGbAbU2U0VXj9icI4Y0K2', 0, 0, 'test512@mail.hu', '06207651234', '2025-04-24 20:47:35', NULL),
(142, 'firsnametest', 'lastnametest', 'testfel3515', '$2y$10$.22PEhYhcfzVfqAEz1ueGu6x.0BAdwCYtaKIi03orZNCXBElPFP16', 0, 0, 'test5152@mail.hu', '06207651234', '2025-04-24 20:51:28', NULL),
(143, 'firsnametest', 'lastnametest', 'testfel4515', '$2y$10$tlPEJgQ8xhAHPs4uCTKA3u80oUseHG3UM3RxDmaF4RhuRVlFezDXe', 1, 1, 'test5452@mail.hu', '06207651234', '2025-04-25 14:50:35', '2025-04-28 18:55:20'),
(144, 'vez', 'ker', 'Azaz', '$2y$10$uBpdpzCFoXJ6yHvQ0U5QN.cbRBmxFIao2L4Kt.vNcCsHmo1udN1Dy', 0, 0, 'maeeewil@gmail.com', '06207779999', '2025-04-26 09:22:26', NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `coupon`
--

DROP TABLE IF EXISTS `coupon`;
CREATE TABLE `coupon` (
  `id` int(8) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` varchar(50) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `expiration_date` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `coupon`
--

INSERT INTO `coupon` (`id`, `name`, `description`, `code`, `discount_type`, `discount_value`, `expiration_date`, `is_active`) VALUES
(42, 'test1', 'test', 'ezegytestcode', 'test', 1000.00, '2025-06-27 01:55:00', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
CREATE TABLE `customer_addresses` (
  `customer_user_id` int(8) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `postal_code` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `customer_coupon`
--

DROP TABLE IF EXISTS `customer_coupon`;
CREATE TABLE `customer_coupon` (
  `customer_user_id` int(8) NOT NULL,
  `coupon_id` int(8) NOT NULL,
  `used_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `delivery`
--

DROP TABLE IF EXISTS `delivery`;
CREATE TABLE `delivery` (
  `id` int(8) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `postal_code` int(4) DEFAULT NULL,
  `phonenumber` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `deliveryperson_user_id` int(8) DEFAULT NULL,
  `order_id` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `delivery`
--

INSERT INTO `delivery` (`id`, `city`, `address`, `postal_code`, `phonenumber`, `status`, `deliveryperson_user_id`, `order_id`) VALUES
(2, 'Budapest', 'Fő utca 1.', 1011, '06301234567', 'in transit', NULL, 123),
(3, 'Budapest', 'Fő utca 1.', 1011, '06301234567', 'delivered', NULL, 123),
(4, 'Budapest', 'Fő utca 1.', 1011, '06301234567', 'delivered', NULL, 123),
(5, 'Budapest', 'Fő utca 1.', 1011, '06301234567', 'delivered', NULL, 123),
(6, 'Budapest', 'Fő utca 1.', 1011, '06301234567', 'pending', NULL, 123),
(7, 'Pécs', 'ez az utca 10', 8796, '06305559999', 'in transit', NULL, 0),
(8, 'igen', 'igen', 8796, '06305559999', 'in transit', NULL, 0),
(9, 'Budapest', 'Fő utca 1.', 1011, '06301234567', 'delivered', NULL, 123),
(10, 'igen', 'igen', 8796, '06305559999', 'in transit', NULL, 0),
(11, 'igen', 'igen', 8796, '06305559999', 'in transit', NULL, 0),
(12, 'igen', '12 utcaaa', 8796, '062654319690', 'delivered', NULL, 0),
(13, 'igen', '12 utcaaa', 8796, '062654319690', 'in transit', NULL, 0),
(14, 'igen', 'igen', 8796, '06305559999', 'in transit', NULL, 0),
(15, 'pé', 'igen', 3, '06305559999', 'in transit', NULL, 0),
(16, 'igen', 'igen', 8796, '06305559999', 'delivered', NULL, 0),
(17, 'igen', 'ide kérem', 8796, '08207383454', 'in transit', NULL, 0),
(18, 'igen', 'igen', 8796, '06305559999', 'in transit', NULL, 0),
(19, 'igen', 'igen', 8796, '06305559999', 'delivered', NULL, 0),
(20, 'igen', 'igen', 8796, '06305559999', 'in transit', NULL, 0),
(21, 'igen', 'igen', 8796, '06305559999', 'in transit', NULL, 0),
(22, 'a', 'a', 3, '36305808558', 'delivered', NULL, 0),
(23, 'igen', 'igen', 3, '06305559999', 'pending', NULL, 0),
(24, 'a', 'a', 7, '99884447777', 'pending', NULL, 0),
(25, 'Budapest', 'Fő utca 1.', 1011, '06301234567', 'pending', NULL, 123),
(26, 'Budapest', 'Fő utca 1.', 1011, '06301234567', 'delivered', NULL, 123),
(27, 'igen', 'igen', 8796, '0630555999', 'pending', NULL, 0),
(28, 'igen', 'igen', 8796, '0630555999', 'pending', NULL, 0),
(29, 'a', 'a', 77, '0620333222', 'pending', NULL, 0),
(30, 'a', 'a', 7, '0620123456', 'delivered', NULL, 0),
(31, 'a', 'a', 12, '0620123456', 'pending', NULL, 0),
(32, 'a', 'a', 12, '063055599999', 'pending', NULL, 0),
(33, 'a', 'a', 31, '06305551234567', 'pending', NULL, 0),
(34, 'a', 'a', 1212, '06201234567', 'pending', NULL, 0),
(35, 'a', 'a', 12, '0620123456', 'pending', NULL, 0),
(36, 'a', 'a', 3, '36201234567', 'pending', NULL, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `featured_pizzas`
--

DROP TABLE IF EXISTS `featured_pizzas`;
CREATE TABLE `featured_pizzas` (
  `id` int(8) NOT NULL,
  `pizza_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `crust` varchar(255) NOT NULL,
  `cutstyle` varchar(255) NOT NULL,
  `pizzasize` varchar(255) NOT NULL,
  `ingredient` varchar(255) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `discounted_price` decimal(10,0) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `featured_pizzas`
--

INSERT INTO `featured_pizzas` (`id`, `pizza_id`, `name`, `crust`, `cutstyle`, `pizzasize`, `ingredient`, `price`, `discounted_price`, `updated_at`) VALUES
(958, 9, 'Tenger gyümölcsei pizza', 'ropogós tészta', 'szeletelt', 'nagy', 'paradicsom, mozzarella, garnéla, kagyló, polip, tintahal', 3800, 3230, '2025-04-24 16:19:33'),
(959, 1, 'Margherita pizza', 'vékony tészta', 'szeletelt', 'közepes', 'paradicsom, mozzarella, bazsalikom', 2500, 2250, '2025-04-24 16:19:33'),
(960, 6, 'Hawaii pizza', 'vékony tészta', 'szeletelt', 'közepes', 'paradicsom, mozzarella, sonka, ananász', 3100, 2635, '2025-04-24 16:19:33');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `orderitem`
--

DROP TABLE IF EXISTS `orderitem`;
CREATE TABLE `orderitem` (
  `id` int(8) NOT NULL,
  `quantity` int(4) NOT NULL,
  `order_id` int(8) DEFAULT NULL,
  `pizza_id` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(8) NOT NULL,
  `comment` varchar(1000) DEFAULT NULL,
  `paymenttransactionid` varchar(255) DEFAULT NULL,
  `customer_id` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(39, 41, 'e7125a88e321babfd13b288c1232bf05', '2025-04-03 02:22:54', '2025-04-03 01:22:54');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `pizza`
--

DROP TABLE IF EXISTS `pizza`;
CREATE TABLE `pizza` (
  `id` int(8) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `crust` varchar(50) DEFAULT NULL,
  `cutstyle` varchar(50) DEFAULT NULL,
  `pizzasize` varchar(50) DEFAULT NULL,
  `ingredient` varchar(1000) DEFAULT NULL,
  `price` decimal(10,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `pizza`
--

INSERT INTO `pizza` (`id`, `name`, `crust`, `cutstyle`, `pizzasize`, `ingredient`, `price`) VALUES
(1, 'Margherita pizza', 'vékony tészta', 'szeletelt', 'közepes', 'paradicsom, mozzarella, bazsalikom', 2500),
(2, 'Pepperoni pizza', 'normál tészta', 'szeletelt', 'nagy', 'paradicsom, mozzarella, pepperoni', 3000),
(3, 'Vegetáriánus pizza', 'vékony tészta', 'háromszög alakú', 'közepes', 'paradicsom, mozzarella, kaliforniai paprika, olívabogyó, gomba, hagyma', 2900),
(4, 'Négy sajtos pizza', 'ropogós tészta', 'szeletelt', 'közepes', 'mozzarella, cheddar, kék sajt, parmezán', 2700),
(5, 'Húsimádó pizza', 'vastag tészta', 'szeletelt', 'nagy', 'paradicsom, mozzarella, sonka, szalámi, kolbász, marhahús', 3200),
(6, 'Hawaii pizza', 'vékony tészta', 'szeletelt', 'közepes', 'paradicsom, mozzarella, sonka, ananász', 3100),
(7, 'BBQ csirke pizza', 'vastag tészta', 'háromszög alakú', 'nagy', 'BBQ szósz, mozzarella, grillezett csirke, lilahagyma, koriander', 3500),
(8, 'Csípős pizza', 'vékony tészta', 'szeletelt', 'közepes', 'paradicsom, mozzarella, fűszeres szalami, chili paprika', 2800),
(9, 'Tenger gyümölcsei pizza', 'ropogós tészta', 'szeletelt', 'nagy', 'paradicsom, mozzarella, garnéla, kagyló, polip, tintahal', 3800);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE `review` (
  `id` int(8) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `message` varchar(1000) DEFAULT NULL,
  `rated` int(4) NOT NULL,
  `order_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `userroles`
--

DROP TABLE IF EXISTS `userroles`;
CREATE TABLE `userroles` (
  `id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `userroles`
--

INSERT INTO `userroles` (`id`, `user_id`, `role`) VALUES
(40, 4, 'customer'),
(41, 5, 'admin'),
(42, 6, 'customer'),
(123, 57, 'delivery'),
(124, 58, 'customer'),
(125, 59, 'customer'),
(126, 60, 'customer'),
(127, 61, 'customer'),
(128, 62, 'customer'),
(129, 63, 'customer'),
(130, 64, 'customer'),
(131, 65, 'deliveryPerson'),
(132, 66, 'customer'),
(133, 67, 'customer'),
(134, 68, 'customer'),
(135, 69, 'customer'),
(136, 70, 'customer'),
(137, 71, 'customer'),
(138, 72, 'customer'),
(139, 73, 'customer'),
(140, 74, 'customer'),
(141, 75, 'customer'),
(142, 76, 'customer'),
(143, 77, 'customer'),
(144, 78, 'customer');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `coupon`
--
ALTER TABLE `coupon`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `featured_pizzas`
--
ALTER TABLE `featured_pizzas`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `orderitem`
--
ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `pizza`
--
ALTER TABLE `pizza`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `userroles`
--
ALTER TABLE `userroles`
  ADD PRIMARY KEY (`user_id`) USING BTREE;

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `account`
--
ALTER TABLE `account`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT a táblához `coupon`
--
ALTER TABLE `coupon`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT a táblához `delivery`
--
ALTER TABLE `delivery`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT a táblához `featured_pizzas`
--
ALTER TABLE `featured_pizzas`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=961;

--
-- AUTO_INCREMENT a táblához `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT a táblához `pizza`
--
ALTER TABLE `pizza`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT a táblához `userroles`
--
ALTER TABLE `userroles`
  MODIFY `user_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

DELIMITER $$
--
-- Események
--
DROP EVENT IF EXISTS `event_unban_users`$$
CREATE DEFINER=`root`@`localhost` EVENT `event_unban_users` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-02-16 23:07:58' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE account 
    SET locked = 0, ban_expires_at = NULL 
    WHERE locked = 1 AND ban_expires_at <= NOW()$$

DROP EVENT IF EXISTS `update_featured_pizzas`$$
CREATE DEFINER=`root`@`localhost` EVENT `update_featured_pizzas` ON SCHEDULE EVERY 1 DAY STARTS '2025-02-23 18:19:33' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- Töröljük a korábbi kiemelt pizzákat
    DELETE FROM featured_pizzas;
    
    -- Beszúrjuk a 3 véletlenszerű pizzát, az eredeti árat és a kedvezményes árat (5%, 10% vagy 15% kedvezménnyel)
    INSERT INTO featured_pizzas (pizza_id, name, crust, cutstyle, pizzasize, ingredient, price, discounted_price)
    SELECT 
        id, 
        name, 
        crust, 
        cutstyle, 
        pizzasize, 
        ingredient,
        price,
        price * (1 - (FLOOR(1 + (RAND() * 3)) * 0.05)) AS discounted_price
    FROM pizza
    ORDER BY RAND()
    LIMIT 3;
END$$

DROP EVENT IF EXISTS `expire_coupons`$$
CREATE DEFINER=`root`@`localhost` EVENT `expire_coupons` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-03-30 00:04:22' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE coupon
  SET is_active = 0
  WHERE expiration_date < NOW() AND is_active <> 0$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
