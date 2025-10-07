# MembersController Field Mapping Reference

This document explains how database field names map to their actual meanings in the mobile app.

## ⚠️ IMPORTANT: Field Names vs. Labels

**DO NOT change field names** - they are database column names.  
**Only labels** were updated to match the mobile app's actual usage.

---

## 🔄 Field Mappings (Database → Actual Usage)

### Life & Death Information
| Field Name | Database Type | Actual Label | Values | Notes |
|------------|---------------|--------------|--------|-------|
| `reg_number` | varchar | **Life Status** | 'Alive' / 'Late' | NOT registration number! |
| `cv` | date | **Date of Death** | YYYY-MM-DD | Only for deceased members |

### Family Relationship
| Field Name | Database Type | Actual Label | Values | Notes |
|------------|---------------|--------------|--------|-------|
| `website` | varchar | **Family Parent Relationship** | 'Root' / 'Father' / 'Mother' | Parent relationship type |
| `linkedin` | varchar | **Parent ID** | User ID | ID of father/mother member |
| `twitter` | varchar | **Parent Name** | Text | Name of father/mother member |

### Contribution Settings
| Field Name | Database Type | Actual Label | Values | Notes |
|------------|---------------|--------------|--------|-------|
| `language` | varchar | **Contribution Eligibility** | 'Compulsory' / 'Optional' / 'None' | NOT language spoken! |
| `personalized_contribution_amount` | decimal | **Personalized Contribution Amount (UGX)** | Numeric | Monthly contribution |
| `contribution_start_date` | date | **Contribution Start Date** | YYYY-MM-DD | When to start contributing |

### Education & Personal
| Field Name | Database Type | Actual Label | Values | Notes |
|------------|---------------|--------------|--------|-------|
| `other_link` | varchar | **Highest Level of Education** | Dropdown values | Education level |
| `country` | varchar | **Country of Residence** | Country names | Current residence |
| `id_front` | varchar | **Spouse** | Text | Spouse name (NOT ID photo!) |

### Admin & Permissions
| Field Name | Database Type | Actual Label | Values | Notes |
|------------|---------------|--------------|--------|-------|
| `is_admin` | varchar | **Make Admin** | 'Yes' / 'No' | Admin privileges |

---

## 📋 Complete Field Usage in Mobile App

### Form Fields (in order of appearance):

1. **First Name** (`first_name`) - Text
2. **Last Name** (`last_name`) - Text
3. **Family Parent Relationship** (`website`) - Radio: Father/Mother/Root
   - If Father/Mother selected:
     - **Select Father/Mother** (`twitter`) - Opens member picker, stores parent name
     - Parent ID (`linkedin`) - Auto-filled with selected parent's ID
4. **Gender** (`sex`) - Radio: Male/Female
5. **Date of Birth** (`dob`) - Date picker
6. **Life Status** (`reg_number`) - Radio: Alive/Deceased
   - If Deceased:
     - **Date of Death** (`cv`) - Date picker
7. **Make Admin** (`is_admin`) - Radio: Yes/No (only for alive members)
8. **Contribution Eligibility** (`language`) - Radio: Compulsory/Optional/N/A
9. **Personalized Contribution Amount (UGX)** (`personalized_contribution_amount`) - Number
10. **Contribution Start Date** (`contribution_start_date`) - Date picker
11. **Phone Number** (`phone_number`) - Phone input
12. **Highest Level of Education** (`other_link`) - Dropdown
13. **Country of Residence** (`country`) - Dropdown
14. **Address** (`address`) - Text area
15. **Spouse** (`id_front`) - Text (labeled as "Spunce" in mobile - possible typo)
16. **Photo** (`avatar`) - Image upload

---

## 🔍 Mobile App Logic

### Deceased Members:
- When `reg_number` = 'Late':
  - `language` (Contribution Eligibility) is automatically set to 'None'
  - Admin options are hidden
  - Date of death field appears

### Parent Relationship:
- When `website` = 'Father':
  - Member picker opens filtered to Male members
  - Selected member's ID stored in `linkedin`
  - Selected member's name stored in `twitter`
- When `website` = 'Mother':
  - Member picker opens filtered to Female members
  - Selected member's ID stored in `linkedin`
  - Selected member's name stored in `twitter`

### Contribution Settings:
- Only shown for alive members (`reg_number` = 'Alive')
- When `language` = 'None': contribution fields hidden
- When `language` = 'Compulsory' or 'Optional': shows amount and start date

---

## 📊 Grid Display (Admin Panel)

### Visible Columns:
- ID
- Photo (avatar)
- First Name + Last Name (combined)
- Gender (with color dots)
- Phone Number
- Life Status (Alive=green, Deceased=red)
- User Type
- Membership Status
- Account Status
- Date Joined
- SACCO (admin only)

### Hidden Columns (available via selector):
- Date of Birth (with age calculation)
- Email
- WhatsApp
- Address
- Country of Residence
- Balance
- Contribution Amount
- Last Active

---

## ⚙️ Backend Logic

### Saving Logic:
```php
// Auto-set contribution eligibility to None for deceased
if ($form->reg_number == 'Late') {
    $form->language = 'None';
}

// Auto-generate username from phone
if (empty($form->username)) {
    $form->username = $form->phone_number ?? 'user_' . time();
}

// Auto-generate full name
$form->name = trim($form->first_name . ' ' . $form->last_name);
```

### Validation Rules:
- **phone_number**: Required, unique
- **email**: Optional, unique (if provided)
- **first_name**: Required, 2-100 characters
- **last_name**: Required, 2-100 characters
- **sex**: Required
- **reg_number**: Required (Life Status)
- **is_admin**: Required
- **language**: Required (Contribution Eligibility)

---

## 🚫 Unused Fields in Mobile App

These fields are NOT used in the mobile app registration:

- `facebook` - Hidden
- `profile_photo` - NOT used (different from avatar)
- `profile_photo_large` - NOT used
- `id_back` - NOT used
- `intro` - Only in admin panel
- `about` - Only in admin panel
- `title` - Only in admin panel
- `occupation` - Only in admin panel
- `location_lat` - Only in admin panel
- `location_long` - Only in admin panel

---

## 📝 Notes for Developers

1. **Never rename database columns** - other parts of the system may depend on them
2. **Only update labels** in the admin controller to match mobile app
3. **Field repurposing is intentional** - the mobile app reuses fields for different purposes
4. **Parent data** (linkedin, twitter) is auto-filled from mobile app's member picker
5. **Life status** (reg_number) controls visibility of many other fields
6. **Contribution eligibility** (language) is auto-set to 'None' for deceased members

---

## 🔧 Testing Checklist

- [ ] Create member from admin panel
- [ ] Create member from mobile app
- [ ] Verify parent selection works
- [ ] Verify deceased member sets contribution to None
- [ ] Verify all validations work
- [ ] Verify data syncs correctly between admin and mobile
- [ ] Test edit existing member
- [ ] Test photo upload
- [ ] Test filters in grid
- [ ] Export data and verify labels

---

**Last Updated**: October 6, 2025  
**Laravel Controller**: `/app/Admin/Controllers/MembersController.php`  
**Flutter Form**: `AccountEdit` (lib/screens/account/account_edit.dart)
