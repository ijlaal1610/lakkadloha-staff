import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/sale_model.dart';

class SalesScreen extends StatefulWidget {
  @override
  _SalesScreenState createState() => _SalesScreenState();
}

class _SalesScreenState extends State<SalesScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<Sale> _sales = [];

  @override
  void initState() {
    super.initState();
    _fetchSales();
  }

  Future<void> _fetchSales() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('/sales');
      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(response.body);
        final List<dynamic> data = responseData['data'] ?? [];
        setState(() {
          _sales = data.map((json) => Sale.fromJson(json)).toList();
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load sales')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Recent Sales')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: _sales.length,
              itemBuilder: (context, index) {
                final sale = _sales[index];
                return ListTile(
                  leading: CircleAvatar(
                    backgroundColor: Colors.green.shade100,
                    child: Icon(Icons.receipt, color: Colors.green),
                  ),
                  title: Text('Receipt: ${sale.receiptNumber ?? '#${sale.id}'}'),
                  subtitle: Text('Date: ${sale.date.split('T')[0]}'),
                  trailing: Text('₹${sale.totalAmount.toStringAsFixed(2)}', 
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.green)
                  ),
                );
              },
            ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Create Sale coming soon!')));
        },
        child: Icon(Icons.add),
        tooltip: 'New Sale',
      ),
    );
  }
}
