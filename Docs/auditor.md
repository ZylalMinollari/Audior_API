# Auditor API Documentation

Welcome to the Auditor API documentation. This API provides endpoints for managing auditors, allowing registration, login, logout, retrieval, update, and deletion of auditor records. Below you will find details on how to interact with each endpoint.

## Base URL

The base URL for accessing the Auditor API is:

http://127.0.0.1:8000/api/auditor/

## Endpoints

1. **Register**

   - **URL:** `/register`
   - **Method:** POST
   - **Description:** Register a new auditor.
   - **Request Body:** See documentation for details.
   - **Response:**
     - **Status:** 201 Created
     - **Body:** 
       ```json
       {
           "message": "Auditor registered",
           "username": "example_username"
       }
       ```

2. **Login**

   - **URL:** `/login`
   - **Method:** POST
   - **Description:** Log in an auditor.
   - **Response:**
     - **Status:** 200 OK
     - **Body:**
       ```json
       {
           "message": "Login successful"
       }
       ```

3. **Logout**

   - **URL:** `/logout`
   - **Method:** POST
   - **Description:** Log out the currently logged-in auditor.
   - **Response:**
     - **Status:** 200 OK
     - **Body:**
       ```json
       {
           "message": "Logout successful"
       }
       ```

4. **Get List of Auditors**

   - **URL:** `/index`
   - **Method:** GET
   - **Description:** Retrieve a paginated list of auditors.
   - **Response:**
     - **Status:** 200 OK
     - **Body:** Array of auditor objects with the following properties:
       - `id` (integer): The unique identifier of the auditor.
       - `name` (string): The name of the auditor.
       - `username` (string): The username of the auditor.
       - `timezone` (string): The timezone of the auditor.

5. **Get Auditor Details**

   - **URL:** `/show/{id}`
   - **Method:** GET
   - **Description:** Retrieve details of a specific auditor.
   - **Response:**
     - **Status:** 200 OK
     - **Body:** Auditor object with the following properties:
       - `id` (integer): The unique identifier of the auditor.
       - `name` (string): The name of the auditor.
       - `username` (string): The username of the auditor.
       - `timezone` (string): The timezone of the auditor.

6. **Update Auditor Details**

   - **URL:** `/{id}/edit`
   - **Method:** POST
   - **Description:** Update details of a specific auditor.
   - **URL Parameters:** id.
   - **Response:**
     - **Status:** 200 OK
     - **Body:**
       ```json
       {
           "message": "Auditor updated",
           "id": 1
       }
       ```

7. **Delete Auditor**

   - **URL:** `/delete/{id}`
   - **Method:** DELETE
   - **Description:** Delete a specific auditor.
   - **URL Parameters:** See documentation for details.
   - **Response:**
     - **Status:** 200 OK
     - **Body:**
       ```json
       {
           "message": "Auditor deleted",
           "username": "example_username"
       }
       ```

## Authentication

Authentication for the API is handled via sessions. When logging in, the API sets a session cookie containing the auditor's ID. This session cookie is required for accessing certain endpoints like logout and delete.

## Error Handling

The API returns appropriate HTTP status codes and error messages for invalid requests or failed operations.

## Notes

- All requests and responses are in JSON format.
- This API utilizes Symfony for server-side implementation.

## Example Usage

To register a new auditor:

```json
POST /api/auditor/register
Content-Type: application/json

{
    "name": "John Doe",
    "username": "johndoe",
    "password": "examplepassword",
    "timezone": "2024-04-05T12:00:00+01:00"
}
```