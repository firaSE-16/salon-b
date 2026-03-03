# API Examples - Creating Tenant with Admin/Owner

## Overview
The onboarding endpoint allows superadmins to create a complete tenant setup including:
- Tenant organization
- Tenant domain
- Owner/Admin user account
- First salon
- All necessary memberships

## Endpoint
```
POST /api/onboarding/bootstrap
```

**Authentication Required:** Yes (Superadmin Bearer Token)

---

## Step 1: Login as Superadmin

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "loariftech@gmail.com",
    "password": "Admin123!@#"
  }'
```

**Response:**
```json
{
  "token": "1|CaNrpRkI0jxvedsfEObpliFzcBSugRg58pKJPILp88ef632d",
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "loariftech@gmail.com",
    "is_superadmin": true
  }
}
```

---

## Step 2: Create Tenant with Admin/Owner

### Request Body Structure

```json
{
  "tenant_name": "Salon Group A",
  "tenant_host": "salona.local",
  "owner_name": "John Owner",
  "owner_email": "john@salona.local",
  "owner_password": "SecurePassword123!",
  "owner_phone": "+1234567890",
  "first_salon_name": "Main Salon",
  "timezone": "Europe/Sarajevo"
}
```

### Required Fields:
- `tenant_name` - Name of the tenant organization (2-120 chars)
- `tenant_host` - Domain/host for the tenant (3-190 chars, e.g., "salona.local")
- `owner_name` - Full name of the owner/admin (2-120 chars)
- `owner_email` - Email address for the owner/admin
- `first_salon_name` - Name of the first salon (2-120 chars)

### Optional Fields:
- `owner_password` - Password for owner (min 8 chars). If not provided, a random password is generated
- `owner_phone` - Phone number for owner
- `timezone` - Timezone (defaults to "Europe/Sarajevo")

---

## Example: Using cURL

```bash
# Replace YOUR_SUPERADMIN_TOKEN with the token from Step 1
curl -X POST http://localhost:8080/api/onboarding/bootstrap \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SUPERADMIN_TOKEN" \
  -d '{
    "tenant_name": "Beauty Salon Group",
    "tenant_host": "beauty.local",
    "owner_name": "Sarah Manager",
    "owner_email": "sarah@beauty.local",
    "owner_password": "SecurePass123!",
    "owner_phone": "+1234567890",
    "first_salon_name": "Beauty Salon - Downtown",
    "timezone": "America/New_York"
  }'
```

### Success Response (201 Created):
```json
{
  "data": {
    "tenant": {
      "id": 2,
      "slug": "beauty-salon-group",
      "host": "beauty.local",
      "db": "krema_tenant_beauty_salon_group"
    },
    "owner": {
      "id": 2,
      "email": "sarah@beauty.local"
    },
    "salon": {
      "id": 1,
      "name": "Beauty Salon - Downtown"
    }
  }
}
```

---

## Example: Using JavaScript/Fetch

```javascript
// Step 1: Login
const loginResponse = await fetch('http://localhost:8080/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'loariftech@gmail.com',
    password: 'Admin123!@#'
  })
});

const loginData = await loginResponse.json();
const token = loginData.token;

// Step 2: Create Tenant with Admin
const onboardingResponse = await fetch('http://localhost:8080/api/onboarding/bootstrap', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({
    tenant_name: 'Beauty Salon Group',
    tenant_host: 'beauty.local',
    owner_name: 'Sarah Manager',
    owner_email: 'sarah@beauty.local',
    owner_password: 'SecurePass123!',
    owner_phone: '+1234567890',
    first_salon_name: 'Beauty Salon - Downtown',
    timezone: 'America/New_York'
  })
});

const result = await onboardingResponse.json();
console.log('Created:', result);
```

---

## Example: Using Postman

1. **Create a new POST request**
   - URL: `http://localhost:8080/api/onboarding/bootstrap`

2. **Headers:**
   - `Content-Type: application/json`
   - `Accept: application/json`
   - `Authorization: Bearer YOUR_SUPERADMIN_TOKEN`

3. **Body (raw JSON):**
```json
{
  "tenant_name": "Beauty Salon Group",
  "tenant_host": "beauty.local",
  "owner_name": "Sarah Manager",
  "owner_email": "sarah@beauty.local",
  "owner_password": "SecurePass123!",
  "owner_phone": "+1234567890",
  "first_salon_name": "Beauty Salon - Downtown",
  "timezone": "America/New_York"
}
```

---

## What Gets Created

When you call this endpoint, it automatically creates:

1. **Tenant** - A new tenant organization
2. **Tenant Domain** - Maps the host to the tenant
3. **Owner User** - A new user account (or uses existing if email exists)
4. **Tenant Membership** - Links user to tenant with OWNER role
5. **Tenant Database** - Creates a separate database for the tenant
6. **Tenant Migrations** - Runs all tenant-specific migrations
7. **First Salon** - Creates the initial salon
8. **Salon Membership** - Links owner to salon with OWNER role

---

## Error Responses

### 403 Forbidden (Not Superadmin)
```json
{
  "message": "Superadmin only"
}
```

### 422 Validation Error
```json
{
  "message": "The tenant name field is required.",
  "errors": {
    "tenant_name": ["The tenant name field is required."]
  }
}
```

### 500 Server Error
Check backend logs for details.

---

## Notes

- The `tenant_host` should be a unique domain (e.g., "salona.local", "beauty.local")
- If `owner_email` already exists, the existing user will be linked to the tenant (password won't be updated)
- If `owner_password` is not provided, a random 14-character password is generated
- The tenant database name is auto-generated from the tenant slug
- All tenant-specific migrations are automatically run

