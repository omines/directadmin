# Get ApiGen.phar
wget http://www.apigen.org/apigen.phar

# Add branch
mkdir -p ../gh-pages
cd ../gh-pages
git clone https://${GH_TOKEN}@github.com/omines/directadmin.git > /dev/null
git checkout -B gh-pages
rm -rf api

# Generate Api
php apigen.phar generate -s ../src -d api --template-theme "bootstrap"

# Set identity
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis"

# Push generated files
git add .
git commit -m "API updated"
git push origin -fq > /dev/null

