<?php

namespace App\Http\Controllers;

use App\Models\forecast as ModelsForecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ForcastController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response=array();
        //$ip = '103.239.147.187'; //For static IP address get
        $request = new Request();
        $ip = $request->ip(); //Dynamic IP address get
        $data = (array) \Location::get($ip); 
        $response =(isset($data["cityName"]))? $this->getWeather($data["cityName"]) : array("success"=>false,"message"=>"Fail to get weather"); 
        return view('welcome', ['response' => $response]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {          
      $userCity = $request->input('city');
      if(isset($userCity)){
        $response =$this->getWeather($userCity);
      }else
      {
          return $this->index();
      }
       
      return view('welcome', ['response' => $response]);
    }

    

    private function getWeather($userCity)
    {
        
      $city=str_replace(" ","+",$userCity);
      $response = Http::get("http://api.openweathermap.org/data/2.5/forecast?q=".$city."&appid=5bd724b7bba97fc163cbe54df6fdcd86"); 
      $currentDate = Carbon::parse(Carbon::now())->format('l');;
      
       if($response->successful())
       {
            $days=array();
            $icons=array("clouds"=>"cloudy.png","rain"=>"rainy.png","sunny"=>"sunny.png","clear"=>"sunny.png");
            $color=array("clouds"=>"#638593","rain"=>"#56565c","sunny"=>"#eabc46","clear"=>"#eabc46");
            $weatherItems = json_decode($response->body());
            foreach($weatherItems->list as $weatherItem)
            {
                    $date = current(explode(" ",$weatherItem->dt_txt));
                    $itemDay= Carbon::parse($date)->format('l');                    
                    if (!isset($days[$itemDay])) {
                        $day= ($currentDate==$itemDay)?"Today":$itemDay;
                        $image ="/images/xhdpi/".$icons[strtolower($weatherItem->weather[0]->main)];
                             $days[$day]= array(
                                "min"=> round($weatherItem->main->temp_min/10,0),
                                "max"=> round( $weatherItem->main->temp_max/10,0),
                                "current"=> round($weatherItem->main->temp/10,0),
                                "image"=> $image,
                                "icon" => $weatherItem->weather[0]->icon,                               
                                "color"=> $color[strtolower($weatherItem->weather[0]->main)],
                                "i"=>$weatherItem->weather[0]->main
                                );  
                                
                                $image ="";
                    }
            }
            $this->save($days);
            $json= array("success"=>true,"days"=>$days);
       }else
       {
        $json= array("success"=>false,"message"=>"Error message");
       }

        return $json;
    }

    private function save($data)
    {
        $newForcast = new ModelsForecast();
        $newForcast->data = json_encode($data);
        $newForcast->save();
        return true;
    }

}
