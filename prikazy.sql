--vytvorili jsme penzion
CREATE DATABASE penzion DEFAULT CHARACTER SET = 'utf8mb4';

--tento prikaz zaktivuje databzi penzion,  abyhcom enmuseli klikat na ten modry barel, protze tne plugin stoji za prd
USE penzion;

--vytvorime tabulku spravce
CREATE TABLE spravce (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    heslo VARCHAR(255) NOT NULL
);

--vytvorime spravce
INSERT INTO spravce SET username="admin", heslo="papousek123";
INSERT INTO spravce SET username="tony", heslo="brokolice60";

SELECT * FROM spravce;

--vytvorime tabulku "stranka"
CREATE TABLE stranka (
    id VARCHAR(255) PRIMARY KEY,
    titulek VARCHAR(255),
    menu VARCHAR(255),
    obrazek VARCHAR(255),
    obsah TEXT,
    poradi INT DEFAULT 0
);

-- vlozime jednu testovaci stranku
INSERT INTO stranka SET id="domu", titulek="PrimaPenzion", menu="Domů", obrazek="primapenzion-main.jpg", obsah="aaaaaaaaaaaaaaaaaaaaa", poradi="0";
SELECT*FROM stranka; 