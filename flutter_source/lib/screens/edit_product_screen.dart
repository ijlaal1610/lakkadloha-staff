import 'package:flutter/material.dart';
import '../models/product_model.dart';
import '../services/api_service.dart';

class EditProductScreen extends StatefulWidget {
  final Product product;
  
  EditProductScreen({required this.product});

  @override
  _EditProductScreenState createState() => _EditProductScreenState();
}

class _EditProductScreenState extends State<EditProductScreen> {
  final _formKey = GlobalKey<FormState>();
  final ApiService _apiService = ApiService();
  
  late String _name;
  late String _sku;
  late double _price;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _name = widget.product.name;
    _sku = widget.product.sku ?? '';
    _price = widget.product.price;
  }

  void _submit() async {
    if (!_formKey.currentState!.validate()) return;
    _formKey.currentState!.save();
    
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.post('/inventory/${widget.product.id}?_method=PUT', {
        'name': _name,
        'sku': _sku,
        'price': _price,
      });

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Product updated successfully!')));
        Navigator.pop(context, true); // Return true to trigger a refresh
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to update product')));
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error updating product')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Edit Product')),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              TextFormField(
                initialValue: _name,
                decoration: InputDecoration(labelText: 'Product Name', border: OutlineInputBorder()),
                validator: (val) => val!.isEmpty ? 'Required' : null,
                onSaved: (val) => _name = val!,
              ),
              SizedBox(height: 16),
              TextFormField(
                initialValue: _sku,
                decoration: InputDecoration(labelText: 'SKU (Optional)', border: OutlineInputBorder()),
                onSaved: (val) => _sku = val ?? '',
              ),
              SizedBox(height: 16),
              TextFormField(
                initialValue: _price.toString(),
                decoration: InputDecoration(labelText: 'Price (₹)', border: OutlineInputBorder()),
                keyboardType: TextInputType.numberWithOptions(decimal: true),
                validator: (val) => val!.isEmpty ? 'Required' : null,
                onSaved: (val) => _price = double.parse(val!),
              ),
              SizedBox(height: 24),
              _isLoading 
                ? Center(child: CircularProgressIndicator())
                : ElevatedButton(
                    style: ElevatedButton.styleFrom(minimumSize: Size(double.infinity, 50), backgroundColor: Colors.brown.shade700, foregroundColor: Colors.white),
                    onPressed: _submit,
                    child: Text('Update Product', style: TextStyle(fontSize: 16)),
                  )
            ],
          ),
        ),
      ),
    );
  }
}
