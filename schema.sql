--
-- Отключение внешних ключей
--
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;

--
-- Установить режим SQL (SQL mode)
--
/*!40101 SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;

--
-- Установка кодировки, с использованием которой клиент будет посылать запросы на сервер
--
SET NAMES 'utf8';

CREATE DATABASE IF NOT EXISTS db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

--
-- Установка базы данных по умолчанию
--
USE db;

--
-- Создать таблицу `merchants`
--
CREATE TABLE IF NOT EXISTS merchants
(
    id   bigint(20) UNSIGNED NOT NULL,
    name varchar(100)        NOT NULL DEFAULT '',
    PRIMARY KEY (id)
)
    ENGINE = INNODB,
    AVG_ROW_LENGTH = 163,
    CHARACTER SET utf8mb4,
    COLLATE utf8mb4_general_ci;

--
-- Создать таблицу `batches`
--
CREATE TABLE IF NOT EXISTS batches
(
    id            char(36)                NOT NULL,
    merchant_id   bigint(20) UNSIGNED     NOT NULL,
    batch_date    date                    NOT NULL,
    batch_ref_num decimal(25, 0) UNSIGNED NOT NULL,
    PRIMARY KEY (id)
)
    ENGINE = INNODB,
    AVG_ROW_LENGTH = 152,
    CHARACTER SET utf8mb4,
    COLLATE utf8mb4_general_ci;

--
-- Создать индекс `UK_batches` для объекта типа таблица `batches`
--
ALTER TABLE batches
    ADD UNIQUE INDEX UK_batches (merchant_id, batch_date, batch_ref_num);

--
-- Создать внешний ключ
--
ALTER TABLE batches
    ADD CONSTRAINT FK_batchs_merchants_id FOREIGN KEY (merchant_id)
        REFERENCES merchants (id) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Создать таблицу `transactions`
--
CREATE TABLE IF NOT EXISTS transactions
(
    id              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    batch_id        char(36)            NOT NULL,
    trans_date      date                NOT NULL,
    trans_type      varchar(20)         NOT NULL DEFAULT '',
    trans_card_type char(2)             NOT NULL DEFAULT '',
    trans_card_num  varchar(20)         NOT NULL DEFAULT '',
    trans_amount    decimal(15, 2)      NOT NULL,
    PRIMARY KEY (id)
)
    ENGINE = INNODB,
    AUTO_INCREMENT = 23460,
    AVG_ROW_LENGTH = 129,
    CHARACTER SET utf8mb4,
    COLLATE utf8mb4_general_ci;

--
-- Создать индекс `IDX_transactions_trans_card_type` для объекта типа таблица `transactions`
--
ALTER TABLE transactions
    ADD INDEX IDX_transactions_trans_card_type (trans_card_type);

--
-- Создать внешний ключ
--
ALTER TABLE transactions
    ADD CONSTRAINT FK_transactions_batches_id FOREIGN KEY (batch_id)
        REFERENCES batches (id) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Восстановить предыдущий режим SQL (SQL mode)
--
/*!40101 SET SQL_MODE = @OLD_SQL_MODE */;

--
-- Включение внешних ключей
--
/*!40014 SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS */;