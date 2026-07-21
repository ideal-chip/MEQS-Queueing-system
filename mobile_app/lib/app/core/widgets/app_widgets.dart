import 'dart:math';

import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../theme/app_colors.dart';
import '../theme/app_palette.dart';

// ─── Particle model ──────────────────────────────────────────────────────────

class _Particle {
  final double x;
  final double y;
  final double speed;
  final double radius;
  final double opacity;
  final Color color;
  final double phase;

  _Particle({
    required this.x,
    required this.y,
    required this.speed,
    required this.radius,
    required this.opacity,
    required this.color,
    required this.phase,
  });
}

class _ParticlePainter extends CustomPainter {
  final double animValue;
  final List<_Particle> particles;

  _ParticlePainter(this.animValue, this.particles);

  @override
  void paint(Canvas canvas, Size size) {
    for (final p in particles) {
      final dy = animValue * p.speed;
      final y = ((p.y - dy) % 1.0 + 1.0) % 1.0;
      final flicker = 0.5 + 0.5 * sin(animValue * 2 * pi * 3 + p.phase);
      final paint = Paint()
        ..color = p.color.withValues(alpha: p.opacity * flicker)
        ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 2);
      canvas.drawCircle(
        Offset(p.x * size.width, y * size.height),
        p.radius,
        paint,
      );
    }
  }

  @override
  bool shouldRepaint(_ParticlePainter old) => true;
}

/// Deep-navy gradient with drifting brand-blue/gold particles and two slow
/// radial glows — the signature backdrop from the reference UI.
class AnimatedBackground extends StatefulWidget {
  final Widget child;
  final bool showParticles;

  const AnimatedBackground({
    super.key,
    required this.child,
    this.showParticles = true,
  });

  @override
  State<AnimatedBackground> createState() => _AnimatedBackgroundState();
}

class _AnimatedBackgroundState extends State<AnimatedBackground>
    with TickerProviderStateMixin {
  late final AnimationController _glowCtrl;
  late final AnimationController _particleCtrl;
  late final List<_Particle> _particles;

  @override
  void initState() {
    super.initState();
    _glowCtrl = AnimationController(
      duration: const Duration(seconds: 6),
      vsync: this,
    )..repeat(reverse: true);
    _particleCtrl = AnimationController(
      duration: const Duration(seconds: 25),
      vsync: this,
    )..repeat();

    final rand = Random(99);
    _particles = List.generate(35, (i) {
      final isGold = i % 5 == 0;
      return _Particle(
        x: rand.nextDouble(),
        y: rand.nextDouble(),
        speed: 0.04 + rand.nextDouble() * 0.06,
        radius: 1.0 + rand.nextDouble() * 2.0,
        opacity: 0.15 + rand.nextDouble() * 0.35,
        color: isGold ? AppColors.gold : AppColors.blue,
        phase: rand.nextDouble() * 2 * pi,
      );
    });
  }

  @override
  void dispose() {
    _glowCtrl.dispose();
    _particleCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        DecoratedBox(
          decoration: BoxDecoration(gradient: context.tones.bg),
          child: const SizedBox.expand(),
        ),
        if (widget.showParticles) ...[
          AnimatedBuilder(
            animation: _glowCtrl,
            builder: (_, _) => DecoratedBox(
              decoration: BoxDecoration(
                gradient: RadialGradient(
                  center: const Alignment(-0.6, -0.6),
                  radius: 1.4,
                  colors: [
                    AppColors.blue.withValues(alpha: 0.10 * _glowCtrl.value),
                    Colors.transparent,
                  ],
                ),
              ),
              child: const SizedBox.expand(),
            ),
          ),
          AnimatedBuilder(
            animation: _glowCtrl,
            builder: (_, _) => DecoratedBox(
              decoration: BoxDecoration(
                gradient: RadialGradient(
                  center: const Alignment(0.8, 0.9),
                  radius: 1.2,
                  colors: [
                    AppColors.gold.withValues(alpha: 0.05 * (1 - _glowCtrl.value)),
                    Colors.transparent,
                  ],
                ),
              ),
              child: const SizedBox.expand(),
            ),
          ),
          AnimatedBuilder(
            animation: _particleCtrl,
            builder: (_, _) => CustomPaint(
              painter: _ParticlePainter(_particleCtrl.value, _particles),
              child: const SizedBox.expand(),
            ),
          ),
        ],
        widget.child,
      ],
    );
  }
}

// ─── Staggered slide + fade entrance ──────────────────────────────────────────

class SlideFadeIn extends StatefulWidget {
  final Widget child;
  final Duration delay;
  final double offsetY;

  const SlideFadeIn({
    super.key,
    required this.child,
    this.delay = Duration.zero,
    this.offsetY = 30,
  });

  @override
  State<SlideFadeIn> createState() => _SlideFadeInState();
}

class _SlideFadeInState extends State<SlideFadeIn>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _fade;
  late final Animation<Offset> _slide;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );
    _fade = CurvedAnimation(parent: _ctrl, curve: Curves.easeOut);
    _slide = Tween<Offset>(
      begin: Offset(0, widget.offsetY / 100),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOut));
    Future.delayed(widget.delay, () {
      if (mounted) _ctrl.forward();
    });
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FadeTransition(
      opacity: _fade,
      child: SlideTransition(position: _slide, child: widget.child),
    );
  }
}

// ─── Glowing gradient button ──────────────────────────────────────────────────

class GlowButton extends StatefulWidget {
  final String label;
  final IconData? icon;
  final VoidCallback? onTap;
  final bool isLoading;
  final Color color;

  const GlowButton({
    super.key,
    required this.label,
    this.icon,
    this.onTap,
    this.isLoading = false,
    this.color = AppColors.blue,
  });

  @override
  State<GlowButton> createState() => _GlowButtonState();
}

class _GlowButtonState extends State<GlowButton>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _glow;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      duration: const Duration(milliseconds: 1400),
      vsync: this,
    )..repeat(reverse: true);
    _glow = Tween<double>(begin: 6, end: 18)
        .animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final enabled = widget.onTap != null && !widget.isLoading;
    return AnimatedBuilder(
      animation: _glow,
      builder: (_, _) => GestureDetector(
        onTap: enabled ? widget.onTap : null,
        child: Opacity(
          opacity: enabled ? 1 : 0.5,
          child: Container(
            height: 54.h,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16.r),
              gradient: LinearGradient(
                colors: [widget.color.withValues(alpha: 0.9), widget.color],
              ),
              boxShadow: [
                BoxShadow(
                  color: widget.color.withValues(alpha: enabled ? 0.45 : 0),
                  blurRadius: _glow.value,
                ),
              ],
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (widget.isLoading)
                  SizedBox(
                    width: 22.r,
                    height: 22.r,
                    child: const CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation(Colors.white),
                    ),
                  )
                else ...[
                  if (widget.icon != null) ...[
                    Icon(widget.icon, color: Colors.white, size: 22.sp),
                    SizedBox(width: 10.w),
                  ],
                  Text(
                    widget.label,
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 16.sp,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ─── Translucent glass card ───────────────────────────────────────────────────

class GlassCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final Color? borderColor;
  final VoidCallback? onTap;

  const GlassCard({
    super.key,
    required this.child,
    this.padding,
    this.borderColor,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final tones = context.tones;
    final card = Container(
      padding: padding ?? EdgeInsets.all(16.w),
      decoration: BoxDecoration(
        color: tones.card,
        borderRadius: BorderRadius.circular(18.r),
        border: Border.all(color: borderColor ?? tones.line, width: 1),
        boxShadow: [
          BoxShadow(
            color: AppColors.blue.withValues(alpha: 0.06),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: child,
    );
    if (onTap == null) return card;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(18.r),
        onTap: onTap,
        child: card,
      ),
    );
  }
}

// ─── Pulsing badge (wraps the logo on splash/headers) ─────────────────────────

class PulseBadge extends StatefulWidget {
  final Widget child;
  final double size;
  final Color glowColor;

  const PulseBadge({
    super.key,
    required this.child,
    this.size = 120,
    this.glowColor = AppColors.blue,
  });

  @override
  State<PulseBadge> createState() => _PulseBadgeState();
}

class _PulseBadgeState extends State<PulseBadge>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _scale;
  late final Animation<double> _opacity;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      duration: const Duration(milliseconds: 1800),
      vsync: this,
    )..repeat();
    _scale = Tween<double>(begin: 1.0, end: 1.5)
        .animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOut));
    _opacity = Tween<double>(begin: 0.5, end: 0.0)
        .animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final s = widget.size.r;
    return SizedBox(
      width: s * 1.6,
      height: s * 1.6,
      child: Stack(
        alignment: Alignment.center,
        children: [
          AnimatedBuilder(
            animation: _ctrl,
            builder: (_, _) => Transform.scale(
              scale: _scale.value,
              child: Container(
                width: s,
                height: s,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: widget.glowColor.withValues(alpha: _opacity.value),
                    width: 2,
                  ),
                ),
              ),
            ),
          ),
          Container(
            width: s + 16,
            height: s + 16,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: widget.glowColor.withValues(alpha: 0.25),
                  blurRadius: 24,
                  spreadRadius: 4,
                ),
              ],
            ),
          ),
          widget.child,
        ],
      ),
    );
  }
}

/// The iDEAL-Q wordmark on a light rounded chip so its dark text stays
/// readable against the app's dark background.
class BrandLogo extends StatelessWidget {
  final double height;
  const BrandLogo({super.key, this.height = 56});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: height.h,
      padding: EdgeInsets.symmetric(horizontal: 14.w, vertical: 6.h),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16.r),
        boxShadow: [
          BoxShadow(
            color: AppColors.blue.withValues(alpha: 0.35),
            blurRadius: 24,
            spreadRadius: 1,
          ),
        ],
      ),
      child: Image.asset('assets/images/logo.png', fit: BoxFit.contain),
    );
  }
}
