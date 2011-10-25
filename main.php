<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @package PhpMyAdmin
 */

/**
 * Gets some core libraries and displays a top message if required
 */
require_once './libraries/common.inc.php';

$GLOBALS['js_include'][] = 'jquery/jquery-ui-1.8.16.custom.js';
$GLOBALS['js_include'][] = 'jquery/jquery.sprintf.js';

// Handles some variables that may have been sent by the calling script
$GLOBALS['db'] = '';
$GLOBALS['table'] = '';
$show_query = '1';
require_once './libraries/header.inc.php';

// Any message to display?
if (! empty($message)) {
    PMA_showMessage($message);
    unset($message);
}

$common_url_query =  PMA_generate_common_url('', '');

// when $server > 0, a server has been chosen so we can display
// all MySQL-related information
if ($server > 0) {
    include './libraries/server_common.inc.php';
    include './libraries/StorageEngine.class.php';
    include './libraries/server_links.inc.php';

    // Use the verbose name of the server instead of the hostname
    // if a value is set
    $server_info = '';
    if (! empty($cfg['Server']['verbose'])) {
        $server_info .= htmlspecialchars($cfg['Server']['verbose']);
        if ($GLOBALS['cfg']['ShowServerInfo']) {
            $server_info .= ' (';
        }
    }
    if ($GLOBALS['cfg']['ShowServerInfo'] || empty($cfg['Server']['verbose'])) {
        $server_info .= PMA_DBI_get_host_info();
    }
    if (! empty($cfg['Server']['verbose']) && $GLOBALS['cfg']['ShowServerInfo']) {
    $server_info .= ')';
    }
    $mysql_cur_user_and_host = PMA_DBI_fetch_value('SELECT USER();');

    // should we add the port info here?
    $short_server_info = (!empty($GLOBALS['cfg']['Server']['verbose'])
                ? $GLOBALS['cfg']['Server']['verbose']
                : $GLOBALS['cfg']['Server']['host']);
}

echo '<div id="maincontainer">' . "\n";
echo '<div id="main_pane_left">';
if ($server > 0
 || (! $cfg['LeftDisplayServers'] && count($cfg['Servers']) > 1)) {
    echo '<div class="group">';
    echo '<h2>' . __('General Settings') . '</h2>';
    echo '<ul>';

    /**
     * Displays the MySQL servers choice form
     */
    if (! $cfg['LeftDisplayServers']
     && (count($cfg['Servers']) > 1 || $server == 0 && count($cfg['Servers']) == 1)) {
        echo '<li id="li_select_server">';
        include_once './libraries/select_server.lib.php';
        PMA_select_server(true, true);
        echo '</li>';
    }

    /**
     * Displays the mysql server related links
     */
    if ($server > 0 && !PMA_DRIZZLE) {
        include_once './libraries/check_user_privileges.lib.php';

        // Logout for advanced authentication
        if ($cfg['Server']['auth_type'] != 'config') {
            if ($cfg['ShowChgPassword']) {
                if ($GLOBALS['cfg']['AjaxEnable']) {
                    $conditional_class = 'ajax';
                } else {
                    $conditional_class = null;
                }
                PMA_printListItem(__('Change password'), 'li_change_password',
                    './user_password.php?' . $common_url_query, null, null, 'change_password_anchor', null, $conditional_class);
            }
        } // end if
        echo '    <li id="li_select_mysql_collation">';
        echo '        <form method="post" action="index.php" target="_parent">' . "\n"
           . PMA_generate_common_hidden_inputs(null, null, 4, 'collation_connection')
           . '            <label for="select_collation_connection">' . "\n"
           . '                ' . __('Server connection collation') . "\n"
           // put the doc link in the form so that it appears on the same line
           . PMA_showMySQLDocu('MySQL_Database_Administration', 'Charset-connection') . ': ' .  "\n"
           . '            </label>' . "\n"

           . PMA_generateCharsetDropdownBox(PMA_CSDROPDOWN_COLLATION, 'collation_connection', 'select_collation_connection', $collation_connection, true, 4, true)
           . '            <noscript><input type="submit" value="' . __('Go') . '" /></noscript>' . "\n"
           . '        </form>' . "\n"
           . '    </li>' . "\n";
    } // end of if ($server > 0 && !PMA_DRIZZLE)
    echo '</ul>';
    echo '</div>';
}

echo '<div class="group">';
echo '<h2>' . __('Appearance Settings') . '</h2>';
echo '  <ul>';

// Displays language selection combo
if (empty($cfg['Lang'])) {
    echo '<li id="li_select_lang">';
    include_once './libraries/display_select_lang.lib.php';
    PMA_select_language();
    echo '</li>';
}

// ThemeManager if available

if ($GLOBALS['cfg']['ThemeManager']) {
    echo '<li id="li_select_theme">';
    echo $_SESSION['PMA_Theme_Manager']->getHtmlSelectBox();
    echo '</li>';
}
echo '<li id="li_select_fontsize">';
echo PMA_Config::getFontsizeForm();
echo '</li>';

echo '</ul>';

// User preferences

if ($server > 0) {
    echo '<ul>';
    echo PMA_printListItem(__('More settings'), 'li_user_preferences',
                    './prefs_manage.php?' . $common_url_query);
    echo '</ul>';
}

echo '</div>';


echo '</div>';
echo '<div id="main_pane_right">';


if ($server > 0 && $GLOBALS['cfg']['ShowServerInfo']) {
    echo '<div class="group">';
    echo '<h2>' . __('Database server') . '</h2>';
    echo '<ul>' . "\n";
    PMA_printListItem(__('Server') . ': ' . $server_info, 'li_server_info');
    PMA_printListItem(__('Software') . ': ' . PMA_getServerType(), 'li_server_type');
    PMA_printListItem(__('Software version') . ': ' . PMA_MYSQL_STR_VERSION . ' - ' . PMA_MYSQL_VERSION_COMMENT, 'li_server_version');
    PMA_printListItem(__('Protocol version') . ': ' . PMA_DBI_get_proto_info(),
        'li_mysql_proto');
    PMA_printListItem(__('User') . ': ' . htmlspecialchars($mysql_cur_user_and_host),
        'li_user_info');

    echo '    <li id="li_select_mysql_charset">';
    echo '        ' . __('Server charset') . ': '
       . '        <span xml:lang="en" dir="ltr">'
       . '           ' . $mysql_charsets_descriptions[$mysql_charset_map['utf-8']] . "\n"
       . '           (' . $mysql_charset_map['utf-8'] . ')' . "\n"
       . '        </span>' . "\n"
       . '    </li>' . "\n";
    echo '  </ul>';
    echo ' </div>';
}

if ($GLOBALS['cfg']['ShowServerInfo'] || $GLOBALS['cfg']['ShowPhpInfo']) {
    echo '<div class="group">';
    echo '<h2>' . __('Web server') . '</h2>';
    echo '<ul>';
    if ($GLOBALS['cfg']['ShowServerInfo']) {
        PMA_printListItem($_SERVER['SERVER_SOFTWARE'], 'li_web_server_software');

        if ($server > 0) {
            $client_version_str = PMA_DBI_get_client_info();
            if (preg_match('#\d+\.\d+\.\d+#', $client_version_str)
                    && in_array($GLOBALS['cfg']['Server']['extension'], array('mysql', 'mysqli'))) {
                $client_version_str = 'libmysql - ' . $client_version_str;
            }
            PMA_printListItem(__('Database client version') . ': ' . $client_version_str,
                'li_mysql_client_version');
            PMA_printListItem(__('PHP extension') . ': ' . $GLOBALS['cfg']['Server']['extension']. ' '
                    . PMA_showPHPDocu('book.' . $GLOBALS['cfg']['Server']['extension'] . '.php'),
                'li_used_php_extension');
        }
    }

    if ($cfg['ShowPhpInfo']) {
        PMA_printListItem(__('Show PHP information'), 'li_phpinfo', './phpinfo.php?' . $common_url_query);
    }
    echo '  </ul>';
    echo ' </div>';
}

echo '<div class="group pmagroup">';
echo '<h2>phpMyAdmin</h2>';
echo '<ul>';
$class = null;
// workaround for bug 3302733; some browsers don't like the situation
// where phpMyAdmin is called on a secure page but a part of the page
// (the version check) refers to a non-secure page
if ($GLOBALS['cfg']['VersionCheck'] && ! $GLOBALS['PMA_Config']->get('is_https')) {
    $class = 'jsversioncheck';
}
PMA_printListItem(__('Version information') . ': ' . PMA_VERSION, 'li_pma_version', null, null, null, null, $class);
PMA_printListItem(__('Documentation'), 'li_pma_docs', 'Documentation.html', null, '_blank');
PMA_printListItem(__('Wiki'), 'li_pma_wiki', PMA_linkURL('http://wiki.phpmyadmin.net/'), null, '_blank');

// does not work if no target specified, don't know why
PMA_printListItem(__('Official Homepage'), 'li_pma_homepage', PMA_linkURL('http://www.phpMyAdmin.net/'), null, '_blank');
PMA_printListItem(__('Contribute'), 'li_pma_contribute', PMA_linkURL('http://www.phpmyadmin.net/home_page/improve.php'), null, '_blank');
PMA_printListItem(__('Get support'), 'li_pma_support', PMA_linkURL('http://www.phpmyadmin.net/home_page/support.php'), null, '_blank');
PMA_printListItem(__('List of changes'), 'li_pma_changes', PMA_linkURL('changelog.php'), null, '_blank');
?>
    </ul>
 </div>

</div>

<?php
/**
 * BUG: MSIE needs two <br /> here, otherwise it will not extend the outer div to the
 * full height of the inner divs
 */
?>
<br class="clearfloat" />
<br class="clearfloat" />
</div>

<?php
/**
 * Warning if using the default MySQL privileged account
 */
if ($server != 0
 && $cfg['Server']['user'] == 'root'
 && $cfg['Server']['password'] == '') {
    trigger_error(__('Your configuration file contains settings (root with no password) that correspond to the default MySQL privileged account. Your MySQL server is running with this default, is open to intrusion, and you really should fix this security hole by setting a password for user \'root\'.'), E_USER_WARNING);
}

/**
 * Nijel: As we try to handle charsets by ourself, mbstring overloads just
 * break it, see bug 1063821.
 */
if (@extension_loaded('mbstring') && @ini_get('mbstring.func_overload') > 1) {
    trigger_error(__('You have enabled mbstring.func_overload in your PHP configuration. This option is incompatible with phpMyAdmin and might cause some data to be corrupted!'), E_USER_WARNING);
}

/**
 * Nijel: mbstring is used for handling multibyte inside parser, so it is good
 * to tell user something might be broken without it, see bug #1063149.
 */
if (! @extension_loaded('mbstring')) {
    trigger_error(__('The mbstring PHP extension was not found and you seem to be using a multibyte charset. Without the mbstring extension phpMyAdmin is unable to split strings correctly and it may result in unexpected results.'), E_USER_WARNING);
}

/**
 * Check whether session.gc_maxlifetime limits session validity.
 */
$gc_time = (int)@ini_get('session.gc_maxlifetime');
if ($gc_time < $GLOBALS['cfg']['LoginCookieValidity'] ) {
    trigger_error(PMA_Message::decodeBB(__('Your PHP parameter [a@http://php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime@]session.gc_maxlifetime[/a] is lower that cookie validity configured in phpMyAdmin, because of this, your login will expire sooner than configured in phpMyAdmin.')), E_USER_WARNING);
}

/**
 * Check whether LoginCookieValidity is limited by LoginCookieStore.
 */
if ($GLOBALS['cfg']['LoginCookieStore'] != 0 && $GLOBALS['cfg']['LoginCookieStore'] < $GLOBALS['cfg']['LoginCookieValidity']) {
    trigger_error(PMA_Message::decodeBB(__('Login cookie store is lower than cookie validity configured in phpMyAdmin, because of this, your login will expire sooner than configured in phpMyAdmin.')), E_USER_WARNING);
}

/**
 * Check if user does not have defined blowfish secret and it is being used.
 */
if (!empty($_SESSION['auto_blowfish_secret']) &&
        empty($GLOBALS['cfg']['blowfish_secret'])) {
    trigger_error(__('The configuration file now needs a secret passphrase (blowfish_secret).'), E_USER_WARNING);
}

/**
 * Check for existence of config directory which should not exist in
 * production environment.
 */
if (file_exists('./config')) {
    trigger_error(__('Directory [code]config[/code], which is used by the setup script, still exists in your phpMyAdmin directory. You should remove it once phpMyAdmin has been configured.'), E_USER_WARNING);
}

if ($server > 0) {
    $cfgRelation = PMA_getRelationsParam();
    if (! $cfgRelation['allworks'] && $cfg['PmaNoRelation_DisableWarning'] == false) {
        $message = PMA_Message::notice(__('The phpMyAdmin configuration storage is not completely configured, some extended features have been deactivated. To find out why click %shere%s.'));
        $message->addParam('<a href="' . $cfg['PmaAbsoluteUri'] . 'chk_rel.php?' . $common_url_query . '">', false);
        $message->addParam('</a>', false);
        /* Show error if user has configured something, notice elsewhere */
        if (!empty($cfg['Servers'][$server]['pmadb'])) {
            $message->isError(true);
        }
        $message->display();
    } // end if
}

/**
 * Show notice when javascript support is missing.
 */
echo '<noscript>';
$message = PMA_Message::notice(__('Javascript support is missing or disabled in your browser, some phpMyAdmin functionality will be missing. For example navigation frame will not refresh automatically.'));
$message->isError(true);
$message->display();
echo '</noscript>';

/**
 * Warning about different MySQL library and server version
 * (a difference on the third digit does not count).
 * If someday there is a constant that we can check about mysqlnd, we can use it instead
 * of strpos().
 * If no default server is set, PMA_DBI_get_client_info() is not defined yet.
 * Drizzle can speak MySQL protocol, so don't warn about version mismatch for Drizzle servers.
 */
if (function_exists('PMA_DBI_get_client_info') && !PMA_DRIZZLE) {
    $_client_info = PMA_DBI_get_client_info();
    if ($server > 0 && strpos($_client_info, 'mysqlnd') === false && substr(PMA_MYSQL_CLIENT_API, 0, 3) != substr(PMA_MYSQL_INT_VERSION, 0, 3)) {
        trigger_error(PMA_sanitize(sprintf(__('Your PHP MySQL library version %s differs from your MySQL server version %s. This may cause unpredictable behavior.'),
                $_client_info,
                substr(PMA_MYSQL_STR_VERSION, 0, strpos(PMA_MYSQL_STR_VERSION . '-', '-')))),
            E_USER_NOTICE);
    }
    unset($_client_info);
}

/**
 * Warning about Suhosin
 */
if ($cfg['SuhosinDisableWarning'] == false && @ini_get('suhosin.request.max_value_length')) {
    trigger_error(PMA_sanitize(sprintf(__('Server running with Suhosin. Please refer to %sdocumentation%s for possible issues.'), '[a@./Documentation.html#faq1_38@_blank]', '[/a]')), E_USER_WARNING);
    }

/**
 * Warning about mcrypt.
 */
if (!function_exists('mcrypt_encrypt') && !$GLOBALS['cfg']['McryptDisableWarning']) {
    PMA_warnMissingExtension('mcrypt');
}

/**
 * Warning about incomplete translations.
 *
 * The data file is created while creating release by ./scripts/remove-incomplete-mo
 */
if (file_exists('./libraries/language_stats.inc.php')) {
    include './libraries/language_stats.inc.php';
    /*
     * This message is intentionally not translated, because we're
     * handling incomplete translations here and focus on english
     * speaking users.
     */
    if (isset($GLOBALS['language_stats'][$lang]) && $GLOBALS['language_stats'][$lang] < $cfg['TranslationWarningThreshold']) {
        trigger_error('You are using an incomplete translation, please help to make it better by <a href="http://www.phpmyadmin.net/home_page/improve.php#translate" target="_blank">contributing</a>.', E_USER_NOTICE);
    }
}

/**
 * prints list item for main page
 *
 * @param string  $name   displayed text
 * @param string  $id     id, used for css styles
 * @param string  $url    make item as link with $url as target
 * @param string  $mysql_help_page  display a link to MySQL's manual
 * @param string  $target special target for $url
 * @param string  $a_id   id for the anchor, used for jQuery to hook in functions
 * @param string  $class  class for the li element
 * @param string  $a_class  class for the anchor element
 */
function PMA_printListItem($name, $id = null, $url = null, $mysql_help_page = null, $target = null, $a_id = null, $class = null, $a_class = null)
{
    echo '<li id="' . $id . '"';
    if (null !== $class) {
        echo ' class="' . $class . '"';
    }
    echo '>';
    if (null !== $url) {
        echo '<a href="' . $url . '"';
        if (null !== $target) {
           echo ' target="' . $target . '"';
        }
        if (null != $a_id) {
            echo ' id="' . $a_id .'"';
        }
        if (null != $a_class) {
            echo ' class="' . $a_class .'"';
        }
        echo '>';
    }

    echo $name;

    if (null !== $url) {
        echo '</a>' . "\n";
    }
    if (null !== $mysql_help_page) {
        echo PMA_showMySQLDocu('', $mysql_help_page);
    }
    echo '</li>';
}

/**
 * Displays the footer
 */
require './libraries/footer.inc.php';
?>
