import 'package:flutter/material.dart';

/// Default values used the first time the app runs, and whenever the user
/// taps "Reset to defaults" on the Settings screen. Colors are pulled
/// directly from the web app's own stylesheets (beaa/css/common.css,
/// beaa/css/feedback.css, the jquery-bar-rating "fontawesome-stars" theme)
/// so the app matches the web kiosk out of the box.
class AppDefaults {
  AppDefaults._();

  /// Change this to your server's real address if it differs from the demo
  /// machine. Overridable at runtime from the Settings screen -- nothing
  /// else in the app hardcodes a host.
  static const String apiBaseUrl = 'http://192.168.1.41:8000/beaa/api/v1';

  // beaa/css/common.css: .bg-blue-deep (navbar), a friendlier mid blue for
  // buttons/links, and the gold used for the star-rating "active" color.
  static const Color primaryColor = Color(0xFF2C3E50); // bg-blue-deep
  static const Color secondaryColor = Color(0xFF3498DB); // btn-primary blue
  static const Color accentColor = Color(0xFFF1C40F); // bg-yellow-heavy / star gold
  static const Color starColor = Color(0xFFEDB867); // br-theme-fontawesome-stars active
  static const Color successColor = Color(0xFF27AE60);
  static const Color dangerColor = Color(0xFFE74C3C);
  static const Color backgroundColor = Color(0xFFF5F7FA);

  static const String appTitle = 'iDEAL-Q Feedback';
  static const String generalFeedbackTitle = 'Rate your experience';
  static const String counterFeedbackTitle = 'Counter Feedback';
  static const String settingsTitle = 'Settings';

  static const String languageCode = 'en';
}
