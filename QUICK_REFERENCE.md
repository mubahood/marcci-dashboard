# 🚀 LIVE API - QUICK REFERENCE CARD

**Base URL:** `http://your-domain.com/api/live`  
**Authentication:** Bearer Token Required  
**All Requests:** `Authorization: Bearer {token}`

---

## 📡 ENDPOINTS AT A GLANCE

| Endpoint | Purpose | Key Filters |
|----------|---------|-------------|
| `GET /transactions` | Transaction history | type, date_from, date_to, amount_min, amount_max, cycle_id |
| `GET /loans` | Loan management | status, overdue, scheme_id, amount_min, amount_max |
| `GET /contribution-programs` | Program listing | status, contribution_type, periodic_type |
| `GET /contribution-records` | Payment records | program_id, is_paid, period_name, member_id |
| `GET /share-records` | Share transactions | type, date_from, date_to, amount_min, amount_max |
| `GET /members` | Member list (Admin) | search, user_type, status, sacco_join_status |
| `GET /cycles` | Cycle management | status, date_from, date_to |
| `GET /dashboard` | User dashboard | No parameters |
| `GET /statistics` | Analytics | period, date_from, date_to |

---

## 🔍 COMMON FILTERS (Available on Most Endpoints)

| Parameter | Type | Example | Description |
|-----------|------|---------|-------------|
| `search` | string | `deposit` | Full-text search |
| `date_from` | date | `2025-01-01` | Start date (Y-m-d) |
| `date_to` | date | `2025-12-31` | End date (Y-m-d) |
| `amount_min` | number | `1000` | Minimum amount |
| `amount_max` | number | `100000` | Maximum amount |
| `sort_by` | string | `created_at` | Sort field |
| `sort_order` | string | `desc` | Sort order (asc/desc) |
| `per_page` | integer | `20` | Items per page (max 100) |
| `page` | integer | `1` | Page number |

---

## 📋 TRANSACTION TYPES

- `DEPOSIT` - Money added to account
- `WITHDRAW` - Money taken from account
- `LOAN` - Loan-related transaction
- `CONTRIBUTION` - Contribution payment
- `SHARE` - Share purchase/sale

---

## 💰 LOAN STATUSES

- `Pending` - Awaiting approval
- `Approved` - Approved but not disbursed
- `Active` - Currently active
- `Completed` - Fully paid
- `Rejected` - Rejected application

---

## 📊 CONTRIBUTION TYPES

- **Contribution Type:**
  - `Periodic` - Regular scheduled contributions
  - `Open` - Flexible contributions

- **Periodic Type:**
  - `Daily` - Every day
  - `Weekly` - Every week
  - `Monthly` - Every month
  - `Quarterly` - Every 3 months
  - `Yearly` - Every year

- **Amount Type:**
  - `Specific` - Fixed amount per member
  - `Any` - Variable amount per member

---

## 🎯 QUICK EXAMPLES

### Get Recent Deposits
```bash
GET /api/live/transactions?type=DEPOSIT&per_page=10&sort_by=created_at&sort_order=desc
```

### Get Overdue Loans
```bash
GET /api/live/loans?overdue=true&status=Active
```

### Get Unpaid Contributions
```bash
GET /api/live/contribution-records?program_id=8&is_paid=No
```

### Get Dashboard Summary
```bash
GET /api/live/dashboard
```

### Get Monthly Statistics
```bash
GET /api/live/statistics?period=monthly&date_from=2025-01-01
```

---

## 📊 RESPONSE FORMAT

```json
{
  "success": true,           // Request success status
  "code": 1,                 // 1 = success, 0 = error
  "message": "...",          // Human-readable message
  "data": [...],             // Array of items or object
  "meta": {
    "pagination": {          // Pagination info
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 95,
      "has_more": true
    },
    "summary": {...},        // Aggregated statistics
    "filters_applied": {...} // Filters used
  },
  "timestamp": "..."         // Response timestamp
}
```

---

## ⚠️ ERROR CODES

| Code | Status | Meaning |
|------|--------|---------|
| 0 | 400 | Bad Request - Invalid parameters |
| 0 | 401 | Unauthorized - Invalid/missing token |
| 0 | 403 | Forbidden - Insufficient permissions |
| 0 | 404 | Not Found - Resource doesn't exist |
| 0 | 500 | Server Error - Internal error |
| 1 | 200 | Success - Request completed |

---

## 🧪 TESTING CHECKLIST

### Postman Testing
- [ ] Import `POSTMAN_COLLECTION.json`
- [ ] Set `base_url` variable
- [ ] Get auth token via login
- [ ] Set `token` variable
- [ ] Test each endpoint
- [ ] Try different filters
- [ ] Test pagination
- [ ] Test error scenarios

### Integration Testing
- [ ] Test authentication flow
- [ ] Test data accuracy
- [ ] Test pagination with large datasets
- [ ] Test filters produce correct results
- [ ] Test summary calculations
- [ ] Test sorting works correctly
- [ ] Test error handling
- [ ] Test with different user roles

---

## 📱 FLUTTER QUICK START

### 1. Install Dependencies
```yaml
dependencies:
  http: ^1.1.0
  provider: ^6.1.1
  shared_preferences: ^2.2.2
```

### 2. Configure API
```dart
class ApiConfig {
  static const String liveApiUrl = 'https://your-domain.com/api/live';
}
```

### 3. Create API Service
```dart
class LiveApiService {
  static Future<Map<String, dynamic>> getTransactions() async {
    final response = await http.get(
      Uri.parse('${ApiConfig.liveApiUrl}/transactions'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );
    return json.decode(response.body);
  }
}
```

### 4. Implement in UI
```dart
// In your widget
FutureBuilder(
  future: LiveApiService.getTransactions(),
  builder: (context, snapshot) {
    if (snapshot.hasData) {
      return ListView.builder(...);
    }
    return CircularProgressIndicator();
  },
)
```

---

## 💡 BEST PRACTICES

### Performance
- ✅ Use pagination (don't load all at once)
- ✅ Apply filters to reduce data
- ✅ Cache dashboard data
- ✅ Use appropriate `per_page` values

### Error Handling
- ✅ Check `success` field in response
- ✅ Handle 401 errors (re-login)
- ✅ Show user-friendly error messages
- ✅ Retry on network errors

### Security
- ✅ Store token securely
- ✅ Clear token on logout
- ✅ Handle token expiration
- ✅ Use HTTPS in production

### UX
- ✅ Show loading indicators
- ✅ Implement pull-to-refresh
- ✅ Show empty states
- ✅ Display summary statistics

---

## 📚 FULL DOCUMENTATION

- **API Reference:** `LIVE_API_DOCUMENTATION.md`
- **Flutter Guide:** `FLUTTER_INTEGRATION_GUIDE.md`
- **Postman Collection:** `POSTMAN_COLLECTION.json`
- **Implementation Summary:** `IMPLEMENTATION_SUMMARY.md`

---

## 🆘 TROUBLESHOOTING

**Problem:** 401 Unauthorized  
**Solution:** Check token is valid, re-login if expired

**Problem:** Empty data array  
**Solution:** Check filters, verify data exists

**Problem:** Slow response  
**Solution:** Reduce per_page, apply more filters

**Problem:** Pagination not working  
**Solution:** Check `has_more` flag, increment page

---

## ✅ PRODUCTION CHECKLIST

- [ ] All endpoints tested with Postman
- [ ] Authentication working correctly
- [ ] Filters produce accurate results
- [ ] Pagination works with large data
- [ ] Summary calculations are correct
- [ ] Performance is acceptable
- [ ] Error handling works properly
- [ ] Database indexes added
- [ ] Security audit completed
- [ ] Documentation reviewed
- [ ] Mobile app integrated
- [ ] User acceptance testing done

---

## 🎯 KEY CONTACTS

**Backend Developer:** Review `LiveApiController.php`  
**Mobile Developer:** Start with `FLUTTER_INTEGRATION_GUIDE.md`  
**QA Tester:** Use `POSTMAN_COLLECTION.json`  
**Project Manager:** Read `IMPLEMENTATION_SUMMARY.md`

---

**Last Updated:** October 4, 2025  
**Version:** 1.0  
**Status:** Production Ready ✅

---

*Keep this card handy for quick reference during development!*
