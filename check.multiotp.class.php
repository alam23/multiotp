<?php
/**
 * @file  check.multiotp.class.php
 * @brief Check the implementation of some multiOTP functionnalities
 *
 * multiOTP - Strong two-factor authentication PHP class package
 * http://www.multiotp.net
 *
 * Visit http://forum.multiotp.net/ for additional support.
 *
 * Donation are always welcome! Please check http://www.multiotp.net
 * and you will find the magic button ;-)
 *
 *
 * check.multiotp.class.php is a file implementing the Multiotp class
 * in order to check the compliance with RFC4226. It must be
 * placed in the same directory as the multiotp.class.php file.
 *
 * WARNING! DO NOT FORGET TO REMOVE this test file from your disk when you go in production !
 *
 *
 * PHP 4.4.4 or higher is supported.
 *
 * @author    Andre Liechti, SysCo systemes de communication sa, <info@multiotp.net>
 * @version   4.3.0.0
 * @date      2014-11-04
 * @since     2013-07-10
 * @copyright (c) 2010-2014 SysCo systemes de communication sa
 * @copyright GNU Lesser General Public License
 *
 *//*
 *
 * LICENCE
 *
 *   Copyright (c) 2010-2014 SysCo systemes de communication sa
 *   SysCo (tm) is a trademark of SysCo syst�mes de communication sa
 *   (http://www.sysco.ch/)
 *   All rights reserved.
 *
 *   This file is part of the multiOTP project.
 *
 *   This script is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 3 of the License, or (at your option) any later version.
 *
 *   This script is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *   Lesser General Public License for more details.
 *
 *   You should have received a copy of the GNU Lesser General Public
 *   License along with multiOTP PHP class.
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * Usage
 *  
 *   The file must be placed in the same directory as multiotp.class.php
 *
 *
 * External file needed
 *
 *   multiotp.class.php
 *
 *
 * External file created
 *
 *   Multiotp class will create some internals folders and files
 *
 *
 * Change Log
 *
 *   2014-11-04 4.3.0.0 SysCo/al Additional tests included
 *   2014-03-30 4.2.4.2 SysCo/al Additional tokens tests included
 *   2014-03-30 4.2.4   SysCo/al Additional tests included
 *                               MySQL backend test added (set the $check_sql_xxx parameters below)
 *                               List of attributes to encrypt in the backend is set to null during the tests
 *   2013-08-25 4.0.7   SysCo/al Version synchronization
 *   2013-08-25 4.0.6   SysCo/al File renamed to check.multiotp.class.php
 *   2013-07-10 4.0.4   SysCo/al Initial release of check.multiotp.php
 ***************************************************************/

$first_time = time();

require_once('multiotp.class.php');

// SQL server test parameters
$check_sql_server   = isset($GLOBALS['check_sql_server'])?$GLOBALS['check_sql_server']:'';
$check_sql_username = isset($GLOBALS['check_sql_username'])?$GLOBALS['check_sql_username']:'';
$check_sql_password = isset($GLOBALS['check_sql_password'])?$GLOBALS['check_sql_password']:'';
$check_sql_database = isset($GLOBALS['check_sql_database'])?$GLOBALS['check_sql_database']:'';


// Default backend is 'files'
$default_backend = 'files';
$backend = $default_backend;
$current_backend = '';


// Tests counter
$tests = 0;


// Successes counter
$successes = 0;


$browser_mode = isset($_SERVER["HTTP_USER_AGENT"]);

// $crlf will skip a line in command line mode and also in browser mode
$crlf   = $browser_mode?"<br />\n":"\r\n";
$b_on   = $browser_mode?'<b>':'';
$b_off  = $browser_mode?'</b>':'';
$h2_on  = $browser_mode?'<h2>':' *** ';
$h2_off = $browser_mode?'</h2>':' *** ';
$hr     = $browser_mode?'<hr />':'----------'.$crlf;
$i_on   = $browser_mode?'<i>':'';
$i_off  = $browser_mode?'</i>':'';
$ok_on  = $browser_mode?'<span style="color: green;"><b>':'';
$ok_off = $browser_mode?'</b></span>':'';
$ko_on  = $browser_mode?'<span style="color: red;"><b>':'';
$ko_off = $browser_mode?'</b></span>':'';


// Declare and initialize the Multiotp class if not done by an other file including this one
if (!isset($multiotp))
{
	$multiotp = new Multiotp('DefaultCliEncryptionKey');
}
$multiotp->SetMaxEventResyncWindow(500); // 500 is enough and quicker for the check
$multiotp->EnableVerboseLog(); // Could be helpful at the beginning

$multiotp->_config_data['attributes_to_encrypt'] = '**';  // For test purposes only

// Write the configuration information in the configuration file
$multiotp->WriteConfigData();


if (('' != $check_sql_server) &&
    ('' != $check_sql_username) &&
    ('' != $check_sql_password) &&
    ('' != $check_sql_database)
   )
{
    $backend = 'mysql';
    $multiotp->SetSqlServer($check_sql_server);
    $multiotp->SetSqlUsername($check_sql_username);
    $multiotp->SetSqlPassword($check_sql_password);
    $multiotp->SetSqlDatabase($check_sql_database);
}

if ($browser_mode && (!isset($GLOBALS['no_header'])))
{
    echo <<<EOWEBHEADER
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>
            multiOTP class implementation check
        </title>
        <style>
            body {
                font-family: Verdana, Helvetica, Arial;
                color: black;
                font-size: 10pt;
                font-weight: normal;
                text-decoration: none;
            }
        </style>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <script>
            // Finds y value of given object
            function findPos(obj)
            {
                var curtop = 0;
                if (obj.offsetParent)
                {
                    do
                    {
                        curtop += obj.offsetTop;
                    } while (obj = obj.offsetParent);
                    return [curtop];
                }
            }

            // Scroll to an object
            function scrollToObject(object_div)
            {
                //Get object
                var ObjectDiv = document.getElementById(object_div);

                //Scroll to location of ObjectDiv
                window.scroll(0,findPos(ObjectDiv));
            }
        </script>
    </head>
    <body onload="scrollToObject('test_result');">
EOWEBHEADER;
}


//====================================================================
// Display header and version information
echo $crlf;
echo $b_on.$multiotp->GetClassName()." HOTP implementation check".$b_off.$crlf;
echo "(RFC 4226, http://www.ietf.org/rfc/rfc4226.txt)".$crlf;
echo "-----------------------------------------------".$crlf;
echo $crlf;
echo $multiotp->GetFullVersionInfo();
echo ", running with PHP version ".phpversion().$crlf;
echo $crlf;
echo "Library hash: ".str_replace("\t",", ",$multiotp->GetLibraryHash()).$crlf;
echo $crlf;
echo "Valid algorithms: ".str_replace("\t",", ",$multiotp->GetAlgorithmsList()).$crlf;
echo $crlf;
echo $b_on."List of supported SMS providers".$b_off.$crlf;
echo str_replace("\t",$crlf,$multiotp->GetSmsProvidersList());
echo $crlf;

while ($current_backend != 'files')
{
    $multiotp->SetBackendType($backend);
    $multiotp->WriteConfigData();
    if ('mysql' == $backend)
    {
        $multiotp->InitializeBackend();
    }
    $current_backend = $backend;
    echo $hr;
    echo $crlf;
    echo "Selected backend: ".$b_on.$backend.$b_off.$crlf;
    echo $crlf;


    //====================================================================
    // TEST: Clear the log
    $tests++;
    echo $b_on."Clear the log".$b_off.$crlf;
    if ($multiotp->ClearLog())
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Log successfully cleared".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Unable to clear the log".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Write in the log
    $tests++;
    echo $b_on."Write in the log".$b_off.$crlf;
    
    $test_tag = '['.date("YmdHis").']';
    
    $multiotp->WriteLog("Test: test tag is $test_tag", FALSE, FALSE, 19, 'System', '');
    $log_content = $multiotp->ShowLog(TRUE);
    if (FALSE !== strpos($log_content, $test_tag))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Log successfully updated".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Unable to write in the log".$crlf;
    }
    echo $crlf;


    //============================================
    // TEST: Write the configuration file/database
    $tests++;
    echo $b_on."Write the configuration file/database".$b_off.$crlf;
    $backup_prefix = $multiotp->GetVerboseLogPrefix();
    $multiotp->SetVerboseLogPrefix('tESt');
    if ($multiotp->WriteConfigData())
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Configuration successfully written".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Failed while writing the configuration".$crlf;
    }
    echo $crlf;


    //===========================================
    // TEST: Read the configuration file/database
    $tests++;
    echo $b_on."Read the configuration file/database".$b_off.$crlf;
    $multiotp->SetVerboseLogPrefix('EMPTY');
    $multiotp->ReadConfigData();
    $test_prefix = $multiotp->GetVerboseLogPrefix();
    $multiotp->SetVerboseLogPrefix($backup_prefix);
    $multiotp->WriteConfigData();
    if ('tESt' == $test_prefix)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Configuration successfully read".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Failed while reading the configuration ($test_prefix)".$crlf;
    }
    echo $crlf;


    $yubikey_class = new MultiotpYubikey();
    
    //====================================================================
    // TEST: Hexa to ModHex
    $tests++;
    echo $b_on."Check Hexa to ModHex encoding".$b_off.$crlf;
    $source   = '0123456789abcdef';
    $expected = 'cbdefghijklnrtuv';
    if (FALSE === ($result = $yubikey_class->HexToModHex($source)))
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Hexa to ModHex encoding failed".$crlf;
    }
    elseif ($expected == $result)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Hexa to ModHex encoding successful ($result)".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Hexa to ModHex encoding failed ($result instead of $expected)".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: ModHex to Hexa
    $tests++;
    echo $b_on."Check ModHex to Hexa decoding".$b_off.$crlf;
    
    $source   = 'cbdefghijklnrtuv';
    $expected = '0123456789abcdef';
    if (FALSE === ($result = $yubikey_class->ModHexToHex($source)))
    {
        echo "- ".$ko_on.'KO!'.$ko_off." ModHex to Hexa encoding failed".$crlf;
    }
    elseif ($expected == $result)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." ModHex to Hexa encoding successful ($result)".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." ModHex to Hexa encoding failed ($result instead of $expected)".$crlf;
    }
    echo $crlf;


   //====================================================================
    // Delete the user test_user if it exists
    echo $i_on;
    echo "Deleting the test_user".$crlf;
    if (!$multiotp->DeleteUser('test_user', TRUE))
    {
        echo "- INFO: User test_user doesn't exist yet".$crlf;
    }
    else
    {
        echo "- INFO: User test_user successfully deleted".$crlf;
    }
    echo $i_off;
    echo $crlf;


    //====================================================================
    // Delete the token test_token if it exists
    echo $i_on;
    echo "Deleting the test_token".$crlf;
    if (!$multiotp->DeleteToken('test_token'))
    {
        echo "- INFO: Token test_token doesn't exist yet".$crlf;
    }
    else
    {
        echo "- INFO: Token test_token successfully deleted".$crlf;
    }
    echo $i_off;
    echo $crlf;


    //====================================================================
    // TEST: Creating token test_token with the RFC test values HOTP token
    $tests++;
    echo $b_on."Creating token test_token with the RFC test values HOTP token".$b_off.$crlf;
    if ($multiotp->CreateToken('test_token', 'HOTP', '3132333435363738393031323334353637383930', 6, -1))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token test_token successfully created".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Creation of test_token token failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Creating user test_user with the HOTP RFC test token test_token created before
    $tests++;
    echo $b_on."Creating user test_user with the HOTP RFC test token test_token created before".$b_off.$crlf;
    if (!$multiotp->CreateUserFromToken('test_user', 'test_token'))
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Token test_token doesn't exist".$crlf;
    }
    else
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User test_user successfully created with token test_token".$crlf;
        $successes++;
    }
    $multiotp->SetUser('test_user');
    $multiotp->SetUserPrefixPin(0);
    $multiotp->WriteUserData();
    echo $crlf;


    //====================================================================
    // TEST: Authenticating test_user with the first token of the RFC test values
    $tests++;
    echo $b_on."Authenticating test_user with the first token of the RFC test values".$b_off.$crlf;
    $multiotp->SetUser('test_user');
    if (0 == ($error = $multiotp->CheckToken('755224')))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token of the user test_user successfully accepted".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Error authenticating the user test_user with the first token".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Testing the replay rejection
    $tests++;
    echo $b_on."Testing the replay rejection".$b_off.$crlf;
    $multiotp->SetUser('test_user');
    if (0 != ($error = $multiotp->CheckToken('755224')))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token of the user test_user successfully REJECTED (replay)".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Replayed token *WRONGLY* accepted".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Resynchronizing the key
    $tests++;
    echo $b_on."Resynchronizing the key".$b_off.$crlf;
    if ($multiotp->ResyncUserToken('test_user', '338314', '254676', (!$browser_mode)))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token of the user test_user successfully resynchronized".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Token of the user test_user NOT resynchronized".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Testing a false resynchronisation (in the past, may take some time)
    $tests++;
    echo $b_on."Testing a false resynchronisation (in the past, may take some time)".$b_off.$crlf;
    $multiotp->SetUser('test_user');
    $start_time = time();
    if (!$multiotp->ResyncToken('287082', '359152', (!$browser_mode)))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token of test_user successfully NOT resynchronized (in the past), in less than ".(1+time()-$start_time)." second(s) ".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Token of user test_user *WRONGLY* resynchronized".$crlf;
    }
    echo $crlf;


    //====================================================================
    // Delete the user test_user8 if it exists
    echo $i_on;
    echo "Deleting the test_user8".$crlf;
    if (!$multiotp->DeleteUser('test_user8', TRUE))
    {
        echo "- INFO: User test_user8 doesn't exist yet".$crlf;
    }
    else
    {
        echo "- INFO: User test_user8 successfully deleted".$crlf;
    }
    echo $i_off;
    echo $crlf;


    //====================================================================
    // Delete the token test_token8 if it exists
    echo $i_on;
    echo "Deleting the test_token8".$crlf;
    if (!$multiotp->DeleteToken('test_token8'))
    {
        echo "- INFO: Token test_token8 doesn't exist yet".$crlf;
    }
    else
    {
        echo "- INFO: Token test_token8 successfully deleted".$crlf;
    }
    echo $i_off;
    echo $crlf;


    //====================================================================
    // TEST: Creating token test_token8 with the RFC test values HOTP token
    $tests++;
    echo $b_on."Creating token test_token8 with the RFC test values HOTP token".$b_off.$crlf;
    if ($multiotp->CreateToken('test_token8', 'HOTP', '3132333435363738393031323334353637383930', 8, -1))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token test_token8 successfully created".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Creation of test_token8 token failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Creating user test_user8 with the HOTP RFC test token test_token8 created before
    $tests++;
    echo $b_on."Creating user test_user8 with the HOTP RFC test token test_token8 created before".$b_off.$crlf;
    if (!$multiotp->CreateUserFromToken('test_user8', 'test_token8'))
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Token test_token8 doesn't exist".$crlf;
    }
    else
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User test_user8 successfully created with token test_token8".$crlf;
        $successes++;
    }
    $multiotp->SetUser('test_user8');
    $multiotp->SetUserPrefixPin(0);
    $multiotp->WriteUserData();
    echo $crlf;


    //====================================================================
    // TEST: Authenticating test_user8 with the first token of the RFC test values
    $tests++;
    echo $b_on."Authenticating test_user8 with the first 8 digits token of the RFC test values".$b_off.$crlf;
    $multiotp->SetUser('test_user8');
    if (0 == ($error = $multiotp->CheckToken('84755224')))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token of the user test_user8 successfully accepted".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Error authenticating the user test_user8 with the first token".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Deleting the test_user2 if it exists
    echo $i_on;
    echo "Deleting the test_user2".$crlf;
    if (!$multiotp->DeleteUser('test_user2', TRUE))
    {
        echo "- INFO: User test_user2 doesn't exist yet".$crlf;
    }
    else
    {
        echo "- INFO: User test_user2 successfully deleted".$crlf;
    }
    echo $i_off;
    echo $crlf;


    //====================================================================
    // TEST: Creating user test_user2 with the RFC test values HOTP token and PIN prefix
    $tests++;
    echo $b_on."Creating user test_user2 with the RFC test values HOTP token and PIN prefix".$b_off.$crlf;
    if ($multiotp->CreateUser('test_user2',1,'HOTP','3132333435363738393031323334353637383930','1234',6,0))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User test_user2 successfully created".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Creation of user test_user2 failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Authenticating test_user2 with the first token of the RFC test values with PIN
    $tests++;
    echo $b_on."Authenticating test_user2 with the first token of the RFC test values with PIN".$b_off.$crlf;
    $multiotp->SetUser('test_user2');
    if (0 == ($error = $multiotp->CheckToken('1234755224')))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token of the user test_user2 (with prefix PIN) successfully accepted".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Error #".$error." authenticating user test_user2 with the first token and PIN prefix".$crlf;
    }
    echo $crlf;


    //====================================================================
    // Delete the user fast_user if it exists
    echo $i_on;
    echo "Deleting the user fast_user".$crlf;
    if (!$multiotp->DeleteUser('fast_user', TRUE))
    {
        echo "- INFO: User fast_user doesn't exist yet".$crlf;
    }
    else
    {
        echo "- INFO: User fast_user successfully deleted".$crlf;
    }
    echo $i_off;
    echo $crlf;


    //====================================================================
    // Delete the user fast_user_renamed if it exists
    echo $i_on;
    echo "Deleting the user fast_user_renamed".$crlf;
    if (!$multiotp->DeleteUser('fast_user_renamed', TRUE))
    {
        echo "- INFO: User fast_user_renamed doesn't exist yet".$crlf;
    }
    else
    {
        echo "- INFO: User fast_user_renamed successfully deleted".$crlf;
    }
    echo $i_off;
    echo $crlf;


    //====================================================================
    // TEST: Creating user fast_user using the one parameter FastCreateUser() function
    $tests++;
    echo $b_on."Creating user fast_user using the one parameter FastCreateUser() function".$b_off.$crlf;
    if ($multiotp->FastCreateUser('fast_user'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User fast_user successfully created".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Creation of user fast_user failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Check if user fast_user exists
    $tests++;
    echo $b_on."Check if the user fast_user exists".$b_off.$crlf;

    if ($multiotp->CheckUserExists('fast_user'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User fast_user exists".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." fast_user does not exist".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Rename user fast_user
    $tests++;
    echo $b_on."Renaming the user fast_user to fast_user_renamed".$b_off.$crlf;

    $multiotp->SetUser('fast_user');

    if ($multiotp->RenameCurrentUser('fast_user_renamed'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User fast_user successfully renamed to fast_user_renamed".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." RenameCurrentUser function failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: WriteUserData / ReadUserData with fast_user_renamed
    $tests++;
    echo $b_on."Write/Read information concerning user fast_user_renamed".$b_off.$crlf;
    $test_value = 'tESt';
    $multiotp->ReadUserData('fast_user_renamed');
    $multiotp->SetUserDescription($test_value);
    $multiotp->WriteUserData();
    $multiotp->SetUserDescription('');
    $multiotp->ReadUserData('fast_user_renamed');

    if ($test_value == $multiotp->GetUserDescription())
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Write/Read information for fast_user_renamed successfully done".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Write/Read information for fast_user_renamed failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Delete the user fast_user_renamed
    $tests++;
    echo $b_on."Deleting the user fast_user_renamed".$b_off.$crlf;
    if ($multiotp->DeleteUser('fast_user_renamed'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User fast_user fast_user_renamed successfully deleted".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." DeleteUser function failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Check if user fast_user exists
    $tests++;
    echo $b_on."Check if the user fast_user does not exist".$b_off.$crlf;

    if (!$multiotp->CheckUserExists('fast_user'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User fast_user does not exist".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." fast_user exist".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Creating a QRcode provisioning file for the HOTP RFC test token
    $tests++;
    echo $b_on."Creating a QRcode provisioning file for the HOTP RFC test token".$b_off.$crlf;
    $size_result = $multiotp->qrcode('otpauth://hotp/multiOTP hotp test?counter=0&digits=6&secret='.base32_encode(hex2bin('3132333435363738393031323334353637383930')).'&issuer=multiOTP test', $multiotp->GetScriptFolder().'qrcode/qrHOTP.png');
    if (0 < $size_result)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." HOTP QRcode successfully created".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." HOTP QRcode not created".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Creating a QRcode provisioning file for the TOTP RFC test token
    $tests++;
    echo $b_on."Creating a QRcode provisioning file for the TOTP RFC test token".$b_off.$crlf;
    $size_result = $multiotp->qrcode('otpauth://totp/multiOTP totp test?period=30&digits=6&secret='.base32_encode(hex2bin('3132333435363738393031323334353637383930')).'&issuer=multiOTP test', $multiotp->GetScriptFolder().'qrcode/qrTOTP.png');
    if (0 < $size_result)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." TOTP QRcode successfully created".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." TOTP QRcode not created".$crlf;
    }
    echo $crlf;


    //====================================================================
    // Display the QRcode in the browser using inline images
    if ($browser_mode && ($size_result > 0))
    {
        echo "Displaying inline image for the test_user (HOTP QRCode Google Auhtenticator token)".$crlf;
        echo "<img src=\"data:image/png;base64,".base64_encode($multiotp->GetUserTokenQrCode('test_user', 'multiOTP test_user token'))."\" alt=\"test_user test token\">".$crlf;
        echo $crlf;

        echo "Displaying inline image for TOTP QRCode Google Auhtenticator token".$crlf;
        $binary_result = $multiotp->qrcode('otpauth://totp/multiOTP totp test?secret='.base32_encode(hex2bin('3132333435363738393031323334353637383930')).'&digits=6&period=30&issuer=multiOTP test', "binary");
        
        echo "<img src=\"data:image/png;base64,".base64_encode($binary_result)."\" alt=\"multiOTP TOTP test token\">".$crlf;
        echo $crlf;
    }


    //====================================================================
    // TEST: Check Base32 functions
    $tests++;
    echo $b_on."Check Base32 functions".$b_off." (should return 3132333435363738393031323334353637383930)".$crlf;

    if ('3132333435363738393031323334353637383930' == bin2hex(base32_decode(base32_encode(hex2bin('3132333435363738393031323334353637383930')))))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Base32 functions successfully checked".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Base32 function failed".$crlf;
    }
    echo $crlf;

    
    //====================================================================
    // Locking the test_user
    echo $i_on;
    echo "Locking the test_user".$crlf;
    $multiotp->SetUser('test_user');
    $multiotp->SetUserErrorCounter(1000);
    $multiotp->WriteUserData();
    $multiotp->CheckToken('LOCKME');
    echo $crlf;


    //====================================================================
    // TEST: Number of existing users
    $tests++;
    echo $b_on."Number of existing users".$b_off.$crlf;
    $count = $multiotp->GetUsersCount();
    if (0 < $count)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." $count existing users".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Failed to count existing users".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: List of existing users (originally tab separated)
    $tests++;
    echo $b_on."List of existing users".$b_off.$crlf;
    $list = $multiotp->GetUsersList();
    echo str_replace("\t",", ",$list).$crlf;
    if ('' != trim($list))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." List is not empty".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." List is empty".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: List of active users (originally tab separated)
    $tests++;
    echo $b_on."List of active users".$b_off.$crlf;
    $list = $multiotp->GetActiveUsersList();
    echo str_replace("\t",", ",$list).$crlf;
    if ('' != trim($list))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." List is not empty".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." List is empty".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: List of locked users (originally tab separated)
    $tests++;
    echo $b_on."List of locked users".$b_off.$crlf;
    $list = $multiotp->GetLockedUsersList();
    echo str_replace("\t",", ",$list).$crlf;
    if ('' != trim($list))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." List is not empty".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." List is empty".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: List of existing users in an array
    $tests++;
    echo $b_on."List of existing users in an array".$b_off.$crlf;
    $counter = 0;
    foreach($multiotp->GetDetailedUsersArray() as $one_detail)
    {
        echo $one_detail['user'].': '.encode_utf8_if_needed($one_detail['description']).$crlf;
        $counter++;
    }
    if ($counter > 0)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." List is not empty".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." List is empty".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Check if user fast_user exists
    $tests++;
    echo $b_on."Check if the user fast_user does not exist".$b_off.$crlf;

    if (!$multiotp->CheckUserExists('fast_user'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." User fast_user does not exist".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." fast_user exist".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Import CSV test tokens definition file
    $tests++;
    echo $b_on."Import CSV test tokens definition file".$b_off.$crlf;

    if ($multiotp->ImportTokensFile('test-tokens.csv', 'test-tokens.csv'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." File test-tokens.csv successfully imported".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Unable to import test-tokens.csv file".$crlf;
    }
    echo $crlf;


    //====================================================================
    // List of existing tokens (originally tab separated)
    $tests++;
    echo $b_on."List of existing CSV tokens".$b_off.$crlf;
    $list = $multiotp->GetTokensList();
    echo str_replace("\t",", ",$list).$crlf;
    if (FALSE !== strpos(strtolower($list), strtolower('ABCDEF012302')))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." CSV Token ABCDEF012302 is present".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." CSV Token ABCDEF012302 is missing".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Import PSKC test tokens definition file
    $tests++;
    echo $b_on."Import PSKC test tokens definition file".$b_off.$crlf;

    if ($multiotp->ImportTokensFile('oath/pskc-hotp-aes.txt', 'pskc-hotp-aes.txt', '12345678901234567890123456789012', '1122334455667788990011223344556677889900') &&
        $multiotp->ImportTokensFile('oath/pskc-hotp-pbe.txt', 'pskc-hotp-pbe.txt', 'qwerty', 'bdaab8d648e850d25a3289364f7d7eaaf53ce581') &&
        $multiotp->ImportTokensFile('oath/pskc-totp-aes.txt', 'pskc-totp-aes.txt', '12345678901234567890123456789012', '1122334455667788990011223344556677889900') &&
        $multiotp->ImportTokensFile('oath/pskc-totp-pbe.txt', 'pskc-totp-pbe.txt', 'qwerty', 'bdaab8d648e850d25a3289364f7d7eaaf53ce581') &&
        $multiotp->ImportTokensFile('oath/tokens_hotp_aes.pskc', 'tokens_hotp_aes.pskc', '12345678901234567890123456789012', '') &&
        $multiotp->ImportTokensFile('oath/tokens_totp_aes.pskc', 'tokens_totp_aes.pskc', '12345678901234567890123456789012', '') &&
        $multiotp->ImportTokensFile('oath/tokens_hotp_pbe.pskc', 'tokens_hotp_pbe.pskc', 'qwerty', '') &&
        $multiotp->ImportTokensFile('oath/tokens_totp_pbe.pskc', 'tokens_totp_pbe.pskc', 'qwerty', '')
        // $multiotp->ImportTokensFile('oath/tokens_ocra_aes.pskc', 'tokens_ocra_aes.pskc', '12345678901234567890123456789012', '') &&
        // $multiotp->ImportTokensFile('oath/tokens_ocra_pbe.pskc', 'tokens_ocra_pbe.pskc', 'qwerty', '')
       )
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Test files from oath successfully imported".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Unable to import test files from oath".$crlf;
    }
    echo $crlf;


    //====================================================================
    // List of existing tokens (originally tab separated)
    $tests++;
    echo $b_on."List of existing tokens".$b_off.$crlf;
    $list = $multiotp->GetTokensList();
    echo str_replace("\t",", ",$list).$crlf;
    if (FALSE !== strpos(strtolower($list), strtolower('ZZ0100000000')))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token ZZ0100000000 is present".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Token ZZ0100000000 is missing".$crlf;
    }
    echo $crlf;

    
    //====================================================================
    // TEST: Rename token ZZ0100000000 to ZZ0100000001
    $tests++;
    echo $b_on."Rename token ZZ0100000000 to ZZ0100000001".$b_off.$crlf;
    $multiotp->SetToken('ZZ0100000000');
    if ($multiotp->RenameCurrentToken('ZZ0100000001'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token ZZ0100000000 successfully renamed to ZZ0100000001".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Unable to rename the token ZZ0100000000".$crlf;
        $multiotp->DeleteToken('ZZ0100000000');
    }
    echo $crlf;


    //=======================================
    // Check if the token ZZ0100000001 exists
    $tests++;
    echo $b_on."Check if the token ZZ0100000001 exists".$b_off.$crlf;
    if ($multiotp->CheckTokenExists('ZZ0100000001'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Token ZZ0100000001 exists".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Token ZZ0100000001 is missing".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: WriteTokenData / ReadTokenData with ZZ0100000001
    $tests++;
    echo $b_on."Write/Read information concerning token ZZ0100000001".$b_off.$crlf;
    $test_value = 'tEStToKeN';
    $multiotp->ReadTokenData('ZZ0100000001');
    $multiotp->SetTokenDescription($test_value);
    $multiotp->WriteTokenData();
    $multiotp->SetTokenDescription('');
    $multiotp->ReadTokenData('ZZ0100000001');

    if ($test_value == $multiotp->GetTokenDescription())
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Write/Read information concerning token ZZ0100000001 successfully done".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Write/Read information concerning token ZZ0100000001 failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Delete tokens ZZ0000000000 and ZZ0100000001
    $tests++;
    echo $b_on."Delete tokens ZZ0000000000 and ZZ0100000001".$b_off.$crlf;
    if (($multiotp->DeleteToken('ZZ0000000000')) && ($multiotp->DeleteToken('ZZ0100000001')) && (!$multiotp->CheckTokenExists('ZZ0100000001')))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Tokens ZZ0000000000 and ZZ0100000001 successfully deleted".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Failed during tokens deletion".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Create the device 123456 test_device
    $tests++;
    $multiotp->DeleteDevice(123456, TRUE);
    echo $b_on."Create the device test_device (123456)".$b_off.$crlf;
    if ($multiotp->CreateDevice(123456, 'test_device', 'test_secret', '123.124.125.126', '255.255.255.255', 'test_device', FALSE))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Device test_device successfully created".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Creation of device test_device failed".$crlf;
    }
    echo $crlf;

    
    //====================================================================
    // TEST: Read the device 123456 test_device
    $tests++;
    echo $b_on."Read the device test_device (123456)".$b_off.$crlf;
    $multiotp->SetDeviceDescription('');
    $multiotp->ReadDeviceData(123456);
    $description = $multiotp->GetDeviceDescription();
    if ('test_device' == $description)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Device test_device successfully read".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Failed to read the device test_device".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: List of existing devices (originally tab separated)
    $tests++;
    echo $b_on."List of existing devices".$b_off.$crlf;
    $list = $multiotp->GetDevicesList();
    echo str_replace("\t",", ",$list).$crlf;
    if ('' != trim($list))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." List is not empty".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." List is empty".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Delete the device test_device
    $tests++;
    echo $b_on."Delete the device test_device".$b_off.$crlf;
    if ($multiotp->DeleteDevice(123456))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Device test_device successfully deleted".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." DeleteDevice function failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Create the group 123456 test_group
    $tests++;
    $multiotp->DeleteGroup(123456, TRUE);
    echo $b_on."Create the group test_group (123456)".$b_off.$crlf;
    if ($multiotp->CreateGroup(123456, 'test_group', 'test_description'))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Group test_group successfully created".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Creation of group test_group failed".$crlf;
    }
    echo $crlf;

    
    //====================================================================
    // TEST: Read the group 123456 test_group
    $tests++;
    echo $b_on."Read the group test_group (123456)".$b_off.$crlf;
    $multiotp->SetGroupDescription('');
    $multiotp->ReadGroupData(123456);
    $description = $multiotp->GetGroupDescription();
    if ('test_description' == $description)
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Group test_group successfully read".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Failed to read the group test_group".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Check if the group 123456 test_group exists
    $tests++;
    echo $b_on."Check if the group test_group (123456) exists".$b_off.$crlf;
    if ($multiotp->CheckGroupExists(123456))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Group test_group exists".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Group test_group doesn't exists".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: List of existing groups (originally tab separated)
    $tests++;
    echo $b_on."List of existing groups".$b_off.$crlf;
    $list = $multiotp->GetGroupsList();
    echo str_replace("\t",", ",$list).$crlf;
    if ('' != trim($list))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." List is not empty".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." List is empty".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Delete the group test_group
    $tests++;
    echo $b_on."Delete the group test_group".$b_off.$crlf;
    if ($multiotp->DeleteGroup(123456))
    {
        echo "- ".$ok_on.'OK!'.$ok_off." Group test_group successfully deleted".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." DeleteGroup function failed".$crlf;
    }
    echo $crlf;


    //====================================================================
    // TEST: Show the log
    $tests++;
    echo $b_on."Show the log".$b_off.$crlf;
    if (FALSE !== ($log = $multiotp->ShowLog(TRUE)))
    {
        echo str_replace("\n",$crlf, $log);
        echo "- ".$ok_on.'OK!'.$ok_off." Log successfuly displayed".$crlf;
        $successes++;
    }
    else
    {
        echo "- ".$ko_on.'KO!'.$ko_off." Unable to show the log".$crlf;
    }
    echo $crlf;


    $backend = $default_backend;
}


//====================================================================
// TESTS result
if ($browser_mode)
{
    echo '<div id="test_result">';
}
echo $b_on;
if ($successes == $tests)
{
    echo $ok_on."OK! ALL $tests TESTS HAVE PASSED SUCCESSFULLY !".$ok_off.$crlf;
}
else
{
    echo $ko_on."KO! ONLY $successes/$tests TESTS HAVE PASSED SUCCESSFULLY !".$ko_off.$crlf;
}
echo $b_off;
if ($browser_mode)
{
    echo '</div>';
}
echo $crlf;

echo $hr;

echo "Time spent for the whole script: less than ".(1+time()-$first_time)." second(s)";
echo $crlf;
echo $crlf;

$multiotp->SetBackendType($default_backend);
$multiotp->_config_data['attributes_to_encrypt'] = '';
$multiotp->WriteConfigData();

if ($browser_mode && (!isset($GLOBALS['no_header'])))
{
    echo <<<EOWEBFOOTER
    </body>
</html>
EOWEBFOOTER;

}
?>