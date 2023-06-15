## Deployment instructions

### Deployment back-end
- `copy back/.env.example back/.env`
- `docker-compose up -d`
- `docker compose run php composer install`
- `docker compose run php artisan migrate`

### Deployment front-end
- `npm i`
- `npm run dev`

// ngrok http 8000 - добавить получившийся адресс в BACKAND_URL front/src/main.js
// ssh -R 80:localhost:5173 nokey@localhost.run - выдаст нестабильную ссылку
