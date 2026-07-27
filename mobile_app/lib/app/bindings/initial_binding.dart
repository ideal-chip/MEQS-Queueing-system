import 'package:get/get.dart';

import '../core/constants/app_defaults.dart';
import '../data/providers/api_provider.dart';
import '../data/repositories/counters_repository.dart';
import '../data/repositories/feedback_repository.dart';
import '../data/repositories/settings_repository.dart';
import '../presentation/controllers/counters_list_controller.dart';
import '../presentation/controllers/general_feedback_controller.dart';
import '../presentation/controllers/settings_controller.dart';

/// Wires the whole dependency graph once at app start: data layer
/// (repositories/providers) first, then the controllers that depend on
/// them. [CounterFeedbackController] is deliberately NOT put here -- it's
/// created per-counter when the user taps one (see
/// CountersListScreen._CounterTile.onTap), since its data is specific to
/// whichever counter was tapped.
class InitialBinding extends Bindings {
  @override
  void dependencies() {
    // -- data layer --
    Get.put(SettingsRepository(), permanent: true);
    Get.put(
      ApiProvider(settings: Get.find<SettingsRepository>()),
      permanent: true,
    );
    Get.put(FeedbackRepository(api: Get.find<ApiProvider>()), permanent: true);
    Get.put(CountersRepository(api: Get.find<ApiProvider>()), permanent: true);

    // -- app-wide controllers --
    Get.put(
      SettingsController(repository: Get.find<SettingsRepository>()),
      permanent: true,
    );

    // -- home screen --
    Get.put(
      GeneralFeedbackController(
        repository: Get.find<FeedbackRepository>(),
        settings: Get.find<SettingsController>(),
      ),
      permanent: true,
    );

    // Per-counter feedback is off (AppDefaults.counterFeedbackEnabled), so its
    // controller is not created and the app makes no counters request at
    // startup. Kept wired up behind the flag so re-enabling is a one-line change.
    if (AppDefaults.counterFeedbackEnabled) {
      Get.put(
        CountersListController(repository: Get.find<CountersRepository>()),
        permanent: true,
      );
    }
  }
}
