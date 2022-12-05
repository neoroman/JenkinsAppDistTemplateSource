# App Distribution Web Site Template
Language: HTML, PHP, Javascript

## Introduction
- This a template web site for iOS / Android application distributions.
- You can start jenkins build first then change config.json, etc.


## Requirements
- Apache or Nginx web server
- PHP 7.0 or later
- ``jenkins-build.sh`` as git submodule, see ``Installation`` section


## Installation
- First you should get ``jenkins (bash) shell script`` into your iOS or Android source working copy like following:
  ```
    git submodule add https://github.com/neoroman/JenkinsBuild.git jenkins
  ```
- Edit config/config.json for various path for source, CLI commands, ... etc
- Edit lang/lang_{ko,en}.json for messages on web pages.
- After create jenkins item and input followings into ``Build`` section
  ```
    git submodule init
    git submodule update
    git submodule foreach git pull origin main
    bash -ex ${WORKSPACE}/jenkins/jenkins-build.sh -p ios --toppath "Company/Project"
  ```


## Configuration
- ``config/config.json``: parameter for site global variables
- ``lang/lang_ko.json``: php-i18n for Korean
- ``lang/lang_en.json``: php-i18n for English
- You can add more langauge file in lang/ if you need.


## Author
COMPANY Corp. /  appdev.svc@company.com


## License
See the [LICENSE](./LICENSE) file for more info.
