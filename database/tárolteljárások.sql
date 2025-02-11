--pizza tábla értékeinek vissza adása

--pizza_product
BEGIN
    SELECT * FROM pizza;
END


--registAndCheck
DELIMITER //

CREATE PROCEDURE registAndCheck(
    IN p_username VARCHAR(255),
    IN p_password VARCHAR(255),
    OUT p_result INT
)
BEGIN
    DECLARE user_count INT;

    -- Ellenőrizzük, hogy van-e már ilyen felhasználónév
    SELECT COUNT(*) INTO user_count FROM account WHERE username = p_username;
    
    IF user_count > 0 THEN
        SET p_result = 0;  -- Már létezik
    ELSE
        INSERT INTO account(username, password) VALUES (p_username, p_password);
        SET p_result = 1;  -- Sikeres regisztráció
    END IF;
END;
//
DELIMITER ;


--getAccountLogin
DELIMITER //

CREATE PROCEDURE getAccountLogin(
    IN p_username VARCHAR(255)
)
BEGIN
    SELECT id, password FROM users WHERE username = p_username;
END;
//
DELIMITER ;
