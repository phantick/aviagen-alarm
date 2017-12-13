--
-- Скрипт сгенерирован Devart dbForge Studio for MySQL, Версия 7.2.78.0
-- Домашняя страница продукта: http://www.devart.com/ru/dbforge/mysql/studio
-- Дата скрипта: 03.11.2017 9:34:24
-- Версия сервера: 5.5.57-38.9-log
-- Версия клиента: 4.1
--


SET NAMES 'utf8';

INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('NOTFOUND', 'Сообщение не найдено', -3);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('WAIT', 'Ожидает отправки', -1);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('SENT', 'Передано оператору', 0);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('DELIVERED', 'Доставлено', 1);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('READ', 'Прочитано', 2);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('EXPIRED', 'Просрочено', 3);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('CANTDELIVER', 'Невозможно доставить', 20);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('WRONGBUMBER', 'Неверный номер', 22);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('FORBIDDEN', 'Запрещено', 23);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('NOMONEY', 'Недостаточно средств', 24);
INSERT INTO SMS_STATUS(CODE, NAME, I_CODE) VALUES
('NOSERVICE', 'Недоступный номер', 25);