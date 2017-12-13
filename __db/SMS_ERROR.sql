--
-- Скрипт сгенерирован Devart dbForge Studio for MySQL, Версия 7.2.78.0
-- Домашняя страница продукта: http://www.devart.com/ru/dbforge/mysql/studio
-- Дата скрипта: 03.11.2017 9:34:23
-- Версия сервера: 5.5.57-38.9-log
-- Версия клиента: 4.1
--


SET NAMES 'utf8';

INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NO_ERROR', 'Нет ошибки', 0);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NOT_EXISTS', 'Абонент не существует', 1);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NOT_AVAILABLE', 'Абонент не в сети', 6);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NO_SERVICE', 'Нет услуги SMS', 11);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('HARWARE_ERROR', 'Ошибка в телефоне абонента', 12);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('BLOCKED', 'Абонент заблокирован', 13);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NOT_SUPPORTED', 'Нет поддержки SMS', 21);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('TEST_MODE', 'Виртуальная отправка', 200);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('QUEUE_OVERFLOW', 'Переполнена очередь у оператора', 220);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('BUSY', 'Абонент занят', 240);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('SOUND_CONVERT_ERROR', 'Ошибка конвертации звука', 241);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('VOICEMAIL', 'Зафиксирован автоответчик', 242);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NO_CONRACT', 'Не заключен договор', 243);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('SPAM_DISABLED', 'Рассылки запрещены', 244);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NO_STATUS', 'Статус не получен', 245);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('TIME_RANGE_LIMIT', 'Ограничение по времени', 246);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('PER_DAY_LIMIT', 'Превышен лимит сообщений', 247);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NO_ROURE', 'Нет маршрута', 248);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('WRONG_PHONE_FORMAT', 'Неверный формат номера', 249);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('DENIED_PHONE', 'Номер запрещен настройками', 250);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('PER_PHONE_LIMIT', 'Превышен лимит на один номер', 251);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('SERVICE_PHONE', 'Номер запрещен', 252);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('SPAM_FILTER', 'Запрещено спам-фильтром', 253);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('UNREGISTERED_SERVICE_ID', 'Незарегистрированный sender id', 254);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('REJECTED_BY_OPERATOR', 'Отклонено оператором', 255);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('WRONG_PARAMS', 'Ошибка в параметрах', -1);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('WRONG_AUTH_DATA', 'Неверный логин или пароль', -2);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('NO_MONEY', 'Недостаточно средств на счете Клиента', -3);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('IP_WAS_BLOCKED', 'IP-адрес временно заблокирован из-за частых ошибок', -4);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('WRONG_DATE_FORMAT', 'Неверный формат даты', -5);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('SMS_WAS_BLOCKED', 'Сообщение запрещено (по тексту или по имени)', -6);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('WRONG_CELL_FORMAT', 'Неверный формат номера телефона', -7);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('CANT_DELIVER', 'Сообщение не может быть доставлено', -8);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('SAME_REQUEST_LIMIT', 'Отправка более одного одинакового запроса', -9);
INSERT INTO SMS_ERROR(CODE, NAME, I_CODE) VALUES
('UNKNOWN_ERROR', 'Неизвестная ошибка', 999);