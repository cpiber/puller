# Puller
A laravel package for broadcasting events using long-polling

## Installation


```bash
php artisan install:broadcasting  # do not install reverb, puller will be used instead
composer require as247/puller
php artisan migrate
npm i puller-js
```


## Usage
### 1. Update broadcasting driver in .env file
```dotenv
BROADCAST_DRIVER=puller
```

### 2. Configure Echo
```javascript
import Echo from 'laravel-echo'
import Puller from 'puller-js'
window.Echo = new Echo({
    broadcaster: Puller.echoConnect,
});
```

## Troubleshooting

- If Laravel does not pick up the `puller` driver, add it to your `bootstrap/providers.php`:
  
  ```php
  <?php
  
  return [
      App\Providers\AppServiceProvider::class,
      // ... your other service providers ...
      As247\Puller\PullerServiceProvider::class,
  ];
  ```

- To publish the puller configuration as well as database migrations, run this command: `php artisan vendor:publish --provider "As247\Puller\PullerServiceProvider"`
