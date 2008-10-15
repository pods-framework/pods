<?php
/*
Plugin Name: Pods
Plugin URI: http://wp-pods.googlecode.com
Description: Allows posts to be treated like CMS modules.
Version: 1.0.5
Author: Matt Gibbs
Author URI: http://pods.uproot.us/

Copyright 2008  Matt Gibbs  (email : logikal16@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function initialize()
{
    $sql = "CREATE TABLE IF NOT EXISTS wp_pod (
        id INT unsigned auto_increment primary key,
        row_id INT unsigned,
        post_id INT unsigned,
        datatype TINYINT unsigned
    )";
    mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);

    $sql = "CREATE TABLE IF NOT EXISTS wp_pod_types (
        id INT unsigned auto_increment primary key,
        name VARCHAR(32),
        description VARCHAR(128),
        list_filters TEXT,
        tpl_detail TEXT,
        tpl_list TEXT
    )";
    mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);

    $sql = "CREATE TABLE IF NOT EXISTS wp_pod_fields (
        id INT unsigned auto_increment primary key,
        datatype TINYINT unsigned,
        name VARCHAR(32),
        coltype VARCHAR(4),
        pickval VARCHAR(32),
        sister_field_id INT unsigned,
        weight TINYINT
    )";
    mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);

    $sql = "CREATE TABLE IF NOT EXISTS wp_pod_rel (
        id INT unsigned auto_increment primary key,
        post_id INT unsigned,
        sister_post_id INT unsigned,
        field_id INT unsigned,
        term_id INT unsigned
    )";
    mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);

    // Add the country and state tables
    $result = mysql_query("SHOW TABLES LIKE '%tbl_country'");
    if (1 > mysql_num_rows($result))
    {
        $countries = "Afghanistan|Aland Islands|Albania|Algeria|American Samoa|Andorra|Angola|Anguilla|Antarctica|Antigua And Barbuda|Argentina|Armenia|Aruba|Australia|Austria|Azerbaijan|Bahamas|Bahrain|Bangladesh|Barbados|Belarus|Belgium|Belize|Benin|Bermuda|Bhutan|Bolivia|Bosnia and Herzegowina|Botswana|Bouvet Island|Brazil|British Indian Ocean Territory|Brunei Darussalam|Bulgaria|Burkina Faso|Burundi|Cambodia|Cameroon|Canada|Cape Verde|Cayman Islands|Central African Republic|Chad|Chile|China|Christmas Island|Cocos (Keeling) Islands|Colombia|Comoros|Congo|Congo, the Democratic Republic of the|Cook Islands|Costa Rica|Cote d'Ivoire|Croatia|Cuba|Cyprus|Czech Republic|Denmark|Djibouti|Dominica|Dominican Republic|Ecuador|Egypt|El Salvador|Equatorial Guinea|Eritrea|Estonia|Ethiopia|Falkland Islands (Malvinas)|Faroe Islands|Fiji|Finland|France|French Guiana|French Polynesia|French Southern Territories|Gabon|Gambia|Georgia|Germany|Ghana|Gibraltar|Great Britain|Greece|Greenland|Grenada|Guadeloupe|Guam|Guatemala|Guernsey|Guinea|Guinea-Bissau|Guyana|Haiti|Heard and McDonald Islands|Holy See (Vatican City State)|Honduras|Hong Kong|Hungary|Iceland|India|Indonesia|Iran, Islamic Republic of|Iraq|Ireland|Isle of Man|Israel|Italy|Jamaica|Japan|Jersey|Jordan|Kazakhstan|Kenya|Kiribati|Korea, Democratic People's Republic of|Korea, Republic of|Kuwait|Kyrgyzstan|Lao People's Democratic Republic|Latvia|Lebanon|Lesotho|Liberia|Libyan Arab Jamahiriya|Liechtenstein|Lithuania|Luxembourg|Macao|Macedonia, The Former Yugoslav Republic Of|Madagascar|Malawi|Malaysia|Maldives|Mali|Malta|Marshall Islands|Martinique|Mauritania|Mauritius|Mayotte|Mexico|Micronesia, Federated States of|Moldova, Republic of|Monaco|Mongolia|Montenegro|Montserrat|Morocco|Mozambique|Myanmar|Namibia|Nauru|Nepal|Netherlands|Netherlands Antilles|New Caledonia|New Zealand|Nicaragua|Niger|Nigeria|Niue|Norfolk Island|Northern Mariana Islands|Norway|Oman|Pakistan|Palau|Palestinian Territory, Occupied|Panama|Papua New Guinea|Paraguay|Peru|Philippines|Pitcairn|Poland|Portugal|Puerto Rico|Qatar|Reunion|Romania|Russian Federation|Rwanda|Saint Barthelemy|Saint Helena|Saint Kitts and Nevis|Saint Lucia|Saint Pierre and Miquelon|Saint Vincent and the Grenadines|Samoa|San Marino|Sao Tome and Principe|Saudi Arabia|Senegal|Serbia|Seychelles|Sierra Leone|Singapore|Slovakia|Slovenia|Solomon Islands|Somalia|South Africa|South Georgia and the South Sandwich Islands|Spain|Sri Lanka|Sudan|Suriname|Svalbard and Jan Mayen|Swaziland|Sweden|Switzerland|Syrian Arab Republic|Taiwan, Province of China|Tajikistan|Tanzania, United Republic of|Thailand|Timor-Leste|Togo|Tokelau|Tonga|Trinidad and Tobago|Tunisia|Turkey|Turkmenistan|Turks and Caicos Islands|Tuvalu|Uganda|Ukraine|United Arab Emirates|United Kingdom|United States|United States Minor Outlying Islands|Uruguay|Uzbekistan|Vanuatu|Venezuela|Viet Nam|Virgin Islands, British|Virgin Islands, U.S.|Wallis and Futuna|Western Sahara|Yemen|Zambia|Zimbabwe";
        $countries = str_replace('|', "'),('", mysql_real_escape_string($countries));
        mysql_query("CREATE TABLE tbl_country (id int unsigned auto_increment primary key, name varchar(64))");
        mysql_query("INSERT INTO tbl_country (name) VALUES ('$countries')");

        $states = "Alabama|Alaska|Arizona|Arkansas|California|Colorado|Connecticut|Delaware|District of Columbia|Florida|Georgia|Hawaii|Idaho|Illinois|Indiana|Iowa|Kansas|Kentucky|Louisiana|Maine|Maryland|Massachussetts|Michigan|Minnesota|Mississippi|Missouri|Montana|Nebraska|Nevada|New Hampshire|New Mexico|New Jersey|New York|North Carolina|North Dakota|Ohio|Oklahoma|Oregon|Pennsylvania|Rhode Island|South Carolina|South Dakota|Tennessee|Texas|Utah|Virginia|Vermont|Washington|West Virginia|Wisconsin|Wyoming";
        $states = str_replace('|', "'),('", $states);
        mysql_query("CREATE TABLE tbl_state (id int unsigned auto_increment primary key, name varchar(64))");
        mysql_query("INSERT INTO tbl_state (name) VALUES ('$states')");
    }
}

function adminMenu()
{
    // Add new box under Manage > Posts
    add_meta_box('pod', 'Choose a Pod', 'edit_post_page', 'post', 'normal', 'high');

    // Add a new submenu under Manage
    add_management_page('Pods', 'Pods', 8, 'pods', 'edit_options_page');
}

function edit_post_page()
{
    include realpath(dirname(__FILE__) . '/edit-post.php');
}

function edit_options_page()
{
    include realpath(dirname(__FILE__) . '/options.php');
}

function deletePost($post_ID)
{
    mysql_query("DELETE FROM wp_pod WHERE post_id = $post_ID LIMIT 1");
    mysql_query("DELETE FROM wp_pod_rel WHERE post_id = $post_ID");
}

function redirect()
{
    if (is_page() || is_404())
    {
        $uri = explode('?', $_SERVER['REQUEST_URI']);
        $uri = ('/' == substr($uri[0], 0, 1)) ? substr($uri[0], 1) : $uri[0];
        $uri = ('/' == substr($uri, -1)) ? substr($uri, 0, -1) : $uri;
        $uri = empty($uri) ? array('home') : explode('/', $uri);

        // See if the hierarchical template exists
        for ($i = count($uri); $i > 0; $i--)
        {
            $uri_string = implode('/', $uri);
            $tpl_path = realpath(dirname(__FILE__) . "/pages/$uri_string.tpl");
            if (file_exists($tpl_path))
            {
                include realpath(dirname(__FILE__) . '/router.php');
                return;
            }
            array_pop($uri);
        }
    }
}

// Create the DB tables, get the gears turning
initialize();

// Hook for adding admin menus
add_action('admin_menu', 'adminMenu');

// Hook for post deletion
add_action('delete_post', 'deletePost');

// Hook for redirection
add_action('template_redirect', 'redirect');

