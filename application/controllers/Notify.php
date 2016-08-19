<?php

class Notify extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function refund($supplier)
    {
        if (!is_null($supplier) && !empty($supplier)) {
            // get supplier
            $this->load->model('supplier_model');
            $supplierInfo = $this->supplier_model->getSupplierByCode($supplier);

            if (!is_null($supplierInfo)) {
                $module = '1.0/' . $supplier . '/payment/notify';
                $this->load->library($module);

                $this->notify->refund();
            }
        }
    }

    public function reserve($gatewayVersion = null, $supplier = null, $product = null, $action = null)
    {
        if (!is_null($gatewayVersion) && !empty($gatewayVersion)) {
            // get supplier
            $this->load->model('supplier_model');
            $supplierInfo = $this->supplier_model->getSupplierByCode($supplier);
            // get product
            $this->load->model('product_model');
            $productInfo = $this->product_model->getProductBySupplierIdxAndCode($supplierInfo['idx'], $product);
            // get action
            $this->load->model('action_model');
            $actionInfo = $this->action_model->getActionBySupplierIdxAndProductIdxAndCode($supplierInfo['idx'], $productInfo['idx'], $action);

            if (!is_null($supplierInfo) && !is_null($productInfo) && !is_null($actionInfo)) {
                $module = $gatewayVersion . '/' . $supplier . '/' . $product . '/' . $action;
                $this->load->library($module, null, 'reserve');

                $this->reserve->confirm();
            }
        }
    }
}
