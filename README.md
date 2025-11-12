# Product Management System

> ⚠️ **Important:** Due to a tight schedule, the progress will continue soon. The backend engine is **100% covered**, and the overall project is about **95% complete**.

---

## Tech Stack

- **Backend:** Laravel  
- **Testing:** PHPUnit  
- **API Docs:** Swagger

---

## Setup Instructions

1. Ensure **Docker** is installed on your local machine.  
2. Run the following command to start the project:

```bash
docker-compose up -d --build
```

> ⚠️ Note: Docker has issues running two environments simultaneously. For PHPUnit testing, it is recommended to test locally on your machine. Ensure PHP and project dependencies are set up properly on your local environment, and create your own .env.testing file with APP_ENV=testing to run tests safely without affecting the Docker environment.

3. Authentication:
- Start with the login endpoint to get the token.
- Credentials:
    - Email: test@example.com
    - Password: password
- For a developer-friendly approach, a custom Artisan command is ready:
    ```bash
    php artisan app:sample-token
    ```
4. Swagger Documentation
- Access Swagger at: http://localhost:8085/api/documentation
- You can get a token either via the login endpoint in Swagger or the custom Artisan command above.
- Copy the token and authorize the API using Bearer Token in Swagger UI.
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