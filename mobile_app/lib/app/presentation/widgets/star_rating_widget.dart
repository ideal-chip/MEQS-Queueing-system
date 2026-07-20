import 'package:flutter/material.dart';

import '../../core/constants/app_defaults.dart';

/// A row of 5 tappable stars, matching the web kiosk's jquery-bar-rating
/// "fontawesome-stars" theme: unrated = light grey outline, rated = gold
/// fill up to the selected value.
class StarRatingWidget extends StatelessWidget {
  final int value; // 0-5
  final ValueChanged<int> onChanged;
  final double size;

  const StarRatingWidget({
    super.key,
    required this.value,
    required this.onChanged,
    this.size = 32,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(5, (i) {
        final starIndex = i + 1;
        final filled = starIndex <= value;
        return InkWell(
          borderRadius: BorderRadius.circular(24),
          onTap: () => onChanged(starIndex),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 2),
            child: Icon(
              filled ? Icons.star_rounded : Icons.star_outline_rounded,
              size: size,
              color: filled ? AppDefaults.starColor : Colors.grey.shade300,
            ),
          ),
        );
      }),
    );
  }
}
