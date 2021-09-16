# Xamlinx Backend

This project was generated with [Codeigniter 4.1.1](https://codeigniter.com/user_guide/intro/index.html) and [CycleORM](https://cycle-orm.dev/docs/intro-quick-start).

## What is Xamlinx?

About Xamlinx

## Prerequisites

1. Install [Docker](https://docs.docker.com/)
2. Install [Docker Compose](https://docs.docker.com/compose/install/)

## Setup

1. Copy `.env.example` to `.env` in project root directory.
```bash
cp .env.example .env
```

## Directory Structure

1. Backend source code are inside source directory. 
2. There are two .env files in the project. The .env in root directory is for docker-compose.yml file, and the .env file in the source directory is to be used for CodeIgniter backend code.

## Backend Docker Setup Steps (to local database)

1. Configure environments in .env and source/.env files

2. Configure db credentials in .env and source/.env files

3. Create database and tables. 
	`docker-compose run spark migrate:refresh`

4. Populate tables.	
	- Run a command in order to enter bash container
	`docker-compose exec mysql bash`

	- The mysql container needs to be bound-mounted in order to import sql files from the local directory into the container. Add ./source/sql:/var/dumps in docker-compose.yml file to achieve that. 

	- Run following commands inside the mysql container in order to populate the countries and schools table within the database.
	`mysql -u username -p dbname < /var/dumps/1country.sql`
	`mysql -u username -p dbname < /var/dumps/2schools.sql`

5. Seed test data
	`docker-compose run spark db:seed DatabaseSeeder`	

6. Give 777 permission to source/writable directory.

7. Run this command
	`docker-compose up -d`  - on local environment
	`docker-compose -f docker-compose.dev.yml up -d`  - on dev server
	`docker-compose -f docker-compose.prod.yml up -d`  - on production server

## Backend Docker Setup Steps (to remote database)	

1. Configure db credentials in .env and source/.env files

2. Create database and tables. 
    `docker-compose run spark migrate:refresh`	

3. Check the connection to the remote database and available database.
	`echo "show databases" | mysql -u username -p dbname -h remote-db-host --port=3306`
	
4. Populate tables.
	- Run a command in order to enter bash container    
	`docker-compose exec mysql bash`

	- The mysql container needs to be bound-mounted in order to import sql files from the local directory into the container. Add ./source/sql:/var/dumps in docker-compose.yml file to achieve that. 

	- Run following commands inside the mysql container in order to populate the countries and schools table within the database. 
	`mysql --host=<remote-db-host> --port=3306 -u <remote-db-user> -p <remote-db-name> < /var/dumps/1country.sql`
	`mysql --host=<remote-db-host> --port=3306 -u <remote-db-user> -p <remote-db-name> < /var/dumps/2schools.sql`

5. Seed test data
	`docker-compose run spark db:seed DatabaseSeeder`	

6. Give 777 permission to source/writable directory.

7. Run this command
	`docker-compose up -d`  - on local environment
	`docker-compose -f docker-compose.dev.yml up -d`  - on dev server
	`docker-compose -f docker-compose.prod.yml up -d`  - on production server