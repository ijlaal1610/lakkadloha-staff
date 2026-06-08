class Attendance {
  final int id;
  final String date;
  final String? checkInTime;
  final String? checkOutTime;
  final String status;

  Attendance({
    required this.id,
    required this.date,
    this.checkInTime,
    this.checkOutTime,
    required this.status,
  });

  factory Attendance.fromJson(Map<String, dynamic> json) {
    return Attendance(
      id: json['id'] ?? 0,
      date: json['date'] ?? '',
      checkInTime: json['check_in_time'],
      checkOutTime: json['check_out_time'],
      status: json['status'] ?? 'Unknown',
    );
  }
}
