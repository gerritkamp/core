<?php

/**
 * Email, manages user authentication
 *
 * @category   Core
 * @package    Core_Email
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */

/**
 * @category   Core
 * @package    Core_Email
 * @copyright  Copyright (c) 2013 Gerrit Kamp
 * @author     Gerrit Kamp<gpkamp@gmail.com>
 */
class Core_Email
{
  /**
   * @var Zend Logger
   */
  protected $_logger;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->_logger = Zend_Registry::get('logger');
    $this->_config = Zend_Registry::get('config');
  }

  /**
   * Main method to send emails. @todo: add attachment and to/cc/bcc functionality
   *
   * @param string  $type        The email type
   * @param array   $to          Array with people[name, email]
   * @param array   $params      Array with parameters that are needed to generate the email body
   * @param array   $subject     Array with the email subjects
   * @param array   $from        Array with the from-details
   * @param boolean $bulk        If false (=default), a unique email is created for each user.
   * @param array   $attachments If given, the path to files that should be attached
   *
   * @return
   */
  public function sendEmail(
    $type, $to, $params, $subject, $from=array(), $bulk=false, $attachments=null
  ){
    $this->_logger->info(__METHOD__);
    $this->_logger->debug(__METHOD__.' to: '.print_r($to, true));
    $params['host_url'] = empty($params['host_url']) ? Zend_Registry::get('host_url') : $params['host_url'];
    if (!$bulk) {
      if (isset($to['name']) && isset($to['email'])) {
        $newTo[0] = $to;  // set into single item array format
        $to = $newTo;     // remove old to
      }
      $params['to_name']  = $to[0]['name'];
      $params['to_email'] = $to[0]['email'];
    }
    if (empty($from)) {
      $from = array(
        'email' => $this->_config->email->from->email,
        'name' => $this->_config->email->from->name
      );
    }
    $this->_logger->debug(__METHOD__.' to: '.print_r($to, true));
    // filter test emails
    $to = $this->_checkTestEmail($to);
    if (empty($to)) {
      $this->_logger->notice(__METHOD__.' to: empty, all test-users??');
      return false;
    }
    switch ($this->_config->email->method) {
      case 'local':
        $n = $this->_sendZendMail($type, $to, $params, $subject, $from, $bulk, $attachments);
        break;
      case 'all-mail':
        $n = $this->_sendAllMail($type, $to, $params, $subject, $from, $bulk, $attachments);
        break;
      case 'mandrill':
        $n = $this->_sendMandrillMail($type, $to, $params, $subject, $from, $bulk, $attachments);
        break;
    }
    $this->_logger->info(__METHOD__.' '.$n.' emails sent!');
    return $n;
  }

  /**
   * Method to send email using the Zend_Mail libraray
   *
   * @param string  $type        The email type
   * @param array   $to          Array with people[name, email]
   * @param array   $params      Array with parameters that are needed to generate the email body
   * @param array   $subject     Array with the email subjects
   * @param array   $from        Array with the from-details
   * @param boolean $bulk        If false (=default), a unique email is created for each user.
   * @param array   $attachments If given, the path to files that should be attached
   *
   * @return
   */
  protected function _sendZendMail($type, $to, $params, $subject, $from, $bulk, $attachments)
  {
    $this->_logger->info(__METHOD__);
    // set items that are the same for each user. If subject has to be unique, the sendMail function
    // itself will need to be called multiple times
    $mail = new Zend_Mail();
    $mail->setSubject($subject);
    if (!empty($from['email']) && !empty($from['name'])) {
        $mail->setFrom($from['email'], $from['name']);
      } elseif (!empty($from['email'])) {
        $mail->setFrom($from['email']);
      } elseif (is_string($from)) {
        $mail->setFrom($from);
      } else {
        $this->_logger->err(__METHOD__.' could not set from: '.print_r($from, true));
        return false;
      }
    // add attachments, if any
    if ($attachments) {
      foreach ($attachments as $attachment) {
        $fileParts = pathinfo($attachment);
        $fileContents = file_get_contents($attachment);
        $at = new Zend_Mime_Part($fileContents);
        $at->type        = $this->_getMimeType($fileParts['extension']);
        $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $at->encoding    = Zend_Mime::ENCODING_BASE64;
        $at->filename    = $fileParts['filename'];
        $mail->addAttachment($at);
      }
    }
    // send all emails with the same body
    if ($bulk) {
      $mail->setBodyHtml($this->_createBody($type, $params));
      $mail->addTo($to);
      $mail->send();
    } else {
      foreach ($to as $user) {
        // create unique body per user
        $mail->setBodyHtml($this->_createBody($type, $params));
        if (!empty($user['email']) && !empty($user['name'])) {
          $mail->addTo($user['email'], $user['name']);
        } elseif (!empty($user['email'])) {
          $mail->addTo($user['email']);
        } elseif (is_string($user)) {
          $mail->addTo($user);
        } else {
          $this->_logger->err(__METHOD__.' could not send to : '.print_r($user, true));
          return false;
        }
        $mail->send();
        // clear the recipient
        $mail->clearRecipients();
      }
      return count($to);
    }
  }

  /**
   * Method to send email using the Mandrill API
   *
   * @param string  $type        The email type
   * @param array   $to          Array with people[name, email]
   * @param array   $params      Array with parameters that are needed to generate the email body
   * @param array   $subject     Array with the email subjects
   * @param array   $from        Array with the from-details
   * @param boolean $bulk        If false (=default), a unique email is created for each user.
   * @param array   $attachments If given, the path to files that should be attached
   *
   * @return
   */
  protected function _sendMandrillMail($type, $to, $params, $subject, $from, $bulk, $attachments)
  {
    $this->_logger->info(__METHOD__);
    $config = Zend_Registry::get('config');
    $this->_logger->debug(__METHOD__.' got config');
    $key = $config->mandrill->key;
    $this->_logger->debug(__METHOD__.' got key: '.$key);
    //try {
      $mandrill = new Mandrill($key);
      $this->_logger->debug(__METHOD__.' created mandrill');
      $message = array(
        'html' => $this->_createBody($type, $params),
        'subject' => $subject,
        'from_email' => 'no-reply@virtualmentor.co', // must do this because of DMARC rules of AOL
        'from_name' => $from['name'],
        'to' => $to,
        'headers' => array('Reply-To' => $from['email']),
        'important' => false,
        'track_opens' => null,
        'track_clicks' => null,
        'auto_text' => null,
        'auto_html' => null,
        'inline_css' => null,
        'url_strip_qs' => null,
        'preserve_recipients' => null,
        'view_content_link' => null,
        'tracking_domain' => null,
        'signing_domain' => null,
        'return_path_domain' => null,
        'merge' => true,
        'tags' => array($type),
      );
      $this->_logger->debug(__METHOD__.' message: '.print_r($message, true));
      if ($attachments) {
        foreach ($attachments as $attachment) {
          $fileParts = pathinfo($attachment);
          $content = file_get_contents($attachment);
          $message['attachments'][] = array(
            'type' => $this->_getMimeType($fileParts['extension']),
            'name' => $fileParts['filename'],
            'content' => base64_encode($content)
          );
        }
      }
      $async = false;
      $result = $mandrill->messages->send($message, $async);
      $this->_logger->debug(__METHOD__.print_r($result, true));
      return count($to);
    //} catch(Mandrill_Error $e) {
        // Mandrill errors are thrown as exceptions
    //    $this->_logger->err(__METHOD__.' Mandrill error: '.get_class($e).'-'.$e->getMessage());
    //}
  }

  /**
   * Method to send email using the All-Mail service
   *
   * @param  string  $type    The email type
   * @param  array   $to      Array with people[name, email]
   * @param  array   $params  Array with parameters that are needed to generate the email body
   * @param  string  $subject The email subject
   * @param  array   $from    Array with the from-details
   * @param  boolean $bulk    If false (=default), a unique email is created for each user.
   *
   * @return
   */
  protected function _sendAllMail($type, $to, $params, $subject, $from)
  {

  }

  /**
   * Method to check for test emails (those starting with test++)
   *
   * @param  array $to Array with people[name, email]
   *
   * @return array same array with test emails removed.
   */
  protected function _checkTestEmail($to)
  {
    $this->_logger->info(__METHOD__);
    foreach ($to as $index => $user) {
      $this->_logger->debug(__METHOD__.' to: '.print_r($to, true));
      if (!empty($user['email'])) {
        if (substr($user['email'], 0, 5) == 'test+') {
          $this->_logger->notice(__METHOD__.' not sending to test email: '.print_r($to[$index], true));
          unset($to[$index]);
        }
      }
    }
    if (!empty($to)) {
      return $to;
    } else {
      return array();
    }
  }

  /**
   * Create the body for an email. Uses Views.
   *
   * @param  string $type   The email template type
   * @param  array  $params The params to be inserted in the email script
   *
   * @return string The html body
   */
  protected function _createBody($type, $params)
  {
    $this->_logger->info(__METHOD__);
    $this->_logger->debug(__METHOD__.' params: '.print_r($params, true));
    // create view
    $html = new Zend_View();
    $html->setScriptPath(APPLICATION_PATH.'/views/emails/');
    // assign variables (which can be strings/arrays/objects etc)
    foreach ($params as $key => $value) {
      $html->assign($key, $value);
    }
    $template = strtolower($type.'.phtml');
    $body = $html->render('_header.phtml');
    $body.= $html->render($template);
    $body.= $html->render('_footer.phtml');
    $this->_logger->debug(__METHOD__.' body: '.print_r($body, true));
    return $body;
  }

  /**
   * Method to get the mime type of a given file
   *
   * @param string $ext The file extension
   *
   * @return string mime type
   */
  protected function _getMimeType($ext){
    $mimetypes = array(
      '' => 'application/octet-stream',
      '323' => 'text/h323',
      'acx' => 'application/internet-property-stream',
      'ai' => 'application/postscript',
      'aif' => 'audio/x-aiff',
      'aifc' => 'audio/x-aiff',
      'aiff' => 'audio/x-aiff',
      'asf' => 'video/x-ms-asf',
      'asr' => 'video/x-ms-asf',
      'asx' => 'video/x-ms-asf',
      'au' => 'audio/basic',
      'avi' => 'video/x-msvideo',
      'axs' => 'application/olescript',
      'bas' => 'text/plain',
      'bcpio' => 'application/x-bcpio',
      'bin' => 'application/octet-stream',
      'bmp' => 'image/bmp',
      'c' => 'text/plain',
      'cat' => 'application/vnd.ms-pkiseccat',
      'cdf' => 'application/x-cdf',
      'cer' => 'application/x-x509-ca-cert',
      'class' => 'application/octet-stream',
      'clp' => 'application/x-msclip',
      'cmx' => 'image/x-cmx',
      'cod' => 'image/cis-cod',
      'cpio' => 'application/x-cpio',
      'crd' => 'application/x-mscardfile',
      'crl' => 'application/pkix-crl',
      'crt' => 'application/x-x509-ca-cert',
      'csh' => 'application/x-csh',
      'css' => 'text/css',
      'dcr' => 'application/x-director',
      'der' => 'application/x-x509-ca-cert',
      'dir' => 'application/x-director',
      'dll' => 'application/x-msdownload',
      'dms' => 'application/octet-stream',
      'doc' => 'application/msword',
      'dot' => 'application/msword',
      'dvi' => 'application/x-dvi',
      'dxr' => 'application/x-director',
      'eps' => 'application/postscript',
      'etx' => 'text/x-setext',
      'evy' => 'application/envoy',
      'exe' => 'application/octet-stream',
      'fif' => 'application/fractals',
      'flr' => 'x-world/x-vrml',
      'flv' => 'video/x-flv',
      'gif' => 'image/gif',
      'gtar' => 'application/x-gtar',
      'gz' => 'application/x-gzip',
      'h' => 'text/plain',
      'hdf' => 'application/x-hdf',
      'hlp' => 'application/winhlp',
      'hqx' => 'application/mac-binhex40',
      'hta' => 'application/hta',
      'htc' => 'text/x-component',
      'htm' => 'text/html',
      'html' => 'text/html',
      'htt' => 'text/webviewhtml',
      'ico' => 'image/x-icon',
      'ief' => 'image/ief',
      'iii' => 'application/x-iphone',
      'ins' => 'application/x-internet-signup',
      'isp' => 'application/x-internet-signup',
      'jfif' => 'image/pipeg',
      'jpe' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpg' => 'image/jpeg',
      'js' => 'application/x-javascript',
      'latex' => 'application/x-latex',
      'lha' => 'application/octet-stream',
      'lsf' => 'video/x-la-asf',
      'lsx' => 'video/x-la-asf',
      'lzh' => 'application/octet-stream',
      'm13' => 'application/x-msmediaview',
      'm14' => 'application/x-msmediaview',
      'm3u' => 'audio/x-mpegurl',
      'man' => 'application/x-troff-man',
      'mdb' => 'application/x-msaccess',
      'me' => 'application/x-troff-me',
      'mht' => 'message/rfc822',
      'mhtml' => 'message/rfc822',
      'mid' => 'audio/mid',
      'mny' => 'application/x-msmoney',
      'mov' => 'video/quicktime',
      'movie' => 'video/x-sgi-movie',
      'mp2' => 'video/mpeg',
      'mp3' => 'audio/mpeg',
      'mpa' => 'video/mpeg',
      'mpe' => 'video/mpeg',
      'mpeg' => 'video/mpeg',
      'mpg' => 'video/mpeg',
      'mpp' => 'application/vnd.ms-project',
      'mpv2' => 'video/mpeg',
      'ms' => 'application/x-troff-ms',
      'mvb' => 'application/x-msmediaview',
      'nws' => 'message/rfc822',
      'oda' => 'application/oda',
      'p10' => 'application/pkcs10',
      'p12' => 'application/x-pkcs12',
      'p7b' => 'application/x-pkcs7-certificates',
      'p7c' => 'application/x-pkcs7-mime',
      'p7m' => 'application/x-pkcs7-mime',
      'p7r' => 'application/x-pkcs7-certreqresp',
      'p7s' => 'application/x-pkcs7-signature',
      'pbm' => 'image/x-portable-bitmap',
      'pdf' => 'application/pdf',
      'pfx' => 'application/x-pkcs12',
      'pgm' => 'image/x-portable-graymap',
      'pko' => 'application/ynd.ms-pkipko',
      'pma' => 'application/x-perfmon',
      'pmc' => 'application/x-perfmon',
      'pml' => 'application/x-perfmon',
      'pmr' => 'application/x-perfmon',
      'pmw' => 'application/x-perfmon',
      'png' => 'image/png',
      'pnm' => 'image/x-portable-anymap',
      'pot,' => 'application/vnd.ms-powerpoint',
      'ppm' => 'image/x-portable-pixmap',
      'pps' => 'application/vnd.ms-powerpoint',
      'ppt' => 'application/vnd.ms-powerpoint',
      'prf' => 'application/pics-rules',
      'ps' => 'application/postscript',
      'pub' => 'application/x-mspublisher',
      'qt' => 'video/quicktime',
      'ra' => 'audio/x-pn-realaudio',
      'ram' => 'audio/x-pn-realaudio',
      'ras' => 'image/x-cmu-raster',
      'rgb' => 'image/x-rgb',
      'rmi' => 'audio/mid',
      'roff' => 'application/x-troff',
      'rtf' => 'application/rtf',
      'rtx' => 'text/richtext',
      'scd' => 'application/x-msschedule',
      'sct' => 'text/scriptlet',
      'setpay' => 'application/set-payment-initiation',
      'setreg' => 'application/set-registration-initiation',
      'sh' => 'application/x-sh',
      'shar' => 'application/x-shar',
      'sit' => 'application/x-stuffit',
      'snd' => 'audio/basic',
      'spc' => 'application/x-pkcs7-certificates',
      'spl' => 'application/futuresplash',
      'src' => 'application/x-wais-source',
      'sst' => 'application/vnd.ms-pkicertstore',
      'stl' => 'application/vnd.ms-pkistl',
      'stm' => 'text/html',
      'svg' => 'image/svg+xml',
      'sv4cpio' => 'application/x-sv4cpio',
      'sv4crc' => 'application/x-sv4crc',
      'swf' => 'application/x-shockwave-flash',
      't' => 'application/x-troff',
      'tar' => 'application/x-tar',
      'tcl' => 'application/x-tcl',
      'tex' => 'application/x-tex',
      'texi' => 'application/x-texinfo',
      'texinfo' => 'application/x-texinfo',
      'tgz' => 'application/x-compressed',
      'tif' => 'image/tiff',
      'tiff' => 'image/tiff',
      'tr' => 'application/x-troff',
      'trm' => 'application/x-msterminal',
      'tsv' => 'text/tab-separated-values',
      'txt' => 'text/plain',
      'uls' => 'text/iuls',
      'ustar' => 'application/x-ustar',
      'vcf' => 'text/x-vcard',
      'vrml' => 'x-world/x-vrml',
      'wav' => 'audio/x-wav',
      'wcm' => 'application/vnd.ms-works',
      'wdb' => 'application/vnd.ms-works',
      'wks' => 'application/vnd.ms-works',
      'wmf' => 'application/x-msmetafile',
      'wps' => 'application/vnd.ms-works',
      'wri' => 'application/x-mswrite',
      'wrl' => 'x-world/x-vrml',
      'wrz' => 'x-world/x-vrml',
      'xaf' => 'x-world/x-vrml',
      'xbm' => 'image/x-xbitmap',
      'xla' => 'application/vnd.ms-excel',
      'xlc' => 'application/vnd.ms-excel',
      'xlm' => 'application/vnd.ms-excel',
      'xls' => 'application/vnd.ms-excel',
      'xlt' => 'application/vnd.ms-excel',
      'xlw' => 'application/vnd.ms-excel',
      'xof' => 'x-world/x-vrml',
      'xpm' => 'image/x-xpixmap',
      'xwd' => 'image/x-xwindowdump',
      'z' => 'application/x-compress',
      'zip' => 'application/zip',
    );
    if(array_key_exists($ext, $mimetypes)){
      return $mimetypes[$ext];
    } else {
      return 'application/octet-stream';
    }
  }

}