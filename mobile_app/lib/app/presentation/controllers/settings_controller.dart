import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../core/constants/app_defaults.dart';
import '../../data/repositories/settings_repository.dart';

/// Owns the reactive copies of every Settings value. The root app rebuilds
/// its [ThemeData] whenever [primaryColor]/[secondaryColor]/[accentColor]
/// change, so edits on the Settings screen apply instantly, app-wide.
class SettingsController extends GetxController {
  final SettingsRepository repository;

  SettingsController({required this.repository});

  final apiBaseUrl = ''.obs;
  final primaryColor = AppDefaults.primaryColor.obs;
  final secondaryColor = AppDefaults.secondaryColor.obs;
  final accentColor = AppDefaults.accentColor.obs;
  final appTitle = ''.obs;
  final generalFeedbackTitle = ''.obs;
  final counterFeedbackTitle = ''.obs;
  final languageCode = ''.obs;

  @override
  void onInit() {
    super.onInit();
    _loadFromStorage();
  }

  void _loadFromStorage() {
    apiBaseUrl.value = repository.apiBaseUrl;
    primaryColor.value = repository.primaryColor;
    secondaryColor.value = repository.secondaryColor;
    accentColor.value = repository.accentColor;
    appTitle.value = repository.appTitle;
    generalFeedbackTitle.value = repository.generalFeedbackTitle;
    counterFeedbackTitle.value = repository.counterFeedbackTitle;
    languageCode.value = repository.languageCode;
  }

  Future<void> updateApiBaseUrl(String value) async {
    await repository.setApiBaseUrl(value);
    apiBaseUrl.value = repository.apiBaseUrl;
  }

  Future<void> updatePrimaryColor(Color value) async {
    await repository.setPrimaryColor(value);
    primaryColor.value = value;
  }

  Future<void> updateSecondaryColor(Color value) async {
    await repository.setSecondaryColor(value);
    secondaryColor.value = value;
  }

  Future<void> updateAccentColor(Color value) async {
    await repository.setAccentColor(value);
    accentColor.value = value;
  }

  Future<void> updateAppTitle(String value) async {
    await repository.setAppTitle(value);
    appTitle.value = repository.appTitle;
  }

  Future<void> updateGeneralFeedbackTitle(String value) async {
    await repository.setGeneralFeedbackTitle(value);
    generalFeedbackTitle.value = repository.generalFeedbackTitle;
  }

  Future<void> updateCounterFeedbackTitle(String value) async {
    await repository.setCounterFeedbackTitle(value);
    counterFeedbackTitle.value = repository.counterFeedbackTitle;
  }

  Future<void> updateLanguageCode(String value) async {
    await repository.setLanguageCode(value);
    languageCode.value = value;
  }

  Future<void> resetToDefaults() async {
    await repository.resetToDefaults();
    _loadFromStorage();
  }
}
