import 'package:flutter/material.dart';

/// The Ideal Feedback design system, adapted from the Smart-Glasses reference
/// UI (deep-navy background + animated particles/glows) and tinted to the
/// iDEAL-Q brand: the logo's blue is the primary accent, its gold dot the
/// secondary accent.
class AppColors {
  AppColors._();

  // ─── Background layers (deep navy) ─────────────────────────────────────────
  static const Color bgDeep = Color(0xFF060D18);
  static const Color bgDark = Color(0xFF0A1628);
  static const Color bgMid = Color(0xFF0D2137);
  static const Color bgLight = Color(0xFF112944);

  // ─── Brand blue (iDEAL-Q logo square) — the primary accent ────────────────
  static const Color blue = Color(0xFF2E77B5);
  static const Color blueDark = Color(0xFF215D91);
  static const Color blueLight = Color(0xFF4A93D1);
  static const Color blueGlow = Color(0x332E77B5);

  // ─── Brand gold (iDEAL-Q logo dot) — the secondary accent ─────────────────
  static const Color gold = Color(0xFFF5A623);
  static const Color goldDark = Color(0xFFD98A0B);
  static const Color goldGlow = Color(0x33F5A623);

  // ─── Surfaces (dark) ──────────────────────────────────────────────────────
  static const Color surface = Color(0x990D1F35);
  static const Color surfaceSolid = Color(0xFF0D1F35);
  static const Color cardBorder = Color(0x332E77B5);

  // ─── Text (dark) ──────────────────────────────────────────────────────────
  static const Color textPrimary = Color(0xFFFFFFFF);
  static const Color textSecondary = Color(0xFF9CA3AF);
  static const Color textMuted = Color(0xFF64748B);

  // ─── Light-mode counterparts ──────────────────────────────────────────────
  static const Color bgDeepL = Color(0xFFF4F8FC);
  static const Color bgMidL = Color(0xFFEAF1F9);
  static const Color bgLightL = Color(0xFFE1EBF5);
  static const Color surfaceL = Color(0xFFFFFFFF);
  static const Color cardBorderL = Color(0x1F2E77B5);
  static const Color fieldFillL = Color(0xFFEEF3F8);
  static const Color textPrimaryL = Color(0xFF0F2038);
  static const Color textSecondaryL = Color(0xFF5B6B7F);
  static const Color textMutedL = Color(0xFF9AA7B5);

  static const LinearGradient bgGradientL = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [bgDeepL, bgMidL, bgLightL],
  );

  // ─── Status ───────────────────────────────────────────────────────────────
  static const Color success = Color(0xFF22C55E);
  static const Color error = Color(0xFFEF4444);
  static const Color warning = Color(0xFFF59E0B);

  // ─── Gradients ────────────────────────────────────────────────────────────
  static const LinearGradient bgGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [bgDeep, bgDark, bgMid],
  );

  static const LinearGradient blueGradient = LinearGradient(
    colors: [blueDark, blue],
  );

  static const LinearGradient goldGradient = LinearGradient(
    colors: [goldDark, gold],
  );
}
