## Mini Wallet Application "ZapWallet"

A simplified digital wallet application that allows users to transfer money to each other.

## Link to Zapwallet Frontend in vuejs

https://github.com/sushantaryal/zapwallet-vue

## Project Setup

Clone repository

```sh
git clone https://github.com/sushantaryal/zapwallet zapwallet-backend
```

```sh
composer install
```

### Environment Variables

Copy `.env.example` to `.env` and update the values.

```
BROADCAST_CONNECTION=pusher
```

and fill these values in the `.env` file

```
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:5173

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
PUSHER_PORT=443
PUSHER_SCHEME=https
```

### Database Setup

```sh
php artisan migrate --seed
```

### Running the Application

```sh
php artisan serve
```

## Open new terminal and run

```sh
php artisan queue:work
```
