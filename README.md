# web-2025

### run project
1. Have php installed.
   - on Windows: https://www.geeksforgeeks.org/how-to-install-php-in-windows-10/
      - you need to edit `php.ini` to uncomment `extension_dir = "ext"` and `extension=mysqli` lines. (If it's missing make a copy of `php.ini-development` and rename it).
   - alternatively uif you have chocolatey: choco install php (download from here https://chocolatey.org/install, don't forget to run the powershell as an adminstrator)

<br>

2. You need to have mysql server with `dbname='project_manager'`, `host='localhost'`, `username='root'` and `password='root'` (At some stage a Docker file should replace this step).
   - run the script in `sql_script.txt` to initialize the databse

<br>

3. Go to the root of project directory.

<br>

4. run `php -S localhost:8000`
