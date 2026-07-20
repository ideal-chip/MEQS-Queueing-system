import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/api_exception.dart';
import '../repositories/settings_repository.dart';

/// Thin wrapper around the iDEAL-Q REST API v1 (see
/// beaa/api/v1/index.php). Every response is the uniform envelope
/// {success, data, meta, error}; this unwraps it and throws
/// [ApiException] on any success:false or transport failure, so
/// repositories/controllers only ever deal with plain data or a caught
/// exception.
class ApiProvider {
  final SettingsRepository settings;
  final http.Client _client;

  ApiProvider({required this.settings, http.Client? client})
      : _client = client ?? http.Client();

  String get _baseUrl {
    final url = settings.apiBaseUrl.trim();
    return url.endsWith('/') ? url.substring(0, url.length - 1) : url;
  }

  Future<dynamic> get(String path, {Map<String, String>? query}) async {
    final uri = Uri.parse('$_baseUrl$path').replace(queryParameters: query);
    try {
      final response = await _client.get(uri).timeout(const Duration(seconds: 15));
      return _unwrap(response);
    } on ApiException {
      rethrow;
    } catch (e) {
      throw ApiException.network('Could not reach the server: $e');
    }
  }

  Future<dynamic> post(String path, Map<String, dynamic> body) async {
    final uri = Uri.parse('$_baseUrl$path');
    try {
      final response = await _client
          .post(uri, headers: {'Content-Type': 'application/json'}, body: jsonEncode(body))
          .timeout(const Duration(seconds: 15));
      return _unwrap(response);
    } on ApiException {
      rethrow;
    } catch (e) {
      throw ApiException.network('Could not reach the server: $e');
    }
  }

  dynamic _unwrap(http.Response response) {
    Map<String, dynamic> envelope;
    try {
      envelope = jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {
      throw ApiException(
        statusCode: response.statusCode,
        code: 'invalid_response',
        message: 'The server sent an unexpected response (HTTP ${response.statusCode}).',
      );
    }

    if (envelope['success'] == true) {
      return envelope['data'];
    }

    final error = envelope['error'] as Map<String, dynamic>?;
    throw ApiException(
      statusCode: response.statusCode,
      code: error?['code'] as String? ?? 'unknown_error',
      message: error?['message'] as String? ?? 'Something went wrong.',
    );
  }
}
