This is a demo app created for codemaster challenge.

# Install Composer
    curl -sS https://getcomposer.org/installer | php



run the Composer command to install the application:

    php composer.phar install


add a .env file with following params:
    
    DATABASE_URL="mysql://root:@127.0.0.1:3306/code_masters?serverVersion=5.7"


please update the db name, db user and password accordingly


create the database:


you can start using the application by importing the sql file provided in the repo:
code_master.sql

start temporary server

    symfony server:start

you can start the server in background:

    symfony server:start -d
    
Access api doc from here:
    https://127.0.0.1:8000/api/doc
 
 How to plpay with the application:
 
 1. First register an Agent(user):
 
     https://127.0.0.1:8000/auth/register
     
 2. Get JWT token:
 
     https://127.0.0.1:8000/api/login_check
 
 3. Creat Room:
 
    https://127.0.0.1:8000/api/api/rooms/
 
 4. Create Customer:
     https://127.0.0.1:8000/api/customer/
 
 All the endpoints are provided in the postman collection, you cna find in the repo 