<?php

abstract class BaseModule extends BaseLibrary
{
    // config 檔
    protected $edcConfig;
    // 連線 log idx
	protected $connectLogIdx;
    // edc
    protected $edc;
    // merchant
    protected $merchant;
    // terminal
    protected $terminal;
	// service supplier
    protected $supplier;
	// service product
    protected $product;
	// service action
    protected $action;
	// service option
    protected $option;

    public function __construct()
    {
        parent::__construct();
    }

    abstract public function init();

    public function setEdc($edc)
    {
        $this->edc = $edc;
    }

    public function setEdcConfig($config)
    {
        $this->edcConfig = $config;
    }

    public function setConnectLogIdx($connectLogIdx)
    {
    	$this->connectLogIdx = $connectLogIdx;
    }

    public function setMerchant(array $merchant)
    {
    	$this->merchant = $merchant;
    }

    public function setTerminal(array $terminal)
    {
    	$this->terminal = $terminal;
    }

    public function setSupplier(array $supplier)
    {
        $this->supplier = $supplier;
    }

    public function setProduct(array $product)
    {
        $this->product = $product;
    }

    public function setAction(array $action)
    {
        $this->action = $action;
    }

    public function setOption(array $option)
    {
        $this->option = $option;
    }
}
