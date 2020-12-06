CREATE TABLE `user` (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email NVARCHAR(255) NOT NULL,
  name NVARCHAR(255) NOT NULL,
  password CHAR(60) NOT NULL,
  is_staff BIT NOT NULL DEFAULT 0
);

CREATE TABLE quiz (
  id INT PRIMARY KEY AUTO_INCREMENT,
  author_id INT NOT NULL,
  title NVARCHAR(255) NOT NULL,
  available BIT NOT NULL,
  duration SMALLINT NOT NULL
);

ALTER TABLE quiz
  ADD FOREIGN KEY (author_id) REFERENCES `user`(id)
  ON DELETE CASCADE -- Delete all the user's quizzes
  ON UPDATE CASCADE; -- Update the user_id whenever it changes

CREATE TABLE submission (
  quiz_id INT NOT NULL,
  user_id INT NOT NULL,
  date_of_attempt DATE NOT NULL,
  PRIMARY KEY(quiz_id, user_id)
);

ALTER TABLE submission
  ADD FOREIGN KEY (quiz_id) REFERENCES quiz(id)
  ON DELETE CASCADE -- Delete all the attempts for the quiz
  ON UPDATE CASCADE; -- Update the quiz_id whenever it changes

ALTER TABLE submission
  ADD FOREIGN KEY (user_id) REFERENCES `user`(id)
  ON DELETE CASCADE -- Delete all the attempts for the quiz
  ON UPDATE CASCADE; -- Update the user_id whenever it changes

CREATE TABLE question (
  quiz_id INT NOT NULL,
  no INT NOT NULL,
  text NVARCHAR(1000) NOT NULL,
  answer TINYINT NOT NULL,
  opt_a NVARCHAR(255) NOT NULL,
  opt_b NVARCHAR(255) NOT NULL,
  opt_c NVARCHAR(255) NOT NULL,
  opt_d NVARCHAR(255) NOT NULL,
  PRIMARY KEY(quiz_id, no)
);

ALTER TABLE question
  ADD FOREIGN KEY (quiz_id) REFERENCES quiz(id)
  ON DELETE CASCADE -- Delete all the questions for the quiz
  ON UPDATE CASCADE; -- Update the quiz_id whenever it changes

CREATE TABLE submitted_answer (
  quiz_id INT NOT NULL,
  question_no INT NOT NULL,
  user_id INT NOT NULL,
  answer TINYINT NOT NULL,
  PRIMARY KEY(quiz_id, question_no, user_id)
);

ALTER TABLE submitted_answer
  ADD FOREIGN KEY (quiz_id, question_no) REFERENCES question(quiz_id, no)
  ON DELETE CASCADE -- Delete all the submissions for the quiz
  ON UPDATE CASCADE; -- Update the FK whenever it changes

ALTER TABLE submitted_answer
  ADD FOREIGN KEY (quiz_id, user_id) REFERENCES submission(quiz_id, user_id)
  ON DELETE CASCADE -- Delete all the submissions for the quiz
  ON UPDATE CASCADE; -- Update the FK whenever it changes

ALTER TABLE submitted_answer
  ADD FOREIGN KEY (user_id) REFERENCES `user`(id)
  ON DELETE CASCADE -- Delete all the submissions for the quiz
  ON UPDATE CASCADE; -- Update the user_id whenever it changes
