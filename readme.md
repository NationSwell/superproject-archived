NationSwell
===========

This repository contains all code for the NationSwell website found at http://nationswell.org/. This repository is a hosted branch of the following git repository (github.com/wordpress/wordpress) maintained by Wordpress (wordpress.org). The custom theme is maintained under git within its own repositories.

* /wp-content/plugins  = Contains all contributed plugins 
* https://github.com/nationswell/theme = Custom theme

## Wordpress code update
We are branching off Wordpress’ main git repository (```git@github.com:WordPress/WordPress.git```). Never update from within wordpress admin dashboard. In order to update, we need to execute the following in git on the master branch within your local installation:

* To add a new remote, use the ```git remote``` add command on the terminal, in the directory your repository is stored at. ```git remote``` takes two parameteres:
  * A remote name, for example, ```wordpress```
  * A remote URL, for example, ```git@github.com:WordPress/WordPress.git```
  * Example: ```git remote add wordpress git@github.com:WordPress/WordPress.git```

* ```git fetch wordpress``` (assuming you're using “wordpress” as the nickname of the remote repository)
* ```git pull wordpress <version_number>```

Wordpress will notify of any new upgrades on any of the sites status reports. There is always a series of conflicted files between versions. Always take the update that comes from the remote. To resolve conflict, do the following:
* ```git checkout --theirs PATH/FILE```
* ```git add  PATH/FILE```
* If the conflict is a  file is being deleted by the remote, just add the change
* ```git add  PATH/FILE```
* Commit your changes and push up

## Wordpress plugin updates
* Download the new versions of the plugins you wish to update from wordpress.org
* Unzip and replace your local files with the updates
* Commit to master
* SSH to server and pull via git

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
4. Navigate to your Sites / web root directory, run `git clone https://github.com/Varying-Vagrant-Vagrants/VVV nationswell`
5. `cd nationswell`
6. `vagrant up`
7. `cd www/wordpress-default`
8. `git clone https://github.com/NationSwell/superproject nationswell`
9. `cd superproject`
10. `mv .git ..`
11. `cd ..`
12. `git reset HEAD --hard`
13. `rm -rf nationswell`
14. `cd wp-content/themes`
15. Clone the theme repo: `git clone https://github.com/NationSwell/theme nationswell`
16. `cd nationswell`

See the theme directory repo for theme development instructions and dependencies: https://github.com/NationSwell/theme