import 'add_inventory_screen.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/product_model.dart';

class InventoryScreen extends StatefulWidget {
  @override
  _InventoryScreenState createState() => _InventoryScreenState();
}

class _InventoryScreenState extends State<InventoryScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<Product> _products = [];

  @override
  void initState() {
    super.initState();
    _fetchInventory();
  }

  Future<void> _fetchInventory() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('/inventory');
      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(response.body);
        final List<dynamic> data = responseData['data'] ?? [];
        setState(() {
          _products = data.map((json) => Product.fromJson(json)).toList();
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load inventory')));
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Inventory'), actions: [IconButton(icon: Icon(Icons.add), onPressed: () async { final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => AddInventoryScreen())); if(result == true) _fetchInventory(); })]),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: _products.length,
              itemBuilder: (context, index) {
                final product = _products[index];
                return Card(
                  margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: product.stock > 10 ? Colors.blue.shade100 : Colors.red.shade100,
                      child: Icon(Icons.inventory_2, color: product.stock > 10 ? Colors.blue : Colors.red),
                    ),
                    title: Text(product.name, style: TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Text('SKU: ${product.sku ?? 'N/A'} | Price: ₹${product.price}'),
                    trailing: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text('${product.stock}', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        Text('in stock', style: TextStyle(fontSize: 12, color: Colors.grey)),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}
