CREATE TABLE users(
	id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
	email varchar(255) NOT NULL UNIQUE,
    pass varchar(255) NOT NULL
);

CREATE TABLE forms(
	id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
	user_id int unsigned NOT NULL,
	title varchar(255) NOT NULL,
	description varchar(255) NOT NULL,
	created_at Timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_active boolean NOT NULL,
	available_for_non_users boolean NOT NULL,
	pin varchar(12),  
	link varchar(36) UNIQUE NOT NULL,
	submission_count int unsigned NOT NULL DEFAULT 0,
	last_updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	foreign key (user_id) references users (id)
);


CREATE TABLE questions(
	id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
	form_id int unsigned NOT NULL,
	question_text varchar(255) NOT NULL,
	answer_type enum('text', 'big_text', 'multiple_choice', 'checkbox', 'linear-scale') NOT NULL,
	is_required boolean NOT NULL,
	created_at Timestamp NOT NULL,
    foreign key (form_id) references forms (id)
);

CREATE TABLE choices(
    id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
    question_id int unsigned NOT NULL,
	option_text varchar(255) NOT NULL,
	created_at Timestamp NOT NULL,
	foreign key (question_id) references questions (id)
);
CREATE TABLE templates (
    id int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id int UNSIGNED NOT NULL,
    title varchar(255) NOT NULL,
    description varchar(255) NOT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    link varchar(36) UNIQUE NOT NULL,
    foreign key (user_id) references users(id)
);

CREATE TABLE templates_questions(
	id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
    template_id int unsigned NOT NULL,
	question_text varchar(255) NOT NULL,
	answer_type enum('text', 'big_text', 'multiple_choice', 'checkbox', 'linear-scale') NOT NULL,
	is_required boolean NOT NULL,
    created_at Timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    foreign key (template_id) references templates (id)
);

CREATE TABLE templete_choices(
    id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
    template_question_id int unsigned NOT NULL,
	option_text varchar(255) NOT NULL,
	created_at Timestamp NOT NULL,
	foreign key (template_question_id) references templates_questions (id)
);	

CREATE TABLE responses(
    id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
    form_id int unsigned NOT NULL,
    respondednt_id int unsigned,
	created_at Timestamp NOT NULL,
    foreign key (form_id) references forms (id),
    foreign key (respondednt_id) references users (id)
);

CREATE TABLE answers(
	id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
    response_id int unsigned NOT NULL,
    question_id int unsigned NOT NULL,
    answer_text varchar(255) NULL,
	created_at Timestamp NOT NULL,
	foreign key (response_id) references responses (id),
    foreign key (question_id) references questions (id)
);
