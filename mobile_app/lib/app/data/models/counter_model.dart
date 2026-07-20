/// A service counter, as returned by GET /counters?feedback_enabled=1
class CounterModel {
  final int counterId;
  final String counterName;
  final int counterNumber;
  final int zoneId;
  final String? zoneName;
  final bool active;
  final String feedbackUrl;

  const CounterModel({
    required this.counterId,
    required this.counterName,
    required this.counterNumber,
    required this.zoneId,
    required this.zoneName,
    required this.active,
    required this.feedbackUrl,
  });

  factory CounterModel.fromJson(Map<String, dynamic> json) {
    return CounterModel(
      counterId: (json['counter_id'] as num).toInt(),
      counterName: json['counter_name'] as String? ?? '',
      counterNumber: (json['counter_number'] as num?)?.toInt() ?? 0,
      zoneId: (json['zone_id'] as num?)?.toInt() ?? 0,
      zoneName: json['zone_name'] as String?,
      active: json['active'] as bool? ?? false,
      feedbackUrl: json['feedback_url'] as String? ?? '',
    );
  }

  /// Whether [query] matches this counter's name, number, or zone
  /// (case-insensitive) -- used by the search field on the Counter
  /// Feedback list screen.
  bool matches(String query) {
    if (query.trim().isEmpty) return true;
    final q = query.trim().toLowerCase();
    return counterName.toLowerCase().contains(q) ||
        counterNumber.toString().contains(q) ||
        (zoneName?.toLowerCase().contains(q) ?? false);
  }
}
