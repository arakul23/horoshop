Test task for the position of a software engineer.

### Install

```
docker compose build
docker compose up -d
```

```Composer install``` and ```php bin/console doctrine:migrations:migrate``` will also be executed, ```.env``` will be created if it doesn't exist.
  
Access the application at http://localhost:8082  


After launch, it may take 10-15 seconds for the migration and dump to complete.

### Authentication:  
The application uses Bearer Token authentication.

Define your ```APP_TOKEN_SECRET``` in the ```.env```.

Use this secret to sign your tokens for API requests.  

### Tests  
```docker compose exec -it php php bin/phpunit```
