import 'package:get/get.dart';

import '../../data/models/api_exception.dart';
import '../../data/models/feedback_question_model.dart';
import '../../data/repositories/feedback_repository.dart';
import 'feedback_form_status.dart';
import 'settings_controller.dart';

/// Drives the General Feedback screen: loads the question list from
/// GET /feedback/form, tracks star ratings, and submits via
/// POST /feedback/submissions. Mirrors the web kiosk at /beaa/feedback/
/// exactly -- same questions, same 1-5 scale, "rate everything before
/// submitting" validation.
class GeneralFeedbackController extends GetxController {
  final FeedbackRepository repository;
  final SettingsController settings;

  GeneralFeedbackController({required this.repository, required this.settings});

  final status = FeedbackFormStatus.loading.obs;
  final questions = <FeedbackQuestionModel>[].obs;
  final errorMessage = ''.obs;

  @override
  void onInit() {
    super.onInit();
    loadForm();
  }

  Future<void> loadForm() async {
    status.value = FeedbackFormStatus.loading;
    try {
      final form = await repository.getGeneralForm(language: settings.languageCode.value);
      questions.assignAll(form.questions);
      status.value = FeedbackFormStatus.ready;
    } on ApiException catch (e) {
      errorMessage.value = e.message;
      status.value = FeedbackFormStatus.error;
    }
  }

  void setRating(int index, int value) {
    if (index < 0 || index >= questions.length) return;
    questions[index].rating = value;
    questions.refresh();
  }

  bool get allRated => questions.isNotEmpty && questions.every((q) => q.rating > 0);

  double get averageScore {
    if (questions.isEmpty) return 0;
    final total = questions.fold<int>(0, (sum, q) => sum + q.rating);
    return total / questions.length;
  }

  Future<void> submit() async {
    if (!allRated || status.value == FeedbackFormStatus.submitting) return;
    status.value = FeedbackFormStatus.submitting;
    try {
      final ratings = <String, int>{for (final q in questions) q.key: q.rating};
      await repository.submitGeneral(language: settings.languageCode.value, ratings: ratings);
      status.value = FeedbackFormStatus.submitted;
    } on ApiException catch (e) {
      errorMessage.value = e.message;
      status.value = FeedbackFormStatus.ready;
      Get.snackbar('Could not submit', e.message);
    }
  }

  void resetForm() {
    for (final q in questions) {
      q.rating = 0;
    }
    questions.refresh();
    status.value = FeedbackFormStatus.ready;
  }
}
