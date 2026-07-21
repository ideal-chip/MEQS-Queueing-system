import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:get/get.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_palette.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/app_widgets.dart';
import '../../../data/models/counter_model.dart';
import '../../../data/repositories/feedback_repository.dart';
import '../../controllers/counter_feedback_controller.dart';
import '../../controllers/counters_list_controller.dart';
import '../../controllers/settings_controller.dart';
import 'counter_feedback_screen.dart';

/// Lists every counter available for feedback, with a live search field
/// (by name, number, or zone). Tapping a counter opens its own feedback form.
class CountersListScreen extends StatelessWidget {
  const CountersListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<CountersListController>();
    final settings = Get.find<SettingsController>();

    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        title: Obx(() {
          final custom = settings.counterFeedbackTitle.value;
          return Text(custom.isEmpty ? 'counter_title'.tr : custom);
        }),
      ),
      body: SafeArea(
        top: false,
        child: Column(
          children: [
            Padding(
              padding: EdgeInsets.fromLTRB(16.w, 8.h, 16.w, 8.h),
              child: TextField(
                onChanged: controller.updateSearch,
                decoration: InputDecoration(
                  hintText: 'search_counters'.tr,
                  prefixIcon: const Icon(Icons.search_rounded),
                ),
              ),
            ),
            Expanded(
              child: Obx(() {
                switch (controller.status.value) {
                  case CountersListStatus.loading:
                    return const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation(AppColors.blue),
                      ),
                    );
                  case CountersListStatus.error:
                    return _CountersError(
                      message: controller.errorMessage.value,
                      onRetry: controller.loadCounters,
                    );
                  case CountersListStatus.ready:
                    final counters = controller.filteredCounters;
                    if (counters.isEmpty) {
                      return Center(
                        child: Text(
                          controller.searchQuery.value.isEmpty
                              ? 'no_counters'.tr
                              : 'no_counters_match'.tr,
                          style: TextStyle(color: context.tones.fg2),
                        ),
                      );
                    }
                    return _CountersGrid(counters: counters, onRefresh: controller.loadCounters);
                }
              }),
            ),
          ],
        ),
      ),
    );
  }
}

class _CountersGrid extends StatelessWidget {
  final List<CounterModel> counters;
  final Future<void> Function() onRefresh;

  const _CountersGrid({required this.counters, required this.onRefresh});

  @override
  Widget build(BuildContext context) {
    final columns = context.gridColumns;
    return RefreshIndicator(
      onRefresh: onRefresh,
      color: AppColors.blue,
      backgroundColor: AppColors.bgLight,
      child: LayoutBuilder(
        builder: (context, constraints) {
          final gap = 14.w;
          final maxW = constraints.maxWidth.clamp(0, context.contentMaxWidth);
          final itemW = columns == 2 ? (maxW - gap) / 2 : maxW.toDouble();
          return SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.fromLTRB(16.w, 4.h, 16.w, 24.h),
            child: Center(
              child: ConstrainedBox(
                constraints:
                    BoxConstraints(maxWidth: context.contentMaxWidth),
                child: Wrap(
                  spacing: gap,
                  runSpacing: gap,
                  children: [
                    for (int i = 0; i < counters.length; i++)
                      SizedBox(
                        width: itemW,
                        child: SlideFadeIn(
                          delay: Duration(milliseconds: 60 * i),
                          child: _CounterCard(counter: counters[i]),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

class _CounterCard extends StatelessWidget {
  final CounterModel counter;

  const _CounterCard({required this.counter});

  void _open() {
    // A fresh controller per counter — delete any previous one first so
    // switching between counters never shows stale ratings.
    if (Get.isRegistered<CounterFeedbackController>()) {
      Get.delete<CounterFeedbackController>();
    }
    Get.put(
      CounterFeedbackController(
        repository: Get.find<FeedbackRepository>(),
        settings: Get.find<SettingsController>(),
        counterId: counter.counterId,
      ),
    );
    Get.to(() => const CounterFeedbackScreen(), transition: Transition.rightToLeft);
  }

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      onTap: _open,
      padding: EdgeInsets.symmetric(horizontal: 16.w, vertical: 14.h),
      child: Row(
        children: [
          Container(
            width: 46.r,
            height: 46.r,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: const LinearGradient(
                colors: [AppColors.blue, AppColors.blueDark],
              ),
              boxShadow: [
                BoxShadow(
                  color: AppColors.blue.withValues(alpha: 0.4),
                  blurRadius: 10,
                ),
              ],
            ),
            child: Text(
              counter.counterNumber.toString(),
              style: TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w800,
                fontSize: 16.sp,
              ),
            ),
          ),
          SizedBox(width: 14.w),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  counter.counterName,
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 15.sp,
                    color: context.tones.fg,
                  ),
                ),
                if ((counter.zoneName ?? '').isNotEmpty) ...[
                  SizedBox(height: 2.h),
                  Text(
                    counter.zoneName!,
                    style: TextStyle(
                      color: context.tones.fg2,
                      fontSize: 12.5.sp,
                    ),
                  ),
                ],
              ],
            ),
          ),
          Icon(Icons.chevron_right_rounded,
              color: context.tones.fg3, size: 24.sp),
        ],
      ),
    );
  }
}

class _CountersError extends StatelessWidget {
  final String message;
  final Future<void> Function() onRetry;

  const _CountersError({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: EdgeInsets.all(24.w),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.wifi_off_rounded, size: 64.sp, color: context.tones.fg3),
            SizedBox(height: 16.h),
            Text(
              message.isEmpty ? 'something_wrong'.tr : message,
              textAlign: TextAlign.center,
              style: TextStyle(color: context.tones.fg2),
            ),
            SizedBox(height: 22.h),
            SizedBox(
              width: 200.w,
              child: GlowButton(
                label: 'retry'.tr,
                icon: Icons.refresh_rounded,
                onTap: onRetry,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
