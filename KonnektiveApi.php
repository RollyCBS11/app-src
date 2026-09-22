<?php
class KonnektiveApi {

	private $loginId;
	private $password;
	private $curl; // reusable cURL handle

	public function __construct($loginId = null, $password = null) {
		self::loadEnv();

		$this->loginId = $loginId ?: getenv('KONNEKTIVE_LOGIN_ID');
		$this->password = $password ?: getenv('KONNEKTIVE_PASSWORD');

		// Initialize reusable cURL handle
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_POST, 1);		
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
	

		// Allow connection reuse (persistent keep-alive)
		//curl_setopt($this->curl, CURLOPT_FORBID_REUSE, false);
		//curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, false);

		// Default headers with keep-alive
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json',
			'Connection: keep-alive'
		]);
	}

	public function __destruct() {
		// Close cURL when the object is destroyed
		if ($this->curl) {
			curl_close($this->curl);
		}
	}

	public function creditials($loginId, $password) {
		$this->loginId = $loginId;
		$this->password = $password;
	}

	// Parses the project's .env file into getenv()/putenv() once per request,
	// without overwriting variables already set by the system environment.
	private static function loadEnv() {
		static $loaded = false;
		if ($loaded) {
			return;
		}
		$loaded = true;

		$envFile = (defined('BASEPATH') ? BASEPATH : dirname(__DIR__)) . '/.env';
		if (!is_file($envFile)) {
			return;
		}

		foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
			$line = trim($line);
			if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
				continue;
			}

			list($key, $value) = array_map('trim', explode('=', $line, 2));
			if (getenv($key) !== false) {
				continue; // don't override a real environment variable
			}
			putenv($key . '=' . trim($value, "\"'"));
		}
	}

	public function import_click($data) {
		return $this->post_request($data, 'https://api.checkoutchamp.com/landers/clicks/import/');
	}

	public function customer_add_note($data){
		return $this->post_request($data,'https://api.checkoutchamp.com/customer/addnote/');
	}
	
	public function import_lead($data) {
		return $this->post_request($data, 'https://api.checkoutchamp.com/leads/import/');
	}
	
	public function import_order($data) {
		return $this->post_request($data, 'https://api.checkoutchamp.com/order/import/');
	}
	
	public function import_upsale($data) {
		return $this->post_request($data, 'https://api.checkoutchamp.com/upsale/import/');
	}
	
	public function query_order($data) {
		return $this->post_request($data, 'https://api.checkoutchamp.com/order/query/');
	}

	public function confirm_paypal($data) {
		return $this->post_request($data, 'https://api.checkoutchamp.com/transactions/confirmPaypal/');
	}

	public function order_qa($data) {
		return $this->post_request($data, 'https://api.checkoutchamp.com/order/qa/');
	}
	
	public function confirm_order($orderId) {
		return $this->post_request(['orderId' => $orderId], 'https://api.checkoutchamp.com/order/confirm/');
	}
	
	public function get_order($orderId, $email, $campaignId) {
		$resp = $this->post_request([
			'orderId' => $orderId,
			'emailAddress' =>$email,
			'campaignId' => $campaignId
	
		], 'https://api.checkoutchamp.com/order/query/');
		$resp = json_decode($resp);
		
		if ($resp->result === "SUCCESS") {
			return $resp->message->data[0];
		}
		return null;
	}

	public function check_duplicate_order($customerId, $numDays=0) {
		$resp = $this->post_request([			
			'customerId' => $customerId,
			'orderStatus' => "SALE",
			'startDate' => date("m/d/Y", strtotime($numDays." days")),
			'endDate' => date("m/d/Y"),
		],  'https://api.checkoutchamp.com/order/query/');
		$resp = json_decode($resp);
		
		if ($resp->result === "SUCCESS") {
			return json_encode([
				"foundOrders" => count($resp->message->data),
				"customerId" => $resp->message->data[0]->customerId
			]);
			//return $resp->message;
		}
		return null;
	}

	public function queryCustomerRecord($fname, $lname, $email, $numDays=-30) {
		$resp = $this->post_request([			
			'firstName' => $fname,
			'lastName' => $lname,
			'startDate' => date("m/d/Y", strtotime($numDays." days")),
			'endDate' => date("m/d/Y"),
			'emailAddress' => $email,
			'exactEmailMatch' => 1,
			'excludeHistory' => 1
		],  'https://api.checkoutchamp.com/customer/query/');
		$resp = json_decode($resp);
		
		if ($resp->result === "SUCCESS") {
			return json_encode([
				"foundOrders" => count($resp->message->data),
				"customerId" => $resp->message->data[0]->customerId
			]);
			//return $resp->message;
		}
		return null;
	}
	
	private $campaignCacheTtl = 1800; // seconds; campaign/pricing config rarely changes intra-minute

	public function clearCampaignCache($campaignId = null) {
		$cacheDir = BASEPATH . '/cachev2/campaign';

		if ($campaignId !== null) {
			$file = $cacheDir . '/' . basename((string)$campaignId) . '.json';
			if (is_file($file)) {
				unlink($file);
			}
			return;
		}

		foreach (glob($cacheDir . '/*.json') ?: [] as $file) {
			unlink($file);
		}
	}

	public function get_campaign($campaignId) {
		$cacheDir = BASEPATH . '/cachev2/campaign';
		$cacheFile = $cacheDir . '/' . $campaignId . '.json';

		if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $this->campaignCacheTtl) {
			$cached = file_get_contents($cacheFile);
			if ($cached !== false) {
				return $cached;
			}
		}

		$resp = $this->post_request(['campaignId' => $campaignId], 'https://api.checkoutchamp.com/campaign/query/');
		$resp = json_decode($resp);

		if ($resp->result === "SUCCESS") {
			$result = json_encode([
				"result" => "SUCCESS",
				"products" => $resp->message->data->$campaignId->products,
				"countries"=> $resp->message->data->$campaignId->countries,
				"currency" =>$resp->message->data->$campaignId->currency,
				"currencySymbol" =>$resp->message->data->$campaignId->currencySymbol,
				"coupons" =>$resp->message->data->$campaignId->coupons,
				"shipProfiles" =>$resp->message->data->$campaignId->shipProfiles,
				"taxes" =>$resp->message->data->$campaignId->taxes,
				"zones" =>$resp->message->data->$campaignId->zones,
				"storePickup" =>$resp->message->data->$campaignId->storePickup,
				"reOrderDays" =>$resp->message->data->$campaignId->reOrderDays,
			]);

			if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
				return $result; // couldn't create cache dir, still return fresh data
			}
			file_put_contents($cacheFile, $result, LOCK_EX);

			return $result;
		} else {
			return json_encode([
				"result" => "ERROR",
				"message" => $resp->message
			]);
		}
	}


	

	private function post_request($data, $url, $async=false) {
		// Build query params
		$query = http_build_query(array_merge($data, [
			'loginId' => $this->loginId,
			'password' => $this->password
		])); 
		
		// Set URL dynamically for each request
		curl_setopt($this->curl, CURLOPT_URL, $url . '?' . $query);

		if(!$async){
			curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 90);
			curl_setopt($this->curl, CURLOPT_TIMEOUT, 120);
		} else {
			curl_setopt($this->curl, CURLOPT_TIMEOUT, 1);
		}
		

		// Execute
		$resp = curl_exec($this->curl);
		$httpcode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);


		if (curl_errno($this->curl)) {
			$error_msg = curl_error($this->curl);
			return json_encode([
				"result" => "ERROR",
				"message" => $error_msg
			]);
		}

		return $resp;
	}
}
