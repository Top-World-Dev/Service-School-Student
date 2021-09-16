# Xamlinx Backend

This project was generated with [Codeigniter 4.1.1](https://codeigniter.com/user_guide/intro/index.html) and [CycleORM](https://cycle-orm.dev/docs/intro-quick-start).

## What is Xamlinx?

About Xamlinx

## Prerequisites

1. Install apache
2. Install MySQL Server
3. Create database

## Backend Setup

1. Copy `env` to `.env` in project root directory.
```bash
cp env .env
```
2. Set environment ro `development` or `production` in `.env` file.
```bash
CI_ENVIRONMENT = production
```
or
```bash
CI_ENVIRONMENT = development
```
3. Set base url and front url.
```bash
app.baseURL = '<WEBSITE_URL>'
app.frontURL = '<FRONT_URL>'
```
4. Set database configuration
```bash
database.default.hostname = localhost
database.default.database = <YOUR_DB_NAME>
database.default.username = <YOUR_DB_USERNAME>
database.default.password = <YOUR_DB_PASSWORD>
database.default.DBDriver = MySQLi
```
5. Set cloudinary keys
```bash
CLOUDINARY_NAME=<YOUR_CLOUDINARY_NAME>
CLOUDINARY_API_KEY=<YOUR_CLOUDINARY_API_KEY>
CLOUDINARY_API_SECRET=<YOUR_CLOUDINARY_API_SECRET>
CLOUDINARY_URL=cloudinary://<YOUR_CLOUDINARY_API_KEY>:<YOUR_CLOUDINARY_API_SECRET>@<YOUR_CLOUDINARY_NAME>
```
6. Set stripe key
```bash
stripe.secret_key=<YOUR_STRIPE_SECRET_KEY>
```
7. Set api url in `app/Config/App.php`.
```bash
public $baseURL = '<BASE_URL>';
```
8. Run following command to migrate database tables in project directory
```bash
php spark migrate
```
9. Run following command to import database from sql files (`sql/1country.sql` and `sql/2school.sql`)
```bash
mysql -u <YOUR_DB_USERNAME> -p <YOUR_DB_NAME> < sql/1country.sql
mysql -u <YOUR_DB_USERNAME> -p <YOUR_DB_NAME> < sql/2school.sql
```
10. Run follwing command to populate default data
You can change administrator's email and password in `app/Database/Seeds/UserSeeder.php`
```bash
'email' => '<ADMIN_EMAIL>',
'password' => password_hash('<ADMIN_PASSWORD>', PASSWORD_BCRYPT),
```
If everything is set, run the command.
```bash
php spark db:seed DatabaseSeeder
```

## Cloudinary Setup

Create a folder named `temp_quest`

## Stripe Setup

Set a webhook endpoint in [Stripe Dashboard](https://dashboard.stripe.com/webhooks)
```bash
<WEBSITE_URL>/api/hook/stripe
```
Add following events.
```bash
checkout.session.async_payment_succeeded
checkout.session.async_payment_failed
checkout.session.completed
```

## Server Requirements

PHP version 7.3 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php)
- xml (enabled by default - don't turn it off)
