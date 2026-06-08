import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/notification_model.dart';

class NotificationsScreen extends StatefulWidget {
  @override
  _NotificationsScreenState createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<AppNotification> _notifications = [];

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
  }

  Future<void> _fetchNotifications() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('/notifications');
      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(response.body);
        final List<dynamic> data = responseData['notifications']['data'] ?? responseData['notifications'] ?? [];
        setState(() {
          _notifications = data.map((json) => AppNotification.fromJson(json)).toList();
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load notifications')));
    }
    setState(() => _isLoading = false);
  }

  Future<void> _markAsRead(String id) async {
    try {
      final response = await _apiService.post('/notifications/$id/read', {});
      if (response.statusCode == 200) {
        _fetchNotifications(); // Refresh the list
      }
    } catch (e) {
      // Handle error silently or show snackbar
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Notifications')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _notifications.isEmpty 
              ? Center(child: Text('No new notifications'))
              : ListView.builder(
                  itemCount: _notifications.length,
                  itemBuilder: (context, index) {
                    final note = _notifications[index];
                    return ListTile(
                      tileColor: note.isRead ? null : Colors.blue.shade50,
                      leading: Icon(
                        note.isRead ? Icons.notifications_none : Icons.notifications_active,
                        color: note.isRead ? Colors.grey : Colors.blue,
                      ),
                      title: Text(note.message, style: TextStyle(fontWeight: note.isRead ? FontWeight.normal : FontWeight.bold)),
                      subtitle: Text(note.date.split('T')[0]),
                      onTap: () {
                        if (!note.isRead) {
                          _markAsRead(note.id);
                        }
                      },
                    );
                  },
                ),
    );
  }
}
