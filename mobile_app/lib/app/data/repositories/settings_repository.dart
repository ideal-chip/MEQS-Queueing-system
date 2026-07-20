import 'package:flutter/material.dart';
import 'package:get_storage/get_storage.dart';

import '../../core/constants/app_defaults.dart';
import '../../core/constants/storage_keys.dart';

/// Reads/writes the user-configurable Settings (API base URL, colors,
/// screen titles, language) to on-device storage via GetStorage. Every
/// value has a sensible default from [AppDefaults], so a fresh install
/// works immediately without visiting Settings first.
class SettingsRepository {
  final GetStorage _box = GetStorage();

  String get apiBaseUrl =>
      _box.read<String>(StorageKeys.apiBaseUrl) ?? AppDefaults.apiBaseUrl;

  Color get primaryColor =>
      _colorFromHex(_box.read<String>(StorageKeys.primaryColor)) ??
      AppDefaults.primaryColor;

  Color get secondaryColor =>
      _colorFromHex(_box.read<String>(StorageKeys.secondaryColor)) ??
      AppDefaults.secondaryColor;

  Color get accentColor =>
      _colorFromHex(_box.read<String>(StorageKeys.accentColor)) ??
      AppDefaults.accentColor;

  String get appTitle =>
      _box.read<String>(StorageKeys.appTitle) ?? AppDefaults.appTitle;

  String get generalFeedbackTitle =>
      _box.read<String>(StorageKeys.generalFeedbackTitle) ??
      AppDefaults.generalFeedbackTitle;

  String get counterFeedbackTitle =>
      _box.read<String>(StorageKeys.counterFeedbackTitle) ??
      AppDefaults.counterFeedbackTitle;

  String get languageCode =>
      _box.read<String>(StorageKeys.languageCode) ?? AppDefaults.languageCode;

  Future<void> setApiBaseUrl(String value) =>
      _box.write(StorageKeys.apiBaseUrl, value.trim());

  Future<void> setPrimaryColor(Color value) =>
      _box.write(StorageKeys.primaryColor, _hexFromColor(value));

  Future<void> setSecondaryColor(Color value) =>
      _box.write(StorageKeys.secondaryColor, _hexFromColor(value));

  Future<void> setAccentColor(Color value) =>
      _box.write(StorageKeys.accentColor, _hexFromColor(value));

  Future<void> setAppTitle(String value) =>
      _box.write(StorageKeys.appTitle, value);

  Future<void> setGeneralFeedbackTitle(String value) =>
      _box.write(StorageKeys.generalFeedbackTitle, value);

  Future<void> setCounterFeedbackTitle(String value) =>
      _box.write(StorageKeys.counterFeedbackTitle, value);

  Future<void> setLanguageCode(String value) =>
      _box.write(StorageKeys.languageCode, value);

  Future<void> resetToDefaults() async {
    await _box.remove(StorageKeys.apiBaseUrl);
    await _box.remove(StorageKeys.primaryColor);
    await _box.remove(StorageKeys.secondaryColor);
    await _box.remove(StorageKeys.accentColor);
    await _box.remove(StorageKeys.appTitle);
    await _box.remove(StorageKeys.generalFeedbackTitle);
    await _box.remove(StorageKeys.counterFeedbackTitle);
    await _box.remove(StorageKeys.languageCode);
  }

  static Color? _colorFromHex(String? hex) {
    if (hex == null || hex.isEmpty) return null;
    final cleaned = hex.replaceAll('#', '');
    final value = int.tryParse(cleaned, radix: 16);
    if (value == null) return null;
    return Color(0xFF000000 | value);
  }

  static String _hexFromColor(Color color) {
    final argb = color.toARGB32();
    return (argb & 0x00FFFFFF).toRadixString(16).padLeft(6, '0');
  }
}
