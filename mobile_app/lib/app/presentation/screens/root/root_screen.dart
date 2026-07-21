import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:get/get.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_palette.dart';
import '../../../core/widgets/app_widgets.dart';
import '../../controllers/nav_controller.dart';
import '../counter_feedback/counters_list_screen.dart';
import '../general_feedback/general_feedback_screen.dart';
import '../settings/settings_screen.dart';

/// The app shell: the animated particle background behind a three-tab
/// IndexedStack — General Feedback, Counter Feedback, Settings — with a
/// glowing custom bottom navigation bar. No login required.
class RootScreen extends StatelessWidget {
  const RootScreen({super.key});

  static const _screens = [
    GeneralFeedbackScreen(),
    CountersListScreen(),
    SettingsScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    final nav = Get.find<NavController>();

    return Scaffold(
      extendBody: true,
      backgroundColor: context.tones.canvas,
      body: AnimatedBackground(
        child: Obx(
          () => IndexedStack(
            index: nav.currentIndex.value,
            children: _screens,
          ),
        ),
      ),
      bottomNavigationBar: Obx(
        () => _GlowNavBar(
          currentIndex: nav.currentIndex.value,
          onTap: nav.changeTab,
        ),
      ),
    );
  }
}

class _GlowNavBar extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;

  const _GlowNavBar({required this.currentIndex, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: context.tones.canvas.withValues(alpha: 0.95),
        border: Border(
          top: BorderSide(
            color: AppColors.blue.withValues(alpha: 0.25),
            width: 0.8,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: AppColors.blue.withValues(alpha: 0.08),
            blurRadius: 16,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: EdgeInsets.symmetric(vertical: 6.h),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _NavItem(
                icon: Icons.star_outline_rounded,
                activeIcon: Icons.star_rounded,
                label: 'nav_general'.tr,
                index: 0,
                currentIndex: currentIndex,
                onTap: onTap,
              ),
              _NavItem(
                icon: Icons.storefront_outlined,
                activeIcon: Icons.storefront_rounded,
                label: 'nav_counters'.tr,
                index: 1,
                currentIndex: currentIndex,
                onTap: onTap,
              ),
              _NavItem(
                icon: Icons.settings_outlined,
                activeIcon: Icons.settings_rounded,
                label: 'nav_settings'.tr,
                index: 2,
                currentIndex: currentIndex,
                onTap: onTap,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final IconData icon;
  final IconData activeIcon;
  final String label;
  final int index;
  final int currentIndex;
  final ValueChanged<int> onTap;

  const _NavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    required this.index,
    required this.currentIndex,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isActive = index == currentIndex;
    return GestureDetector(
      onTap: () => onTap(index),
      behavior: HitTestBehavior.opaque,
      child: SizedBox(
        width: 92.w,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 220),
              padding: EdgeInsets.all(7.r),
              decoration: BoxDecoration(
                color: isActive
                    ? AppColors.blue.withValues(alpha: 0.15)
                    : Colors.transparent,
                borderRadius: BorderRadius.circular(12.r),
                boxShadow: isActive
                    ? [
                        BoxShadow(
                          color: AppColors.blue.withValues(alpha: 0.35),
                          blurRadius: 12,
                        ),
                      ]
                    : null,
              ),
              child: Icon(
                isActive ? activeIcon : icon,
                color: isActive ? AppColors.blueLight : context.tones.fg3,
                size: 24.sp,
              ),
            ),
            SizedBox(height: 3.h),
            AnimatedDefaultTextStyle(
              duration: const Duration(milliseconds: 220),
              style: TextStyle(
                fontSize: 11.sp,
                color: isActive ? AppColors.blueLight : context.tones.fg3,
                fontWeight: isActive ? FontWeight.w700 : FontWeight.w500,
              ),
              child: Text(label),
            ),
          ],
        ),
      ),
    );
  }
}
