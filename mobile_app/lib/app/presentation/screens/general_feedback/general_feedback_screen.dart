import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:get/get.dart';

import '../../controllers/general_feedback_controller.dart';
import '../../controllers/settings_controller.dart';
import '../../widgets/feedback_form_body.dart';
import '../settings/settings_screen.dart';

/// Rate the service in general (not tied to any counter) — the mobile
/// equivalent of the web kiosk's /beaa/feedback/ page, and the app's home
/// screen. The gear button in the top bar is the only way into Settings now
/// that the bottom navigation bar is gone; it sits on the leading edge of the
/// actions area, which in the default Arabic (RTL) layout is the top left.
class GeneralFeedbackScreen extends StatelessWidget {
  const GeneralFeedbackScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<GeneralFeedbackController>();
    final settings = Get.find<SettingsController>();

    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        title: Obx(() {
          final custom = settings.generalFeedbackTitle.value;
          return Text(custom.isEmpty ? 'general_title'.tr : custom);
        }),
        actions: [
          IconButton(
            icon: Icon(Icons.settings_rounded, size: 24.sp),
            tooltip: 'nav_settings'.tr,
            onPressed: () => Get.to(
              () => const SettingsScreen(),
              transition: Transition.rightToLeft,
            ),
          ),
          SizedBox(width: 4.w),
        ],
      ),
      body: SafeArea(
        top: false,
        child: Obx(
          () => FeedbackFormBody(
            questions: controller.questions,
            status: controller.status.value,
            errorMessage: controller.errorMessage.value,
            allRated: controller.allRated,
            averageScore: controller.averageScore,
            onRatingChanged: controller.setRating,
            onSubmit: controller.submit,
            onReset: controller.resetForm,
            onRetry: controller.loadForm,
          ),
        ),
      ),
    );
  }
}
