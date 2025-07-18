# Docker development setup process for the Applications

## 1. Clone the api-testing-platform in your machine and be located in the root directory of this repository.
## 2. Start Docker.
## 3. Go to the docker directory of api-testing-platform repository and follow these steps:
    1. Copy .env.example to .env and make your customizations if needed 
    2. Execute: docker compose up --build
## 4. Go to the backend directory of api-testing-platform repository and follow these steps:
    1. Copy .env.example to .env and make your customizations if needed 
    2. Open terminal in the backend application root
    3. Execute: docker exec -it <backend_container_name> php artisan key:generate
    4. Execute: docker exec -it <backend_container_name> php artisan config:cache

## 5. Go to the frontend directory of api-testing-platform repository and follow these steps:
    1. Copy .env.example to .env and make your customizations if needed 
    2. No need to restart the container since the updates are directly reloaded

## The projects can be reached with:
    Frontend: localhost:50000
    Backend: localhost:8888
 