# NewsPortal-App

## Instructions

You will need to have **docker** and **docker-compose** installed to successfully run this project locally.
Visit `https://www.docker.com/products/docker-desktop/` to download docker
Follow the steps below to setup the project.

1. Clone the repository to your computer.
2. On your terminal, cd to the project root folder and run `cp .env.example .env` to copy the environment variables.
3. Navigate to the `.env` file and set the neccessary variables.
4. Run `docker compose build` to build the app.
5. Run `docker compose up` to start all containers.
6. Run `docker compose run --rm app composer install` to install all dependencies
7. Run `docker compose run --rm app php artisan key:generate` to generate app key
8. Run `docker compose run --rm app php artisan migrate` to run database migrations.
9. Goto http://localhost:{YOUR_PORT}/api/documentation e.g http://localhost:8085/api/documentation to view the API documentation
10. Run `docker compose run --rm app php artisan schedule:work` to start background process
11. RUn `docker compose run --rm app php artisan queue:work` to listen to any dispatched job
12. To run testcases, run `docker compose run --rm app php artisan test`

**Note**:
You can execute php artisan commands inside a container by running `docker compose exec app sh`.
You need to setup the following environment variable so that this app can fetch articles from the services
NEWSAPI_DOT_ORG_API_TOKEN=
NEWSAPI_DOT_AI_API_TOKEN=
NEWS_DATA_TOKEN=
NEW_YORK_TIMES_TOKEN=

Swagger Documentation
<img width="3250" height="1812" alt="image" src="https://github.com/user-attachments/assets/c86a64a2-b5be-4493-89a5-a576cc696b36" />
