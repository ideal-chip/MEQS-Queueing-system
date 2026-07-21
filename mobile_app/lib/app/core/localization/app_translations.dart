import 'package:flutter/widgets.dart';
import 'package:get/get.dart';

/// English + Arabic strings for the whole UI. Question labels themselves are
/// NOT here — the backend returns them already localized for the requested
/// `language` (see FeedbackRepository), so switching language re-fetches the
/// form and the questions come back in the new language automatically.
///
/// Arabic is the default locale (see [AppTranslations.arabic]); English is the
/// fallback. Switch at runtime with `Get.updateLocale(...)`, which also flips
/// the whole app to RTL/LTR via the Global localization delegates.
class AppTranslations extends Translations {
  static const Locale english = Locale('en');
  static const Locale arabic = Locale('ar');
  static const List<Locale> supported = [arabic, english];

  @override
  Map<String, Map<String, String>> get keys => {
        'en': _en,
        'ar': _ar,
      };

  static const Map<String, String> _en = {
    'app_name': 'Ideal Feedback',
    'app_tagline': 'Your feedback improves our service',

    // Bottom navigation
    'nav_general': 'General',
    'nav_counters': 'Counters',
    'nav_settings': 'Settings',

    // Feedback screens
    'general_title': 'Rate your experience',
    'counter_title': 'Counter Feedback',
    'submit': 'Submit',
    'submitting': 'Submitting…',
    'rate_all_hint': 'Please rate all questions before submitting.',
    'thank_you': 'Thank you!',
    'feedback_submitted': 'Your feedback has been submitted.',
    'rate_again': 'Rate again',
    'average': 'Average',

    // Counters list
    'search_counters': 'Search by name, number or zone',
    'no_counters': 'No counters available',
    'no_counters_match': 'No counters match your search',

    // Errors
    'retry': 'Retry',
    'something_wrong': 'Something went wrong.',
    'could_not_submit': 'Could not submit',

    // Settings
    'settings_server': 'Server',
    'api_base_url': 'API base URL',
    'settings_titles': 'Titles',
    'app_title_label': 'App title',
    'general_title_label': 'General feedback title',
    'counter_title_label': 'Counter feedback title',
    'settings_language': 'Language',
    'settings_theme': 'Theme',
    'theme_dark': 'Dark',
    'theme_light': 'Light',
    'settings_appearance': 'Accent colour',
    'reset_defaults': 'Reset to defaults',
    'reset_done': 'Settings restored to defaults',
    'reset_title': 'Reset settings?',
    'reset_confirm_msg':
        'This restores the server URL, titles, colour and language to their defaults.',
    'cancel': 'Cancel',
    'saved': 'Saved',
  };

  static const Map<String, String> _ar = {
    'app_name': 'آيديل للتقييم',
    'app_tagline': 'رأيك يطوّر خدمتنا',

    // Bottom navigation
    'nav_general': 'عام',
    'nav_counters': 'الكاونترات',
    'nav_settings': 'الإعدادات',

    // Feedback screens
    'general_title': 'قيّم تجربتك',
    'counter_title': 'تقييم الكاونتر',
    'submit': 'إرسال',
    'submitting': 'جارٍ الإرسال…',
    'rate_all_hint': 'يرجى تقييم جميع الأسئلة قبل الإرسال.',
    'thank_you': 'شكراً لك!',
    'feedback_submitted': 'تم إرسال تقييمك بنجاح.',
    'rate_again': 'قيّم مرة أخرى',
    'average': 'المعدل',

    // Counters list
    'search_counters': 'ابحث بالاسم أو الرقم أو المنطقة',
    'no_counters': 'لا توجد كاونترات متاحة',
    'no_counters_match': 'لا توجد نتائج مطابقة',

    // Errors
    'retry': 'إعادة المحاولة',
    'something_wrong': 'حدث خطأ ما.',
    'could_not_submit': 'تعذّر الإرسال',

    // Settings
    'settings_server': 'الخادم',
    'api_base_url': 'رابط الخادم (API)',
    'settings_titles': 'العناوين',
    'app_title_label': 'اسم التطبيق',
    'general_title_label': 'عنوان التقييم العام',
    'counter_title_label': 'عنوان تقييم الكاونتر',
    'settings_language': 'اللغة',
    'settings_theme': 'المظهر',
    'theme_dark': 'داكن',
    'theme_light': 'فاتح',
    'settings_appearance': 'لون التمييز',
    'reset_defaults': 'استعادة الإعدادات الافتراضية',
    'reset_done': 'تمت استعادة الإعدادات الافتراضية',
    'reset_title': 'استعادة الإعدادات؟',
    'reset_confirm_msg':
        'سيؤدي هذا إلى استعادة رابط الخادم والعناوين واللون واللغة إلى الإعدادات الافتراضية.',
    'cancel': 'إلغاء',
    'saved': 'تم الحفظ',
  };
}
