# web-2025

### run project
1. Have php installed.
   - on Windows: https://www.geeksforgeeks.org/how-to-install-php-in-windows-10/
      - you need to edit `php.ini` to uncomment `extension_dir = "ext"` and `extension=mysqli` lines. (If it's missing make a copy of `php.ini-development` and rename it).
   - alternatively if you have chocolatey: choco install php
3. You need to have mysql server with `dbname='project_manager'`, `host='localhost'`, `username='root'` and `password='root'` (At some stage a Docker file should replace this step).
4. Go to the root of project directory.
5. run `php -S localhost:8000`
