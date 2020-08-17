<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FedEx\ShipService;
use FedEx\ShipService\ComplexType;
use FedEx\ShipService\SimpleType;

require_once 'credentials.php';

class FedexShipmentController extends Controller
{
    public function generateShipment(Request $request)
    {

      
        $shipper_address = $request->input('shipper_address');
        $recipient_address = $request->input('recipient_address');
        $products_detail =  $request->input('products_detail');
        $shipperContactDetail =  $request->input('shipperContact');
        $recipientContactDetail =  $request->input('recipientContact');
        $ServiceType = $request->input('ServiceType');
        $totalPackages = count($products_detail);

        
        $userCredential = new ComplexType\WebAuthenticationCredential();
        $userCredential
            ->setKey(FEDEX_KEY)
            ->setPassword(FEDEX_PASSWORD);

        $webAuthenticationDetail = new ComplexType\WebAuthenticationDetail();
        $webAuthenticationDetail->setUserCredential($userCredential);

        $clientDetail = new ComplexType\ClientDetail();
        $clientDetail
            ->setAccountNumber(FEDEX_ACCOUNT_NUMBER)
            ->setMeterNumber(FEDEX_METER_NUMBER);

        $version = new ComplexType\VersionId();
        $version
            ->setMajor(23)
            ->setIntermediate(0)
            ->setMinor(0)
            ->setServiceId('ship');

        $shipperAddress = new ComplexType\Address();
        $shipperAddress
            ->setStreetLines([$shipper_address['street']])
            ->setCity($shipper_address['city'])
            ->setStateOrProvinceCode($shipper_address['state_code'])
            ->setPostalCode($shipper_address['postal_code'])
            ->setCountryCode($shipper_address['country_code']);

        $shipperContact = new ComplexType\Contact();
        $shipperContact
            ->setCompanyName($shipperContactDetail['company_name'])
            ->setEMailAddress($shipperContactDetail['company_email'])
            ->setPersonName($shipperContactDetail['person_name'])
            ->setPhoneNumber(($shipperContactDetail['person_mobile_no']));

        $shipper = new ComplexType\Party();
        $shipper
            ->setAccountNumber(FEDEX_ACCOUNT_NUMBER)
            ->setAddress($shipperAddress)
            ->setContact($shipperContact);

        $recipientAddress = new ComplexType\Address();
        $recipientAddress
            ->setStreetLines([$recipient_address['street']])
            ->setCity($recipient_address['city'])
            ->setStateOrProvinceCode($recipient_address['state_code'])
            ->setPostalCode($recipient_address['postal_code'])
            ->setCountryCode($recipient_address['country_code']);

        $recipientContact = new ComplexType\Contact();
        $recipientContact
            ->setPersonName($recipientContactDetail['person_name'])
            ->setPhoneNumber($recipientContactDetail['contact_name']);

        $recipient = new ComplexType\Party();
        $recipient
            ->setAddress($recipientAddress)
            ->setContact($recipientContact);

        $labelSpecification = new ComplexType\LabelSpecification();
        $labelSpecification
            ->setLabelStockType(new SimpleType\LabelStockType(SimpleType\LabelStockType::_PAPER_7X4POINT75))
            ->setImageType(new SimpleType\ShippingDocumentImageType(SimpleType\ShippingDocumentImageType::_PDF))
            ->setLabelFormatType(new SimpleType\LabelFormatType(SimpleType\LabelFormatType::_COMMON2D));


        $requestedPackageLineItems = [];
        if($totalPackages){
            if(!empty($products_detail)){
                foreach($products_detail as $key => $val){
                    $count = $key + 1;
                    
                    $packageLineItem = $count.'packageLineItem';
                    $packageLineItem = new ComplexType\RequestedPackageLineItem();
                    $packageLineItem->setSequenceNumber($count)
                                        ->setItemDescription($val['description'])
                                        ->setDimensions(new ComplexType\Dimensions(array(
                                            'Width' => $val['width'],
                                            'Height' => $val['height'],
                                            'Length' => $val['length'],
                                            'Units' => SimpleType\LinearUnits::_IN
                                        )))
                                        ->setWeight(new ComplexType\Weight(array(
                                            'Value' => $val['weight'],
                                            'Units' => SimpleType\WeightUnits::_LB
                                        )));
                    $requestedPackageLineItems[] = $packageLineItem;
                }
            }



           
        }
       
        // $packageLineItem1 = new ComplexType\RequestedPackageLineItem();
        // $packageLineItem1
        //     ->setSequenceNumber(1)
        //     ->setItemDescription('Product description')
        //     ->setDimensions(new ComplexType\Dimensions(array(
        //         'Width' => 10,
        //         'Height' => 10,
        //         'Length' => 25,
        //         'Units' => SimpleType\LinearUnits::_IN
        //     )))
        //     ->setWeight(new ComplexType\Weight(array(
        //         'Value' => 2,
        //         'Units' => SimpleType\WeightUnits::_LB
        //     )));

        $shippingChargesPayor = new ComplexType\Payor();
        $shippingChargesPayor->setResponsibleParty($shipper);

        $shippingChargesPayment = new ComplexType\Payment();
        $shippingChargesPayment
            ->setPaymentType(SimpleType\PaymentType::_SENDER)
            ->setPayor($shippingChargesPayor);


            
        $requestedShipment = new ComplexType\RequestedShipment();
        $requestedShipment->setShipTimestamp(date('c'));
        $requestedShipment->setDropoffType(new SimpleType\DropoffType(SimpleType\DropoffType::_REGULAR_PICKUP));
        $requestedShipment->setServiceType(new SimpleType\ServiceType($ServiceType));
        $requestedShipment->setPackagingType(new SimpleType\PackagingType(SimpleType\PackagingType::_YOUR_PACKAGING));
        $requestedShipment->setShipper($shipper);
        $requestedShipment->setRecipient($recipient);
        $requestedShipment->setLabelSpecification($labelSpecification);
        $requestedShipment->setRateRequestTypes(array(new SimpleType\RateRequestType(SimpleType\RateRequestType::_PREFERRED)));
        //$requestedShipment->setPackageCount(1);
        $requestedShipment->setPackageCount($totalPackages);
        // $requestedShipment->setRequestedPackageLineItems([
        //     $packageLineItem1
        // ]);

        $requestedShipment->setRequestedPackageLineItems($requestedPackageLineItems);
        $requestedShipment->setShippingChargesPayment($shippingChargesPayment);

        $processShipmentRequest = new ComplexType\ProcessShipmentRequest();
        $processShipmentRequest->setWebAuthenticationDetail($webAuthenticationDetail);
        $processShipmentRequest->setClientDetail($clientDetail);
        $processShipmentRequest->setVersion($version);
        $processShipmentRequest->setRequestedShipment($requestedShipment);

        $shipService = new ShipService\Request();
        //$shipService->getSoapClient()->__setLocation('https://ws.fedex.com:443/web-services/ship');
        $result = $shipService->getProcessShipmentReply($processShipmentRequest);

       
        $error = '';
        $labelUrl = '';
        $trackingId = '';
        if($result->Notifications[0]->Code == 3017 || $result->Notifications[0]->Code == 3021){
            $error = $result->Notifications[0]->Message;
           
        }else{

            $fileName = rand(10,1000).'_'.time().'_label.pdf';
            $labelName = public_path().'/label/'.$fileName;
           
            file_put_contents($labelName, $result->CompletedShipmentDetail->CompletedPackageDetails[0]->Label->Parts[0]->Image);
            
            $labelUrl = 'http://ec2-3-87-57-22.compute-1.amazonaws.com/Fedex/public/label/'.$fileName;
            $trackingId = $result->CompletedShipmentDetail->MasterTrackingId->TrackingNumber;
        }
        // var_dump($result);
        // Save .pdf label
       // echo $result->CompletedShipmentDetail->CompletedPackageDetails[0]->Label->Parts[0]->Image;
        
       return response()->json(['data'=>['label'=>$labelUrl,'TrackingNumber'=>$trackingId],'error'=>$error]);
    }
}
