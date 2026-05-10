<?php
require_once 'db.php';
require_once 'google_config.php';

class CloudRouter {
    private $pdo;
    private $accounts = [];

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $stmt = $this->pdo->query("SELECT * FROM cloud_accounts WHERE service_name='google_drive'");
        $this->accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_next_account() {
        if (empty($this->accounts)) return null;
        // Simple Round-Robin for now
        static $index = 0;
        $acc = $this->accounts[$index % count($this->accounts)];
        $index++;
        return $acc;
    }

    private function get_or_create_folder($name, $parent_id, $token) {
        // Search for folder
        $query = "name = '$name' and '$parent_id' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
        $url = "https://www.googleapis.com/drive/v3/files?q=" . urlencode($query);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!empty($res['files'])) return $res['files'][0]['id'];

        // Create if not exists
        $meta = ['name' => $name, 'mimeType' => 'application/vnd.google-apps.folder', 'parents' => [$parent_id]];
        $ch = curl_init('https://www.googleapis.com/drive/v3/files');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meta));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $res['id'] ?? $parent_id;
    }

    public function upload_direct($source_url, $filename, $mime_type) {
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ignore_user_abort(true);
        
        $acc = $this->get_next_account();
        if (!$acc) return ['error' => 'No cloud accounts linked.'];

        $token = $this->refresh_token($acc);
        $root_folder = $acc['remote_folder_id'];

        // 1. Determine Subfolder
        $subfolder_name = "Crevix_General";
        if (strpos($mime_type, 'video') !== false) $subfolder_name = "Crevix_Videos";
        elseif (strpos($mime_type, 'image') !== false) $subfolder_name = "Crevix_Images";
        
        $target_folder = $this->get_or_create_folder($subfolder_name, $root_folder, $token);

        // 2. BUFFERED INGESTION: Context-Aware Retrieval
        $temp_dir = 'uploads/buffer';
        if (!is_dir($temp_dir)) mkdir($temp_dir, 0777, true);
        $temp_path = "$temp_dir/$filename";

        if (strpos($mime_type, 'image') !== false) {
            // High-speed CURL for photos
            $ch = curl_init($source_url);
            $fp = fopen($temp_path, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        } else {
            // Industrial Engine for videos
            $yt_cmd = "yt-dlp --fixup detect_or_warn --concurrent-fragments 5 -o " . escapeshellarg($temp_path) . " " . escapeshellarg($source_url);
            shell_exec($yt_cmd);
        }

        if (!file_exists($temp_path) || filesize($temp_path) < 100) {
            return ['error' => 'Buffer Download Failed', 'path' => $temp_path];
        }

        // STEP 1: Create Resumable Upload Session
        $meta = ['name' => $filename, 'parents' => [$target_folder]];
        
        $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json; charset=UTF-8',
            'X-Upload-Content-Type: ' . $mime_type
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meta));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $resp = curl_exec($ch);
        curl_close($ch);

        $location = "";
        if (preg_match('/location: (.*)/i', $resp, $matches)) {
            $location = trim($matches[1]);
        }
        if (!$location) return ['error' => 'Could not start resumable session'];

        // STEP 2: Stream from Buffer to Drive
        $src = fopen($temp_path, 'rb');
        $ch = curl_init($location);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILE, $src);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($temp_path));
        
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        fclose($src);

        // STEP 3: PURGE BUFFER (Zero Disk Promise)
        unlink($temp_path);

        if (isset($res['id'])) {
            $drive_id = $res['id'];
            
            // STEP 3: Set Permissions to 'Anyone with Link' (Fixes Access Denied)
            $perm_url = "https://www.googleapis.com/drive/v3/files/$drive_id/permissions";
            $perm_meta = ['role' => 'reader', 'type' => 'anyone'];
            $ch = curl_init($perm_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($perm_meta));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);

            return [
                'success' => true,
                'drive_id' => $drive_id,
                'account_id' => $acc['id'],
                'account_email' => $acc['account_email']
            ];
        }
        return ['error' => 'Buffered Upload Failed', 'details' => $res];
    }

    private function refresh_token($acc) {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'refresh_token' => $acc['refresh_token'],
            'grant_type'    => 'refresh_token'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        if (isset($data['access_token'])) {
            $this->pdo->prepare("UPDATE cloud_accounts SET access_token = ? WHERE id = ?")
                      ->execute([$data['access_token'], $acc['id']]);
            return $data['access_token'];
        }
        return $acc['access_token'];
    }
}
?>
