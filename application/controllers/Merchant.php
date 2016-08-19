<?php

class Merchant extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function notify($supplier = null)
    {
        if (!is_null($supplier) && !empty($supplier)) {

            if ($supplier === 'pay2go') {
                $supplier = 'spgateway';
            }

        	// get supplier
        	$this->load->model('supplier_model');
        	$supplierInfo = $this->supplier_model->getSupplierByCode($supplier);

        	if (!is_null($supplierInfo)) {
	        	$module = '1.0/' . $supplier . '/merchant/notify';
	        	$this->load->library($module);

	        	$this->notify->verify();
        	}
        }
    }
}
