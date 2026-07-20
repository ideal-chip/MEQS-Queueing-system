import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../controllers/counter_feedback_controller.dart';
import '../../widgets/feedback_form_body.dart';

/// Rate a specific counter -- the mobile equivalent of
/// /beaa/feedback/{counter_id}/ on the web. The app bar shows the
/// counter's real name/number/zone, fetched fresh from the API (never
/// hardcoded), so it always matches whatever is currently set on
/// /beaa/admin/counters.php.
class CounterFeedbackScreen extends StatelessWidget {
  const CounterFeedbackScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<CounterFeedbackController>();

    return Scaffold(
      appBar: AppBar(
        title: Obx(() => Text(
              controller.counterName.value.isEmpty
                  ? 'Counter Feedback'
                  : '${controller.counterName.value} Feedback',
            )),
      ),
      body: Column(
        children: [
          Obx(() {
            if (controller.counterName.value.isEmpty) return const SizedBox.shrink();
            return Container(
              width: double.infinity,
              color: Theme.of(context).colorScheme.primary,
              padding: const EdgeInsets.fromLTRB(20, 4, 20, 16),
              child: Text(
                [
                  controller.counterName.value,
                  if (controller.counterNumber.value > 0) '#${controller.counterNumber.value}',
                  if (controller.zoneName.value.isNotEmpty) controller.zoneName.value,
                ].join('  ·  '),
                style: const TextStyle(color: Colors.white70, fontSize: 13),
              ),
            );
          }),
          Expanded(
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
        ],
      ),
    );
  }
}
