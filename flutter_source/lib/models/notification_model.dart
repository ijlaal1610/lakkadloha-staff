class AppNotification {
  final String id;
  final String message;
  final bool isRead;
  final String date;

  AppNotification({
    required this.id,
    required this.message,
    required this.isRead,
    required this.date,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    // Laravel typically stores notification data in a JSON column called 'data'
    final data = json['data'] ?? {};
    return AppNotification(
      id: json['id'].toString(),
      message: data['message'] ?? data['title'] ?? 'New notification',
      isRead: json['read_at'] != null,
      date: json['created_at'] ?? '',
    );
  }
}
