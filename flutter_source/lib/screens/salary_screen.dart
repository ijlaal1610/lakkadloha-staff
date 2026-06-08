import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/salary_model.dart';

class SalaryScreen extends StatefulWidget {
  @override
  _SalaryScreenState createState() => _SalaryScreenState();
}

class _SalaryScreenState extends State<SalaryScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<Salary> _salaries = [];

  @override
  void initState() {
    super.initState();
    _fetchSalary();
  }

  Future<void> _fetchSalary() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('/salary');
      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(response.body);
        final List<dynamic> data = responseData['data'] ?? [];
        setState(() {
          _salaries = data.map((json) => Salary.fromJson(json)).toList();
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load salary data')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Salary Records')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: _salaries.length,
              itemBuilder: (context, index) {
                final salary = _salaries[index];
                return Card(
                  margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: salary.status.toLowerCase() == 'paid' ? Colors.green.shade100 : Colors.orange.shade100,
                      child: Icon(Icons.attach_money, color: salary.status.toLowerCase() == 'paid' ? Colors.green : Colors.orange),
                    ),
                    title: Text(salary.period, style: TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Text('Status: ${salary.status} | Date: ${salary.date.split('T')[0]}'),
                    trailing: Text('₹${salary.netSalary.toStringAsFixed(2)}', 
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)
                    ),
                  ),
                );
              },
            ),
    );
  }
}
