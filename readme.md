nationswell
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
