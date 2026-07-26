import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

/// Default values used on first run and on "Reset to defaults". The on-screen
/// titles default to empty, which makes each screen fall back to its localized
/// string (`*_title`.tr) — so the app is fully Arabic/English out of the box.
/// A user can still type a fixed custom title in Settings to override.
class AppDefaults {
  AppDefaults._();

  /// Server address. Overridable at runtime from the Settings screen.
  static const String apiBaseUrl = 'http://192.168.1.41:8000/beaa/api/v1';

  // Brand palette (iDEAL-Q logo): blue primary, gold accent, on deep navy.
  static const Color primaryColor = AppColors.blue;
  static const Color secondaryColor = AppColors.blueLight;
  static const Color accentColor = AppColors.gold;
  static const Color starColor = AppColors.gold;
  static const Color successColor = AppColors.success;
  static const Color dangerColor = AppColors.error;
  static const Color backgroundColor = AppColors.bgDeep;

  // Empty => screens use the localized default title.
  static const String appTitle = '';
  static const String generalFeedbackTitle = '';
  static const String counterFeedbackTitle = '';
  static const String settingsTitle = '';

  /// Arabic is the default language (Ministry of Environment requirement).
  static const String languageCode = 'ar';

  /// Dark is the default theme ('dark' | 'light').
  static const String themeMode = 'dark';
}
