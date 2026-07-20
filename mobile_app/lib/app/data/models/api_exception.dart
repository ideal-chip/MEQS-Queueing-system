/// Thrown by [ApiProvider] whenever the API responds with success:false
/// (or the request fails outright), carrying the server's error code/message
/// when available so the UI can show something meaningful instead of a raw
/// stack trace.
class ApiException implements Exception {
  final int statusCode;
  final String code;
  final String message;

  const ApiException({
    required this.statusCode,
    required this.code,
    required this.message,
  });

  factory ApiException.network(String message) => ApiException(
        statusCode: 0,
        code: 'network_error',
        message: message,
      );

  @override
  String toString() => message;
}
