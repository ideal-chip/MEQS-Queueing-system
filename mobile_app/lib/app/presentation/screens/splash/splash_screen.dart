import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:get/get.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_palette.dart';
import '../../../core/widgets/app_widgets.dart';
import '../root/root_screen.dart';

/// A short branded intro: the iDEAL-Q logo inside a pulsing badge over the
/// animated particle background, then a fade into the app shell.
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 2200), () {
      if (!mounted) return;
      Get.off(
        () => const RootScreen(),
        transition: Transition.fadeIn,
        duration: const Duration(milliseconds: 500),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: AnimatedBackground(
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              PulseBadge(
                size: 128,
                child: const BrandLogo(height: 64),
              ),
              SizedBox(height: 36.h),
              SlideFadeIn(
                delay: const Duration(milliseconds: 300),
                child: Text(
                  'app_name'.tr,
                  style: TextStyle(
                    fontSize: 26.sp,
                    fontWeight: FontWeight.w800,
                    color: context.tones.fg,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
              SizedBox(height: 10.h),
              SlideFadeIn(
                delay: const Duration(milliseconds: 500),
                child: Text(
                  'app_tagline'.tr,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 14.sp,
                    color: context.tones.fg2,
                  ),
                ),
              ),
              SizedBox(height: 40.h),
              SlideFadeIn(
                delay: const Duration(milliseconds: 700),
                child: SizedBox(
                  width: 26.r,
                  height: 26.r,
                  child: const CircularProgressIndicator(
                    strokeWidth: 2.4,
                    valueColor: AlwaysStoppedAnimation(AppColors.blue),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
