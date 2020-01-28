<?php
/* 09-07-2019 : Berat Kara - https://www.linkedin.com/in/beratkara/ */
include("anticaptcha.php");
include("imagetotext.php");
set_time_limit(0);
error_reporting(E_ALL);
ignore_user_abort(true);
$cerez=str_replace('\\','/',dirname(__FILE__)).'/cerez/cerez.txt';

function VeriOku2($Url,$data = NULL,$proxy = NULL){
			global $cerez;
			
			/*if(!empty($data))
				print_r($data);*/
			
			$Curl = curl_init ();
			curl_setopt($Curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
			curl_setopt($Curl, CURLOPT_URL, $Url);
			curl_setopt($Curl, CURLOPT_REFERER, 'https://internet.btk.gov.tr/sitesorgu/');
			curl_setopt($Curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($Curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($Curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($Curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($Curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);

			$request_headers = array(
			  'Connection: keep-alive',
			  'Upgrade-Insecure-Requests: 1',
			  'User-Agent: Googlebot/2.1 (+http://www.googlebot.com/bot.html)',
			  'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			  'Accept-Encoding: compressed',
			  'Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3',
			  'Content-Type: application/x-www-form-urlencoded',
			);
			
			curl_setopt($Curl, CURLOPT_HTTPHEADER, $request_headers);
			
			if(!empty($data))
			{
				curl_setopt($Curl, CURLOPT_POST ,1);
				curl_setopt($Curl, CURLOPT_POSTFIELDS, http_build_query($data));
			}
			
			curl_setopt($Curl, CURLOPT_ENCODING,  'gzip,deflate');
			if(!empty($proxy))
				curl_setopt($Curl, CURLOPT_PROXY, $proxy);
			
			//curl_setopt($Curl, CURLOPT_CONNECTTIMEOUT, 5);
			//curl_setopt($Curl, CURLOPT_TIMEOUT, 5);
			//curl_setopt($Curl, CURLOPT_POSTREDIR, 3);
			curl_setopt($Curl,CURLOPT_COOKIEFILE,$cerez);
			curl_setopt($Curl,CURLOPT_COOKIEJAR,$cerez);
	
			$VeriOkux = curl_exec ($Curl);
			curl_close($Curl);
			
			
			return str_replace(array("\n","\t","\r"), null, $VeriOkux);
}

function VeriOkufile_download($Url,$dosya_adi){
			global $cerez;
			$Curl = curl_init ();
			curl_setopt($Curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
			curl_setopt($Curl, CURLOPT_URL, $Url);
			curl_setopt($Curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($Curl, CURLOPT_BINARYTRANSFER,1);
			curl_setopt($Curl, CURLOPT_REFERER, 'https://internet.btk.gov.tr/sitesorgu/');
			curl_setopt($Curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($Curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($Curl, CURLOPT_FOLLOWLOCATION, true);
			
			$request_headers = array(
			  'Connection: keep-alive',
			  'Upgrade-Insecure-Requests: 1',
			  'User-Agent: Googlebot/2.1 (+http://www.googlebot.com/bot.html)',
			  'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			  'Accept-Encoding: compressed',
			  'Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3',
			  'Content-Type: application/x-www-form-urlencoded',
			);
			
			curl_setopt($Curl, CURLOPT_HTTPHEADER, $request_headers);
			
			if(!empty($data))
				curl_setopt($Curl, CURLOPT_POSTFIELDS, $data);
			
			curl_setopt($Curl, CURLOPT_ENCODING,  'gzip,deflate');
			if(!empty($proxy))
				curl_setopt($Curl, CURLOPT_PROXY, $proxy);
			
			//curl_setopt($Curl, CURLOPT_CONNECTTIMEOUT, 5);
			//curl_setopt($Curl, CURLOPT_TIMEOUT, 5);
			//curl_setopt($Curl, CURLOPT_POSTREDIR, 3);
			curl_setopt($Curl,CURLOPT_COOKIEFILE,$cerez);
			curl_setopt($Curl,CURLOPT_COOKIEJAR,$cerez);
	
			$VeriOkux = curl_exec ($Curl);
			curl_close($Curl);
			
			$dosyayolu = dirname( __FILE__ )."/";

			$fp = fopen($dosyayolu."/images/".$dosya_adi,'w');
			fwrite($fp, $VeriOkux);
			fclose($fp);
}

	if (file_exists($cerez))
		unlink($cerez);

	$proxyp = @$_POST['proxy'];

	$getsiteler =file_get_contents('txt/sitelerim.txt');
	$getsitelerdata = explode(PHP_EOL, $getsiteler);
		
	for($i = 0; $i < count($getsitelerdata); $i++)
	{
		$sorgulancaksite = $getsitelerdata[$i];
		$datas = VeriOku2("https://internet.btk.gov.tr/sitesorgu/",null,$proxy);
		if(empty($datas))
		{
			print_r(json_encode(array("success"=>false,"error"=>"Proxyden veri gelmedi !")));
			die();
		}

		preg_match('@data-zoom-image="(.*?)"@si', $datas, $images);
		
		$images = "https://internet.btk.gov.tr".str_replace("&amp;","&",$images[1]);
		VeriOkufile_download($images,"test.png");

		$api = new ImageToText();
		$api->setVerboseMode(false);
		$api->setCaseFlag(true);
		$api->setKey($apicaptchakey);
		$dosyayolu = dirname( __FILE__ )."/";
		$api->setFile($dosyayolu."/images/"."test.png");
		if (!$api->createTask()) {
			return false;
		}

		$taskId = $api->getTaskId();

		if (!$api->waitForResult()) {} else {
			$captchaText =   $api->getTaskSolution();
			$arr = array(
				"deger"=>$sorgulancaksite,
				"ipw"=>"",
				"kat"=>"",
				"tr"=>"",
				"eg"=>"",
				"ayrintili"=>0,
				"submit"=>"Sorgula",
				"security_code"=>$captchaText
			);
			$register = VeriOku2("https://internet.btk.gov.tr/sitesorgu/",$arr,$proxy);

			preg_match('@<div class="yazi_tum_icerik">(.*?)</div>@si', $register, $sonuc);
			
			if(empty($sonuc[1]))
				mail($mailayarlardata[1], $sorgulancaksite." Sitesi İçin Btk Sorgusu Yapıldı : Engel Yok", $sorgulancaksite." Sitesi İçin Sorgusu Yapıldı : Engel Yok . Captcha : ".$captchaText." Tarih Saat :".date("Y-m-d H:i:s"),"From:" . $mailayarlardata[0]);
			else
				mail($mailayarlardata[1], $sorgulancaksite." Sitesi İçin Btk Sorgusu Yapıldı : Btk Engeli Mevcut", $sorgulancaksite." Sitesi İçin Sorgusu Yapıldı : Btk Engeli Mevcut . Captcha : ".$captchaText." Tarih Saat :".date("Y-m-d H:i:s"),"From:" . $mailayarlardata[0]);
		}
	}
	
?>