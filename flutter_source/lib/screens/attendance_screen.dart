import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/attendance_model.dart';

class AttendanceScreen extends StatefulWidget {
  @override
  _AttendanceScreenState createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<Attendance> _attendanceRecords = [];
  bool _isCheckedInToday = false;

  @override
  void initState() {
    super.initState();
    _fetchAttendance();
  }

  Future<void> _fetchAttendance() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('/attendance');
      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(response.body);
        final List<dynamic> data = responseData['data'] ?? []; 
        
        setState(() {
          _attendanceRecords = data.map((json) => Attendance.fromJson(json)).toList();
          
          if (_attendanceRecords.isNotEmpty) {
            final latest = _attendanceRecords.first;
            _isCheckedInToday = latest.checkInTime != null && latest.checkOutTime == null;
          }
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load attendance')));
    }
    setState(() => _isLoading = false);
  }

  Future<void> _handleCheckInOut() async {
    setState(() => _isLoading = true);
    final endpoint = _isCheckedInToday ? '/attendance/check-out' : '/attendance/check-in';
    
    try {
      final response = await _apiService.post(endpoint, {});
      if (response.statusCode == 200 || response.statusCode == 201) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(_isCheckedInToday ? 'Checked Out Successfully' : 'Checked In Successfully')),
        );
        await _fetchAttendance();
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Action failed. Try again.')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Attendance')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: ElevatedButton.icon(
                    onPressed: _handleCheckInOut,
                    icon: Icon(_isCheckedInToday ? Icons.logout : Icons.login),
                    label: Text(_isCheckedInToday ? 'Check Out Now' : 'Check In Now', style: TextStyle(fontSize: 18)),
                    style: ElevatedButton.styleFrom(
                      minimumSize: Size(double.infinity, 60),
                      backgroundColor: _isCheckedInToday ? Colors.orange : Colors.green,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ),
                Divider(),
                Expanded(
                  child: ListView.builder(
                    itemCount: _attendanceRecords.length,
                    itemBuilder: (context, index) {
                      final record = _attendanceRecords[index];
                      return ListTile(
                        leading: CircleAvatar(
                          backgroundColor: record.status == 'Present' ? Colors.green.shade100 : Colors.red.shade100,
                          child: Icon(Icons.calendar_today, color: record.status == 'Present' ? Colors.green : Colors.red),
                        ),
                        title: Text(record.date),
                        subtitle: Text('In: ${record.checkInTime ?? '--:--'} | Out: ${record.checkOutTime ?? '--:--'}'),
                        trailing: Text(record.status, style: TextStyle(fontWeight: FontWeight.bold)),
                      );
                    },
                  ),
                ),
              ],
            ),
    );
  }
}
