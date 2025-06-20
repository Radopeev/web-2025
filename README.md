# web-2025

## run project

> [!IMPORTANT]
> If you wish to run the project from another folder, make sure to update the `BASE_PATH` constant in the `config/constants.php`
> 

### manually
1. Have php installed.
   - on Windows: https://www.geeksforgeeks.org/how-to-install-php-in-windows-10/
      - you need to edit `php.ini` to uncomment `extension_dir = "ext"` and `extension=mysqli` lines. (If it's missing make a copy of `php.ini-development` and rename it).
   - alternatively uif you have chocolatey: choco install php (download from here https://chocolatey.org/install, don't forget to run the powershell as an adminstrator)

<br>

2. You need to have mysql server with `dbname='project_manager'`, `host='localhost'`, `username='root'` and `password='root'` (At some stage a Docker file should replace this step).
   - run the script in `sql_script.txt` to initialize the databse
   - run the script in `sql_data.txt` to initialize the data in the database

<br>

3. Go to the root of project directory.

<br>

4. run `php -S localhost:8000`


### via Docker

1. Have Docker installed (and have Docker Desktop open)

2. run `docker-compose up --build` in the project root directory

> If you wish to open a shell to the docker use: `docker exec -it web-2025-php-1 sh` or `winpty docker exec -it web-2025-php-1 sh`

### via XAMPP

1. Install XAMPP from <a href="https://www.apachefriends.org/">here</a>.

2. Run XAMPP Control Panel as Administrator and configure your MySQL and Apache modules if you need to.

   - There is no need to change the default configuration if you do not need to.

3. Copy the entire project into the `xampp/htdocs/` folder (in the XAMPP configuration).

4. Run the `sql_script.txt` to initialize the databases in the `http://localhost/phpmyadmin/` (and `sql_data.txt` for initial data).

   - Configure `config/database.php` files if needed (if you changed the default settings of XAMPP).

> [!NOTE]
> If you wish to run the app in subfolder (not from app root) make sure to match the subfloder in the `BASE_PATH` constant in the `config/constants.php`.

> Common issues:
> 1. Database port amiguity. If you are experiencing DB setup problems, try to change the port (from the default 3306) in the `my.ini` file (all `port=3306` flags) of the MySQL and `config.inc.php` file (add `$cfg['Servers'][$i]['port'] = '<custom_port>';`) for the Apache. Note that you should change the `host` in the `database.php` file in the app to `localhost:<custom_port>`.

