import '../models/counter_model.dart';
import '../providers/api_provider.dart';

class CountersRepository {
  final ApiProvider api;

  CountersRepository({required this.api});

  /// GET /counters?feedback_enabled=1 -- every counter available for
  /// feedback, used by the searchable Counter Feedback list screen.
  Future<List<CounterModel>> getCounters() async {
    final data = await api.get('/counters', query: {'feedback_enabled': '1'});
    return (data as List<dynamic>)
        .map((c) => CounterModel.fromJson(c as Map<String, dynamic>))
        .toList();
  }
}
