import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/attendance_screen.dart';
import 'screens/inventory_screen.dart';
import 'screens/sales_screen.dart';
import 'screens/salary_screen.dart';
import 'screens/notifications_screen.dart';

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
      title: 'FlowTrack Staff',
      theme: ThemeData(
        primarySwatch: Colors.blue, 
        useMaterial3: true,
        appBarTheme: AppBarTheme(
          backgroundColor: Colors.blue.shade800,
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

class DashboardScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    
    return Scaffold(
      appBar: AppBar(
        title: Text('FlowTrack Dashboard'),
        actions: [
          IconButton(
            icon: Icon(Icons.logout),
            onPressed: () => auth.logout(),
            tooltip: 'Logout',
          )
        ],
      ),
      body: GridView.count(
        crossAxisCount: 2,
        padding: EdgeInsets.all(16.0),
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
            case '/attendance':
              screen = AttendanceScreen();
              break;
            case '/inventory':
              screen = InventoryScreen();
              break;
            case '/sales':
              screen = SalesScreen();
              break;
            case '/salary':
              screen = SalaryScreen();
              break;
            case '/notifications':
              screen = NotificationsScreen();
              break;
            default:
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Coming soon')));
              return;
          }
          Navigator.push(context, MaterialPageRoute(builder: (context) => screen));
        },
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 54, color: Colors.blue.shade700),
            SizedBox(height: 12),
            Text(title, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}
