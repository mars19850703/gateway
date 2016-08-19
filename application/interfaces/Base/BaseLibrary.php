<?php

class BaseLibrary
{
    protected $data;
    protected $ci;

    public function __construct()
    {
        $this->data = array();
        $this->ci   = &get_instance();
    }
}
