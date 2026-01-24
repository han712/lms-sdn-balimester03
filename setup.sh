#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   LMS SD - Complete Setup Script     ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# Step 1: Install Laravel 12
echo -e "${YELLOW}[1/15] Installing Laravel 12...${NC}"
composer create-project laravel/laravel lms-sd
cd lms-sd || exit

# Step 2: Install Laravel Breeze
echo -e "${YELLOW}[2/15] Installing Laravel Breeze...${NC}"
composer require laravel/breeze --dev
php artisan breeze:install blade

# Step 3: Install NPM dependencies
echo -e "${YELLOW}[3/15] Installing NPM dependencies...${NC}"
npm install

# Step 4: Create Middleware
echo -e "${YELLOW}[4/15] Creating Middleware...${NC}"
php artisan make:middleware CheckRole
php artisan make:middleware CheckActiveUser
php artisan make:middleware LogUserActivity

# Step 5: Create Models
echo -e "${YELLOW}[5/15] Creating Models...${NC}"
php artisan make:model Materi -mf
php artisan make:model Absensi -mf
php artisan make:model JawabanKuis -mf

# Step 6: Create Policies
echo -e "${YELLOW}[6/15] Creating Policies...${NC}"
php artisan make:policy MateriPolicy --model=Materi
php artisan make:policy UserPolicy --model=User

# Step 7: Create Controllers
echo -e "${YELLOW}[7/15] Creating Controllers...${NC}"
php artisan make:controller SuperAdminController
php artisan make:controller GuruController
php artisan make:controller SiswaController

# Step 8: Create Form Requests
echo -e "${YELLOW}[8/15] Creating Form Requests...${NC}"
php artisan make:request StoreMateriRequest
php artisan make:request UpdateMateriRequest
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest

# Step 9: Create Commands
echo -e "${YELLOW}[9/15] Creating Commands...${NC}"
php artisan make:command CreateAdminCommand
php artisan make:command ResetUserPasswordCommand
php artisan make:command ListUsersCommand
php artisan make:command ShowStatsCommand

# Step 10: Create Helpers directory
echo -e "${YELLOW}[10/15] Creating Helpers...${NC}"
mkdir -p app/Helpers
touch app/Helpers/helpers.php

# Step 11: Create Service Provider
echo -e "${YELLOW}[11/15] Creating Service Providers...${NC}"
php artisan make:provider ViewServiceProvider

# Step 12: Configure Database
echo -e "${YELLOW}[12/15] Configuring Database...${NC}"
echo -e "${GREEN}Please configure your .env file with database credentials${NC}"
echo -e "${GREEN}Press Enter when done...${NC}"
read -r

# Step 13: Run Migrations
echo -e "${YELLOW}[13/15] Running Migrations...${NC}"


# Step 14: Create Storage Link
echo -e "${YELLOW}[14/15] Creating Storage Link...${NC}"
php artisan storage:link

# Step 15: Build Assets
echo -e "${YELLOW}[15/15] Building Assets...${NC}"
npm run build

# Clear cache
echo -e "${YELLOW}Clearing cache...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear

# Autoload composer
echo -e "${YELLOW}Dumping autoload...${NC}"
composer dump-autoload

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║         Setup Complete! ✓             ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}To start the development server, run:${NC}"
echo -e "${GREEN}php artisan serve${NC}"
echo ""
echo -e "${BLUE}Default Login Credentials:${NC}"
echo -e "${GREEN}Super Admin: admin@lms.com / password${NC}"
echo -e "${GREEN}Guru: siti@lms.com / password${NC}"
echo -e "${GREEN}Siswa: ahmad@siswa.com / password${NC}"
echo ""
echo -e "${BLUE}Custom Commands:${NC}"
echo -e "${GREEN}php artisan lms:create-admin${NC}"
echo -e "${GREEN}php artisan lms:reset-password [email]${NC}"
echo -e "${GREEN}php artisan lms:list-users${NC}"
echo -e "${GREEN}php artisan lms:stats${NC}"
echo ""