import 'package:get/get.dart';

import '../data/providers/api_provider.dart';
import '../data/repositories/counters_repository.dart';
import '../data/repositories/feedback_repository.dart';
import '../data/repositories/settings_repository.dart';
import '../presentation/controllers/counters_list_controller.dart';
import '../presentation/controllers/general_feedback_controller.dart';
import '../presentation/controllers/nav_controller.dart';
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
    Get.put(SettingsController(repository: Get.find<SettingsRepository>()), permanent: true);
    Get.put(NavController(), permanent: true);

    // -- per-tab controllers (General + Counters list are always alive
    //    behind the bottom nav's IndexedStack, so they're permanent too) --
    Get.put(
      GeneralFeedbackController(
        repository: Get.find<FeedbackRepository>(),
        settings: Get.find<SettingsController>(),
      ),
      permanent: true,
    );
    Get.put(
      CountersListController(repository: Get.find<CountersRepository>()),
      permanent: true,
    );
  }
}
