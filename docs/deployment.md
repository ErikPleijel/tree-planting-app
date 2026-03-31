


ssh root@139.84.228.69
To navigate: cd ..  Dir
cd /var/www/tree-planting-app

QUICK DEPLOY
/var/www/tree-planting-app/deploy.sh    ./deploy.sh

WIPE OUT REMOTE:
To wipe remote VPS db, Use: php artisan migrate:fresh --seed --force
For normal deploy, use: To wipe remote VPS db, Use: php artisan migrate --force


Log in at mysql
mysql -u root -p
Mysql: bhu1nji2mko3#
Password: secret123

Backup database to VPS
mysqldump -u root -p treeplanting_test > /root/treeplanting_test_backup_$(date +%F_%H-%M).sql




HOW TO UPDATE FROM GitHub
cd /var/www/tree-planting-app
((If made changes on VPS files: git reset --hard))
git pull origin main

Update BUILD files. From local PS:
scp -r public/build root@139.84.228.69:/var/www/tree-planting-app/public/

Next time, try to run from VPS:
rm -rf public/build
npm run build




STEPS
Push to GitHup
1. Deploy the Laravel App Code to Your VPS
2. Migrate Your Local MySQL Database to the VPS

Step 1
cd /var/www/
git clone https://github.com/ErikPleijel/tree-planting-app.git tree-planting-app
cd tree-planting-app

cd /var/www/tree-planting-app
git pull origin main


php artisan serve --host=0.0.0.0 --port=8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tree_planting_app
DB_USERNAME=laravel
DB_PASSWORD=secret123
