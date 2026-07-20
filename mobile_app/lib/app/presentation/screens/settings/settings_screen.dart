import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../controllers/settings_controller.dart';

/// Lets the user reconfigure the app without a rebuild: the API base URL
/// (so this same app can point at any deployment, not just the demo
/// machine), the three theme colors, the three screen titles, and the
/// feedback-form language. Everything here is persisted via GetStorage and
/// takes effect immediately -- no login is required for any of this.
class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final settings = Get.find<SettingsController>();

    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          const _SectionHeader('API'),
          _TextSetting(
            label: 'API base URL',
            hint: 'http://192.168.1.41:8000/beaa/api/v1',
            initial: settings.apiBaseUrl.value,
            onSaved: settings.updateApiBaseUrl,
          ),
          const SizedBox(height: 24),
          const _SectionHeader('Titles'),
          _TextSetting(
            label: 'App title',
            initial: settings.appTitle.value,
            onSaved: settings.updateAppTitle,
          ),
          _TextSetting(
            label: 'General Feedback title',
            initial: settings.generalFeedbackTitle.value,
            onSaved: settings.updateGeneralFeedbackTitle,
          ),
          _TextSetting(
            label: 'Counter Feedback title',
            initial: settings.counterFeedbackTitle.value,
            onSaved: settings.updateCounterFeedbackTitle,
          ),
          const SizedBox(height: 24),
          const _SectionHeader('Language'),
          Obx(
            () => SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'en', label: Text('English')),
                ButtonSegment(value: 'ar', label: Text('عربي')),
              ],
              selected: {settings.languageCode.value},
              onSelectionChanged: (s) => settings.updateLanguageCode(s.first),
            ),
          ),
          const SizedBox(height: 24),
          const _SectionHeader('Colors'),
          Obx(
            () => Column(
              children: [
                _ColorSetting(
                  label: 'Primary (app bar)',
                  color: settings.primaryColor.value,
                  onChanged: settings.updatePrimaryColor,
                ),
                _ColorSetting(
                  label: 'Secondary (buttons, links)',
                  color: settings.secondaryColor.value,
                  onChanged: settings.updateSecondaryColor,
                ),
                _ColorSetting(
                  label: 'Accent (highlights)',
                  color: settings.accentColor.value,
                  onChanged: settings.updateAccentColor,
                ),
              ],
            ),
          ),
          const SizedBox(height: 32),
          OutlinedButton.icon(
            onPressed: () => _confirmReset(context, settings),
            icon: const Icon(Icons.restart_alt_rounded),
            label: const Text('Reset to defaults'),
          ),
        ],
      ),
    );
  }

  void _confirmReset(BuildContext context, SettingsController settings) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Reset settings?'),
        content: const Text('This restores the API URL, titles, colors, and language to their defaults.'),
        actions: [
          TextButton(onPressed: () => Get.back(), child: const Text('Cancel')),
          FilledButton(
            onPressed: () {
              settings.resetToDefaults();
              Get.back();
            },
            child: const Text('Reset'),
          ),
        ],
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;

  const _SectionHeader(this.title);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Text(
        title,
        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Colors.grey),
      ),
    );
  }
}

class _TextSetting extends StatefulWidget {
  final String label;
  final String? hint;
  final String initial;
  final ValueChanged<String> onSaved;

  const _TextSetting({
    required this.label,
    this.hint,
    required this.initial,
    required this.onSaved,
  });

  @override
  State<_TextSetting> createState() => _TextSettingState();
}

class _TextSettingState extends State<_TextSetting> {
  late final TextEditingController _controller = TextEditingController(text: widget.initial);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextField(
        controller: _controller,
        decoration: InputDecoration(labelText: widget.label, hintText: widget.hint),
        onSubmitted: widget.onSaved,
        onTapOutside: (_) => widget.onSaved(_controller.text),
      ),
    );
  }
}

class _ColorSetting extends StatelessWidget {
  final String label;
  final Color color;
  final ValueChanged<Color> onChanged;

  const _ColorSetting({required this.label, required this.color, required this.onChanged});

  static const List<Color> _palette = [
    Color(0xFF2C3E50),
    Color(0xFF3498DB),
    Color(0xFFF1C40F),
    Color(0xFF27AE60),
    Color(0xFFE74C3C),
    Color(0xFF9B59B6),
    Color(0xFF1ABC9C),
    Color(0xFF34495E),
  ];

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Row(
        children: [
          Expanded(child: Text(label)),
          Wrap(
            spacing: 8,
            children: _palette.map((c) {
              final selected = c.toARGB32() == color.toARGB32();
              return GestureDetector(
                onTap: () => onChanged(c),
                child: Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: c,
                    shape: BoxShape.circle,
                    border: selected ? Border.all(color: Colors.black87, width: 2.5) : null,
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}
