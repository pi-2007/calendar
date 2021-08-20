<?php
/**
 * Encryption class
 * (Copied by JodliDev from https://github.com/mstilkerich/rcmcarddav/blob/master/carddav.php)
 *
 * @author Jorge López Pérez <jorge@adobo.org> (original author)
 * @author JodliDev <jodlidev@gmail.com>
 *
 *
 */

class Encryption {
    private function getDesKey()
    {
        $rcube = rcube::get_instance();
        $imap_password = $rcube->decrypt((string) $_SESSION['password']);
        
        if ($imap_password === false || strlen($imap_password) == 0) {
            throw new \Exception('No password available to use for encryption');
        }
        
        while (strlen($imap_password) < 24) {
            $imap_password .= $imap_password;
        }
        return substr($imap_password, 0, 24);
    }
    
    /**
     * Converts a password to storage format according to the password storage scheme setting.
     *
     * @param string $clear The password in clear text.
     * @return string The password in storage format (e.g. encrypted with user password as key)
     * @throws Exception
     */
    public function encrypt($clear) {
        // encrypted with IMAP password
        $rcube = rcube::get_instance();
    
        $imap_password = $this->getDesKey();
        $rcube->config->set('carddav_des_key', $imap_password);
    
        $crypted = $rcube->encrypt($clear, 'carddav_des_key');
    
        // there seems to be no way to unset a preference
        $rcube->config->set('carddav_des_key', '');
    
        if ($crypted === false) {
            throw new \Exception('Password encryption with user password failed');
        }
    
        return $crypted;
    }
    
    public function decrypt($crypt) {
        try {
            $rcube = rcube::get_instance();
        
            $imap_password = $this->getDesKey();
            $rcube->config->set('carddav_des_key', $imap_password);
            $clear = $rcube->decrypt($crypt, 'carddav_des_key');
            // there seems to be no way to unset a preference
            $rcube->config->set('carddav_des_key', '');
            if ($clear === false) {
                $clear = '';
            }
        
            return $clear;
        } catch (\Exception $e) {
            return "";
        }
    }
}
?>
