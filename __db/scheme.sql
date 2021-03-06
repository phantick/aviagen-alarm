CREATE TABLE SMS_STATUS (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  CODE varchar(50) NOT NULL,
  NAME varchar(255) NOT NULL,
  I_CODE bigint(20) NOT NULL,
  PRIMARY KEY (ID),
  UNIQUE INDEX UK_SMS_STATUS_I_CODE (I_CODE),
  UNIQUE INDEX UK_SMS_STATUS_ID (ID)
)
ENGINE = INNODB
AUTO_INCREMENT = 23
AVG_ROW_LENGTH = 1489
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE SMS_ERROR (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  CODE varchar(50) NOT NULL,
  NAME varchar(255) NOT NULL,
  I_CODE bigint(20) NOT NULL,
  PRIMARY KEY (ID),
  UNIQUE INDEX CODE (CODE),
  UNIQUE INDEX I_CODE (I_CODE)
)
ENGINE = INNODB
AUTO_INCREMENT = 39
AVG_ROW_LENGTH = 468
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE EVENT_TYPE (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  CODE varchar(50) NOT NULL,
  NAME varchar(50) NOT NULL,
  DESCR varchar(255) DEFAULT NULL,
  PRIMARY KEY (ID)
)
ENGINE = INNODB
AUTO_INCREMENT = 3
AVG_ROW_LENGTH = 16384
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE MAILBOX (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  NAME varchar(128) NOT NULL,
  QUEUE tinyint(1) NOT NULL DEFAULT 0,
  PASSWORD varchar(50) NOT NULL,
  PRIMARY KEY (ID),
  UNIQUE INDEX NAME (NAME)
)
ENGINE = INNODB
AUTO_INCREMENT = 6
AVG_ROW_LENGTH = 8192
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE EVENT (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  EVENT_TYPE_ID bigint(20) UNSIGNED NOT NULL,
  TS timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  TEXT varchar(2048) DEFAULT NULL,
  M_ID varchar(255) NOT NULL,
  MAILBOX_ID bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (ID),
  CONSTRAINT FK_EVENT_EVENT_TYPE_ID FOREIGN KEY (EVENT_TYPE_ID)
  REFERENCES EVENT_TYPE (ID) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT FK_EVENT_MAILBOX_ID FOREIGN KEY (MAILBOX_ID)
  REFERENCES MAILBOX (ID) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 76
AVG_ROW_LENGTH = 4096
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE ROLE (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  CODE varchar(32) NOT NULL,
  NAME varchar(50) NOT NULL,
  PRIMARY KEY (ID),
  UNIQUE INDEX CODE (CODE),
  UNIQUE INDEX NAME (NAME)
)
ENGINE = INNODB
AUTO_INCREMENT = 22
AVG_ROW_LENGTH = 8192
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE USER (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  LOGIN varchar(32) NOT NULL,
  PASSWORD varchar(32) NOT NULL,
  NAME varchar(255) NOT NULL,
  EMAIL varchar(255) NOT NULL,
  ROLE_ID bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (ID),
  UNIQUE INDEX EMAIL (EMAIL),
  INDEX IDX_USER_ROLE_ID (ROLE_ID),
  UNIQUE INDEX LOGIN (LOGIN),
  CONSTRAINT FK_USER_ROLE_ID FOREIGN KEY (ROLE_ID)
  REFERENCES ROLE (ID) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 16
AVG_ROW_LENGTH = 5461
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE MOBILE_USER (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  NAME varchar(255) NOT NULL,
  MOBILE varchar(20) NOT NULL,
  USER_ID bigint(20) UNSIGNED DEFAULT NULL,
  EMAIL varchar(255) DEFAULT NULL,
  PRIMARY KEY (ID),
  UNIQUE INDEX MOBILE (MOBILE),
  UNIQUE INDEX USER_ID (USER_ID),
  CONSTRAINT FK_MOBILEUSER_USER_ID FOREIGN KEY (USER_ID)
  REFERENCES USER (ID) ON DELETE SET NULL ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 11
AVG_ROW_LENGTH = 16384
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE MAILBOX_USER (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  MAILBOX_ID bigint(20) UNSIGNED NOT NULL,
  MOBILE_USER_ID bigint(20) UNSIGNED NOT NULL,
  ORDERNUMBER bigint(20) NOT NULL,
  PRIMARY KEY (ID),
  CONSTRAINT FK_MAILBOX_USER FOREIGN KEY (MAILBOX_ID)
  REFERENCES MAILBOX (ID) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT FK_MAILBOX_USER_MOBILE_USER_ID FOREIGN KEY (MOBILE_USER_ID)
  REFERENCES MOBILE_USER (ID) ON DELETE CASCADE ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 7
AVG_ROW_LENGTH = 4096
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE SMS (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  EVENT_ID bigint(20) UNSIGNED NOT NULL,
  MAILBOX_USER_ID bigint(20) UNSIGNED NOT NULL,
  TS timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  TEXT varchar(1024) NOT NULL,
  SMS_UID varchar(255) DEFAULT NULL,
  MOBILE varchar(16) NOT NULL,
  PRIMARY KEY (ID),
  CONSTRAINT FK_SMS_EVENT_ID FOREIGN KEY (EVENT_ID)
  REFERENCES EVENT (ID) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT FK_SMS_MAILBOX_USER_ID FOREIGN KEY (MAILBOX_USER_ID)
  REFERENCES MAILBOX_USER (ID) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 25
AVG_ROW_LENGTH = 4096
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE SMS_ST (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  SMS_ID bigint(20) UNSIGNED NOT NULL,
  STATUS_ID bigint(20) UNSIGNED DEFAULT NULL,
  ERROR_ID bigint(20) UNSIGNED DEFAULT NULL,
  TS timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  CONSTRAINT FK_SMS_ST_SMS_ERROR FOREIGN KEY (ERROR_ID)
  REFERENCES SMS_ERROR (ID) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT FK_SMS_ST_SMS_ID FOREIGN KEY (SMS_ID)
  REFERENCES SMS (ID) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT FK_SMS_ST_SMS_STATUS FOREIGN KEY (STATUS_ID)
  REFERENCES SMS_STATUS (ID) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 2
AVG_ROW_LENGTH = 16384
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE QUEUE (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  SMS_ID bigint(20) UNSIGNED NOT NULL,
  CLONED tinyint(1) NOT NULL DEFAULT 0,
  TS timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  CONSTRAINT FK_QUEUE_SMS_ID FOREIGN KEY (SMS_ID)
  REFERENCES SMS (ID) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;
