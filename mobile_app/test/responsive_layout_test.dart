import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:get/get.dart';

import 'package:meqs_feedback/app/core/utils/responsive.dart';
import 'package:meqs_feedback/app/data/models/feedback_question_model.dart';
import 'package:meqs_feedback/app/presentation/controllers/feedback_form_status.dart';
import 'package:meqs_feedback/app/presentation/widgets/feedback_form_body.dart';
import 'package:meqs_feedback/app/presentation/widgets/star_rating_widget.dart';

/// Screen sizes, in logical pixels, of the devices this app has to look right
/// on. The tablet numbers are a 10.95" Android panel (1920x1200 @ dpr 2).
const _phonePortrait = Size(430, 932);
const _tabletPortrait = Size(600, 960);
const _tabletLandscape = Size(960, 600);

List<FeedbackQuestionModel> _questions() => [
  FeedbackQuestionModel(key: 'fb0', label: 'جودة الخدمة لدينا'),
  FeedbackQuestionModel(key: 'fb1', label: 'الموظف لديه الخبرة الكافية'),
  FeedbackQuestionModel(key: 'fb2', label: 'سرعة إنجاز المعاملة'),
];

/// Pumps the shared feedback form at [size] using the same ScreenUtil setup
/// main.dart uses, so the widths the test exercises are the real ones.
Future<void> _pumpFormAt(WidgetTester tester, Size size) async {
  tester.view.physicalSize = size;
  tester.view.devicePixelRatio = 1.0;
  addTearDown(tester.view.reset);

  await tester.pumpWidget(
    ScreenUtilInit(
      designSize: DesignCanvas.of(size),
      minTextAdapt: true,
      splitScreenMode: true,
      builder: (context, child) => GetMaterialApp(
        locale: const Locale('ar'),
        home: Scaffold(
          body: FeedbackFormBody(
            questions: _questions(),
            status: FeedbackFormStatus.ready,
            errorMessage: '',
            allRated: false,
            averageScore: 0,
            onRatingChanged: (_, _) {},
            onSubmit: () {},
            onReset: () {},
            onRetry: () {},
          ),
        ),
      ),
    ),
  );
  await tester.pump(const Duration(seconds: 1));
}

void main() {
  group('DesignCanvas', () {
    test('follows the device orientation instead of a fixed portrait phone', () {
      expect(DesignCanvas.of(_phonePortrait), DesignCanvas.phonePortrait);
      expect(DesignCanvas.of(const Size(932, 430)), DesignCanvas.phoneLandscape);
      expect(DesignCanvas.of(_tabletPortrait), DesignCanvas.tabletPortrait);
      expect(DesignCanvas.of(_tabletLandscape), DesignCanvas.tabletLandscape);
    });

    test('keeps the two ScreenUtil scale factors close on every device', () {
      // This is the actual regression. With the old fixed 430x932 canvas a
      // landscape tablet scaled width by ~2.98 and height by ~0.64 -- a 4.6x
      // mismatch, which is what tore the horizontal layout apart.
      for (final screen in [_phonePortrait, _tabletPortrait, _tabletLandscape]) {
        final design = DesignCanvas.of(screen);
        final scaleW = screen.width / design.width;
        final scaleH = screen.height / design.height;
        expect(
          (scaleW / scaleH),
          closeTo(1.0, 0.25),
          reason: 'width/height scaling diverges on $screen',
        );

        final oldScaleW = screen.width / 430;
        final oldScaleH = screen.height / 932;
        expect(
          (scaleW / scaleH - 1).abs(),
          lessThanOrEqualTo((oldScaleW / oldScaleH - 1).abs()),
          reason: 'no better than the old fixed canvas on $screen',
        );
      }
    });
  });

  group('feedback form layout', () {
    testWidgets('renders without overflow on a landscape tablet', (
      tester,
    ) async {
      await _pumpFormAt(tester, _tabletLandscape);
      expect(tester.takeException(), isNull);
    });

    testWidgets('renders without overflow on a portrait tablet', (
      tester,
    ) async {
      await _pumpFormAt(tester, _tabletPortrait);
      expect(tester.takeException(), isNull);
    });

    testWidgets('renders without overflow on a phone', (tester) async {
      await _pumpFormAt(tester, _phonePortrait);
      expect(tester.takeException(), isNull);
    });

    testWidgets('keeps all five stars on one row per question', (tester) async {
      await _pumpFormAt(tester, _tabletLandscape);

      final stars = find.byIcon(Icons.star_outline_rounded);
      expect(stars, findsNWidgets(15)); // 3 questions x 5 stars

      // Every star of a question must share its neighbours' vertical centre;
      // before the fix the row wrapped onto a second line.
      for (var q = 0; q < 3; q++) {
        final centres = [
          for (var i = 0; i < 5; i++) tester.getCenter(stars.at(q * 5 + i)).dy,
        ];
        for (final dy in centres) {
          expect(dy, closeTo(centres.first, 0.5));
        }
      }
    });

    testWidgets('goes two-up in landscape and one-up in portrait', (
      tester,
    ) async {
      // The question cards are private to feedback_form_body.dart, so their
      // star rows stand in for them: side by side means one row, stacked
      // means one column.
      final rows = find.byType(StarRatingWidget);

      await _pumpFormAt(tester, _tabletLandscape);
      expect(
        tester.getCenter(rows.at(0)).dy,
        closeTo(tester.getCenter(rows.at(1)).dy, 0.5),
      );

      await _pumpFormAt(tester, _tabletPortrait);
      expect(
        tester.getCenter(rows.at(1)).dy,
        greaterThan(tester.getCenter(rows.at(0)).dy),
      );
    });
  });
}
