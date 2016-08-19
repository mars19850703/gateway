<?php

class MY_Email extends CI_Email
{
    protected $ci;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->ci = & get_instance();
    }

    public function sendMail($from, $to, $subject, $content, $mailType = 'html')
    {
        $config['mailtype'] = 'html';
        $this->ci->email->initialize($config);


        $this->ci->email->from($from['email'], (isset($from['name']))? $from['name'] : '');
        $this->ci->email->to($to['email']);

        $this->ci->email->subject($subject);
        $this->ci->email->message($content);

        $this->ci->email->send();
    }


}
