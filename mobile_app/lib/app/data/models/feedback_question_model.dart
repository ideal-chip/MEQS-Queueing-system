/// One rating question (e.g. "Waiting time"), as returned by
/// GET /feedback/form and GET /counters/{id}/feedback/form.
class FeedbackQuestionModel {
  final String key; // fb0..fb4
  final String label;
  int rating; // 0 = unrated, 1-5 = selected

  FeedbackQuestionModel({
    required this.key,
    required this.label,
    this.rating = 0,
  });

  factory FeedbackQuestionModel.fromJson(Map<String, dynamic> json) {
    return FeedbackQuestionModel(
      key: json['key'] as String,
      label: json['label'] as String,
    );
  }
}

/// A counter's identity, embedded in GET /counters/{id}/feedback/form.
class FeedbackFormCounterInfo {
  final int counterId;
  final String counterName;
  final int counterNumber;
  final String? zoneName;

  const FeedbackFormCounterInfo({
    required this.counterId,
    required this.counterName,
    required this.counterNumber,
    required this.zoneName,
  });

  factory FeedbackFormCounterInfo.fromJson(Map<String, dynamic> json) {
    return FeedbackFormCounterInfo(
      counterId: (json['counter_id'] as num).toInt(),
      counterName: json['counter_name'] as String? ?? '',
      counterNumber: (json['counter_number'] as num?)?.toInt() ?? 0,
      zoneName: json['zone_name'] as String?,
    );
  }
}

/// The full response of a feedback-form request: the question list, plus
/// (for counter-scoped forms) which counter it's for.
class FeedbackFormModel {
  final String scope; // 'global' | 'counter'
  final FeedbackFormCounterInfo? counter;
  final List<FeedbackQuestionModel> questions;

  const FeedbackFormModel({
    required this.scope,
    required this.counter,
    required this.questions,
  });

  factory FeedbackFormModel.fromJson(Map<String, dynamic> json) {
    return FeedbackFormModel(
      scope: json['scope'] as String? ?? 'global',
      counter: json['counter'] != null
          ? FeedbackFormCounterInfo.fromJson(json['counter'] as Map<String, dynamic>)
          : null,
      questions: (json['questions'] as List<dynamic>? ?? [])
          .map((q) => FeedbackQuestionModel.fromJson(q as Map<String, dynamic>))
          .toList(),
    );
  }
}
