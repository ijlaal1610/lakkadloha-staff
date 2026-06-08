class Salary {
  final int id;
  final String period;
  final double netSalary;
  final String status;
  final String date;

  Salary({
    required this.id,
    required this.period,
    required this.netSalary,
    required this.status,
    required this.date,
  });

  factory Salary.fromJson(Map<String, dynamic> json) {
    return Salary(
      id: json['id'] ?? 0,
      period: json['month'] ?? json['period'] ?? 'Unknown Period',
      netSalary: double.tryParse(json['net_salary']?.toString() ?? json['amount']?.toString() ?? '0') ?? 0.0,
      status: json['status'] ?? 'Pending',
      date: json['payment_date'] ?? json['created_at'] ?? 'Pending',
    );
  }
}
