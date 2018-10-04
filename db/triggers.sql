
USE application;


DELIMITER $$

CREATE TRIGGER trig_assignstep_step
	BEFORE INSERT ON assignstep
	FOR EACH ROW BEGIN
		SET NEW.step = (
			SELECT IFNULL(MAX(step), 0) + 1
			FROM assignstep
			WHERE assignment = NEW.assignment
			);
end $$

CREATE TRIGGER trig_turnin_subcount
	BEFORE INSERT ON turnin
	FOR EACH ROW BEGIN
		SET NEW.subcount= (
			SELECT IFNULL(MAX(subcount), 0) + 1
			FROM turnin
			WHERE studentid = NEW.studentid
			AND assignment = NEW.assignment
			AND step = NEW.step
			);
end $$

CREATE TRIGGER trig_filename_filenumber
	BEFORE INSERT ON filename
	FOR EACH ROW BEGIN
		SET NEW.filenumber = (
			SELECT IFNULL(MAX(filenumber), 0) + 1
			FROM filename
			WHERE studentid = NEW.studentid
			AND assignment = NEW.assignment
			AND step = NEW.step
			AND subcount = NEW.subcount
			);
end $$

DELIMITER ;
