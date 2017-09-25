<?php
require_once APPPATH . '/libraries/REST_Controller.php';
require "env.php";

class Init extends REST_Controller
{

    protected $tokens = [];

    public function index_get() {
        $token = get_cookie('shopify_token');
        $shop = get_cookie('shopify_shop');
        var_dump($token);var_dump($shop);
        $headers = [
            'X-Shopify-Access-Token:'. $token
        ];
//        $prods = $this->call_get('https://' .$shop . '/admin/products.json?access_token=', $headers);var_dump($prods);
        $wh = [
            'script_tag' => [
                "event" => "onload",
                "src" => APP_URL . 'assets/main.js',
                "display_scope" => 'all'
            ]
        ];
        $nscript = $this->call_post('https://'. $shop . '/admin/script_tags.json', $wh, $headers);
        var_dump($nscript);
        $scripts = $this->call_get('https://' .$shop . '/admin/script_tags.json', $headers);
        var_dump($scripts);
//        $webhooks = $this->call_get('https://'. $shop . '/admin/webhooks.json', $headers);var_dump($webhooks);
    }

    public function install_get()
    {
        $shop = $_GET['shop'];
        $install_url = "http://{$shop}/admin/oauth/authorize?client_id=" . API_KEY . "&scope=". APP_SCOPES. "&redirect_uri=" . APP_URL . 'init/auth';
        redirect($install_url);
    }

    public function auth_get()
    {
        $code = $_GET['code'];
        $shop = $_GET['shop'];
        $hmac = $_GET['hmac'];
        $params = $_GET;
        unset($params['hmac']);
        ksort($params);
        $str = http_build_query($params);
        $digest = hash_hmac('sha256', $str, API_SECRET);
        if ($digest != $hmac) {
            $this->response("Authentication failed. Digest provided was: {$digest}", 403);
        }
//        if (!isset($this->tokens[$shop])) {
        $payload = [
            'client_id' => API_KEY,
            'client_secret' => API_SECRET,
            'code' => $code
        ];
        $url = "https://{$shop}/admin/oauth/access_token.json";
        $content = $this->call_post($url, $payload);

        $this->tokens[$shop] = $content['access_token'];
        set_cookie('shopify_token', $content['access_token'], time() + 3600 * 7);
        set_cookie('shopify_shop', $shop, time() + 3600 * 7);
        $this->session->set_userdata('shopify_token', $content['access_token']);
        $this->session->set_userdata('shopify_shop', $shop);

        var_dump($content);
        redirect(APP_URL);
    }

    public function script_post() {}

    protected function call_get($url, $headers = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!is_null($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $result = curl_exec($ch);
        if (curl_error($ch)) {
            return false;
        }
        curl_close($ch);
        return json_decode($result, TRUE);
    }

    protected function call_post($url, $data, $headers = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // json_encode($fields)
        if (!is_null($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $result = curl_exec($ch);
        if (curl_error($ch)) {
            return false;
        }
        curl_close($ch);
        return json_decode($result, TRUE);
    }

    protected function guzzle_post($url, $data, $headers = null) {
        $client = new \GuzzleHttp\Client();
        $data['json'] = $data;
        $req = $client->request('POST', $url, $data);
        $statusCode = $req->getStatusCode();
        if ($statusCode != 200) {
            $this->response("Something went wrong", 500);
        }
        return $req->getBody()->getContents();
    }
}