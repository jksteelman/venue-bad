<?phprequire_once(UL_INC_DIR . '/LoginBackend.inc.php');class ulSsh2LoginBackend extends ulLoginBackend{    private $RemoteHost;    private $RemotePort;    private $RemoteFingerprint;    public function __construct($remote_host = NULL, $remote_port = NULL, $remote_fingerprint = NULL)    {        if ($remote_host == NULL)            $remote_host = UL_SSH2_REMOTE_HOST;        $this->RemoteHost = $remote_host;        if ($remote_port == NULL)            $remote_port = UL_SSH2_REMOTE_PORT;        $this->RemotePort = $remote_port;        if ($remote_fingerprint == NULL)            $remote_fingerprint = UL_SSH2_REMOTE_FINGERPRINT;        $this->RemoteFingerprint = $remote_fingerprint;    }    // Returns true if remember-me functionality can be used    // with this backend.    public function IsAutoLoginAllowed()    {        return true;    }    // Returns true if it is possible to perform user authentication by the    // current settings. False otherwise.  Used to check cnfiguration.    public function AuthTest()    {        return function_exists('ssh2_connect');    }    // Tries to authenticate a user against the backend.    // Returns true is sccessfully authenticated,    // or an error code otherwise.    public function Authenticate($uid, $pass)    {        $this->AuthResult = false;        // Connect        $con = ssh2_connect($this->RemoteHost, $this->RemotePort);        if ($con === false)            return ulLoginBackend::ERROR;        // Check fingerprint        if ($this->RemoteFingerprint != '') {            if (ssh2_fingerprint($con, SSH2_FINGERPRINT_SHA1 | SSH2_FINGERPRINT_HEX) != $this->RemoteFingerprint)                return ulLoginBackend::ERROR;        }        // Test if server supports password-based authentication        $auth_methods = ssh2_auth_none($con, 'user');        if (!in_array('password', $auth_methods))            return ulLoginBackend::ERROR;        // Connect again, because we can only try to authenticate once on a connection        $con = ssh2_connect($this->RemoteHost, $this->RemotePort);        if ($con === false)            return ulLoginBackend::ERROR;        // Try to authenticate        if (ssh2_auth_password($con, $uid, $pass)) {            $this->AuthResult = $uid;            return true;        } else {            return ulLoginBackend::BAD_CREDENTIALS;        }    }    // Given the backend-specific unique identifier, returns    // a unique identifier that can be displayed to the user.    // False on error.    public function Username($uid)    {        return $uid;    }    // Given a user-friendly unique identifier, returns    // a backed-specific unique identifier.    // False on error.    public function Uid($username)    {        return $username;    }    // Sets the timestamp of the last login for the    // specified user to NOW. True on success or error code.    public function UpdateLastLoginTime($uid)    {        return true;    }    // Creates a new login for a user.    // Returns true if successful, or an error code.    // The format of the $profile parameter is backend-specific    // and need not/may not be supported by the current backend.    public function CreateLogin($username, $password, $profile)    {        return ulLoginBackend::NOT_SUPPORTED;    }    // Deletes a login from the database.    // Returns true if successful, an error code otherwise.    public function DeleteLogin($dn)    {        return ulLoginBackend::NOT_SUPPORTED;    }    // Changes the password for an already existing login.    // Returns true if successful, an error code otherwise.    public function SetPassword($dn, $pass)    {        return ulLoginBackend::NOT_SUPPORTED;    }    // Blocks or unblocks a user.    // Set $block to a positive value to block for that many seconds.    // Set $block to zero or negative to unblock.    // Returns true on success, otherwise an error code.    public function BlockUser($dn, $block_secs)    {        return true;    }    // If the user is blocked, returns a DateTime (local timezone) object    // telling when to unblock the user. If a past block expired    // or the user is not blocked, returns a DateTime from the past.    // Can also return error codes.    protected function UserBlockExpires($dn, &$flagged)    {        return new DateTime('1000 years ago');    }}?>