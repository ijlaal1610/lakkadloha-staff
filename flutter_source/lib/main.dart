import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/attendance_screen.dart';
import 'screens/inventory_screen.dart';
import 'screens/sales_screen.dart';

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
      title: 'Staff App',
      theme: ThemeData(primarySwatch: Colors.blue, useMaterial3: true),
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
        title: Text('Dashboard'),
        actions: [
          IconButton(
            icon: Icon(Icons.logout),
            onPressed: () => auth.logout(),
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
      elevation: 2,
      child: InkWell(
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
            default:
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Navigating to $title (Coming soon)')));
              return;
          }
          Navigator.push(context, MaterialPageRoute(builder: (context) => screen));
        },
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 48, color: Colors.blue),
            SizedBox(height: 8),
            Text(title, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }
}
