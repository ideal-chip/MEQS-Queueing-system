import 'package:get/get.dart';

/// Tracks which bottom-nav tab is active: 0 = General Feedback,
/// 1 = Counter Feedback, 2 = Settings.
class NavController extends GetxController {
  final currentIndex = 0.obs;

  void changeTab(int index) => currentIndex.value = index;
}
