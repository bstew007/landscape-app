# Mobile Timesheet API Documentation

## Overview
RESTful API endpoints for the React Native mobile app to manage timesheets in the field.

**Base URL**: `http://your-domain.com/api/mobile`  
**Authentication**: Laravel Session Auth (same as web app)  
**Response Format**: JSON

---

## Endpoints

### 1. Get My Jobs
Get all active jobs assigned to the authenticated user.

**Endpoint**: `GET /api/mobile/my-jobs`  
**Auth Required**: Yes

**Response**:
```json
{
  "success": true,
  "jobs": [
    {
      "id": 1,
      "job_number": "2025-001",
      "title": "Smith Residence - Backyard Renovation",
      "status": "in_progress",
      "client_name": "John Smith",
      "address": "123 Main St, City, State 12345",
      "scheduled_start": "2025-12-01",
      "scheduled_end": "2025-12-15",
      "work_areas": [
        {
          "id": 1,
          "name": "Paver Patio",
          "description": "200 sq ft patio installation"
        }
      ],
      "active_timesheet": {
        "id": 5,
        "work_area_id": 1,
        "work_area_name": "Paver Patio",
        "clock_in": "2025-12-01T08:00:00-05:00",
        "elapsed_seconds": 14400
      }
    }
  ]
}
```

---

### 2. Get My Timesheets
Retrieve timesheet history for authenticated user.

**Endpoint**: `GET /api/mobile/my-timesheets`  
**Auth Required**: Yes

**Query Parameters**:
- `status` (optional): Filter by status (draft, submitted, approved, rejected)
- `start_date` (optional): Filter by start date (YYYY-MM-DD)
- `end_date` (optional): Filter by end date (YYYY-MM-DD)

**Response**:
```json
{
  "success": true,
  "timesheets": [
    {
      "id": 5,
      "job_number": "2025-001",
      "job_title": "Smith Residence - Backyard Renovation",
      "work_area": "Paver Patio",
      "work_date": "2025-12-01",
      "clock_in": "2025-12-01T08:00:00-05:00",
      "clock_out": "2025-12-01T16:30:00-05:00",
      "break_minutes": 30,
      "total_hours": 8.0,
      "status": "approved",
      "notes": "Completed base preparation",
      "rejection_reason": null
    }
  ]
}
```

---

### 3. Clock In
Start a new timesheet for a job.

**Endpoint**: `POST /api/mobile/clock-in`  
**Auth Required**: Yes

**Request Body**:
```json
{
  "job_id": 1,
  "work_area_id": 1
}
```

**Response - Success**:
```json
{
  "success": true,
  "message": "Clocked in successfully",
  "timesheet": {
    "id": 5,
    "job_id": 1,
    "work_area_id": 1,
    "clock_in": "2025-12-01T08:00:00-05:00"
  }
}
```

**Response - Already Clocked In** (400):
```json
{
  "success": false,
  "message": "You are already clocked in to a job",
  "active_timesheet": {
    "id": 4,
    "job_id": 2,
    "job_number": "2025-002",
    "clock_in": "2025-12-01T07:30:00-05:00"
  }
}
```

**Validation Errors** (422):
```json
{
  "success": false,
  "errors": {
    "job_id": ["The job id field is required."]
  }
}
```

---

### 4. Clock Out
End a timesheet session.

**Endpoint**: `POST /api/mobile/clock-out`  
**Auth Required**: Yes

**Request Body**:
```json
{
  "timesheet_id": 5,
  "break_minutes": 30,
  "notes": "Completed base preparation for patio area"
}
```

**Response - Success**:
```json
{
  "success": true,
  "message": "Clocked out successfully",
  "timesheet": {
    "id": 5,
    "clock_in": "2025-12-01T08:00:00-05:00",
    "clock_out": "2025-12-01T16:30:00-05:00",
    "break_minutes": 30,
    "total_hours": 8.0,
    "notes": "Completed base preparation for patio area"
  }
}
```

**Response - Already Clocked Out** (400):
```json
{
  "success": false,
  "message": "Already clocked out"
}
```

**Response - Unauthorized** (403):
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

---

### 5. Submit Timesheet
Submit a timesheet for foreman approval.

**Endpoint**: `POST /api/mobile/submit-timesheet`  
**Auth Required**: Yes

**Request Body**:
```json
{
  "timesheet_id": 5
}
```

**Response - Success**:
```json
{
  "success": true,
  "message": "Timesheet submitted for approval",
  "timesheet": {
    "id": 5,
    "status": "submitted",
    "total_hours": 8.0
  }
}
```

**Response - Cannot Submit** (400):
```json
{
  "success": false,
  "message": "Cannot submit timesheet without clocking out"
}
```

---

## Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created (clock in) |
| 400 | Bad Request (validation or business logic error) |
| 401 | Unauthorized (not authenticated) |
| 403 | Forbidden (not authorized for this resource) |
| 422 | Unprocessable Entity (validation errors) |
| 500 | Internal Server Error |

---

## Authentication

The mobile app should use Laravel session-based authentication (same as web). When a user logs in via the mobile app:

1. POST credentials to `/login`
2. Store session cookie
3. Include cookie in all subsequent API requests
4. Session remains active based on Laravel's session configuration

For production, consider implementing Laravel Sanctum token-based authentication for better mobile app security.

---

## Rate Limiting

API requests are subject to Laravel's default rate limiting (60 requests per minute per user).

---

## Example Usage (JavaScript/React Native)

```javascript
// Clock In
const clockIn = async (jobId, workAreaId) => {
  const response = await fetch('http://localhost:8000/api/mobile/clock-in', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    credentials: 'include', // Important for session cookies
    body: JSON.stringify({
      job_id: jobId,
      work_area_id: workAreaId,
    }),
  });
  
  const data = await response.json();
  return data;
};

// Clock Out
const clockOut = async (timesheetId, breakMinutes, notes) => {
  const response = await fetch('http://localhost:8000/api/mobile/clock-out', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    credentials: 'include',
    body: JSON.stringify({
      timesheet_id: timesheetId,
      break_minutes: breakMinutes,
      notes: notes,
    }),
  });
  
  const data = await response.json();
  return data;
};

// Get My Jobs
const getMyJobs = async () => {
  const response = await fetch('http://localhost:8000/api/mobile/my-jobs', {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
    },
    credentials: 'include',
  });
  
  const data = await response.json();
  return data.jobs;
};
```

---

## Testing with cURL

```bash
# Clock In (requires authenticated session)
curl -X POST http://localhost:8000/api/mobile/clock-in \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{"job_id": 1, "work_area_id": 1}'

# Clock Out
curl -X POST http://localhost:8000/api/mobile/clock-out \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{"timesheet_id": 5, "break_minutes": 30, "notes": "Finished foundation work"}'

# Get My Jobs
curl http://localhost:8000/api/mobile/my-jobs \
  -H "Accept: application/json" \
  -b cookies.txt
```
