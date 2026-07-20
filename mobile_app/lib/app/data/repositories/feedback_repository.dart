import '../models/feedback_question_model.dart';
import '../providers/api_provider.dart';

class FeedbackRepository {
  final ApiProvider api;

  FeedbackRepository({required this.api});

  /// GET /feedback/form -- the general (global) feedback questionnaire.
  Future<FeedbackFormModel> getGeneralForm({required String language}) async {
    final data = await api.get('/feedback/form', query: {'language': language});
    return FeedbackFormModel.fromJson(data as Map<String, dynamic>);
  }

  /// GET /counters/{id}/feedback/form -- a specific counter's questionnaire.
  Future<FeedbackFormModel> getCounterForm({
    required int counterId,
    required String language,
  }) async {
    final data = await api.get(
      '/counters/$counterId/feedback/form',
      query: {'language': language},
    );
    return FeedbackFormModel.fromJson(data as Map<String, dynamic>);
  }

  /// POST /feedback/submissions -- submit a global rating.
  Future<void> submitGeneral({
    required String language,
    required Map<String, int> ratings,
    String note = '',
  }) async {
    await api.post('/feedback/submissions', {
      'language': language,
      'ratings': ratings,
      'note': note,
    });
  }

  /// POST /counters/{id}/feedback/submissions -- submit a counter rating.
  Future<void> submitCounter({
    required int counterId,
    required String language,
    required Map<String, int> ratings,
    String note = '',
  }) async {
    await api.post('/counters/$counterId/feedback/submissions', {
      'language': language,
      'ratings': ratings,
      'note': note,
    });
  }
}
