# JobApiController Documentation

## Base URL

http://127.0.0.1:8000/api/job/

## Class Description
This class represents an API controller for handling CRUD operations related to jobs.

## Class Structure
- `JobApiController` extends `AbstractController`

## Routes and Methods
### `POST /new`
- Creates a new job.
- Accepts JSON data in the request body.
- Required fields: title, description, createdAt, name, should_be_finished.
- Optional field: assessment.
- Returns JSON response with the newly created job.
- HTTP methods: GET, POST

### `GET /index`
- Retrieves a list of jobs.
- Paginated with 10 jobs per page.
- Returns JSON response with job data.
- HTTP method: GET

### `GET /show/{id}`
- Retrieves details of a specific job by ID.
- Returns JSON response with job details.
- HTTP method: GET

### `POST /{id}/edit`
- Edits an existing job.
- Accepts JSON data in the request body.
- Returns JSON response indicating successful update.
- HTTP methods: GET, POST

### `DELETE /delete/{id}`
- Deletes a job by ID.
- Checks if the job is scheduled before deletion.
- Returns JSON response indicating successful deletion.
- HTTP method: DELETE


## Usage
- Configure routes to point to appropriate controller methods.
- Ensure correct request payload format for creating and updating jobs.
- Handle error responses appropriately.

### Creating a new job (POST /api/job/new)
```json
{
    "title": "Software Engineer",
    "description": "Developing web applications",
    "createdAt": "2024-04-03T10:00:00",
    "name": "John_Doe",
    "should_be_finished": "2024-05-03T10:00:00",
    "assessment": "Excellent"
}
```

### Editing an existing job (POST /api/job/{id}/edit)
```json
{
    "title": "Software Engineer (Updated)",
    "description": "Developing web applications (Updated)",
    "createdAt": "2024-04-03T11:00:00",
    "name": "Jane_Smith",
    "should_be_finished": "2024-06-03T10:00:00",
    "assessment": "Good"
}
```