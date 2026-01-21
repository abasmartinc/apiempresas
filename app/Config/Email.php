<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{

    /**
     * @var string
     */
    public $fromEmail = '';
    public $fromName  = '';
    public $recipients;
    public $userAgent = 'CodeIgniter';
    public $protocol  = 'smtp';
    public $mailPath  = '/usr/sbin/sendmail';
    public $SMTPHost  = '';
    public $SMTPUser  = '';
    public $SMTPPass  = '';
    public $SMTPPort  = 587;
    public $SMTPTimeout = 5;
    public $SMTPKeepAlive = false;
    public $SMTPCrypto = 'tls';

    public function __construct()
    {
        parent::__construct();

        $this->fromEmail  = env('email.fromEmail', 'soporte@apiempresas.es');
        $this->fromName   = env('email.fromName', 'APIEmpresas.es');
        $this->protocol   = env('email.protocol', 'smtp');
        $this->SMTPHost   = env('email.SMTPHost', 'localhost');
        $this->SMTPUser   = env('email.SMTPUser', '');
        $this->SMTPPass   = env('email.SMTPPass', '');
        $this->SMTPPort   = (int) env('email.SMTPPort', 465);
        $this->SMTPCrypto = env('email.SMTPCrypto', 'ssl');
    }

    /**
     * Enable word-wrap
     *
     * @var boolean
     */
    public $wordWrap = true;

    /**
     * Character count to wrap at
     *
     * @var integer
     */
    public $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     *
     * @var string
     */
    public $mailType = 'html';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     *
     * @var string
     */
    public $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     *
     * @var boolean
     */
    public $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     *
     * @var integer
     */
    public $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     *
     * @var string
     */
    public $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     *
     * @var string
     */
    public $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     *
     * @var boolean
     */
    public $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     *
     * @var integer
     */
    public $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     *
     * @var boolean
     */
    public $DSN = false;

}
