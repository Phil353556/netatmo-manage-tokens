# netatmo-manage-token
Management of tokens needs to interact with Netatmo devices

# Introduction 
Since end of 2022, it it no more possible to obtain a token with a username and a password.
A new process must be followed as described here:
https://dev.netatmo.com/apidocumentation/oauth#authorization-code 

# Pre-requisistes
  The following packages must be already available or installed on the system before using the php script supplied.
  - php
  - php-curl

# Sumup of the process

* Create an application on https://dev.netatmo.com/apps/
  note the client_id, client_secret and redirect_uri
* Edit the file file_parameters.txt 
  and modify ONLY the field after the sign =  
  fill with your client_id, client_secret, scope and redirect_url values
  run the script with the display option on the command to check, what you wrote seems ok
  example:
  ```
  $ ./netatmo_manage_tokens.php display
  -----------------------------------------------------------------------------------
  Line #0 client_id=6xxxxxxxxxxxxxxxxxxxxxxc 
  Line #1 client_secret=Hxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxn
  Line #2 scope=read_presence write_presence read_camera write_camera
  Line #3 redirect_uri=http://localhost/ 
  -----------------------------------------------------------------------------------
  ```
  The three others files will be created automaticaly by the script:
    file_access_token.txt  
    file_expire_time.txt
    file_refresh_token.txt
  so don't create them
 
- Use the client_id and client_secret with scope needed (station, camera, thermostat ....)
   to create a URL like: <br/>
   ```https://api.netatmo.com/oauth2/authorize?client_id ``` <br/>
   and so on
      
   An example:
  ``https://api.netatmo.com/oauth2/authorize?client_id=6xxxxxxxxxxxxxxxxxxxxxx4&redirect_uri=http://localhost/&netatmo&state=codestate&scope=read_presence%20write_presence%20read_camera%20write_camera%20`` <br/>
  to get a code which is display once you validate on the netatmo's webpage  
  example:  
  Result is:  ``http://localhost/?state=teststate&code=8c57xxxxxxxxxxxxxxxxxxxxxxxxadfa``<br/>  
  use the 32 caracters after the word code =  
  to initiate the process using the netatmo_manage_tokens.php to obtain access and refresh tokens and expire in time  

  example:
 ``` 
  $ ./netatmo_manage_tokens.php 8c57xxxxxxxxxxxxxxxxxxxxxxxxadfa
 ----------------8c57xxxxxxxxxxxxxxxxxxxxxxxxadfa------------
 --------------------------------------------------------------------------
 --------------------------------------------------------------------------
 atoken 5xxxxxxxxxxxxxxxxxxxxx43|5bxxxxxxxxxxxxxxxxxxxxxxxxxxxx78
 rtoken 5xxxxxxxxxxxxxxxxxxxxx43|0xxxxxxxxxxxxxxxxxxxxxxxxxxxxxa2
 etoken 10800
 Write succesful 549c26f41c775931e28b4743|5bde24b77f17ae6c264ccc8a500ed078 in file file_access_token.txt
 Write succesful 549c26f41c775931e28b4743|0b15f04cac748852da4f2fc6f331ada2 in file file_refresh_token.txt
 Write succesful 1737385729 in file file_expire_time.txt
 --------------------------------------------------------------------------
 ```

 The results are the three files written with the correct tokens and expire time values.  

# Please note:
Coherency is a MUST on redirect_uri  between:
        -your application at https://dev.netatmo.com/apps/
        -the URL https://api.netatmo.com/oauth2/authorize
        the URL of a redirect_uri MUST terminate with a /

All possibles scopes are:
        read_station
        read_presence write_presence read_camera write_camera
        read_doorbell read_smokedetector read_carbonmonoxidedetector read_homecoach
        read_thermostat write_thermostat
        read_magellan write_magellan read_mx
  all scopes can be used in one row.


- Create a script which uses the code to interact with the netatmo's devices of the home
  This part will not be discussed here, only the management of tokens. This will be another topic later.

- in // manage the token because the access_token expire every 10800s (so 3hours) after you get it
  Thus the tokens (access and refresh) must be refresh at least every 3hours so the the script hereunder can make the job.
  ------------------------------------------------------------------------------*/

# usage of the script netatmo_manage_tokens.php

  ```
./netatmo_manage_tokens.php
 -----------------------------------------------------------------------------------
 Usage: [usage|code|current|refresh ]

 usage
         This help
         If the program is run with no parameter, usage is displayed

 display
         Display the contents of the parameters file, there are four lines:
         -client_id
         -client_secret
         -scope
         -redirect-uri

 code
         Use the code (32c) displayed in URL bar
         AFTER the use of the specific URL similar to:
         https://api.netatmo.com/oauth2/authorize?client_id=xxxxxx

         If a valide code is the first parameter of the script then :
         - The access_token and refresh_token are requested from netatmo
        - The access token and refresh token are displayed
         - And store in two files:  file_access_token.txt and file_refresh_token.txt
         - A third file file_expire_time.txt is also created

 current
         Display the contents of access token, refresh token and expire time files
         Calculate and display the number of seconds since the expire time obtained

 refresh
         Get a new access token and a new refresh token
         using the current refresh token
 -----------------------------------------------------------------------------------
  ```

# Return codes by function

##function f_read_file_parameters($display)  
   10 : file parameters.txt do not exist

##function f_get_tokens($grant_type,$client_id,$client_secret,$code,$scope,$redirect_uri,$Content_Type)   
   20 : the http code is not 200  

##function f_writefile($access_token_to_write,$refresh_token_to_write)  
   30 : file file_access_token.txt do not exist  
   31 : file file_access_token.txt cannot be written  
   32 : file file_refresh_token.txt do not exist  
   33 : file file_refresh_token.txt cannot be written  
   34 : file file_expire_time.txt do not exist  
   35 : file file_expire_time.txt cannot be written  

##function f_readfile()  
   40 : file file_access_token.txt do not exist  
   41 : file file_refresh_token.txt do not exist  
   42 : file file_expire_time.txt do not exist  

##function f_get_refresh_tokens($grant_type,$refresh_token,$client_id,$client_secret)  
   50 : file file_expire_time.txt do not exist  
   51 : file file_refresh_token.txt do not exist  
   52 : the http code is not 200  

##function f_usage()  
 -> no return code  

##Main routine  
      0 : normal run  
    100 : no parameter on command line  
    101 : usage was the first parameter  
    102 : display was the first parameter  
    103 : current was the first parameter  
    104 : refresh was the first parameter  
    105 : code length must be 32 caracters  
    106 : code must composed with letters and numbers only  

# Debug mode
if you want to activate the debug mode 
``export  NA_DEBUG=1``  
or  
``export NA_DEBUG=true``  

To desactivate the debug mode  
``export  NA_DEBUG=0``
or  
``export NA_DEBUG=false``
