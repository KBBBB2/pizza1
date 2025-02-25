-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Feb 20. 14:06
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
    SELECT * FROM pizza;
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
(1, 'vez', 'ker', 'fel', '$2y$10$Wk8MzmlTeGeYXcCfo6HBsuwyQ1NoFCuJUYBFYQ1Z8hxeLM3aVWEbC', 0, 0, 'mail@gmail.com', '0620', '2025-02-17 12:48:36', NULL),
(9, '', '', 'testfel', '$2y$10$an8cT9iNdT2kS0hXjMED7OaJahPq1qlJlYt7RehpadIFKladVWu9i', 0, 0, NULL, NULL, '2025-02-08 10:10:05', NULL),
(28, 'admin', 'admin', 'admin', '$2y$10$L.qowHKvxLoZeoYExaHtce11TaS7QzFa0NufrVw2m74IdWmvohwoS', 0, 0, 'admin@pizzabazis.hu', '06205556666', '2025-02-16 15:00:25', NULL);


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
(0, 'Valentin nap', 'Február 14.-e Valentin napi kód, ami 02.01. - 03.01. - ig tart', 'valentin_pizza', 'ünnep', 1500.00, '2025-03-01 23:59:00', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
CREATE TABLE `customer_addresses` (
  `customer_user_id` int(8) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `housenumber` varchar(255) DEFAULT NULL,
  `apartmentnumber` varchar(255) DEFAULT NULL
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
  `street` varchar(255) DEFAULT NULL,
  `housenumber` varchar(255) DEFAULT NULL,
  `apartmentnumber` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `deliveryperson_user_id` int(8) DEFAULT NULL,
  `order_id` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `customer_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(9, 'Tenger gyümölcsei pizza', 'ropogós tészta', 'szeletelt', 'nagy', 'paradicsom, mozzarella, garnéla, kagyló, polip, tintahal', 3700);

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
  ADD PRIMARY KEY (`id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `account`
--
ALTER TABLE `account`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT a táblához `pizza`
--
ALTER TABLE `pizza`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

DELIMITER $$
--
-- Események
--
DROP EVENT IF EXISTS `event_unban_users`$$
CREATE DEFINER=`root`@`localhost` EVENT `event_unban_users` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-02-16 23:07:58' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE account 
    SET locked = 0, ban_expires_at = NULL 
    WHERE locked = 1 AND ban_expires_at <= NOW()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
