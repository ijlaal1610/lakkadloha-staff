import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();

  bool _isAuthenticated = false;
  Map<String, dynamic>? _user;

  bool get isAuthenticated => _isAuthenticated;
  Map<String, dynamic>? get user => _user;

  Future<void> checkAuth() async {
    const storage = FlutterSecureStorage();

    String? token = await storage.read(key: 'auth_token');

    if (token != null) {
      await fetchUser();
    } else {
      _isAuthenticated = false;
      notifyListeners();
    }
  }

  Future<bool> login(String email, String password) async {
    try {
      final response = await _apiService.post(
        '/auth/login',
        {
          'email': email,
          'password': password,
          'device_name': 'LakkadLoha Staff App',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        await _apiService.saveToken(data['token']);

        await fetchUser();

        return true;
      }

      try {
        final error = jsonDecode(response.body);
        debugPrint('Login failed: $error');
      } catch (_) {
        debugPrint('Login failed: ${response.body}');
      }

      return false;
    } catch (e) {
      debugPrint('Login exception: $e');
      return false;
    }
  }

  Future<void> fetchUser() async {
    try {
      final response = await _apiService.get('/auth/me');

      if (response.statusCode == 200) {
        _user = jsonDecode(response.body);
        _isAuthenticated = true;
      } else {
        _isAuthenticated = false;
        await _apiService.deleteToken();
      }
    } catch (e) {
      debugPrint('Fetch user exception: $e');
      _isAuthenticated = false;
      await _apiService.deleteToken();
    }

    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await _apiService.post('/auth/logout', {});
    } catch (_) {}

    await _apiService.deleteToken();

    _isAuthenticated = false;
    _user = null;

    notifyListeners();
  }
}