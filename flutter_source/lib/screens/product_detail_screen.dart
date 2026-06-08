import 'edit_product_screen.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import '../models/product_model.dart';
import '../services/api_service.dart';

class ProductDetailScreen extends StatefulWidget {
  final Product product;
  
  ProductDetailScreen({required this.product});

  @override
  _ProductDetailScreenState createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = false;
  late int _currentStock;

  @override
  void initState() {
    super.initState();
    _currentStock = widget.product.stock;
  }

  void _addStock() {
    int quantityToAdd = 0;
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Add Stock'),
        content: TextField(
          keyboardType: TextInputType.number,
          decoration: InputDecoration(labelText: 'Quantity to Add', border: OutlineInputBorder()),
          onChanged: (val) => quantityToAdd = int.tryParse(val) ?? 0,
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              if (quantityToAdd <= 0) return;
              Navigator.pop(context);
              setState(() => _isLoading = true);
              try {
                final response = await _apiService.post('/inventory/${widget.product.id}/add-stock', {
                  'quantity': quantityToAdd
                });
                if (response.statusCode == 200) {
                  setState(() => _currentStock += quantityToAdd);
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Stock updated successfully!')));
                }
              } catch (e) {
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to update stock')));
              }
              setState(() => _isLoading = false);
            },
            child: Text('Add'),
          )
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Product Details'), actions: [IconButton(icon: Icon(Icons.edit), onPressed: () async { final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => EditProductScreen(product: widget.product))); if(result == true) Navigator.pop(context, true); })]),
      body: _isLoading 
          ? Center(child: CircularProgressIndicator())
          : Padding(
              padding: EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Card(
                    elevation: 4,
                    child: Padding(
                      padding: EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(widget.product.name, style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
                          SizedBox(height: 8),
                          Text('SKU: ${widget.product.sku ?? 'N/A'}', style: TextStyle(fontSize: 16, color: Colors.grey.shade700)),
                          Divider(height: 32),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('Price', style: TextStyle(color: Colors.grey)),
                                  Text('₹${widget.product.price}', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                                ],
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text('Current Stock', style: TextStyle(color: Colors.grey)),
                                  Text('$_currentStock', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _currentStock > 10 ? Colors.green : Colors.red)),
                                ],
                              )
                            ],
                          )
                        ],
                      ),
                    ),
                  ),
                  SizedBox(height: 24),
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(minimumSize: Size(double.infinity, 50), backgroundColor: Colors.brown.shade700, foregroundColor: Colors.white),
                    onPressed: _addStock,
                    icon: Icon(Icons.add_shopping_cart),
                    label: Text('Quick Add Stock', style: TextStyle(fontSize: 16)),
                  ),
                ],
              ),
            ),
    );
  }
}
