import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AddInventoryScreen extends StatefulWidget {
  @override
  _AddInventoryScreenState createState() => _AddInventoryScreenState();
}

class _AddInventoryScreenState extends State<AddInventoryScreen> {
  final _formKey = GlobalKey<FormState>();
  final ApiService _apiService = ApiService();
  
  String _name = '';
  String _sku = '';
  int _stock = 0;
  double _price = 0.0;
  bool _isLoading = false;

  void _submit() async {
    if (!_formKey.currentState!.validate()) return;
    _formKey.currentState!.save();
    
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.post('/inventory', {
        'name': _name,
        'sku': _sku,
        'stock_quantity': _stock,
        'price': _price,
      });

      if (response.statusCode == 201 || response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Product added successfully!')));
        Navigator.pop(context, true); // Return true to refresh list
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to add product')));
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error adding product')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Add New Product')),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              TextFormField(
                decoration: InputDecoration(labelText: 'Product Name', border: OutlineInputBorder()),
                validator: (val) => val!.isEmpty ? 'Required' : null,
                onSaved: (val) => _name = val!,
              ),
              SizedBox(height: 16),
              TextFormField(
                decoration: InputDecoration(labelText: 'SKU (Optional)', border: OutlineInputBorder()),
                onSaved: (val) => _sku = val ?? '',
              ),
              SizedBox(height: 16),
              TextFormField(
                decoration: InputDecoration(labelText: 'Initial Stock', border: OutlineInputBorder()),
                keyboardType: TextInputType.number,
                validator: (val) => val!.isEmpty ? 'Required' : null,
                onSaved: (val) => _stock = int.parse(val!),
              ),
              SizedBox(height: 16),
              TextFormField(
                decoration: InputDecoration(labelText: 'Price (₹)', border: OutlineInputBorder()),
                keyboardType: TextInputType.numberWithOptions(decimal: true),
                validator: (val) => val!.isEmpty ? 'Required' : null,
                onSaved: (val) => _price = double.parse(val!),
              ),
              SizedBox(height: 24),
              _isLoading 
                ? Center(child: CircularProgressIndicator())
                : ElevatedButton(
                    style: ElevatedButton.styleFrom(minimumSize: Size(double.infinity, 50)),
                    onPressed: _submit,
                    child: Text('Save Product', style: TextStyle(fontSize: 16)),
                  )
            ],
          ),
        ),
      ),
    );
  }
}
