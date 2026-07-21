import 'package:flutter/widgets.dart';

/// Small helpers for laying out the same screens on a phone and on the
/// Ministry of Environment's 10.95" tablets. Used together with
/// flutter_screenutil (which handles typographic/spacing scaling); this adds
/// the layout-level decisions screenutil can't make on its own.
extension Responsive on BuildContext {
  Size get _size => MediaQuery.sizeOf(this);

  /// A tablet is anything whose shortest side is a tablet's worth of dp.
  bool get isTablet => _size.shortestSide >= 600;

  bool get isLandscape => _size.width > _size.height;

  /// Cap the reading column on big screens so a form doesn't stretch to a
  /// full 10.95" panel; phones stay edge-to-edge.
  double get contentMaxWidth => isTablet ? 720 : double.infinity;

  /// Question / counter cards go two-up on a wide tablet, one-up on a phone.
  int get gridColumns => (isTablet && isLandscape) ? 2 : 1;
}
