import 'dart:convert';
import 'package:flutter/material.dart';
import '../models/sale_model.dart';
import '../services/api_service.dart';

class SaleDetailScreen extends StatefulWidget {
  final Sale sale;
  
  SaleDetailScreen({required this.sale});

  @override
  _SaleDetailScreenState createState() => _SaleDetailScreenState();
}

class _SaleDetailScreenState extends State<SaleDetailScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  Map<String, dynamic>? _saleDetails;

  @override
  void initState() {
    super.initState();
    _fetchSaleDetails();
  }

  Future<void> _fetchSaleDetails() async {
    try {
      final response = await _apiService.get('/sales/${widget.sale.id}');
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          _saleDetails = data['data'] ?? data;
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load receipt details')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Receipt Details')),
      body: _isLoading 
          ? Center(child: CircularProgressIndicator())
          : Padding(
              padding: EdgeInsets.all(16.0),
              child: Card(
                elevation: 4,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: EdgeInsets.all(24.0),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.check_circle, color: Colors.green, size: 64),
                      SizedBox(height: 16),
                      Text('Lakkadloha Receipt', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                      Text('No: ${widget.sale.receiptNumber ?? '#${widget.sale.id}'}', style: TextStyle(color: Colors.grey)),
                      Text('Date: ${widget.sale.date.split('T')[0]}', style: TextStyle(color: Colors.grey)),
                      Divider(height: 32, thickness: 2),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Payment Method', style: TextStyle(fontSize: 16)),
                          Text((_saleDetails?['payment_method'] ?? 'N/A').toString().toUpperCase(), style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                        ],
                      ),
                      SizedBox(height: 16),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Total Amount', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                          Text('₹${widget.sale.totalAmount.toStringAsFixed(2)}', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.green)),
                        ],
                      ),
                      SizedBox(height: 32),
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(minimumSize: Size(double.infinity, 50)),
                        onPressed: () => Navigator.pop(context),
                        icon: Icon(Icons.arrow_back),
                        label: Text('Back to Sales'),
                      )
                    ],
                  ),
                ),
              ),
            ),
    );
  }
}
