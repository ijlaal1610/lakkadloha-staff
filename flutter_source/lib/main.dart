import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'services/api_service.dart';
import 'screens/login_screen.dart';
import 'screens/attendance_screen.dart';
import 'screens/inventory_screen.dart';
import 'screens/sales_screen.dart';
import 'screens/salary_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/profile_screen.dart';

void main() {
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()..checkAuth()),
      ],
      child: MyApp(),
    ),
  );
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Lakkadloha Staff',
      theme: ThemeData(
        primarySwatch: Colors.brown, 
        useMaterial3: true,
        appBarTheme: AppBarTheme(
          backgroundColor: Colors.brown.shade800,
          foregroundColor: Colors.white,
        )
      ),
      home: Consumer<AuthProvider>(
        builder: (context, auth, _) {
          return auth.isAuthenticated ? DashboardScreen() : LoginScreen();
        },
      ),
    );
  }
}

class DashboardScreen extends StatefulWidget {
  @override
  _DashboardScreenState createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiService _apiService = ApiService();
  Map<String, dynamic> _stats = {};
  bool _isLoadingStats = true;

  @override
  void initState() {
    super.initState();
    _fetchDashboardStats();
  }

  Future<void> _fetchDashboardStats() async {
    try {
      final response = await _apiService.get('/dashboard');
      if (response.statusCode == 200) {
        setState(() {
          _stats = jsonDecode(response.body);
          _isLoadingStats = false;
        });
      }
    } catch (e) {
      setState(() => _isLoadingStats = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    
    return Scaffold(
      appBar: AppBar(
        title: Text('Lakkadloha Portal'),
        actions: [
          IconButton(
            icon: Icon(Icons.person),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ProfileScreen())),
            tooltip: 'Profile',
          ),
          IconButton(
            icon: Icon(Icons.logout),
            onPressed: () => auth.logout(),
            tooltip: 'Logout',
          )
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchDashboardStats,
        child: SingleChildScrollView(
          physics: AlwaysScrollableScrollPhysics(),
          padding: EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Welcome back, ${auth.user?['name'] ?? 'Staff'}!', 
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.brown.shade900)
              ),
              SizedBox(height: 20),
              
              _isLoadingStats 
                  ? Center(child: CircularProgressIndicator())
                  : Row(
                      children: [
                        Expanded(child: _buildStatCard('Today Sales', '₹${_stats['today_sales'] ?? '0.00'}', Colors.green)),
                        SizedBox(width: 16),
                        Expanded(child: _buildStatCard('Low Stock', '${_stats['low_stock_items'] ?? '0'}', Colors.red)),
                      ],
                    ),
              
              SizedBox(height: 24),
              Text('Quick Actions', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              SizedBox(height: 12),
              
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: NeverScrollableScrollPhysics(),
                crossAxisSpacing: 16.0,
                mainAxisSpacing: 16.0,
                children: [
                  _buildMenuCard(context, 'Attendance', Icons.access_time, '/attendance'),
                  _buildMenuCard(context, 'Inventory', Icons.inventory, '/inventory'),
                  _buildMenuCard(context, 'Sales', Icons.point_of_sale, '/sales'),
                  _buildMenuCard(context, 'Salary', Icons.attach_money, '/salary'),
                  _buildMenuCard(context, 'Notifications', Icons.notifications, '/notifications'),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, Color color) {
    return Container(
      padding: EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.5)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: TextStyle(color: color, fontWeight: FontWeight.w600)),
          SizedBox(height: 8),
          Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color.withOpacity(0.8))),
        ],
      ),
    );
  }

  Widget _buildMenuCard(BuildContext context, String title, IconData icon, String route) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () {
          Widget screen;
          switch (route) {
            case '/attendance': screen = AttendanceScreen(); break;
            case '/inventory': screen = InventoryScreen(); break;
            case '/sales': screen = SalesScreen(); break;
            case '/salary': screen = SalaryScreen(); break;
            case '/notifications': screen = NotificationsScreen(); break;
            default: return;
          }
          Navigator.push(context, MaterialPageRoute(builder: (context) => screen));
        },
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 48, color: Colors.brown.shade700),
            SizedBox(height: 12),
            Text(title, style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}
