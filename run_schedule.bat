@echo off
cd /d e:\TopoGest
php artisan schedule:run >> storage\logs\schedule.log 2>&1
