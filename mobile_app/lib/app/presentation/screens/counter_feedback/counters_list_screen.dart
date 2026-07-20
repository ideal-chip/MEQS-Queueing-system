import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../data/models/counter_model.dart';
import '../../../data/repositories/feedback_repository.dart';
import '../../controllers/counter_feedback_controller.dart';
import '../../controllers/counters_list_controller.dart';
import '../../controllers/settings_controller.dart';
import 'counter_feedback_screen.dart';

/// Lists every counter available for feedback, with a live search field
/// (by name, number, or zone) at the top. Tapping a counter opens its own
/// feedback form -- the mobile equivalent of visiting
/// /beaa/feedback/{counter_id}/ on the web.
class CountersListScreen extends StatelessWidget {
  const CountersListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<CountersListController>();
    final settings = Get.find<SettingsController>();

    return Scaffold(
      appBar: AppBar(
        title: Obx(() => Text(settings.counterFeedbackTitle.value)),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: TextField(
              onChanged: controller.updateSearch,
              decoration: const InputDecoration(
                hintText: 'Search counters by name, number, or zone',
                prefixIcon: Icon(Icons.search_rounded),
              ),
            ),
          ),
          Expanded(
            child: Obx(() {
              switch (controller.status.value) {
                case CountersListStatus.loading:
                  return const Center(child: CircularProgressIndicator());
                case CountersListStatus.error:
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.wifi_off_rounded, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 16),
                          Text(
                            controller.errorMessage.value,
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: Colors.grey),
                          ),
                          const SizedBox(height: 20),
                          ElevatedButton(
                            onPressed: controller.loadCounters,
                            child: const Text('Retry'),
                          ),
                        ],
                      ),
                    ),
                  );
                case CountersListStatus.ready:
                  final counters = controller.filteredCounters;
                  if (counters.isEmpty) {
                    return const Center(child: Text('No counters found.'));
                  }
                  return RefreshIndicator(
                    onRefresh: controller.loadCounters,
                    child: ListView.builder(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                      itemCount: counters.length,
                      itemBuilder: (context, i) => _CounterTile(counter: counters[i]),
                    ),
                  );
              }
            }),
          ),
        ],
      ),
    );
  }
}

class _CounterTile extends StatelessWidget {
  final CounterModel counter;

  const _CounterTile({required this.counter});

  @override
  Widget build(BuildContext context) {
    final secondary = Theme.of(context).colorScheme.secondary;
    return Card(
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        leading: CircleAvatar(
          backgroundColor: secondary.withValues(alpha: 0.12),
          child: Text(
            counter.counterNumber.toString(),
            style: TextStyle(color: secondary, fontWeight: FontWeight.w700),
          ),
        ),
        title: Text(counter.counterName, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(counter.zoneName ?? ''),
        trailing: const Icon(Icons.chevron_right_rounded),
        onTap: () {
          // A fresh controller per counter -- delete any previous one first
          // so switching between counters never shows stale ratings.
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
          Get.to(() => const CounterFeedbackScreen());
        },
      ),
    );
  }
}
