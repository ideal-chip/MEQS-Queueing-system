import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

import 'app/bindings/initial_binding.dart';
import 'app/core/theme/app_theme.dart';
import 'app/presentation/controllers/settings_controller.dart';
import 'app/presentation/screens/root/root_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await GetStorage.init();
  InitialBinding().dependencies();
  runApp(const MeqsFeedbackApp());
}

/// Root widget, wrapped in [Obx] so the whole app's [ThemeData] and title
/// update live whenever the user changes a color or title on the Settings
/// screen -- no restart needed.
class MeqsFeedbackApp extends StatelessWidget {
  const MeqsFeedbackApp({super.key});

  @override
  Widget build(BuildContext context) {
    final settings = Get.find<SettingsController>();
    return Obx(() {
      // Touching these makes this Obx rebuild whenever any of them change.
      settings.primaryColor.value;
      settings.secondaryColor.value;
      settings.accentColor.value;
      final title = settings.appTitle.value;

      return GetMaterialApp(
        title: title.isEmpty ? 'iDEAL-Q Feedback' : title,
        debugShowCheckedModeBanner: false,
        theme: AppTheme.build(settings.repository),
        home: const RootScreen(),
      );
    });
  }
}
