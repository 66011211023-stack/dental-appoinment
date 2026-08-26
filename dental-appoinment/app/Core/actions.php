<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

final class Actions
{
    public static function handle(string $action): void
    {
        Auth::requireLogin();
        Csrf::verify();
        try {
            match ($action) {
                'patient_save' => self::patientSave(),
                'history_save' => self::historySave(),
                'appointment_save' => self::appointmentSave(),
                'appointment_status' => self::appointmentStatus(),
                'appointment_cancel_patient' => self::appointmentCancelPatient(),
                'schedule_save' => self::scheduleSave(),
                'treatment_save' => self::treatmentSave(),
                'material_save' => self::materialSave(),
                'material_transaction' => self::materialTransaction(),
                'user_save' => self::userSave(),
                'user_toggle' => self::userToggle(),
                'right_save' => self::rightSave(),
                'right_toggle' => self::rightToggle(),
                'notifications_read' => self::notificationsRead(),
                'profile_save' => self::profileSave(),
                default => throw new \RuntimeException('ไม่พบคำสั่งที่ร้องขอ'),
            };
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'ดำเนินการไม่สำเร็จ: ' . $e->getMessage();
            $appointmentPage=(Auth::user()['role']??'')==='patient'?'booking':'appointments';
            $fallbackPages = ['appointment_save'=>$appointmentPage,'appointment_status'=>'appointments','appointment_cancel_patient'=>'appointments','schedule_save'=>'schedule','profile_save'=>'profile'];
            self::redirect((string)($_POST['return_page'] ?? $fallbackPages[$action] ?? 'dashboard'));
        }
    }

    private static function role(array $roles): array
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role'], $roles, true)) {
            throw new \RuntimeException('คุณไม่มีสิทธิ์ดำเนินการนี้');
        }
        return $user;
    }

    private static function required(string $key): string
    {
        $value = trim((string)($_POST[$key] ?? ''));
        if ($value === '') throw new \InvalidArgumentException('กรุณากรอกข้อมูลให้ครบ');
        return $value;
    }

    private static function patientSave(): void
    {
        self::role(['admin', 'staff']);
        $id = (int)($_POST['id'] ?? 0);
        $values = [self::required('hn'), trim((string)($_POST['citizen_id'] ?? '')), self::required('full_name'), $_POST['birth_date'] ?: null, trim((string)($_POST['phone'] ?? '')), trim((string)($_POST['address'] ?? '')), trim((string)($_POST['treatment_right'] ?? ''))];
        if ($id) {
            Database::query('UPDATE patients SET hn=?,citizen_id=?,full_name=?,birth_date=?,phone=?,address=?,treatment_right=? WHERE id=?', [...$values, $id]);
        } else {
            Database::query('INSERT INTO patients(hn,citizen_id,full_name,birth_date,phone,address,treatment_right) VALUES(?,?,?,?,?,?,?)', $values);
        }
        self::success('บันทึกข้อมูลผู้ป่วยแล้ว', 'patients');
    }

    private static function historySave(): void
    {
        self::role(['admin', 'staff', 'dentist']);
        Database::query('INSERT INTO medical_histories(patient_id,allergy,disease,medicine,blood_pressure) VALUES(?,?,?,?,?)', [(int)self::required('patient_id'), trim((string)($_POST['allergy'] ?? '')), trim((string)($_POST['disease'] ?? '')), trim((string)($_POST['medicine'] ?? '')), trim((string)($_POST['blood_pressure'] ?? ''))]);
        self::success('บันทึกประวัติสุขภาพแล้ว', 'patients');
    }

    private static function appointmentSave(): void
    {
        $user = self::role(['admin', 'staff', 'patient']);
        $patientId = (int)($_POST['patient_id'] ?? 0);
        if ($user['role'] === 'patient') {
            $patientId = (int)Database::query('SELECT id FROM patients WHERE user_id=?', [(int)$user['id']])->fetchColumn();
        }
        if (!$patientId) throw new \RuntimeException('ไม่พบข้อมูลผู้ป่วยของบัญชีนี้');
        $pdo = Database::connection(); $pdo->beginTransaction();
        try {
            $scheduleId = 0;
            if ($user['role'] === 'patient') {
                $scheduleId = (int)self::required('schedule_id');
                $slot = Database::query('SELECT * FROM dentist_schedules WHERE id=? FOR UPDATE', [$scheduleId])->fetch();
                if (!$slot || $slot['status'] !== 'available') throw new \RuntimeException('ช่วงเวลานี้ไม่ว่างแล้ว กรุณาเลือกเวลาใหม่');
                $date=(string)$slot['available_date']; $time=(string)$slot['start_time']; $dentistId=(int)$slot['dentist_id'];
            } else {
                $date=self::required('appointment_date'); $time=self::required('appointment_time'); $dentistId=(int)self::required('dentist_id');
                $slot=Database::query('SELECT id,status FROM dentist_schedules WHERE dentist_id=? AND available_date=? AND start_time=? LIMIT 1 FOR UPDATE',[$dentistId,$date,$time])->fetch();
                if($slot && $slot['status']!=='available') throw new \RuntimeException('ช่วงเวลานี้ไม่ว่างแล้ว');
                $scheduleId=(int)($slot['id']??0);
            }
            if ($date < date('Y-m-d')) throw new \InvalidArgumentException('ไม่สามารถจองวันที่ย้อนหลังได้');
            $exists=(int)Database::query("SELECT COUNT(*) FROM appointments WHERE dentist_id=? AND appointment_date=? AND appointment_time=? AND status<>'cancelled'",[$dentistId,$date,$time])->fetchColumn();
            if($exists) throw new \RuntimeException('ช่วงเวลานี้มีนัดหมายแล้ว');
            Database::query('INSERT INTO appointments(patient_id,dentist_id,appointment_date,appointment_time,service,status,note) VALUES(?,?,?,?,?,?,?)',[$patientId,$dentistId,$date,$time,self::required('service'),$user['role']==='patient'?'pending':'approved',trim((string)($_POST['note']??''))]);
            if($scheduleId) Database::query("UPDATE dentist_schedules SET status='reserved' WHERE id=?",[$scheduleId]);
            if($user['role']==='patient') Database::query('INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)',[(int)$user['id'],'ส่งคำขอจองคิวแล้ว','ระบบได้รับคำขอวันที่ '.$date.' เวลา '.substr($time,0,5).' น. แล้ว']);
            $pdo->commit();
        } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
        self::success('บันทึกนัดหมายแล้ว', 'appointments');
    }

    private static function appointmentStatus(): void
    {
        $user = self::role(['admin', 'staff', 'dentist']);
        $status = self::required('status');
        if (!in_array($status, ['pending','approved','completed','cancelled'], true)) throw new \InvalidArgumentException('สถานะไม่ถูกต้อง');
        $id=(int)self::required('id'); $pdo=Database::connection(); $pdo->beginTransaction();
        try{
            $appointment=Database::query('SELECT a.*,p.user_id FROM appointments a JOIN patients p ON p.id=a.patient_id WHERE a.id=? FOR UPDATE',[$id])->fetch();
            if(!$appointment) throw new \RuntimeException('ไม่พบนัดหมาย');
            if($user['role']==='dentist'){
                $dentistId=(int)Database::query('SELECT id FROM dentists WHERE user_id=?',[(int)$user['id']])->fetchColumn();
                if($dentistId!==(int)$appointment['dentist_id']||$status!=='completed') throw new \RuntimeException('ทันตแพทย์ทำเครื่องหมายเสร็จสิ้นได้เฉพาะนัดของตนเอง');
            }
            Database::query('UPDATE appointments SET status=? WHERE id=?',[$status,$id]);
            if($status==='cancelled') self::setMatchingScheduleStatus($appointment,'available');
            if($status==='approved') self::setMatchingScheduleStatus($appointment,'reserved');
            if($appointment['user_id']){
                $messages=['approved'=>'นัดหมายของคุณได้รับการอนุมัติแล้ว','completed'=>'การนัดหมายเสร็จสิ้นแล้ว','cancelled'=>'คำขอนัดหมายของคุณถูกปฏิเสธหรือยกเลิก','pending'=>'นัดหมายอยู่ระหว่างตรวจสอบ'];
                Database::query('INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)',[(int)$appointment['user_id'],'อัปเดตสถานะนัดหมาย',$messages[$status]]);
            }
            $pdo->commit();
        }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
        self::success('อัปเดตสถานะนัดหมายแล้ว', 'appointments');
    }

    private static function appointmentCancelPatient(): void
    {
        $user=self::role(['patient']); $id=(int)self::required('id'); $pdo=Database::connection(); $pdo->beginTransaction();
        try{
            $appointment=Database::query('SELECT a.* FROM appointments a JOIN patients p ON p.id=a.patient_id WHERE a.id=? AND p.user_id=? FOR UPDATE',[$id,(int)$user['id']])->fetch();
            if(!$appointment) throw new \RuntimeException('ไม่พบนัดหมายของคุณ');
            if(!in_array($appointment['status'],['pending','approved'],true)||$appointment['appointment_date']<date('Y-m-d')) throw new \RuntimeException('นัดหมายนี้ไม่สามารถยกเลิกได้');
            Database::query("UPDATE appointments SET status='cancelled' WHERE id=?",[$id]);
            self::setMatchingScheduleStatus($appointment,'available');
            Database::query('INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)',[(int)$user['id'],'ยกเลิกนัดหมายแล้ว','ระบบยกเลิกนัดวันที่ '.$appointment['appointment_date'].' เวลา '.substr($appointment['appointment_time'],0,5).' น. แล้ว']);
            $pdo->commit();
        }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
        self::success('ยกเลิกนัดหมายแล้ว','appointments');
    }

    private static function setMatchingScheduleStatus(array $appointment,string $status): void
    {
        Database::query('UPDATE dentist_schedules SET status=? WHERE dentist_id=? AND available_date=? AND start_time=?',[$status,(int)$appointment['dentist_id'],$appointment['appointment_date'],$appointment['appointment_time']]);
    }

    private static function scheduleSave(): void
    {
        $user=self::role(['admin','staff','dentist']); $dentistId=(int)self::required('dentist_id');
        if($user['role']==='dentist'){$ownId=(int)Database::query('SELECT id FROM dentists WHERE user_id=?',[(int)$user['id']])->fetchColumn();if($dentistId!==$ownId)throw new \RuntimeException('เพิ่มเวลาได้เฉพาะตารางของตนเอง');}
        $date=self::required('available_date');$start=self::required('start_time');$end=self::required('end_time');
        if($date<date('Y-m-d'))throw new \InvalidArgumentException('ไม่สามารถเพิ่มวันที่ย้อนหลังได้');
        if($start>=$end)throw new \InvalidArgumentException('เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม');
        $overlap=(int)Database::query('SELECT COUNT(*) FROM dentist_schedules WHERE dentist_id=? AND available_date=? AND start_time<? AND end_time>?',[$dentistId,$date,$end,$start])->fetchColumn();
        if($overlap)throw new \RuntimeException('ช่วงเวลานี้ทับซ้อนกับตารางเดิม');
        Database::query('INSERT INTO dentist_schedules(dentist_id,available_date,start_time,end_time,status) VALUES(?,?,?,?,?)',[$dentistId,$date,$start,$end,$_POST['status']??'available']);
        self::success('เพิ่มเวลาทันตแพทย์แล้ว', 'schedule');
    }

    private static function treatmentSave(): void
    {
        self::role(['admin', 'dentist']);
        $appointmentId = (int)self::required('appointment_id');
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::query('INSERT INTO treatments(appointment_id,diagnosis,treatment_detail,tooth_position,cost) VALUES(?,?,?,?,?)', [$appointmentId, self::required('diagnosis'), self::required('treatment_detail'), trim((string)($_POST['tooth_position'] ?? '')), (float)($_POST['cost'] ?? 0)]);
            Database::query("UPDATE appointments SET status='completed' WHERE id=?", [$appointmentId]);
            $pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
        self::success('บันทึกการรักษาแล้ว', 'treatments');
    }

    private static function materialSave(): void
    {
        self::role(['admin', 'staff']);
        Database::query('INSERT INTO materials(material_code,material_name,unit,quantity,reorder_level,price,expiry_date) VALUES(?,?,?,?,?,?,?)', [self::required('material_code'), self::required('material_name'), self::required('unit'), (int)($_POST['quantity'] ?? 0), (int)($_POST['reorder_level'] ?? 5), (float)($_POST['price'] ?? 0), $_POST['expiry_date'] ?: null]);
        self::success('เพิ่มวัสดุแล้ว', 'materials');
    }

    private static function materialTransaction(): void
    {
        $user = self::role(['admin', 'staff', 'dentist']);
        $materialId = (int)self::required('material_id');
        $quantity = (int)self::required('quantity');
        $type = self::required('transaction_type');
        if ($quantity < 1 || !in_array($type, ['receive','issue'], true)) throw new \InvalidArgumentException('รายการวัสดุไม่ถูกต้อง');
        $pdo = Database::connection(); $pdo->beginTransaction();
        try {
            $stock = (int)Database::query('SELECT quantity FROM materials WHERE id=? FOR UPDATE', [$materialId])->fetchColumn();
            $newStock = $type === 'receive' ? $stock + $quantity : $stock - $quantity;
            if ($newStock < 0) throw new \RuntimeException('วัสดุคงเหลือไม่เพียงพอ');
            Database::query('UPDATE materials SET quantity=? WHERE id=?', [$newStock, $materialId]);
            Database::query('INSERT INTO material_transactions(material_id,user_id,transaction_type,quantity,note) VALUES(?,?,?,?,?)', [$materialId, (int)$user['id'], $type, $quantity, trim((string)($_POST['note'] ?? ''))]);
            $pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
        self::success($type === 'receive' ? 'รับวัสดุเข้าคลังแล้ว' : 'เบิกวัสดุแล้ว', (string)($_POST['return_page'] ?? 'materials'));
    }

    private static function userSave(): void
    {
        self::role(['admin']);
        $username = self::required('username'); $role = self::required('role');
        if (!in_array($role, ['admin','staff','dentist','patient'], true)) throw new \InvalidArgumentException('บทบาทไม่ถูกต้อง');
        $pdo=Database::connection();$pdo->beginTransaction();
        try {
            Database::query('INSERT INTO users(username,password_hash,role,full_name,email) VALUES(?,?,?,?,?)', [$username, Password::hash(self::required('password')), $role, self::required('full_name'), trim((string)($_POST['email'] ?? ''))]);
            $id=(int)$pdo->lastInsertId();
            if($role==='dentist') Database::query('INSERT INTO dentists(user_id,license_no,specialty,phone) VALUES(?,?,?,?)',[$id,self::required('license_no'),trim((string)($_POST['specialty']??'')),trim((string)($_POST['phone']??''))]);
            if($role==='patient') Database::query('INSERT INTO patients(user_id,hn,full_name,phone) VALUES(?,?,?,?)',[$id,self::required('hn'),self::required('full_name'),trim((string)($_POST['phone']??''))]);
            $pdo->commit();
        } catch(Throwable $e){$pdo->rollBack();throw $e;}
        self::success('เพิ่มบัญชีผู้ใช้แล้ว', 'users');
    }

    private static function userToggle(): void
    {
        $current = self::role(['admin']); $id = (int)self::required('id');
        if ($id === (int)$current['id']) throw new \RuntimeException('ไม่สามารถปิดบัญชีที่กำลังใช้งาน');
        Database::query('UPDATE users SET is_active=IF(is_active=1,0,1) WHERE id=?', [$id]);
        self::success('เปลี่ยนสถานะบัญชีแล้ว', 'users');
    }

    private static function rightSave(): void
    {
        self::role(['admin']);
        Database::query('INSERT INTO treatment_rights(right_code,right_name,organization) VALUES(?,?,?) ON DUPLICATE KEY UPDATE right_name=VALUES(right_name),organization=VALUES(organization)', [self::required('right_code'), self::required('right_name'), trim((string)($_POST['organization'] ?? ''))]);
        self::success('บันทึกสิทธิ์รักษาแล้ว', 'rights');
    }

    private static function rightToggle(): void
    {
        self::role(['admin']);
        Database::query('UPDATE treatment_rights SET is_active=IF(is_active=1,0,1) WHERE id=?', [(int)self::required('id')]);
        self::success('เปลี่ยนสถานะสิทธิ์แล้ว', 'rights');
    }

    private static function notificationsRead(): void
    {
        $user = self::role(['patient']);
        Database::query('UPDATE notifications SET is_read=1 WHERE user_id=?', [(int)$user['id']]);
        self::success('อ่านการแจ้งเตือนทั้งหมดแล้ว', 'notifications');
    }

    private static function profileSave(): void
    {
        $user = self::role(['admin','staff','dentist','patient']);
        $userId = (int)$user['id'];
        $profileImage = $user['profile_image'] ?? null;
        $uploaded = $_FILES['profile_image'] ?? null;

        if ($uploaded && (int)($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ((int)$uploaded['error'] !== UPLOAD_ERR_OK) throw new \RuntimeException('อัปโหลดรูปโปรไฟล์ไม่สำเร็จ');
            if ((int)$uploaded['size'] > 2 * 1024 * 1024) throw new \RuntimeException('รูปโปรไฟล์ต้องมีขนาดไม่เกิน 2 MB');

            $temporaryPath = (string)$uploaded['tmp_name'];
            $imageInfo = @getimagesize($temporaryPath);
            $mime = (string)($imageInfo['mime'] ?? '');
            $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            if (!isset($extensions[$mime])) throw new \RuntimeException('รองรับเฉพาะรูป JPG, PNG และ WEBP');

            $uploadDirectory = dirname(__DIR__, 2) . '/public/assets/uploads/profiles';
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                throw new \RuntimeException('ไม่สามารถสร้างโฟลเดอร์เก็บรูปโปรไฟล์ได้');
            }

            $filename = 'user-' . $userId . '-' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
            if (!move_uploaded_file($temporaryPath, $uploadDirectory . '/' . $filename)) {
                throw new \RuntimeException('ไม่สามารถบันทึกรูปโปรไฟล์ได้');
            }

            self::deleteOldProfileImage((string)$profileImage);
            $profileImage = '/assets/uploads/profiles/' . $filename;
        } elseif (!empty($_POST['remove_profile_image'])) {
            self::deleteOldProfileImage((string)$profileImage);
            $profileImage = null;
        }

        Database::query('UPDATE users SET full_name=?,email=?,profile_image=? WHERE id=?', [self::required('full_name'), trim((string)($_POST['email'] ?? '')), $profileImage, $userId]);
        if (!empty($_POST['password'])) Database::query('UPDATE users SET password_hash=? WHERE id=?', [Password::hash((string)$_POST['password']), (int)$user['id']]);
        $_SESSION['user'] = Database::query('SELECT id,username,role,full_name,email,profile_image,is_active,created_at FROM users WHERE id=?', [$userId])->fetch();
        self::success('บันทึกข้อมูลส่วนตัวแล้ว', 'profile');
    }

    private static function deleteOldProfileImage(string $profileImage): void
    {
        $prefix = '/assets/uploads/profiles/';
        if ($profileImage === '' || !str_starts_with($profileImage, $prefix)) return;
        $path = dirname(__DIR__, 2) . '/public/assets/uploads/profiles/' . basename($profileImage);
        if (is_file($path)) @unlink($path);
    }

    private static function success(string $message, string $page): never
    {
        $_SESSION['flash_success'] = $message;
        self::redirect($page);
    }

    private static function redirect(string $page): never
    {
        header('Location: /?page=' . rawurlencode($page));
        exit;
    }
}
