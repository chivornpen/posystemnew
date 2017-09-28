<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Channel;
use App\Customer;
use App\Product;
use App\Province;
use App\Purchaseorder;
use App\Purchaseordersd;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class reportController extends Controller
{
    public function index(){
        $customer="";
        if(Auth::user()->position->name=="SD" ){//&& Auth::user()->position->name=="SD"
            $customer = Customer::where('brand_id',Auth::user()->brand_id)->get();
        }elseif(Auth::user()->position->name!="SD"){
            $customer = Customer::all();
        }
        $brand = Brand::all();
        return view('admin.report.index',compact('customer','brand'));
    }
    public function show($data,$brand){//Live search in customer list
            if(Auth::user()->position->name!="SD"){//
                if($data){
                    $customer="";
                    $province_id=0;
                    $province_id = Province::where('name','like','%'.trim($data).'%')->value('id');
                    if($province_id){
                        $province_id = Province::where('name','like','%'.trim($data).'%')->value('id');
                    }else{
                        $province_id=0;
                    }
                    if($brand!=0){
                        $customer =DB::select("SELECT * FROM customers WHERE  brand_id = {$brand} and contactNo LIKE '%".$data."%'");
                        //// AND province_id ={$province_id} OR name LIKE '%".$data."%' OR contactNo LIKE '%".$data."%' OR location LIKE'%".$data."%'");
                    }else{
                        $customer =DB::select("SELECT * FROM customers WHERE province_id ={$province_id} OR name LIKE '%".$data."%' OR contactNo LIKE '%".$data."%' OR location LIKE'%".$data."%'");
                    }
                    return view('admin.report.search',compact('customer'));
                    ////            OR name LIKE '%".$data."%'OR contactNo LIKE '%".$data."%' OR location LIKE'%".$data."%'
                }else{
                    $customer = "";
                    if($brand!=0){
                        $customer = Customer::where('brand_id',$brand)->get();
                    }else{
                        $customer = Customer::all();
                        //                $customer = Customer::whereNull('brand_id')->get();
                    }
                    return view('admin.report.search',compact('customer'));
                }
            }elseif(Auth::user()->position->name="SD"){
                $customer="";
                $channel_id = Channel::where('description','like','%'.trim($data).'%')->value('id');
                if($data==0){
                    $customer = Customer::where('brand_id',Auth::user()->brand_id)->get();
                }
                if($channel_id){
                    $customer = Customer::where('brand_id',Auth::user()->brand_id)->where('channel_id',$channel_id)->get();
                }else{
                    $customer = Customer::where('brand_id',Auth::user()->brand_id)->where('contactNo','like','%'.$data.'%')->get();
                }
                return view('admin.report.search',compact('customer'));
            }


    }

    public function SaleReport(){//sale report
        $purchaseorder="";
        $product="";
        $users="";
        if(Auth::user()->position->name!="SD"){//Auth::user()->position->name!="SD"
            $users = DB::table('positions')->join('users','users.position_id','=','positions.id')->select('users.nameDisplay','users.id')->where('positions.name','sale')->orwhere('positions.name','sd')->get();
            $purchaseorder = Purchaseorder::where('isDelivery','=',1)->get();
//            $brand =Brand::all();
            $product = Product::all();
        }else{//if(Auth::user()->position->name=="SD")
            $user_id=array();
            $users = DB::table('positions')->join('users','users.position_id','=','positions.id')->select('users.nameDisplay','users.id')->where('users.brand_id',Auth::user()->brand_id)->get();
            $user=User::where('brand_id',Auth::user()->brand_id)->get();
            foreach ($user as $u){
                $user_id[]= $u->id;
            }
            $purchaseorder = Purchaseordersd::whereIn('user_id',$user_id)->get();
//            $product = DB::table('brand_product')->where('brand_id',Auth::user()->brand_id)->get();
            $brand= Brand::find(Auth::user()->brand_id);
            $product = $brand->products;
        }
        $brands =Brand::all();
        return view('admin.report.saleReport',compact('product','purchaseorder','users','brands'));
    }
    public function SaleReportSearch($saleName, $startDate, $endDate, $brand){
        $start=$startDate;
        $end=$endDate;
        if(strtolower(Auth::user()->position->name)!="SD" && $brand==0) {//Auth::user()->position->name!="SD"
            if ($start == 0 && $end == 0 && $saleName == 0) {
                $product = Product::all();
                $purchaseorder = Purchaseorder::where('isDelivery', '=', 1)->get();
                return view('admin.report.SaleReportSearch', compact('product', 'purchaseorder','brand'));
            }
            //dd($start.$end.$saleName);
            if ($startDate && $endDate != 0) {
                $start = Carbon::parse($startDate)->format('Y-m-d');
                $end = Carbon::parse($endDate)->format('Y-m-d');
            }

            if ($saleName == 0) {//Search between
                $product = Product::all();
                $purchaseorder = Purchaseorder::whereBetween('poDate', [$start, $end])->get();
                return view('admin.report.SaleReportSearch', compact('product', 'purchaseorder','brand'));
            } elseif ($start == 0 || $end == 0) {//search by user ID
                $product = Product::all();
                $purchaseorder = Purchaseorder::where('user_id', $saleName)->get();
                return view('admin.report.SaleReportSearch', compact('product', 'purchaseorder','brand'));
            } elseif ($start != 0 && $end != 0 && $saleName != 0) {
                $product = Product::all();
                $purchaseorder = Purchaseorder::where('user_id', $saleName)->whereBetween('poDate', [$start, $end])->get();
                return view('admin.report.SaleReportSearch', compact('product', 'purchaseorder','brand'));
            }

        }elseif(Auth::user()->position->name=="SD" || $brand!=0){//if(Auth::user()->position->name=="SD")
//            $brands= Brand::find(Auth::user()->brand_id);
            $byBrand ="";
            if(Auth::user()->brand_id){
                $byBrand = Auth::user()->brand_id;
            }else{
                $byBrand = $brand;
            }
            $brands= Brand::find($byBrand);
            if($start==0 && $end==0 && $saleName==0){
                $user_id=array();
                $user=User::where('brand_id',$byBrand)->get();
                foreach ($user as $u){
                    $user_id[]= $u->id;
                }
                $purchaseorder = Purchaseordersd::whereIn('user_id',$user_id)->get();
//                $product = DB::table('brand_product')->where('brand_id',$byBrand)->get();
                $product = $brands->products;
                return view('admin.report.SaleReportSearch',compact('product','purchaseorder','brand'));
            }
            if($startDate && $endDate !=0){
                $start= Carbon::parse($startDate)->format('Y-m-d');
                $end= Carbon::parse($endDate)->format('Y-m-d');
            }

            if($saleName==0){//Search between
                $product = $brands->products;
                $purchaseorder = Purchaseordersd::whereIn('poDate',[$start,$end])->get();
                return view('admin.report.SaleReportSearch',compact('product','purchaseorder','brand'));
            }elseif($start==0 || $end==0){//search by user ID
                $product = $brands->products;
                $purchaseorder = Purchaseordersd::where('user_id',$saleName)->get();
                return view('admin.report.SaleReportSearch',compact('product','purchaseorder','brand'));
            }elseif($start!=0 && $end!=0 && $saleName!=0){
                $product = $brands->products;
                $purchaseorder = Purchaseordersd::where('user_id',$saleName)->whereBetween('poDate',[$start,$end])->get();
                return view('admin.report.SaleReportSearch',compact('product','purchaseorder','brand'));
            }

        }
    }

    public function paymentReport(){
        $customer = Customer::all();
//      DB::table('positions')->join('users','users.position_id','=','positions.id')->select('users.nameDisplay','users.id')->where('positions.name','sale')->orwhere('positions.name','sd')->get();
        $product = Product::all();
        $purchaseorder = Purchaseorder::where([['isDelivery',1],['paid','!=',0],['cradit',0]])->get();
        return view('admin.report.paymentReport',compact('product','purchaseorder','customer'));
    }

    public function paymentReportSearch($custName, $startDate, $endDate){

        $start=$startDate;
        $end=$endDate;

        if($start==0 && $end==0 && $custName==0){
            $product = Product::all();
            $purchaseorder = Purchaseorder::where([['isDelivery',1],['paid','!=',0],['cradit',0]])->get();
            return view('admin.report.paymentSearch',compact('product','purchaseorder'));
        }
        //dd($start.$end.$custName);
        if($startDate && $endDate !=0){
            $start= Carbon::parse($startDate)->format('Y-m-d');
            $end= Carbon::parse($endDate)->format('Y-m-d');
        }
        if($custName==0){//Search between
            $product = Product::all();
            $purchaseorder = Purchaseorder::where([['isDelivery',1],['paid','!=',0],['cradit',0]])->whereBetween('paidDate',[$start,$end])->get();
            return view('admin.report.paymentSearch',compact('product','purchaseorder'));
        }elseif($start==0 || $end==0){//search by user ID
            $cutomerContact = Customer::where('id',$custName)->value('contactNo');
            $user_id = User::where('contactNum',$cutomerContact)->value('id');
            $product = Product::all();
            if($user_id!=""){
                $purchaseorder = Purchaseorder::where('user_id',$user_id)->where([['isDelivery',1],['paid','!=',0],['cradit',0]])->get();
                return view('admin.report.paymentSearch',compact('product','purchaseorder'));
            }else{
                $purchaseorder = Purchaseorder::where('customer_id',$custName)->where([['isDelivery',1],['paid','!=',0],['cradit',0]])->get();
                return view('admin.report.paymentSearch',compact('product','purchaseorder'));
            }
        }elseif($start!=0 && $end!=0 && $custName!=0){
            $cutomerContact = Customer::where('id',$custName)->value('contactNo');
            $user_id = User::where('contactNum',$cutomerContact)->value('id');
            $product = Product::all();
            if($user_id!=""){
                $purchaseorder = Purchaseorder::where('user_id',$user_id)->where([['isDelivery',1],['paid','!=',0],['cradit',0]])->whereBetween('paidDate',[$start,$end])->get();
                return view('admin.report.paymentSearch',compact('product','purchaseorder'));
            }else{
                $purchaseorder = Purchaseorder::where('customer_id',$custName)->where([['isDelivery',1],['paid','!=',0],['cradit',0]])->whereBetween('paidDate',[$start,$end])->get();
                return view('admin.report.paymentSearch',compact('product','purchaseorder'));
            }

        }
    }

    public function customerCredit(){

        $customer = Customer::all();
//            DB::table('positions')->join('users','users.position_id','=','positions.id')->select('users.nameDisplay','users.id')->where('positions.name','sale')->orwhere('positions.name','sd')->get();
        $product = Product::all();
        $purchaseorder = Purchaseorder::where([['isDelivery',1],['cradit','!=',0]])->get();
        return view('admin.report.customerCredit',compact('product','purchaseorder','customer'));
    }

    public function customerCreditSearch($cusName, $startDate, $endDate){
//        customerCreditSearch

        $start=$startDate;
        $end=$endDate;
        if($start==0 && $end==0 && $cusName==0){
            $product = Product::all();
            $purchaseorder = Purchaseorder::where([['isDelivery',1],['cradit','!=',0]])->get();
            return view('admin.report.customerCreditSearch',compact('product','purchaseorder'));
        }

        if($startDate && $endDate !=0){
            $start= Carbon::parse($startDate)->format('Y-m-d');
            $end= Carbon::parse($endDate)->format('Y-m-d');
        }

        if($cusName==0){//Search between
            $product = Product::all();
            $purchaseorder = Purchaseorder::where([['isDelivery',1],['cradit','!=',0]])->whereBetween('dueDate',[$start,$end])->get();
            return view('admin.report.customerCreditSearch',compact('product','purchaseorder'));
        }elseif($start==0 || $end==0){//search by user ID
            $cutomerContact = Customer::where('id',$cusName)->value('contactNo');
            $user_id = User::where('contactNum',$cutomerContact)->value('id');
            $product = Product::all();
            if($user_id!=""){
                $purchaseorder = Purchaseorder::where('user_id',$user_id)->where([['isDelivery',1],['cradit','!=',0]])->get();
                return view('admin.report.customerCreditSearch',compact('product','purchaseorder'));
            }else{
                $purchaseorder = Purchaseorder::where('customer_id',$cusName)->where([['isDelivery',1],['cradit','!=',0]])->get();
                return view('admin.report.customerCreditSearch',compact('product','purchaseorder'));
            }

        }elseif($start!=0 && $end!=0 && $cusName!=0){
            $cutomerContact = Customer::where('id',$cusName)->value('contactNo');
            $user_id = User::where('contactNum',$cutomerContact)->value('id');
            $product = Product::all();
            if($user_id!=""){
                $purchaseorder = Purchaseorder::where('user_id',$user_id)->where([['isDelivery',1],['cradit','!=',0]])->whereBetween('dueDate',[$start,$end])->get();
                return view('admin.report.customerCreditSearch',compact('product','purchaseorder'));
            }else{
                $purchaseorder = Purchaseorder::where('customer_id',$cusName)->where([['isDelivery',1],['cradit','!=',0]])->whereBetween('dueDate',[$start,$end])->get();
                return view('admin.report.customerCreditSearch',compact('product','purchaseorder'));
            }

        }
    }




    /////////////////////////////////////////////////////////
    /// SD Reports
    public  function sdcustomerList(){
      $customer="";
      $protect = 0;
      if(Auth::user()->brand_id && Auth::user()->position->name =="SD"){
          $customer = Customer::where('brand_id',Auth::user()->brand_id)->get();
      }else{
          return view('admin.report.sdCustomerList',compact('customer','protect'));
      }
      return view('admin.report.sdCustomerList',compact('customer','protect'));
    }

    public function sdCustomerSearch($data){
        $customer="";
        $channel_id = Channel::where('description','like','%'.trim($data).'%')->value('id');
        if($data==0){
            $customer = Customer::where('brand_id',Auth::user()->brand_id)->get();
        }
         if($channel_id){
             $customer = Customer::where('brand_id',Auth::user()->brand_id)->where('channel_id',$channel_id)->get();
         }else{
             $customer = Customer::where('brand_id',Auth::user()->brand_id)->where('contactNo','like','%'.$data.'%')->get();
         }
        return view('admin.report.sdCustomerSearch',compact('customer'));
    }

    public function sdSaleReport(){
        $brand_id = Auth::user()->brand_id;
        echo $brand_id;
        $users = DB::table('positions')->join('users','users.position_id','=','positions.id')->select('users.nameDisplay','users.id')->where('positions.name','sale')->orwhere('positions.name','sd')->get();
        $product = Product::all();
        $purchaseorder = Purchaseorder::where('isDelivery','=',1)->get();
        return view('admin.report.sdSaleReport',compact('users','product','purchaseorder'));
    }
    public function sdPaymentReport(){
        return view('admin.report.sdPayment');
    }

}
