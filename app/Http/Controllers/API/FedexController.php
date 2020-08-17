<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use FedEx\RateService\Request as FedExRequest;
use FedEx\RateService\ComplexType;
use FedEx\RateService\SimpleType;
use Illuminate\Http\Request;
require_once 'credentials.php';

class FedexController extends Controller
{
    
    public function getShippingrate(Request $request,FedExRequest $fedexrequest)
    {

        $sameCOuntry = ($request->input('is_domestic_shipping'))?$request->input('is_domestic_shipping'):true;
        $shipper_address = $request->input('shipper_address');
        $recipient_address = $request->input('recipient_address');
        $products_detail =  $request->input('products_detail');
        $totalPackages = count($products_detail);
        
       // return response()->json(['shippig'=>$shipper_address,'recipe'=>$recipient_address]);die;
        


        $rateRequest = new ComplexType\RateRequest();

        
        //authentication & client details
        $rateRequest->WebAuthenticationDetail->UserCredential->Key = FEDEX_KEY;
        $rateRequest->WebAuthenticationDetail->UserCredential->Password = FEDEX_PASSWORD;
        $rateRequest->ClientDetail->AccountNumber = FEDEX_ACCOUNT_NUMBER;
        $rateRequest->ClientDetail->MeterNumber = FEDEX_METER_NUMBER;

        $rateRequest->TransactionDetail->CustomerTransactionId = 'testing rate service request';

        //version
        $rateRequest->Version->ServiceId = 'crs';
        $rateRequest->Version->Major = 24;
        $rateRequest->Version->Minor = 0;
        $rateRequest->Version->Intermediate = 0;

        $rateRequest->ReturnTransitAndCommit = true;

        //shipper
        $rateRequest->RequestedShipment->PreferredCurrency = 'USD';
        $rateRequest->RequestedShipment->Shipper->Address->StreetLines = [$shipper_address['street']]; //10 Fed Ex Pkwy
        $rateRequest->RequestedShipment->Shipper->Address->City = $shipper_address['city']; //Memphis
        $rateRequest->RequestedShipment->Shipper->Address->StateOrProvinceCode = $shipper_address['state_code']; //TN
        $rateRequest->RequestedShipment->Shipper->Address->PostalCode = $shipper_address['postal_code']; //38115
        $rateRequest->RequestedShipment->Shipper->Address->CountryCode = $shipper_address['country_code']; //US

        //recipient
        $rateRequest->RequestedShipment->Recipient->Address->StreetLines = [$recipient_address['street']];
        $rateRequest->RequestedShipment->Recipient->Address->City = $recipient_address['city'];
        $rateRequest->RequestedShipment->Recipient->Address->StateOrProvinceCode = $recipient_address['state_code'];
        $rateRequest->RequestedShipment->Recipient->Address->PostalCode = $recipient_address['postal_code'];
        $rateRequest->RequestedShipment->Recipient->Address->CountryCode = $recipient_address['country_code'];

        //shipping charges payment
        $rateRequest->RequestedShipment->ShippingChargesPayment->PaymentType = SimpleType\PaymentType::_SENDER;

        //rate request types
        $rateRequest->RequestedShipment->RateRequestTypes = [SimpleType\RateRequestType::_PREFERRED, SimpleType\RateRequestType::_LIST];

        $rateRequest->RequestedShipment->PackageCount = $totalPackages; // 2 for two packages

        //create package line items
        //$rateRequest->RequestedShipment->RequestedPackageLineItems = [new ComplexType\RequestedPackageLineItem(), new ComplexType\RequestedPackageLineItem()];
        
        $requestedPackageLineItems = [];
        if($totalPackages){
            
            for($i=1; $i<=$totalPackages; $i++){
                $requestedPackageLineItems[] = new ComplexType\RequestedPackageLineItem();
            }
        }
        $rateRequest->RequestedShipment->RequestedPackageLineItems = $requestedPackageLineItems;




        if(!empty($products_detail)){
            foreach($products_detail as $key => $val){
                $rateRequest->RequestedShipment->RequestedPackageLineItems[$key]->Weight->Value = $val['weight'];
                $rateRequest->RequestedShipment->RequestedPackageLineItems[$key]->Weight->Units = SimpleType\WeightUnits::_LB;
                $rateRequest->RequestedShipment->RequestedPackageLineItems[$key]->Dimensions->Length = $val['length'];
                $rateRequest->RequestedShipment->RequestedPackageLineItems[$key]->Dimensions->Width = $val['width'];
                $rateRequest->RequestedShipment->RequestedPackageLineItems[$key]->Dimensions->Height = $val['height'];
                $rateRequest->RequestedShipment->RequestedPackageLineItems[$key]->Dimensions->Units = SimpleType\LinearUnits::_IN;
                $rateRequest->RequestedShipment->RequestedPackageLineItems[$key]->GroupPackageCount = $val['quantity'];
            }
        }
        //package 1
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Weight->Value = 2;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Weight->Units = SimpleType\WeightUnits::_LB;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Length = 10;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Width = 10;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Height = 3;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Units = SimpleType\LinearUnits::_IN;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->GroupPackageCount = 1;

        //package 2
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[1]->Weight->Value = 5;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[1]->Weight->Units = SimpleType\WeightUnits::_LB;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[1]->Dimensions->Length = 20;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[1]->Dimensions->Width = 20;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[1]->Dimensions->Height = 10;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[1]->Dimensions->Units = SimpleType\LinearUnits::_IN;
        // $rateRequest->RequestedShipment->RequestedPackageLineItems[1]->GroupPackageCount = 1;

        $rateServiceRequest = new FedExRequest();
        //$rateServiceRequest->getSoapClient()->__setLocation(Request::PRODUCTION_URL); //use production URL

        $rateReply = $rateServiceRequest->getGetRatesReply($rateRequest); // send true as the 2nd argument to return the SoapClient's stdClass response.

   

        // if (!empty($rateReply->RateReplyDetails)) {
        //     foreach ($rateReply->RateReplyDetails as $rateReplyDetail) {
        //         var_dump($rateReplyDetail->ServiceType);
                
        //         if (!empty($rateReplyDetail->RatedShipmentDetails)) {
        //             foreach ($rateReplyDetail->RatedShipmentDetails as $ratedShipmentDetail) {
        //                 var_dump($ratedShipmentDetail->ShipmentRateDetail->RateType . ": " . $ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount);
        //             }
        //         }
        //         echo "<hr />";
        //     }
        // }
        // die;

        if(!empty($rateReply->Notifications)){
            $isError = false;
            $errMessage = '';
            foreach($rateReply->Notifications as $notification){
              
                if((int)$notification->Code === 556){
                    $errMessage .= $notification->Message;
                    $isError = true;
                    
                }
            }
           
            if($isError === true){

                return response()->json(['data'=>[],'error'=>$errMessage]);
                die('test');
            }

        }
   

        
        $FEDEX_GROUND_DELIVERY_RATE = [];


        

        if (!empty($rateReply->RateReplyDetails)) {
            foreach ($rateReply->RateReplyDetails as $rateReplyDetail) {
                //var_dump($rateReplyDetail->ServiceType);


                if($sameCOuntry){
                    //FEDEX_GROUND
                   
                    //if($rateReplyDetail->ServiceType === 'FEDEX_EXPRESS_SAVER'){
                      
                        if (!empty($rateReplyDetail->RatedShipmentDetails)) {
                            
                            foreach ($rateReplyDetail->RatedShipmentDetails as $ratedShipmentDetail) {
                               
                                if($ratedShipmentDetail->ShipmentRateDetail->RateType === 'PAYOR_ACCOUNT_PACKAGE'){
                                        $FEDEX_GROUND_DELIVERY_RATE[] = ['service_type'=>$rateReplyDetail->ServiceType, 'rate_type'=> $ratedShipmentDetail->ShipmentRateDetail->RateType,'rate'=>$ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount];
                                }
                                // var_dump($ratedShipmentDetail->ShipmentRateDetail->RateType . ": " . $ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount);
                            }
                        }
                   // }
                }else{
                    //if($rateReplyDetail->ServiceType === 'INTERNATIONAL_ECONOMY'){
                        if (!empty($rateReplyDetail->RatedShipmentDetails)) {
                            
                            foreach ($rateReplyDetail->RatedShipmentDetails as $ratedShipmentDetail) {
                               
                               
                                if($ratedShipmentDetail->ShipmentRateDetail->RateType === 'PAYOR_ACCOUNT_SHIPMENT'){
                                    $FEDEX_GROUND_DELIVERY_RATE[] = ['service_type'=>$rateReplyDetail->ServiceType, 'rate_type'=> $ratedShipmentDetail->ShipmentRateDetail->RateType,'rate'=>$ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount];
                                }
                               
                               // var_dump($ratedShipmentDetail->ShipmentRateDetail->RateType . ": " . $ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount);
                            }
                        }
                   // }
                }
                
               
               // echo "<hr />";
            }
        }
       // echo $FEDEX_GROUND_DELIVERY_RATE;
    //    echo '<pre>';
    //    print_r($rateReply);
    //    die;
        return response()->json(['data'=>$FEDEX_GROUND_DELIVERY_RATE,'error'=>'']);
        
    }

    
}
