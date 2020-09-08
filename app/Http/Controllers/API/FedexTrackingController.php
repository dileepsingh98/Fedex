<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FedEx\TrackService\Request as FedExRequest;
use FedEx\TrackService\ComplexType;
use FedEx\TrackService\SimpleType;
require_once 'credentials.php';
class FedexTrackingController extends Controller
{
    public function getTrackingDetail(Request $request,FedExRequest $fedexrequest,$trackingId1){
    
        $trackRequest = new ComplexType\TrackRequest();
        $trackRequest->WebAuthenticationDetail->UserCredential->Key = FEDEX_KEY;
        $trackRequest->WebAuthenticationDetail->UserCredential->Password = FEDEX_PASSWORD;


        // Client Detail
        $trackRequest->ClientDetail->AccountNumber = FEDEX_ACCOUNT_NUMBER;
        $trackRequest->ClientDetail->MeterNumber = FEDEX_METER_NUMBER;


        // Version
        $trackRequest->Version->ServiceId = 'trck';
        $trackRequest->Version->Major = 16;
        $trackRequest->Version->Intermediate = 0;
        $trackRequest->Version->Minor = 0;

        $trackRequest->SelectionDetails = [new ComplexType\TrackSelectionDetail()];

    // For get all events
        $trackRequest->ProcessingOptions = [SimpleType\TrackRequestProcessingOptionType::_INCLUDE_DETAILED_SCANS];

        // Track shipment 1
        $trackRequest->SelectionDetails[0]->PackageIdentifier->Value = $trackingId1;
        $trackRequest->SelectionDetails[0]->PackageIdentifier->Type = SimpleType\TrackIdentifierType::_TRACKING_NUMBER_OR_DOORTAG;

        // Track shipment 2
        // $trackRequest->SelectionDetails[1]->PackageIdentifier->Value = $trackingId2;
        // $trackRequest->SelectionDetails[1]->PackageIdentifier->Type = SimpleType\TrackIdentifierType::_TRACKING_NUMBER_OR_DOORTAG;

        $request = new Request();
        $result = $fedexrequest->getTrackReply($trackRequest);
        $error = '';
        if($result->Notifications[0]->Code != 0000){
            $error = $result->Notifications[0]->Message;
            // $error = 'FedEx Service Api encounter some error';

        }



       // return response()->json(['data'=>$shippingDetailList,'error'=>$error]);
        echo '<pre>';
        print_r($result);die;

        var_dump($result);
    }
}
