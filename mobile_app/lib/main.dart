import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

import 'app/bindings/initial_binding.dart';
import 'app/core/localization/app_translations.dart';
import 'app/core/theme/app_theme.dart';
import 'app/presentation/controllers/settings_controller.dart';
import 'app/presentation/screens/splash/splash_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.light,
    ),
  );
  await GetStorage.init();
  InitialBinding().dependencies();
  runApp(const IdealFeedbackApp());
}

/// Root of the Ideal Feedback app. Wrapped in [ScreenUtilInit] for responsive
/// sizing (phone <-> 10.95" tablet) and in an [Obx] so switching language or
/// re-tinting the accent colour on the Settings screen updates the whole app —
/// fonts, text direction (RTL/LTR) and theme — live, with no restart.
class IdealFeedbackApp extends StatelessWidget {
  const IdealFeedbackApp({super.key});

  @override
  Widget build(BuildContext context) {
    final settings = Get.find<SettingsController>();
    return ScreenUtilInit(
      designSize: const Size(430, 932),
      minTextAdapt: true,
      splitScreenMode: true,
      builder: (context, child) => Obx(() {
        final code =
            settings.languageCode.value.isEmpty ? 'ar' : settings.languageCode.value;
        final locale = Locale(code);
        // Touch these so the app rebuilds when the accent colour or theme
        // mode changes.
        settings.primaryColor.value;
        settings.accentColor.value;
        final isDark = settings.isDarkMode;

        return GetMaterialApp(
          title: 'Ideal Feedback',
          debugShowCheckedModeBanner: false,
          translations: AppTranslations(),
          locale: locale,
          fallbackLocale: AppTranslations.english,
          supportedLocales: AppTranslations.supported,
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          theme: AppTheme.build(settings.repository, locale, Brightness.light),
          darkTheme:
              AppTheme.build(settings.repository, locale, Brightness.dark),
          themeMode: isDark ? ThemeMode.dark : ThemeMode.light,
          home: const SplashScreen(),
        );
      }),
    );
  }
}
