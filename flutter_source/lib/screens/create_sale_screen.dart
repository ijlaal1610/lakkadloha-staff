import 'package:flutter/material.dart';
import '../services/api_service.dart';

class CreateSaleScreen extends StatefulWidget {
  @override
  _CreateSaleScreenState createState() => _CreateSaleScreenState();
}

class _CreateSaleScreenState extends State<CreateSaleScreen> {
  final _formKey = GlobalKey<FormState>();
  final ApiService _apiService = ApiService();
  
  double _totalAmount = 0.0;
  String _paymentMethod = 'cash';
  bool _isLoading = false;

  void _submit() async {
    if (!_formKey.currentState!.validate()) return;
    _formKey.currentState!.save();
    
    setState(() => _isLoading = true);
    try {
      // Based on typical POS setup, simplify to total amount for now
      final response = await _apiService.post('/sales', {
        'total_amount': _totalAmount,
        'payment_method': _paymentMethod,
        'items': [] // Add item selection logic later if needed
      });

      if (response.statusCode == 201 || response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Sale recorded successfully!')));
        Navigator.pop(context, true); // Return true to refresh list
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to record sale')));
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error recording sale')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Record Sale')),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                decoration: InputDecoration(
                  labelText: 'Total Amount (₹)', 
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.currency_rupee)
                ),
                keyboardType: TextInputType.numberWithOptions(decimal: true),
                validator: (val) => val!.isEmpty ? 'Required' : null,
                onSaved: (val) => _totalAmount = double.parse(val!),
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              SizedBox(height: 24),
              DropdownButtonFormField<String>(
                value: _paymentMethod,
                decoration: InputDecoration(labelText: 'Payment Method', border: OutlineInputBorder()),
                items: [
                  DropdownMenuItem(value: 'cash', child: Text('Cash')),
                  DropdownMenuItem(value: 'upi', child: Text('UPI / Online')),
                  DropdownMenuItem(value: 'card', child: Text('Credit/Debit Card')),
                ],
                onChanged: (val) => setState(() => _paymentMethod = val!),
              ),
              Spacer(),
              _isLoading 
                ? CircularProgressIndicator()
                : ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      minimumSize: Size(double.infinity, 50),
                      backgroundColor: Colors.green,
                      foregroundColor: Colors.white,
                    ),
                    onPressed: _submit,
                    child: Text('Complete Sale', style: TextStyle(fontSize: 18)),
                  )
            ],
          ),
        ),
      ),
    );
  }
}
