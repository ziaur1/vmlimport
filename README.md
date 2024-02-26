1. Installation: 
   1.1  Method One:
   
       - Code is in a Zip file format named Vml.zip
       - Unzip the zip file in magento_solution_directory/app/code folder 
       - Run the following commands to install the module 
          - php bin/magento setup:di:compile && php bin/magento setup:upgrade
       - Flush the cache by running 
          - php bin/magento cache:flush
       - Place the JSON and CSV files at following path 
          - magento_solution_directory/var/import/ 

  1.2. Method Two: 
  
      - Please execute the following two commands in order to set Composer to accept dev releases
        - composer config minimum-stability dev
        - composer config prefer-stable true
      - For the installation of the module, please run
        - composer require ziaur1/vmlimport (It will install the module under directory magento_solution_director/vendor)
      - Once installed, please run
        - php bin/magento setup:di:compile && php bin/magento setup:uphgrade && php bin/magento module:enable Vml_Import
      - Flush the cache by running following command
        - php bin/magento cache:flush 

2. Usage
 - Please run the following commands to import data from given sample files 
 - For JSON Profile 
   - `php bin/magento customer:import sample-json var/import/sample.json`
  - For CSV Profile
    -`php bin/magento customer:import sample-csv var/import/sample.csv`




