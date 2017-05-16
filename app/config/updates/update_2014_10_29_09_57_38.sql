
START TRANSACTION;

SET @id := (SELECT `id` FROM `fields` WHERE `key` = "pj_email_taken");

UPDATE `multi_lang` SET `content` = 'User with this email address exists.' WHERE `foreign_id` = @id AND `model` = "pjField" AND `field` = "title";

COMMIT;