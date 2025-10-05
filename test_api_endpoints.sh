#!/bin/bash

# Test Live API Endpoints
# Run this script to verify all endpoints work correctly

echo "========================================"
echo "Testing Live API Endpoints"
echo "========================================"
echo ""

# Base URL
BASE_URL="http://127.0.0.1:8888/marcci-dashboard/api"

# First, we need to login to get a token
echo "1. Logging in to get authentication token..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "admin",
    "password": "admin"
  }')

# Extract token (this is a simplified extraction - adjust based on your actual response structure)
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "❌ Failed to get authentication token"
    echo "Response: $LOGIN_RESPONSE"
    echo ""
    echo "Please update the username/password in this script or use a test user"
    exit 1
fi

echo "✅ Token obtained successfully"
echo ""

# Test Dashboard
echo "========================================"
echo "2. Testing Dashboard Endpoint"
echo "========================================"
DASHBOARD_RESPONSE=$(curl -s -X GET "$BASE_URL/live/dashboard" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "$DASHBOARD_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$DASHBOARD_RESPONSE"
echo ""

# Test Loans
echo "========================================"
echo "3. Testing Loans Endpoint"
echo "========================================"
LOANS_RESPONSE=$(curl -s -X GET "$BASE_URL/live/loans?page=1&per_page=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "$LOANS_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$LOANS_RESPONSE"
echo ""

# Check for status field in first loan
echo "Checking for 'status' field in loan data..."
if echo "$LOANS_RESPONSE" | grep -q '"status"'; then
    echo "✅ 'status' field found in response!"
else
    echo "❌ 'status' field NOT found in response"
fi
echo ""

# Test Loans with Overdue Filter
echo "========================================"
echo "4. Testing Loans with Overdue Filter"
echo "========================================"
OVERDUE_RESPONSE=$(curl -s -X GET "$BASE_URL/live/loans?page=1&per_page=5&overdue=true" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "$OVERDUE_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$OVERDUE_RESPONSE"
echo ""

# Test Transactions
echo "========================================"
echo "5. Testing Transactions Endpoint"
echo "========================================"
TRANS_RESPONSE=$(curl -s -X GET "$BASE_URL/live/transactions?page=1&per_page=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "$TRANS_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$TRANS_RESPONSE"
echo ""

# Test Contribution Programs
echo "========================================"
echo "6. Testing Contribution Programs Endpoint"
echo "========================================"
CONTRIB_RESPONSE=$(curl -s -X GET "$BASE_URL/live/contribution-programs?page=1&per_page=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "$CONTRIB_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$CONTRIB_RESPONSE"
echo ""

echo "========================================"
echo "Testing Complete!"
echo "========================================"
echo ""
echo "Summary:"
echo "- All endpoints should return success: true"
echo "- Loans should include 'status' field"
echo "- No SQL errors about missing 'status' column"
echo "- HTTP codes should be 200"
echo ""
