# check if .env exists, if not copy from .env.example
if [ ! -f .env ]; then
    echo "Copying .env.example to .env"
    cp .env.example .env
else
    echo ".env already exists, skipping copy"
fi

docker compose build

docker run --rm -it --volume $(pwd):/app resepin-be composer install --no-dev --optimize-autoloader
docker run --rm -it --volume $(pwd):/app resepin-be php artisan key:generate

docker run --rm -it --volume $(pwd):/app resepin-be php artisan octane:install --server=frankenphp

# up composer
docker compose up -d
docker compose exec api php artisan migrate
docker compose ps