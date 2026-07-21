import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:get/get.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_palette.dart';
import '../../../core/widgets/app_widgets.dart';
import '../../controllers/counter_feedback_controller.dart';
import '../../widgets/feedback_form_body.dart';

/// Rate a specific counter — the mobile equivalent of
/// /beaa/feedback/{counter_id}/ on the web. The header shows the counter's
/// real name/number/zone, fetched fresh from the API.
class CounterFeedbackScreen extends StatelessWidget {
  const CounterFeedbackScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<CounterFeedbackController>();

    return Scaffold(
      backgroundColor: context.tones.canvas,
      appBar: AppBar(
        title: Obx(() => Text(
              controller.counterName.value.isEmpty
                  ? 'counter_title'.tr
                  : controller.counterName.value,
            )),
      ),
      body: AnimatedBackground(
        child: SafeArea(
          top: false,
          child: Column(
            children: [
              Obx(() {
                if (controller.counterName.value.isEmpty) {
                  return const SizedBox.shrink();
                }
                final parts = <String>[
                  if (controller.counterNumber.value > 0)
                    '#${controller.counterNumber.value}',
                  if (controller.zoneName.value.isNotEmpty)
                    controller.zoneName.value,
                ];
                if (parts.isEmpty) return const SizedBox.shrink();
                return Padding(
                  padding: EdgeInsets.fromLTRB(16.w, 0, 16.w, 8.h),
                  child: Container(
                    padding:
                        EdgeInsets.symmetric(horizontal: 14.w, vertical: 8.h),
                    decoration: BoxDecoration(
                      color: AppColors.blue.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(12.r),
                      border: Border.all(color: context.tones.line),
                    ),
                    child: Text(
                      parts.join('   ·   '),
                      style: TextStyle(
                        color: AppColors.blueLight,
                        fontSize: 13.sp,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
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
        ),
      ),
    );
  }
}
