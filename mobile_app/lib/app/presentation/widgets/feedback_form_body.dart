import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:get/get.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_palette.dart';
import '../../core/utils/responsive.dart';
import '../../core/widgets/app_widgets.dart';
import '../../data/models/feedback_question_model.dart';
import '../controllers/feedback_form_status.dart';
import 'star_rating_widget.dart';

/// The question list + submit + thank-you / error states, shared by the
/// General and Counter feedback screens so both look and behave identically.
/// Fully localized (`.tr`) and responsive: one column on a phone, two columns
/// on a landscape tablet, with the reading column capped on wide screens.
class FeedbackFormBody extends StatelessWidget {
  final List<FeedbackQuestionModel> questions;
  final FeedbackFormStatus status;
  final String errorMessage;
  final bool allRated;
  final double averageScore;
  final void Function(int index, int value) onRatingChanged;
  final VoidCallback onSubmit;
  final VoidCallback onReset;
  final VoidCallback onRetry;

  const FeedbackFormBody({
    super.key,
    required this.questions,
    required this.status,
    required this.errorMessage,
    required this.allRated,
    required this.averageScore,
    required this.onRatingChanged,
    required this.onSubmit,
    required this.onReset,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    switch (status) {
      case FeedbackFormStatus.loading:
        return const Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation(AppColors.blue),
          ),
        );
      case FeedbackFormStatus.error:
        return _ErrorView(message: errorMessage, onRetry: onRetry);
      case FeedbackFormStatus.submitted:
        return _ThankYouView(average: averageScore, onReset: onReset);
      case FeedbackFormStatus.ready:
      case FeedbackFormStatus.submitting:
        return _QuestionsView(
          questions: questions,
          allRated: allRated,
          submitting: status == FeedbackFormStatus.submitting,
          onRatingChanged: onRatingChanged,
          onSubmit: onSubmit,
        );
    }
  }
}

class _QuestionsView extends StatelessWidget {
  final List<FeedbackQuestionModel> questions;
  final bool allRated;
  final bool submitting;
  final void Function(int, int) onRatingChanged;
  final VoidCallback onSubmit;

  const _QuestionsView({
    required this.questions,
    required this.allRated,
    required this.submitting,
    required this.onRatingChanged,
    required this.onSubmit,
  });

  @override
  Widget build(BuildContext context) {
    final columns = context.gridColumns;
    return LayoutBuilder(
      builder: (context, constraints) {
        final maxW = constraints.maxWidth.clamp(0, context.contentMaxWidth);
        final gap = 14.w;
        final itemW = columns == 2 ? (maxW - gap) / 2 : maxW.toDouble();
        return SingleChildScrollView(
          padding: EdgeInsets.fromLTRB(16.w, 16.h, 16.w, 32.h),
          child: Center(
            child: ConstrainedBox(
              constraints: BoxConstraints(maxWidth: context.contentMaxWidth),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Wrap(
                    spacing: gap,
                    runSpacing: gap,
                    children: [
                      for (int i = 0; i < questions.length; i++)
                        SizedBox(
                          width: itemW,
                          child: SlideFadeIn(
                            delay: Duration(milliseconds: 80 * i),
                            child: _QuestionCard(
                              index: i,
                              question: questions[i],
                              onRatingChanged: onRatingChanged,
                            ),
                          ),
                        ),
                    ],
                  ),
                  SizedBox(height: 24.h),
                  GlowButton(
                    label: submitting ? 'submitting'.tr : 'submit'.tr,
                    icon: submitting ? null : Icons.send_rounded,
                    isLoading: submitting,
                    onTap: allRated && !submitting ? onSubmit : null,
                  ),
                  if (!allRated && questions.isNotEmpty) ...[
                    SizedBox(height: 12.h),
                    Text(
                      'rate_all_hint'.tr,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: context.tones.fg2,
                        fontSize: 13.sp,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}

class _QuestionCard extends StatelessWidget {
  final int index;
  final FeedbackQuestionModel question;
  final void Function(int, int) onRatingChanged;

  const _QuestionCard({
    required this.index,
    required this.question,
    required this.onRatingChanged,
  });

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      padding: EdgeInsets.symmetric(horizontal: 18.w, vertical: 18.h),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            question.label,
            style: TextStyle(
              fontSize: 16.sp,
              fontWeight: FontWeight.w700,
              color: context.tones.fg,
            ),
          ),
          SizedBox(height: 12.h),
          Center(
            child: StarRatingWidget(
              value: question.rating,
              onChanged: (v) => onRatingChanged(index, v),
            ),
          ),
        ],
      ),
    );
  }
}

class _ThankYouView extends StatelessWidget {
  final double average;
  final VoidCallback onReset;

  const _ThankYouView({required this.average, required this.onReset});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SingleChildScrollView(
        padding: EdgeInsets.all(24.w),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TweenAnimationBuilder<double>(
              tween: Tween(begin: 0, end: 1),
              duration: const Duration(milliseconds: 700),
              curve: Curves.elasticOut,
              builder: (_, v, _) => Transform.scale(
                scale: v,
                child: Container(
                  width: 108.r,
                  height: 108.r,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: const RadialGradient(
                      colors: [AppColors.blue, AppColors.blueDark],
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.blue.withValues(alpha: 0.45),
                        blurRadius: 30,
                        spreadRadius: 4,
                      ),
                    ],
                  ),
                  child: Icon(Icons.check_rounded,
                      size: 64.sp, color: Colors.white),
                ),
              ),
            ),
            SizedBox(height: 26.h),
            Text(
              'thank_you'.tr,
              style: TextStyle(fontSize: 24.sp, fontWeight: FontWeight.w800),
            ),
            SizedBox(height: 10.h),
            Text(
              'feedback_submitted'.tr,
              textAlign: TextAlign.center,
              style: TextStyle(color: context.tones.fg2, fontSize: 14.sp),
            ),
            if (average > 0) ...[
              SizedBox(height: 18.h),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.star_rounded, color: AppColors.gold, size: 22.sp),
                  SizedBox(width: 6.w),
                  Text(
                    '${'average'.tr}: ${average.toStringAsFixed(1)} / 5',
                    style: TextStyle(
                      color: AppColors.gold,
                      fontWeight: FontWeight.w700,
                      fontSize: 15.sp,
                    ),
                  ),
                ],
              ),
            ],
            SizedBox(height: 30.h),
            SizedBox(
              width: 220.w,
              child: GlowButton(
                label: 'rate_again'.tr,
                icon: Icons.refresh_rounded,
                color: AppColors.gold,
                onTap: onReset,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const _ErrorView({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SingleChildScrollView(
        padding: EdgeInsets.all(24.w),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.wifi_off_rounded,
                size: 64.sp, color: context.tones.fg3),
            SizedBox(height: 16.h),
            Text(
              message.isEmpty ? 'something_wrong'.tr : message,
              textAlign: TextAlign.center,
              style: TextStyle(color: context.tones.fg2, fontSize: 14.sp),
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
