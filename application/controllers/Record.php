<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Record extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function redirect()
    {
        $this->fields = array(
            'r'
        );

        $this->data["postData"] = $this->input->get($this->fields, true);

        // MY_Controller::dumpData($this->data['postData']);

        if (!is_null($this->data['postData']['r']) || !empty($this->data['postData']['r'])) {
            header('Location:' . $this->data['postData']['r']);
            exit;
        }
    }
}
