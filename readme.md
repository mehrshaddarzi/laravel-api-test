# Blegrator Code-Base

Blegrator code-base for user management

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

What things you need to install the software and how to install them

- php 7.2 <=
    - gd 
    - sqlite 
    - bcmath  
    - mbstring 
    - dom 
- mysql
- laravel 6.0.0
- composer

### Installing

A step by step series of examples that tell you how to get a development env running

Clone into repository and run

```
composer install
```

And

```
cp .env.example .env
```

Then Run

```
php artisan serve
```
Follow instruction on web user interface to create `.env` file for your project.


## Running the tests

for running unit test, at the root of the project run:

```
./vendor/phpunit/phpunit/phpunit 
```

### And coding style tests

PSR2 has been implemented as coding style. For more information about PSR2 please check here: [PSR2](https://www.php-fig.org/psr/psr-2/).
For the purpose of code linting, `phpcs` has been used. `phpcs` config file locates at root of project and named `phpcs.xml`.
For checking coding styles run:

```
phpcs
```
be aware that the `phpcs` should have installed and globally registered in your OS path.
