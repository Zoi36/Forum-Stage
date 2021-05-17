# Forum-trainingship
This is a project of a discussion forum with the features of Symfony (Controllers, Routing, Templating, Forms, Validation, Doctrine, Security).

##Possible so far
* Registration
* Form-Login
* creating categories
* creating conversations in categories
* creating messages in conversations
* pagination of messeges and conversations
* different roles
    * the user-role is by default 'ROLE_USER'
    * to create a user with the user-role 'ROLE_ADMIN' register with the username 'admin'
    * admins may access the admin-panel
    * admins can create categories
    * admins can delete messages, conversations and categories



##Installation
* setup database connection in the parameters.yml
* composer install
* app/console doctrine:database:create
* app/console doctrine:schema:update --force
* assets:install
* register with the username 'admin' to create categories on the admin panel

