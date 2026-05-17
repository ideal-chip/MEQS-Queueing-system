USE project_demo_db;

INSERT INTO settings (set_key, set_value) VALUES
('defaultLanguage', 'ar'),
('displayDigits', '3'),
('bulkStatus', '1'),
('audioShortBeep', '0'),
('feedbackUpdated', '0')
ON DUPLICATE KEY UPDATE set_value = VALUES(set_value);

INSERT INTO zones (zone_id, zone_name, zone_desc) VALUES
(1, 'Main Hall', 'Demo main service hall')
ON DUPLICATE KEY UPDATE zone_name = VALUES(zone_name), zone_desc = VALUES(zone_desc);

INSERT INTO bigdisplaytypes (bdtype_id, bdtype_name) VALUES
(1, 'Latest Call'),
(2, 'Bulk Waiting'),
(3, 'Counter Calls'),
(4, 'Latest With Waiting')
ON DUPLICATE KEY UPDATE bdtype_name = VALUES(bdtype_name);

INSERT INTO users (user_name, user_password, user_privileges, user_fullname, user_desc, user_phone) VALUES
('admin.demo@example.com', SHA2('AdminDemo@123', 256), 255, 'Demo Admin', 'Full demo administrator', '0500000001'),
('viewer.demo@example.com', SHA2('ViewerDemo@123', 256), 65, 'Demo Viewer', 'Read/report demo user', '0500000003')
ON DUPLICATE KEY UPDATE user_password = VALUES(user_password), user_privileges = VALUES(user_privileges), user_fullname = VALUES(user_fullname);

INSERT INTO clerks (clerk_name, clerk_password, clerk_fullname, clerk_desc, clerk_phone, clerk_zone) VALUES
('operator.demo@example.com', SHA2('OperatorDemo@123', 256), 'Demo Operator', 'Counter operator for demo testing', '0500000002', 1)
ON DUPLICATE KEY UPDATE clerk_password = VALUES(clerk_password), clerk_fullname = VALUES(clerk_fullname), clerk_zone = VALUES(clerk_zone);

INSERT INTO texts (text_language, text_key, text_value) VALUES
('ar', 'dir', 'rtl'),
('en', 'dir', 'ltr'),
('ar', 'CAT001', 'خدمة العملاء'),
('en', 'CAT001', 'Customer Service'),
('ar', 'CAT002', 'المعاملات المالية'),
('en', 'CAT002', 'Payments'),
('ar', 'adminLogin', 'تسجيل دخول الإدارة'),
('en', 'adminLogin', 'Admin Login'),
('ar', 'userPrivileges1', 'التقارير والعمليات'),
('ar', 'userPrivileges2', 'إدارة الموظفين'),
('ar', 'userPrivileges4', 'صلاحية احتياطية'),
('ar', 'userPrivileges8', 'الدخول العام'),
('ar', 'userPrivileges16', 'إعدادات النظام'),
('ar', 'userPrivileges32', 'إدارة المستخدمين'),
('ar', 'userPrivileges64', 'البحث'),
('ar', 'userPrivileges128', 'اللغات')
ON DUPLICATE KEY UPDATE text_value = VALUES(text_value);

INSERT INTO audios (audio_id, audio_name, audio_path, audio_language, audio_gender) VALUES
(1, 'Default Arabic Male', 'files/audios/default.mp3', 'ar', 2)
ON DUPLICATE KEY UPDATE audio_name = VALUES(audio_name), audio_path = VALUES(audio_path);

INSERT INTO displays (display_id, display_name, display_zone, display_updated) VALUES
(1, 'Main Counter Display', 1, 0)
ON DUPLICATE KEY UPDATE display_name = VALUES(display_name), display_zone = VALUES(display_zone);

INSERT INTO bigdisplays (display_id, display_number, display_name, display_zone, display_updated, display_type, goto, arrow_dir) VALUES
(1, 1, 'Main Hall Big Display', 1, 0, 4, 'Counter', 0)
ON DUPLICATE KEY UPDATE display_name = VALUES(display_name), display_zone = VALUES(display_zone), display_type = VALUES(display_type);

INSERT INTO categories (category_id, category_key, serial_no_ref, category_char, category_parent, category_zone, category_enabled) VALUES
(1, 'CAT001', 'A', 'A', 0, 1, 1),
(2, 'CAT002', 'B', 'B', 0, 1, 1)
ON DUPLICATE KEY UPDATE category_key = VALUES(category_key), category_enabled = VALUES(category_enabled);

INSERT INTO subcategories (subcategory_id, subcategory_name, wait_time_days, papers, main_category_id, in_report) VALUES
(1, 'تحديث بيانات العميل', 2, 'هوية وطنية; نموذج طلب', 1, 1),
(2, 'دفع فاتورة', 0, 'رقم الحساب', 2, 1)
ON DUPLICATE KEY UPDATE subcategory_name = VALUES(subcategory_name), main_category_id = VALUES(main_category_id);

INSERT INTO counters (counter_id, counter_name, counter_no, counter_display, counter_audio, counter_zone, counter_active, current_clerk, direct_transfer_category, can_pick_tickets) VALUES
(1, 'Counter 1', 1, 1, 1, 1, 0, 0, 0, 1),
(2, 'Counter 2', 2, 1, 1, 1, 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE counter_name = VALUES(counter_name), counter_zone = VALUES(counter_zone);

INSERT INTO countercategories (cc_counter, cc_category, cc_enabled) VALUES
(1, 1, 1),
(1, 2, 1),
(2, 1, 1)
ON DUPLICATE KEY UPDATE cc_enabled = VALUES(cc_enabled);

INSERT INTO kiosks (kiosk_id, kiosk_name, kiosk_printer_type, kiosk_printer_location, kiosk_printer_parameters, kiosk_zone, kiosk_updated) VALUES
(1, 'Main Kiosk', 'TCP', '127.0.0.1', '9100', 1, 0)
ON DUPLICATE KEY UPDATE kiosk_name = VALUES(kiosk_name), kiosk_zone = VALUES(kiosk_zone);

INSERT INTO kioskbuttons (kb_kiosk, kb_category, kb_priority) VALUES
(1, 1, 0),
(1, 2, 1)
ON DUPLICATE KEY UPDATE kb_priority = VALUES(kb_priority);

INSERT INTO bigdisplayscounters (bdc_bigdisplay, bdc_counter) VALUES
(1, 1),
(1, 2)
ON DUPLICATE KEY UPDATE bdc_bigdisplay = VALUES(bdc_bigdisplay);

INSERT INTO bigdisplayforcounter (bd_id, counter_id, quantity) VALUES
(1, 1, 5)
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity);

INSERT INTO bigdisplayservices (bd_id, category_id, qty, priority) VALUES
(1, 1, 5, 0),
(1, 2, 5, 1)
ON DUPLICATE KEY UPDATE qty = VALUES(qty), priority = VALUES(priority);

INSERT INTO events (event_id, event_time, event_category, event_no, event_priority, event_level, event_language, event_zone, event_kiosk) VALUES
(1, NOW() - INTERVAL 20 MINUTE, 1, 1, 0, 0, 'ar', 1, 1),
(2, NOW() - INTERVAL 10 MINUTE, 2, 1, 1, 0, 'ar', 1, 1),
(3, NOW() - INTERVAL 5 MINUTE, 1, 2, 0, 1, 'ar', 1, 1)
ON DUPLICATE KEY UPDATE event_time = VALUES(event_time), event_level = VALUES(event_level);

INSERT INTO events_logs (log_type, log_event, log_clerk, log_counter, log_zone, log_time, log_ip_address) VALUES
(2, 3, 1, 1, 1, NOW() - INTERVAL 4 MINUTE, '127.0.0.1');

INSERT INTO displays_logs (log_event, log_counter, log_display, log_zone, log_time) VALUES
(3, 1, 1, 1, NOW() - INTERVAL 4 MINUTE);

INSERT INTO feedback (fb0, fb1, fb2, fb3, fb4, feedback_note, feedback_date) VALUES
(5, 4, 5, 4, 5, 'Demo feedback entry', NOW() - INTERVAL 1 DAY);

INSERT INTO followups (serial_no, client_name, mobile_number, category_id, subcategory_id, clerk_id, notes, date_created, is_done) VALUES
('DEMO-FU-001', 'عميل تجريبي', '0501111111', 1, 1, 1, 'متابعة تجريبية لاختبار التقارير', NOW() - INTERVAL 2 DAY, 0);
