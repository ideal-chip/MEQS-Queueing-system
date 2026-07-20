CREATE DATABASE IF NOT EXISTS project_demo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE project_demo_db;

CREATE TABLE IF NOT EXISTS zones (
  zone_id INT AUTO_INCREMENT PRIMARY KEY,
  zone_name VARCHAR(80) NOT NULL UNIQUE,
  zone_desc VARCHAR(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  set_key VARCHAR(80) PRIMARY KEY,
  set_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS texts (
  text_id INT AUTO_INCREMENT PRIMARY KEY,
  text_language VARCHAR(10) NOT NULL,
  text_key VARCHAR(120) NOT NULL,
  text_value TEXT NOT NULL,
  UNIQUE KEY uq_text_lang_key (text_language, text_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(80) NOT NULL UNIQUE,
  user_password VARCHAR(128) NOT NULL,
  user_privileges INT NOT NULL DEFAULT 0,
  user_fullname VARCHAR(120) DEFAULT '',
  user_desc VARCHAR(255) DEFAULT '',
  user_phone VARCHAR(30) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clerks (
  clerk_id INT AUTO_INCREMENT PRIMARY KEY,
  clerk_name VARCHAR(80) NOT NULL UNIQUE,
  clerk_password VARCHAR(128) NOT NULL,
  clerk_fullname VARCHAR(120) DEFAULT '',
  clerk_desc VARCHAR(255) DEFAULT '',
  clerk_phone VARCHAR(30) DEFAULT '',
  clerk_zone INT NOT NULL,
  KEY idx_clerk_zone (clerk_zone),
  CONSTRAINT fk_clerks_zone FOREIGN KEY (clerk_zone) REFERENCES zones(zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audios (
  audio_id INT AUTO_INCREMENT PRIMARY KEY,
  audio_name VARCHAR(80) NOT NULL,
  audio_path VARCHAR(255) NOT NULL,
  audio_language VARCHAR(10) DEFAULT 'ar',
  audio_gender TINYINT NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS displays (
  display_id INT AUTO_INCREMENT PRIMARY KEY,
  display_name VARCHAR(80) NOT NULL UNIQUE,
  display_zone INT NOT NULL,
  display_updated TINYINT NOT NULL DEFAULT 0,
  KEY idx_display_zone (display_zone),
  CONSTRAINT fk_displays_zone FOREIGN KEY (display_zone) REFERENCES zones(zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bigdisplaytypes (
  bdtype_id INT PRIMARY KEY,
  bdtype_name VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bigdisplays (
  display_id INT AUTO_INCREMENT PRIMARY KEY,
  display_number INT NOT NULL UNIQUE,
  display_name VARCHAR(80) NOT NULL UNIQUE,
  display_zone INT NOT NULL,
  display_updated TINYINT NOT NULL DEFAULT 0,
  display_type INT NOT NULL DEFAULT 1,
  goto VARCHAR(80) DEFAULT '',
  arrow_dir INT NOT NULL DEFAULT 0,
  KEY idx_bigdisplay_zone (display_zone),
  CONSTRAINT fk_bigdisplays_zone FOREIGN KEY (display_zone) REFERENCES zones(zone_id),
  CONSTRAINT fk_bigdisplays_type FOREIGN KEY (display_type) REFERENCES bigdisplaytypes(bdtype_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_key VARCHAR(20) NOT NULL UNIQUE,
  serial_no_ref VARCHAR(10) NOT NULL UNIQUE,
  category_char VARCHAR(10) NOT NULL UNIQUE,
  category_parent INT DEFAULT NULL,
  category_zone INT NOT NULL,
  category_enabled TINYINT NOT NULL DEFAULT 1,
  category_data TEXT DEFAULT NULL,
  KEY idx_category_zone (category_zone),
  CONSTRAINT fk_categories_zone FOREIGN KEY (category_zone) REFERENCES zones(zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subcategories (
  subcategory_id INT AUTO_INCREMENT PRIMARY KEY,
  subcategory_name VARCHAR(120) NOT NULL,
  wait_time_days INT NOT NULL DEFAULT 0,
  papers TEXT,
  main_category_id INT NOT NULL,
  in_report TINYINT NOT NULL DEFAULT 1,
  KEY idx_subcategory_category (main_category_id),
  CONSTRAINT fk_subcategories_category FOREIGN KEY (main_category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS counters (
  counter_id INT AUTO_INCREMENT PRIMARY KEY,
  counter_name VARCHAR(80) NOT NULL UNIQUE,
  counter_no INT NOT NULL UNIQUE,
  counter_display INT NOT NULL,
  counter_audio INT NOT NULL,
  counter_zone INT NOT NULL,
  counter_active TINYINT NOT NULL DEFAULT 0,
  current_clerk INT NOT NULL DEFAULT 0,
  direct_transfer_category INT NOT NULL DEFAULT 0,
  can_pick_tickets TINYINT NOT NULL DEFAULT 1,
  ip_address VARCHAR(45) DEFAULT '',
  last_seen DATETIME DEFAULT NULL,
  KEY idx_counter_zone (counter_zone),
  KEY idx_counter_display (counter_display),
  KEY idx_counter_audio (counter_audio),
  CONSTRAINT fk_counters_zone FOREIGN KEY (counter_zone) REFERENCES zones(zone_id),
  CONSTRAINT fk_counters_display FOREIGN KEY (counter_display) REFERENCES displays(display_id),
  CONSTRAINT fk_counters_audio FOREIGN KEY (counter_audio) REFERENCES audios(audio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS countercategories (
  cc_id INT AUTO_INCREMENT PRIMARY KEY,
  cc_counter INT NOT NULL,
  cc_category INT NOT NULL,
  cc_enabled TINYINT NOT NULL DEFAULT 1,
  UNIQUE KEY uq_counter_category (cc_counter, cc_category),
  CONSTRAINT fk_cc_counter FOREIGN KEY (cc_counter) REFERENCES counters(counter_id),
  CONSTRAINT fk_cc_category FOREIGN KEY (cc_category) REFERENCES categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kiosks (
  kiosk_id INT AUTO_INCREMENT PRIMARY KEY,
  kiosk_name VARCHAR(80) NOT NULL UNIQUE,
  kiosk_printer_type VARCHAR(20) DEFAULT 'TCP',
  kiosk_printer_location VARCHAR(120) DEFAULT '127.0.0.1',
  kiosk_printer_parameters VARCHAR(120) DEFAULT '9100',
  kiosk_zone INT NOT NULL,
  kiosk_updated TINYINT NOT NULL DEFAULT 0,
  KEY idx_kiosk_zone (kiosk_zone),
  CONSTRAINT fk_kiosks_zone FOREIGN KEY (kiosk_zone) REFERENCES zones(zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kioskbuttons (
  kb_id INT AUTO_INCREMENT PRIMARY KEY,
  kb_kiosk INT NOT NULL,
  kb_category INT NOT NULL,
  kb_priority INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_kiosk_category (kb_kiosk, kb_category),
  CONSTRAINT fk_kb_kiosk FOREIGN KEY (kb_kiosk) REFERENCES kiosks(kiosk_id),
  CONSTRAINT fk_kb_category FOREIGN KEY (kb_category) REFERENCES categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bigdisplayscounters (
  bdc_id INT AUTO_INCREMENT PRIMARY KEY,
  bdc_bigdisplay INT NOT NULL,
  bdc_counter INT NOT NULL,
  UNIQUE KEY uq_bigdisplay_counter (bdc_bigdisplay, bdc_counter),
  CONSTRAINT fk_bdc_bigdisplay FOREIGN KEY (bdc_bigdisplay) REFERENCES bigdisplays(display_id),
  CONSTRAINT fk_bdc_counter FOREIGN KEY (bdc_counter) REFERENCES counters(counter_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bigdisplayforcounter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bd_id INT NOT NULL,
  counter_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 5,
  UNIQUE KEY uq_bdfc_bd_counter (bd_id, counter_id),
  CONSTRAINT fk_bdfc_bigdisplay FOREIGN KEY (bd_id) REFERENCES bigdisplays(display_id),
  CONSTRAINT fk_bdfc_counter FOREIGN KEY (counter_id) REFERENCES counters(counter_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bigdisplayservices (
  bds_id INT AUTO_INCREMENT PRIMARY KEY,
  bd_id INT NOT NULL,
  category_id INT NOT NULL,
  qty INT NOT NULL DEFAULT 5,
  priority INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_bds_bd_category (bd_id, category_id),
  CONSTRAINT fk_bds_bigdisplay FOREIGN KEY (bd_id) REFERENCES bigdisplays(display_id),
  CONSTRAINT fk_bds_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS events (
  event_id INT AUTO_INCREMENT PRIMARY KEY,
  event_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  event_category INT NOT NULL,
  event_no INT NOT NULL,
  event_priority INT NOT NULL DEFAULT 0,
  event_level INT NOT NULL DEFAULT 0,
  event_language VARCHAR(10) DEFAULT 'ar',
  event_zone INT NOT NULL,
  event_kiosk INT DEFAULT NULL,
  priority_updated TINYINT NOT NULL DEFAULT 0,
  KEY idx_events_day_category (event_time, event_category),
  KEY idx_events_level (event_level),
  CONSTRAINT fk_events_category FOREIGN KEY (event_category) REFERENCES categories(category_id),
  CONSTRAINT fk_events_zone FOREIGN KEY (event_zone) REFERENCES zones(zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS events_logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  log_type INT NOT NULL,
  log_event INT NOT NULL,
  log_clerk INT DEFAULT NULL,
  log_counter INT DEFAULT NULL,
  log_zone INT NOT NULL,
  log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  log_ip_address VARCHAR(45) DEFAULT '',
  KEY idx_event_logs_event (log_event),
  KEY idx_event_logs_day (log_time),
  CONSTRAINT fk_event_logs_event FOREIGN KEY (log_event) REFERENCES events(event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS counters_logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  log_type INT NOT NULL,
  log_clerk INT NOT NULL,
  log_counter INT NOT NULL,
  log_zone INT NOT NULL,
  log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS displays_logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  log_event INT NOT NULL DEFAULT 0,
  log_counter INT NOT NULL,
  log_display INT NOT NULL,
  log_zone INT NOT NULL,
  log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_display_logs_display (log_display, log_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audios_logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  log_event INT NOT NULL,
  log_counter INT NOT NULL,
  log_audio INT NOT NULL,
  log_seen TINYINT NOT NULL DEFAULT 0,
  log_zone INT NOT NULL,
  bd_id INT DEFAULT NULL,
  log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audios_logs_bulk (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  log_event INT NOT NULL,
  log_category INT NOT NULL,
  log_audio INT NOT NULL,
  log_seen TINYINT NOT NULL DEFAULT 0,
  log_zone INT NOT NULL,
  bd_id INT DEFAULT NULL,
  log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transfers (
  transfer_id INT AUTO_INCREMENT PRIMARY KEY,
  transfer_event INT NOT NULL,
  transfer_clerk INT NOT NULL,
  transfer_counter INT NOT NULL,
  transfer_cat INT DEFAULT NULL,
  transfer_new_counter INT DEFAULT 0,
  transfer_new_category INT DEFAULT 0,
  transfer_zone INT DEFAULT NULL,
  transfer_done TINYINT NOT NULL DEFAULT 0,
  transfer_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_transfer_event (transfer_event),
  CONSTRAINT fk_transfer_event FOREIGN KEY (transfer_event) REFERENCES events(event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS followups (
  followup_id INT AUTO_INCREMENT PRIMARY KEY,
  serial_no VARCHAR(80) NOT NULL,
  day_order_no INT DEFAULT NULL,
  event_id INT DEFAULT NULL,
  client_name VARCHAR(120) NOT NULL,
  mobile_number VARCHAR(40) DEFAULT '',
  category_id INT NOT NULL,
  subcategory_id INT DEFAULT NULL,
  extension_no VARCHAR(30) DEFAULT NULL,
  clerk_id INT DEFAULT NULL,
  notes TEXT,
  date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_done DATETIME DEFAULT NULL,
  is_done TINYINT NOT NULL DEFAULT 0,
  KEY idx_followups_category (category_id),
  KEY idx_followups_created (date_created)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS feedback (
  feedback_id INT AUTO_INCREMENT PRIMARY KEY,
  fb0 INT DEFAULT NULL,
  fb1 INT DEFAULT NULL,
  fb2 INT DEFAULT NULL,
  fb3 INT DEFAULT NULL,
  fb4 INT DEFAULT NULL,
  fb5 INT DEFAULT NULL,
  feedback_score DECIMAL(4,2) DEFAULT NULL,
  feedback_note TEXT,
  feedback_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS extension_numbers (
  extension_id INT AUTO_INCREMENT PRIMARY KEY,
  extension_no VARCHAR(30) NOT NULL UNIQUE,
  extension_name VARCHAR(120) DEFAULT '',
  extension_desc VARCHAR(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sms_setting (
  sms_id INT AUTO_INCREMENT PRIMARY KEY,
  provider_name VARCHAR(80) NOT NULL,
  is_active TINYINT NOT NULL DEFAULT 0,
  is_defualt TINYINT NOT NULL DEFAULT 0,
  config_json TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
