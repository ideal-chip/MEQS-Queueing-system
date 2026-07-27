import 'package:flutter/widgets.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

/// The design canvas handed to flutter_screenutil's `ScreenUtilInit`.
///
/// This is the piece that used to be wrong. The app pinned a single portrait
/// phone canvas (430x932) for every device, and screenutil derives its two
/// scale factors straight from it:
///
///   scaleWidth  = screenWidth  / designSize.width     -> `.w`
///   scaleHeight = screenHeight / designSize.height    -> `.h`
///
/// On a tablet held horizontally (~1280x800 dp) that produced
/// scaleWidth ~= 2.98 and scaleHeight ~= 0.86: every horizontal gap tripled
/// while every vertical gap shrank, and `.sp` text (min of the two, with
/// minTextAdapt) shrank with it. Rows sized in `.w` — the 5-star rating row
/// above all — grew past their card and wrapped.
///
/// The fix is to keep the canvas' aspect ratio close to the device's, so the
/// two factors stay close to each other and a layout tuned in portrait keeps
/// its proportions in landscape. Same numbers, one per device class, simply
/// turned on their side when the device is.
class DesignCanvas {
  DesignCanvas._();

  /// Reference phone (iPhone 15 Pro Max class) — what the UI was drawn on.
  static const Size phonePortrait = Size(430, 932);
  static const Size phoneLandscape = Size(932, 430);

  /// Reference tablet: the Ministry's 10.95" panels report ~800x1280 dp.
  static const Size tabletPortrait = Size(800, 1280);
  static const Size tabletLandscape = Size(1280, 800);

  /// The Material breakpoint for "this is a tablet, not a large phone".
  static const double tabletShortestSide = 600;

  /// Pick the canvas matching [screenSize]'s device class and orientation.
  static Size of(Size screenSize) {
    final isTablet = screenSize.shortestSide >= tabletShortestSide;
    final isLandscape = screenSize.width > screenSize.height;

    if (isTablet) {
      return isLandscape ? tabletLandscape : tabletPortrait;
    }
    return isLandscape ? phoneLandscape : phonePortrait;
  }
}

/// Small helpers for laying out the same screens on a phone and on the
/// Ministry of Environment's 10.95" tablets. Used together with
/// flutter_screenutil (which handles typographic/spacing scaling); this adds
/// the layout-level decisions screenutil can't make on its own.
extension Responsive on BuildContext {
  Size get _size => MediaQuery.sizeOf(this);

  /// A tablet is anything whose shortest side is a tablet's worth of dp.
  bool get isTablet => _size.shortestSide >= DesignCanvas.tabletShortestSide;

  bool get isLandscape => _size.width > _size.height;

  /// Cap the reading column so a form doesn't stretch across a full 10.95"
  /// panel; phones stay edge-to-edge. Expressed in `.w` so it tracks the
  /// canvas above instead of being a fixed dp value that means something
  /// different in each orientation.
  double get contentMaxWidth {
    if (!isTablet) return double.infinity;
    return isLandscape ? 1040.w : 720.w;
  }

  /// Question / counter cards go two-up on a wide tablet, one-up on a phone.
  int get gridColumns => (isTablet && isLandscape) ? 2 : 1;
}
