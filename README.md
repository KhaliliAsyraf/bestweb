# Product Management System

> ⚠️ **Important:** Due to a tight schedule, the progress will continue soon. The backend engine is **100% covered**, and the overall project is about **85% complete**.

---

## Tech Stack

- **Backend:** Laravel  
- **Testing:** PHPUnit  
- **API Docs:** Swagger (to add soon)  

---

## Setup Instructions

1. Ensure **Docker** is installed on your local machine.  
2. Run the following command to start the project:

```bash
docker-compose up -d --build
```
3. Swagger API endpoints will be added soon.
4. Authentication:
- Start with the login endpoint to get the token.
- Credentials:
    - Email: test@example.com
    - Password: password
- CLI-friendly login commands may be used later (not included yet due to tight schedule).

---

## Highlights / Key Features

1. Rate Limiter – Protects endpoints from abuse.

2. Atomic Lock – Uses cache to handle concurrent requests safely.

3. Cursor Pagination – Optimized and faster than typical pagination.

4. Cached Data – Provides faster response (feature to be added soon).

5. Dependency Injection – Clean, maintainable code.

6. PHPUnit – Backend is covered by automated tests.

7. Single Responsibility Pattern – Each class/service has one clear responsibility.

8. Docker – Containerized environment for consistent setup.