# myfirstDBproject

For using: git clone git@github.com:foxy-vik/myfirstDBproject.git




# This project was created on Docker4Drupal  - Drupal 9 Without Composer Installation

This is a sample Drupal 9 with Composer installation pre-configured for use with Docksal.

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
 ```
    drush config-set "system.site" uuid "fe7ea394-0677-4156-969d-5b33afb70dee"

 ```


2. Initialize the site

    This will initialize local settings and install the site via drush

    ```
    docker-compose up -d
    ```
   A `composer.lock` file will be generated. This file should be committed to your repository.
3. Compilation of styles


    ```
    npm init
    npm install webpack --save-dev
    npm install webpack-cli --save-dev

    npm i css-loader sass-loader node-sass mini-css-extract-plugin --save-dev
    ```
  once run
      ```
      npm run build
      ```

5. Point your browser to

    ```
    http://drupal9.docksal
    ```

When the automated install is complete the command line output will display the admin username and password.


## More automation with 'fin init'

Site provisioning can be automated using `fin init`, which calls the shell script in [.docksal/commands/init](.docksal/commands/init).
This script is meant to be modified per project. The one in this repo will give you a good starting example.

Some common tasks that can be handled by the init script (an other [custom commands](https://docs.docksal.io/fin/custom-commands/)):

- initialize local settings files for Docker Compose, Drupal, Behat, etc.
- import DB or perform a site install
- compile Sass
- run DB updates, revert features, clear caches, etc.
- enable/disable modules, update variables values

