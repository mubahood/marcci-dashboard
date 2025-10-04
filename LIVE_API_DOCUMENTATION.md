# 📡 LIVE API ENDPOINTS DOCUMENTATION

**Version:** 1.0  
**Created:** October 4, 2025  
**Base URL:** `http://your-domain.com/api/live`  
**Authentication:** Required (Bearer Token)

---

## 🎯 OVERVIEW

These are advanced, production-ready API endpoints designed for real-time data access in mobile and web applications. They provide comprehensive filtering, searching, sorting, and pagination capabilities.

### Key Features
- ✅ **Advanced Filtering** - Filter by multiple fields simultaneously
- ✅ **Full-Text Search** - Search across relevant text fields
- ✅ **Date Range Filtering** - Filter by custom date ranges
- ✅ **Amount Range Filtering** - Filter by min/max amounts
- ✅ **Flexible Sorting** - Sort by any field in ascending or descending order
- ✅ **Pagination** - Built-in pagination with metadata
- ✅ **Relationship Loading** - Related data loaded automatically
- ✅ **Summary Statistics** - Get aggregated data alongside results
- ✅ **Performance Optimized** - Efficient database queries with proper indexing
- ✅ **Error Handling** - Comprehensive error messages
- ✅ **Consistent Response Format** - Standardized JSON responses

---

## 🔐 AUTHENTICATION

All endpoints require authentication using Bearer token in the Authorization header.

```http
Authorization: Bearer {your_access_token}
```

**How to get token:**
1. Login via `/api/auth/login` endpoint
2. Use the returned `access_token` in subsequent requests

---

## 📊 RESPONSE FORMAT

All responses follow a consistent format:

### Success Response
```json
{
  "success": true,
  "code": 1,
  "message": "Success message",
  "data": [...], // Array of items or object
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 95,
      "from": 1,
      "to": 20,
      "has_more": true
    },
    "summary": {
      // Aggregated statistics
    },
    "filters_applied": {
      // Filters that were applied
    }
  },
  "timestamp": "2025-10-04T12:00:00.000Z"
}
```

### Error Response
```json
{
  "success": false,
  "code": 0,
  "message": "Error message",
  "data": null,
  "timestamp": "2025-10-04T12:00:00.000Z"
}
```

---

## 📚 ENDPOINTS

### 1. TRANSACTIONS

Fetch user's transaction history with advanced filtering.

**Endpoint:** `GET /api/live/transactions`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `search` | string | No | Search by description, details, type, payment method | `deposit` |
| `type` | string | No | Filter by transaction type | `DEPOSIT`, `WITHDRAW`, `LOAN` |
| `date_from` | date | No | Start date (Y-m-d format) | `2025-01-01` |
| `date_to` | date | No | End date (Y-m-d format) | `2025-12-31` |
| `amount_min` | number | No | Minimum amount | `1000` |
| `amount_max` | number | No | Maximum amount | `100000` |
| `cycle_id` | integer | No | Filter by cycle ID | `5` |
| `user_id` | integer | No | Filter by user (Admin only) | `123` |
| `sort_by` | string | No | Field to sort by | `created_at`, `amount` |
| `sort_order` | string | No | Sort order | `asc`, `desc` |
| `per_page` | integer | No | Items per page (max 100) | `20` |
| `page` | integer | No | Page number | `1` |

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/live/transactions?type=DEPOSIT&date_from=2025-01-01&per_page=20&page=1" \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json"
```

#### Example Response

```json
{
  "success": true,
  "code": 1,
  "message": "Transactions fetched successfully",
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "sacco_id": 1,
      "cycle_id": 5,
      "type": "DEPOSIT",
      "amount": 50000,
      "description": "Monthly savings deposit",
      "details": "Deposited via mobile money",
      "payment_method": "Mobile Money",
      "created_at": "2025-10-01T10:30:00.000Z",
      "updated_at": "2025-10-01T10:30:00.000Z",
      "user": {
        "id": 123,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "cycle": {
        "id": 5,
        "name": "2025 Cycle 1",
        "status": "Active"
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 20,
      "total": 55,
      "from": 1,
      "to": 20,
      "has_more": true
    },
    "summary": {
      "total_deposits": 250000,
      "total_withdrawals": -50000,
      "current_balance": 200000,
      "transaction_count": 55
    },
    "filters_applied": {
      "type": "DEPOSIT",
      "date_from": "2025-01-01"
    }
  },
  "timestamp": "2025-10-04T12:00:00.000Z"
}
```

---

### 2. LOANS

Fetch loans with comprehensive filtering and balance calculations.

**Endpoint:** `GET /api/live/loans`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `search` | string | No | Search by loan details | `emergency` |
| `status` | string | No | Filter by status | `Pending`, `Approved`, `Active`, `Completed` |
| `date_from` | date | No | Start date | `2025-01-01` |
| `date_to` | date | No | End date | `2025-12-31` |
| `amount_min` | number | No | Minimum loan amount | `10000` |
| `amount_max` | number | No | Maximum loan amount | `1000000` |
| `scheme_id` | integer | No | Filter by loan scheme | `3` |
| `overdue` | boolean | No | Filter overdue loans | `true`, `false` |
| `user_id` | integer | No | Filter by user (Admin only) | `123` |
| `sort_by` | string | No | Field to sort by | `created_at`, `amount` |
| `sort_order` | string | No | Sort order | `asc`, `desc` |
| `per_page` | integer | No | Items per page (max 100) | `20` |
| `page` | integer | No | Page number | `1` |

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/live/loans?status=Active&overdue=false&per_page=10" \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json"
```

#### Example Response

```json
{
  "success": true,
  "code": 1,
  "message": "Loans fetched successfully",
  "data": [
    {
      "id": 15,
      "user_id": 123,
      "sacco_id": 1,
      "loan_scheem_id": 3,
      "cycle_id": 5,
      "amount": 500000,
      "status": "Active",
      "due_date": "2026-01-01",
      "description": "Business expansion loan",
      "created_at": "2025-06-01T10:00:00.000Z",
      "balance_calculated": 350000,
      "is_overdue": false,
      "user": {
        "id": 123,
        "name": "John Doe"
      },
      "loan_scheem": {
        "id": 3,
        "name": "Business Loan",
        "interest_rate": 12
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 2,
      "from": 1,
      "to": 2,
      "has_more": false
    },
    "summary": {
      "total_loans": 5,
      "active_loans": 2,
      "total_borrowed": 1500000,
      "total_balance": 850000
    },
    "filters_applied": {
      "status": "Active",
      "overdue": "false"
    }
  }
}
```

---

### 3. CONTRIBUTION PROGRAMS

Fetch contribution programs with member participation data.

**Endpoint:** `GET /api/live/contribution-programs`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `search` | string | No | Search by name, details | `monthly` |
| `contribution_type` | string | No | Filter by type | `Periodic`, `Open` |
| `periodic_type` | string | No | Filter by period | `Monthly`, `Weekly`, `Daily` |
| `status` | string | No | Filter by status | `Active`, `Inactive`, `Completed` |
| `date_from` | date | No | Start date | `2025-01-01` |
| `date_to` | date | No | End date | `2025-12-31` |
| `sort_by` | string | No | Field to sort by | `created_at`, `name` |
| `sort_order` | string | No | Sort order | `asc`, `desc` |
| `per_page` | integer | No | Items per page (max 100) | `20` |
| `page` | integer | No | Page number | `1` |

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/live/contribution-programs?status=Active&contribution_type=Periodic" \
  -H "Authorization: Bearer your_token_here"
```

#### Example Response

```json
{
  "success": true,
  "code": 1,
  "message": "Contribution programs fetched successfully",
  "data": [
    {
      "id": 8,
      "sacco_id": 1,
      "name": "Monthly Savings Program",
      "contribution_type": "Periodic",
      "periodic_type": "Monthly",
      "amount_per_member_type": "Specific",
      "amount_per_member_value": 50000,
      "start_date": "2025-01-01",
      "end_date": "2025-12-31",
      "status": "Active",
      "total_expected": 6000000,
      "total_collected": 4500000,
      "total_balance": 1500000,
      "members": [123, 456, 789],
      "total_records": 120,
      "paid_records": 85,
      "payment_rate": 70.83,
      "user_is_member": true,
      "created_at": "2025-01-01T00:00:00.000Z"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 3,
      "from": 1,
      "to": 3,
      "has_more": false
    },
    "filters_applied": {
      "status": "Active",
      "contribution_type": "Periodic"
    }
  }
}
```

---

### 4. CONTRIBUTION RECORDS

Fetch contribution payment records with filtering.

**Endpoint:** `GET /api/live/contribution-records`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `program_id` | integer | No* | Filter by program (*required for members) | `8` |
| `member_id` | integer | No | Filter by member (Admin only) | `123` |
| `is_paid` | string | No | Filter by payment status | `Yes`, `No` |
| `date_from` | date | No | Payment date from | `2025-01-01` |
| `date_to` | date | No | Payment date to | `2025-12-31` |
| `payment_date_from` | date | No | Payment date range start | `2025-01-01` |
| `payment_date_to` | date | No | Payment date range end | `2025-12-31` |
| `period_name` | string | No | Filter by period | `January 2025` |
| `sort_by` | string | No | Field to sort by | `created_at`, `amount` |
| `sort_order` | string | No | Sort order | `asc`, `desc` |
| `per_page` | integer | No | Items per page (max 100) | `20` |
| `page` | integer | No | Page number | `1` |

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/live/contribution-records?program_id=8&is_paid=No" \
  -H "Authorization: Bearer your_token_here"
```

#### Example Response

```json
{
  "success": true,
  "code": 1,
  "message": "Contribution records fetched successfully",
  "data": [
    {
      "id": 450,
      "sacco_id": 1,
      "contribution_program_id": 8,
      "member_id": 123,
      "amount": 50000,
      "paid_amount": 0,
      "is_paid": "No",
      "period_name": "October 2025",
      "payment_date": null,
      "teasurer_id": null,
      "created_at": "2025-10-01T00:00:00.000Z",
      "member": {
        "id": 123,
        "name": "John Doe",
        "phone_number": "0700000000"
      },
      "program": {
        "id": 8,
        "name": "Monthly Savings Program",
        "periodic_type": "Monthly"
      },
      "treasurer": null
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 20,
      "total": 35,
      "from": 1,
      "to": 20,
      "has_more": true
    },
    "summary": {
      "total_expected": 600000,
      "total_paid": 450000,
      "total_balance": 150000,
      "paid_count": 9,
      "unpaid_count": 3
    },
    "filters_applied": {
      "program_id": "8",
      "is_paid": "No"
    }
  }
}
```

---

### 5. SHARE RECORDS

Fetch share purchase/sale records.

**Endpoint:** `GET /api/live/share-records`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `search` | string | No | Search by details | `purchase` |
| `type` | string | No | Filter by type | `Purchase`, `Sale` |
| `date_from` | date | No | Start date | `2025-01-01` |
| `date_to` | date | No | End date | `2025-12-31` |
| `amount_min` | number | No | Minimum amount | `5000` |
| `amount_max` | number | No | Maximum amount | `500000` |
| `user_id` | integer | No | Filter by user (Admin only) | `123` |
| `sort_by` | string | No | Field to sort by | `created_at` |
| `sort_order` | string | No | Sort order | `asc`, `desc` |
| `per_page` | integer | No | Items per page (max 100) | `20` |
| `page` | integer | No | Page number | `1` |

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/live/share-records?per_page=10" \
  -H "Authorization: Bearer your_token_here"
```

---

### 6. MEMBERS

Fetch SACCO members with filtering (Admin functionality).

**Endpoint:** `GET /api/live/members`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `search` | string | No | Search by name, email, phone | `john` |
| `user_type` | string | No | Filter by user type | `Admin`, `Member`, `Treasurer` |
| `status` | string | No | Filter by active status | `1`, `0` |
| `sacco_join_status` | string | No | Filter by join status | `Approved`, `Pending`, `Rejected` |
| `active_status` | integer | No | Filter by status | `1`, `0` |
| `sort_by` | string | No | Field to sort by | `created_at`, `name` |
| `sort_order` | string | No | Sort order | `asc`, `desc` |
| `per_page` | integer | No | Items per page (max 100) | `20` |
| `page` | integer | No | Page number | `1` |

---

### 7. CYCLES

Fetch SACCO cycles with statistics.

**Endpoint:** `GET /api/live/cycles`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `status` | string | No | Filter by status | `Active`, `Inactive`, `Completed` |
| `date_from` | date | No | Start date | `2025-01-01` |
| `date_to` | date | No | End date | `2025-12-31` |
| `sort_by` | string | No | Field to sort by | `created_at` |
| `sort_order` | string | No | Sort order | `asc`, `desc` |
| `per_page` | integer | No | Items per page (max 100) | `20` |
| `page` | integer | No | Page number | `1` |

---

### 8. DASHBOARD

Get comprehensive dashboard data for the user.

**Endpoint:** `GET /api/live/dashboard`

#### No Query Parameters Required

Returns comprehensive summary including:
- Account balance
- Active loans with balances
- Contribution summary
- Share holdings
- Recent transactions (last 5)
- Upcoming payments (next 5 unpaid contributions)

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/live/dashboard" \
  -H "Authorization: Bearer your_token_here"
```

#### Example Response

```json
{
  "success": true,
  "code": 1,
  "message": "Dashboard data fetched successfully",
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "phone_number": "0700000000",
      "user_type": "Member"
    },
    "account_balance": 1500000,
    "loans": {
      "count": 2,
      "total_borrowed": 1000000,
      "total_balance": 650000,
      "loans": [
        {
          "id": 15,
          "amount": 500000,
          "status": "Active",
          "current_balance": 350000,
          "loan_scheem": {
            "name": "Business Loan"
          }
        }
      ]
    },
    "contributions": {
      "total_expected": 600000,
      "total_paid": 450000,
      "unpaid_count": 3,
      "active_programs": 2
    },
    "shares": {
      "total_shares": 50,
      "total_value": 250000
    },
    "recent_transactions": [...],
    "upcoming_payments": [...]
  },
  "timestamp": "2025-10-04T12:00:00.000Z"
}
```

---

### 9. STATISTICS

Get advanced statistics and analytics.

**Endpoint:** `GET /api/live/statistics`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `period` | string | No | Aggregation period | `daily`, `weekly`, `monthly`, `yearly` |
| `date_from` | date | No | Start date (default: 6 months ago) | `2025-01-01` |
| `date_to` | date | No | End date (default: today) | `2025-12-31` |

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/live/statistics?period=monthly&date_from=2025-01-01" \
  -H "Authorization: Bearer your_token_here"
```

#### Example Response

```json
{
  "success": true,
  "code": 1,
  "message": "Statistics fetched successfully",
  "data": {
    "period": "monthly",
    "date_range": {
      "from": "2025-01-01",
      "to": "2025-10-04"
    },
    "transaction_trends": [
      {
        "period": "2025-01",
        "deposits": 250000,
        "withdrawals": -50000,
        "transaction_count": 15
      },
      {
        "period": "2025-02",
        "deposits": 300000,
        "withdrawals": -75000,
        "transaction_count": 18
      }
    ],
    "contribution_trends": [
      {
        "period": "2025-01",
        "expected": 50000,
        "paid": 50000,
        "record_count": 1
      }
    ],
    "summary": {
      "total_deposits": 2250000,
      "total_withdrawals": 450000,
      "contributions_paid": 450000
    }
  }
}
```

---

## 🔍 COMMON QUERY PATTERNS

### Pattern 1: Basic Listing
```
GET /api/live/transactions?per_page=20&page=1
```

### Pattern 2: Search
```
GET /api/live/transactions?search=deposit&per_page=20
```

### Pattern 3: Date Range Filter
```
GET /api/live/loans?date_from=2025-01-01&date_to=2025-12-31
```

### Pattern 4: Multiple Filters
```
GET /api/live/transactions?type=DEPOSIT&amount_min=10000&date_from=2025-01-01&sort_by=amount&sort_order=desc
```

### Pattern 5: Status Filter
```
GET /api/live/loans?status=Active&overdue=false
```

---

## ⚡ PERFORMANCE TIPS

1. **Use Pagination**: Always specify reasonable `per_page` values (default: 20, max: 100)
2. **Filter Early**: Apply filters to reduce data set size
3. **Limit Fields**: Only request data you need
4. **Cache Results**: Cache dashboard and statistics data on client side
5. **Use Indexes**: Database indexes are optimized for common filters

---

## 🐛 ERROR CODES

| Code | HTTP Status | Description |
|------|-------------|-------------|
| 0 | 400 | Bad Request - Invalid parameters |
| 0 | 401 | Unauthorized - Invalid or missing token |
| 0 | 403 | Forbidden - Insufficient permissions |
| 0 | 404 | Not Found - Resource doesn't exist |
| 0 | 500 | Server Error - Internal server error |
| 1 | 200 | Success - Request completed successfully |

---

## 📱 FLUTTER INTEGRATION EXAMPLE

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class LiveApiService {
  final String baseUrl = 'https://your-domain.com/api/live';
  final String token;
  
  LiveApiService(this.token);
  
  Future<Map<String, dynamic>> getTransactions({
    String? type,
    String? dateFrom,
    String? dateTo,
    int perPage = 20,
    int page = 1,
  }) async {
    var queryParams = {
      'per_page': perPage.toString(),
      'page': page.toString(),
    };
    
    if (type != null) queryParams['type'] = type;
    if (dateFrom != null) queryParams['date_from'] = dateFrom;
    if (dateTo != null) queryParams['date_to'] = dateTo;
    
    var uri = Uri.parse('$baseUrl/transactions')
        .replace(queryParameters: queryParams);
    
    var response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load transactions');
    }
  }
  
  Future<Map<String, dynamic>> getDashboard() async {
    var uri = Uri.parse('$baseUrl/dashboard');
    
    var response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load dashboard');
    }
  }
}

// Usage
void main() async {
  var api = LiveApiService('your_token_here');
  
  // Get transactions
  var transactions = await api.getTransactions(
    type: 'DEPOSIT',
    perPage: 20,
    page: 1,
  );
  
  print('Transactions: ${transactions['data']}');
  print('Total: ${transactions['meta']['pagination']['total']}');
  
  // Get dashboard
  var dashboard = await api.getDashboard();
  print('Balance: ${dashboard['data']['account_balance']}');
}
```

---

## 🧪 TESTING WITH POSTMAN

1. **Import Collection**: Create a new Postman collection
2. **Set Base URL**: Environment variable `{{base_url}} = http://your-domain.com/api/live`
3. **Set Token**: Environment variable `{{token}} = your_bearer_token`
4. **Authorization**: Use Bearer Token with `{{token}}`
5. **Test Endpoints**: Use the examples above

### Sample Postman Collection Structure
```
📁 SACCO Live API
  📁 Transactions
    ├─ List All Transactions
    ├─ Filter by Type
    ├─ Search Transactions
    └─ Date Range Filter
  📁 Loans
    ├─ List All Loans
    ├─ Active Loans
    └─ Overdue Loans
  📁 Contributions
    ├─ List Programs
    ├─ List Records
    └─ Unpaid Contributions
  📁 Dashboard
    └─ Get Dashboard
  📁 Statistics
    └─ Get Statistics
```

---

## 📋 CHANGELOG

### Version 1.0 (October 4, 2025)
- ✅ Initial release
- ✅ 9 endpoints implemented
- ✅ Advanced filtering and pagination
- ✅ Summary statistics
- ✅ Relationship eager loading
- ✅ Comprehensive error handling
- ✅ Performance optimizations

---

## 🆘 SUPPORT

For issues or questions:
- Check error messages in response
- Verify authentication token
- Ensure correct parameter formats
- Review this documentation
- Check API logs on server

---

## 🎉 CONCLUSION

These Live API endpoints provide a robust, production-ready solution for real-time data access in your SACCO management application. The comprehensive filtering, pagination, and search capabilities make it easy to integrate into any mobile or web application.

**Key Benefits:**
- ⚡ Fast and efficient queries
- 🔒 Secure with proper authorization
- 📊 Rich metadata and statistics
- 🎯 Flexible filtering options
- 📱 Mobile-friendly responses
- 🔄 Consistent response format
- 📈 Scalable architecture

**Happy Coding! 🚀**
