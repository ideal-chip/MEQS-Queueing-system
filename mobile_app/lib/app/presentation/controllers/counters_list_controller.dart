import 'package:get/get.dart';

import '../../data/models/api_exception.dart';
import '../../data/models/counter_model.dart';
import '../../data/repositories/counters_repository.dart';

enum CountersListStatus { loading, ready, error }

/// Drives the Counter Feedback tab's list screen: loads every counter from
/// GET /counters?feedback_enabled=1 and filters it live as the user types
/// in the search field (by name, number, or zone).
class CountersListController extends GetxController {
  final CountersRepository repository;

  CountersListController({required this.repository});

  final status = CountersListStatus.loading.obs;
  final counters = <CounterModel>[].obs;
  final searchQuery = ''.obs;
  final errorMessage = ''.obs;

  List<CounterModel> get filteredCounters =>
      counters.where((c) => c.matches(searchQuery.value)).toList();

  @override
  void onInit() {
    super.onInit();
    loadCounters();
  }

  Future<void> loadCounters() async {
    status.value = CountersListStatus.loading;
    try {
      final result = await repository.getCounters();
      counters.assignAll(result);
      status.value = CountersListStatus.ready;
    } on ApiException catch (e) {
      errorMessage.value = e.message;
      status.value = CountersListStatus.error;
    }
  }

  void updateSearch(String query) => searchQuery.value = query;
}
