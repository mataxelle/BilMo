﻿# BilMo

## Table of contents

*  [General info](#general-info)
*  [Features](#features)
*  [Development environment](#development-environment)
*  [Install on local](#install-on-local)
*  [Start the environment](#start-the-environment)
*  [Documentation](#documentation)
*  [Testing the API](#testing-the-api)

## General info

Project : BilMo is a company that offers a wide selection of high-end mobile phones.

Developing the mobile phone catalog for BileMo. BilMo's business model is not to directly sell its products on the website but to provide access to the catalog via an API (Application Programming Interface) to any platforms that want it. Therefore, it is exclusively B2B (business-to-business) sales.

## Development environment

* PHP 8.1.10
* Symfony CLI
* Composer

## Requirements check

* symfony check:requirements

## Install on local

1. Clone the repo on your local webserver : [Repository](https://github.com/mataxelle/BilMo.git).

2. Make sure you have Php and composer on your computer.

3. Create a .env.local file at the root of your project, same level as .env, and configure the appropriate values for your project to run.

```
#Database standard parameters

DATABASE_URL='your database'

#Lexik standard parameters

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE='your passphrase'
```
4. Create a database run :

```
php bin/console doctrine:database:create
```
5. Generate database schema run :

```
php bin/console make:migration

php bin/console doctrine:migrations:migrate
```
6. Load fixtures run :

```
symfony console doctrine:fixtures:load
```
7. Generate SSH JWT key

Create a folder named "jwt" inside the config folde and run :

```
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa
_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem
 -pubout
```
A "passphrase" will be requested from you. This passphrase will essentially act as the key for encoding/decoding the token. It must remain confidential!

8. Try to connect as an admin with : `admin@email.com` `azertyuiop`


## Start the environment

```
Composer install
symfony server:start
```

## Documentation

* http://localhost:8000/api/doc

The documentation provides you with examples & the list of available API endpoints.
You can test out the API directly.

## Testing the API

However, you can use [Postman](https://www.postman.com/) or other online clients to test the API as it greatly simplifies the creation and sending of HTTP requests, allowing you to quickly verify and validate the proper functioning of the API.
