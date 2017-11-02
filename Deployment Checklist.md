# Deployment Checklist

When it's time to upload your web app to a public server for production or testing, check against the set of items below to ensure you have a streamlined experience. The below recommendations are intended for a LAMP-stack server with Apache, adapt as necessary for alternative hosts and setups. 

*Deployment is generally achieved using basic file upload methods such as FTP*. This document assumes you have a basic knowledge of dealing with web hosts and which folders can be accessed through a browser and those that can not.



## Prior to upload:

- Determine where on the remote server the following folders will be respectively uploaded to:
    - BLogic (**just the framework not the whole development kit**, the framework component is sym-linked in your project)
    - Private folder
    - Log folder
    - Public folder

On a typical shared host or LAMP server your FTP top level area will have a 'www' or 'public_html' subfolder. It is within this subfolder that all files within 'Public' should be copied to. **Your 'Private' and 'BLogic' folders should be placed somewhere outside of this subfolder.** 

- Once determined adjust the BLOGIC, ROOT and LOG constants for DEPLOYED OR TESTING in **Public/settings.php**. These paths are *relative* to the public web folder. 

- Determine the database login credentials for the DEPLOYED or TESTING environments in **Private/Config/database.php**.

- Organise the deployment of your database (if using one).


## After upload

- Place an empty file named **.production** (for DEPLOYED settings) or .testing (for TESTING settings) in the **remote** folder alongside your public files.
- Ensure the web server has read-write access to:
     - The log folder
     - Private/Sessions
     - Private/Persistence (if using this folder for any kind of storage)
- If using routing set up the .htaccess file alongside the public files to enable **mod_rewrite**. A typical .htaccess looks like this:

    RewriteEngine On
    RewriteCond %{SCRIPT_FILENAME} !-d
    RewriteCond %{SCRIPT_FILENAME} !-f
    RewriteRule ^([^\.]*)(\?(.*))?$ index.php?$2
    
    
## Example deployment folder structure

    logs
        - various log files, including any of your web app logs. 
    Release
        BLogic
            BL
            PL
            Utils
            BLogic.php
            changelog.md
            Error.php
            license.txt
            Readme.md
        Private
            Components
            Config
            Entities
            Persistence
            Sessions
            Utils
            settings.php
    public_html
        .production
        .htaccess
        css
        images
        js
        index.php
        error.html
        not_found.html
        offline.html
        settings.php
        
        
    
     
    