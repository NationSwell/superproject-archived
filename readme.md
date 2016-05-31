nationswell
===========

-----
#### Dependencies

1. git `http://git-scm.com/book/en/Getting-Started-Installing-Git`

2. ruby

3. npm

4. brew `http://brew.sh/`

5. grunt `npm install -g grunt-cli`

6. fontforge
   ````
   brew install ttfautohint fontforge --with-python
   ````

7. Xquartz - http://xquartz.macosforge.org/landing/

8. Bundler - http://bundler.io/

-----
1. Install virtualbox https://www.virtualbox.org/

2. Install Vagrant
   http://www.vagrantup.com/

3. Install the vagrant-hostsupdater plugin `vagrant plugin install vagrant-hostsupdater`

4. `git clone https://github.com/10up/varying-vagrant-vagrants nationswell`

5. `cd nationswell`

6. `vagrant up`

7. `cd www/wordpress-default`

8. `git clone https://github.com/ronik-design/nationswell.git`

9. `cd nationswell`

10. `mv .git ..`

11. `cd ..`

12. `git reset HEAD --hard`

13. `rm -rf nationswell`

14. `cd wp-content/themes/nationswell`

15. `bundle install`

15. `npm install`

16. `grunt` then `grunt watch`

17. Wordpress Export from Staging or Production http://nationswell.com/wp-admin/export.php.

18. Import the export file into your local Wordpress install. Make sure to specify to download images and attachments during the import, otherwise posts will lack images.
   http://local.wordpress.dev/wp-admin/admin.php?import=wordpress

19. Go to http://local.wordpress.dev/wp-admin/admin.php?page=acf-options. Click 'Save Options'. Then fill out any un-configured options needed and re-save.

20. `vagrant ssh`, then access `/etc/nginx/nginx-wp-common.conf`

21. Add this nginx location directive to nginx-wp-common.conf directly above `location ~ \.php$ {`

````
location ~ ^/static/\d+/(js|fonts|img|css)/(.*)$ {
  try_files $uri $uri/ /wp-content/themes/nationswell/$1/$2;
}
````

22. Restart nginx `sudo /etc/init.d/nginx restart`

23. Install and Activate Wordpress Plugins and NationSwell theme 

```
cd /vagrant/www/wordpress-default`
chmod +x init.sh
./init.sh
```
