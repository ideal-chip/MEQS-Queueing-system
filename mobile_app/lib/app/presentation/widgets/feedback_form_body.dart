import 'package:flutter/material.dart';

import '../../data/models/feedback_question_model.dart';
import '../controllers/feedback_form_status.dart';
import 'star_rating_widget.dart';

/// The question list + submit button + "thank you" state, shared by both
/// the General Feedback and Counter Feedback screens so the two stay
/// visually and behaviorally identical (same questions, same stars, same
/// flow) -- only the data source differs between them.
class FeedbackFormBody extends StatelessWidget {
  final List<FeedbackQuestionModel> questions;
  final FeedbackFormStatus status;
  final String errorMessage;
  final bool allRated;
  final double averageScore;
  final void Function(int index, int value) onRatingChanged;
  final VoidCallback onSubmit;
  final VoidCallback onReset;
  final VoidCallback onRetry;

  const FeedbackFormBody({
    super.key,
    required this.questions,
    required this.status,
    required this.errorMessage,
    required this.allRated,
    required this.averageScore,
    required this.onRatingChanged,
    required this.onSubmit,
    required this.onReset,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    switch (status) {
      case FeedbackFormStatus.loading:
        return const Center(child: CircularProgressIndicator());

      case FeedbackFormStatus.error:
        return _ErrorView(message: errorMessage, onRetry: onRetry);

      case FeedbackFormStatus.submitted:
        return _ThankYouView(onReset: onReset);

      case FeedbackFormStatus.ready:
      case FeedbackFormStatus.submitting:
        return _QuestionsView(
          questions: questions,
          allRated: allRated,
          submitting: status == FeedbackFormStatus.submitting,
          onRatingChanged: onRatingChanged,
          onSubmit: onSubmit,
        );
    }
  }
}

class _QuestionsView extends StatelessWidget {
  final List<FeedbackQuestionModel> questions;
  final bool allRated;
  final bool submitting;
  final void Function(int, int) onRatingChanged;
  final VoidCallback onSubmit;

  const _QuestionsView({
    required this.questions,
    required this.allRated,
    required this.submitting,
    required this.onRatingChanged,
    required this.onSubmit,
  });

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
      children: [
        for (int i = 0; i < questions.length; i++)
          Card(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    questions[i].label,
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 10),
                  StarRatingWidget(
                    value: questions[i].rating,
                    onChanged: (v) => onRatingChanged(i, v),
                  ),
                ],
              ),
            ),
          ),
        const SizedBox(height: 12),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: allRated && !submitting ? onSubmit : null,
            child: submitting
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white),
                  )
                : const Text('Submit'),
          ),
        ),
        if (!allRated && questions.isNotEmpty) ...[
          const SizedBox(height: 10),
          const Text(
            'Please rate all questions before submitting.',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.grey),
          ),
        ],
      ],
    );
  }
}

class _ThankYouView extends StatelessWidget {
  final VoidCallback onReset;

  const _ThankYouView({required this.onReset});

  @override
  Widget build(BuildContext context) {
    final accent = Theme.of(context).colorScheme.secondary;
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.check_circle_rounded, size: 84, color: accent),
            const SizedBox(height: 20),
            const Text(
              'Thank you!',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            const Text(
              'Your feedback has been submitted.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey),
            ),
            const SizedBox(height: 24),
            OutlinedButton(onPressed: onReset, child: const Text('Rate again')),
          ],
        ),
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const _ErrorView({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.wifi_off_rounded, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              message.isEmpty ? 'Something went wrong.' : message,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.grey),
            ),
            const SizedBox(height: 20),
            ElevatedButton(onPressed: onRetry, child: const Text('Retry')),
          ],
        ),
      ),
    );
  }
}
