# Schedule API Documentation

## Base URL

http://127.0.0.1:8000/api/schedule/


## Endpoints

### 1. Create a New Schedule

- **URL:** `/new`
- **Method:** `POST`
- **Description:** Creates a new schedule for an auditor and a job.
- **Request Body:**
  - `auditor_id` (string, required): Username of the auditor.
  - `job_id` (string, required): Title of the job.
  - `assigned_date` (string, required): Assigned date in the format 'Y-m-d\TH:i:s'.
  - `completion_date` (string, required): Completion date in the format 'Y-m-d\TH:i:s'.
- **Response:**
  - `201 Created` - Schedule created successfully.
  - `400 Bad Request` - If validation fails or if the job completion time has already passed for the auditor.
  - `404 Not Found` - If auditor or job not found.

### 2. Retrieve Schedules

- **URL:** `/index`
- **Method:** `GET`
- **Description:** Retrieves a list of schedules with pagination.
- **Query Parameters:**
  - `page` (integer, optional): Page number for pagination (default is 1).
- **Response:**
  - `200 OK` with JSON payload containing the list of schedules.

### 3. Show Schedule Details

- **URL:** `/show/{id}`
- **Method:** `GET`
- **Description:** Retrieves details of a specific schedule by ID.
- **Path Parameter:**
  - `id` (integer, required): ID of the schedule.
- **Response:**
  - `200 OK` with JSON payload containing schedule details.
  - `404 Not Found` if schedule not found.

### 4. Edit Schedule

- **URL:** `/{id}/edit`
- **Method:** `POST`
- **Description:** Edits an existing schedule.
- **Path Parameter:**
  - `id` (integer, required): ID of the schedule to be edited.
- **Request Body:**
  - Same as the request body for creating a new schedule.
- **Response:**
  - `200 OK` - Schedule updated successfully.
  - `400 Bad Request` - If validation fails.
  - `404 Not Found` - If schedule not found.

### 5. Delete Schedule

- **URL:** `/delete/{id}`
- **Method:** `DELETE`
- **Description:** Deletes a schedule by ID.
- **Path Parameter:**
  - `id` (integer, required): ID of the schedule to be deleted.
- **Response:**
  - `200 OK` - Schedule deleted successfully.
  - `400 Bad Request` - If the job is still in progress.
  - `404 Not Found` - If schedule not found.


### Creating a new schedule (POST /new)
```json
{
  "auditor_id": "auditor_username",
  "job_id": "job_title",
  "assigned_date": "2024-04-03T10:00:00", // can be nullable
  "completion_date": "2024-04-05T15:00:00" //can be nullbale
}
```

### Editing a new schedule (POST /{id}/edit)
```json
{
  "auditor_id": "auditor_username",
  "job_id": "job_title",
  "assigned_date": "2024-04-03T10:00:00",
  "completion_date": "2024-04-05T15:00:00"
}
```
