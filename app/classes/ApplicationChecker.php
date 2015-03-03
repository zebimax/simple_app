<?php

class ApplicationChecker
{
    public function check(messageStack $messageStack)
    {
        $oldRootDir = MVC_APP_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        if (WARN_INSTALL_EXISTENCE == 'true') {
            if (file_exists($oldRootDir . 'install')) {
                $messageStack->add('header', WARNING_INSTALL_DIRECTORY_EXISTS, 'warning');
            }
        }

// check if the configure.php file is writeable
        if (WARN_CONFIG_WRITEABLE == 'true') {
            $configureScript = $oldRootDir . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
            if ( (file_exists($configureScript)) && (is_writeable($configureScript)) ) {
                $messageStack->add('header', WARNING_CONFIG_FILE_WRITEABLE, 'warning');
            }
        }

// check if the session folder is writeable
        if (WARN_SESSION_DIRECTORY_NOT_WRITEABLE == 'true') {
            if (STORE_SESSIONS == '') {
                if (!is_dir(tep_session_save_path())) {
                    $messageStack->add('header', WARNING_SESSION_DIRECTORY_NON_EXISTENT, 'warning');
                } elseif (!is_writeable(tep_session_save_path())) {
                    $messageStack->add('header', WARNING_SESSION_DIRECTORY_NOT_WRITEABLE, 'warning');
                }
            }
        }

// check session.auto_start is disabled
        if ( (function_exists('ini_get')) && (WARN_SESSION_AUTO_START) ) {
            if (ini_get('session.auto_start') == '1') {
                $messageStack->add('header', WARNING_SESSION_AUTO_START, 'warning');
            }
        }

        if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
            if (!is_dir(DIR_FS_DOWNLOAD)) {
                $messageStack->add('header', WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT, 'warning');
            }
        }
    }
}