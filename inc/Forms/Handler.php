<?php
/**
 * @package mam-amber-util
 */

namespace Mam\AmberUtil\Forms;

use Mam\AmberUtil\ServiceInterface;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Handler implements ServiceInterface
{

    /**
     * @var PHPMailer;
     */
    protected $mail;

    /**
     * Forms handler constructor, init PHPMailer
     *
     */
    public function __construct()
    {
        $this->mail = new PHPMailer(true);
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        if ($this->get('request') != 'form') {
            return false;
        }
        $to = $this->processTo();
        $message = $this->processMessage();
        $form = $this->processFrom();
        $subject = $this->processSubject();

        return $this->sendMessage($to, $form, $message, $subject);
    }

    /**
     * Process the To email
     * @return string The email recipients list
     */
    private function processTo()
    {
        return 'ali@moveaheadmedia.co.uk';
    }

    /**
     * Process the email message
     * @return string The email html message
     */
    private function processMessage()
    {
        $count = 1;
        ob_start(); ?>
        <h1>Email From Website | Amber Tiles</h1>
        <table style="width: 100%; max-width:768px;">
            <?php foreach ($_POST as $key => $value) {
                $count = $count + 1; ?>
                <tr <?php if (!($count % 2)){ ?>style="background-color: #f2f2f2;" <?php } ?> >
                    <td><p style="padding: 0 15px;"><b><?php echo ucfirst($key); ?></b></p></td>
                    <td><p style="padding: 0 15px;"><?php if ($value) {
                                if (is_array($value)) {
                                    echo implode("<br />", $value);
                                } else {
                                    echo $value;
                                }
                            } ?></p></td>
                </tr>
            <?php } ?>

            <tr <?php if (!($count % 2)){ ?>style="background-color: #f2f2f2;" <?php } ?> >
                <td><p style="padding: 0 15px;"><b>Logged postcode:</b></p></td>
                <td><p style="padding: 0 15px;"><?php echo $this->get('store'); ?></p></td>
            </tr>

            <tr <?php if (!($count % 2)){ ?>style="background-color: #f2f2f2;" <?php } ?> >
                <td><p style="padding: 0 15px;"><b>Page</b></p></td>
                <td><p style="padding: 0 15px;"><?php echo $this->get('page'); ?></p></td>
            </tr>
        </table>
        <?php
        $message = ob_get_clean();
        return $message;
    }

    /**
     * Process the From email
     * @return array The email and name from
     */
    private function processFrom()
    {
        // init email
        $email = $this->post('email');
        if (!$email) {
            $email = 'website@ambertiles.com.au';
        }

        // init name
        $name = $this->post('name');
        if (!$email) {
            $name = 'Amber Tiles Website';
        }

        // return the res
        return [
            'email' => $email,
            'name' => $name
        ];
    }

    /**
     * Process the email subject
     * @return string The email subject
     */
    private function processSubject()
    {
        return 'Email From Website | Amber Tiles';
    }

    /**
     * Use PHP mailer to send the email and echo json data for the status and for the error message
     *
     * @param $to string the recipients emails list separated by coma.
     * @param $from array ['email','name']
     * @param $message string html message to be sent
     * @param $subject string the subject of the email
     *
     */
    private function sendMessage($to, $from, $message, $subject)
    {
        try {
            $this->mail->setFrom('website@ambertiles.com.au', 'Amber Tiles Website');
            $emails = explode(",", $to);
            foreach ($emails as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->mail->addAddress($email);
                }
            }
            foreach ($_FILES as $file) {
                $this->mail->addAttachment($file['tmp_name'], $file['name']);
            }
            $this->mail->addReplyTo($from['email'], $from['name']);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->send();

            echo json_encode([
                'status' => 'success',
                'message' => 'The message has been sent'
            ]);
            return true;
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->errorMessage()
            ]);
            return false;
        }
    }

    /**
     * Get the variable value from $_GET if it exists
     *
     * @param $variableName string the variable name that you want to get.
     *
     * @return false|string false if the variable doesn't exist | or the value of the variable
     */
    private function get($variableName)
    {
        $value = false;
        if (isset($_GET[$variableName])) {
            $value = $_GET[$variableName];
        }
        return $value;
    }

    /**
     * Get the variable value from $_POST  if it exists
     *
     * @param $variableName string the variable name that you want to get.
     *
     * @return false|string false if the variable doesn't exist | or the value of the variable
     */
    private function post($variableName)
    {
        $value = false;
        if (isset($_POST[$variableName])) {
            $value = $_POST[$variableName];
        }
        return $value;
    }
}