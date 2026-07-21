import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../controllers/general_feedback_controller.dart';
import '../../controllers/settings_controller.dart';
import '../../widgets/feedback_form_body.dart';

/// Rate the service in general (not tied to any counter) — the mobile
/// equivalent of the web kiosk's /beaa/feedback/ page.
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
