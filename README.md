# covid_vaccine_bcn
PHP CLI project to get an appointment from https://vacunacovid.catsalut.gencat.ca

# Installation
In order to run this project, you need PHP > 7 and Composer, once you've downloaded the project you can execute
```sh
  composer install
```

# Steps
* Go to https://vacunacovid.catsalut.gencat.ca
* Open your dev tools (right click on the page -> Inspect) and go to the Network tab and select only XHR
* Fill the form with your data
* Request and enter the SMS code
* And Click on "Vacunarme en un centro masivo"
* On the Network tab (dev tools) you will see a request to "centers" [https://frontdoornodepro.azurefd.net/sf/centers]
  * From this request copy the Request headers and put it on the config/users.json file:
    *   x-token
    *   x-queue-token
    *   x-auth-token
    *   cip  
    
    You can add as many users as you want, but you need to do the previous process for each person that need an appointment

# Config
  * config/users.json  Users that need an appointment
  * config/centers.php Centers that will be used to get the appointment, add more centers if you want to have more chances. For that you will need to know the ID of the center. You can see it during the iterations on the terminal. You will see a message like: 
  ```sh 
  No centers available at the moment matching your criteria:
        Sant Antoni de Calonge (50)
```

# Execution

Now that we have everything run the script bin/vaccine.php
```sh
php bin/vaccine.php
```

The script will run forever until it finds an appointment for all users. The script is basically a periodic timer that's executed every 15s inside a ReactPHP loop

