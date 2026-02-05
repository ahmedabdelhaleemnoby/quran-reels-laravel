@echo off
echo ========================================
echo Laravel Project Setup
echo ========================================
echo.

echo [1/5] Checking PHP installation...
php --version
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    pause
    exit /b 1
)
echo.

echo [2/5] Installing Composer dependencies...
echo This may take a while...
composer install --ignore-platform-reqs --no-interaction
if %errorlevel% neq 0 (
    echo ERROR: Composer install failed
    echo Please enable zip extension in php.ini
    pause
    exit /b 1
)
echo.

echo [3/5] Creating SQLite database...
if not exist "database\database.sqlite" (
    type nul > database\database.sqlite
    echo Database file created successfully
) else (
    echo Database file already exists
)
echo.

echo [4/5] Generating application key...
php artisan key:generate --force
if %errorlevel% neq 0 (
    echo ERROR: Failed to generate app key
    pause
    exit /b 1
)
echo.

echo [5/5] Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo WARNING: Migration failed - you may need to run it manually
)
echo.

echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo To start the development server, run:
echo     php artisan serve
echo.
echo Then open your browser at: http://localhost:8000
echo.
pause
