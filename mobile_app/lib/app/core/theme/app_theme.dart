import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../data/repositories/settings_repository.dart';
import 'app_colors.dart';

/// Builds the app's [ThemeData] for a given [brightness]. Typography is
/// locale-aware — Cairo for Arabic, Poppins for English. The accent colour
/// comes from Settings (defaults to the iDEAL-Q brand blue). Both the dark
/// (default) and light variants share the same brand colours; only the
/// neutrals differ.
class AppTheme {
  AppTheme._();

  static ThemeData build(
    SettingsRepository settings,
    Locale locale,
    Brightness brightness,
  ) {
    final isDark = brightness == Brightness.dark;
    final primary = settings.primaryColor; // brand blue by default
    final accent = settings.accentColor; // brand gold by default

    final base = ThemeData(brightness: brightness, useMaterial3: true);
    final isArabic = locale.languageCode == 'ar';
    final fg = isDark ? AppColors.textPrimary : AppColors.textPrimaryL;
    final textTheme = (isArabic
            ? GoogleFonts.cairoTextTheme(base.textTheme)
            : GoogleFonts.poppinsTextTheme(base.textTheme))
        .apply(bodyColor: fg, displayColor: fg);

    final colorScheme = ColorScheme.fromSeed(
      seedColor: primary,
      brightness: brightness,
      primary: primary,
      secondary: accent,
      surface: isDark ? AppColors.bgMid : AppColors.surfaceL,
    );

    return base.copyWith(
      colorScheme: colorScheme,
      scaffoldBackgroundColor:
          isDark ? AppColors.bgDeep : AppColors.bgDeepL,
      textTheme: textTheme,
      primaryColor: primary,
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
        foregroundColor: fg,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
        titleTextStyle: (isArabic ? GoogleFonts.cairo() : GoogleFonts.poppins())
            .copyWith(color: fg, fontSize: 19, fontWeight: FontWeight.w700),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: isDark
            ? AppColors.bgLight.withValues(alpha: 0.6)
            : AppColors.fieldFillL,
        hintStyle: TextStyle(
            color: isDark ? AppColors.textMuted : AppColors.textMutedL),
        prefixIconColor:
            isDark ? AppColors.textSecondary : AppColors.textSecondaryL,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(
            color: isDark ? AppColors.cardBorder : AppColors.cardBorderL,
            width: 1,
          ),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.blue, width: 1.5),
        ),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor:
            isDark ? AppColors.surfaceSolid : AppColors.textPrimaryL,
        contentTextStyle: const TextStyle(color: Colors.white),
      ),
      dividerColor: isDark ? AppColors.cardBorder : AppColors.cardBorderL,
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.iOS: FadeUpwardsPageTransitionsBuilder(),
          TargetPlatform.android: FadeUpwardsPageTransitionsBuilder(),
        },
      ),
    );
  }
}
