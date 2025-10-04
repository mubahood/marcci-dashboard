# 📱 FLUTTER INTEGRATION GUIDE - SACCO Live API

**Last Updated:** October 4, 2025  
**Target:** Flutter Mobile App Developers

---

## 🎯 OVERVIEW

This guide helps you integrate the SACCO Live API endpoints into your Flutter mobile application. The API provides real-time access to transactions, loans, contributions, and more with advanced filtering capabilities.

---

## 📦 DEPENDENCIES

Add these to your `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.1.0
  shared_preferences: ^2.2.2
  provider: ^6.1.1
  intl: ^0.18.1
```

Run:
```bash
flutter pub get
```

---

## 🏗️ PROJECT STRUCTURE

```
lib/
├── main.dart
├── config/
│   └── api_config.dart          # API configuration
├── services/
│   ├── api_service.dart         # Base API service
│   └── live_api_service.dart    # Live API endpoints
├── models/
│   ├── transaction.dart
│   ├── loan.dart
│   ├── contribution_program.dart
│   ├── contribution_record.dart
│   ├── api_response.dart
│   └── pagination_meta.dart
├── providers/
│   ├── auth_provider.dart
│   ├── transaction_provider.dart
│   ├── loan_provider.dart
│   └── dashboard_provider.dart
└── screens/
    ├── dashboard_screen.dart
    ├── transactions_screen.dart
    ├── loans_screen.dart
    └── contributions_screen.dart
```

---

## ⚙️ CONFIGURATION

### 1. API Configuration (`lib/config/api_config.dart`)

```dart
class ApiConfig {
  // Change this to your actual API URL
  static const String baseUrl = 'https://your-domain.com/api';
  static const String liveApiUrl = '$baseUrl/live';
  
  // API Endpoints
  static const String authLogin = '$baseUrl/auth/login';
  static const String authRegister = '$baseUrl/auth/register';
  
  // Live API Endpoints
  static const String transactions = '$liveApiUrl/transactions';
  static const String loans = '$liveApiUrl/loans';
  static const String contributionPrograms = '$liveApiUrl/contribution-programs';
  static const String contributionRecords = '$liveApiUrl/contribution-records';
  static const String shareRecords = '$liveApiUrl/share-records';
  static const String members = '$liveApiUrl/members';
  static const String cycles = '$liveApiUrl/cycles';
  static const String dashboard = '$liveApiUrl/dashboard';
  static const String statistics = '$liveApiUrl/statistics';
  
  // Request timeouts
  static const Duration connectionTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
  
  // Pagination
  static const int defaultPerPage = 20;
  static const int maxPerPage = 100;
}
```

---

## 📊 DATA MODELS

### API Response Model (`lib/models/api_response.dart`)

```dart
class ApiResponse<T> {
  final bool success;
  final int code;
  final String message;
  final T? data;
  final PaginationMeta? meta;
  final String timestamp;

  ApiResponse({
    required this.success,
    required this.code,
    required this.message,
    this.data,
    this.meta,
    required this.timestamp,
  });

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic)? fromJsonT,
  ) {
    return ApiResponse<T>(
      success: json['success'] ?? false,
      code: json['code'] ?? 0,
      message: json['message'] ?? '',
      data: json['data'] != null && fromJsonT != null 
          ? fromJsonT(json['data']) 
          : json['data'],
      meta: json['meta'] != null 
          ? PaginationMeta.fromJson(json['meta']) 
          : null,
      timestamp: json['timestamp'] ?? '',
    );
  }
}
```

### Pagination Meta Model (`lib/models/pagination_meta.dart`)

```dart
class PaginationMeta {
  final PaginationData? pagination;
  final Map<String, dynamic>? summary;
  final Map<String, dynamic>? filtersApplied;

  PaginationMeta({
    this.pagination,
    this.summary,
    this.filtersApplied,
  });

  factory PaginationMeta.fromJson(Map<String, dynamic> json) {
    return PaginationMeta(
      pagination: json['pagination'] != null
          ? PaginationData.fromJson(json['pagination'])
          : null,
      summary: json['summary'],
      filtersApplied: json['filters_applied'],
    );
  }
}

class PaginationData {
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;
  final int from;
  final int to;
  final bool hasMore;

  PaginationData({
    required this.currentPage,
    required this.lastPage,
    required this.perPage,
    required this.total,
    required this.from,
    required this.to,
    required this.hasMore,
  });

  factory PaginationData.fromJson(Map<String, dynamic> json) {
    return PaginationData(
      currentPage: json['current_page'] ?? 1,
      lastPage: json['last_page'] ?? 1,
      perPage: json['per_page'] ?? 20,
      total: json['total'] ?? 0,
      from: json['from'] ?? 0,
      to: json['to'] ?? 0,
      hasMore: json['has_more'] ?? false,
    );
  }
}
```

### Transaction Model (`lib/models/transaction.dart`)

```dart
class Transaction {
  final int id;
  final int userId;
  final int saccoId;
  final int? cycleId;
  final String type;
  final double amount;
  final String? description;
  final String? details;
  final String? paymentMethod;
  final DateTime createdAt;
  final DateTime updatedAt;
  final User? user;
  final Cycle? cycle;

  Transaction({
    required this.id,
    required this.userId,
    required this.saccoId,
    this.cycleId,
    required this.type,
    required this.amount,
    this.description,
    this.details,
    this.paymentMethod,
    required this.createdAt,
    required this.updatedAt,
    this.user,
    this.cycle,
  });

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      id: json['id'],
      userId: json['user_id'],
      saccoId: json['sacco_id'],
      cycleId: json['cycle_id'],
      type: json['type'],
      amount: double.parse(json['amount'].toString()),
      description: json['description'],
      details: json['details'],
      paymentMethod: json['payment_method'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      user: json['user'] != null ? User.fromJson(json['user']) : null,
      cycle: json['cycle'] != null ? Cycle.fromJson(json['cycle']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'sacco_id': saccoId,
      'cycle_id': cycleId,
      'type': type,
      'amount': amount,
      'description': description,
      'details': details,
      'payment_method': paymentMethod,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}

class User {
  final int id;
  final String name;
  final String email;

  User({required this.id, required this.name, required this.email});

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
    );
  }
}

class Cycle {
  final int id;
  final String name;
  final String status;

  Cycle({required this.id, required this.name, required this.status});

  factory Cycle.fromJson(Map<String, dynamic> json) {
    return Cycle(
      id: json['id'],
      name: json['name'],
      status: json['status'],
    );
  }
}
```

### Loan Model (`lib/models/loan.dart`)

```dart
class Loan {
  final int id;
  final int userId;
  final int saccoId;
  final int? loanScheemId;
  final int? cycleId;
  final double amount;
  final String status;
  final DateTime? dueDate;
  final String? description;
  final DateTime createdAt;
  final double? balanceCalculated;
  final bool? isOverdue;
  final User? user;
  final LoanScheem? loanScheem;

  Loan({
    required this.id,
    required this.userId,
    required this.saccoId,
    this.loanScheemId,
    this.cycleId,
    required this.amount,
    required this.status,
    this.dueDate,
    this.description,
    required this.createdAt,
    this.balanceCalculated,
    this.isOverdue,
    this.user,
    this.loanScheem,
  });

  factory Loan.fromJson(Map<String, dynamic> json) {
    return Loan(
      id: json['id'],
      userId: json['user_id'],
      saccoId: json['sacco_id'],
      loanScheemId: json['loan_scheem_id'],
      cycleId: json['cycle_id'],
      amount: double.parse(json['amount'].toString()),
      status: json['status'],
      dueDate: json['due_date'] != null ? DateTime.parse(json['due_date']) : null,
      description: json['description'],
      createdAt: DateTime.parse(json['created_at']),
      balanceCalculated: json['balance_calculated'] != null
          ? double.parse(json['balance_calculated'].toString())
          : null,
      isOverdue: json['is_overdue'],
      user: json['user'] != null ? User.fromJson(json['user']) : null,
      loanScheem: json['loan_scheem'] != null
          ? LoanScheem.fromJson(json['loan_scheem'])
          : null,
    );
  }
}

class LoanScheem {
  final int id;
  final String name;
  final double? interestRate;

  LoanScheem({required this.id, required this.name, this.interestRate});

  factory LoanScheem.fromJson(Map<String, dynamic> json) {
    return LoanScheem(
      id: json['id'],
      name: json['name'],
      interestRate: json['interest_rate'] != null
          ? double.parse(json['interest_rate'].toString())
          : null,
    );
  }
}
```

---

## 🔧 API SERVICE

### Base API Service (`lib/services/api_service.dart`)

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';

class ApiService {
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  static Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  static Future<Map<String, String>> getHeaders() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<http.Response> get(
    String url, {
    Map<String, String>? queryParams,
  }) async {
    final uri = Uri.parse(url).replace(queryParameters: queryParams);
    final headers = await getHeaders();
    
    try {
      final response = await http
          .get(uri, headers: headers)
          .timeout(ApiConfig.connectionTimeout);
      return response;
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }

  static Future<http.Response> post(
    String url,
    Map<String, dynamic> body,
  ) async {
    final uri = Uri.parse(url);
    final headers = await getHeaders();
    
    try {
      final response = await http
          .post(uri, headers: headers, body: json.encode(body))
          .timeout(ApiConfig.connectionTimeout);
      return response;
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }

  static Map<String, dynamic> parseResponse(http.Response response) {
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return json.decode(response.body);
    } else if (response.statusCode == 401) {
      throw Exception('Unauthorized - Please login again');
    } else if (response.statusCode == 403) {
      throw Exception('Forbidden - Insufficient permissions');
    } else if (response.statusCode == 404) {
      throw Exception('Not found');
    } else if (response.statusCode >= 500) {
      throw Exception('Server error - Please try again later');
    } else {
      final body = json.decode(response.body);
      throw Exception(body['message'] ?? 'Request failed');
    }
  }
}
```

### Live API Service (`lib/services/live_api_service.dart`)

```dart
import '../config/api_config.dart';
import '../models/api_response.dart';
import '../models/transaction.dart';
import '../models/loan.dart';
import 'api_service.dart';

class LiveApiService {
  // Get Transactions
  static Future<ApiResponse<List<Transaction>>> getTransactions({
    String? search,
    String? type,
    String? dateFrom,
    String? dateTo,
    double? amountMin,
    double? amountMax,
    int? cycleId,
    String? sortBy,
    String? sortOrder,
    int perPage = 20,
    int page = 1,
  }) async {
    final queryParams = <String, String>{
      'per_page': perPage.toString(),
      'page': page.toString(),
    };

    if (search != null) queryParams['search'] = search;
    if (type != null) queryParams['type'] = type;
    if (dateFrom != null) queryParams['date_from'] = dateFrom;
    if (dateTo != null) queryParams['date_to'] = dateTo;
    if (amountMin != null) queryParams['amount_min'] = amountMin.toString();
    if (amountMax != null) queryParams['amount_max'] = amountMax.toString();
    if (cycleId != null) queryParams['cycle_id'] = cycleId.toString();
    if (sortBy != null) queryParams['sort_by'] = sortBy;
    if (sortOrder != null) queryParams['sort_order'] = sortOrder;

    final response = await ApiService.get(
      ApiConfig.transactions,
      queryParams: queryParams,
    );

    final jsonData = ApiService.parseResponse(response);
    
    return ApiResponse<List<Transaction>>.fromJson(
      jsonData,
      (data) => (data as List).map((item) => Transaction.fromJson(item)).toList(),
    );
  }

  // Get Loans
  static Future<ApiResponse<List<Loan>>> getLoans({
    String? search,
    String? status,
    String? dateFrom,
    String? dateTo,
    double? amountMin,
    double? amountMax,
    int? schemeId,
    bool? overdue,
    String? sortBy,
    String? sortOrder,
    int perPage = 20,
    int page = 1,
  }) async {
    final queryParams = <String, String>{
      'per_page': perPage.toString(),
      'page': page.toString(),
    };

    if (search != null) queryParams['search'] = search;
    if (status != null) queryParams['status'] = status;
    if (dateFrom != null) queryParams['date_from'] = dateFrom;
    if (dateTo != null) queryParams['date_to'] = dateTo;
    if (amountMin != null) queryParams['amount_min'] = amountMin.toString();
    if (amountMax != null) queryParams['amount_max'] = amountMax.toString();
    if (schemeId != null) queryParams['scheme_id'] = schemeId.toString();
    if (overdue != null) queryParams['overdue'] = overdue.toString();
    if (sortBy != null) queryParams['sort_by'] = sortBy;
    if (sortOrder != null) queryParams['sort_order'] = sortOrder;

    final response = await ApiService.get(
      ApiConfig.loans,
      queryParams: queryParams,
    );

    final jsonData = ApiService.parseResponse(response);
    
    return ApiResponse<List<Loan>>.fromJson(
      jsonData,
      (data) => (data as List).map((item) => Loan.fromJson(item)).toList(),
    );
  }

  // Get Dashboard
  static Future<ApiResponse<Map<String, dynamic>>> getDashboard() async {
    final response = await ApiService.get(ApiConfig.dashboard);
    final jsonData = ApiService.parseResponse(response);
    
    return ApiResponse<Map<String, dynamic>>.fromJson(
      jsonData,
      (data) => data as Map<String, dynamic>,
    );
  }

  // Get Statistics
  static Future<ApiResponse<Map<String, dynamic>>> getStatistics({
    String period = 'monthly',
    String? dateFrom,
    String? dateTo,
  }) async {
    final queryParams = <String, String>{
      'period': period,
    };

    if (dateFrom != null) queryParams['date_from'] = dateFrom;
    if (dateTo != null) queryParams['date_to'] = dateTo;

    final response = await ApiService.get(
      ApiConfig.statistics,
      queryParams: queryParams,
    );

    final jsonData = ApiService.parseResponse(response);
    
    return ApiResponse<Map<String, dynamic>>.fromJson(
      jsonData,
      (data) => data as Map<String, dynamic>,
    );
  }
}
```

---

## 🎨 STATE MANAGEMENT (Provider)

### Transaction Provider (`lib/providers/transaction_provider.dart`)

```dart
import 'package:flutter/foundation.dart';
import '../models/transaction.dart';
import '../models/api_response.dart';
import '../services/live_api_service.dart';

class TransactionProvider with ChangeNotifier {
  List<Transaction> _transactions = [];
  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  bool _hasMore = true;
  Map<String, dynamic>? _summary;

  List<Transaction> get transactions => _transactions;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasMore => _hasMore;
  Map<String, dynamic>? get summary => _summary;

  Future<void> fetchTransactions({
    String? search,
    String? type,
    String? dateFrom,
    String? dateTo,
    bool refresh = false,
  }) async {
    if (refresh) {
      _currentPage = 1;
      _transactions = [];
      _hasMore = true;
    }

    if (_isLoading || !_hasMore) return;

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await LiveApiService.getTransactions(
        search: search,
        type: type,
        dateFrom: dateFrom,
        dateTo: dateTo,
        page: _currentPage,
        perPage: 20,
      );

      if (response.success && response.data != null) {
        _transactions.addAll(response.data!);
        _summary = response.meta?.summary;
        _hasMore = response.meta?.pagination?.hasMore ?? false;
        _currentPage++;
      } else {
        _error = response.message;
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void clearTransactions() {
    _transactions = [];
    _currentPage = 1;
    _hasMore = true;
    _error = null;
    _summary = null;
    notifyListeners();
  }
}
```

---

## 🖥️ UI EXAMPLES

### Transaction List Screen (`lib/screens/transactions_screen.dart`)

```dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../providers/transaction_provider.dart';
import '../models/transaction.dart';

class TransactionsScreen extends StatefulWidget {
  @override
  _TransactionsScreenState createState() => _TransactionsScreenState();
}

class _TransactionsScreenState extends State<TransactionsScreen> {
  final ScrollController _scrollController = ScrollController();
  String? _selectedType;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadTransactions();
    _scrollController.addListener(_onScroll);
  }

  void _loadTransactions() {
    final provider = Provider.of<TransactionProvider>(context, listen: false);
    provider.fetchTransactions(refresh: true);
  }

  void _onScroll() {
    if (_scrollController.position.pixels ==
        _scrollController.position.maxScrollExtent) {
      final provider = Provider.of<TransactionProvider>(context, listen: false);
      provider.fetchTransactions(
        type: _selectedType,
        search: _searchController.text.isEmpty ? null : _searchController.text,
      );
    }
  }

  void _applyFilters() {
    final provider = Provider.of<TransactionProvider>(context, listen: false);
    provider.fetchTransactions(
      type: _selectedType,
      search: _searchController.text.isEmpty ? null : _searchController.text,
      refresh: true,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Transactions'),
        actions: [
          IconButton(
            icon: Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search transactions...',
                prefixIcon: Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          _applyFilters();
                        },
                      )
                    : null,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              onSubmitted: (_) => _applyFilters(),
            ),
          ),
          Consumer<TransactionProvider>(
            builder: (context, provider, child) {
              if (provider.summary != null) {
                return _buildSummaryCard(provider.summary!);
              }
              return SizedBox.shrink();
            },
          ),
          Expanded(
            child: Consumer<TransactionProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading && provider.transactions.isEmpty) {
                  return Center(child: CircularProgressIndicator());
                }

                if (provider.error != null && provider.transactions.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 60, color: Colors.red),
                        SizedBox(height: 16),
                        Text(provider.error!),
                        SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadTransactions,
                          child: Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.transactions.isEmpty) {
                  return Center(
                    child: Text('No transactions found'),
                  );
                }

                return RefreshIndicator(
                  onRefresh: () async {
                    _loadTransactions();
                  },
                  child: ListView.builder(
                    controller: _scrollController,
                    itemCount: provider.transactions.length +
                        (provider.hasMore ? 1 : 0),
                    itemBuilder: (context, index) {
                      if (index == provider.transactions.length) {
                        return Center(
                          child: Padding(
                            padding: EdgeInsets.all(16),
                            child: CircularProgressIndicator(),
                          ),
                        );
                      }

                      return _buildTransactionCard(provider.transactions[index]);
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryCard(Map<String, dynamic> summary) {
    final formatter = NumberFormat.currency(symbol: 'UGX ');
    
    return Card(
      margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _buildSummaryItem(
              'Deposits',
              formatter.format(summary['total_deposits'] ?? 0),
              Colors.green,
            ),
            _buildSummaryItem(
              'Withdrawals',
              formatter.format((summary['total_withdrawals'] ?? 0).abs()),
              Colors.red,
            ),
            _buildSummaryItem(
              'Balance',
              formatter.format(summary['current_balance'] ?? 0),
              Colors.blue,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryItem(String label, String value, Color color) {
    return Column(
      children: [
        Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
        ),
        SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }

  Widget _buildTransactionCard(Transaction transaction) {
    final formatter = NumberFormat.currency(symbol: 'UGX ');
    final dateFormatter = DateFormat('MMM dd, yyyy HH:mm');
    
    final isDeposit = transaction.type == 'DEPOSIT';
    final color = isDeposit ? Colors.green : Colors.red;
    final icon = isDeposit ? Icons.arrow_downward : Icons.arrow_upward;

    return Card(
      margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: color.withOpacity(0.1),
          child: Icon(icon, color: color),
        ),
        title: Text(
          transaction.description ?? transaction.type,
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (transaction.details != null)
              Text(transaction.details!),
            SizedBox(height: 4),
            Text(
              dateFormatter.format(transaction.createdAt),
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
            if (transaction.cycle != null)
              Text(
                'Cycle: ${transaction.cycle!.name}',
                style: TextStyle(fontSize: 12, color: Colors.blue),
              ),
          ],
        ),
        trailing: Text(
          formatter.format(transaction.amount),
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ),
    );
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Filter Transactions'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            DropdownButtonFormField<String>(
              value: _selectedType,
              decoration: InputDecoration(
                labelText: 'Transaction Type',
                border: OutlineInputBorder(),
              ),
              items: [
                DropdownMenuItem(value: null, child: Text('All')),
                DropdownMenuItem(value: 'DEPOSIT', child: Text('Deposits')),
                DropdownMenuItem(value: 'WITHDRAW', child: Text('Withdrawals')),
                DropdownMenuItem(value: 'LOAN', child: Text('Loans')),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedType = value;
                });
              },
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              setState(() {
                _selectedType = null;
                _searchController.clear();
              });
              Navigator.pop(context);
              _applyFilters();
            },
            child: Text('Clear'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _applyFilters();
            },
            child: Text('Apply'),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }
}
```

---

## ✅ BEST PRACTICES

### 1. Error Handling

```dart
try {
  final response = await LiveApiService.getTransactions();
  if (response.success) {
    // Handle success
  } else {
    // Show error message
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(response.message)),
    );
  }
} catch (e) {
  // Handle exception
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(content: Text('An error occurred: $e')),
  );
}
```

### 2. Token Management

```dart
// Save token after login
await ApiService.saveToken(loginResponse.token);

// Clear token on logout
await ApiService.clearToken();

// Check if user is logged in
final token = await ApiService.getToken();
if (token == null) {
  // Redirect to login
}
```

### 3. Pagination

```dart
// Implement infinite scroll
void _onScroll() {
  if (_scrollController.position.pixels >= 
      _scrollController.position.maxScrollExtent * 0.8) {
    // Load more when 80% scrolled
    _loadMore();
  }
}
```

### 4. Caching

```dart
// Cache dashboard data for 5 minutes
class CacheManager {
  static final Map<String, CacheEntry> _cache = {};
  
  static void set(String key, dynamic data, Duration duration) {
    _cache[key] = CacheEntry(data, DateTime.now().add(duration));
  }
  
  static dynamic get(String key) {
    final entry = _cache[key];
    if (entry == null || entry.expiry.isBefore(DateTime.now())) {
      return null;
    }
    return entry.data;
  }
}

class CacheEntry {
  final dynamic data;
  final DateTime expiry;
  
  CacheEntry(this.data, this.expiry);
}
```

---

## 🚀 TESTING

### Unit Test Example

```dart
import 'package:flutter_test/flutter_test.dart';
import 'package:your_app/services/live_api_service.dart';

void main() {
  group('LiveApiService', () {
    test('getTransactions returns valid data', () async {
      final response = await LiveApiService.getTransactions(
        perPage: 10,
        page: 1,
      );
      
      expect(response.success, true);
      expect(response.data, isNotNull);
      expect(response.data!.length, lessThanOrEqualTo(10));
    });
    
    test('getTransactions with filters', () async {
      final response = await LiveApiService.getTransactions(
        type: 'DEPOSIT',
        perPage: 10,
      );
      
      expect(response.success, true);
      expect(response.data, isNotNull);
      for (var transaction in response.data!) {
        expect(transaction.type, 'DEPOSIT');
      }
    });
  });
}
```

---

## 📖 ADDITIONAL RESOURCES

- [API Documentation](LIVE_API_DOCUMENTATION.md)
- [Postman Collection](POSTMAN_COLLECTION.json)
- [Flutter HTTP Package](https://pub.dev/packages/http)
- [Provider Package](https://pub.dev/packages/provider)

---

## 🆘 TROUBLESHOOTING

### Common Issues

**1. 401 Unauthorized Error**
- Ensure token is valid and not expired
- Check if token is being sent in headers
- Re-login if token is expired

**2. Network Timeout**
- Check internet connection
- Increase timeout duration in ApiConfig
- Check server status

**3. Parsing Error**
- Ensure API response matches model structure
- Check for null values
- Add proper null safety checks

**4. Pagination Not Working**
- Verify `hasMore` flag from API
- Check scroll controller attachment
- Ensure `page` parameter is incrementing

---

## 🎉 CONCLUSION

You now have everything needed to integrate the SACCO Live API into your Flutter app! The examples provide a solid foundation that you can customize based on your specific requirements.

**Happy Coding! 🚀**
