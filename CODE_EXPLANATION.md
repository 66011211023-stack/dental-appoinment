# คำอธิบายโค้ด DentiCare PHP + HTML + MySQL Docker

เอกสารนี้อธิบายว่าแต่ละไฟล์และแต่ละส่วนทำหน้าที่อะไร ระบบนี้เปลี่ยนเฉพาะส่วนประมวลผลจากเว็บไซต์ตัวอย่างเดิมมาเป็น PHP/MySQL ส่วนโครงสร้างหน้าจอและ CSS ใช้ชุดเดียวกับเว็บไซต์ตัวอย่างล่าสุด

## 1. ลำดับการทำงานของระบบ

1. ผู้ใช้เปิด `http://localhost:8080`
2. Apache ส่งคำขอทั้งหมดไปที่ `public/index.php`
3. `public/index.php` ตรวจตัวแปร `page` และ Session
4. หน้าเข้าสู่ระบบค้นหาบัญชีจากตาราง `users`
5. รหัสผ่านที่กรอกถูกแปลงเป็น SHA-256 แล้วเปรียบเทียบกับฐานข้อมูล
6. เมื่อถูกต้อง ระบบเก็บข้อมูลผู้ใช้ไว้ใน `$_SESSION['user']`
7. `header.php` อ่าน `role` แล้วสร้าง Sidebar ที่แตกต่างกันสำหรับผู้ใช้ 4 กลุ่ม
8. View แต่ละหน้าดึงข้อมูลผ่าน `Database::query()` และแสดงผลด้วย HTML/CSS ชุดเดียวกับเว็บไซต์ตัวอย่าง
9. เมื่อกดออกจากระบบ ระบบล้าง Session และกลับหน้า Login

## 2. Docker และฐานข้อมูล

### `docker-compose.yml`

- Service `web` คือ PHP 8.3 + Apache
- Service `db` คือ MySQL 8.4
- Port `8080:80` ทำให้เปิดเว็บที่ `localhost:8080`
- Port `3307:3306` ทำให้โปรแกรมบน Windows ติดต่อ MySQL ผ่าน Port 3307 ได้
- Volume `dental_data` เก็บข้อมูลแม้ปิด Container
- Healthcheck รอให้ MySQL พร้อมก่อนเริ่ม PHP

### `Dockerfile`

- ใช้ Image `php:8.3-apache`
- ติดตั้ง `PDO` และ `pdo_mysql`
- เปิด Apache Rewrite Module
- ใช้ `public` เป็น Document Root เพื่อป้องกันการเปิดไฟล์ Config โดยตรง

### `database/init.sql`

สร้างตารางหลักดังนี้:

| ตาราง | หน้าที่ |
|---|---|
| `users` | บัญชีเข้าสู่ระบบและประเภทผู้ใช้ |
| `patients` | ข้อมูลผู้ป่วยและสิทธิ์การรักษา |
| `dentists` | ข้อมูลทันตแพทย์และเลขใบอนุญาต |
| `appointments` | คำขอจองคิว วัน เวลา และสถานะ |
| `medical_histories` | ซักประวัติ โรคประจำตัว ยา และการแพ้ยา |
| `treatments` | ผลวินิจฉัย หัตถการ ตำแหน่งฟัน และค่าใช้จ่าย |
| `materials` | วัสดุทันตกรรมและจำนวนคงเหลือ |
| `material_transactions` | ประวัติรับเข้าและเบิกใช้วัสดุ |
| `dentist_schedules` | วันและเวลาว่างของทันตแพทย์ |
| `treatment_rights` | ประเภทสิทธิ์และหน่วยงานเจ้าของสิทธิ์ |
| `notifications` | การแจ้งเตือนของผู้ใช้งาน |
| `login_logs` | สถิติการเข้าสู่ระบบของแต่ละบัญชี |

ข้อมูลเริ่มต้นมีบัญชี `admin`, `staff`, `dentist`, `patient` และใช้รหัสผ่าน `1234`

## 3. จุดเริ่มต้นของ PHP

### `public/index.php`

- `session_start()` เริ่ม Session
- `spl_autoload_register()` โหลด Class ใน `app` อัตโนมัติ
- `$page` อ่านหน้าที่ต้องการจาก URL เช่น `?page=patients`
- ส่วน Login ตรวจ Username และ Password
- ส่วน Logout เรียก `Auth::logout()`
- `$allowed` ป้องกันการเรียกชื่อหน้าที่ไม่มีในระบบ
- หน้า Dashboard ดึงจำนวนผู้ป่วย นัดหมาย คำขอ และวัสดุใกล้หมด
- `$rolePages` จำกัดหน้าให้ตรงกับขอบเขตของผู้ใช้ทั้ง 4 กลุ่ม
- หน้า Module ส่งไปยังไฟล์แยก เช่น `modules/patients.php`

## 4. Core Classes

### `app/Core/Database.php`

- สร้าง PDO Connection เพียงครั้งเดียว
- อ่าน Host, Port, Database, Username และ Password จาก `config/config.php`
- เปิด Exception Mode เพื่อให้ตรวจข้อผิดพลาด SQL ได้ชัดเจน
- `query()` ใช้ Prepared Statement ป้องกัน SQL Injection

### `app/Core/Auth.php`

- `user()` คืนข้อมูลผู้ใช้ปัจจุบัน
- `check()` ตรวจว่าล็อกอินแล้วหรือไม่
- `requireLogin()` ป้องกันผู้ที่ยังไม่เข้าสู่ระบบ
- `logout()` ล้าง Session

### `app/Core/View.php`

- รับชื่อไฟล์ View และข้อมูลที่ต้องแสดง
- โหลด `header.php`
- โหลดเนื้อหาของหน้าที่เลือก
- โหลด `footer.php`
- โหลด `components.php` ซึ่งมีฟังก์ชันสร้างหัวข้อ การ์ดสถิติ และตารางมาตรฐาน

## 5. Layout และหน้าจอ

### `app/Views/layouts/header.php`

- สร้าง Sidebar ตาม `role`
- แบ่งเมนูเป็นหมวดเหมือนเว็บไซต์ตัวอย่าง
- สร้าง Topbar ช่องค้นหา การแจ้งเตือน และข้อมูลบัญชี
- ปุ่มออกจากระบบอยู่ใน Dropdown ชื่อผู้ใช้

### `app/Views/auth/login.php`

- โครงสร้างสองคอลัมน์เหมือนเว็บไซต์ตัวอย่าง
- ฝั่งซ้ายแสดงชื่อระบบ ข้อความ และสถิติ
- ฝั่งขวาเป็นฟอร์มเข้าสู่ระบบ
- ไม่มีปุ่มเลือกบัญชีสาธิตด้านล่าง

### `app/Views/dashboard/index.php`

- Admin แสดงภาพรวมผู้ป่วย นัดหมาย คำขอ วัสดุ และงานด่วน
- Staff แสดงงานตรวจคำขอ ผู้ป่วย และคลังวัสดุ
- Dentist แสดงตารางรักษาและสถิติการให้บริการ
- Patient แสดง Hero สีน้ำเงิน นัดหมายครั้งถัดไป และทางลัดบริการ

### `app/Views/modules/*.php`

แต่ละหน้าถูกแยกเป็นไฟล์ของตนเองเพื่อให้อ่านและแก้ไขง่าย:

- `appointments` รายการนัดหมายตามผู้ใช้ที่เข้าสู่ระบบ
- `patients` ข้อมูลผู้ป่วย ซักประวัติ และประวัติรักษาในหน้าเดียวกัน
- `materials` จำนวนวัสดุคงเหลือและจุดสั่งซื้อ
- `booking` ขั้นตอนเลือกบริการ วัน เวลา และส่งคำขอ
- `history` ประวัติการรักษาของผู้ป่วย
- `notifications` การแจ้งเตือนนัดหมายและผลการอนุมัติ
- `profile` ข้อมูลส่วนตัว
- `schedule` ตารางรักษารายวัน
- `treatments` วินิจฉัย ผลรักษา ค่าใช้จ่าย และตำแหน่งฟัน
- `reports` สถิติ การ์ดสรุป กราฟแท่ง และกราฟวงกลม
- `users` บุคลากรและบัญชีผู้ใช้งาน
- `rights` สิทธิ์การรักษา
- `material-usage` การเบิกวัสดุของทันตแพทย์
- `service-stats` สถิติการให้บริการของทันตแพทย์

## 6. CSS และ JavaScript

### `public/assets/css/browser-latest.css`

เป็น CSS จากเว็บไซต์ตัวอย่างล่าสุดโดยตรง ใช้กำหนด:

- สีกรมท่าและพื้นหลังสีขาว
- Sidebar และ Topbar
- Login Page
- การ์ดสถิติ ตาราง แบบฟอร์ม และ Modal
- หน้าผู้ป่วย Booking Timeline Notification และ Profile
- Responsive Layout สำหรับมือถือ

### `public/assets/css/php-compat.css`

เว็บไซต์เดิมใช้ React `<button>` สำหรับเมนู แต่ PHP ใช้ `<a>` เพื่อเปิด URL ไฟล์นี้ทำให้ `<a>` แสดงผลเหมือนปุ่มเดิมทุกประการ โดยไม่แก้ CSS ต้นฉบับ

### `public/assets/js/app.js`

- แสดง Toast เมื่อกดปุ่มตัวอย่าง
- เปิดและปิด Sidebar บนมือถือ
- กรองข้อมูลผู้ป่วยจากช่อง Search
- สลับหน้าลืมรหัสผ่านและตั้งรหัสผ่านใหม่
- ควบคุมขั้นตอนจองคิว 3 ขั้นตอน

## 7. HTML Preview

โฟลเดอร์ `html` ใช้สำหรับเปิดดูหน้าตาโดยไม่ต้องเปิด PHP หรือ MySQL

- `login.html` หน้าเข้าสู่ระบบ
- `dashboard.html` หน้า Admin Dashboard
- `staff.html` หน้าเจ้าหน้าที่
- `dentist.html` หน้าทันตแพทย์
- `patient.html` หน้าผู้ป่วย
- `appointments.html` หน้านัดหมาย
- `patients.html` หน้าผู้ป่วยและซักประวัติ
- `materials.html` หน้าคลังวัสดุ

HTML Preview เป็นตัวอย่างหน้าจอ ส่วนการบันทึกและอ่านข้อมูลจริงต้องเปิดผ่าน Docker ที่ `localhost:8080`

## 8. การแก้ปัญหา DB_HOST

- เมื่อ PHP รันใน Docker ให้ใช้ Host `db` เพราะเป็นชื่อ Service ใน Compose
- เมื่อ PHP รันด้วย XAMPP บน Windows ให้ใช้ Host `127.0.0.1` และ Port `3307`
- อย่าใช้ `db` กับ PHP ที่เปิดจาก XAMPP เพราะ Windows จะหา Host ชื่อนี้ไม่พบ

## 9. การเริ่มระบบใหม่หลังแก้ SQL

```bash
docker compose down -v
docker compose up -d --build
```

คำสั่ง `-v` จะลบฐานข้อมูลเดิมและให้ MySQL อ่าน `init.sql` ใหม่ จึงควรสำรองข้อมูลก่อนใช้กับข้อมูลจริง
