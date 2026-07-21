import 'package:flutter/material.dart';

import 'app_colors.dart';

/// Brightness-aware colour set. Brand colours (blue/gold) stay the same in
/// both modes; only the neutrals (background, surfaces, text) flip. Widgets
/// read `context.tones` so the exact same widget tree renders correctly in
/// dark mode (default) and light mode.
class Tones {
  final bool isDark;
  final Color fg; // primary text
  final Color fg2; // secondary text
  final Color fg3; // muted text / empty stars
  final Color card; // glass card surface
  final Color line; // borders
  final Color field; // input fill
  final Color canvas; // scaffold / nav background
  final Gradient bg; // animated background gradient

  const Tones({
    required this.isDark,
    required this.fg,
    required this.fg2,
    required this.fg3,
    required this.card,
    required this.line,
    required this.field,
    required this.canvas,
    required this.bg,
  });

  static const Tones dark = Tones(
    isDark: true,
    fg: AppColors.textPrimary,
    fg2: AppColors.textSecondary,
    fg3: AppColors.textMuted,
    card: Color(0xD90D2137), // bgMid @ ~85%
    line: AppColors.cardBorder,
    field: Color(0x99112944), // bgLight @ 60%
    canvas: AppColors.bgDeep,
    bg: AppColors.bgGradient,
  );

  static const Tones light = Tones(
    isDark: false,
    fg: AppColors.textPrimaryL,
    fg2: AppColors.textSecondaryL,
    fg3: AppColors.textMutedL,
    card: AppColors.surfaceL,
    line: AppColors.cardBorderL,
    field: AppColors.fieldFillL,
    canvas: AppColors.bgDeepL,
    bg: AppColors.bgGradientL,
  );
}

extension TonesX on BuildContext {
  Tones get tones =>
      Theme.of(this).brightness == Brightness.dark ? Tones.dark : Tones.light;
}
