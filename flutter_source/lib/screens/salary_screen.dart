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
  Map<String, dynamic> _summary = {};

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() => _isLoading = true);
    try {
      // Fetch both Summary and List concurrently
      final responses = await Future.wait([
        _apiService.get('/salary/summary'),
        _apiService.get('/salary')
      ]);

      if (responses[0].statusCode == 200) {
        _summary = jsonDecode(responses[0].body);
      }
      
      if (responses[1].statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(responses[1].body);
        final List<dynamic> data = responseData['data'] ?? [];
        _salaries = data.map((json) => Salary.fromJson(json)).toList();
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load salary data')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Salary & Advances')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Summary Card
                Container(
                  padding: EdgeInsets.all(16),
                  color: Colors.brown.shade50,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _buildSummaryStat('Total Paid', _summary['total_paid'] ?? 0, Colors.green),
                      _buildSummaryStat('Pending', _summary['pending_amount'] ?? 0, Colors.orange),
                      _buildSummaryStat('Advances', _summary['total_advances'] ?? 0, Colors.blue),
                    ],
                  ),
                ),
                Divider(height: 1, thickness: 1),
                
                // History List
                Expanded(
                  child: ListView.builder(
                    itemCount: _salaries.length,
                    itemBuilder: (context, index) {
                      final salary = _salaries[index];
                      return Card(
                        margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: salary.status.toLowerCase() == 'paid' ? Colors.green.shade100 : Colors.orange.shade100,
                            child: Icon(Icons.account_balance_wallet, color: salary.status.toLowerCase() == 'paid' ? Colors.green : Colors.orange),
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
                ),
              ],
            ),
    );
  }

  Widget _buildSummaryStat(String label, dynamic amount, Color color) {
    return Column(
      children: [
        Text(label, style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.w600)),
        SizedBox(height: 4),
        Text('₹$amount', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
      ],
    );
  }
}
