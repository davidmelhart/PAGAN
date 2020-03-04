# PAGAN [Platform for Audiovisual General-purpose Annotation]

You can find the app hosted by the Institute of Digital Games at [pagan.institutedigitalgames.com/](http://pagan.institutedigitalgames.com/).

This repository contains the sourcefiles to set up your own server and use both YouTube videos and uploaded videos for gathering annotations.

Please be aware that if you record and store the likeness of participants, you have to abide by GDPR rules and regulations. Otherwise the system anonymises entries.

Passwords are hashed and handled securely, and communication with the server uses PDO prepared statemets.

The quality of the code can be improved both on the app and the server side, treat it as "research code", it functions but would require a whole deal of refactoring, which is unfortunately out of the scope of my current work.

## Database Setup
Use the `config.php` file to set up the credentials to your connection.

Your database needs the following tables set up:
* **`reg_keys`**: `id, secret, created_at`
* **`users`**: `id, username, email, affiliation, password, created_at`
* **`password_resets`**: `id, email, token, created_at`
* **`projects`**: `id, user_name, project_id, project_name, target, type, source_type, video_loading, endless, n_of_entries, n_of_participant_runs, end_message, survey_link, sound, start_message, archived, upload_message, autofill_id, created_at`
* **`project_entries`**: `id, project_id, entry_id, source_type, source_url, original_name, type, created_at`
* **`logs`**: `id, project_id, participant_id, session_id, time_stamp, videotime, annotation_value, original_name, annotation_type, entry_id`

If you are not experienced with mySQL or just want to set up the application qickly, here are the mySQL commands with which you can set up everything in one go:

**reg_keys**
```sql
CREATE TABLE reg_keys (
	id INT(11) NOT NULL AUTO_INCREMENT,
	secret VARCHAR(6),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
   	PRIMARY KEY (id)
);
```

**users**
```sql
CREATE TABLE users (
	id INT(11) NOT NULL AUTO_INCREMENT,
    user_name VARCHAR(50),
    email VARCHAR(50),
    affiliation VARCHAR(50),
    password VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
   	PRIMARY KEY (id)
);
```

**password_resets**
```sql
CREATE TABLE password_resets (
	id INT(11) NOT NULL AUTO_INCREMENT,
	email VARCHAR(50),
	token VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
   	PRIMARY KEY (id)
);
```

**projects**
```sql
CREATE TABLE projects (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_name VARCHAR(50),
    project_id VARCHAR(36),
    project_name VARCHAR(100),
    target VARCHAR(30),
    type VARCHAR(10),
    source_type VARCHAR(13),
    video_loading VARCHAR(8),
    endless VARCHAR(3),
    n_of_entries INT(10),
    n_of_participant_runs INT(10),
    end_message VARCHAR(255),
    survey_link VARCHAR(255),
    sound VARCHAR(3),
	start_message VARCHAR(255),
	archived VARCHAR(5),
	upload_message VARCHAR(500),
	autofill_id VARCHAR(11),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
   	PRIMARY KEY (id)
);
```

**project_entries**
```sql
CREATE TABLE project_entries (
    id INT(11) NOT NULL AUTO_INCREMENT,
    project_id VARCHAR(36),
    entry_id VARCHAR(36),
    source_type VARCHAR(13),
    source_url VARCHAR(255),
    original_name VARCHAR(128),
    type VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
   	PRIMARY KEY (id)
);
```

**logs**
```sql
CREATE TABLE logs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    project_id VARCHAR(36),
    participant_id VARCHAR(36),
    session_id VARCHAR(36),
    time_stamp BIGINT(32),
    videotime INT(32),
    annotation_value INT(32),
    original_name VARCHAR(128),
    annotation_type VARCHAR(10),
    entry_id VARCHAR(36),
   	PRIMARY KEY (id)
);
```
