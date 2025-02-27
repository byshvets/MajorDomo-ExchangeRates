<?php
/**
* ExchangeRates 
* @package project
* @author Alex Sokolov <admin@gelezako.com>
* @copyright Alex Sokolov http://blog.gelezako.com (c)
* @version 0.1 (wizard, 10:02:10 [Feb 06, 2017])
*/
//
//
class ExchangeRates extends module {
/**
* ExchangeRates
*
* Module class constructor
*
* @access private
*/
public function ExchangeRates() {
  $this->name="ExchangeRates";
  $this->title="Курс валют";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
public function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
public function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
public function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/

/*
protected function SetAutoUpdate()
   {
       injectObjectMethodCode('ClockChime.onNewHour','ExchangeRates','
       include_once(DIR_MODULES . "ExchangeRates/ExchangeRates.class.php");
       $app_exRate = new ExchangeRates();
       $app_exRate->SaveAutoUpdate();
'); 
   }
   
*/

public function SaveAutoUpdate(){
	//Начало парсинга Приватбанк АПИ
	libxml_use_internal_errors(true);
	$url = 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=11'; 
	$xml = @simplexml_load_file($url);
    if (!$xml) {
			 $out["notification"]="<#LANG_ER_APP_NOTIF#>";
			 /*
			 sg("exchange_rate.eurobuy","");
			 sg("exchange_rate.eurosale","");
			 sg("exchange_rate.usdbuy","");
			 sg("exchange_rate.usdsale","");
			 sg("exchange_rate.rurbuy","");
			 sg("exchange_rate.rursale","");
			 */
			 Debmes("Не удалось обновить курс валют");
     }
	 else if (false === $xml) {
		     $out["notification"]="<#LANG_ER_APP_NOTIF#>";
			 /*
			 sg("exchange_rate.eurobuy","");
			 sg("exchange_rate.eurosale","");
			 sg("exchange_rate.usdbuy","");
			 sg("exchange_rate.usdsale","");
			 sg("exchange_rate.rurbuy","");
			 sg("exchange_rate.rursale","");
			 */
			 Debmes("Не удалось обновить курс валют");
	}
	else if($xml) {
        $i=0;
        //получаем курс евро
        foreach($xml->row[1]->exchangerate->attributes() as $key => $exchangerate){
		  $updated = 0;
          if($i==2){
		    $cureurobuy = gg("exchange_rate.eurobuy");
			if($cureurobuy!=round((float)$exchangerate,2)){
				sg("exchange_rate.eurobuy",round((float)$exchangerate,2));
				$out["eurobuy"]=round((float)$exchangerate,2);  
				DebMes("ExchangeRate: currrent eurobuy=".$cureurobuy.".New value=".round((float)$exchangerate,2));
				//sg("exchange_rate.date1",date("Y-m-d H:i:s"));
				//$out["date1"]=date("Y-m-d H:i:s");
			} else {
				$out["eurobuy"]=$cureurobuy;
			}
			$updated=1;			
          }
          else if($i==3){
			$cureurosale = gg("exchange_rate.eurosale");
			if($cureurosale!=round((float)$exchangerate,2)){
				sg("exchange_rate.eurosale",round((float)$exchangerate,2));
				$out["eurosale"]=round((float)$exchangerate,2);
				DebMes("ExchangeRate: currrent eurosale=".$cureurosale.".New value=".round((float)$exchangerate,2));
				//sg("exchange_rate.date1",date("Y-m-d H:i:s"));
				//$out["date1"]=date("Y-m-d H:i:s");
			} else {
				$out["eurosale"]=$cureurosale;
			}
			$updated=1;			
          }
          ++$i;
        }
		
		//получаем курс доллара
		$j=0;
        foreach($xml->row[0]->exchangerate->attributes() as $key => $exchangerate){
          if($j==2){
			$curusdbuy = gg("exchange_rate.usdbuy");
			if($curusdbuy!=round((float)$exchangerate,2)){
				sg("exchange_rate.usdbuy",round((float)$exchangerate,2));
				$out["usdbuy"]=round((float)$exchangerate,2);
				$updated=1;
		  	} else {
				$out["usdbuy"]=$curusdbuy;
			}
          }
          else if($j==3){
			$curusdsale = gg("exchange_rate.usdsale");
			if($curusdsale!=round((float)$exchangerate,2)){
			  sg("exchange_rate.usdsale",round((float)$exchangerate,2));
			  $out["usdsale"]=round((float)$exchangerate,2);
			  $updated=1;
			} else {
			  $out["usdsale"]=$curusdsale;
			}
          }
          ++$j;
        }
		
		//получаем курс рубля
		$k=0;
        foreach($xml->row[2]->exchangerate->attributes() as $key => $exchangerate){
          if($k==2){
			$currurbuy = gg("exchange_rate.rurbuy");
			if($currurbuy!=round((float)$exchangerate,2)){		  
				sg("exchange_rate.rurbuy",round((float)$exchangerate,2));
				$out["rurbuy"]=round((float)$exchangerate,2);
				$updated=1;
			} else {
				$out["rurbuy"]=$currurbuy;
			}
          }
          else if($k==3){
			$currursale = gg("exchange_rate.rursale");
			if($currursale!=round((float)$exchangerate,2)){			
				sg("exchange_rate.rursale",round((float)$exchangerate,2));
				$out["rursale"]=round((float)$exchangerate,2);
				$updated=1;
			} else {
				$out["rursale"]=$currursale;
			}
          }
          ++$k;
        }
		
		if ($updated!=0) {
		  $out["date1"]=date("Y-m-d H:i:s");
		  sg("exchange_rate.date1",date("Y-m-d H:i:s"));
		  $updated=0;
		}
		
     }
	 //Конец парсинга Приватбанк АПИ
	 
	//Начало парсинга ЦБР
	$file = simplexml_load_file("http://www.cbr.ru/scripts/XML_daily.asp?date_req=".date("d/m/Y"));
		if (false === $file) {
		Debmes("Не удалось обновить курс валют Приватбанка");
		$out["notification2"]="<#LANG_ER_APP_NOTIF2#>";
		sg("exchange_rate.dollarrur","");
		sg("exchange_rate.eurorur","");
	}
	else if($file) {
				$xml = $file->xpath("//Valute[@ID='R01235']");
				$valute = strval($xml[0]->Value);
				$dollar = str_replace(",",".",$valute);
				$curdollarrur = gg("exchange_rate.dollarrur");
				if($curdollarrur!=round((float)$dollar,2)){
					sg("exchange_rate.dollarrur",round((float)$dollar,2));
					$out["dollarrur"]=round((float)$dollar,2);
				} else {
					$out["dollarrur"]=$curdollarrur;
				}

				$xml = $file->xpath("//Valute[@ID='R01239']");
				$valute = strval($xml[0]->Value);
				$euro = str_replace(",",".",$valute);
				$cureurorur = gg("exchange_rate.eurorur");
				if($cureurorur!=round((float)$euro,2)){	
					sg("exchange_rate.eurorur",round((float)$euro,2));
					$out["eurorur"]=round((float)$euro,2);
				} else {
					$out["eurorur"]=$cureurorur;
				}
				
				$out["date2"]=date("Y-m-d H:i:s");
				sg("exchange_rate.date2",date("Y-m-d H:i:s"));
			}
    //Конец парсинга ЦБР
			
	// Начало парсинга курсов от Минфин
	/*
    ini_set("user_agent","MajorDomo-ExchangeRates/0.1");
	$file_nbu = file_get_contents('http://api.minfin.com.ua/nbu/434f685ddcfc82024569b9516a87838053f383a0/',true);
	$file_nbu = json_decode($file_nbu);
		  if ($file_nbu){ 
				$d=$file_nbu->usd->ask;
				sg("exchange_rate.usdnbu",round((float)$d,2));
				$out["usdnbu"]=round((float)$d,2);
				
				sg("exchange_rate.euronbu",round((float)$file_nbu->eur->ask,2));
				$out["euronbu"]=round((float)$file_nbu->eur->ask,2);
				
				sg("exchange_rate.rurnbu",round((float)$file_nbu->rub->ask,2));
				$out["rurnbu"]=round((float)$file_nbu->rub->ask,2);
				
				sg("exchange_rate.date3",date("Y-m-d H:i:s"));
				$out["date3"]=date("Y-m-d H:i:s");
		  }
		  else{Debmes("Не удалось обновить курс валют");}
		  */
	//Конец парсинга курсов от Минфин	  
		  
		  
	// Начало парсинга курсов Нац Банка Казахстана
	$url = "http://www.nationalbank.kz/rss/rates_all.xml";
	$dataObj = simplexml_load_file($url);
	    if (false === $dataObj) {
			$out["notification4"]="<#LANG_ER_APP_NOTIF4#>";
			sg("exchange_rate.kztusd","");
			sg("exchange_rate.kzteur","");
			Debmes("Не удалось обновить курс валют");
        }
		else{
			foreach ($dataObj->channel->item as $item) {
				if ($item->title =='USD') {
					 //sg('exchange_rate.date4',$item->pubDate);
					 $curkztusd = gg("exchange_rate.kztusd");
					 if($curkztusd!=$item->description){	
						sg('exchange_rate.kztusd',(float)$item->description);
						$out["kztusd"]=$item->description;
					 } else {
						$out["kztusd"]=$curkztusd;
					 }
				} 
				if ($item->title =='EUR') {
					 //sg('exchange_rate.date4',$item->pubDate);
					 $curkzteur = gg("exchange_rate.kzteur");
					 if($curkzteur!=$item->description){
						sg('exchange_rate.kzteur',(float)$item->description);
						$out["kzteur"]=$item->description;
					 } else {
						$out["kzteur"]=$curkzteur;
					 }
				} 
				$out["date4"]=date("Y-m-d H:i:s");
				sg("exchange_rate.date4",date("Y-m-d H:i:s"));
			}
		}
	// Конец парсинга курсов Нац Банка Казахстана
	
	
	
	// Начало парсинга курсов Нац Банка Республики Беларусь
	$fileBY = simplexml_load_file("http://www.nbrb.by/Services/XmlExRates.aspx");
		if (false === $fileBY) {
		Debmes("Не удалось обновить курс валют Нац Банка Республики Беларусь");
		$out["notification5"]="<#LANG_ER_APP_NOTIF5#>";
		sg("exchange_rate.dollarbyn","");
		sg("exchange_rate.eurobyn","");
	}
	else if($fileBY) {
				$xml = $fileBY->xpath("//Currency[@Id='431']");
				$valute = strval($xml[0]->Rate);
				$curdollarbyn = gg("exchange_rate.dollarbyn");
				if($curdollarbyn!=$valute){
					sg("exchange_rate.dollarbyn",$valute);
					$out["dollarbyn"]=$valute;
				} else {
					$out["dollarbyn"]=$curdollarbyn;
				}

				$xml = $fileBY->xpath("//Currency[@Id='451']");
				$valute = strval($xml[0]->Rate);
				$cureurobyn = gg("exchange_rate.eurobyn");
				if($cureurobyn!=$valute){
					sg("exchange_rate.eurobyn",$valute);
					$out["eurobyn"]= $valute;
				} else {
					$out["eurobyn"]=$cureurobyn;
				}
				
				sg("exchange_rate.date5",date("Y-m-d H:i:s"));
				$out["date5"] = date("Y-m-d H:i:s");
			}
	// Конец парсинга курсов Нац Банка Республики Беларусь
	

	
}

public function admin(&$out) {
    libxml_use_internal_errors(true);
	$url = 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=11'; 
	$xml = @simplexml_load_file($url);
    if (!$xml) {
			 $out["notification"]="<#LANG_ER_APP_NOTIF#>";
			 /*
			 sg("exchange_rate.eurobuy","");
			 sg("exchange_rate.eurosale","");
			 sg("exchange_rate.usdbuy","");
			 sg("exchange_rate.usdsale","");
			 sg("exchange_rate.rurbuy","");
			 sg("exchange_rate.rursale","");
			 */
			 Debmes("Не удалось обновить курс валют Приватбанка");
     }
	 else if (false === $xml) {
		     $out["notification"]="<#LANG_ER_APP_NOTIF#>";
			 /*
			 sg("exchange_rate.eurobuy","");
			 sg("exchange_rate.eurosale","");
			 sg("exchange_rate.usdbuy","");
			 sg("exchange_rate.usdsale","");
			 sg("exchange_rate.rurbuy","");
			 sg("exchange_rate.rursale","");
			 */
			 Debmes("Не удалось обновить курс валют Приватбанка");
	}
     else{
        global $eurohr;
        if(isset($eurohr)){ 
        $i=0;
        //получаем курс евро
        foreach($xml->row[1]->exchangerate->attributes() as $key => $exchangerate){
          $updated = 0;
          if($i==2){
		    $cureurobuy = gg("exchange_rate.eurobuy");
			if($cureurobuy!=round((float)$exchangerate,2)){
				sg("exchange_rate.eurobuy",round((float)$exchangerate,2));
				$out["eurobuy"]=round((float)$exchangerate,2);  
				DebMes("ExchangeRate: currrent eurobuy=".$cureurobuy.".New value=".round((float)$exchangerate,2));
				//sg("exchange_rate.date1",date("Y-m-d H:i:s"));
				//$out["date1"]=date("Y-m-d H:i:s");
			} else {
				$out["eurobuy"]=$cureurobuy;
			}
			$updated=1;			
          }
          else if($i==3){
			$cureurosale = gg("exchange_rate.eurosale");
			if($cureurosale!=round((float)$exchangerate,2)){
				sg("exchange_rate.eurosale",round((float)$exchangerate,2));
				$out["eurosale"]=round((float)$exchangerate,2);
				DebMes("ExchangeRate: currrent eurosale=".$cureurosale.".New value=".round((float)$exchangerate,2));
				//sg("exchange_rate.date1",date("Y-m-d H:i:s"));
				//$out["date1"]=date("Y-m-d H:i:s");
			} else {
				$out["eurosale"]=$cureurosale;
			}
			$updated=1;			
          }
          ++$i;
        }}


        global $usdhr;
        if(isset($usdhr)){ 
        //получаем курс доллара
        $j=0;
        foreach($xml->row[0]->exchangerate->attributes() as $key => $exchangerate){
          if($j==2){
			$curusdbuy = gg("exchange_rate.usdbuy");
			if($curusdbuy!=round((float)$exchangerate,2)){
				sg("exchange_rate.usdbuy",round((float)$exchangerate,2));
				$out["usdbuy"]=round((float)$exchangerate,2);
				$updated=1;
		  	} else {
				$out["usdbuy"]=$curusdbuy;
			}
          }
          else if($j==3){
			$curusdsale = gg("exchange_rate.usdsale");
			if($curusdsale!=round((float)$exchangerate,2)){
			  sg("exchange_rate.usdsale",round((float)$exchangerate,2));
			  $out["usdsale"]=round((float)$exchangerate,2);
			  $updated=1;
			} else {
			  $out["usdsale"]=$curusdsale;
			}
          }
          ++$j;
        }}

        global $rurhr;
        if(isset($rurhr)){
        //получаем курс рубля
        $k=0;
        foreach($xml->row[2]->exchangerate->attributes() as $key => $exchangerate){
          if($k==2){
			$currurbuy = gg("exchange_rate.rurbuy");
			if($currurbuy!=round((float)$exchangerate,2)){		  
				sg("exchange_rate.rurbuy",round((float)$exchangerate,2));
				$out["rurbuy"]=round((float)$exchangerate,2);
				$updated=1;
			} else {
				$out["rurbuy"]=$currurbuy;
			}
          }
          else if($k==3){
			$currursale = gg("exchange_rate.rursale");
			if($currursale!=round((float)$exchangerate,2)){			
				sg("exchange_rate.rursale",round((float)$exchangerate,2));
				$out["rursale"]=round((float)$exchangerate,2);
				$updated=1;
			} else {
				$out["rursale"]=$currursale;
			}
          }
          ++$k;
        }}

		if ($updated!=0) {
		  $out["date1"]=date("Y-m-d H:i:s");
		  sg("exchange_rate.date1",date("Y-m-d H:i:s"));
		  $updated=0;
		}
    } //Конец парсинга хмл от ПриватБанка


// Начало парсинга ЦБР
  global $dollarrur,$eurorur;
  $file = simplexml_load_file("http://www.cbr.ru/scripts/XML_daily.asp?date_req=".date("d/m/Y"));
    if (!$file) {
        $out["notification2"]="<#LANG_ER_APP_NOTIF2#>";
		sg("exchange_rate.dollarrur","");
		sg("exchange_rate.eurorur","");
		Debmes("Не удалось обновить курс валют");
        }
    else if (false === $file) {
        $out["notification2"]="<#LANG_ER_APP_NOTIF2#>";
		sg("exchange_rate.dollarrur","");
		sg("exchange_rate.eurorur","");
		Debmes("Не удалось обновить курс валют банка России");
        }		
     else{ 
        if(isset($dollarrur)){
            $xml = $file->xpath("//Valute[@ID='R01235']");
            $valute = strval($xml[0]->Value);
            $dollar = str_replace(",",".",$valute);
			$curdollarrur = gg("exchange_rate.dollarrur");
			if($curdollarrur!=round((float)$dollar,2)){	
				sg("exchange_rate.dollarrur",round((float)$dollar,2));
				$out["dollarrur"]=round((float)$dollar,2);
			} else {
				$out["dollarrur"]=$curdollarrur;
			}
			$updated=1;
        }
        if(isset($eurorur)){
            $xml = $file->xpath("//Valute[@ID='R01239']");
            $valute = strval($xml[0]->Value);
            $euro = str_replace(",",".",$valute);
			$cureurorur = gg("exchange_rate.eurorur");
			if($cureurorur!=round((float)$euro,2)){	
				sg("exchange_rate.eurorur",round((float)$euro,2));
				$out["eurorur"]=round((float)$euro,2);
			} else {
				$out["eurorur"]=$cureurorur;
			}
			$updated=1;			
        }
		
		if ($updated!=0) {
		  $out["date2"]=date("Y-m-d H:i:s");
		  sg("exchange_rate.date2",date("Y-m-d H:i:s"));
		  $updated=0;
		}
    }
//Конец парсинга ЦБР
	
    libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	
// Начало парсинга курсов от Минфин
/*
	global $euronbu,$usdnbu,$rurnbu;
    ini_set("user_agent","MajorDomo-ExchangeRates/0.1");
	$file_nbu = file_get_contents('http://api.minfin.com.ua/nbu/434f685ddcfc82024569b9516a87838053f383a0/',true);
	$file_nbu = json_decode($file_nbu);
		  if (!$file_nbu) {
			$out["notification3"]="<#LANG_ER_APP_NOTIF3#>";
			sg("exchange_rate.rurnbu","");
			sg("exchange_rate.usdnbu","");
			sg("exchange_rate.euronbu","");
			}
		 else{ 
			if(isset($usdnbu)){
				$d=$file_nbu->usd->ask;
				sg("exchange_rate.usdnbu",round((float)$d,2));
				$out["usdnbu"]=round((float)$d,2);
				
				sg("exchange_rate.date3",date("Y-m-d H:i:s"));
				$out["date3"]=date("Y-m-d H:i:s");
			}
			if(isset($euronbu)){	
				sg("exchange_rate.euronbu",round((float)$file_nbu->eur->ask,2));
				$out["euronbu"]=round((float)$file_nbu->eur->ask,2);
								
				sg("exchange_rate.date3",date("Y-m-d H:i:s"));
				$out["date3"]=date("Y-m-d H:i:s");
			}
			if(isset($rurnbu)){
				sg("exchange_rate.rurnbu",round((float)$file_nbu->rub->ask,2));
				$out["rurnbu"]=round((float)$file_nbu->rub->ask,2);
								
				sg("exchange_rate.date3",date("Y-m-d H:i:s"));
				$out["date3"]=date("Y-m-d H:i:s");
			}
		}
*/
//Конец парсинга курсов от Минфин
	
// Начало парсинга курсов Нац Банка Казахстана
	global $kztusd,$kzteur;
	$url_kz = "http://www.nationalbank.kz/rss/rates_all.xml";
	$dataObj = simplexml_load_file($url_kz);
		if (!$dataObj  or false === $dataObj) {
			$out["notification4"]="<#LANG_ER_APP_NOTIF4#>";
			sg("exchange_rate.kztusd","");
			sg("exchange_rate.kzteur","");
			}
		else{ 
			foreach ($dataObj->channel->item as $item) {
			
				if (($item->title =='USD')&&(isset($kztusd))) {
					 //sg('exchange_rate.date4',$item->pubDate);
					 $curkztusd = gg("exchange_rate.kztusd");
					 if($curkztusd!=$item->description){
						//DebMes("ExchangeRate: currrent kztusd=".$curkztusd.".New value=".$item->description);
						sg('exchange_rate.kztusd',(float)$item->description);
						$out["kztusd"]=$item->description;
					 } else {
						$out["kztusd"]=$curkztusd;
					 }
				} 
				if (($item->title =='EUR')&&(isset($kzteur))) {
					 //sg('exchange_rate.date4',$item->pubDate);
					 $curkzteur = gg("exchange_rate.kzteur");
					 if($curkzteur!=$item->description){
						//DebMes("ExchangeRate: currrent kzteur=".$curkzteur.".New value=".$item->description);
						sg('exchange_rate.kzteur',(float)$item->description);
						$out["kzteur"]=$item->description;
					 } else {
						$out["kzteur"]=$curkzteur;
					 }
				}
				if ((isset($kztusd))or(isset($kzteur))){
					$out["date4"]=date("Y-m-d H:i:s");
					sg("exchange_rate.date4",date("Y-m-d H:i:s"));
				}
			}
		}		
// Конец парсинга курсов Нац Банка Казахстана
	
	
// Начало парсинга курсов Нац Банка Республики Беларусь
	global $dollarbyn, $eurobyn;
	$fileBY = simplexml_load_file("http://www.nbrb.by/Services/XmlExRates.aspx");
		if (false === $fileBY) {
		Debmes("Не удалось обновить курс валют Нац Банка Республики Беларусь");
		$out["notification5"]="<#LANG_ER_APP_NOTIF5#>";
		sg("exchange_rate.dollarbyn","");
		sg("exchange_rate.eurobyn","");
	}
	else if($fileBY) {
				$xml = $fileBY->xpath("//Currency[@Id='431']");
				$valute = strval($xml[0]->Rate);
				if (isset($dollarbyn)) {
					$curdollarbyn = gg("exchange_rate.dollarbyn");
					if($curdollarbyn!=$valute){
						sg("exchange_rate.dollarbyn",$valute);
						$out["dollarbyn"]=$valute;
					} else {
						$out["dollarbyn"]=$curdollarbyn;
					}
					$updated=1;
				}	
				
				$xml = $fileBY->xpath("//Currency[@Id='451']");
				$valute = strval($xml[0]->Rate);
				if (isset($eurobyn)) {
					$cureurobyn = gg("exchange_rate.eurobyn");
					if($cureurobyn!=$valute){
						sg("exchange_rate.eurobyn",$valute);
						$out["eurobyn"]= $valute;
					} else {
						$out["eurobyn"]=$cureurobyn;
					}
					$updated=1;
				}	
					
				if ($updated!=0) {
					sg("exchange_rate.date5",date("Y-m-d H:i:s"));
					$out["date5"] = date("Y-m-d H:i:s");
				}
			}
// Конец парсинга курсов Нац Банка Республики Беларусь
	
}




/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
public function usual(&$out) {
 $this->admin($out);
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
public function install($data='') {
 $className = 'ExchangeRates'; //имя класса
 $objectName = array('exchange_rate');//имя обьектов
 //$objDescription = array('Курс валют');
 $rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe($className) . "'");
 
    if (!$rec['ID']) {
        $rec = array();
        $rec['TITLE'] = $className;
        $rec['DESCRIPTION'] = "Курс Валют";
        $rec['ID'] = SQLInsert('classes', $rec);
    }
    for ($i = 0; $i < count($objectName); $i++) {
        $obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objectName[$i]) . "'");
        if (!$obj_rec['ID']) {
            $obj_rec = array();
            $obj_rec['CLASS_ID'] = $rec['ID'];
            $obj_rec['TITLE'] = $objectName[$i];
            //$obj_rec['DESCRIPTION'] = $objDescription[$i];
            $obj_rec['ID'] = SQLInsert('objects', $obj_rec);
        }
    }
  
  //$this->SetAutoUpdate();
  subscribeToEvent($this->name, 'HOURLY'); //событие HOURLY которое совершается раз в час
  parent::install();
 }

 function processSubscription($event_name, $details='') {
        if ($event_name=='HOURLY') {
			$this->SaveAutoUpdate();
			//say("Выполняю обновление курсов валют");
        }
    }
 
public function uninstall()
   {
      SQLExec("delete from pvalues where property_id in (select id FROM properties where object_id in (select id from objects where class_id = (select id from classes where title = 'ExchangeRates')))");
      SQLExec("delete from properties where object_id in (select id from objects where class_id = (select id from classes where title = 'ExchangeRates'))");
      SQLExec("delete from objects where class_id = (select id from classes where title = 'ExchangeRates')");
      SQLExec("delete from classes where title = 'ExchangeRates'");
      
	  unsubscribeFromEvent($this->name, 'HOURLY');
      parent::uninstall();
   }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDA2LCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
