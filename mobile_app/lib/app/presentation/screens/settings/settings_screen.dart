import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:get/get.dart';

import '../../../core/constants/app_defaults.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_palette.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/app_widgets.dart';
import '../../controllers/settings_controller.dart';

/// Reconfigure the app without a rebuild: server URL, custom titles, language
/// (Arabic/English — switching here flips the whole app, including RTL), the
/// accent colour, and a reset. Everything is persisted and applies instantly.
///
/// Pushed as its own route from the gear button in the general rating page's
/// top bar, so it carries its own background and back button rather than
/// relying on the shell that used to host it behind the bottom nav bar.
class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final settings = Get.find<SettingsController>();

    return Scaffold(
      backgroundColor: context.tones.canvas,
      appBar: AppBar(title: Text('nav_settings'.tr)),
      body: AnimatedBackground(
        child: SafeArea(
          top: false,
          child: Center(
            child: ConstrainedBox(
              constraints: BoxConstraints(maxWidth: context.contentMaxWidth),
              child: ListView(
                padding: EdgeInsets.fromLTRB(16.w, 8.h, 16.w, 32.h),
                children: [
                  Center(
                    child: Padding(
                      padding: EdgeInsets.symmetric(vertical: 8.h),
                      child: const BrandLogo(height: 52),
                    ),
                  ),
                  SizedBox(height: 12.h),

                  // ── Language ──────────────────────────────────────────────
                  _Section(
                    title: 'settings_language'.tr,
                    child: Obx(
                      () => _LanguageToggle(
                        current: settings.languageCode.value,
                        onSelect: settings.setLanguage,
                      ),
                    ),
                  ),

                  // ── Theme ─────────────────────────────────────────────────
                  _Section(
                    title: 'settings_theme'.tr,
                    child: Obx(
                      () => Row(
                        children: [
                          Expanded(
                            child: _ToggleButton(
                              label: 'theme_dark'.tr,
                              icon: Icons.dark_mode_rounded,
                              selected: settings.isDarkMode,
                              onTap: () => settings.setThemeMode('dark'),
                            ),
                          ),
                          SizedBox(width: 10.w),
                          Expanded(
                            child: _ToggleButton(
                              label: 'theme_light'.tr,
                              icon: Icons.light_mode_rounded,
                              selected: !settings.isDarkMode,
                              onTap: () => settings.setThemeMode('light'),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // ── Server ────────────────────────────────────────────────
                  _Section(
                    title: 'settings_server'.tr,
                    child: _TextSetting(
                      label: 'api_base_url'.tr,
                      hint: AppDefaults.apiBaseUrl,
                      initial: settings.apiBaseUrl.value,
                      onSaved: settings.updateApiBaseUrl,
                    ),
                  ),

                  // ── Titles ────────────────────────────────────────────────
                  _Section(
                    title: 'settings_titles'.tr,
                    child: Column(
                      children: [
                        _TextSetting(
                          label: 'general_title_label'.tr,
                          initial: settings.generalFeedbackTitle.value,
                          onSaved: settings.updateGeneralFeedbackTitle,
                        ),
                        SizedBox(height: 12.h),
                        _TextSetting(
                          label: 'counter_title_label'.tr,
                          initial: settings.counterFeedbackTitle.value,
                          onSaved: settings.updateCounterFeedbackTitle,
                        ),
                      ],
                    ),
                  ),

                  // ── Accent colour ─────────────────────────────────────────
                  _Section(
                    title: 'settings_appearance'.tr,
                    child: Obx(
                      () => Column(
                        children: [
                          _ColorSetting(
                            color: settings.primaryColor.value,
                            onChanged: settings.updatePrimaryColor,
                          ),
                          SizedBox(height: 12.h),
                          _ColorSetting(
                            color: settings.accentColor.value,
                            onChanged: settings.updateAccentColor,
                          ),
                        ],
                      ),
                    ),
                  ),

                  SizedBox(height: 20.h),
                  GlowButton(
                    label: 'reset_defaults'.tr,
                    icon: Icons.restart_alt_rounded,
                    color: AppColors.textMuted,
                    onTap: () => _confirmReset(context, settings),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _confirmReset(BuildContext context, SettingsController settings) {
    Get.dialog(
      AlertDialog(
        backgroundColor: AppColors.bgMid,
        title: Text('reset_title'.tr),
        content: Text('reset_confirm_msg'.tr),
        actions: [
          TextButton(onPressed: Get.back, child: Text('cancel'.tr)),
          FilledButton(
            onPressed: () {
              settings.resetToDefaults();
              Get.back();
              Get.snackbar(
                'saved'.tr,
                'reset_done'.tr,
                snackPosition: SnackPosition.BOTTOM,
              );
            },
            child: Text('reset_defaults'.tr),
          ),
        ],
      ),
    );
  }
}

class _Section extends StatelessWidget {
  final String title;
  final Widget child;

  const _Section({required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: 18.h),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: EdgeInsets.only(bottom: 8.h, left: 4.w, right: 4.w),
            child: Text(
              title,
              style: TextStyle(
                fontSize: 13.sp,
                fontWeight: FontWeight.w700,
                color: AppColors.blueLight,
                letterSpacing: 0.4,
              ),
            ),
          ),
          GlassCard(child: child),
        ],
      ),
    );
  }
}

class _LanguageToggle extends StatelessWidget {
  final String current;
  final ValueChanged<String> onSelect;

  const _LanguageToggle({required this.current, required this.onSelect});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _ToggleButton(
            label: 'العربية',
            selected: current == 'ar',
            onTap: () => onSelect('ar'),
          ),
        ),
        SizedBox(width: 10.w),
        Expanded(
          child: _ToggleButton(
            label: 'English',
            selected: current == 'en',
            onTap: () => onSelect('en'),
          ),
        ),
      ],
    );
  }
}

class _ToggleButton extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;
  final IconData? icon;

  const _ToggleButton({
    required this.label,
    required this.selected,
    required this.onTap,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    final tones = context.tones;
    final unselectedFg = tones.fg2;
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        height: 46.h,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12.r),
          gradient: selected
              ? const LinearGradient(
                  colors: [AppColors.blue, AppColors.blueDark],
                )
              : null,
          color: selected ? null : tones.field,
          border: Border.all(color: selected ? AppColors.blue : tones.line),
          boxShadow: selected
              ? [
                  BoxShadow(
                    color: AppColors.blue.withValues(alpha: 0.4),
                    blurRadius: 12,
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (icon != null) ...[
              Icon(
                icon,
                size: 18.sp,
                color: selected ? Colors.white : unselectedFg,
              ),
              SizedBox(width: 8.w),
            ],
            Text(
              label,
              style: TextStyle(
                fontSize: 15.sp,
                fontWeight: FontWeight.w700,
                color: selected ? Colors.white : unselectedFg,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TextSetting extends StatefulWidget {
  final String label;
  final String? hint;
  final String initial;
  final ValueChanged<String> onSaved;

  const _TextSetting({
    required this.label,
    this.hint,
    required this.initial,
    required this.onSaved,
  });

  @override
  State<_TextSetting> createState() => _TextSettingState();
}

class _TextSettingState extends State<_TextSetting> {
  late final TextEditingController _controller = TextEditingController(
    text: widget.initial,
  );

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: _controller,
      style: TextStyle(fontSize: 14.sp, color: context.tones.fg),
      decoration: InputDecoration(
        labelText: widget.label,
        hintText: widget.hint,
        isDense: true,
      ),
      onSubmitted: widget.onSaved,
      onTapOutside: (_) {
        FocusScope.of(context).unfocus();
        widget.onSaved(_controller.text);
      },
    );
  }
}

class _ColorSetting extends StatelessWidget {
  final Color color;
  final ValueChanged<Color> onChanged;

  const _ColorSetting({required this.color, required this.onChanged});

  static const List<Color> _palette = [
    AppColors.blue,
    AppColors.blueLight,
    AppColors.gold,
    Color(0xFF27AE60),
    Color(0xFFE74C3C),
    Color(0xFF9B59B6),
    Color(0xFF1ABC9C),
    Color(0xFF34495E),
  ];

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 12.w,
      runSpacing: 12.h,
      children: _palette.map((c) {
        final selected = c.toARGB32() == color.toARGB32();
        return GestureDetector(
          onTap: () => onChanged(c),
          child: Container(
            width: 34.r,
            height: 34.r,
            decoration: BoxDecoration(
              color: c,
              shape: BoxShape.circle,
              border: Border.all(
                color: selected ? Colors.white : Colors.transparent,
                width: 2.5,
              ),
              boxShadow: selected
                  ? [BoxShadow(color: c.withValues(alpha: 0.6), blurRadius: 12)]
                  : null,
            ),
          ),
        );
      }).toList(),
    );
  }
}
