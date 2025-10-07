# 🎯 Dashboard Quick Reference

## File Location
```
/app/Admin/Controllers/HomeController.php
```

## Access URL
```
http://localhost/marcci-dashboard/admin
```

---

## 📊 Dashboard Sections

### Row 1: Financial Metrics (4 InfoBoxes)
| Box | Metric | Color | Link |
|-----|--------|-------|------|
| 1 | Total Members | Aqua | /admin/members |
| 2 | Contribution Programs | Green | /admin/contributions |
| 3 | Expected Contributions | Yellow | /admin/contribution-program-records |
| 4 | Collected Contributions | Green | /admin/contribution-program-records |

### Row 2: Payment Status (4 InfoBoxes)
| Box | Metric | Color | Link |
|-----|--------|-------|------|
| 1 | Total Records | Purple | /admin/contribution-program-records |
| 2 | Expected (Records) | Red | /admin/contribution-program-records |
| 3 | Paid Amount | Green | /admin/contribution-program-records |
| 4 | Outstanding Balance | Red | /admin/contribution-program-records |

### Row 3: Operations (4 InfoBoxes)
| Box | Metric | Color | Link |
|-----|--------|-------|------|
| 1 | Total Loans | Blue | /admin/loans |
| 2 | Transactions | Olive | /admin/transactions |
| 3 | Share Records | Teal | /admin/share-records |
| 4 | SACCO Cycles | Navy | /admin/cycles |

### Row 4: Recent Activity (2 Tables)
| Table | Records | Columns |
|-------|---------|---------|
| Recent Contribution Programs | 5 | Name, Type, Status, Expected, Collected, Balance |
| Unpaid Contributions | 10 | Member, Program, Period, Due Date, Amount |

### Row 5: Analytics (2 Tables)
| Table | Records | Columns |
|-------|---------|---------|
| Top Contributors | 10 | Rank, Member, Records Paid, Total Paid |
| 6-Month Trends | 6 | Month, Expected (K), Collected (K), Rate |

---

## 🔐 Access Control

| User Role | Sees |
|-----------|------|
| Admin | All SACCOs data |
| SACCO User | Own SACCO only |

---

## 💰 Money Format

| Amount | Display |
|--------|---------|
| 1,500,000,000 | 1.5B |
| 25,300,000 | 25.3M |
| 500,500 | 500.5K |
| 950 | 950 |

---

## 🎨 Color Meanings

| Color | Meaning | Usage |
|-------|---------|-------|
| Green | Success/Positive | Active, Collected, Paid |
| Red | Alert/Negative | Overdue, Outstanding, Unpaid |
| Yellow | Warning | Expected, Pending |
| Blue | Information | Loans, Primary data |
| Purple | Records | Contribution records |
| Aqua | Members | User statistics |

---

## 🏆 Top Contributors Medals

| Rank | Medal |
|------|-------|
| 1st | 🥇 Gold |
| 2nd | 🥈 Silver |
| 3rd | 🥉 Bronze |

---

## 📈 Collection Rate Colors

| Rate | Color | Label |
|------|-------|-------|
| ≥ 80% | Green | success |
| 50-79% | Yellow | warning |
| < 50% | Red | danger |

---

## 🔄 Key Calculations

### Collection Rate
```
(total_collected / total_expected) × 100
```

### Payment Rate
```
(paid_records / total_records) × 100
```

### Outstanding Balance
```
total_expected - total_collected
```

### Days Overdue
```
today - period_range_start
```

---

## 📊 Database Models

| Model | Table | Usage |
|-------|-------|-------|
| User | users | Members |
| ContributionProgram | contribution_programs | Programs |
| ContributionProgramRecord | contribution_program_records | Individual records |
| Loan | loans | Loan portfolio |
| Transaction | transactions | Financial transactions |
| ShareRecord | share_records | Share ownership |
| Cycle | cycles | SACCO cycles |
| Sacco | saccos | SACCO information |

---

## ⚡ Performance Tips

### Query Optimization
```php
// ✅ Good - Database aggregation
$total = Model::sum('amount');

// ❌ Bad - PHP aggregation
$records = Model::all();
$total = $records->sum('amount');
```

### Eager Loading
```php
// ✅ Good - 1 query
$records = Record::with('member')->get();

// ❌ Bad - N+1 queries
$records = Record::all();
foreach ($records as $r) {
    $name = $r->member->name;
}
```

---

## 🧪 Testing URLs

### Admin User Test
```
Login: admin@admin.com
Should see: "SACCO Dashboard"
Data: All SACCOs combined
```

### SACCO User Test
```
Login: [sacco_user]@email.com
Should see: "[SACCO Name] - Dashboard"
Data: Only that SACCO
```

---

## 📝 Quick Edits

### Change Recent Programs Limit
**File**: Line 379  
**Current**: `->limit(5)`  
**Change to**: `->limit(10)`

### Change Unpaid Records Limit
**File**: Line 410  
**Current**: `->limit(10)`  
**Change to**: `->limit(20)`

### Change Top Contributors Limit
**File**: Line 456  
**Current**: `->limit(10)`  
**Change to**: `->limit(20)`

### Change Trends Period
**File**: Line 491  
**Current**: `for ($i = 5; $i >= 0; $i--)`  
**Change to**: `for ($i = 11; $i >= 0; $i--)` (12 months)

---

## 🐛 Common Issues

### Issue: isRole() undefined
**Cause**: IDE linter doesn't recognize Laravel-Admin method  
**Solution**: This is a false positive - code works fine  

### Issue: No data showing
**Cause**: SACCO filter not set  
**Solution**: Check `$sacco_id` is being passed correctly  

### Issue: Slow loading
**Cause**: Too many records, no caching  
**Solution**: Reduce limits, add caching  

---

## 📚 Documentation

- **Full Features**: `DASHBOARD_FEATURES.md`
- **Implementation**: `DASHBOARD_IMPLEMENTATION.md`
- **Code**: `app/Admin/Controllers/HomeController.php`

---

## ✅ Checklist

Before deploying:
- [ ] Test as admin user
- [ ] Test as SACCO user
- [ ] Verify all InfoBox links work
- [ ] Check calculations accuracy
- [ ] Test on mobile device
- [ ] Review performance with real data
- [ ] Backup database
- [ ] Deploy to staging first

---

**Last Updated**: October 7, 2025  
**Status**: ✅ Production Ready
