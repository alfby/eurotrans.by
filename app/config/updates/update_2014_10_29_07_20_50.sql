
START TRANSACTION;

INSERT INTO `fields` VALUES (NULL, 'lblDefineSeats', 'backend', 'Label / Define seats', 'script', '2014-10-29 07:20:40');

SET @id := (SELECT LAST_INSERT_ID());

INSERT INTO `multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::',  'title', 'Once you upload the image you will be able to define seats.', 'script');

COMMIT;