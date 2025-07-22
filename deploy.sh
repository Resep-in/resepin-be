cp .env.example .env
docker compose build

docker run --rm -it --volume $(pwd):/app resepin-be composer install --no-dev --optimize-autoloader
docker run --rm -it --volume $(pwd):/app resepin-be php artisan key:generate

# up composer
docker compose up -d
docker compose exec api php artisan migrate
docker compose ps