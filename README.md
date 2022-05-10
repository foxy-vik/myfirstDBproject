# myfirstDBproject

For using: git clone git@github.com:foxy-vik/myfirstDBproject.git




# This project was created on Docker4Drupal  - Drupal 9 Without Composer Installation

This is a sample Drupal 9 with Composer installation pre-configured for use with Docker4Drupal.

## Setup instructions

### Step #1: Docker4Drupal environment setup


**This is a one time setup - skip this if you already have a working Docker4Drupal environment.**

Follow [Docker4Drupal environment setup instructions](https://wodby.com/docs/1.0/stacks/drupal/local/)

### Step #2: Project setup

1. Clone this repo into your Projects directory

    ```
    git clone -b stage git@github.com:foxy-vik/myfirstDBproject.git drupal9
    cd drupal9

    ```
   $settings["config_sync_directory"] = 'config/sync';

   drush config-set "system.site" uuid "fe7ea394-0677-4156-969d-5b33afb70dee"



2. Initialize the site

   This will initialize local settings

    ```
    docker-compose up -d

    composer install
    ```
   A `composer.lock` file will be generated. This file should be committed to your repository.

3. import database


    ```
    docker exec -it developSite_php bash

    drush sqlc < database.sql
    ```

4. Point your browser to

    ```
    http://drupal9.localhost
    ```

When the automated install is complete the command line output will display the admin username and password.


## More automation with 'https://wodby.com/docs/'

- initialize local settings files for Docker Compose, Drupal, Behat, etc.
- import DB or perform a site install
- compile Sass
- run DB updates, revert features, clear caches, etc.
- enable/disable modules, update variables values
