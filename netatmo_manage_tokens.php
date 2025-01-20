#!/usr/bin/php
<?php
/*------------------------------------------------------------------------------*/
/* 2025/01/20 (c) Phil353556 github.com                                         */
/*  email: 51y9oj579@relay.firefox.com                                          */
/*                                                                              */
/* This piece of software is as IS with no Warranty. Use it of your own risk!   */
/* See LICENCE.md file in repository                                            */
/*------------------------------------------------------------------------------*/

/*------------------------------------------------------------------------------*/
/* The goal is to manage tokens so it is possible then to interact with Netatmo devices */
/* Run the script without any parameter to display the help                     */
/*------------------------------------------------------------------------------*/
/* list of function used                                                        */
/*                                                                              */
/*function f_read_file_parameters($display)					*/
/*function f_get_tokens($grant_type,$client_id,$client_secret,$code,$scope,$redirect_uri,$Content_Type)*/
/*function f_writefile($access_token_to_write,$refresh_token_to_write)		*/
/*function f_readfile()								*/
/*function f_get_refresh_tokens($grant_type,$refresh_token,$client_id,$client_secret)
/*function f_usage()								*/
/*------------------------------------------------------------------------------
Return codes by function:

function f_read_file_parameters($display)
   10 : file parameters.txt do not exist

function f_get_tokens($grant_type,$client_id,$client_secret,$code,$scope,$redirect_uri,$Content_Type)
   20 : the http code is not 200

function f_writefile($access_token_to_write,$refresh_token_to_write)
   30 : file file_access_token.txt do not exist
   31 : file file_access_token.txt cannot be written
   32 : file file_refresh_token.txt do not exist
   33 : file file_refresh_token.txt cannot be written
   34 : file file_expire_time.txt do not exist
   35 : file file_expire_time.txt cannot be written

function f_readfile()
   40 : file file_access_token.txt do not exist
   41 : file file_refresh_token.txt do not exist
   42 : file file_expire_time.txt do not exist

function f_get_refresh_tokens($grant_type,$refresh_token,$client_id,$client_secret)
   50 : file file_expire_time.txt do not exist
   51 : file file_refresh_token.txt do not exist
   52 : the http code is not 200


function f_usage()
 -> no return code

Main routine
      0 : normal run
    100 : no parameter on command line
    101 : usage was the first parameter
    102 : display was the first parameter
    103 : current was the first parameter
    104 : refresh was the first parameter
    105 : code length must be 32 caracters
    106 : code must composed with letters and numbers only
------------------------------------------------------------------------------*/

/*------------------------------------------------------------------------------*/
/* The lines hereunder SHOULD NOT be modified  				 	*/
/*------------------------------------------------------------------------------*/
$file_parameters="file_parameters.txt";
$file_access_token="file_access_token.txt";
$file_refresh_token="file_refresh_token.txt";
$file_expire_time="file_expire_time.txt";

$Content_Type="application/x-www-form-urlencoded;charset=UTF-8";
$grant_type="authorization_code";  

/*------------------------------------------------------------------------------*/
/* This function read the file with parameters needed and set variables		*/
/*------------------------------------------------------------------------------*/
function f_read_file_parameters($display)
{
global $DEBUG;
global $file_parameters;
global $client_id;
global $client_secret;
global $scope;
global $redirect_uri;

if ( $DEBUG == true )
{
	printf(" --------------------------------------------------------------------------\n");
	printf(" function: f_read_file_parameters\n");
}
if ( $display == "true" ) 
{
	printf(" ----------------------------------------------------------------------------------- \n");
}

if (file_exists($file_parameters) )
 {
	$file_parameters_txt = file_get_contents($file_parameters);

	$rows = explode("\n", $file_parameters_txt);
	
	foreach($rows as $row => $data)
	{
        	$test=trim($data);
        	if ( !empty($test) )
        	{
        		$row_data = explode('=', $data);
		
        		$info[$row]['id']           = $row_data[0];
        		$info[$row]['name']         = $row_data[1];
		
			if ( $display == "true" ) 
			{
        		printf(" Line #".$row." ".$info[$row]['id']."=".$info[$row]['name']." \n");
        		}
        	}
	}
	$client_id=$info[0]['name'];
	$client_secret=$info[1]['name'];
	$scope=$info[2]['name'];
	$redirect_uri=$info[3]['name'];
	
	if ( $DEBUG == true)
	{
		var_dump($info);
	}
 }
 else
 {
	 printf(" function f_read_file_parameters:  file ".$file_parameters." do not exist \n");
	 exit(10);
 }

if ( $display == "true" ) 
{
	printf(" ----------------------------------------------------------------------------------- \n");
}
}

/*------------------------------------------------------------------------------*/
/*  https://dev.netatmo.com/apidocumentation/oauth#authorization-code */
/*------------------------------------------------------------------------------*/
/*
POST /oauth2/token HTTP/1.1
    Host: api.netatmo.com
    Content-Type: application/x-www-form-urlencoded;charset=UTF-8

    grant_type=authorization_code
    client_id=[YOUR_APP_ID]
    client_secret=[YOUR_CLIENT_SECRET]
    code=[CODE_RECEIVED_FROM_USER]
    redirect_uri=[YOUR_REDIRECT_URI]
    scope=[SCOPE_SPACE_SEPARATED]
 */
/*------------------------------------------------------------------------------*/
function f_get_tokens($grant_type,$client_id,$client_secret,$code,$scope,$redirect_uri,$Content_Type)
{
global $DEBUG;
global $client_id;
global $client_secret;
global $scope;
global $redirect_uri;

printf(" --------------------------------------------------------------------------\n");
if ( $DEBUG == true )
{
	printf(" function: f_get_tokens\n");
}

$handle = curl_init();

$datas = array("grant_type"=>$grant_type,"client_id"=>$client_id,"client_secret"=>$client_secret,"code"=>$code,"scope"=>$scope,"redirect_uri"=>$redirect_uri,"Content-Type:" => $Content_Type);
if ( $DEBUG == true )
{
	var_dump($datas);
}

curl_setopt($handle, CURLOPT_POSTFIELDS, $datas);


if ( $DEBUG == true )
{
	curl_setopt_array($handle, array(
        CURLOPT_URL => "https://api.netatmo.com/oauth2/token",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $datas,
        CURLOPT_VERBOSE => true,
        CURLOPT_RETURNTRANSFER => true
        )
);
}
else
{
	curl_setopt_array($handle, array(
        CURLOPT_URL => "https://api.netatmo.com/oauth2/token",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $datas,
        CURLOPT_RETURNTRANSFER => true
        )
);
}

$result=curl_exec($handle);
$httpcode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
curl_close($handle);
$array = json_decode($result, true);

if($httpcode != 200 ) {
	printf(" --------------------------------------\n");
        printf(" ERROR: serveur return code is: ".$httpcode." \n");
	printf(" --------------------------------------\n");
       	var_dump($array);
	exit(20);
    }

if ( $DEBUG == true )
{
       	var_dump($array);
	printf(" httpcode; ".$httpcode."\n");
	printf(" access_token; ".$array['access_token']."\n");
	printf(" refresh_token: ".$array['refresh_token']."\n");
	printf(" access_token_expire_in: ".$array['expires_in']."\n");
}
printf(" --------------------------------------------------------------------------\n");
return [$array['access_token'],$array['refresh_token'],$array['expires_in']]; 
}

/*------------------------------------------------------------------------------*/
/* 										*/
/*------------------------------------------------------------------------------*/
function f_writefile($access_token_to_write,$refresh_token_to_write)
{
global $DEBUG;
global $file_access_token;
global $file_refresh_token;
global $file_expire_time;
  
if ( $DEBUG == true )
{
	printf(" --------------------------------------------------------------------------\n");
	printf(" function: f_write files\n");
	printf(" file : ".$file_access_token." \n");
	printf(" file : ".$file_refresh_token." \n");
}

if (!$myfile = fopen($file_access_token, 'c')) {
        printf(" The file cannot be open: $file_access_token \n");
        exit(30);
}
if (fwrite($myfile, $access_token_to_write) === FALSE) {
        printf(" Cannot write in the file: $file_access_token \n");
        exit(31);
}
printf(" Write succesful $access_token_to_write in file $file_access_token \n");
fclose($myfile);

if (!$myfile = fopen($file_refresh_token, 'c')) {
         printf(" The file cannot be open: $file_refresh_token \n");
         exit(32);
}
if (fwrite($myfile, $refresh_token_to_write) === FALSE) {
        printf( "Cannot write in the file: $file_refresh_token \n");
        exit(33);
}
printf(" Write succesful $refresh_token_to_write in file $file_refresh_token \n");
fclose($myfile);

$current_unix_time=time();
if (!$myfile = fopen($file_expire_time, 'c')) {
         printf(" The file cannot be open: $file_expire_time \n");
         exit(34);
}
if (fwrite($myfile, $current_unix_time) === FALSE) {
        printf(" Cannot write in the file: $file_expire_time \n");
        exit(35);
}
printf(" Write succesful $current_unix_time in file $file_expire_time \n");
fclose($myfile);
printf(" --------------------------------------------------------------------------\n");

}

/*-------------------------------------------------------------------------------*/
/* Read the three files:                                                         */
/*                - file_access_token.txt, 					 */
/*                - file_refresh_token.txt                                     	 */
/*                - file_expire_time.txt                               	         */
/* Display their content       							 */
/* Calculate the name of second between the initial expire time received and now */
/*-------------------------------------------------------------------------------*/
function f_readfile()
{
global $DEBUG;
global $file_access_token;
global $file_refresh_token;
global $file_expire_time;

printf(" ----------------------------------------------------------------------------------- \n");
if ( $DEBUG == true )
{
	printf(" function: f_readfiles\n\n");
}

printf(" File : ".$file_access_token." \n");
if (!$myfile = @fopen($file_access_token, 'r')) {
        printf(" The file cannot be open: $file_access_token \n");
        exit(40);
}
printf(" ".$str=str_replace("\n","",fread($myfile,filesize($file_access_token)))."\n\n");
fclose($myfile);

printf(" File : ".$file_refresh_token." \n");
if (!$myfile = @fopen($file_refresh_token, 'r')) {
        printf(" The file cannot be open: $file_access_token \n");
        exit(41);
}
printf(" ".$str=str_replace("\n","",fread($myfile,filesize($file_refresh_token)))."\n\n");
fclose($myfile);

printf(" File : ".$file_expire_time." \n");
if (!$myfile = @fopen($file_expire_time, 'r')) {
        printf("  The file cannot be open: $file_access_token \n");
        exit(42);
}
$expire_time=fread($myfile,filesize($file_expire_time));

printf(" Initial time ".$expire_time." \n");
$expire_time= (int)$expire_time;
$delta=time()-$expire_time;
printf(" Delta time with now  ".$delta." / 10800 \n");
fclose($myfile);

printf(" ----------------------------------------------------------------------------------- \n");
}

/*------------------------------------------------------------------------------*/
/* Refresh the access_token AND refresh_token 					*/
/* using the current refresh token, client_id and client_secret			*/
/* The grand_type must be refresh_token						*/
/*------------------------------------------------------------------------------
Source: https://dev.netatmo.com/apidocumentation/oauth#refreshing-a-token

Endpoint: https://api.netatmo.com/oauth2/token
Method: POST

Entry parameters
Name            Required        Description
grant_type      yes             refresh_token
refresh_token   yes             the refresh token retrieved while requesting an access_token
client_id       yes             your client id
client_secret   yes             your client secret

Return parameters
Name            Description
access_token    Access token for your user
expires_in      Validity timelaps in seconds
refresh_token   Use this token to get a new access_token once it has expired
and all the scopes
------------------------------------------------------------------------------------------ */
function f_get_refresh_tokens($grant_type,$refresh_token,$client_id,$client_secret)
{
global $DEBUG;
global $client_id;
global $client_secret;
global $scope;
global $redirect_uri;
global $refresh_token;
global $access_token;
global $expire_in;
global $file_access_token;
global $file_refresh_token;
global $file_expire_time;

printf(" ----------------------------------------------------------------------------------- \n");
if ( $DEBUG == true )
{
	printf(" function: f_get_refresh_tokens\n");
}

printf(" file : ".$file_expire_time." \n");
if (!$myfile = @fopen($file_expire_time, 'r')) {
        printf(" The file cannot be open: $file_expire_time \n");
        exit(50);
}
$expire_time=fread($myfile,filesize($file_expire_time));
printf(" Initial time ".$expire_time." \n");
$expire_time= (int)$expire_time;
$delta=time()-$expire_time;
printf(" Delta time with now  ".$delta." / 10800 \n");
if ( $delta > "10800" ) 
	{
		printf(" Token expired - refresh token is needed\n\n");
	}
fclose($myfile);

printf(" Retrieve refresh token store in file : ".$file_refresh_token." \n");
if (!$myfile = @fopen($file_refresh_token, 'r')) {
        printf(" The file cannot be open: $file_refresh_token \n");
        exit(51);
}

$refresh_token=$str=str_replace("\n","",fread($myfile,filesize($file_refresh_token)));
$grant_type="refresh_token";
printf(" \n");
printf(" Getting NEW access token AND refresh token, using current refresh token\n");
printf(" \n");

if ( $DEBUG == true )
{
	printf(" grant type:".$grant_type."\n");
	printf(" refresh_token: ".$refresh_token."\n");
	printf(" client_id: ".$client_id."\n");
	printf(" client_secret: ".$client_secret." \n");
}

$handle = curl_init();

$datas = array("grant_type"=>$grant_type,"refresh_token"=>$refresh_token,"client_id"=>$client_id,"client_secret"=>$client_secret);
if ( $DEBUG == true )
{
       	var_dump($datas);
}

curl_setopt($handle, CURLOPT_POSTFIELDS, $datas);

if ( $DEBUG == true )
{
	curl_setopt_array($handle, array(
        CURLOPT_URL => "https://api.netatmo.com/oauth2/token",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $datas,
        CURLOPT_VERBOSE => true,
        CURLOPT_RETURNTRANSFER => true
        )
);
}
else
{
	curl_setopt_array($handle, array(
        CURLOPT_URL => "https://api.netatmo.com/oauth2/token",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $datas,
        CURLOPT_RETURNTRANSFER => true
        )
);
}

$result=curl_exec($handle);
$httpcode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
curl_close($handle);
$array = json_decode($result, true);


if($httpcode != 200 ) {
        printf(" --------------------------------------\n");
        printf(" ERROR: serveur return code is: ".$httpcode." \n");
        printf(" --------------------------------------\n");
       	var_dump($array);
        exit(52);
    }


if ( $DEBUG == true )
{
       	var_dump($array);
	printf(" httpcode; ".$httpcode."\n");
	printf(" access_token; ".$array['access_token']."\n");
	printf(" refresh_token: ".$array['refresh_token']."\n");
	printf(" access_token_expire_in: ".$array['expires_in']."\n");
}

printf(" ----------------------------------------------------------------------------------- \n");
return [$array['access_token'],$array['refresh_token'],$array['expires_in']];
}

/*------------------------------------------------------------------------------*/
/* Display the help for this script                                             */
/*------------------------------------------------------------------------------*/
function f_usage()
{
echo " ----------------------------------------------------------------------------------- \n";
echo " Usage: [usage|code|current|refresh ]			                           \n";
echo "                                                                                     \n";
echo " usage                                                                               \n";
echo "         This help                                                                   \n";
echo "         If the program is run with no parameter, usage is displayed                 \n";
echo "                                                                                     \n";
echo " display                                                                             \n";
echo "         Display the contents of the parameters file, there are four lines:          \n";
echo "         -client_id                                                                  \n";
echo "         -client_secret                                                              \n";
echo "         -scope                                                                      \n";
echo "         -redirect-uri                                                               \n";
echo "                                                                                     \n";
echo " code                                                                                \n";
echo "         Use the code (32c) displayed in URL bar 					   \n";
echo "         AFTER the use of the specific URL similar to:                               \n";
echo "         https://api.netatmo.com/oauth2/authorize?client_id=xxxxxx                   \n";
echo "                                                                                     \n";
echo "         If a valide code is the first parameter of the script then :                \n";
echo "         - The access_token and refresh_token are requested from netatmo             \n";     
echo "         - The access token and refresh token are displayed 	                   \n";     
echo "         - And store in two files:  file_access_token.txt and file_refresh_token.txt \n";     
echo "         - A third file file_expire_time.txt is also created	                   \n";     
echo "                                                                                     \n";
echo " current                                                                             \n";
echo "         Display the contents of access token, refresh token and expire time files   \n";
echo "         Calculate and display the number of seconds since the expire time obtained  \n";
echo "                                                                                     \n";
echo " refresh                                                                             \n";
echo "         Get a new access token and a new refresh token 				   \n";
echo "         using the current refresh token                                             \n";
echo " ----------------------------------------------------------------------------------- \n";
}

/*------------------------------------------------------------------------------*/
/* Main Routine 								*/
/*------------------------------------------------------------------------------*/
global $DEBUG;
global $client_id;
global $client_secret;
global $scope;
global $redirect_uri;
global $refresh_token;
global $access_token;
global $expire_time;

$env = getenv('NA_DEBUG');

if ( ( $env == 0 ) || $env == "false" )
{
        $DEBUG = 0;
}

if ( ( $env == 1 ) || $env == "true" )
{
        $DEBUG = 1;
	printf(" ----------------------------------------------------------------------------------- \n");
        printf(" DEBUG is ON 									     \n");
	printf(" ----------------------------------------------------------------------------------- \n");
}

$argc = count($argv);

if (  $argc != 2 )  { f_usage(); exit(100); }
if ( $DEBUG == true )
{
        var_dump($argv);
}

if ( $argv[1] == "usage" )
        {
		f_usage();
                exit(101);
        }


if ( $argv[1] == "display" )
        {
                f_read_file_parameters("true");
                exit(102);
        }

if ( $argv[1] == "current" )
        {
		f_read_file_parameters("false");
                f_readfile();
                exit(103);
        }

if ( $argv[1] == "refresh" )
        {
		f_read_file_parameters("false");
		[$access_token, $refresh_token, $expire_token ] = f_get_refresh_tokens($grant_type,$refresh_token,$client_id,$client_secret);
		f_writefile($access_token,$refresh_token);
                exit(104);
        }
else
{
	$code =  $argv[1];
	if ( strlen($code) <> 32 )
	{
		printf(" The code must have 32c \n");
		f_usage();
		exit(105);
	}
	$result=ctype_alnum($code);
	if (! $result)
	{
        	printf(" KO ".$result."   \n");
		f_usage();
		exit(106);
	}
	else
	{
	f_read_file_parameters("false");
	printf(" ----------------".$code."------------\n");
	[$access_token, $refresh_token, $expire_token ] = f_get_tokens($grant_type,$client_id,$client_secret,$code,$scope,$redirect_uri,$Content_Type);
	printf(" atoken ".$access_token."\n rtoken ".$refresh_token."\n etoken ".$expire_token."\n");
	f_writefile($access_token,$refresh_token);
	}
}

exit(0)
?>
/* ------------------------------------------------------------------------------ */
