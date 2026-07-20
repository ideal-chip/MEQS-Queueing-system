import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../controllers/nav_controller.dart';
import '../counter_feedback/counters_list_screen.dart';
import '../general_feedback/general_feedback_screen.dart';
import '../settings/settings_screen.dart';

/// The app shell: a bottom navigation bar with exactly the three sections
/// requested -- General Feedback, Counter Feedback, Settings. No login is
/// required for any of them.
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

    return Obx(
      () => Scaffold(
        body: IndexedStack(
          index: nav.currentIndex.value,
          children: _screens,
        ),
        bottomNavigationBar: BottomNavigationBar(
          currentIndex: nav.currentIndex.value,
          onTap: nav.changeTab,
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.star_outline_rounded),
              activeIcon: Icon(Icons.star_rounded),
              label: 'General',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.storefront_outlined),
              activeIcon: Icon(Icons.storefront_rounded),
              label: 'Counters',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.settings_outlined),
              activeIcon: Icon(Icons.settings_rounded),
              label: 'Settings',
            ),
          ],
        ),
      ),
    );
  }
}
