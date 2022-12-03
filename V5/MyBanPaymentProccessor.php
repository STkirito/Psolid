<?php

namespace V5;

class MyBankPaymentProcessor implements PaymentProcessor 
{
    protected $myBankApi;


    public function __construct(MyBankApi $myBankApi){

        $this->myBankApi = $myBankApi;
    }

    public function pay(){
        $this->myBankapi->changerCustomer();
    }
}