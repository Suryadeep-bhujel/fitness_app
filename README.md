
## Step To Setup application 
### Compiles and minifies for production
```
composer install
```
 
### Add These key values in .env file or make copy of .env.example  to .env or
```
MONGO_DB_HOST=127.0.0.1
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=fitness_app
MONGO_DB_USERNAME=
MONGO_DB_PASSWORD=
```

### Run migration command
```
php artisan migrate
```
and run 
```
php artisan db:seed
```
### or Run migration with seeder command
```
php artisan migrate --seed
```
 ### Or Run Project
```
php artisan serve 
```
## Api collection is in project root directory import collection
if environment is not in collection update these fields 
```
 URL
 token
 OTP
```
