class Sale {
  final int id;
  final String? receiptNumber;
  final double totalAmount;
  final String date;

  Sale({
    required this.id,
    this.receiptNumber,
    required this.totalAmount,
    required this.date,
  });

  factory Sale.fromJson(Map<String, dynamic> json) {
    return Sale(
      id: json['id'] ?? 0,
      receiptNumber: json['receipt_no'],
      totalAmount: double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0.0,
      date: json['created_at'] ?? 'Unknown Date',
    );
  }
}
