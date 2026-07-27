import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_palette.dart';

/// A row of 5 tappable stars. Selected stars fill with the brand gold and
/// gain a soft glow, and pop in with a springy scale animation — matching the
/// lively feel of the reference UI.
class StarRatingWidget extends StatelessWidget {
  final int value; // 0-5
  final ValueChanged<int> onChanged;
  final double size;

  const StarRatingWidget({
    super.key,
    required this.value,
    required this.onChanged,
    this.size = 40,
  });

  @override
  Widget build(BuildContext context) {
    // The row is sized in `.w`/`.sp`, so on an unusually narrow card (a
    // two-up grid on a tablet, or a long question pushing the card down) five
    // stars could add up to more than the available width and spill out.
    // FittedBox scales the whole row down to fit instead, keeping it on one
    // line at every screen size; it is a no-op when there is room.
    return FittedBox(fit: BoxFit.scaleDown, child: _starsRow(context));
  }

  Widget _starsRow(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(5, (i) {
        final starIndex = i + 1;
        final filled = starIndex <= value;
        return GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTap: () => onChanged(starIndex),
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: 5.w, vertical: 4.h),
            child: AnimatedScale(
              scale: filled ? 1.0 : 0.82,
              duration: const Duration(milliseconds: 220),
              curve: Curves.easeOutBack,
              child: Icon(
                filled ? Icons.star_rounded : Icons.star_outline_rounded,
                size: size.sp,
                color: filled ? AppColors.gold : context.tones.fg3,
                shadows: filled
                    ? [const Shadow(color: AppColors.goldGlow, blurRadius: 16)]
                    : null,
              ),
            ),
          ),
        );
      }),
    );
  }
}
