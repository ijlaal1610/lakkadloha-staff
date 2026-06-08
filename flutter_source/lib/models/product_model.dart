class Product {
  final int id;
  final String name;
  final String? sku;
  final int stock;
  final double price;

  Product({
    required this.id,
    required this.name,
    this.sku,
    required this.stock,
    required this.price,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'] ?? 0,
      name: json['name'] ?? 'Unknown Product',
      sku: json['sku'],
      stock: json['stock_quantity'] ?? json['stock'] ?? 0,
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
    );
  }
}
