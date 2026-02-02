<?php
/**
 * کاوې کلی موټر سروس API
 * 
 * د API اصلي فایل چې ټول عمليات پرمخ وړي
 * 
 * @version 1.0.0
 * @package KawaimotorAPI
 * @author کاوې کلی ټیم
 */

// ==================== د سیسټم تنظیمات ====================
// لومړی headers ولیکئ وروسته output
ob_start(); // د output buffering پیل کول

// د session پیل کول
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// د JSON فایلونو لارې
define('DATA_DIR', __DIR__ . '/data/');

// ==================== د JSON فایلونو چک او جوړول ====================
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

$required_files = [
    'users.json',
    'drivers.json', 
    'passengers.json',
    'trips.json',
    'bookings.json',
    'logs.json',
    'settings.json',
    'admin_pass.txt'
];

// د settings.json د تازه کولو لپاره چک
$settings_initialized = false;
$settings_file_path = DATA_DIR . 'settings.json';

// که settings.json نه وي یا خالي وي، بیا جوړ کړئ
if (!file_exists($settings_file_path) || filesize($settings_file_path) < 10) {
    $initial_settings = [
        'api_version' => '1.0.0',
        'site_title' => 'کاوې کلی موټر سروس API',
        'currency' => 'افغانۍ',
        'rates' => [
            'market' => 100,
            'clinic' => 70
        ],
        'locations' => [
            'کوز کلی',
            'برکلی', 
            'منځ کلی',
            'د پرښو کلی',
            'د بارخیلو جومات',
            'سپین جومات'
        ],
        'reset_time' => '21:00:00',
        'contact_numbers' => [
            '0790123456',
            '0780123456'
        ],
        'max_trips_per_day' => 2,
        'max_bookings_per_day' => 1,
        'api_key' => 'kawaimotor_' . md5(time())
    ];
    
    file_put_contents($settings_file_path, json_encode($initial_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $settings_initialized = true;
}

// نور فایلونه چک او جوړول
foreach ($required_files as $file) {
    $file_path = DATA_DIR . $file;
    
    if (!file_exists($file_path)) {
        switch($file) {
            case 'users.json':
                file_put_contents($file_path, json_encode(['users' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
                
            case 'drivers.json':
                file_put_contents($file_path, json_encode(['drivers' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
                
            case 'passengers.json':
                file_put_contents($file_path, json_encode(['passengers' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
                
            case 'trips.json':
                file_put_contents($file_path, json_encode(['trips' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
                
            case 'bookings.json':
                file_put_contents($file_path, json_encode(['bookings' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
                
            case 'logs.json':
                $log_entry = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'action' => 'API سیسټم پیل شو',
                    'user_type' => 'system',
                    'details' => 'د JSON فایلونه جوړ شول',
                    'ip' => '127.0.0.1'
                ];
                file_put_contents($file_path, json_encode(['logs' => [$log_entry]], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
                
            case 'admin_pass.txt':
                file_put_contents($file_path, 'admin123');
                break;
        }
    }
}

// د تنظیماتو لوستل او د غلطو مخنیوی
$settings = [];
if (file_exists($settings_file_path)) {
    $settings_content = file_get_contents($settings_file_path);
    if (!empty($settings_content)) {
        $settings = json_decode($settings_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // که JSON خراب وي، بیا جوړ کړئ
            $settings = [
                'api_version' => '1.0.0',
                'site_title' => 'کاوې کلی موټر سروس API',
                'currency' => 'افغانۍ',
                'rates' => [
                    'market' => 100,
                    'clinic' => 70
                ],
                'locations' => [
                    'کوز کلی',
                    'برکلی', 
                    'منځ کلی',
                    'د پرښو کلی',
                    'د بارخیلو جومات',
                    'سپین جومات'
                ],
                'reset_time' => '21:00:00',
                'contact_numbers' => [
                    '0790123456',
                    '0780123456'
                ],
                'max_trips_per_day' => 2,
                'max_bookings_per_day' => 1,
                'api_key' => 'kawaimotor_' . md5(time())
            ];
            file_put_contents($settings_file_path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

// د تنظیماتو ډیفالټ ارزښتونه
$default_settings = [
    'api_version' => '1.0.0',
    'site_title' => 'کاوې کلی موټر سروس API',
    'currency' => 'افغانۍ',
    'rates' => ['market' => 100, 'clinic' => 70],
    'locations' => ['کوز کلی', 'برکلی', 'منځ کلی', 'د پرښو کلی', 'د بارخیلو جومات', 'سپین جومات'],
    'reset_time' => '21:00:00',
    'contact_numbers' => ['0790123456', '0780123456'],
    'max_trips_per_day' => 2,
    'max_bookings_per_day' => 1,
    'api_key' => 'kawaimotor_' . md5(time())
];

// د تنظیماتو ډیفالټ ارزښتونه تنظیم کول
foreach ($default_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}

// اوس headers ولیکئ
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// د OPTIONS درخواست ځواب
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean(); // د buffer پاکول
    http_response_code(200);
    exit();
}

// ==================== د مهمو فانکشنونو تعریف ====================

/**
 * د لاګ ثبتولو فانکشن
 * 
 * @param string $action د عمل نوم
 * @param string $user_type د کارن ډول
 * @param string $details د عمل تفصیل
 * @return void
 */
function logAction($action, $user_type, $details) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'user_type' => $user_type,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    
    $logs_file = DATA_DIR . 'logs.json';
    
    // د فایل شتون چک
    if (!file_exists($logs_file)) {
        file_put_contents($logs_file, json_encode(['logs' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    $logs_data = json_decode(file_get_contents($logs_file), true);
    
    if (!isset($logs_data['logs'])) {
        $logs_data['logs'] = [];
    }
    
    array_unshift($logs_data['logs'], $log_entry);
    
    // یوازې وروستي 1000 لاګونه ساتل
    if (count($logs_data['logs']) > 1000) {
        $logs_data['logs'] = array_slice($logs_data['logs'], 0, 1000);
    }
    
    file_put_contents($logs_file, json_encode($logs_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * د JSON ځواب لیږلو فانکشن
 * 
 * @param mixed $data د لیږلو ډېټا
 * @param int $status_code د HTTP حالت کوډ
 * @param string $message د ځواب پیغام
 * @return void
 */
function sendResponse($data = null, $status_code = 200, $message = 'بریالی') {
    // لومړی buffer پاک کړئ
    ob_clean();
    
    http_response_code($status_code);
    
    $response = [
        'success' => $status_code >= 200 && $status_code < 300,
        'status' => $status_code,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit();
}

/**
 * د غلطۍ ځواب لیږلو فانکشن
 * 
 * @param string $message د غلطۍ پیغام
 * @param int $status_code د HTTP حالت کوډ
 * @return void
 */
function sendError($message = 'نامعلومه غلطه', $status_code = 400) {
    sendResponse(null, $status_code, $message);
}

/**
 * د ان پټ پاکولو فانکشن
 * 
 * @param mixed $input د ان پټ ډېټا
 * @return mixed پاک شوی ډېټا
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
        return $input;
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * د موبایل شمېرې اعتبار چک
 * 
 * @param string $mobile د موبایل شمېره
 * @return bool
 */
function validateMobile($mobile) {
    return preg_match('/^07[0-9]{8}$/', $mobile) === 1;
}

/**
 * د API کي چک
 * 
 * @param string $api_key د API کي
 * @return bool
 */
function validateApiKey($api_key) {
    global $settings;
    return isset($settings['api_key']) && $api_key === $settings['api_key'];
}

/**
 * د کارن اعتبار چک
 * 
 * @param string $user_id د کارن ID
 * @param string $password د کارن پاسورډ
 * @return array|false د کارن معلومات یا false
 */
function authenticateUser($user_id, $password) {
    $users_file = DATA_DIR . 'users.json';
    
    if (!file_exists($users_file)) {
        return false;
    }
    
    $users_data = json_decode(file_get_contents($users_file), true);
    
    if (!isset($users_data['users'])) {
        return false;
    }
    
    foreach ($users_data['users'] as $user) {
        if ($user['id'] === $user_id && isset($user['status']) && $user['status'] === 'active') {
            // د ساده پاسورډ چک
            $expected_password = substr(md5($user['mobile'] . 'salt'), 0, 6);
            if ($password === $expected_password) {
                return $user;
            }
        }
    }
    
    return false;
}

/**
 * د اډمین اعتبار چک
 * 
 * @param string $password د اډمین پاسورډ
 * @return bool
 */
function authenticateAdmin($password) {
    $admin_pass_file = DATA_DIR . 'admin_pass.txt';
    
    if (!file_exists($admin_pass_file)) {
        return false;
    }
    
    $admin_pass = trim(file_get_contents($admin_pass_file));
    return $password === $admin_pass;
}

/**
 * د ورځني ریسټ چک
 * 
 * @return void
 */
function checkDailyReset() {
    $reset_time = date('Y-m-d') . ' 21:00:00';
    $now = date('Y-m-d H:i:s');
    
    $last_reset_file = DATA_DIR . 'last_reset.txt';
    
    if (!file_exists($last_reset_file)) {
        file_put_contents($last_reset_file, '');
    }
    
    $last_reset = file_get_contents($last_reset_file);
    
    if ($now >= $reset_time && $last_reset != date('Y-m-d')) {
        // د ریسټ عمل
        $trips_file = DATA_DIR . 'trips.json';
        $bookings_file = DATA_DIR . 'bookings.json';
        
        // د trips فایل چک
        if (file_exists($trips_file)) {
            $trips_data = json_decode(file_get_contents($trips_file), true);
            if ($trips_data && isset($trips_data['trips'])) {
                foreach ($trips_data['trips'] as &$trip) {
                    if (isset($trip['date']) && $trip['date'] < date('Y-m-d')) {
                        $trip['status'] = 'expired';
                    }
                }
                file_put_contents($trips_file, json_encode($trips_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
        
        // د bookings فایل چک
        if (file_exists($bookings_file)) {
            $bookings_data = json_decode(file_get_contents($bookings_file), true);
            if ($bookings_data && isset($bookings_data['bookings'])) {
                $bookings_data['bookings'] = array_filter($bookings_data['bookings'], function($booking) {
                    return isset($booking['trip_date']) && strpos($booking['trip_date'], date('Y-m-d')) !== false;
                });
                file_put_contents($bookings_file, json_encode($bookings_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
        
        file_put_contents($last_reset_file, date('Y-m-d'));
        
        logAction('ورځنی ریسټ', 'system', 'زاړه سفرونه او غوښتنې پاک شول');
    }
}

// د ورځني ریسټ چک پیل کول
checkDailyReset();

// ==================== د API درخواست پروسس کول ====================

// د درخواست معلومات
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH) ?? '/';
$query_string = $_SERVER['QUERY_STRING'] ?? '';
$input_data = [];

// د POST/PUT ډېټا ترلاسه کول
if ($request_method === 'POST' || $request_method === 'PUT') {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($content_type, 'application/json') !== false) {
        $json_input = file_get_contents('php://input');
        if (!empty($json_input)) {
            $input_data = json_decode($json_input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $input_data = [];
            }
        }
    } else {
        $input_data = $_POST;
    }
    
    $input_data = sanitizeInput($input_data);
}

// د GET پیرامټرونه
if ($request_method === 'GET' && !empty($query_string)) {
    parse_str($query_string, $get_params);
    $input_data = sanitizeInput($get_params);
}

// د API کي چک (اختیاري)
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? ($input_data['api_key'] ?? '');
if (!empty($api_key) && !validateApiKey($api_key)) {
    sendError('ناسم API کي', 401);
}

// د API مسیر تحلیل کول
$api_path = str_replace('/index.php', '', $path);
$api_segments = explode('/', trim($api_path, '/'));
$endpoint = $api_segments[0] ?? '';

// ==================== د API عمليات ====================

try {
    switch ($endpoint) {
        // ============ عمومي API ============
        case '':
        case 'home':
        case 'welcome':
            $response_data = [
                'api' => 'کاوې کلی موټر سروس API',
                'version' => $settings['api_version'] ?? '1.0.0',
                'status' => 'فعال',
                'endpoints' => [
                    'GET /' => 'د API معلومات',
                    'POST /auth/register' => 'د ثبت نام',
                    'POST /auth/login' => 'د ننوتلو',
                    'GET /trips' => 'د سفرونو لیست',
                    'GET /trips/{id}' => 'د سفر معلومات',
                    'POST /trips' => 'نوی سفر اعلان',
                    'GET /bookings' => 'د غوښتنو لیست',
                    'POST /bookings' => 'نوی غوښتنه',
                    'GET /users' => 'د کاروونکو لیست (اډمین)',
                    'GET /logs' => 'د لاګونو لیست (اډمین)',
                    'POST /admin/reset' => 'د سیستم ریسټ (اډمین)'
                ],
                'contact' => $settings['contact_numbers'] ?? ['0790123456', '0780123456']
            ];
            sendResponse($response_data, 200, 'د API ته ښه راغلئ');
            break;

        // ============ د ثبت نام API ============
        case 'auth':
            $action = $api_segments[1] ?? '';
            
            if ($request_method === 'POST') {
                if ($action === 'register') {
                    // د ثبت نام عملیات
                    $required_fields = ['name', 'father_name', 'mobile', 'role'];
                    
                    foreach ($required_fields as $field) {
                        if (empty($input_data[$field])) {
                            sendError("د $field فیلډ اړین دی", 400);
                        }
                    }
                    
                    $name = $input_data['name'];
                    $father_name = $input_data['father_name'];
                    $mobile = $input_data['mobile'];
                    $role = $input_data['role'];
                    
                    if (!validateMobile($mobile)) {
                        sendError('د موبایل شمېره باید 10 رقمي وي او له 07 پیل شي', 400);
                    }
                    
                    if (!in_array($role, ['passenger', 'driver'])) {
                        sendError('د رول ارزښت باید passenger یا driver وي', 400);
                    }
                    
                    // د user.json اضافه کول
                    $users_file = DATA_DIR . 'users.json';
                    
                    // د فایل شتون چک
                    if (!file_exists($users_file)) {
                        file_put_contents($users_file, json_encode(['users' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    }
                    
                    $users_data = json_decode(file_get_contents($users_file), true);
                    
                    if (!isset($users_data['users'])) {
                        $users_data['users'] = [];
                    }
                    
                    // د موبایل شمېرې چک
                    foreach ($users_data['users'] as $user) {
                        if (isset($user['mobile']) && $user['mobile'] === $mobile) {
                            sendError('دا موبایل شمېره دمخه ثبت شوې ده', 400);
                        }
                    }
                    
                    // نوی user جوړول
                    $user_id = 'U' . time() . rand(100, 999);
                    $password = substr(md5($mobile . 'salt'), 0, 6);
                    
                    $new_user = [
                        'id' => $user_id,
                        'name' => $name,
                        'father_name' => $father_name,
                        'mobile' => $mobile,
                        'role' => $role,
                        'status' => 'pending',
                        'registration_date' => date('Y-m-d H:i:s'),
                        'password' => $password
                    ];
                    
                    if ($role === 'driver') {
                        // د موټروان اضافي معلومات
                        $required_driver_fields = ['whatsapp', 'car_name', 'seats', 'cargo_capacity', 'location'];
                        
                        foreach ($required_driver_fields as $field) {
                            if (empty($input_data[$field])) {
                                sendError("د $field فیلډ اړین دی", 400);
                            }
                        }
                        
                        $whatsapp = $input_data['whatsapp'];
                        $car_name = $input_data['car_name'];
                        $seats = intval($input_data['seats']);
                        $cargo_capacity = $input_data['cargo_capacity'];
                        $location = $input_data['location'];
                        
                        if (!validateMobile($whatsapp)) {
                            sendError('د WhatsApp شمېره باید 10 رقمي وي او له 07 پیل شي', 400);
                        }
                        
                        if ($seats < 1 || $seats > 20) {
                            sendError('د څوکیو شمېر باید له 1 څخه تر 20 پورې وي', 400);
                        }
                        
                        // د driver.json اضافه کول
                        $drivers_file = DATA_DIR . 'drivers.json';
                        
                        if (!file_exists($drivers_file)) {
                            file_put_contents($drivers_file, json_encode(['drivers' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        }
                        
                        $drivers_data = json_decode(file_get_contents($drivers_file), true);
                        
                        if (!isset($drivers_data['drivers'])) {
                            $drivers_data['drivers'] = [];
                        }
                        
                        $new_driver = [
                            'user_id' => $user_id,
                            'whatsapp' => $whatsapp,
                            'car_name' => $car_name,
                            'seats' => $seats,
                            'cargo_capacity' => $cargo_capacity,
                            'location' => $location
                        ];
                        
                        $drivers_data['drivers'][] = $new_driver;
                        file_put_contents($drivers_file, json_encode($drivers_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    }
                    
                    // د passenger.json اضافه کول
                    $passengers_file = DATA_DIR . 'passengers.json';
                    
                    if (!file_exists($passengers_file)) {
                        file_put_contents($passengers_file, json_encode(['passengers' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    }
                    
                    $passengers_data = json_decode(file_get_contents($passengers_file), true);
                    
                    if (!isset($passengers_data['passengers'])) {
                        $passengers_data['passengers'] = [];
                    }
                    
                    $new_passenger = [
                        'user_id' => $user_id,
                        'total_bookings' => 0,
                        'last_booking' => null
                    ];
                    
                    $passengers_data['passengers'][] = $new_passenger;
                    file_put_contents($passengers_file, json_encode($passengers_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    // د user اضافه کول
                    $users_data['users'][] = $new_user;
                    file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    logAction('نوی ثبت نام', $role, $name . ' (' . $mobile . ') ثبت شو');
                    
                    sendResponse([
                        'user_id' => $user_id,
                        'message' => 'ثبت نام بریالی شو! د اډمین تایید ته انتظار وکړئ',
                        'password' => $password // یوازې د پروډکشن کې دا نه لیږل کیږي
                    ], 201, 'ثبت نام بریالی شو');
                    
                } elseif ($action === 'login') {
                    // د ننوتلو عملیات
                    if (empty($input_data['mobile']) || empty($input_data['password'])) {
                        sendError('موبایل شمېره او پاسورډ اړین دی', 400);
                    }
                    
                    $mobile = $input_data['mobile'];
                    $password = $input_data['password'];
                    
                    // د کارن پلټل
                    $users_file = DATA_DIR . 'users.json';
                    
                    if (!file_exists($users_file)) {
                        sendError('د سیسټم فایلونه نشته', 500);
                    }
                    
                    $users_data = json_decode(file_get_contents($users_file), true);
                    
                    if (!isset($users_data['users'])) {
                        sendError('هېڅ کاروونکي نشته', 404);
                    }
                    
                    $user_found = false;
                    foreach ($users_data['users'] as $user) {
                        if (isset($user['mobile']) && $user['mobile'] === $mobile) {
                            if (!isset($user['status']) || $user['status'] !== 'active') {
                                sendError('ستاسو حساب لا تر اوسه تایید نشوی دی', 403);
                            }
                            
                            $expected_password = substr(md5($mobile . 'salt'), 0, 6);
                            if ($password === $expected_password) {
                                $user_found = true;
                                
                                // د اضافي معلومات ترلاسه کول
                                $user_info = [
                                    'id' => $user['id'] ?? '',
                                    'name' => $user['name'] ?? '',
                                    'mobile' => $user['mobile'] ?? '',
                                    'role' => $user['role'] ?? '',
                                    'registration_date' => $user['registration_date'] ?? ''
                                ];
                                
                                if ($user['role'] === 'driver') {
                                    $drivers_file = DATA_DIR . 'drivers.json';
                                    
                                    if (file_exists($drivers_file)) {
                                        $drivers_data = json_decode(file_get_contents($drivers_file), true);
                                        
                                        if (isset($drivers_data['drivers'])) {
                                            foreach ($drivers_data['drivers'] as $driver) {
                                                if (isset($driver['user_id']) && $driver['user_id'] === $user['id']) {
                                                    $user_info['driver_details'] = $driver;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                logAction('ننوتل', $user['role'], ($user['name'] ?? 'Unknown') . ' ننوت');
                                
                                sendResponse([
                                    'user' => $user_info,
                                    'token' => base64_encode($user['id'] . ':' . $password) // ساده ټوکن
                                ], 200, 'ننوتل بریالی شو');
                            }
                            break;
                        }
                    }
                    
                    if (!$user_found) {
                        sendError('د موبایل شمېره یا پاسورډ ناسم دی', 401);
                    }
                } else {
                    sendError('د API مسیر نه موندل شو', 404);
                }
            } else {
                sendError('د HTTP مېتود نه منل کیږي', 405);
            }
            break;

        // ============ د سفرونو API ============
        case 'trips':
            $trip_id = $api_segments[1] ?? null;
            
            if ($request_method === 'GET') {
                if ($trip_id) {
                    // د یو سفر معلومات
                    $trips_file = DATA_DIR . 'trips.json';
                    
                    if (!file_exists($trips_file)) {
                        sendError('سفرونه نشته', 404);
                    }
                    
                    $trips_data = json_decode(file_get_contents($trips_file), true);
                    
                    if (!isset($trips_data['trips'])) {
                        sendError('سفرونه نشته', 404);
                    }
                    
                    $trip_found = null;
                    foreach ($trips_data['trips'] as $trip) {
                        if (isset($trip['id']) && $trip['id'] === $trip_id) {
                            $trip_found = $trip;
                            break;
                        }
                    }
                    
                    if ($trip_found) {
                        sendResponse($trip_found, 200, 'سفر معلومات');
                    } else {
                        sendError('سفر نه موندل شو', 404);
                    }
                } else {
                    // د ټولو سفرونو لیست
                    $trips_file = DATA_DIR . 'trips.json';
                    
                    if (!file_exists($trips_file)) {
                        sendResponse([
                            'count' => 0,
                            'trips' => []
                        ], 200, 'سفرونه ترلاسه شول');
                    }
                    
                    $trips_data = json_decode(file_get_contents($trips_file), true);
                    
                    if (!isset($trips_data['trips'])) {
                        sendResponse([
                            'count' => 0,
                            'trips' => []
                        ], 200, 'سفرونه ترلاسه شول');
                    }
                    
                    // د فیلټر پیرامټرونه
                    $date = $input_data['date'] ?? date('Y-m-d');
                    $destination = $input_data['destination'] ?? null;
                    $location = $input_data['location'] ?? null;
                    $status = $input_data['status'] ?? 'active';
                    
                    $filtered_trips = [];
                    foreach ($trips_data['trips'] as $trip) {
                        if (!isset($trip['date'], $trip['status'])) {
                            continue;
                        }
                        
                        if ($trip['date'] === $date && $trip['status'] === $status) {
                            if ($destination && isset($trip['destination']) && $trip['destination'] !== $destination) {
                                continue;
                            }
                            if ($location && isset($trip['location']) && $trip['location'] !== $location) {
                                continue;
                            }
                            $filtered_trips[] = $trip;
                        }
                    }
                    
                    sendResponse([
                        'count' => count($filtered_trips),
                        'trips' => $filtered_trips
                    ], 200, 'سفرونه ترلاسه شول');
                }
                
            } elseif ($request_method === 'POST') {
                // نوی سفر اعلان
                if (empty($input_data['user_id']) || empty($input_data['password'])) {
                    sendError('د ننوتلو معلومات اړین دی', 401);
                }
                
                $user_id = $input_data['user_id'];
                $password = $input_data['password'];
                
                $user = authenticateUser($user_id, $password);
                if (!$user || $user['role'] !== 'driver') {
                    sendError('د سفر اعلان لپاره د موټروان حساب ته اړتیا ده', 403);
                }
                
                // د ننني سفرونو شمېر چک
                $trips_file = DATA_DIR . 'trips.json';
                
                $today_trips = 0;
                if (file_exists($trips_file)) {
                    $trips_data = json_decode(file_get_contents($trips_file), true);
                    
                    if (isset($trips_data['trips'])) {
                        foreach ($trips_data['trips'] as $trip) {
                            if (isset($trip['driver_id'], $trip['date']) && 
                                $trip['driver_id'] === $user_id && 
                                $trip['date'] === date('Y-m-d')) {
                                $today_trips++;
                            }
                        }
                    }
                }
                
                $max_trips = $settings['max_trips_per_day'] ?? 2;
                if ($today_trips >= $max_trips) {
                    sendError('تاسو نن نور سفرونه نشئ اعلان کولی', 429);
                }
                
                // د سفر معلومات
                $required_fields = ['destination', 'time'];
                
                foreach ($required_fields as $field) {
                    if (empty($input_data[$field])) {
                        sendError("د $field فیلډ اړین دی", 400);
                    }
                }
                
                $destination = $input_data['destination'];
                $time = $input_data['time'];
                $notes = $input_data['notes'] ?? '';
                
                if (!in_array($destination, ['غزني بازار', 'د وردګو کلینیک'])) {
                    sendError('مقصد باید غزني بازار یا د وردګو کلینیک وي', 400);
                }
                
                // د موټروان معلومات
                $drivers_file = DATA_DIR . 'drivers.json';
                
                $driver_info = null;
                if (file_exists($drivers_file)) {
                    $drivers_data = json_decode(file_get_contents($drivers_file), true);
                    
                    if (isset($drivers_data['drivers'])) {
                        foreach ($drivers_data['drivers'] as $driver) {
                            if (isset($driver['user_id']) && $driver['user_id'] === $user_id) {
                                $driver_info = $driver;
                                break;
                            }
                        }
                    }
                }
                
                if (!$driver_info) {
                    sendError('د موټروان معلومات نه موندل شول', 404);
                }
                
                // نوی سفر جوړول
                $new_trip_id = 'T' . time() . rand(100, 999);
                
                // د کرایې محاسبه
                $fare_passenger = 100; // ډیفالټ د بازار لپاره
                if ($destination === 'د وردګو کلینیک') {
                    $fare_passenger = 70;
                }
                
                $new_trip = [
                    'id' => $new_trip_id,
                    'driver_id' => $user_id,
                    'driver_name' => $user['name'] ?? '',
                    'date' => date('Y-m-d'),
                    'time' => $time,
                    'destination' => $destination,
                    'total_seats' => $driver_info['seats'] ?? 4,
                    'available_seats' => $driver_info['seats'] ?? 4,
                    'fare_passenger' => $fare_passenger,
                    'fare_cargo' => 50,
                    'location' => $driver_info['location'] ?? 'کوز کلی',
                    'status' => 'active',
                    'notes' => $notes,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // د trips فایل تازه کول
                if (!file_exists($trips_file)) {
                    $trips_data = ['trips' => []];
                } else {
                    $trips_data = json_decode(file_get_contents($trips_file), true);
                    if (!isset($trips_data['trips'])) {
                        $trips_data['trips'] = [];
                    }
                }
                
                $trips_data['trips'][] = $new_trip;
                file_put_contents($trips_file, json_encode($trips_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                logAction('نوی سفر', 'driver', ($user['name'] ?? 'Unknown') . ' د ' . $destination . ' لپاره سفر اعلان کړ');
                
                sendResponse($new_trip, 201, 'سفر په بریالیتوب سره اعلان شو');
                
            } elseif ($request_method === 'PUT' && $trip_id) {
                // د سفر تازه کول
                if (empty($input_data['user_id']) || empty($input_data['password'])) {
                    sendError('د ننوتلو معلومات اړین دی', 401);
                }
                
                $user_id = $input_data['user_id'];
                $password = $input_data['password'];
                
                $user = authenticateUser($user_id, $password);
                if (!$user || $user['role'] !== 'driver') {
                    sendError('د سفر تازه کولو لپاره د موټروان حساب ته اړتیا ده', 403);
                }
                
                $trips_file = DATA_DIR . 'trips.json';
                
                if (!file_exists($trips_file)) {
                    sendError('سفرونه نشته', 404);
                }
                
                $trips_data = json_decode(file_get_contents($trips_file), true);
                
                if (!isset($trips_data['trips'])) {
                    sendError('سفرونه نشته', 404);
                }
                
                $trip_found = false;
                foreach ($trips_data['trips'] as &$trip) {
                    if (isset($trip['id'], $trip['driver_id']) && 
                        $trip['id'] === $trip_id && 
                        $trip['driver_id'] === $user_id) {
                        // یوازې ځینې فیلډونه تازه کول اجازه لري
                        if (isset($input_data['notes'])) {
                            $trip['notes'] = $input_data['notes'];
                        }
                        
                        if (isset($input_data['status']) && in_array($input_data['status'], ['active', 'cancelled'])) {
                            $trip['status'] = $input_data['status'];
                        }
                        
                        $trip_found = true;
                        break;
                    }
                }
                
                if ($trip_found) {
                    file_put_contents($trips_file, json_encode($trips_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    logAction('سفر تازه کول', 'driver', ($user['name'] ?? 'Unknown') . ' د ' . $trip_id . ' سفر تازه کړ');
                    
                    sendResponse(null, 200, 'سفر په بریالیتوب سره تازه شو');
                } else {
                    sendError('سفر نه موندل شو یا تاسو د دې سفر مالک نه یاست', 404);
                }
                
            } else {
                sendError('د HTTP مېتود نه منل کیږي', 405);
            }
            break;

        // ============ د غوښتنو API ============
        case 'bookings':
            $booking_id = $api_segments[1] ?? null;
            
            if ($request_method === 'GET') {
                if ($booking_id) {
                    // د یوې غوښتنې معلومات
                    $bookings_file = DATA_DIR . 'bookings.json';
                    
                    if (!file_exists($bookings_file)) {
                        sendError('غوښتنې نشته', 404);
                    }
                    
                    $bookings_data = json_decode(file_get_contents($bookings_file), true);
                    
                    if (!isset($bookings_data['bookings'])) {
                        sendError('غوښتنې نشته', 404);
                    }
                    
                    $booking_found = null;
                    foreach ($bookings_data['bookings'] as $booking) {
                        if (isset($booking['id']) && $booking['id'] === $booking_id) {
                            $booking_found = $booking;
                            break;
                        }
                    }
                    
                    if ($booking_found) {
                        sendResponse($booking_found, 200, 'د غوښتنې معلومات');
                    } else {
                        sendError('غوښتنه نه موندل شوه', 404);
                    }
                } else {
                    // د ټولو غوښتنو لیست
                    $bookings_file = DATA_DIR . 'bookings.json';
                    
                    if (!file_exists($bookings_file)) {
                        sendResponse([
                            'count' => 0,
                            'bookings' => []
                        ], 200, 'غوښتنې ترلاسه شوې');
                    }
                    
                    $bookings_data = json_decode(file_get_contents($bookings_file), true);
                    
                    if (!isset($bookings_data['bookings'])) {
                        sendResponse([
                            'count' => 0,
                            'bookings' => []
                        ], 200, 'غوښتنې ترلاسه شوې');
                    }
                    
                    // د فیلټر پیرامټرونه
                    $passenger_id = $input_data['passenger_id'] ?? null;
                    $trip_id = $input_data['trip_id'] ?? null;
                    $date = $input_data['date'] ?? date('Y-m-d');
                    $status = $input_data['status'] ?? null;
                    
                    $filtered_bookings = [];
                    foreach ($bookings_data['bookings'] as $booking) {
                        if (!isset($booking['trip_date'])) {
                            continue;
                        }
                        
                        if ($date && $booking['trip_date'] !== $date) {
                            continue;
                        }
                        if ($passenger_id && (!isset($booking['passenger_id']) || $booking['passenger_id'] !== $passenger_id)) {
                            continue;
                        }
                        if ($trip_id && (!isset($booking['trip_id']) || $booking['trip_id'] !== $trip_id)) {
                            continue;
                        }
                        if ($status && (!isset($booking['status']) || $booking['status'] !== $status)) {
                            continue;
                        }
                        $filtered_bookings[] = $booking;
                    }
                    
                    sendResponse([
                        'count' => count($filtered_bookings),
                        'bookings' => $filtered_bookings
                    ], 200, 'غوښتنې ترلاسه شوې');
                }
                
            } elseif ($request_method === 'POST') {
                // نوی غوښتنه
                if (empty($input_data['user_id']) || empty($input_data['password'])) {
                    sendError('د ننوتلو معلومات اړین دی', 401);
                }
                
                $user_id = $input_data['user_id'];
                $password = $input_data['password'];
                
                $user = authenticateUser($user_id, $password);
                if (!$user || $user['role'] !== 'passenger') {
                    sendError('د غوښتنې لپاره د مسافر حساب ته اړتیا ده', 403);
                }
                
                // د ننني غوښتنو شمېر چک
                $bookings_file = DATA_DIR . 'bookings.json';
                
                $today_bookings = 0;
                if (file_exists($bookings_file)) {
                    $bookings_data = json_decode(file_get_contents($bookings_file), true);
                    
                    if (isset($bookings_data['bookings'])) {
                        foreach ($bookings_data['bookings'] as $booking) {
                            if (isset($booking['passenger_id'], $booking['trip_date'], $booking['status']) && 
                                $booking['passenger_id'] === $user_id && 
                                $booking['trip_date'] === date('Y-m-d') &&
                                in_array($booking['status'], ['pending', 'confirmed'])) {
                                $today_bookings++;
                            }
                        }
                    }
                }
                
                $max_bookings = $settings['max_bookings_per_day'] ?? 1;
                if ($today_bookings >= $max_bookings) {
                    sendError('تاسو نن نورې غوښتنې نشئ کولی', 429);
                }
                
                // د غوښتنې معلومات
                $required_fields = ['trip_id', 'males'];
                
                foreach ($required_fields as $field) {
                    if (empty($input_data[$field])) {
                        sendError("د $field فیلډ اړین دی", 400);
                    }
                }
                
                $trip_id = $input_data['trip_id'];
                $males = intval($input_data['males']);
                $females = intval($input_data['females'] ?? 0);
                $cargo = $input_data['cargo'] ?? '';
                $notes = $input_data['notes'] ?? '';
                
                // د سفر معلومات چک
                $trips_file = DATA_DIR . 'trips.json';
                
                if (!file_exists($trips_file)) {
                    sendError('سفرونه نشته', 404);
                }
                
                $trips_data = json_decode(file_get_contents($trips_file), true);
                
                if (!isset($trips_data['trips'])) {
                    sendError('سفرونه نشته', 404);
                }
                
                $trip_found = null;
                foreach ($trips_data['trips'] as $trip) {
                    if (isset($trip['id'], $trip['status']) && $trip['id'] === $trip_id) {
                        $trip_found = $trip;
                        break;
                    }
                }
                
                if (!$trip_found || $trip_found['status'] !== 'active') {
                    sendError('سفر نشته یا غیر فعال دی', 404);
                }
                
                if (!isset($trip_found['available_seats']) || $trip_found['available_seats'] < ($males + $females)) {
                    sendError('په دې سفر کې ډیرې څوکۍ نشته', 400);
                }
                
                // نوی booking جوړول
                $new_booking_id = 'B' . time() . rand(100, 999);
                $new_booking = [
                    'id' => $new_booking_id,
                    'trip_id' => $trip_id,
                    'passenger_id' => $user_id,
                    'passenger_name' => $user['name'] ?? '',
                    'trip_date' => $trip_found['date'] ?? date('Y-m-d'),
                    'males' => $males,
                    'females' => $females,
                    'cargo' => $cargo,
                    'notes' => $notes,
                    'status' => 'pending',
                    'booking_time' => date('Y-m-d H:i:s')
                ];
                
                // د bookings فایل تازه کول
                if (!file_exists($bookings_file)) {
                    $bookings_data = ['bookings' => []];
                } else {
                    $bookings_data = json_decode(file_get_contents($bookings_file), true);
                    if (!isset($bookings_data['bookings'])) {
                        $bookings_data['bookings'] = [];
                    }
                }
                
                $bookings_data['bookings'][] = $new_booking;
                file_put_contents($bookings_file, json_encode($bookings_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                logAction('نوی غوښتنه', 'passenger', 
                    ($user['name'] ?? 'Unknown') . ' د ' . $trip_id . ' سفر غوښتنه وکړه');
                
                sendResponse($new_booking, 201, 'غوښتنه په بریالیتوب سره ثبت شوه');
                
            } else {
                sendError('د HTTP مېتود نه منل کیږي', 405);
            }
            break;

        // ============ د احصایې API ============
        case 'stats':
            if ($request_method === 'GET') {
                // د اډمین اعتبار چک (اختیاري)
                $is_admin = false;
                if (!empty($input_data['admin_password'])) {
                    $is_admin = authenticateAdmin($input_data['admin_password']);
                }
                
                $today = date('Y-m-d');
                
                // د مختلفو فایلونو لوستل
                $users_data = [];
                $users_file = DATA_DIR . 'users.json';
                if (file_exists($users_file)) {
                    $users_data = json_decode(file_get_contents($users_file), true) ?: [];
                }
                
                $trips_data = [];
                $trips_file = DATA_DIR . 'trips.json';
                if (file_exists($trips_file)) {
                    $trips_data = json_decode(file_get_contents($trips_file), true) ?: [];
                }
                
                $bookings_data = [];
                $bookings_file = DATA_DIR . 'bookings.json';
                if (file_exists($bookings_file)) {
                    $bookings_data = json_decode(file_get_contents($bookings_file), true) ?: [];
                }
                
                // د عمومي احصایې
                $stats = [
                    'general' => [
                        'total_users' => isset($users_data['users']) ? count($users_data['users']) : 0,
                        'pending_users' => isset($users_data['users']) ? count(array_filter($users_data['users'], 
                            function($user) { return isset($user['status']) && $user['status'] === 'pending'; })) : 0,
                        'active_users' => isset($users_data['users']) ? count(array_filter($users_data['users'], 
                            function($user) { return isset($user['status']) && $user['status'] === 'active'; })) : 0,
                        'total_trips' => isset($trips_data['trips']) ? count($trips_data['trips']) : 0,
                        'total_bookings' => isset($bookings_data['bookings']) ? count($bookings_data['bookings']) : 0
                    ],
                    'today' => [
                        'active_trips' => isset($trips_data['trips']) ? count(array_filter($trips_data['trips'], 
                            function($trip) use ($today) { 
                                return isset($trip['date'], $trip['status']) && 
                                       $trip['date'] === $today && 
                                       $trip['status'] === 'active'; 
                            })) : 0,
                        'today_bookings' => isset($bookings_data['bookings']) ? count(array_filter($bookings_data['bookings'], 
                            function($booking) use ($today) { 
                                return isset($booking['trip_date']) && 
                                       $booking['trip_date'] === $today; 
                            })) : 0,
                        'confirmed_bookings' => isset($bookings_data['bookings']) ? count(array_filter($bookings_data['bookings'], 
                            function($booking) use ($today) { 
                                return isset($booking['trip_date'], $booking['status']) && 
                                       $booking['trip_date'] === $today && 
                                       $booking['status'] === 'confirmed'; 
                            })) : 0
                    ]
                ];
                
                // که اډمین وي، اضافي معلومات
                if ($is_admin) {
                    $stats['admin'] = [
                        'drivers' => isset($users_data['users']) ? count(array_filter($users_data['users'], 
                            function($user) { return isset($user['role']) && $user['role'] === 'driver'; })) : 0,
                        'passengers' => isset($users_data['users']) ? count(array_filter($users_data['users'], 
                            function($user) { return isset($user['role']) && $user['role'] === 'passenger'; })) : 0,
                        'cancelled_trips' => isset($trips_data['trips']) ? count(array_filter($trips_data['trips'], 
                            function($trip) { return isset($trip['status']) && $trip['status'] === 'cancelled'; })) : 0,
                        'rejected_bookings' => isset($bookings_data['bookings']) ? count(array_filter($bookings_data['bookings'], 
                            function($booking) { return isset($booking['status']) && $booking['status'] === 'rejected'; })) : 0
                    ];
                }
                
                sendResponse($stats, 200, 'احصایه ترلاسه شوه');
                
            } else {
                sendError('د HTTP مېتود نه منل کیږي', 405);
            }
            break;

        // ============ د چټک آزمایښت API ============
        case 'test':
            if ($request_method === 'GET') {
                $test_data = [
                    'message' => 'API په سمه توګه کار کوي',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'server_info' => [
                        'php_version' => PHP_VERSION,
                        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                        'request_method' => $_SERVER['REQUEST_METHOD'],
                        'request_uri' => $_SERVER['REQUEST_URI'] ?? '/'
                    ],
                    'data_status' => [
                        'users_count' => file_exists(DATA_DIR . 'users.json') ? 
                            count(json_decode(file_get_contents(DATA_DIR . 'users.json'), true)['users'] ?? []) : 0,
                        'trips_count' => file_exists(DATA_DIR . 'trips.json') ? 
                            count(json_decode(file_get_contents(DATA_DIR . 'trips.json'), true)['trips'] ?? []) : 0,
                        'bookings_count' => file_exists(DATA_DIR . 'bookings.json') ? 
                            count(json_decode(file_get_contents(DATA_DIR . 'bookings.json'), true)['bookings'] ?? []) : 0
                    ]
                ];
                
                sendResponse($test_data, 200, 'د API آزمایښت بریالی');
                
            } elseif ($request_method === 'POST') {
                // د POST آزمایښت
                $test_data = [
                    'message' => 'POST درخواست په سمه توګه ترلاسه شوه',
                    'received_data' => $input_data,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                
                sendResponse($test_data, 200, 'د POST آزمایښت بریالی');
                
            } else {
                sendError('د HTTP مېتود نه منل کیږي', 405);
            }
            break;

        // ============ د API مستندات ============
        case 'docs':
        case 'documentation':
            $documentation = [
                'api' => 'کاوې کلی موټر سروس API',
                'version' => $settings['api_version'] ?? '1.0.0',
                'base_url' => ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['SCRIPT_NAME'] ?? '/index.php'),
                'authentication' => [
                    'user_auth' => 'کاروونکي باید په /auth/login کې ننوتل وکړي او د ټوکن په توګه user_id:password په base64 کې وکاروي',
                    'admin_auth' => 'د اډمین عملياتو لپاره admin_password پارامټر ضروري دی'
                ],
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'endpoint' => '/',
                        'description' => 'د API لومړنی معلومات',
                        'authentication' => 'نه',
                        'parameters' => 'هېڅ',
                        'response' => 'د API معلومات او لارښود'
                    ],
                    [
                        'method' => 'POST',
                        'endpoint' => '/auth/register',
                        'description' => 'نوی کارن ثبت کول',
                        'authentication' => 'نه',
                        'parameters' => 'name, father_name, mobile, role, [driver_details]',
                        'response' => 'د کارن معلومات او پاسورډ'
                    ],
                    [
                        'method' => 'POST',
                        'endpoint' => '/auth/login',
                        'description' => 'کارن ننوتل',
                        'authentication' => 'نه',
                        'parameters' => 'mobile, password',
                        'response' => 'د کارن معلومات او ټوکن'
                    ],
                    [
                        'method' => 'GET',
                        'endpoint' => '/trips',
                        'description' => 'د سفرونو لیست',
                        'authentication' => 'نه',
                        'parameters' => '[date, destination, location, status]',
                        'response' => 'د سفرونو لیست'
                    ],
                    [
                        'method' => 'POST',
                        'endpoint' => '/trips',
                        'description' => 'نوی سفر اعلان کول',
                        'authentication' => 'موټروان',
                        'parameters' => 'user_id, password, destination, time, [notes]',
                        'response' => 'د سفر معلومات'
                    ],
                    [
                        'method' => 'POST',
                        'endpoint' => '/bookings',
                        'description' => 'نوی سفر غوښتنه',
                        'authentication' => 'مسافر',
                        'parameters' => 'user_id, password, trip_id, males, [females, cargo, notes]',
                        'response' => 'د غوښتنې معلومات'
                    ]
                ],
                'error_codes' => [
                    '200' => 'بریالی',
                    '201' => 'جوړ شو',
                    '400' => 'ناسمه درخواست',
                    '401' => 'غیر مجاز',
                    '403' => 'منع شوی',
                    '404' => 'نه موندل شو',
                    '405' => 'د مېتود اجازه نشته',
                    '429' => 'ډیرې درخواستې',
                    '500' => 'د سرور داخلي غلطه'
                ],
                'contact' => [
                    'phone' => $settings['contact_numbers'][0] ?? '0790123456'
                ]
            ];
            
            sendResponse($documentation, 200, 'API مستندات');
            break;

        // ============ د غلط API مسیر ============
        default:
            sendError('د API مسیر نه موندل شو. د /docs لپاره API مستندات وګورئ.', 404);
            break;
    }
    
} catch (Exception $e) {
    // د ناڅاپي غلطو مدیریت
    logAction('API غلطه', 'system', $e->getMessage() . ' په ' . $e->getFile() . ':' . $e->getLine());
    
    if ((isset($_SERVER['HTTP_HOST']) && 
         (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
          strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) ||
        PHP_SAPI === 'cli') {
        // د ډیولپمنټ چاپیریال
        sendError('د سرور غلطه: ' . $e->getMessage(), 500);
    } else {
        // د پروډکشن چاپیریال
        sendError('د سرور داخلي غلطه. مهرباني وکړئ وروسته بیا هڅه وکړئ.', 500);
    }
}
?>
