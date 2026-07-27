import 'package:flutter/material.dart';

import '../../../core/theme/app_palette.dart';
import '../../../core/widgets/app_widgets.dart';
import '../general_feedback/general_feedback_screen.dart';

/// The app shell: the animated particle background behind the general rating
/// page, which is now the app's only home. There is no bottom navigation bar —
/// Settings is reached from the gear button in the top bar (see
/// [GeneralFeedbackScreen]), and per-counter feedback is switched off via
/// `AppDefaults.counterFeedbackEnabled`. No login required.
class RootScreen extends StatelessWidget {
  const RootScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: context.tones.canvas,
      body: const AnimatedBackground(child: GeneralFeedbackScreen()),
    );
  }
}
